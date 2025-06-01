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
    public const string SECTION_DESCRIPTION = "DESCRIPTION";
    public const string SECTION_SKIPIF = "SKIPIF";
    public const string SECTION_CONFLICTS = "CONFLICTS";
    public const string SECTION_EXTENSIONS = "EXTENSIONS";
    public const string SECTION_STDIN = "STDIN";
    public const string SECTION_INI = "INI";
    public const string SECTION_ARGS = "ARGS";
    public const string SECTION_ENV = "ENV";
    public const string SECTION_FILE = "FILE";
    public const string SECTION_FILEEOF = "FILEEOF";
    public const string SECTION_FILE_EXTERNAL = "FILE_EXTERNAL";
    public const string SECTION_REDIRECTTEST = "REDIRECTTEST";
    public const string SECTION_CGI = "CGI";
    public const string SECTION_XFAIL = "XFAIL";
    public const string SECTION_FLAKY = "FLAKY";
    public const string SECTION_EXPECTHEADERS = "EXPECTHEADERS";
    public const string SECTION_EXPECT = "EXPECT";
    public const string SECTION_EXPECT_EXTERNAL = "EXPECT_EXTERNAL";
    public const string SECTION_EXPECTF = "EXPECTF";
    public const string SECTION_EXPECTF_EXTERNAL = "EXPECTF_EXTERNAL";
    public const string SECTION_EXPECTREGEX = "EXPECTREGEX";
    public const string SECTION_EXPECTREGEX_EXTERNAL = "EXPECTREGEX_EXTERNAL";
    public const string SECTION_CLEAN = "CLEAN";

    private const array REQUIRED_SECTIONS = [
        self::SECTION_TEST,
        [self::SECTION_FILE, self::SECTION_FILEEOF, self::SECTION_FILE_EXTERNAL, self::SECTION_REDIRECTTEST, ],
        [self::SECTION_EXPECT, self::SECTION_EXPECT_EXTERNAL, self::SECTION_EXPECTREGEX, self::SECTION_EXPECTREGEX_EXTERNAL, ],
    ];

    private const array OPTIONAL_SECTIONS_ARRAY = [
        self::SECTION_ENV,
        self::SECTION_INI,
        self::SECTION_CONFLICTS,
        self::SECTION_EXTENSIONS,
    ];
    private const array OPTIONAL_SECTIONS_STRING = [
        self::SECTION_DESCRIPTION,
        self::SECTION_SKIPIF,
        self::SECTION_STDIN,
        self::SECTION_ARGS,
        self::SECTION_FILE,
        self::SECTION_FILEEOF,
        self::SECTION_FILE_EXTERNAL,
        self::SECTION_CLEAN,
    ];
    private const array OPTIONAL_SECTIONS_BOOLEAN = [
        self::SECTION_CGI,
        self::SECTION_XFAIL,
        self::SECTION_FLAKY,
    ];

    public function parse(string $filename, bool $checkRequiredSections = true): ParsedFile
    {
        $lines = @file($filename);
        if ($lines === false) {
            throw new FileNotFoundException("File $filename does not exist or cannot be read");
        }

        $sections = [];
        foreach ($lines as $line) {
            if (preg_match("/^--([A-Z_]+)--/", $line, $matches) === 1) {
                $section = $matches[1];
                $sections[$section] = "";
            } elseif (isset($section)) {
                $sections[$section] .= $line;
            }
        }

        $this->transformSections($sections, $filename);
        $this->addOptionalSections($sections);
        if ($checkRequiredSections) {
            $this->checkRequiredSections($sections, $filename);
        }

        $result = new ParsedFile();
        $result->testName = $sections[self::SECTION_TEST]; // @phpstan-ignore assign.propertyType
        $result->testDescription = $sections[self::SECTION_DESCRIPTION]; // @phpstan-ignore assign.propertyType
        $result->skipCode = $sections[self::SECTION_SKIPIF]; // @phpstan-ignore assign.propertyType
        $result->conflictingKeys = $sections[self::SECTION_CONFLICTS]; // @phpstan-ignore assign.propertyType
        $result->requiredExtensions = $sections[self::SECTION_EXTENSIONS]; // @phpstan-ignore assign.propertyType
        $result->input = $sections[self::SECTION_STDIN]; // @phpstan-ignore assign.propertyType
        $result->iniSettings = $sections[self::SECTION_INI]; // @phpstan-ignore assign.propertyType
        $result->arguments = $sections[self::SECTION_ARGS]; // @phpstan-ignore assign.propertyType
        $result->envVariables = $sections[self::SECTION_ENV]; // @phpstan-ignore assign.propertyType
        $result->testCode = $sections[self::SECTION_FILEEOF] !== "" ? $sections[self::SECTION_FILEEOF] : $sections[self::SECTION_FILE]; // @phpstan-ignore assign.propertyType
        $result->testFile = $sections[self::SECTION_FILE_EXTERNAL]; // @phpstan-ignore assign.propertyType
        $result->testRedirects = $sections[self::SECTION_REDIRECTTEST] ?? []; // @phpstan-ignore assign.propertyType
        $result->requiresCgiBinary = $sections[self::SECTION_CGI] !== false;
        $result->supposedToFail = $sections[self::SECTION_XFAIL]; // @phpstan-ignore assign.propertyType
        $result->flaky = $sections[self::SECTION_FLAKY]; // @phpstan-ignore assign.propertyType
        $result->expectedHeaders = $sections[self::SECTION_EXPECTHEADERS] ?? []; // @phpstan-ignore assign.propertyType
        $result->expectedText = $sections[self::SECTION_EXPECT] ?? ""; // @phpstan-ignore assign.propertyType
        $result->expectedTextFile = $sections[self::SECTION_EXPECT_EXTERNAL] ?? ""; // @phpstan-ignore assign.propertyType
        $result->expectedPattern = $sections[self::SECTION_EXPECTF] ?? ""; // @phpstan-ignore assign.propertyType
        $result->expectedPatternFile = $sections[self::SECTION_EXPECTF_EXTERNAL] ?? ""; // @phpstan-ignore assign.propertyType
        $result->expectedRegex = $sections[self::SECTION_EXPECTREGEX] ?? ""; // @phpstan-ignore assign.propertyType
        $result->expectedRegexFile = $sections[self::SECTION_EXPECTREGEX_EXTERNAL] ?? ""; // @phpstan-ignore assign.propertyType
        $result->cleanCode = $sections[self::SECTION_CLEAN]; // @phpstan-ignore assign.propertyType

        return $result;
    }

    /**
     * @param array<string, string|bool|array<string, mixed>> $sections
     */
    private function transformSections(array &$sections, string $filename): void
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
                case self::SECTION_CONFLICTS:
                    $content = str_replace(PHP_EOL, "\n", $content);
                    $content = explode("\n", $content);
                    break;
                case self::SECTION_EXPECTHEADERS:
                    $values = [];
                    foreach (explode("\n", $content) as $line) {
                        $value = explode(":", $line, 2);
                        if ($value[0] !== "" && isset($value[1])) {
                            $values[$value[0]] = trim($value[1]);
                        }
                    }
                    $content = $values;
                    break;
                case self::SECTION_FILE_EXTERNAL:
                case self::SECTION_EXPECT_EXTERNAL:
                case self::SECTION_EXPECTF_EXTERNAL:
                case self::SECTION_EXPECTREGEX_EXTERNAL:
                    $content = dirname($filename) . DIRECTORY_SEPARATOR . $content;
                    break;
            }
        }
    }

    /**
     * @param array<string, string|bool|array<string, mixed>> $sections
     */
    private function addOptionalSections(array &$sections): void
    {
        foreach (self::OPTIONAL_SECTIONS_ARRAY as $sectionName) {
            if (!isset($sections[$sectionName])) {
                $sections[$sectionName] = [];
            }
        }
        foreach (self::OPTIONAL_SECTIONS_STRING as $sectionName) {
            if (!isset($sections[$sectionName])) {
                $sections[$sectionName] = "";
            }
        }
        foreach (self::OPTIONAL_SECTIONS_BOOLEAN as $sectionName) {
            if (!isset($sections[$sectionName])) {
                $sections[$sectionName] = false;
            } elseif ($sections[$sectionName] === "") {
                $sections[$sectionName] = true;
            }
        }
    }

    /**
     * @param array<string, string|bool|array<string, mixed>> $sections
     */
    private function checkRequiredSections(array $sections, string $filename): void
    {
        foreach (self::REQUIRED_SECTIONS as $requiredSection) {
            if (is_string($requiredSection)) {
                if (!array_key_exists($requiredSection, $sections)) {
                    throw new RequiredSectionMissingException($requiredSection, $filename);
                }
            } elseif (is_array($requiredSection)) {
                if (count(array_intersect($requiredSection, array_keys($sections))) === 0) {
                    throw new RequiredSectionMissingException($requiredSection, $filename);
                }
            }
        }
    }
}
