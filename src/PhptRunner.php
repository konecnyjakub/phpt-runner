<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

final readonly class PhptRunner
{
    public function __construct(private Parser $parser, private PhpRunner $phpRunner)
    {
    }

    public function runFile(string $fileName): FileResultSet
    {
        $parsedFile = $this->parser->parse($fileName);

        if ($parsedFile->requiresCgiBinary && !$this->phpRunner->isCgiBinary()) {
            return new FileResultSet(
                $fileName,
                $parsedFile->testName,
                $parsedFile->testDescription,
                Outcome::Skipped,
                "This test requires the cgi binary.",
                ""
            );
        }

        if ($parsedFile->skipCode !== "") {
            $skipResult = $this->phpRunner->runCode($parsedFile->skipCode);
            if (str_starts_with($skipResult, "skip")) {
                return new FileResultSet(
                    $fileName,
                    $parsedFile->testName,
                    $parsedFile->testDescription,
                    Outcome::Skipped,
                    $skipResult,
                    ""
                );
            }
        }

        $output = $this->phpRunner->runCode(
            $parsedFile->testFile !== "" ? (string) file_get_contents($parsedFile->testFile) : $parsedFile->testCode,
            $parsedFile->iniSettings,
            $parsedFile->envVariables,
            $parsedFile->arguments,
            $parsedFile->input
        );
        if (str_ends_with($output, PHP_EOL)) {
            $output = rtrim($output, PHP_EOL);
        }

        $success = true;
        for ($attemptNumber = 1; $attemptNumber <= 2; $attemptNumber++) {
            $expectedOutput = "";
            if ($parsedFile->expectedTextFile !== "") {
                $contents = @file_get_contents($parsedFile->expectedTextFile);
                $success = $success && $contents !== false && $output === $contents;
                if ($contents !== false) {
                    $expectedOutput = $contents;
                }
            } elseif ($parsedFile->expectedText !== "") {
                $expectedOutput = $parsedFile->expectedText;
                $success = ($output === $parsedFile->expectedText);
            }

            if ($success || $parsedFile->flaky !== false) {
                break;
            }
        }

        if (!$success && $parsedFile->supposedToFail !== false) {
            $success = true;
        }

        if ($parsedFile->cleanCode !== "") {
            $this->phpRunner->runCode($parsedFile->cleanCode);
        }

        return new FileResultSet(
            $fileName,
            $parsedFile->testName,
            $parsedFile->testDescription,
            $success ? Outcome::Passed : Outcome::Failed,
            $output,
            $expectedOutput // @phpstan-ignore variable.undefined
        );
    }
}
