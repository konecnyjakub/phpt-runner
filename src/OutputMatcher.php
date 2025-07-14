<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

final readonly class OutputMatcher
{
    public function __construct(private ParsedFile $parsedFile)
    {
    }

    public function getExpectedOutput(): string
    {
        if ($this->parsedFile->expectedTextFile !== "") {
            return (string) @file_get_contents($this->parsedFile->expectedTextFile);
        } elseif ($this->parsedFile->expectedText !== "") {
            return $this->parsedFile->expectedText;
        }
        return "";
    }

    public function matches(string $actualOutput): bool
    {
        return $actualOutput === $this->getExpectedOutput();
    }
}
