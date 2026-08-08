<?php

namespace Platform\Examinations;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Relations\Relation;
use Livewire\Livewire;
use Platform\Core\PlatformCore;
use Platform\Core\Routing\ModuleRouter;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

class ExaminationsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/examinations.php', 'examinations');
    }

    public function boot(): void
    {
        // Morph alias — erbrachte Leistungen (encounter Service) binden per 'examination'.
        Relation::morphMap([
            'examination' => \Platform\Examinations\Models\Examination::class,
        ]);

        // Vermengungsgruppen-Provider registrieren (lose Kopplung → der Termin prüft über die Core-Registry).
        if (class_exists(\Platform\Core\Support\CatalogCombinationRegistry::class)) {
            try {
                app(\Platform\Core\Support\CatalogCombinationRegistry::class)
                    ->register(new \Platform\Examinations\Catalog\ExaminationCombinationProvider());
            } catch (\Throwable $e) {
            }
        }

        // Step 1: Register module
        if (
            config()->has('examinations.routing') &&
            config()->has('examinations.navigation') &&
            Schema::hasTable('modules')
        ) {
            PlatformCore::registerModule([
                'key'        => 'examinations',
                'title'      => 'Untersuchungen',
                'group'      => 'catalog',
                'routing'    => config('examinations.routing'),
                'guard'      => config('examinations.guard'),
                'navigation' => config('examinations.navigation'),
                'sidebar'    => config('examinations.sidebar'),
            ]);
        }

        // Step 2: Routes (only if module registered)
        if (PlatformCore::getModule('examinations')) {
            ModuleRouter::group('examinations', function () {
                $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');
            });
        }

        // Step 3: Migrations
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Step 4: Publish config
        $this->publishes([
            __DIR__ . '/../config/examinations.php' => config_path('examinations.php'),
        ], 'config');

        // Step 5: Views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'examinations');

        // Step 6: Livewire components (auto-scan)
        $this->registerLivewireComponents();

        // Step 7: LLM Tools
        $this->registerTools();
    }

    /**
     * Registers the module's MCP/LLM tools.
     */
    protected function registerTools(): void
    {
        try {
            $registry = resolve(\Platform\Core\Tools\ToolRegistry::class);

            // Overview
            $registry->register(new \Platform\Examinations\Tools\ExaminationsOverviewTool());

            // Examination CRUD
            $registry->register(new \Platform\Examinations\Tools\ListExaminationsTool());
            $registry->register(new \Platform\Examinations\Tools\GetExaminationTool());
            $registry->register(new \Platform\Examinations\Tools\CreateExaminationTool());
            $registry->register(new \Platform\Examinations\Tools\UpdateExaminationTool());
            $registry->register(new \Platform\Examinations\Tools\DeleteExaminationTool());

            // Bündel (Pakete)
            $registry->register(new \Platform\Examinations\Tools\ListBundlesTool());
            $registry->register(new \Platform\Examinations\Tools\GetBundleTool());
            $registry->register(new \Platform\Examinations\Tools\CreateBundleTool());
            $registry->register(new \Platform\Examinations\Tools\AddBundleItemTool());
            $registry->register(new \Platform\Examinations\Tools\RemoveBundleItemTool());
        } catch (\Throwable $e) {
            \Log::warning('Examinations: tool registration failed', ['error' => $e->getMessage()]);
        }
    }

    /**
     * Registers all Livewire components automatically.
     *
     * File src/Livewire/Examination/Index.php -> alias examinations.examination.index
     */
    protected function registerLivewireComponents(): void
    {
        $basePath = __DIR__ . '/Livewire';
        $baseNamespace = 'Platform\\Examinations\\Livewire';
        $prefix = 'examinations';

        if (!is_dir($basePath)) {
            return;
        }

        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($basePath)
        );

        foreach ($iterator as $file) {
            if (!$file->isFile() || $file->getExtension() !== 'php') {
                continue;
            }

            $relativePath = str_replace($basePath . DIRECTORY_SEPARATOR, '', $file->getPathname());
            $classPath = str_replace(['/', '.php'], ['\\', ''], $relativePath);
            $class = $baseNamespace . '\\' . $classPath;

            if (!class_exists($class)) {
                continue;
            }

            $aliasPath = str_replace(['\\', '/'], '.', Str::kebab(str_replace('.php', '', $relativePath)));
            $alias = $prefix . '.' . $aliasPath;

            Livewire::component($alias, $class);
        }
    }
}
