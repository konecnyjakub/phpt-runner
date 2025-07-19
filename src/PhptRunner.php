<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

final readonly class PhptRunner
{
    public function __construct(private Parser $parser, private PhpRunner $phpRunner)
    {
    }

    private function checkPrerequisites(ParsedFile $parsedFile): ?string
    {
        if ($parsedFile->requiresCgiBinary && !$this->phpRunner->isCgiBinary()) {
            return "This test requires the cgi binary.";
        }

        foreach ($parsedFile->requiredExtensions as $extension) {
            if (!$this->phpRunner->isExtensionLoaded($extension)) {
                return "This test requires PHP extension $extension.";
            }
        }

        return null;
    }

    private function getSkipResult(ParsedFile $parsedFile): ?string
    {
        if ($parsedFile->skipCode === "") {
            return null;
        }
        return $this->phpRunner->runCode($parsedFile->skipCode);
    }

    public function runFile(string $fileName): FileResultSet
    {
        $parsedFile = $this->parser->parse($fileName);

        $skipText = $this->checkPrerequisites($parsedFile);
        $skipResult = null;
        if ($skipText === null) {
            $skipResult = $this->getSkipResult($parsedFile);
            if (is_string($skipResult) && str_starts_with(strtolower($skipResult), "skip")) {
                $skipText = $skipResult;
            }
        }
        if (is_string($skipText)) {
            return new FileResultSet(
                $fileName,
                $parsedFile->testName,
                $parsedFile->testDescription,
                Outcome::Skipped,
                $skipText,
                ""
            );
        }

        if (is_string($skipResult)) {
            if (str_starts_with(strtolower($skipResult), "xfail")) {
                $parsedFile->supposedToFail = true;
            }
            if (str_starts_with(strtolower($skipResult), "flaky")) {
                $parsedFile->flaky = true;
            }
        }

        $success = true;
        $outputMatcher = new OutputMatcher($parsedFile);
        for ($attemptNumber = 1; $attemptNumber <= 2; $attemptNumber++) {
            $output = $this->phpRunner->runCode(
                $parsedFile->testFile !== "" ? (string) file_get_contents($parsedFile->testFile) : $parsedFile->testCode,
                $parsedFile->iniSettings,
                $parsedFile->envVariables,
                $parsedFile->arguments,
                $parsedFile->input,
                dirname($fileName),
                $parsedFile->captureStdout,
                $parsedFile->captureStderr
            );
            $success = $outputMatcher->matches($output);

            if ($success || $parsedFile->flaky !== false) {
                break;
            }
        }

        if ($parsedFile->supposedToFail !== false) {
            $success = !$success;
        }

        if ($parsedFile->cleanCode !== "") {
            $this->phpRunner->runCode($parsedFile->cleanCode, workingDirectory: dirname($fileName));
        }

        return new FileResultSet(
            $fileName,
            $parsedFile->testName,
            $parsedFile->testDescription,
            $success ? Outcome::Passed : Outcome::Failed,
            $output, // @phpstan-ignore variable.undefined
            $outputMatcher->getExpectedOutput()
        );
    }
}
