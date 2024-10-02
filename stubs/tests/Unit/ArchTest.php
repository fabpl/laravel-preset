<?php

declare(strict_types=1);

use App\Providers\TelescopeServiceProvider;

arch()->preset()->php();

arch()->preset()->laravel()
    ->ignoring(TelescopeServiceProvider::class);
