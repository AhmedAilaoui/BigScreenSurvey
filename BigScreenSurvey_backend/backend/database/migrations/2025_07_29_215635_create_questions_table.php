<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('questions', function (Blueprint $table) {
            $table->id();
            $table->integer('number')->unique(); // 1 à 20
            $table->text('content'); // Contenu de la question
            $table->enum('type', ['A', 'B', 'C']); // Type de question
            $table->json('options')->nullable(); // Options pour type A et C
            $table->boolean('is_required')->default(true);
            $table->timestamps();

            // Index sur le numéro de question
            $table->index('number');
        });
    }

    public function down()
    {
        Schema::dropIfExists('questions');
    }
};
