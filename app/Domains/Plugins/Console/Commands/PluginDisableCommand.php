<?php

namespace App\Domains\Plugins\Console\Commands;

use App\Domains\Plugins\Services\PluginService;
use Illuminate\Console\Command;

class PluginDisableCommand extends Command
{
    protected $signature = 'plugin:disable {slug : The plugin slug to disable}';

    protected $description = 'Disable a plugin';

    public function handle(PluginService $pluginService): int
    {
        $slug = $this->argument('slug');

        try {
            $pluginService->disable($slug);
            $this->info("Plugin '{$slug}' disabled successfully");

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error("Failed to disable plugin: {$e->getMessage()}");

            return self::FAILURE;
        }
    }
}
