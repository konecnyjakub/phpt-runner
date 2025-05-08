<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

use MyTester\Attributes\TestSuite;
use MyTester\TestCase;

#[TestSuite("PHPT parser")]
final class ParserTest extends TestCase
{
    public function testParse(): void
    {
        $parser = new Parser();

        $sections = $parser->parse(__DIR__ . DIRECTORY_SEPARATOR . "skipped_test.phpt");
        $this->assertSame([
            Parser::SECTION_TEST => "Skipped test",
            Parser::SECTION_SKIPIF => "<?php echo \"skip\"; ?>",
            Parser::SECTION_ENV => [],
            Parser::SECTION_INI => [],
            Parser::SECTION_ARGS => "",
        ], $sections);

        $sections = $parser->parse(__DIR__ . DIRECTORY_SEPARATOR . "test.phpt");
        $this->assertSame([
            Parser::SECTION_TEST => "Test",
            Parser::SECTION_FILE => "<?php" . PHP_EOL . "echo \"test123\";" . PHP_EOL . "?>",
            Parser::SECTION_EXPECT => "test123",
            Parser::SECTION_ENV => [],
            Parser::SECTION_INI => [],
            Parser::SECTION_ARGS => "",
        ], $sections);

        $sections = $parser->parse(__DIR__ . DIRECTORY_SEPARATOR . "test_env.phpt");
        $this->assertSame([
            Parser::SECTION_TEST => "Test env",
            Parser::SECTION_FILE => "<?php echo getenv(\"one\"); ?>",
            Parser::SECTION_EXPECT => "abc",
            Parser::SECTION_ENV => ["one" => "abc", ],
            Parser::SECTION_INI => [],
            Parser::SECTION_ARGS => "",
        ], $sections);

        $sections = $parser->parse(__DIR__ . DIRECTORY_SEPARATOR . "test_args.phpt");
        $this->assertSame([
            Parser::SECTION_TEST => "Test args",
            Parser::SECTION_ARGS => "--one=abc --two def",
            Parser::SECTION_FILE => "<?php var_dump(\$argv[1] === \"--one=abc\" && \$argv[2] === \"--two\" && \$argv[3] === \"def\"); ?>",
            Parser::SECTION_EXPECT => "bool(true)",
            Parser::SECTION_ENV => [],
            Parser::SECTION_INI => [],
        ], $sections);

        $sections = $parser->parse(__DIR__ . DIRECTORY_SEPARATOR . "test_ini.phpt");
        $this->assertSame([
            Parser::SECTION_TEST => "Test ini",
            Parser::SECTION_FILE => "<?php echo ini_get(\"allow_url_fopen\"); ?>",
            Parser::SECTION_EXPECT => "0",
            Parser::SECTION_ENV => [],
            Parser::SECTION_INI => ["allow_url_fopen" => "0",],
            Parser::SECTION_ARGS => "",
        ], $sections);

        $sections = $parser->parse(__DIR__ . DIRECTORY_SEPARATOR . "test_input.phpt");
        $this->assertSame([
            Parser::SECTION_TEST => "Test input",
            Parser::SECTION_FILE => "<?php echo stream_get_contents(STDIN); ?>",
            Parser::SECTION_EXPECT => "first line" . PHP_EOL . "second line",
            Parser::SECTION_ENV => [],
            Parser::SECTION_INI => [],
            Parser::SECTION_ARGS => "",
            Parser::SECTION_STDIN => "first line" . PHP_EOL . "second line",
        ], $sections);

        $this->assertSame([], $parser->parse(__DIR__ . DIRECTORY_SEPARATOR . "non-existing.phpt"));
    }
}
