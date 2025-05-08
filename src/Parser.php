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
    public const string SECTION_STDIN = "STDIN";
    public const string SECTION_INI = "INI";
    public const string SECTION_ARGS = "ARGS";
    public const string SECTION_ENV = "ENV";
    public const string SECTION_FILE = "FILE";
    public const string SECTION_EXPECT = "EXPECT";
    public const string SECTION_EXPECT_EXTERNAL = "EXPECT_EXTERNAL";
    public const string SECTION_EXPECTREGEX = "EXPECTREGEX";
    public const string SECTION_CLEAN = "CLEAN";

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
        return $sections;
    }
}
