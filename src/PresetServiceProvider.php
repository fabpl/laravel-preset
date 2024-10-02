<?php

declare(strict_types=1);

namespace Fabpl\Preset;

use Fabpl\Preset\Console\Commands\InstallCommand;
use Illuminate\Support\ServiceProvider;

final class PresetServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([
            InstallCommand::class,
        ]);
    }
}
