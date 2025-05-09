<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

final class ParsedFile
{
    public string $testName = "";
    public string $skipCode = "";
    /** @var string[] */
    public array $conflictingKeys = [];
    /** @var string[] */
    public array $requiredExtensions = [];
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
    public bool|string $supposedToFail = false;
    public bool|string $flaky = false;
    /** @var array<string, string> */
    public array $expectedHeaders = [];
    public string $expectedText = "";
    public string $expectedTextFile = "";
    public string $expectedRegex = "";
    public string $expectedRegexFile = "";
    public string $cleanCode = "";
}
