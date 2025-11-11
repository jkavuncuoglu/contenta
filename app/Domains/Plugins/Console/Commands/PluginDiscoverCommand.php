<?php

namespace App\Domains\Plugins\Console\Commands;

use App\Domains\Plugins\Services\PluginService;
use Illuminate\Console\Command;

class PluginDiscoverCommand extends Command
{
    protected $signature = 'plugin:discover';

    protected $description = 'Discover and register plugins from the storage directory';

    public function handle(PluginService $pluginService): int
    {
        $this->info('Discovering plugins...');
        $discovered = $pluginService->discoverPlugins();

        if (empty($discovered)) {
            $this->info('No new plugins found');
            return self::SUCCESS;
        }

        foreach ($discovered as $result) {
            if ($result['status'] === 'registered') {
                $this->info("✓ Registered plugin: {$result['slug']}");
            } elseif ($result['status'] === 'skipped') {
                $this->warn("⊘ Skipped plugin: {$result['folder_name']} - {$result['reason']}");
            } else {
                $this->error("✗ Rejected plugin: {$result['slug']} - {$result['reason']}");
            }
        }

        return self::SUCCESS;
    }
}
