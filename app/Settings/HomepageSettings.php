<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class HomepageSettings extends Settings
{
    public array $heroes = [];
    public array $achievements = [];

    public static function group(): string
    {
        return 'homepage';
    }

    protected $casts = [
        'heroes' => 'array',
        'achievements' => 'array',
    ];
}