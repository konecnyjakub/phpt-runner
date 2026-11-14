<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner\Events;

use Konecnyjakub\PHPTRunner\TestsResultSet;

final readonly class RunnerFinished
{
    public function __construct(public TestsResultSet $results)
    {
    }
}
