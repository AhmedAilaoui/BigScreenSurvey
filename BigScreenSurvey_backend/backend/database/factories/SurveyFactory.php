<?php

namespace Database\Factories;

use App\Models\Survey;
use Illuminate\Database\Eloquent\Factories\Factory;

class SurveyFactory extends Factory
{
    protected $model = Survey::class;

    public function definition()
    {
        return [
            'email' => $this->faker->unique()->safeEmail(),
            'completion_status' => $this->faker->randomElement(['pending', 'completed']),
            'completed_at' => function (array $attributes) {
                return $attributes['completion_status'] === 'completed'
                    ? $this->faker->dateTimeBetween('-1 month', 'now')
                    : null;
            },
        ];
    }

    public function completed()
    {
        return $this->state(function (array $attributes) {
            return [
                'completion_status' => 'completed',
                'completed_at' => $this->faker->dateTimeBetween('-1 month', 'now'),
            ];
        });
    }

    public function pending()
    {
        return $this->state(function (array $attributes) {
            return [
                'completion_status' => 'pending',
                'completed_at' => null,
            ];
        });
    }
}