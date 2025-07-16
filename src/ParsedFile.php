<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

final class ParsedFile
{
    public string $testName = "";
    public string $testDescription = "";
    public string $skipCode = "";
    /** @var string[] */
    public array $conflictingKeys = [];
    public bool $captureStdin = true;
    public bool $captureStdout = true;
    public bool $captureStderr = true;
    /** @var string[] */
    public array $requiredExtensions = [];
    /** @var mixed[] */
    public array $getData = [];
    /** @var mixed[] */
    public array $cookies = [];
    public string $input = "";
    /** @var array<string, string> */
    public array $iniSettings = [];
    public string $arguments = "";
    /** @var array<string, string> */
    public array $envVariables = [];
    public string $testCode = "";
    public string $testFile = "";
    /** @var mixed[] */
    public array $testRedirects = [];
    public bool $requiresCgiBinary = false;
    public bool|string $supposedToFail = false;
    public bool|string $flaky = false;
    /** @var array<string, string> */
    public array $expectedHeaders = [];
    public string $expectedText = "";
    public string $expectedTextFile = "";
    public string $expectedPattern = "";
    public string $expectedPatternFile = "";
    public string $expectedRegex = "";
    public string $expectedRegexFile = "";
    public string $cleanCode = "";
}
