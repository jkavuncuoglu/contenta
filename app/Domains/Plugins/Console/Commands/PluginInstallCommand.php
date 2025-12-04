<?php

namespace App\Domains\Plugins\Console\Commands;

use App\Domains\Plugins\Services\PluginService;
use Illuminate\Console\Command;

class PluginInstallCommand extends Command
{
    protected $signature = 'plugin:install {zip-path : Path to the plugin zip file}';

    protected $description = 'Install a plugin from a zip file';

    public function handle(PluginService $pluginService): int
    {
        $zipPath = $this->argument('zip-path');

        if (! file_exists($zipPath)) {
            $this->error("File not found: {$zipPath}");

            return self::FAILURE;
        }

        $this->info('Installing plugin...');
        $result = $pluginService->installFromZip($zipPath);

        if ($result['success']) {
            $this->info($result['message']);

            if (isset($result['scan_results'])) {
                $this->newLine();
                $this->line('Security scan results:');
                $this->line("Scanned files: {$result['scan_results']['scanned_files']}");

                if (! empty($result['scan_results']['warnings'])) {
                    $this->warn('Warnings detected: '.count($result['scan_results']['warnings']));
                }
            }

            return self::SUCCESS;
        } else {
            $this->error($result['message']);

            if (isset($result['scan_results']['threats'])) {
                $this->newLine();
                $this->error('Security threats detected:');
                foreach ($result['scan_results']['threats'] as $threat) {
                    $this->line("  - {$threat['file']}:{$threat['line']} - {$threat['issue']}");
                }
            }

            return self::FAILURE;
        }
    }
}
