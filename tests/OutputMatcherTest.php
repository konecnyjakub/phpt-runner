<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

use MyTester\Attributes\TestSuite;
use MyTester\TestCase;

#[TestSuite("OutputMatcher")]
final class OutputMatcherTest extends TestCase
{
    public function testGetExpectedOutput(): void
    {
        $outputMatcher = new OutputMatcher(new ParsedFile());
        $this->assertSame("", $outputMatcher->getExpectedOutput());

        $parsedFile = new ParsedFile();
        $parsedFile->expectedText = "abc";
        $outputMatcher = new OutputMatcher($parsedFile);
        $this->assertSame("abc", $outputMatcher->getExpectedOutput());

        $parsedFile->expectedTextFile = __DIR__ . DIRECTORY_SEPARATOR . "test_external_output.txt";
        $outputMatcher = new OutputMatcher($parsedFile);
        $this->assertSame("test123", $outputMatcher->getExpectedOutput());

        $parsedFile->expectedRegex = "test[0-9]+";
        $outputMatcher = new OutputMatcher($parsedFile);
        $this->assertSame("test[0-9]+", $outputMatcher->getExpectedOutput());

        $parsedFile->expectedRegexFile = __DIR__ . DIRECTORY_SEPARATOR . "test_external_output_regex.txt";
        $outputMatcher = new OutputMatcher($parsedFile);
        $this->assertSame("test[0-9]+ext", $outputMatcher->getExpectedOutput());

        $parsedFile->expectedPattern = "%i%w%s test";
        $outputMatcher = new OutputMatcher($parsedFile);
        $this->assertSame("%i%w%s test", $outputMatcher->getExpectedOutput());

        $parsedFile->expectedPatternFile = __DIR__ . DIRECTORY_SEPARATOR . "test_external_output_substitution.txt";
        $outputMatcher = new OutputMatcher($parsedFile);
        $this->assertSame("%x%sext", $outputMatcher->getExpectedOutput());
    }

    public function testGetMode(): void
    {
        $outputMatcher = new OutputMatcher(new ParsedFile());
        $this->assertSame(OutputMatcherMode::Literal, $outputMatcher->getMode());

        $parsedFile = new ParsedFile();
        $parsedFile->expectedText = "abc";
        $outputMatcher = new OutputMatcher($parsedFile);
        $this->assertSame(OutputMatcherMode::Literal, $outputMatcher->getMode());

        $parsedFile->expectedTextFile = __DIR__ . DIRECTORY_SEPARATOR . "test_external_output.txt";
        $outputMatcher = new OutputMatcher($parsedFile);
        $this->assertSame(OutputMatcherMode::Literal, $outputMatcher->getMode());

        $parsedFile->expectedRegex = "test[0-9]+";
        $outputMatcher = new OutputMatcher($parsedFile);
        $this->assertSame(OutputMatcherMode::Regex, $outputMatcher->getMode());

        $parsedFile->expectedRegexFile = __DIR__ . DIRECTORY_SEPARATOR . "test_external_output_regex.txt";
        $outputMatcher = new OutputMatcher($parsedFile);
        $this->assertSame(OutputMatcherMode::Regex, $outputMatcher->getMode());

        $parsedFile->expectedPattern = "%i%w%s test";
        $outputMatcher = new OutputMatcher($parsedFile);
        $this->assertSame(OutputMatcherMode::Substitution, $outputMatcher->getMode());

        $parsedFile->expectedPatternFile = __DIR__ . DIRECTORY_SEPARATOR . "test_external_output_substitution.txt";
        $outputMatcher = new OutputMatcher($parsedFile);
        $this->assertSame(OutputMatcherMode::Substitution, $outputMatcher->getMode());
    }

