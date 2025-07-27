<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner\Events;

use Konecnyjakub\PHPTRunner\FileResultSet;

final readonly class TestSkipped
{
    public function __construct(public FileResultSet $fileResultSet)
    {
    }
}
