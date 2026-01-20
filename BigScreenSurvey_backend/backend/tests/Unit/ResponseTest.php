<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\{Survey, Question, Response};
use Illuminate\Foundation\Testing\RefreshDatabase;

class ResponseTest extends TestCase
{
    use RefreshDatabase;

    public function test_response_can_be_created()
    {
        $survey = Survey::create(['email' => 'user@test.com']);
        $question = Question::create([
            'question_text' => 'Test question',
            'question_type' => 'text',
            'question_order' => 1
        ]);

        $response = Response::create([
            'survey_id' => $survey->id,
            'question_id' => $question->id,
            'answer_text' => 'Ma réponse de test'
        ]);

        $this->assertInstanceOf(Response::class, $response);
        $this->assertEquals($survey->id, $response->survey_id);
        $this->assertEquals($question->id, $response->question_id);
        $this->assertEquals('Ma réponse de test', $response->answer_text);
    }

    public function test_response_belongs_to_survey()
    {
        $survey = Survey::create(['email' => 'user@test.com']);
        $question = Question::create([
            'question_text' => 'Test',
            'question_type' => 'text',
            'question_order' => 1
        ]);

        $response = Response::create([
            'survey_id' => $survey->id,
            'question_id' => $question->id,
            'answer_text' => 'Test'
        ]);

        $this->assertInstanceOf(Survey::class, $response->survey);
        $this->assertEquals($survey->id, $response->survey->id);
    }

    public function test_response_belongs_to_question()
    {
        $survey = Survey::create(['email' => 'user@test.com']);
        $question = Question::create([
            'question_text' => 'Test question',
            'question_type' => 'text',
            'question_order' => 1
        ]);

        $response = Response::create([
            'survey_id' => $survey->id,
            'question_id' => $question->id,
            'answer_text' => 'Test'
        ]);

        $this->assertInstanceOf(Question::class, $response->question);
        $this->assertEquals($question->id, $response->question->id);
    }
}