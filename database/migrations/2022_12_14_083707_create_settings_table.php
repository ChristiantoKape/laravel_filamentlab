<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('settings', function (Blueprint $table): void {
            $table->id();

            $table->string('group')->index();  // Tambahkan group
            $table->string('name');            // Tambahkan name
            $table->json('payload');           // Ganti value menjadi payload
            $table->boolean('locked')->default(false);

            $table->timestamps();

            $table->unique(['group', 'name']); // Buat composite unique index
        });
    }

    public function down()
    {
        Schema::dropIfExists('settings');
    }
};
