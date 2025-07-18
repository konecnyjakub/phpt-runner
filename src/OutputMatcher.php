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
        if ($this->parsedFile->expectedRegexFile !== "") {
            return (string) @file_get_contents($this->parsedFile->expectedRegexFile);
        } elseif ($this->parsedFile->expectedRegex !== "") {
            return $this->parsedFile->expectedRegex;
        } elseif ($this->parsedFile->expectedTextFile !== "") {
            return (string) @file_get_contents($this->parsedFile->expectedTextFile);
        } elseif ($this->parsedFile->expectedText !== "") {
            return $this->parsedFile->expectedText;
        }
        return "";
    }

    public function getMode(): OutputMatcherMode
    {
        return match (true) {
            $this->parsedFile->expectedPatternFile !== "" || $this->parsedFile->expectedPattern !== "" => OutputMatcherMode::Substitution,
            $this->parsedFile->expectedRegexFile !== "" || $this->parsedFile->expectedRegex !== "" => OutputMatcherMode::Regex,
            default => OutputMatcherMode::Literal,
        };
    }

    public function matches(string $actualOutput): bool
    {
        $expectedOutput = $this->getExpectedOutput();
        return match ($this->getMode()) {
            OutputMatcherMode::Regex => (bool) preg_match("/^$expectedOutput\$/s", $actualOutput),
            default => $actualOutput === $expectedOutput,
        };
    }
}
