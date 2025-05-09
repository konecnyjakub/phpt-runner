<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

/**
 * Parser for .phpt files
 *
 * @see https://php.github.io/php-src/miscellaneous/writing-tests.html
 */
final readonly class Parser
{
    public const string SECTION_TEST = "TEST";
    public const string SECTION_SKIPIF = "SKIPIF";
    public const string SECTION_CONFLICTS = "CONFLICTS";
    public const string SECTION_EXTENSIONS = "EXTENSIONS";
    public const string SECTION_STDIN = "STDIN";
    public const string SECTION_INI = "INI";
    public const string SECTION_ARGS = "ARGS";
    public const string SECTION_ENV = "ENV";
    public const string SECTION_FILE = "FILE";
    public const string SECTION_FILE_EXTERNAL = "FILE_EXTERNAL";
    public const string SECTION_REDIRECTTEST = "REDIRECTTEST";
    public const string SECTION_XFAIL = "XFAIL";
    public const string SECTION_FLAKY = "FLAKY";
    public const string SECTION_EXPECTHEADERS = "EXPECTHEADERS";
    public const string SECTION_EXPECT = "EXPECT";
    public const string SECTION_EXPECT_EXTERNAL = "EXPECT_EXTERNAL";
    public const string SECTION_EXPECTREGEX = "EXPECTREGEX";
    public const string SECTION_EXPECTREGEX_EXTERNAL = "EXPECTREGEX_EXTERNAL";
    public const string SECTION_CLEAN = "CLEAN";

    private const array REQUIRED_SECTIONS = [
        self::SECTION_TEST,
        [self::SECTION_FILE, self::SECTION_FILE_EXTERNAL, self::SECTION_REDIRECTTEST, ],
        [self::SECTION_EXPECT, self::SECTION_EXPECT_EXTERNAL, self::SECTION_EXPECTREGEX, self::SECTION_EXPECTREGEX_EXTERNAL, ],
    ];

    private const array ARRAY_SECTIONS = [
        self::SECTION_ENV,
        self::SECTION_INI,
    ];
    private const array STRING_SECTIONS = [
        self::SECTION_ARGS,
    ];

    /**
     * @return array<string, string|array<string, mixed>>
     */
    public function parse(string $filename): array
    {
        $lines = @file($filename);
        if ($lines === false) {
            return [];
        }

        $sections = [];
        foreach ($lines as $line) {
            if (preg_match("/^--([A-Z]+)--/", $line, $matches) === 1) {
                $section = $matches[1];
                $sections[$section] = "";
            } elseif (isset($section)) {
                $sections[$section] .= $line;
            }
        }

        $this->transformSections($sections);
        $this->addOptionalSections($sections);
        $this->checkRequiredSections($sections, $filename);

        return $sections;
    }

    /**
     * @param array<string, string|array<string, mixed>> $sections
     */
    private function transformSections(array &$sections): void
    {
        /**
         * @var string $sectionName
         * @var string $content
         */
        foreach ($sections as $sectionName => &$content) {
            $content = trim($content);
            switch ($sectionName) {
                case self::SECTION_ENV:
                case self::SECTION_INI:
                    $values = [];
                    foreach (explode("\n", $content) as $line) {
                        $value = explode("=", $line, 2);
                        if ($value[0] !== "" && isset($value[1])) {
                            $values[$value[0]] = $value[1];
                        }
                    }
                    $content = $values;
                    break;
            }
        }
    }

    /**
     * @param array<string, string|array<string, mixed>> $sections
     */
    private function addOptionalSections(array &$sections): void
    {
        foreach (self::ARRAY_SECTIONS as $sectionName) {
            if (!isset($sections[$sectionName])) {
                $sections[$sectionName] = [];
            }
        }
        foreach (self::STRING_SECTIONS as $sectionName) {
            if (!isset($sections[$sectionName])) {
                $sections[$sectionName] = "";
            }
        }
    }

    /**
     * @param array<string, string|array<string, mixed>> $sections
     */
    private function checkRequiredSections(array $sections, string $filename): void
    {
        foreach (self::REQUIRED_SECTIONS as $requiredSection) {
            if (is_string($requiredSection)) {
                if (!array_key_exists($requiredSection, $sections)) {
                    throw new ParseErrorException("Required section $requiredSection not found in file $filename");
                }
            } elseif (is_array($requiredSection)) {
                if (count(array_intersect($requiredSection, array_keys($sections))) === 0) {
                    throw new ParseErrorException("At least one of sections " . join(", ", $requiredSection) . " is required, none found in file $filename");
                }
            }
        }
    }
}
