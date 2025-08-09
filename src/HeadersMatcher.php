<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

final readonly class HeadersMatcher
{
    public function __construct(private ParsedFile $parsedFile)
    {
    }

    /**
     * @return array<string, string>
     */
    public function getOutputHeaders(string $output): array
    {
        $headers = strstr($output, "\r\n\r\n", true);
        if ($headers === false) {
            return [];
        }

        $result = [];
        foreach (explode(PHP_EOL, $headers) as $line) {
            $value = explode(":", $line, 2);
            if ($value[0] !== "" && isset($value[1])) {
                $result[strtolower($value[0])] = trim($value[1]);
            }
        }
        return $result;
    }

    public function matches(string $actualOutput): bool
    {
        $actualHeaders = $this->getOutputHeaders($actualOutput);
        foreach ($this->parsedFile->expectedHeaders as $headerName => $headerValue) {
            $headerName = strtolower($headerName);
            if (!array_key_exists($headerName, $actualHeaders) || $actualHeaders[$headerName] !== $headerValue) {
                return false;
            }
        }
        return true;
    }
}
