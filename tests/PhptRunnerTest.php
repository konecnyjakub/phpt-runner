<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

use MyTester\Attributes\TestSuite;
use MyTester\TestCase;

#[TestSuite("PHPT file runner")]
final class PhptRunnerTest extends TestCase
{
    public function testRunFile(): void
    {
        $runner = new PhptRunner(new Parser(), new PhpRunner());

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "skipped_test.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Skipped test", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Skipped, $result->outcome);
        $this->assertSame("skip", $result->output);
        $this->assertSame("", $result->expectedOutput);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test", $result->testName);
        $this->assertSame("Just a basic test", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("test123", $result->output);
        $this->assertSame("test123", $result->expectedOutput);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_env.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test env", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("abc", $result->output);
        $this->assertSame("abc", $result->expectedOutput);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_args.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test args", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("bool(true)", $result->output);
        $this->assertSame("bool(true)", $result->expectedOutput);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_ini.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test ini", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("0", $result->output);
        $this->assertSame("0", $result->expectedOutput);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_input.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test input", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("first line" . PHP_EOL . "second line", $result->output);
        $this->assertSame("first line" . PHP_EOL . "second line", $result->expectedOutput);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_xfail.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Failing test", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("test123", $result->output);
        $this->assertSame("test1234", $result->expectedOutput);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_flaky.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Flaky test", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertNotSame(Outcome::Skipped, $result->outcome);
        //$this->assertSame("1", $result->output);
        $this->assertSame("1", $result->expectedOutput);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_conflicts.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Conflicting test", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("test123", $result->output);
        $this->assertSame("test123", $result->expectedOutput);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_expected_headers.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test headers", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("test123", $result->output);
        $this->assertSame("test123", $result->expectedOutput);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_clean.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test clean", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("test123", $result->output);
        $this->assertSame("test123", $result->expectedOutput);
        $this->assertFalse(is_file(__DIR__ . DIRECTORY_SEPARATOR . "tmp1.txt"));

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_cgi.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test CGI", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Skipped, $result->outcome);
        $this->assertSame("This test requires the cgi binary.", $result->output);
        $this->assertSame("", $result->expectedOutput);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_external.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test external", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("test123", $result->output);
        $this->assertSame("test123", $result->expectedOutput);
    }
}
