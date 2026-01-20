<?php

namespace Tests\Feature\Performance;

use Tests\TestCase;
use App\Models\{AdminUser, Survey, Question, Response};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_response_time_under_threshold()
    {
        $admin = AdminUser::factory()->create();

        // Créer un dataset important
        Survey::factory(1000)->completed()->create();
        $questions = Question::factory(20)->create();

        // Créer 20,000 réponses (1000 surveys × 20 questions)
        Survey::all()->each(function ($survey) use ($questions) {
            $questions->each(function ($question) use ($survey) {
                Response::factory()->create([
                    'survey_id' => $survey->id,
                    'question_id' => $question->id,
                ]);
            });
        });

        Sanctum::actingAs($admin);

        $startTime = microtime(true);

        $response = $this->getJson('/api/admin/statistics/general');

        $endTime = microtime(true);
        $executionTime = ($endTime - $startTime) * 1000; // en millisecondes

        $response->assertStatus(200);

        // L'API doit répondre en moins de 2 secondes même avec un gros dataset
        $this->assertLessThan(
            2000,
            $executionTime,
            "L'API statistics/general prend {$executionTime}ms, ce qui dépasse les 2000ms attendues"
        );
    }

    public function test_concurrent_survey_creation()
    {
        // Simuler 10 créations simultanées de sondages
        $responses = [];

        for ($i = 0; $i < 10; $i++) {
            $responses[] = $this->postJson('/api/surveys', [
                'email' => "user{$i}@test.com"
            ]);
        }

        foreach ($responses as $response) {
            $response->assertStatus(201);
        }

        // Vérifier que tous les tokens sont uniques
        $tokens = [];
        foreach ($responses as $response) {
            $token = $response->json('data.survey.unique_token');
            $this->assertNotContains($token, $tokens, 'Token dupliqué détecté');
            $tokens[] = $token;
        }

        $this->assertCount(10, array_unique($tokens));
    }
}