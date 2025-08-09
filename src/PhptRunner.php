<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

use Psr\EventDispatcher\EventDispatcherInterface;

final readonly class PhptRunner
{
    public function __construct(
        private Parser $parser,
        private PhpRunner $phpRunner,
        private ?EventDispatcherInterface $eventDispatcher = null
    ) {
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

    /**
     * Returns $output stripped of headers
     */
    private function getCleanOutput(string $output): string
    {
        $cleanOutput = $output;
        if (str_contains($cleanOutput, "\r\n\r\n")) {
            $cleanOutput = explode("\r\n\r\n", $cleanOutput, 2)[1];
        }
        return $cleanOutput;
    }

    /**
     * Checks if $output meets conditions set in $parsedFile
     */
    private function isSuccess(ParsedFile $parsedFile, string $output): bool
    {
        $outputMatcher = new OutputMatcher($parsedFile);
        if (!$outputMatcher->matches($this->getCleanOutput($output))) {
            return false;
        }

        if (count($parsedFile->expectedHeaders) > 0) {
            $headersMatcher = new HeadersMatcher($parsedFile);
            return $headersMatcher->matches($output);
        }

        return true;
    }

    public function runFile(string $fileName): FileResultSet
    {
        try {
            $parsedFile = $this->parser->parse($fileName);
        } catch (ParseErrorException $e) {
            return new FileResultSet(
                $fileName,
                "",
                "",
                Outcome::Failed,
                "Invalid file: " . $e->getMessage()
            );
        }

        $skipText = $this->checkPrerequisites($parsedFile);
        $skipResult = null;
        if ($skipText === null) {
            $skipResult = $this->getSkipResult($parsedFile);
            if (is_string($skipResult) && str_starts_with(strtolower($skipResult), "skip")) {
                $skipText = $skipResult;
            }
        }
        if (is_string($skipText)) {
            $fileResultSet = new FileResultSet(
                $fileName,
                $parsedFile->testName,
                $parsedFile->testDescription,
                Outcome::Skipped,
                $skipText
            );
            $this->eventDispatcher?->dispatch(new Events\TestSkipped($fileResultSet));
            return $fileResultSet;
        }

        if (is_string($skipResult)) {
            if (str_starts_with(strtolower($skipResult), "xfail")) {
                $parsedFile->supposedToFail = true;
            }
            if (str_starts_with(strtolower($skipResult), "flaky")) {
                $parsedFile->flaky = true;
            }
        }

        $this->eventDispatcher?->dispatch(new Events\TestStarted($parsedFile));
        $success = true;
        for ($attemptNumber = 1; $attemptNumber <= 2; $attemptNumber++) {
            $output = $this->phpRunner->runCode(
                $parsedFile->testFile !== "" ? (string) file_get_contents($parsedFile->testFile) : $parsedFile->testCode,
                $parsedFile->iniSettings,
                $parsedFile->envVariables,
                $parsedFile->arguments,
                $parsedFile->input,
                dirname($fileName),
                $parsedFile->captureStdin,
                $parsedFile->captureStdout,
                $parsedFile->captureStderr
            );
            $success = $this->isSuccess($parsedFile, $output);

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

        $fileResultSet = new FileResultSet(
            $fileName,
            $parsedFile->testName,
            $parsedFile->testDescription,
            $success ? Outcome::Passed : Outcome::Failed,
            $this->getCleanOutput($output), // @phpstan-ignore variable.undefined
            (new OutputMatcher($parsedFile))->getExpectedOutput()
        );
        $this->eventDispatcher?->dispatch(new Events\TestFinished($fileResultSet));
        $this->eventDispatcher?->dispatch(
            $success ? new Events\TestPassed($fileResultSet) : new Events\TestFailed($fileResultSet)
        );
        return $fileResultSet;
    }
}
