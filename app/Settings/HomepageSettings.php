<?php

namespace App\Settings;

use Spatie\LaravelSettings\Settings;

class HomepageSettings extends Settings
{
    // public ?string $hero_banner_desktop_image;
    // public ?string $hero_banner_mobile_image;
    public ?string $hero_title;
    // public ?string $hero_short_description;
    // public ?string $hero_button_text;
    // public ?string $hero_button_url;

    public static function group(): string
    {
        return 'hero';
    }
}