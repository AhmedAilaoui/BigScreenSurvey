<?php

namespace Tests\Unit\Models;

use Tests\TestCase;
use App\Models\Question;
use Illuminate\Foundation\Testing\RefreshDatabase;

class QuestionTest extends TestCase
{
    use RefreshDatabase;

    public function test_question_can_be_created()
    {
        $question = Question::create([
            'question_text' => 'Quel est votre âge ?',
            'question_type' => 'number',
            'is_required' => true,
            'question_order' => 1
        ]);

        $this->assertInstanceOf(Question::class, $question);
        $this->assertEquals('Quel est votre âge ?', $question->question_text);
        $this->assertEquals('number', $question->question_type);
        $this->assertTrue($question->is_required);
        $this->assertEquals(1, $question->question_order);
    }

    public function test_question_with_options_json()
    {
        $options = ['Oculus', 'HTC Vive', 'PlayStation VR'];
        $question = Question::create([
            'question_text' => 'Quel équipement VR possédez-vous ?',
            'question_type' => 'multiple_choice',
            'options' => $options,
            'is_required' => true,
            'question_order' => 6
        ]);

        $this->assertEquals($options, $question->options);
        $this->assertIsArray($question->options);
    }

    public function test_question_has_options_method()
    {
        $questionWithOptions = Question::create([
            'question_text' => 'Test avec options',
            'question_type' => 'multiple_choice',
            'options' => ['Option 1', 'Option 2'],
            'question_order' => 1
        ]);

        $questionWithoutOptions = Question::create([
            'question_text' => 'Test sans options',
            'question_type' => 'text',
            'question_order' => 2
        ]);

        $this->assertTrue($questionWithOptions->hasOptions());
        $this->assertFalse($questionWithoutOptions->hasOptions());
    }
}