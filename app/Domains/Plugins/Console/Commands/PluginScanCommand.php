<?php

namespace App\Domains\Plugins\Console\Commands;

use App\Domains\Plugins\Services\PluginService;
use Illuminate\Console\Command;

class PluginScanCommand extends Command
{
    protected $signature = 'plugin:scan {slug? : The plugin slug to scan}';

    protected $description = 'Scan plugin(s) for security issues';

    public function handle(PluginService $pluginService): int
    {
        $slug = $this->argument('slug');

        if ($slug) {
            $this->info("Scanning plugin: {$slug}");
            $results = $pluginService->scanPlugin($slug);

            $this->displayScanResults($slug, $results);
        } else {
            $this->info('Scanning all plugins...');
            $results = $pluginService->scanAllPlugins();

            foreach ($results as $slug => $result) {
                $this->displayScanResults($slug, $result);
                $this->newLine();
            }
        }

        return self::SUCCESS;
    }

    /**
     * @param  array<string, mixed>  $results
     */
    private function displayScanResults(string $slug, array $results): void
    {
        $this->line("Plugin: {$slug}");
        $this->line("Scanned files: {$results['scanned_files']}");

        if ($results['safe']) {
            $this->info('✓ No security threats detected');
        } else {
            $this->error('✗ Security threats detected!');
        }

        if (! empty($results['threats'])) {
            $this->error("\nThreats found:");
            foreach ($results['threats'] as $threat) {
                $this->line("  - {$threat['file']}:{$threat['line']} - {$threat['issue']}");
                $this->line("    Code: {$threat['code']}");
            }
        }

        if (! empty($results['warnings'])) {
            $this->warn("\nWarnings:");
            foreach ($results['warnings'] as $warning) {
                $this->line("  - {$warning['file']}:{$warning['line']} - {$warning['issue']}");
            }
        }
    }
}
