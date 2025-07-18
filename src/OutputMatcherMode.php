<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

enum OutputMatcherMode
{
    case Literal;
    case Regex;
    case Substitution;
}
