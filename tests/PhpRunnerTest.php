<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

use MyTester\Attributes\TestSuite;
use MyTester\TestCase;

#[TestSuite("PHP code runner")]
final class PhpRunnerTest extends TestCase
{
    public function testIsCgiBinary(): void
    {
        $runner = new PhpRunner();
        $this->assertFalse($runner->isCgiBinary());
    }

    public function testIsExtensionLoaded(): void
    {
        $runner = new PhpRunner();
        $this->assertTrue($runner->isExtensionLoaded("json"));
        $this->assertFalse($runner->isExtensionLoaded("abc"));
    }

    public function testRunCode(): void
    {
        $runner = new PhpRunner();
        $code = "<?php echo 'abc'; ?>";
        $this->assertSame("abc", $runner->runCode($code));

        $code = "<?php die('skip'); ?>";
        $this->assertSame("skip", $runner->runCode($code));

        $code = "<?php fclose(\$abc); ?>";
        $result = $runner->runCode($code);
        $this->assertContains(
            'PHP Fatal error:  Uncaught TypeError: fclose(): Argument #1 ($stream) must be of type resource, null given',
            $result
        );

        $code = "<?php echo \"test123\"; fwrite(STDERR, \"test error\"); ?>";
        $result = $runner->runCode($code);
        $this->assertSame("test123test error", $result);

        $result = $runner->runCode($code, captureStdout: false);
        $this->assertSame("test error", $result);

        $result = $runner->runCode($code, captureStderr: false);
        $this->assertSame("test123", $result);

        $result = $runner->runCode($code, captureStdout: false, captureStderr: false);
        $this->assertSame("", $result);

        $parser = new Parser();
        $parsedFile = $parser->parse(__DIR__ . DIRECTORY_SEPARATOR . "test_args.phpt");
        $result = $runner->runCode(
            $parsedFile->testCode,
            arguments: $parsedFile->arguments
        );
        $this->assertSame("bool(true)", $result);

        $parsedFile = $parser->parse(__DIR__ . DIRECTORY_SEPARATOR . "test_env.phpt");
        $result = $runner->runCode(
            $parsedFile->testCode,
            env: $parsedFile->envVariables
        );
        $this->assertSame("abc", $result);

        $parsedFile = $parser->parse(__DIR__ . DIRECTORY_SEPARATOR . "test_input.phpt");
        $result = $runner->runCode(
            $parsedFile->testCode,
            input: $parsedFile->input
        );
        $this->assertSame("first line" . PHP_EOL . "second line", $result);

        $parsedFile = $parser->parse(__DIR__ . DIRECTORY_SEPARATOR . "test_ini.phpt");
        $result = $runner->runCode(
            $parsedFile->testCode,
            iniSettings: $parsedFile->iniSettings
        );
        $this->assertSame("0", $result);
    }
}
