<?php

namespace EightyNine\Approvals;

use Filament\Support\Assets\AlpineComponent;
use Filament\Support\Assets\Asset;
use Filament\Support\Assets\Css;
use Filament\Support\Assets\Js;
use Filament\Support\Facades\FilamentAsset;
use Filament\Support\Facades\FilamentIcon;
use Illuminate\Filesystem\Filesystem;
use Livewire\Features\SupportTesting\Testable;
use Spatie\LaravelPackageTools\Commands\InstallCommand;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;
use EightyNine\Approvals\Commands\ApprovalCommand;
use EightyNine\Approvals\Commands\PublishCommand;
use EightyNine\Approvals\Testing\TestsApproval;

class ApprovalServiceProvider extends PackageServiceProvider
{
    public static string $name = 'filament-approvals';

    public static string $viewNamespace = 'filament-approvals';

    public function configurePackage(Package $package): void
    {
        /*
         * This class is a Package Service Provider
         *
         * More info: https://github.com/spatie/laravel-package-tools
         */
        $package->name(static::$name)
            ->hasCommands($this->getCommands())
            ->hasInstallCommand(function (InstallCommand $command) {
                $command
                    ->publishConfigFile()
                    ->publishMigrations()
                    ->askToRunMigrations()
                    ->askToStarRepoOnGitHub('eightynine/filament-approvals');
            })
            ->hasViews(static::$viewNamespace);

        $configFileName = $package->shortName();

        if (file_exists($package->basePath("/../config/{$configFileName}.php"))) {
            $package->hasConfigFile();
        }

        if (file_exists($package->basePath('/../database/migrations'))) {
            $package->hasMigrations($this->getMigrations());
        }

        if (file_exists($package->basePath('/../resources/lang'))) {
            $package->hasTranslations();
        }

        if (file_exists($package->basePath('/../resources/views'))) {
            $package->hasViews(static::$viewNamespace);
        }
    }

    public function packageRegistered(): void
    {
    }

    public function packageBooted(): void
    {
        // Asset Registration
        FilamentAsset::register(
            $this->getAssets(),
            $this->getAssetPackageName()
        );

        FilamentAsset::registerScriptData(
            $this->getScriptData(),
            $this->getAssetPackageName()
        );

        // Icon Registration
        FilamentIcon::register($this->getIcons());

        // Handle Stubs
        if (app()->runningInConsole()) {
            // Publish configuration file
            $this->publishes([
                __DIR__ . '/../config/approvals.php' => config_path('approvals.php'),
            ], 'filament-approvals-config');

            foreach (app(Filesystem::class)->files(__DIR__ . '/../stubs/') as $file) {
                $this->publishes([
                    $file->getRealPath() => base_path("stubs/filament-approvals/{$file->getFilename()}"),
                ], 'filament-approvals-stubs');
            }

            // Publish Views for customization
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/filament-approvals'),
            ], 'filament-approvals-views');

            // Publish Filament Resources for customization
            $this->publishes([
                __DIR__ . '/Filament/Resources' => app_path('Filament/Resources'),
            ], 'filament-approvals-resources');

            // Publish Forms and Tables for customization
            $this->publishes([
                __DIR__ . '/Forms' => app_path('Forms/Approvals'),
                __DIR__ . '/Tables' => app_path('Tables/Approvals'),
            ], 'filament-approvals-components');

            // Publish translations for customization
            $this->publishes([
                __DIR__ . '/../resources/lang' => resource_path('lang/vendor/filament-approvals'),
            ], 'filament-approvals-translations');
        }

        // Testing
        Testable::mixin(new TestsApproval());
    }

    protected function getAssetPackageName(): ?string
    {
        return 'eightynine/filament-approvals';
    }

    /**
     * @return array<Asset>
     */
    protected function getAssets(): array
    {
        return [
            // AlpineComponent::make('filament-approvals', __DIR__ . '/../resources/dist/components/filament-approvals.js'),
            Css::make('filament-approvals-styles', __DIR__ . '/../resources/dist/filament-approvals.css'),
            Js::make('filament-approvals-scripts', __DIR__ . '/../resources/dist/filament-approvals.js'),
        ];
    }

    /**
     * @return array<class-string>
     */
    protected function getCommands(): array
    {
        return [
            ApprovalCommand::class,
            PublishCommand::class,
        ];
    }

    /**
     * @return array<string>
     */
    protected function getIcons(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getRoutes(): array
    {
        return [];
    }

    /**
     * @return array<string, mixed>
     */
    protected function getScriptData(): array
    {
        return [];
    }

    /**
     * @return array<string>
     */
    protected function getMigrations(): array
    {
        return [
            'create_filament-approvals_table',
        ];
    }
}
