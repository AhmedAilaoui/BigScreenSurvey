<?php

namespace Tests\Feature\Api;

use Tests\TestCase;
use App\Models\{Survey, AdminUser};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;

class SurveyApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_survey_can_be_created()
    {
        $surveyData = [
            'email' => 'user@test.com'
        ];

        $response = $this->postJson('/api/surveys', $surveyData);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'success',
                'message',
                'data' => [
                    'survey' => [
                        'id',
                        'email',
                        'unique_token',
                        'completion_status',
                        'created_at'
                    ]
                ]
            ]);

        $this->assertDatabaseHas('surveys', [
            'email' => 'user@test.com',
            'completion_status' => 'pending'
        ]);
    }

    public function test_survey_can_be_retrieved_by_token()
    {
        $survey = Survey::create(['email' => 'user@test.com']);

        $response = $this->getJson('/api/surveys/' . $survey->unique_token);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'data' => [
                    'survey' => [
                        'id' => $survey->id,
                        'email' => 'user@test.com',
                        'unique_token' => $survey->unique_token,
                        'completion_status' => 'pending'
                    ]
                ]
            ]);
    }

    public function test_survey_not_found_with_invalid_token()
    {
        $response = $this->getJson('/api/surveys/invalid_token_12345');

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'Sondage introuvable.'
            ]);
    }

    public function test_survey_can_be_completed()
    {
        $survey = Survey::create(['email' => 'user@test.com']);

        $response = $this->putJson('/api/surveys/' . $survey->unique_token . '/complete');

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Sondage marqué comme complété!'
            ]);

        $survey->refresh();
        $this->assertTrue($survey->isCompleted());
        $this->assertNotNull($survey->completed_at);
    }

    public function test_admin_can_list_all_surveys()
    {
        $admin = AdminUser::create([
            'username' => 'admin_test',
            'password' => 'password123'
        ]);

        Survey::create(['email' => 'user1@test.com']);
        Survey::create(['email' => 'user2@test.com']);

        Sanctum::actingAs($admin);

        $response = $this->getJson('/api/admin/surveys');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'data' => [
                    'surveys' => [
                        '*' => ['id', 'email', 'unique_token', 'completion_status']
                    ]
                ]
            ]);
    }

    public function test_admin_can_delete_survey()
    {
        $admin = AdminUser::create([
            'username' => 'admin_test',
            'password' => 'password123'
        ]);

        $survey = Survey::create(['email' => 'user@test.com']);

        Sanctum::actingAs($admin);

        $response = $this->deleteJson('/api/admin/surveys/' . $survey->unique_token);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'Sondage supprimé avec succès!'
            ]);

        $this->assertDatabaseMissing('surveys', ['id' => $survey->id]);
    }
}
