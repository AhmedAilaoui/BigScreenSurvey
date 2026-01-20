<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Survey;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SurveyTest extends TestCase
{
    use RefreshDatabase;

    public function test_survey_can_be_created()
    {
        $survey = Survey::create([
            'email' => 'user@test.com'
        ]);

        $this->assertInstanceOf(Survey::class, $survey);
        $this->assertEquals('user@test.com', $survey->email);
        $this->assertNotNull($survey->unique_token);
        $this->assertEquals(64, strlen($survey->unique_token));
        $this->assertEquals('pending', $survey->completion_status);
    }

    public function test_survey_generates_unique_token_automatically()
    {
        $survey1 = Survey::create(['email' => 'user1@test.com']);
        $survey2 = Survey::create(['email' => 'user2@test.com']);

        $this->assertNotEquals($survey1->unique_token, $survey2->unique_token);
        $this->assertEquals(64, strlen($survey1->unique_token));
        $this->assertEquals(64, strlen($survey2->unique_token));
    }

    public function test_survey_is_completed_method()
    {
        $survey = Survey::create(['email' => 'user@test.com']);

        $this->assertFalse($survey->isCompleted());

        $survey->completion_status = 'completed';
        $this->assertTrue($survey->isCompleted());
    }

    public function test_survey_mark_as_completed_method()
    {
        $survey = Survey::create(['email' => 'user@test.com']);

        $survey->markAsCompleted();

        $this->assertTrue($survey->isCompleted());
        $this->assertNotNull($survey->completed_at);
    }
}
