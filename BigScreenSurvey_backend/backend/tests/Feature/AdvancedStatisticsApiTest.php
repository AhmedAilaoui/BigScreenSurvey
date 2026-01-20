<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\{AdminUser, Survey, Question, Response};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class AdvancedStatisticsApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_statistics_with_large_dataset()
    {
        $admin = AdminUser::factory()->create();

        // Créer 100 sondages avec réponses
        $surveys = Survey::factory(100)->completed()->create();
        $questions = Question::factory(5)->multipleChoice()->create();

        foreach ($surveys as $survey) {
            foreach ($questions as $question) {
                Response::factory()->create([
                    'survey_id' => $survey->id,
                    'question_id' => $question->id,
                    'answer_text' => $question->options[array_rand($question->options)]
                ]);
            }
        }

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/admin/statistics/general');

        $response->assertStatus(200);

        $data = $response->json('data');
        $this->assertEquals(100, $data['total_surveys']);
        $this->assertEquals(100, $data['completed_surveys']);
        $this->assertEquals(100.0, $data['completion_rate']);
        $this->assertEquals(500, $data['total_responses']); // 100 surveys × 5 questions
    }

    public function test_pie_chart_data_accuracy()
    {
        $admin = AdminUser::factory()->create();

        // Créer une question spécifique avec ordre 6
        $question = Question::factory()->create([
            'question_order' => 6,
            'question_type' => 'multiple_choice',
            'options' => ['Oculus', 'HTC Vive', 'PlayStation VR']
        ]);

        // Créer des réponses avec distribution connue
        $surveys = Survey::factory(10)->completed()->create();

        // 5 Oculus, 3 HTC Vive, 2 PlayStation VR
        for ($i = 0; $i < 5; $i++) {
            Response::factory()->create([
                'survey_id' => $surveys[$i]->id,
                'question_id' => $question->id,
                'answer_text' => 'Oculus'
            ]);
        }

        for ($i = 5; $i < 8; $i++) {
            Response::factory()->create([
                'survey_id' => $surveys[$i]->id,
                'question_id' => $question->id,
                'answer_text' => 'HTC Vive'
            ]);
        }

        for ($i = 8; $i < 10; $i++) {
            Response::factory()->create([
                'survey_id' => $surveys[$i]->id,
                'question_id' => $question->id,
                'answer_text' => 'PlayStation VR'
            ]);
        }

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/admin/statistics/pie-charts');

        $response->assertStatus(200);

        $pieData = $response->json('data.question_6.chart_data');

        // Vérifier les pourcentages
        $oculusData = collect($pieData)->firstWhere('label', 'Oculus');
        $this->assertEquals(5, $oculusData['value']);
        $this->assertEquals(50.0, $oculusData['percentage']);
    }
}