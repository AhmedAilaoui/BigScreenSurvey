<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('responses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('survey_id')->constrained()->onDelete('cascade');
            $table->foreignId('question_id')->constrained()->onDelete('cascade');
            $table->text('answer'); // Stocké en texte pour tous les types
            $table->timestamps();

            // Une seule réponse par question/survey
            $table->unique(['survey_id', 'question_id']);

            // Index pour les recherches
            $table->index(['survey_id', 'question_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('responses');
    }
};