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

        $result = $testsRunner->run("/non-existing");
        $this->assertSame([], $result->results);
        $this->assertSame(0, $result->testsTotal);

        $result = $testsRunner->run(__DIR__);
        $this->assertCount(28, $result->results);
        $this->assertSame(28, $result->testsTotal);
    }
}
