<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner\Events;

use Konecnyjakub\PHPTRunner\FileResultSet;

final readonly class TestFinished
{
    public function __construct(public FileResultSet $fileResultSet)
    {
    }
}
