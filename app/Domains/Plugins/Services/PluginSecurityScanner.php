<?php

declare(strict_types=1);

namespace App\Domains\Plugins\Services;

use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RegexIterator;

class PluginSecurityScanner
{
    /**
     * Dangerous PHP functions that should not be used in plugins
     */
    private array $dangerousFunctions = [
        'eval',
        'exec',
        'system',
        'shell_exec',
        'passthru',
        'proc_open',
        'popen',
        'curl_exec',
        'curl_multi_exec',
        'parse_ini_file',
        'show_source',
        'file_put_contents',
        'fwrite',
        'rename',
        'unlink',
        'rmdir',
        'mkdir',
        'chmod',
        'chown',
        'base64_decode',
        'gzinflate',
        'gzuncompress',
        'str_rot13',
    ];

    /**
     * Suspicious patterns that might indicate malicious code
     */
    private array $suspiciousPatterns = [
        '/\\$_(GET|POST|REQUEST|COOKIE|SERVER|FILES)\[/',
        '/<\?php\s+@?\$/',
        '/\$\{.*\}/',
        '/preg_replace.*\/e/',
        '/assert\s*\(/',
        '/create_function\s*\(/',
        '/call_user_func/',
        '/\\\\x[0-9a-f]{2}/i',
        '/file_get_contents\s*\(\s*[\'"]http/',
        '/fsockopen\s*\(/',
        '/socket_create\s*\(/',
    ];

    /**
     * Warning patterns (not necessarily malicious, but suspicious)
     */
    private array $warningPatterns = [
        '/wp_remote_/',  // WordPress functions (if this isn't a WP plugin)
        '/@ini_set/',
        '/error_reporting\s*\(\s*0/',
        '/\$GLOBALS\[/',
    ];

    /**
     * Scan a plugin directory for security issues
     *
     * @return array{
     *   safe: bool,
     *   threats: array<int, array{file: string, line: int, issue: string, code: string}>,
     *   warnings: array<int, array{file: string, line: int, issue: string, code: string}>,
     *   scanned_files: int,
     *   scanned_at: string
     * }
     */
    public function scan(string $pluginPath): array
    {
        $threats = [];
        $warnings = [];
        $scannedFiles = 0;

        if (! is_dir($pluginPath)) {
            return [
                'safe' => false,
                'threats' => [['file' => '', 'line' => 0, 'issue' => 'Plugin directory not found', 'code' => '']],
                'warnings' => [],
                'scanned_files' => 0,
                'scanned_at' => now()->toISOString(),
            ];
        }

        // Scan all PHP files in the plugin directory
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($pluginPath)
        );

        $phpFiles = new RegexIterator($iterator, '/^.+\.php$/i', RegexIterator::GET_MATCH);

        foreach ($phpFiles as $file) {
            $filePath = $file[0];
            $scannedFiles++;

            $content = file_get_contents($filePath);
            if ($content === false) {
                continue;
            }

            $lines = explode("\n", $content);

            // Check for dangerous functions
            foreach ($this->dangerousFunctions as $function) {
                if (preg_match('/\b'.preg_quote($function, '/').'\s*\(/i', $content, $matches, PREG_OFFSET_CAPTURE)) {
                    $lineNumber = $this->getLineNumber($content, $matches[0][1]);
                    $threats[] = [
                        'file' => $this->getRelativePath($pluginPath, $filePath),
                        'line' => $lineNumber,
                        'issue' => "Dangerous function '{$function}' detected",
                        'code' => trim($lines[$lineNumber - 1] ?? ''),
                    ];
                }
            }

            // Check for suspicious patterns
            foreach ($this->suspiciousPatterns as $pattern) {
                if (preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                    $lineNumber = $this->getLineNumber($content, $matches[0][1]);
                    $threats[] = [
                        'file' => $this->getRelativePath($pluginPath, $filePath),
                        'line' => $lineNumber,
                        'issue' => 'Suspicious pattern detected',
                        'code' => trim($lines[$lineNumber - 1] ?? ''),
                    ];
                }
            }

            // Check for warning patterns
            foreach ($this->warningPatterns as $pattern) {
                if (preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                    $lineNumber = $this->getLineNumber($content, $matches[0][1]);
                    $warnings[] = [
                        'file' => $this->getRelativePath($pluginPath, $filePath),
                        'line' => $lineNumber,
                        'issue' => 'Potentially problematic code detected',
                        'code' => trim($lines[$lineNumber - 1] ?? ''),
                    ];
                }
            }
        }

        return [
            'safe' => empty($threats),
            'threats' => $threats,
            'warnings' => $warnings,
            'scanned_files' => $scannedFiles,
            'scanned_at' => now()->toISOString(),
        ];
    }

    /**
     * Get line number from string offset
     */
    private function getLineNumber(string $content, int $offset): int
    {
        return substr_count(substr($content, 0, $offset), "\n") + 1;
    }

    /**
     * Get relative path from plugin directory
     */
    private function getRelativePath(string $basePath, string $fullPath): string
    {
        return str_replace($basePath.'/', '', $fullPath);
    }
}
