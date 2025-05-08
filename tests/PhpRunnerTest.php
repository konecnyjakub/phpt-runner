<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

use MyTester\Attributes\TestSuite;
use MyTester\TestCase;

#[TestSuite("PHP code runner")]
final class PhpRunnerTest extends TestCase
{
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

        $parser = new Parser();
        $sections = $parser->parse(__DIR__ . DIRECTORY_SEPARATOR . "test_args.phpt");
        $result = $runner->runCode(
            $sections[Parser::SECTION_FILE], // @phpstan-ignore argument.type
            arguments: $sections[Parser::SECTION_ARGS] // @phpstan-ignore argument.type
        );
        $this->assertSame("bool(true)\n", $result);

        $sections = $parser->parse(__DIR__ . DIRECTORY_SEPARATOR . "test_env.phpt");
        $result = $runner->runCode(
            $sections[Parser::SECTION_FILE], // @phpstan-ignore argument.type
            env: $sections[Parser::SECTION_ENV] // @phpstan-ignore argument.type
        );
        $this->assertSame("abc", $result);

        $sections = $parser->parse(__DIR__ . DIRECTORY_SEPARATOR . "test_input.phpt");
        $result = $runner->runCode(
            $sections[Parser::SECTION_FILE], // @phpstan-ignore argument.type
            input: $sections[Parser::SECTION_STDIN] // @phpstan-ignore argument.type
        );
        $this->assertSame("first line" . PHP_EOL . "second line", $result);
    }
}
