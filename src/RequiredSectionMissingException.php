<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

use Throwable;

class RequiredSectionMissingException extends ParseErrorException
{
    /**
     * @param string|string[] $section
     */
    public function __construct(array|string $section, string $filename, int $code = 0, ?Throwable $previous = null)
    {
        if (is_string($section)) {
            $message = "Required section $section not found in file $filename";
        } else {
            $message = "At least one of sections " . join(", ", $section) . " is required, none found in file $filename";
        }
        parent::__construct($message, $code, $previous);
    }
}