    public function testMatches(): void
    {
        $outputMatcher = new OutputMatcher(new ParsedFile());
        $this->assertTrue($outputMatcher->matches(""));
        $this->assertFalse($outputMatcher->matches("abc"));
        $this->assertFalse($outputMatcher->matches("test123"));

        $parsedFile = new ParsedFile();
        $parsedFile->expectedText = "abc";
        $outputMatcher = new OutputMatcher($parsedFile);
        $this->assertFalse($outputMatcher->matches(""));
        $this->assertTrue($outputMatcher->matches("abc"));
        $this->assertFalse($outputMatcher->matches("test123"));

        $parsedFile->expectedTextFile = __DIR__ . DIRECTORY_SEPARATOR . "test_external_output.txt";
        $outputMatcher = new OutputMatcher($parsedFile);
        $this->assertFalse($outputMatcher->matches(""));
        $this->assertFalse($outputMatcher->matches("abc"));
        $this->assertTrue($outputMatcher->matches("test123"));

        $parsedFile->expectedRegex = "test[0-9]+";
        $outputMatcher = new OutputMatcher($parsedFile);
        $this->assertFalse($outputMatcher->matches(""));
        $this->assertFalse($outputMatcher->matches("abc"));
        $this->assertTrue($outputMatcher->matches("test123"));

        $parsedFile->expectedRegexFile = __DIR__ . DIRECTORY_SEPARATOR . "test_external_output_regex.txt";
        $outputMatcher = new OutputMatcher($parsedFile);
        $this->assertFalse($outputMatcher->matches(""));
        $this->assertFalse($outputMatcher->matches("abc"));
        $this->assertFalse($outputMatcher->matches("test123"));
        $this->assertTrue($outputMatcher->matches("test123ext"));

        $parsedFile->expectedPattern = "%i%w%s test";
        $outputMatcher = new OutputMatcher($parsedFile);
        $this->assertFalse($outputMatcher->matches(""));
        $this->assertFalse($outputMatcher->matches("abc"));
        $this->assertFalse($outputMatcher->matches("test123"));
        $this->assertFalse($outputMatcher->matches("test123ext"));
        $this->assertTrue($outputMatcher->matches("+123 abc test"));

        $parsedFile->expectedPatternFile = __DIR__ . DIRECTORY_SEPARATOR . "test_external_output_substitution.txt";
        $outputMatcher = new OutputMatcher($parsedFile);
        $this->assertFalse($outputMatcher->matches(""));
        $this->assertFalse($outputMatcher->matches("abc"));
        $this->assertFalse($outputMatcher->matches("test123"));
        $this->assertFalse($outputMatcher->matches("test123ext"));
        $this->assertFalse($outputMatcher->matches("+123 abc test"));
        $this->assertTrue($outputMatcher->matches("11f test ext"));
    }

    public function testGetRegex(): void
    {
        $outputMatcher = new OutputMatcher(new ParsedFile());
        $this->assertNull($outputMatcher->getRegex());

        $parsedFile = new ParsedFile();
        $parsedFile->expectedText = "abc";
        $outputMatcher = new OutputMatcher($parsedFile);
        $this->assertNull($outputMatcher->getRegex());

        $parsedFile->expectedTextFile = __DIR__ . DIRECTORY_SEPARATOR . "test_external_output.txt";
        $outputMatcher = new OutputMatcher($parsedFile);
        $this->assertNull($outputMatcher->getRegex());

        $parsedFile->expectedRegex = "test[0-9]+";
        $outputMatcher = new OutputMatcher($parsedFile);
        $this->assertSame("/^test[0-9]+\$/s", $outputMatcher->getRegex());

        $parsedFile->expectedRegexFile = __DIR__ . DIRECTORY_SEPARATOR . "test_external_output_regex.txt";
        $outputMatcher = new OutputMatcher($parsedFile);
        $this->assertSame("/^test[0-9]+ext\$/s", $outputMatcher->getRegex());

        $parsedFile->expectedPattern = "%i%w%s test";
        $outputMatcher = new OutputMatcher($parsedFile);
        $this->assertSame("/^[+-]?\\d+\\s*[^\\r\\n]+ test\$/s", $outputMatcher->getRegex());

        $parsedFile->expectedPatternFile = __DIR__ . DIRECTORY_SEPARATOR . "test_external_output_substitution.txt";
        $outputMatcher = new OutputMatcher($parsedFile);
        $this->assertSame("/^[0-9a-fA-F]+[^\\r\\n]+ext\$/s", $outputMatcher->getRegex());
    }
}
