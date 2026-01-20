<?php

namespace Tests\Feature\Integration;

use Tests\TestCase;
use App\Models\{AdminUser, Survey, Question, Response};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class CompleteWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_complete_survey_workflow()
    {
        // 1. Seeder les questions (simulé)
        $questions = [
            Question::create([
                'question_text' => 'Quel est votre âge ?',
                'question_type' => 'number',
                'is_required' => true,
                'question_order' => 1
            ]),
            Question::create([
                'question_text' => 'Votre email',
                'question_type' => 'email',
                'is_required' => true,
                'question_order' => 2
            ]),
            Question::create([
                'question_text' => 'Équipement VR',
                'question_type' => 'multiple_choice',
                'options' => ['Oculus', 'HTC Vive', 'PlayStation VR'],
                'is_required' => true,
                'question_order' => 6
            ])
        ];

        // 2. Utilisateur crée un sondage
        $surveyResponse = $this->postJson('/api/surveys', [
            'email' => 'user@test.com'
        ]);

        $surveyResponse->assertStatus(201);
        $surveyToken = $surveyResponse->json('data.survey.unique_token');

        // 3. Utilisateur récupère les questions
        $questionsResponse = $this->getJson('/api/questions');
        $questionsResponse->assertStatus(200);
        $this->assertCount(3, $questionsResponse->json('data.questions'));

        // 4. Utilisateur soumet ses réponses
        $responsesData = [
            'responses' => [
                [
                    'question_id' => $questions[0]->id,
                    'answer_text' => '28'
                ],
                [
                    'question_id' => $questions[1]->id,
                    'answer_text' => 'user@test.com'
                ],
                [
                    'question_id' => $questions[2]->id,
                    'answer_text' => 'Oculus'
                ]
            ]
        ];

        $submitResponse = $this->postJson("/api/surveys/{$surveyToken}/responses", $responsesData);
        $submitResponse->assertStatus(201);

        // 5. Utilisateur finalise le sondage
        $completeResponse = $this->putJson("/api/surveys/{$surveyToken}/complete");
        $completeResponse->assertStatus(200);

        // 6. Utilisateur consulte ses réponses
        $viewResponse = $this->getJson("/api/surveys/{$surveyToken}/responses");
        $viewResponse->assertStatus(200);
        $this->assertCount(3, $viewResponse->json('data.responses'));

        // 7. Admin se connecte et consulte les statistiques
        $admin = AdminUser::factory()->create();
        Sanctum::actingAs($admin);

        $statsResponse = $this->getJson('/api/admin/statistics/general');
        $statsResponse->assertStatus(200);

        $statsData = $statsResponse->json('data');
        $this->assertEquals(1, $statsData['total_surveys']);
        $this->assertEquals(1, $statsData['completed_surveys']);
        $this->assertEquals(100.0, $statsData['completion_rate']);

        // 8. Admin consulte les pie charts
        $pieResponse = $this->getJson('/api/admin/statistics/pie-charts');
        $pieResponse->assertStatus(200);

        $pieData = $pieResponse->json('data.question_6.chart_data');
        $oculusData = collect($pieData)->firstWhere('label', 'Oculus');
        $this->assertEquals(1, $oculusData['value']);
    }

    public function test_multiple_users_survey_workflow()
    {
        // Créer des questions
        Question::factory(5)->create();

        // Simuler 3 utilisateurs qui font le sondage
        $userEmails = ['user1@test.com', 'user2@test.com', 'user3@test.com'];
        $surveyTokens = [];

        foreach ($userEmails as $email) {
            // Chaque utilisateur crée son sondage
            $surveyResponse = $this->postJson('/api/surveys', ['email' => $email]);
            $surveyTokens[] = $surveyResponse->json('data.survey.unique_token');

            // Soumet des réponses
            $questions = Question::take(3)->get();
            $responsesData = [
                'responses' => $questions->map(function ($question) {
                    return [
                        'question_id' => $question->id,
                        'answer_text' => 'Réponse test'
                    ];
                })->toArray()
            ];

            $this->postJson("/api/surveys/{$surveyTokens[count($surveyTokens) - 1]}/responses", $responsesData);

            // Finalise (sauf le dernier pour tester les pending)
            if ($email !== 'user3@test.com') {
                $this->putJson("/api/surveys/{$surveyTokens[count($surveyTokens) - 1]}/complete");
            }
        }

        // Admin vérifie les stats
        $admin = AdminUser::factory()->create();
        Sanctum::actingAs($admin);

        $statsResponse = $this->getJson('/api/admin/statistics/general');
        $statsData = $statsResponse->json('data');

        $this->assertEquals(3, $statsData['total_surveys']);
        $this->assertEquals(2, $statsData['completed_surveys']);
        $this->assertEquals(1, $statsData['pending_surveys']);
        $this->assertEquals(66.67, $statsData['completion_rate']);
    }
}