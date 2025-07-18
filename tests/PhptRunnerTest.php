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
        $this->assertSame("Skipped", $result->output);
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
        if (PHP_OS_FAMILY !== "Windows") { // FIXME: This should work on Windows too
            $this->assertSame(Outcome::Passed, $result->outcome);
            $this->assertSame("bool(true)", $result->output);
        }
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

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_fileeof.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test fileeof", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("test123", $result->output);
        $this->assertSame("test123", $result->expectedOutput);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_extensions.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test extensions", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Skipped, $result->outcome);
        $this->assertSame("This test requires PHP extension abc.", $result->output);
        $this->assertSame("", $result->expectedOutput);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_get.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test get", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Skipped, $result->outcome);
        $this->assertSame("This test requires the cgi binary.", $result->output);
        $this->assertSame("", $result->expectedOutput);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_cookies.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test cookies", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Skipped, $result->outcome);
        $this->assertSame("This test requires the cgi binary.", $result->output);
        $this->assertSame("", $result->expectedOutput);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_regex.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test regex", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("test123", $result->output);
        $this->assertSame("test[0-9]+", $result->expectedOutput);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_regex_external.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test regex external", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("test123ext", $result->output);
        $this->assertSame("test[0-9]+ext", $result->expectedOutput);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_capture_stdio.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test capture stdio", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("test error", $result->output);
        $this->assertSame("test error", $result->expectedOutput);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_substitutions.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test substitutions", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("+123 abc test", $result->output);
        $this->assertSame("%i%w%s test", $result->expectedOutput);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_substitutions_external.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test substitutions external", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("11f test ext", $result->output);
        $this->assertSame("%x%sext", $result->expectedOutput);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_skip_xfail.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test skip xfail", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("one", $result->output);
        $this->assertSame("two", $result->expectedOutput);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_skip_flaky.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test skip flaky", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertNotSame(Outcome::Skipped, $result->outcome);
        //$this->assertSame("1", $result->output);
        $this->assertSame("1", $result->expectedOutput);
    }
}
