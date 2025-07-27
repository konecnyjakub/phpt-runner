<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner\Events;

use Konecnyjakub\PHPTRunner\ParsedFile;

final readonly class TestStarted
{
    public function __construct(public ParsedFile $parsedFile)
    {
    }
}
