<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\{Survey, Question, Response};
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuestionResponseApiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer quelques questions de test
        Question::create([
            'question_text' => 'Quel est votre âge ?',
            'question_type' => 'number',
            'is_required' => true,
            'question_order' => 1
        ]);

        Question::create([
            'question_text' => 'Quel équipement VR possédez-vous ?',
            'question_type' => 'multiple_choice',
            'options' => ['Oculus', 'HTC Vive', 'PlayStation VR'],
            'is_required' => true,
            'question_order' => 6
        ]);
    }

    public function test_can_get_all_questions()
    {
        $response = $this->getJson('/api/questions');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'questions' => [
                        '*' => [
                            'id',
                            'question_text',
                            'question_type',
                            'options',
                            'is_required',
                            'question_order'
                        ]
                    ]
                ]
            ]);
    }

    public function test_can_submit_survey_responses()
    {
        $survey = Survey::create(['email' => 'user@test.com']);
        $questions = Question::all();

        $responsesData = [
            'responses' => [
                [
                    'question_id' => $questions[0]->id,
                    'answer_text' => '25'
                ],
                [
                    'question_id' => $questions[1]->id,
                    'answer_text' => 'Oculus'
                ]
            ]
        ];

        $response = $this->postJson('/api/surveys/' . $survey->unique_token . '/responses', $responsesData);

        $response->assertStatus(201)
            ->assertJson([
                'success' => true,
                'message' => '2 réponses sauvegardées avec succès!'
            ]);

        $this->assertDatabaseHas('responses', [
            'survey_id' => $survey->id,
            'question_id' => $questions[0]->id,
            'answer_text' => '25'
        ]);
    }

    public function test_can_get_survey_responses()
    {
        $survey = Survey::create(['email' => 'user@test.com']);
        $question = Question::first();

        Response::create([
            'survey_id' => $survey->id,
            'question_id' => $question->id,
            'answer_text' => '25'
        ]);

        $response = $this->getJson('/api/surveys/' . $survey->unique_token . '/responses');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'survey' => ['id', 'email', 'unique_token'],
                    'responses' => [
                        '*' => [
                            'id',
                            'answer_text',
                            'question' => ['question_text', 'question_type']
                        ]
                    ]
                ]
            ]);
    }

    public function test_response_validation_fails_with_invalid_data()
    {
        $survey = Survey::create(['email' => 'user@test.com']);

        $invalidData = [
            'responses' => [
                [
                    'question_id' => 999, // Question inexistante
                    'answer_text' => 'Test'
                ]
            ]
        ];

        $response = $this->postJson('/api/surveys/' . $survey->unique_token . '/responses', $invalidData);

        $response->assertStatus(422);
    }
}

// ============================================
// 📋 8. TESTS FEATURE - API Statistics
// ============================================

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\{AdminUser, Survey, Question, Response};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class StatisticsApiTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $questions;

    protected function setUp(): void
    {
        parent::setUp();

        // Créer un admin pour les tests
        $this->admin = AdminUser::create([
            'username' => 'admin_test',
            'password' => 'password123'
        ]);

        // Créer des questions de test
        $this->questions = [
            Question::create([
                'question_text' => 'Quel équipement VR possédez-vous ?',
                'question_type' => 'multiple_choice',
                'options' => ['Oculus', 'HTC Vive', 'PlayStation VR'],
                'question_order' => 6
            ]),
            Question::create([
                'question_text' => 'Qualité graphique (1-5)',
                'question_type' => 'scale',
                'question_order' => 11
            ])
        ];

        // Créer des données de test
        $surveys = [
            Survey::create(['email' => 'user1@test.com', 'completion_status' => 'completed']),
            Survey::create(['email' => 'user2@test.com', 'completion_status' => 'completed']),
            Survey::create(['email' => 'user3@test.com', 'completion_status' => 'pending'])
        ];

        // Réponses de test
        Response::create([
            'survey_id' => $surveys[0]->id,
            'question_id' => $this->questions[0]->id,
            'answer_text' => 'Oculus'
        ]);

        Response::create([
            'survey_id' => $surveys[1]->id,
            'question_id' => $this->questions[0]->id,
            'answer_text' => 'HTC Vive'
        ]);

        Response::create([
            'survey_id' => $surveys[0]->id,
            'question_id' => $this->questions[1]->id,
            'answer_text' => '4'
        ]);
    }

    public function test_admin_can_get_general_statistics()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/admin/statistics/general');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'total_surveys',
                    'completed_surveys',
                    'pending_surveys',
                    'completion_rate',
                    'total_responses'
                ]
            ]);

        $data = $response->json('data');
        $this->assertEquals(3, $data['total_surveys']);
        $this->assertEquals(2, $data['completed_surveys']);
        $this->assertEquals(1, $data['pending_surveys']);
        $this->assertEquals(66.67, $data['completion_rate']);
    }

    public function test_admin_can_get_pie_chart_data()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/admin/statistics/pie-charts');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'question_6' => [
                        'question_text',
                        'chart_data' => [
                            '*' => ['label', 'value', 'percentage']
                        ]
                    ]
                ]
            ]);
    }

    public function test_admin_can_get_radar_chart_data()
    {
        Sanctum::actingAs($this->admin);

        $response = $this->getJson('/api/admin/statistics/radar-chart');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'radar_data' => [
                        'labels',
                        'datasets' => [
                            '*' => ['label', 'data']
                        ]
                    ]
                ]
            ]);
    }

    public function test_unauthenticated_user_cannot_access_statistics()
    {
        $response = $this->getJson('/api/admin/statistics/general');

        $response->assertStatus(401);
    }
}