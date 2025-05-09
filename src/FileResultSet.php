<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

final readonly class FileResultSet
{
    public function __construct(
        public string $fileName,
        public string $testName,
        public Outcome $outcome,
        public string $output,
        public string $expectedOutput
    ) {
    }
}
