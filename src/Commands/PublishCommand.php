<?php

namespace EightyNine\Approvals\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishCommand extends Command
{
    protected $signature = 'approvals:publish 
                            {--config : Publish the configuration file}
                            {--views : Publish the view files for customization}
                            {--resources : Publish Filament resources for customization}
                            {--components : Publish form and table components}
                            {--translations : Publish translation files}
                            {--stubs : Publish stub files}
                            {--all : Publish all customizable files}';

    protected $description = 'Publish Filament Approvals assets for customization';

    public function handle(): int
    {
        $this->info('Publishing Filament Approvals assets...');

        if ($this->option('all')) {
            $this->publishAll();
            return self::SUCCESS;
        }

        if ($this->option('config')) {
            $this->publishConfig();
        }

        if ($this->option('views')) {
            $this->publishViews();
        }

        if ($this->option('resources')) {
            $this->publishResources();
        }

        if ($this->option('components')) {
            $this->publishComponents();
        }

        if ($this->option('translations')) {
            $this->publishTranslations();
        }

        if ($this->option('stubs')) {
            $this->publishStubs();
        }

        // If no options provided, show interactive menu
        if (!$this->hasAnyOption()) {
            $this->showInteractiveMenu();
        }

        $this->newLine();
        $this->info('✅ Publishing completed successfully!');

        return self::SUCCESS;
    }

    protected function publishAll(): void
    {
        $this->info('📦 Publishing all customizable files...');
        
        $this->publishConfig();
        $this->publishViews();
        $this->publishResources();
        $this->publishComponents();
        $this->publishTranslations();
        $this->publishStubs();
    }

    protected function publishConfig(): void
    {
        $this->call('vendor:publish', [
            '--tag' => 'filament-approvals-config',
            '--force' => true,
        ]);
        $this->line('📄 Config file published');
    }

    protected function publishViews(): void
    {
        $this->call('vendor:publish', [
            '--tag' => 'filament-approvals-views',
            '--force' => true,
        ]);
        $this->line('👀 View files published to resources/views/vendor/filament-approvals/');
    }

    protected function publishResources(): void
    {
        $this->call('vendor:publish', [
            '--tag' => 'filament-approvals-resources',
            '--force' => true,
        ]);
        $this->line('🎯 Filament resources published to app/Filament/Resources/');
    }

    protected function publishComponents(): void
    {
        $this->call('vendor:publish', [
            '--tag' => 'filament-approvals-components',
            '--force' => true,
        ]);
        $this->line('🧩 Form and table components published to app/Forms/Approvals/ and app/Tables/Approvals/');
    }

    protected function publishTranslations(): void
    {
        $this->call('vendor:publish', [
            '--tag' => 'filament-approvals-translations',
            '--force' => true,
        ]);
        $this->line('🌐 Translation files published to resources/lang/vendor/filament-approvals/');
    }

    protected function publishStubs(): void
    {
        $this->call('vendor:publish', [
            '--tag' => 'filament-approvals-stubs',
            '--force' => true,
        ]);
        $this->line('📝 Stub files published to stubs/filament-approvals/');
    }

    protected function hasAnyOption(): bool
    {
        return $this->option('config') || 
               $this->option('views') || 
               $this->option('resources') || 
               $this->option('components') || 
               $this->option('translations') || 
               $this->option('stubs') || 
               $this->option('all');
    }

    protected function showInteractiveMenu(): void
    {
        $this->info('🎯 What would you like to publish?');
        
        $choices = [
            'config' => 'Configuration file (config/approvals.php)',
            'views' => 'View files for UI customization',
            'resources' => 'Filament resources (ApprovalFlowResource, etc.)',
            'components' => 'Form and table components',
            'translations' => 'Translation files',
            'stubs' => 'Stub files for development',
            'all' => 'All of the above',
        ];

        $selected = $this->choice(
            'Select what to publish:',
            array_values($choices),
            'all'
        );

        $key = array_search($selected, $choices);

        switch ($key) {
            case 'config':
                $this->publishConfig();
                break;
            case 'views':
                $this->publishViews();
                break;
            case 'resources':
                $this->publishResources();
                break;
            case 'components':
                $this->publishComponents();
                break;
            case 'translations':
                $this->publishTranslations();
                break;
            case 'stubs':
                $this->publishStubs();
                break;
            case 'all':
                $this->publishAll();
                break;
        }
    }
}
