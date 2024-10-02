<?php

declare(strict_types=1);

namespace Fabpl\Preset;

use Illuminate\Support\ServiceProvider;
use Fabpl\Preset\Console\Commands\InstallCommand;

final class PresetServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->commands([
            InstallCommand::class,
        ]);
    }
}
