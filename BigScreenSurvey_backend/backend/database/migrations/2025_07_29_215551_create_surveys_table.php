<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('surveys', function (Blueprint $table) {
            $table->id();
            $table->string('email', 255);
            $table->string('unique_token', 64)->unique();
            $table->boolean('is_completed')->default(false);
            $table->timestamps();

            // Index sur le token pour les recherches rapides
            $table->index('unique_token');
        });
    }

    public function down()
    {
        Schema::dropIfExists('surveys');
    }
};
