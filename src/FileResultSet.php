<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

final readonly class FileResultSet
{
    /**
     * @param array<string, string> $outputHeaders
     * @param array<string, string> $expectedHeaders
     */
    public function __construct(
        public string $fileName,
        public string $testName,
        public string $testDescription,
        public Outcome $outcome,
        public string $output = "",
        public string $expectedOutput = "",
        public array $outputHeaders = [],
        public array $expectedHeaders = []
    ) {
    }
}
