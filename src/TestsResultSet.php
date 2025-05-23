<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

final readonly class TestsResultSet
{
    public int $testsTotal;
    public int $testsPassed;
    public int $testsSkipped;
    public int $testsFailed;

    /**
     * @param FileResultSet[] $results
     */
    public function __construct(public array $results)
    {
        $total = $passed = $skipped = $failed = 0;
        foreach ($this->results as $result) {
            $total++;
            switch ($result->outcome) {
                case Outcome::Passed:
                    $passed++;
                    break;
                case Outcome::Skipped:
                    $skipped++;
                    break;
                case Outcome::Failed:
                    $failed++;
                    break;
            }
        }
        $this->testsTotal = $total;
        $this->testsPassed = $passed;
        $this->testsSkipped = $skipped;
        $this->testsFailed = $failed;
    }
}
