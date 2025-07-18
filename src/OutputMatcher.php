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
        if ($this->parsedFile->expectedPatternFile !== "") {
            return (string) @file_get_contents($this->parsedFile->expectedPatternFile);
        } elseif ($this->parsedFile->expectedPattern !== "") {
            return $this->parsedFile->expectedPattern;
        } elseif ($this->parsedFile->expectedRegexFile !== "") {
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
            OutputMatcherMode::Regex, OutputMatcherMode::Substitution => (bool) preg_match((string) $this->getRegex(), $actualOutput),
            default => $actualOutput === $expectedOutput,
        };
    }

    public function getRegex(): ?string
    {
        if ($this->getMode() === OutputMatcherMode::Literal) {
            return null;
        } elseif ($this->getMode() === OutputMatcherMode::Regex) {
            return "/^" . $this->getExpectedOutput() . "\$/s";
        }
        $result = $this->getExpectedOutput();
        $result = str_replace(
            [
                "%e",
                '%s',
                "%S",
                "%a",
                "%A",
                "%w",
                "%i",
                "%d",
                "%x",
                "%f",
                "%c",
            ],
            [
                DIRECTORY_SEPARATOR,
                "[^\\r\\n]+",
                "[^\\r\\n]*",
                ".+",
                ".*",
                "\\s*",
                "[+-]?\\d+",
                "\\d+",
                "[0-9a-fA-F]+",
                ".",
            ],
            preg_quote($result, "/")
        );
        return "/^" . $result . "\$/s";
    }
}
