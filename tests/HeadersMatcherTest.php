<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

use MyTester\Attributes\TestSuite;
use MyTester\TestCase;

#[TestSuite("HeadersMatcher")]
final class HeadersMatcherTest extends TestCase
{
    public function testOutputHeaders(): void
    {
        $headersMatcher = new HeadersMatcher(new ParsedFile());
        $this->assertSame([], $headersMatcher->getOutputHeaders(""));
        $this->assertSame([], $headersMatcher->getOutputHeaders("abc\r\ndef"));
        $this->assertSame(
            ["content-type" => "text/plain; charset=UTF-8", "status" => "200 OK"],
            $headersMatcher->getOutputHeaders(
                "Content-type: text/plain; charset=UTF-8" .
                PHP_EOL .
                "Status:200 OK \r\n\r\nnabc" .
                PHP_EOL .
                "def"
            )
        );

        $parsedFile = (new Parser())->parse(__DIR__ . DIRECTORY_SEPARATOR . "test_expected_headers.phpt");
        $phpRunner = new PhpRunner(
            PHP_OS_FAMILY !== "Windows" ? "php-cgi" : "C:\\tools\\php\\php-cgi.exe",
            ["opcache.enable" => 0, "expose_php" => 0,]
        );
        $output = $phpRunner->runCode($parsedFile->testCode);
        $this->assertSame(
            ["content-type" => "text/plain; charset=UTF-8", "pragma" => "no-cache",],
            $headersMatcher->getOutputHeaders($output)
        );
    }

    public function testMatches(): void
    {
        $headersMatcher = new HeadersMatcher(new ParsedFile());
        $this->assertTrue($headersMatcher->matches(""));
        $this->assertTrue($headersMatcher->matches("abc\r\ndef"));
        $this->assertTrue($headersMatcher->matches(
            "Content-type: text/plain; charset=UTF-8" .
            PHP_EOL .
            "Status:200 OK \r\n\r\nnabc" .
            PHP_EOL .
            "def"
        ));

        $parsedFile = (new Parser())->parse(__DIR__ . DIRECTORY_SEPARATOR . "test_expected_headers.phpt");
        $phpRunner = new PhpRunner(PHP_OS_FAMILY !== "Windows" ? "php-cgi" : "C:\\tools\\php\\php-cgi.exe");
        $headersMatcher = new HeadersMatcher($parsedFile);
        $output = $phpRunner->runCode($parsedFile->testCode, iniSettings: ["opcache.enable" => 0, "expose_php" => 0,]);
        $this->assertTrue($headersMatcher->matches($output));

        $parsedFile->expectedHeaders["X-Powered-By"] = "PHP";
        $headersMatcher = new HeadersMatcher($parsedFile);
        $this->assertFalse($headersMatcher->matches($output));

        unset($parsedFile->expectedHeaders["X-Powered-By"]);
        $parsedFile->expectedHeaders["Content-type"] = "text/html; charset=UTF-8";
        $headersMatcher = new HeadersMatcher($parsedFile);
        $this->assertFalse($headersMatcher->matches($output));
    }
}
