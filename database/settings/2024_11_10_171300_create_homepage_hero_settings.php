<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        // $this->migrator->add('hero.banner_desktop_image', '');
        // $this->migrator->add('hero.banner_mobile_image', '');
        $this->migrator->add('hero.hero_title', '');
        // $this->migrator->add('hero.short_description', '');
        // $this->migrator->add('hero.button_text', '');
        // $this->migrator->add('hero.button_url', '');
    }

    public function down(): void
    {
        // $this->migrator->add('hero.banner_desktop_image', '');
        // $this->migrator->add('hero.banner_mobile_image', '');
        $this->migrator->delete('hero.hero_title', '');
        // $this->migrator->add('hero.short_description', '');
        // $this->migrator->add('hero.button_text', '');
        // $this->migrator->add('hero.button_url', '');
    }
};
