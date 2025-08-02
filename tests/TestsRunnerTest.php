<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

use MyTester\Attributes\TestSuite;
use MyTester\TestCase;

#[TestSuite("Tests runner")]
final class TestsRunnerTest extends TestCase
{
    public function testRun(): void
    {
        $testsRunner = new TestsRunner(new PhptRunner(new Parser(), new PhpRunner()));

        $this->assertSame([], $testsRunner->run("/non-existing"));

        $result = $testsRunner->run(__DIR__);
        $this->assertArrayOfType(FileResultSet::class, $result);
        $this->assertCount(27, $result);
    }
}
