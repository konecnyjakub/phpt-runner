<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

use Konecnyjakub\EventDispatcher\DebugEventDispatcher;
use Konecnyjakub\EventDispatcher\DummyEventDispatcher;
use Konecnyjakub\PHPTRunner\Events\TestFailed;
use Konecnyjakub\PHPTRunner\Events\TestFinished;
use Konecnyjakub\PHPTRunner\Events\TestPassed;
use Konecnyjakub\PHPTRunner\Events\TestSkipped;
use Konecnyjakub\PHPTRunner\Events\TestStarted;
use MyTester\Attributes\Data;
use MyTester\Attributes\TestSuite;
use MyTester\TestCase;
use Psr\Log\NullLogger;

#[TestSuite("PHPT file runner")]
final class PhptRunnerTest extends TestCase
{
    #[Data([PHP_BINARY,])]
    #[Data(["php-cgi",])]
    public function testRunFile(string $phpBinary): void
    {
        $isCgi = $phpBinary === "php-cgi";
        if ($isCgi && PHP_OS_FAMILY === "Windows") {
            $phpBinary = "C:\\tools\\php\\php-cgi.exe";
        }

        $defaultIniSettings = $isCgi ? ["opcache.enable" => 0, "expose_php" => 0,] : [];
        $outputHeaders = $isCgi ? ["content-type" => "text/html; charset=UTF-8",] : [];
        $runner = new PhptRunner(new Parser(), new PhpRunner($phpBinary, $defaultIniSettings));

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "skipped_test.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Skipped test", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Skipped, $result->outcome);
        $this->assertSame("Skipped", $result->output);
        $this->assertSame("", $result->expectedOutput);
        $this->assertSame([], $result->outputHeaders);
        $this->assertSame([], $result->expectedHeaders);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test", $result->testName);
        $this->assertSame("Just a basic test", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("test123", $result->output);
        $this->assertSame("test123", $result->expectedOutput);
        $this->assertSame($outputHeaders, $result->outputHeaders);
        $this->assertSame([], $result->expectedHeaders);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_env.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test env", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("abc", $result->output);
        $this->assertSame("abc", $result->expectedOutput);
        $this->assertSame($outputHeaders, $result->outputHeaders);
        $this->assertSame([], $result->expectedHeaders);

        if (!$isCgi) {
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
            $this->assertSame($outputHeaders, $result->outputHeaders);
            $this->assertSame([], $result->expectedHeaders);
        }

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_ini.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test ini", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("0", $result->output);
        $this->assertSame("0", $result->expectedOutput);
        $this->assertSame($outputHeaders, $result->outputHeaders);
        $this->assertSame([], $result->expectedHeaders);

        if (!$isCgi) {
            $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_input.phpt";
            $result = $runner->runFile($filename);
            $this->assertSame($filename, $result->fileName);
            $this->assertSame("Test input", $result->testName);
            $this->assertSame("", $result->testDescription);
            $this->assertSame(Outcome::Passed, $result->outcome);
            $this->assertSame("first line" . PHP_EOL . "second line", $result->output);
            $this->assertSame("first line" . PHP_EOL . "second line", $result->expectedOutput);
            $this->assertSame($outputHeaders, $result->outputHeaders);
            $this->assertSame([], $result->expectedHeaders);
        }

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_xfail.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Failing test", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("test123", $result->output);
        $this->assertSame("test1234", $result->expectedOutput);
        $this->assertSame($outputHeaders, $result->outputHeaders);
        $this->assertSame([], $result->expectedHeaders);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_flaky.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Flaky test", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertNotSame(Outcome::Skipped, $result->outcome);
        //$this->assertSame("1", $result->output);
        $this->assertSame("1", $result->expectedOutput);
        $this->assertSame($outputHeaders, $result->outputHeaders);
        $this->assertSame([], $result->expectedHeaders);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_conflicts.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Conflicting test", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("test123", $result->output);
        $this->assertSame("test123", $result->expectedOutput);
        $this->assertSame($outputHeaders, $result->outputHeaders);
        $this->assertSame([], $result->expectedHeaders);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_expected_headers.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test headers", $result->testName);
        $this->assertSame("", $result->testDescription);
        if (!$isCgi) {
            $this->assertSame(Outcome::Skipped, $result->outcome);
            $this->assertSame("This test requires the cgi binary.", $result->output);
            $this->assertSame("", $result->expectedOutput);
            $this->assertSame($outputHeaders, $result->outputHeaders);
            $this->assertSame([], $result->expectedHeaders);
        } else {
            $this->assertSame(Outcome::Passed, $result->outcome);
            $this->assertSame("test123", $result->output);
            $this->assertSame("test123", $result->expectedOutput);
            $this->assertSame(
                ["content-type" => "text/plain; charset=UTF-8", "pragma" => "no-cache", ],
                $result->outputHeaders
            );
            $this->assertSame(
                ["Content-type" => "text/plain; charset=UTF-8", "Pragma" => "no-cache", ],
                $result->expectedHeaders
            );
        }

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_clean.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test clean", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("test123", $result->output);
        $this->assertSame("test123", $result->expectedOutput);
        $this->assertFalse(is_file(__DIR__ . DIRECTORY_SEPARATOR . "tmp1.txt"));
        $this->assertSame($outputHeaders, $result->outputHeaders);
        $this->assertSame([], $result->expectedHeaders);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_cgi.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test CGI", $result->testName);
        $this->assertSame("", $result->testDescription);
        if (!$isCgi) {
            $this->assertSame(Outcome::Skipped, $result->outcome);
            $this->assertSame("This test requires the cgi binary.", $result->output);
            $this->assertSame("", $result->expectedOutput);
        } else {
            $this->assertSame(Outcome::Passed, $result->outcome);
            $this->assertSame("test123", $result->output);
            $this->assertSame("test123", $result->expectedOutput);
        }
        $this->assertSame($outputHeaders, $result->outputHeaders);
        $this->assertSame([], $result->expectedHeaders);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_external.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test external", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("test123", $result->output);
        $this->assertSame("test123", $result->expectedOutput);
        $this->assertSame($outputHeaders, $result->outputHeaders);
        $this->assertSame([], $result->expectedHeaders);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_fileeof.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test fileeof", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("test123", $result->output);
        $this->assertSame("test123", $result->expectedOutput);
        $this->assertSame($outputHeaders, $result->outputHeaders);
        $this->assertSame([], $result->expectedHeaders);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_extensions.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test extensions", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Skipped, $result->outcome);
        $this->assertContains("This test requires PHP extension ", $result->output);
        $this->assertSame("", $result->expectedOutput);
        $this->assertSame([], $result->outputHeaders);
        $this->assertSame([], $result->expectedHeaders);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_get.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test get", $result->testName);
        $this->assertSame("", $result->testDescription);
        if (!$isCgi) {
            $this->assertSame(Outcome::Skipped, $result->outcome);
            $this->assertSame("This test requires the cgi binary.", $result->output);
            $this->assertSame("", $result->expectedOutput);
        } else {
            $this->assertSame(Outcome::Failed, $result->outcome); // FIXME: implement section GET
            $this->assertContains("PHP Warning:  Undefined array key \"two\"", $result->output);
            $this->assertSame("ghi", $result->expectedOutput);
        }
        $this->assertSame([], $result->outputHeaders);
        $this->assertSame([], $result->expectedHeaders);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_cookies.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test cookies", $result->testName);
        $this->assertSame("", $result->testDescription);
        if (!$isCgi) {
            $this->assertSame(Outcome::Skipped, $result->outcome);
            $this->assertSame("This test requires the cgi binary.", $result->output);
            $this->assertSame("", $result->expectedOutput);
        } else {
            $this->assertSame(Outcome::Failed, $result->outcome); // FIXME: implement section COOKIE
            $this->assertContains("PHP Warning:  Undefined array key \"one\"", $result->output);
            $this->assertSame("abc", $result->expectedOutput);
        }
        $this->assertSame([], $result->outputHeaders);
        $this->assertSame([], $result->expectedHeaders);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_regex.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test regex", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("test123", $result->output);
        $this->assertSame("test[0-9]+", $result->expectedOutput);
        $this->assertSame($outputHeaders, $result->outputHeaders);
        $this->assertSame([], $result->expectedHeaders);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_regex_external.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test regex external", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("test123ext", $result->output);
        $this->assertSame("test[0-9]+ext", $result->expectedOutput);
        $this->assertSame($outputHeaders, $result->outputHeaders);
        $this->assertSame([], $result->expectedHeaders);

        if (!$isCgi) {
            $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_capture_stdio.phpt";
            $result = $runner->runFile($filename);
            $this->assertSame($filename, $result->fileName);
            $this->assertSame("Test capture stdio", $result->testName);
            $this->assertSame("", $result->testDescription);
            $this->assertSame(Outcome::Passed, $result->outcome);
            $this->assertSame("test error", $result->output);
            $this->assertSame("test error", $result->expectedOutput);
            $this->assertSame($outputHeaders, $result->outputHeaders);
            $this->assertSame([], $result->expectedHeaders);
        }

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_substitutions.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test substitutions", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("+123 abc test", $result->output);
        $this->assertSame("%i%w%s test", $result->expectedOutput);
        $this->assertSame($outputHeaders, $result->outputHeaders);
        $this->assertSame([], $result->expectedHeaders);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_substitutions_external.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test substitutions external", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("11f test ext", $result->output);
        $this->assertSame("%x%sext", $result->expectedOutput);
        $this->assertSame($outputHeaders, $result->outputHeaders);
        $this->assertSame([], $result->expectedHeaders);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_skip_xfail.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test skip xfail", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Passed, $result->outcome);
        $this->assertSame("one", $result->output);
        $this->assertSame("two", $result->expectedOutput);
        $this->assertSame($outputHeaders, $result->outputHeaders);
        $this->assertSame([], $result->expectedHeaders);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_skip_flaky.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Test skip flaky", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertNotSame(Outcome::Skipped, $result->outcome);
        //$this->assertSame("1", $result->output);
        $this->assertSame("1", $result->expectedOutput);
        $this->assertSame($outputHeaders, $result->outputHeaders);
        $this->assertSame([], $result->expectedHeaders);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "failing_test.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("Failing test", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Failed, $result->outcome);
        $this->assertSame("test123", $result->output);
        $this->assertSame("test1234", $result->expectedOutput);
        $this->assertSame($outputHeaders, $result->outputHeaders);
        $this->assertSame([], $result->expectedHeaders);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_invalid1.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Failed, $result->outcome);
        $this->assertSame("Invalid file: Required section TEST not found in file $filename", $result->output);
        $this->assertSame("", $result->expectedOutput);
        $this->assertSame([], $result->outputHeaders);
        $this->assertSame([], $result->expectedHeaders);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test_invalid2.phpt";
        $result = $runner->runFile($filename);
        $this->assertSame($filename, $result->fileName);
        $this->assertSame("", $result->testName);
        $this->assertSame("", $result->testDescription);
        $this->assertSame(Outcome::Failed, $result->outcome);
        $this->assertSame("Invalid file: At least one of sections EXPECT, EXPECT_EXTERNAL, EXPECTREGEX, EXPECTREGEX_EXTERNAL, EXPECTF, EXPECTF_EXTERNAL is required, none found in file $filename", $result->output);
        $this->assertSame("", $result->expectedOutput);
        $this->assertSame([], $result->outputHeaders);
        $this->assertSame([], $result->expectedHeaders);
    }

    public function testEvents(): void
    {
        $eventDispatcher = new DebugEventDispatcher(new DummyEventDispatcher(), new NullLogger());
        $runner = new PhptRunner(new Parser(), new PhpRunner(), $eventDispatcher);

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "skipped_test.phpt";
        $runner->runFile($filename);
        $this->assertTrue($eventDispatcher->dispatched(TestSkipped::class));
        $this->assertFalse($eventDispatcher->dispatched(TestSkipped::class, 2));
        $this->assertFalse($eventDispatcher->dispatched(TestStarted::class));
        $this->assertFalse($eventDispatcher->dispatched(TestFinished::class));
        $this->assertFalse($eventDispatcher->dispatched(TestPassed::class));
        $this->assertFalse($eventDispatcher->dispatched(TestFailed::class));

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "test.phpt";
        $runner->runFile($filename);
        $this->assertTrue($eventDispatcher->dispatched(TestSkipped::class));
        $this->assertFalse($eventDispatcher->dispatched(TestSkipped::class, 2));
        $this->assertTrue($eventDispatcher->dispatched(TestStarted::class));
        $this->assertFalse($eventDispatcher->dispatched(TestStarted::class, 2));
        $this->assertTrue($eventDispatcher->dispatched(TestFinished::class));
        $this->assertFalse($eventDispatcher->dispatched(TestFinished::class, 2));
        $this->assertTrue($eventDispatcher->dispatched(TestPassed::class));
        $this->assertFalse($eventDispatcher->dispatched(TestPassed::class, 2));
        $this->assertFalse($eventDispatcher->dispatched(TestFailed::class));

        $filename = __DIR__ . DIRECTORY_SEPARATOR . "failing_test.phpt";
        $runner->runFile($filename);
        $this->assertTrue($eventDispatcher->dispatched(TestSkipped::class));
        $this->assertFalse($eventDispatcher->dispatched(TestSkipped::class, 2));
        $this->assertTrue($eventDispatcher->dispatched(TestStarted::class, 2));
        $this->assertFalse($eventDispatcher->dispatched(TestStarted::class, 3));
        $this->assertTrue($eventDispatcher->dispatched(TestFinished::class, 2));
        $this->assertFalse($eventDispatcher->dispatched(TestFinished::class, 3));
        $this->assertTrue($eventDispatcher->dispatched(TestPassed::class));
        $this->assertFalse($eventDispatcher->dispatched(TestPassed::class, 2));
        $this->assertTrue($eventDispatcher->dispatched(TestFailed::class));
        $this->assertFalse($eventDispatcher->dispatched(TestFailed::class, 2));
    }
}
