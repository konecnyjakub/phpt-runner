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

        if (PHP_OS_FAMILY !== "Windows") {
            $runner = new PhpRunner("php-cgi");
            $this->assertTrue($runner->isCgiBinary());
        }
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
        if ($phpBinary === "php-cgi" && PHP_OS_FAMILY === "Windows") {
            $this->markTestSkipped("php-cgi binary is not present on Windows");
        }

        $runner = new PhpRunner($phpBinary);
        $parser = new Parser();
        $defaultIniSettings = $phpBinary === "php-cgi" ? ["opcache.enable" => 0,] : [];
        $defaultOutputHeaders = $phpBinary === "php-cgi" ? "Content-type: text/html; charset=UTF-8\r\n\r\n" : "";
        $code = "<?php echo 'abc'; ?>";
        $this->assertSame($defaultOutputHeaders . "abc", $runner->runCode($code, $defaultIniSettings));

        $code = "<?php die('skip'); ?>";
        $this->assertSame($defaultOutputHeaders . "skip", $runner->runCode($code, $defaultIniSettings));

        $code = "<?php fclose(\$abc); ?>";
        $result = $runner->runCode($code, $defaultIniSettings);
        $this->assertContains(
            'PHP Fatal error:  Uncaught TypeError: fclose(): Argument #1 ($stream) must be of type resource, null given',
            $result
        );

        if ($phpBinary !== "php-cgi") {
            $code = "<?php echo \"test123\"; fwrite(STDERR, \"test error\"); ?>";
            $result = $runner->runCode($code, $defaultIniSettings);
            $this->assertSame("test123test error", $result);

            $result = $runner->runCode($code, $defaultIniSettings, captureStdout: false);
            $this->assertSame("test error", $result);

            $result = $runner->runCode($code, $defaultIniSettings, captureStderr: false);
            $this->assertSame("test123", $result);

            $result = $runner->runCode($code, $defaultIniSettings, captureStdout: false, captureStderr: false);
            $this->assertSame("", $result);

            $code = "<?php echo stream_get_contents(STDIN); ?>";
            $result = $runner->runCode($code, $defaultIniSettings, input: "abc", captureStdin: false);
            $this->assertMatchesRegExp(
                "/^PHP Notice:  stream_get_contents\(\): Read of [0-9]+ bytes failed with errno=9 Bad file descriptor/",
                $result
            );

            $parsedFile = $parser->parse(__DIR__ . DIRECTORY_SEPARATOR . "test_args.phpt");
            $result = $runner->runCode(
                $parsedFile->testCode,
                $defaultIniSettings,
                arguments: $parsedFile->arguments
            );
            $this->assertSame("bool(true)", $result);
        }

        $parsedFile = $parser->parse(__DIR__ . DIRECTORY_SEPARATOR . "test_env.phpt");
        $result = $runner->runCode(
            $parsedFile->testCode,
            $defaultIniSettings,
            env: $parsedFile->envVariables
        );
        $this->assertSame($defaultOutputHeaders . "abc", $result);

        if ($phpBinary !== "php-cgi") {
            $parsedFile = $parser->parse(__DIR__ . DIRECTORY_SEPARATOR . "test_input.phpt");
            $result = $runner->runCode(
                $parsedFile->testCode,
                $defaultIniSettings,
                input: $parsedFile->input
            );
            $this->assertSame($defaultOutputHeaders . "first line" . PHP_EOL . "second line", $result);
        }

        $parsedFile = $parser->parse(__DIR__ . DIRECTORY_SEPARATOR . "test_ini.phpt");
        $result = $runner->runCode(
            $parsedFile->testCode,
            iniSettings: $defaultIniSettings + $parsedFile->iniSettings
        );
        $this->assertSame($defaultOutputHeaders . "0", $result);
    }
}
