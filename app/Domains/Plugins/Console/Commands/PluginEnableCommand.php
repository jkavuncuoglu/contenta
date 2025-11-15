<?php

namespace App\Domains\Plugins\Console\Commands;

use App\Domains\Plugins\Services\PluginService;
use Illuminate\Console\Command;

class PluginEnableCommand extends Command
{
    protected $signature = 'plugin:enable {slug : The plugin slug to enable}';

    protected $description = 'Enable a plugin';

    public function handle(PluginService $pluginService): int
    {
        $slug = $this->argument('slug');

        try {
            $pluginService->enable($slug);
            $this->info("Plugin '{$slug}' enabled successfully");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to enable plugin: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
