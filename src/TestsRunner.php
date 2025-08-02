<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

final readonly class TestsRunner
{
    public function __construct(private PhptRunner $runner)
    {
    }

    /**
     * @return FileResultSet[]
     */
    public function run(string $directory): array
    {
        $results = [];
        $files = glob(realpath($directory) . DIRECTORY_SEPARATOR . "*.phpt") ?: [];
        foreach ($files as $file) {
            $results[] = $this->runner->runFile($file);
        }
        return $results;
    }
}
