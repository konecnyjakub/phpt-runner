<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

/**
 * PHP code runner
 */
final readonly class PhpRunner
{
    public function __construct(private string $phpBinary = PHP_BINARY)
    {
    }

    public function isCgiBinary(): bool
    {
        $output = $this->runCode("<?php echo PHP_SAPI; ?>");
        return str_contains($output, "cgi");
    }

    /**
     * @param array<string, string|int|float> $iniSettings
     * @param array<string, string|int|float> $env
     */
    public function runCode(
        string $code,
        array $iniSettings = [],
        array $env = [],
        string $arguments = "",
        string $input = ""
    ): string {
        $file = tmpfile();
        $filename = stream_get_meta_data($file)['uri'];
        fwrite($file, $code);

        $commandLine = $this->phpBinary;
        foreach ($iniSettings as $key => $value) {
            $commandLine .= " -d $key=$value";
        }
        $commandLine .= " " . $filename;
        if ($arguments !== "") {
            $commandLine .= " " . $arguments;
        }
        $pipesSpec = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["redirect", 1],
        ];
        $process = proc_open($commandLine, $pipesSpec, $pipes, null, $env);
        if ($process === false) {
            return "";
        }
        /** @var resource[] $pipes */
        if ($input !== "") {
            fwrite($pipes[0], $input);
        }
        fclose($pipes[0]);

        $output = (string) stream_get_contents($pipes[1]);
        fclose($pipes[1]);
        proc_close($process);
        return $output;
    }
}
