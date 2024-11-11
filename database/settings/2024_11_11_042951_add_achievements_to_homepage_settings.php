<?php

use Spatie\LaravelSettings\Migrations\SettingsMigration;

return new class extends SettingsMigration
{
    public function up(): void
    {
        $this->migrator->add('homepage.achievements', []);
    }

    public function down(): void
    {
        $this->migrator->delete('homepage.achievements');
    }
};