<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

enum Outcome
{
    case Passed;
    case Failed;
    case Skipped;
}
