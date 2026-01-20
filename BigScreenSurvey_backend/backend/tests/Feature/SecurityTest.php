<?php

namespace Tests\Feature\Security;

use Tests\TestCase;
use App\Models\{AdminUser, Survey};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class SecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_survey_token_is_truly_unique_and_secure()
    {
        $tokens = [];

        // Créer 1000 sondages et vérifier l'unicité des tokens
        for ($i = 0; $i < 1000; $i++) {
            $survey = Survey::create(['email' => "user{$i}@test.com"]);

            // Vérifier la longueur et le format
            $this->assertEquals(64, strlen($survey->unique_token));
            $this->assertMatchesRegularExpression('/^[a-zA-Z0-9]+$/', $survey->unique_token);

            // Vérifier l'unicité
            $this->assertNotContains($survey->unique_token, $tokens);
            $tokens[] = $survey->unique_token;
        }
    }

    public function test_admin_routes_are_protected()
    {
        $protectedRoutes = [
            '/api/admin/surveys',
            '/api/admin/statistics/general',
            '/api/admin/statistics/pie-charts',
            '/api/admin/statistics/radar-chart'
        ];

        foreach ($protectedRoutes as $route) {
            $response = $this->getJson($route);
            $response->assertStatus(401);
        }
    }

    public function test_password_is_properly_hashed()
    {
        $plainPassword = 'test_password_123';

        $admin = AdminUser::create([
            'username' => 'test_admin',
            'email' => 'admin@test.com',
            'password' => $plainPassword
        ]);

        // Le password ne doit jamais être stocké en clair
        $this->assertNotEquals($plainPassword, $admin->password);
        $this->assertTrue(Hash::check($plainPassword, $admin->password));

        // Vérifier dans la DB
        $this->assertDatabaseMissing('admin_users', [
            'password' => $plainPassword
        ]);
    }

    public function test_survey_access_requires_exact_token()
    {
        $survey = Survey::create(['email' => 'user@test.com']);
        $correctToken = $survey->unique_token;

        // Token correct
        $response = $this->getJson("/api/surveys/{$correctToken}");
        $response->assertStatus(200);

        // Tokens incorrects
        $incorrectTokens = [
            substr($correctToken, 0, -1), // Token tronqué
            $correctToken . 'x', // Token avec caractère supplémentaire
            strtoupper($correctToken), // Token en majuscules
            str_replace('a', 'b', $correctToken), // Token modifié
            'fake_token_123'
        ];

        foreach ($incorrectTokens as $incorrectToken) {
            $response = $this->getJson("/api/surveys/{$incorrectToken}");
            $response->assertStatus(404);
        }
    }

    public function test_input_validation_prevents_injection()
    {
        // Test SQL injection tentatives
        $maliciousInputs = [
            "'; DROP TABLE surveys; --",
            "admin@test.com'; DELETE FROM admin_users WHERE '1'='1",
            "<script>alert('XSS')</script>",
            "javascript:alert('XSS')"
        ];

        foreach ($maliciousInputs as $maliciousInput) {
            $response = $this->postJson('/api/surveys', [
                'email' => $maliciousInput
            ]);

            // Doit échouer la validation
            $response->assertStatus(422);
        }

        // Vérifier qu'aucune donnée malicieuse n'a été insérée
        $this->assertDatabaseMissing('surveys', [
            'email' => "'; DROP TABLE surveys; --"
        ]);
    }
}