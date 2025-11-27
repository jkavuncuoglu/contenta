<?php

namespace Database\Seeders;

use App\Domains\Themes\Models\Theme;
use App\Domains\Themes\Services\ThemeServiceContract;
use Illuminate\Database\Seeder;

class ThemeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $themeService = app(ThemeServiceContract::class);

        // Scan for themes in storage/app/themes
        $this->command->info('Scanning for themes...');
        $themes = $themeService->scanThemes();
        $this->command->info('Found '.$themes->count().' theme(s).');

        // Activate the default theme if no theme is active
        $activeTheme = Theme::getActive();

        if (! $activeTheme) {
            $defaultTheme = Theme::where('name', 'default')->first();

            if ($defaultTheme) {
                $defaultTheme->activate();
                $this->command->info('Activated default theme.');
            } else {
                $this->command->warn('Default theme not found!');
            }
        } else {
            $this->command->info('Active theme: '.$activeTheme->display_name);
        }
    }
}
