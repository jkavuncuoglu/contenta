<?php

namespace App\Domains\Plugins\Tests\Unit;

use App\Domains\Plugins\Services\PluginSecurityScanner;
use PHPUnit\Framework\TestCase;

class PluginSecurityScannerTest extends TestCase
{
    private PluginSecurityScanner $scanner;
    private string $testPluginDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->scanner = new PluginSecurityScanner();
        $this->testPluginDir = sys_get_temp_dir() . '/test-plugins-' . uniqid();
        mkdir($this->testPluginDir, 0755, true);
    }

    protected function tearDown(): void
    {
        if (is_dir($this->testPluginDir)) {
            $this->deleteDirectory($this->testPluginDir);
        }
        parent::tearDown();
    }

    private function deleteDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }
        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . '/' . $file;
            is_dir($path) ? $this->deleteDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }

    public function test_detects_safe_plugin(): void
    {
        // Create a safe plugin
        $safeCode = <<<'PHP'
<?php
// Safe plugin code
echo "Hello World";
$data = ['key' => 'value'];
return $data;
PHP;

        file_put_contents($this->testPluginDir . '/safe.php', $safeCode);

        $result = $this->scanner->scan($this->testPluginDir);

        $this->assertTrue($result['safe']);
        $this->assertEmpty($result['threats']);
        $this->assertEquals(1, $result['scanned_files']);
        $this->assertArrayHasKey('scanned_at', $result);
    }

    public function test_detects_eval_vulnerability(): void
    {
        $maliciousCode = <<<'PHP'
<?php
eval($_GET['code']);
PHP;

        file_put_contents($this->testPluginDir . '/malicious.php', $maliciousCode);

        $result = $this->scanner->scan($this->testPluginDir);

        $this->assertFalse($result['safe']);
        $this->assertNotEmpty($result['threats']);
        $this->assertStringContainsString('eval', $result['threats'][0]['issue']);
    }

    public function test_detects_exec_vulnerability(): void
    {
        $maliciousCode = <<<'PHP'
<?php
exec('rm -rf /');
PHP;

        file_put_contents($this->testPluginDir . '/malicious.php', $maliciousCode);

        $result = $this->scanner->scan($this->testPluginDir);

        $this->assertFalse($result['safe']);
        $this->assertNotEmpty($result['threats']);
    }

    public function test_detects_system_command_vulnerability(): void
    {
        $maliciousCode = <<<'PHP'
<?php
system('cat /etc/passwd');
PHP;

        file_put_contents($this->testPluginDir . '/malicious.php', $maliciousCode);

        $result = $this->scanner->scan($this->testPluginDir);

        $this->assertFalse($result['safe']);
        $this->assertNotEmpty($result['threats']);
    }

    public function test_detects_base64_decode_pattern(): void
    {
        $maliciousCode = <<<'PHP'
<?php
$code = base64_decode('ZXZhbCgkX0dFVFsnY21kJ10pOw==');
eval($code);
PHP;

        file_put_contents($this->testPluginDir . '/malicious.php', $maliciousCode);

        $result = $this->scanner->scan($this->testPluginDir);

        $this->assertFalse($result['safe']);
        $this->assertNotEmpty($result['threats']);
    }

    public function test_detects_file_put_contents_vulnerability(): void
    {
        $maliciousCode = <<<'PHP'
<?php
file_put_contents('/tmp/backdoor.php', '<?php eval($_GET["x"]); ?>');
PHP;

        file_put_contents($this->testPluginDir . '/malicious.php', $maliciousCode);

        $result = $this->scanner->scan($this->testPluginDir);

        $this->assertFalse($result['safe']);
        $this->assertNotEmpty($result['threats']);
    }

    public function test_detects_multiple_threats(): void
    {
        $maliciousCode = <<<'PHP'
<?php
eval($_GET['code']);
exec('ls -la');
system('whoami');
file_put_contents('/tmp/test', 'data');
PHP;

        file_put_contents($this->testPluginDir . '/malicious.php', $maliciousCode);

        $result = $this->scanner->scan($this->testPluginDir);

        $this->assertFalse($result['safe']);
        $this->assertGreaterThanOrEqual(4, count($result['threats']));
    }

    public function test_scans_multiple_php_files(): void
    {
        file_put_contents($this->testPluginDir . '/file1.php', '<?php echo "safe";');
        file_put_contents($this->testPluginDir . '/file2.php', '<?php echo "also safe";');
        file_put_contents($this->testPluginDir . '/file3.php', '<?php echo "still safe";');

        $result = $this->scanner->scan($this->testPluginDir);

        $this->assertTrue($result['safe']);
        $this->assertEquals(3, $result['scanned_files']);
    }

    public function test_ignores_non_php_files(): void
    {
        file_put_contents($this->testPluginDir . '/safe.php', '<?php echo "safe";');
        file_put_contents($this->testPluginDir . '/readme.txt', 'This is a readme');
        file_put_contents($this->testPluginDir . '/config.json', '{"key": "value"}');

        $result = $this->scanner->scan($this->testPluginDir);

        $this->assertTrue($result['safe']);
        $this->assertEquals(1, $result['scanned_files']);
    }

    public function test_scan_result_structure(): void
    {
        file_put_contents($this->testPluginDir . '/test.php', '<?php echo "test";');

        $result = $this->scanner->scan($this->testPluginDir);

        $this->assertIsArray($result);
        $this->assertArrayHasKey('safe', $result);
        $this->assertArrayHasKey('threats', $result);
        $this->assertArrayHasKey('warnings', $result);
        $this->assertArrayHasKey('scanned_files', $result);
        $this->assertArrayHasKey('scanned_at', $result);
        $this->assertIsBool($result['safe']);
        $this->assertIsArray($result['threats']);
        $this->assertIsArray($result['warnings']);
        $this->assertIsInt($result['scanned_files']);
        $this->assertIsString($result['scanned_at']);
    }
}
