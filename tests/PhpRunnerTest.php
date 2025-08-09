<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

use MyTester\Attributes\Data;
use MyTester\Attributes\TestSuite;
use MyTester\TestCase;

#[TestSuite("PHP code runner")]
final class PhpRunnerTest extends TestCase
{
    public function testIsCgiBinary(): void
    {
        $runner = new PhpRunner();
        $this->assertFalse($runner->isCgiBinary());

        $cgiBinary = PHP_OS_FAMILY !== "Windows" ? "php-cgi" : "C:\\tools\\php\\php-cgi.exe";
        $runner = new PhpRunner($cgiBinary);
        $this->assertTrue($runner->isCgiBinary());
    }

    public function testIsExtensionLoaded(): void
    {
        $runner = new PhpRunner();
        $this->assertTrue($runner->isExtensionLoaded("json"));
        $this->assertFalse($runner->isExtensionLoaded("abc"));
    }

    #[Data([PHP_BINARY,])]
    #[Data(["php-cgi",])]
    public function testRunCode(string $phpBinary): void
    {
        $isCgi = $phpBinary === "php-cgi";
        if ($isCgi && PHP_OS_FAMILY === "Windows") {
            $phpBinary = "C:\\tools\\php\\php-cgi.exe";
        }

        $defaultIniSettings = $isCgi ? ["opcache.enable" => 0, "expose_php" => 0,] : [];
        $runner = new PhpRunner($phpBinary, $defaultIniSettings);
        $parser = new Parser();
        $defaultOutputHeaders = $isCgi ? "Content-type: text/html; charset=UTF-8\r\n\r\n" : "";
        $code = "<?php echo 'abc'; ?>";
        $this->assertSame($defaultOutputHeaders . "abc", $runner->runCode($code));

        $code = "<?php die('skip'); ?>";
        $this->assertSame($defaultOutputHeaders . "skip", $runner->runCode($code,));

        $code = "<?php fclose(\$abc); ?>";
        $result = $runner->runCode($code);
        $this->assertContains(
            'PHP Fatal error:  Uncaught TypeError: fclose(): Argument #1 ($stream) must be of type resource, null given',
            $result
        );

        if (!$isCgi) {
            $code = "<?php echo \"test123\"; fwrite(STDERR, \"test error\"); ?>";
            $result = $runner->runCode($code);
            $this->assertSame("test123test error", $result);

            $result = $runner->runCode($code, captureStdout: false);
            $this->assertSame("test error", $result);

            $result = $runner->runCode($code, captureStderr: false);
            $this->assertSame("test123", $result);

            $result = $runner->runCode($code, captureStdout: false, captureStderr: false);
            $this->assertSame("", $result);

            $code = "<?php echo stream_get_contents(STDIN); ?>";
            $result = $runner->runCode($code, input: "abc", captureStdin: false);
            $this->assertMatchesRegExp(
                "/^PHP Notice:  stream_get_contents\(\): Read of [0-9]+ bytes failed with errno=9 Bad file descriptor/",
                $result
            );

            $parsedFile = $parser->parse(__DIR__ . DIRECTORY_SEPARATOR . "test_args.phpt");
            $result = $runner->runCode(
                $parsedFile->testCode,
                arguments: $parsedFile->arguments
            );
            $this->assertSame("bool(true)", $result);
        }

        $parsedFile = $parser->parse(__DIR__ . DIRECTORY_SEPARATOR . "test_env.phpt");
        $result = $runner->runCode(
            $parsedFile->testCode,
            env: $parsedFile->envVariables
        );
        $this->assertSame($defaultOutputHeaders . "abc", $result);

        if (!$isCgi) {
            $parsedFile = $parser->parse(__DIR__ . DIRECTORY_SEPARATOR . "test_input.phpt");
            $result = $runner->runCode(
                $parsedFile->testCode,
                input: $parsedFile->input
            );
            $this->assertSame($defaultOutputHeaders . "first line" . PHP_EOL . "second line", $result);
        }

        $parsedFile = $parser->parse(__DIR__ . DIRECTORY_SEPARATOR . "test_ini.phpt");
        $result = $runner->runCode(
            $parsedFile->testCode,
            iniSettings: $parsedFile->iniSettings
        );
        $this->assertSame($defaultOutputHeaders . "0", $result);
    }
}
