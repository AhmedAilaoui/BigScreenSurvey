<?php

namespace Database\Factories;

use App\Models\Question;
use Illuminate\Database\Eloquent\Factories\Factory;

class QuestionFactory extends Factory
{
    protected $model = Question::class;

    public function definition()
    {
        $types = ['text', 'email', 'number', 'scale', 'multiple_choice'];
        $type = $this->faker->randomElement($types);

        return [
            'question_text' => $this->faker->sentence() . '?',
            'question_type' => $type,
            'options' => $type === 'multiple_choice'
                ? $this->faker->randomElements(['Option A', 'Option B', 'Option C', 'Option D'], 3)
                : null,
            'is_required' => $this->faker->boolean(80), // 80% de chance d'être requis
            'question_order' => $this->faker->numberBetween(1, 20),
        ];
    }

    public function multipleChoice()
    {
        return $this->state(function (array $attributes) {
            return [
                'question_type' => 'multiple_choice',
                'options' => $this->faker->randomElements([
                    'Oculus Quest',
                    'HTC Vive',
                    'PlayStation VR',
                    'Valve Index',
                    'Autre'
                ], 4),
            ];
        });
    }

    public function scale()
    {
        return $this->state(function (array $attributes) {
            return [
                'question_type' => 'scale',
                'options' => null,
            ];
        });
    }
}

// ============================================
// 📋 FACTORY - ResponseFactory
// ============================================

namespace Database\Factories;

use App\Models\{Response, Survey, Question};
use Illuminate\Database\Eloquent\Factories\Factory;

class ResponseFactory extends Factory
{
    protected $model = Response::class;

    public function definition()
    {
        return [
            'survey_id' => Survey::factory(),
            'question_id' => Question::factory(),
            'answer_text' => $this->faker->sentence(),
        ];
    }

    public function forScale()
    {
        return $this->state(function (array $attributes) {
            return [
                'answer_text' => (string) $this->faker->numberBetween(1, 5),
            ];
        });
    }

    public function forEmail()
    {
        return $this->state(function (array $attributes) {
            return [
                'answer_text' => $this->faker->safeEmail(),
            ];
        });
    }
}
