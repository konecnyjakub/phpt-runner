<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

use Konecnyjakub\PHPTRunner\Events\RunnerFinished;
use Konecnyjakub\PHPTRunner\Events\RunnerStarted;
use Psr\EventDispatcher\EventDispatcherInterface;

final readonly class TestsRunner
{
    public function __construct(private PhptRunner $runner, private ?EventDispatcherInterface $eventDispatcher = null)
    {
    }

    public function run(string $directory): TestsResultSet
    {
        $this->eventDispatcher?->dispatch(new RunnerStarted());
        $results = [];
        $files = glob(realpath($directory) . DIRECTORY_SEPARATOR . "*.phpt") ?: [];
        foreach ($files as $file) {
            $results[] = $this->runner->runFile($file);
        }
        $resultsSet = new TestsResultSet($results);
        $this->eventDispatcher?->dispatch(new RunnerFinished($resultsSet));
        return $resultsSet;
    }
}
