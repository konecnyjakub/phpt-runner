<?php
declare(strict_types=1);

namespace Konecnyjakub\PHPTRunner;

/**
 * PHP code runner
 */
final readonly class PhpRunner
{
    /**
     * @param array<string, string|int|float> $defaultIniSettings
     */
    public function __construct(private string $phpBinary = PHP_BINARY, private array $defaultIniSettings = [])
    {
    }

    public function isCgiBinary(): bool
    {
        $output = $this->runCode("<?php echo PHP_SAPI; ?>");
        return str_contains($output, "cgi");
    }

    public function isExtensionLoaded(string $extensionName): bool
    {
        $output = $this->runCode("<?php var_dump(extension_loaded(\"$extensionName\")); ?>");
        return $output === "bool(true)";
    }

    /**
     * @param array<string, string|int|float> $iniSettings
     */
    private function createCommandLine(string $filename, array $iniSettings = [], string $arguments = ""): string
    {
        $commandLine = $this->phpBinary;
        foreach ($iniSettings as $key => $value) {
            $commandLine .= " -d $key=$value";
        }
        $commandLine .= " " . $filename;
        if ($arguments !== "") {
            $commandLine .= " " . $arguments;
        }
        return $commandLine;
    }

    /**
     * @return array<int, string[]|int[]|resource|false>
     */
    private function createPipesSpec(bool $stdin, bool $stdout, bool $stderr): array
    {
        $pipesSpec = [
            0 => ["pipe", "r"],
            1 => ["pipe", "w"],
            2 => ["redirect", 1],
        ];
        if (!$stdin) {
            $pipesSpec[0] = fopen(PHP_OS_FAMILY === "Windows" ? "NUL" : "/dev/null", "c");
        }
        if (!$stderr) {
            $pipesSpec[2] = fopen(PHP_OS_FAMILY === "Windows" ? "NUL" : "/dev/null", "c");
        }
        if (!$stdout) {
            $pipesSpec[1] = fopen(PHP_OS_FAMILY === "Windows" ? "NUL" : "/dev/null", "c");
            if ($stderr) {
                $pipesSpec[2] = ["pipe", "w"];
            }
        }
        return $pipesSpec;
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
        string $input = "",
        ?string $workingDirectory = null,
        bool $captureStdin = true,
        bool $captureStdout = true,
        bool $captureStderr = true
    ): string {
        $file = tmpfile();
        $filename = stream_get_meta_data($file)['uri'];
        fwrite($file, $code);

        $process = proc_open(
            $this->createCommandLine($filename, array_merge($this->defaultIniSettings, $iniSettings), $arguments),
            $this->createPipesSpec($captureStdin, $captureStdout, $captureStderr),
            $pipes,
            $workingDirectory,
            $env
        );
        if ($process === false) {
            return "";
        }
        /** @var resource[] $pipes */
        if ($captureStdin) {
            if ($input !== "") {
                fwrite($pipes[0], $input);
            }
            fclose($pipes[0]);
        }

        $output = match (true) {
            $captureStdout => (string) stream_get_contents($pipes[1]),
            $captureStderr => (string) stream_get_contents($pipes[2]),
            default => "",
        };
        if ($captureStdout) {
            fclose($pipes[1]);
        }
        if ($captureStderr && !$captureStdout) {
            fclose($pipes[2]);
        }
        proc_close($process);
        $output = (string) preg_replace("/\n$/m", "", $output);
        $output = (string) preg_replace("/\r\n$/m", "", $output);
        return $output;
    }
}
