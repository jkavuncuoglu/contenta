<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class MigrateToMarkdownCommand extends Command
{
    protected $signature = 'page:migrate-md
                            {--dry-run : Run without making changes}
                            {--page= : Migrate specific page by ID}
                            {--force : Skip confirmation prompt}';

    protected $description = 'Migrate legacy page builder pages to markdown format';

    public function handle(): int
    {
        // Use Artisan::call to execute the domain command
        return $this->call(\App\Domains\PageBuilder\Console\Commands\MigratePagesToMarkdown::class, [
            '--dry-run' => $this->option('dry-run'),
            '--page' => $this->option('page'),
            '--force' => $this->option('force'),
        ]);
    }
}
