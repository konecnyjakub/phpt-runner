<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

use Konecnyjakub\EventDispatcher\DebugEventDispatcher;
use Konecnyjakub\EventDispatcher\DummyEventDispatcher;
use Konecnyjakub\PHPTRunner\Events\RunnerFinished;
use Konecnyjakub\PHPTRunner\Events\RunnerStarted;
use MyTester\Attributes\TestSuite;
use MyTester\TestCase;
use Psr\Log\NullLogger;

#[TestSuite("Tests runner")]
final class TestsRunnerTest extends TestCase
{
    public function testRun(): void
    {
        $eventDispatcher = new DebugEventDispatcher(new DummyEventDispatcher(), new NullLogger());
        $testsRunner = new TestsRunner(new PhptRunner(new Parser(), new PhpRunner()), $eventDispatcher);

        $result = $testsRunner->run("/non-existing");
        $this->assertSame([], $result->results);
        $this->assertSame(0, $result->testsTotal);
        $this->assertTrue($eventDispatcher->dispatched(RunnerStarted::class));
        $this->assertFalse($eventDispatcher->dispatched(RunnerStarted::class, 2));
        $this->assertTrue($eventDispatcher->dispatched(RunnerFinished::class));
        $this->assertFalse($eventDispatcher->dispatched(RunnerFinished::class, 2));

        $result = $testsRunner->run(__DIR__);
        $this->assertCount(27, $result->results);
        $this->assertSame(27, $result->testsTotal);
        $this->assertTrue($eventDispatcher->dispatched(RunnerStarted::class, 2));
        $this->assertFalse($eventDispatcher->dispatched(RunnerStarted::class, 3));
        $this->assertTrue($eventDispatcher->dispatched(RunnerFinished::class, 2));
        $this->assertFalse($eventDispatcher->dispatched(RunnerFinished::class, 3));
    }
}
