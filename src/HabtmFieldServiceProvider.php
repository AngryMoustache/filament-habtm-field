<?php

namespace AngryMoustache\HabtmField;

use Filament\PluginServiceProvider;
use Spatie\LaravelPackageTools\Package;

class HabtmFieldServiceProvider extends PluginServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package->name('filament-habtm-field')
            ->hasViews('habtm-field');
    }
}
