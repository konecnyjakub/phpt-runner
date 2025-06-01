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

        if ($parsedFile->skipCode !== "") {
            $skipResult = $this->phpRunner->runCode($parsedFile->skipCode);
            if (str_starts_with($skipResult, "skip")) {
                return $skipResult;
            }
        }

        return null;
    }

    private function getExpectedOutput(ParsedFile $parsedFile): string
    {
        if ($parsedFile->expectedTextFile !== "") {
            return (string) @file_get_contents($parsedFile->expectedTextFile);
        } elseif ($parsedFile->expectedText !== "") {
            return $parsedFile->expectedText;
        }
        return "";
    }

    public function runFile(string $fileName): FileResultSet
    {
        $parsedFile = $this->parser->parse($fileName);

        $skipText = $this->checkPrerequisites($parsedFile);
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

        $success = true;
        $expectedOutput = $this->getExpectedOutput($parsedFile);
        for ($attemptNumber = 1; $attemptNumber <= 2; $attemptNumber++) {
            $output = $this->phpRunner->runCode(
                $parsedFile->testFile !== "" ? (string) file_get_contents($parsedFile->testFile) : $parsedFile->testCode,
                $parsedFile->iniSettings,
                $parsedFile->envVariables,
                $parsedFile->arguments,
                $parsedFile->input
            );
            $success = $output === $expectedOutput;

            if ($success || $parsedFile->flaky !== false) {
                break;
            }
        }

        if ($parsedFile->supposedToFail !== false) {
            $success = !$success;
        }

        if ($parsedFile->cleanCode !== "") {
            $this->phpRunner->runCode($parsedFile->cleanCode);
        }

        return new FileResultSet(
            $fileName,
            $parsedFile->testName,
            $parsedFile->testDescription,
            $success ? Outcome::Passed : Outcome::Failed,
            $output, // @phpstan-ignore variable.undefined
            $expectedOutput
        );
    }
}
