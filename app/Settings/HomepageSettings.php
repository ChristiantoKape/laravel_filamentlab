<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class HomepageSettings extends Settings
{
    public array $heroes_titles = [];
    public array $heroes_banner_desktop = [];
    public array $heroes_banner_mobile = [];
    public array $heroes_descriptions = [];
    public array $heroes_button_text = [];
    public array $heroes_button_url = [];

    public static function group(): string
    {
        return 'homepage';
    }

    protected $casts = [
        'heroes_titles' => 'array',
        'heroes_banner_desktop' => 'array',
        'heroes_banner_mobile' => 'array',
        'heroes_descriptions' => 'array',
        'heroes_button_text' => 'array',
        'heroes_button_url' => 'array',
    ];
}