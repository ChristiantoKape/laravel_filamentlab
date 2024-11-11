<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('homepage.heroes_titles', []);
        $this->migrator->add('homepage.heroes_banner_desktop', []);
        $this->migrator->add('homepage.heroes_banner_mobile', []);
        $this->migrator->add('homepage.heroes_descriptions', []);
        $this->migrator->add('homepage.heroes_button_text', []);
        $this->migrator->add('homepage.heroes_button_url', []);
    }

    public function down(): void
    {
        $this->migrator->delete('homepage.heroes_titles');
        $this->migrator->delete('homepage.heroes_banner_desktop');
        $this->migrator->delete('homepage.heroes_banner_mobile');
        $this->migrator->delete('homepage.heroes_descriptions');
        $this->migrator->delete('homepage.heroes_button_text');
        $this->migrator->delete('homepage.heroes_button_url');
    }
};
