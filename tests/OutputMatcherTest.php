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
    }
}
