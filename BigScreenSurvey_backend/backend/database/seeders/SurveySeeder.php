<?php

namespace Database\Seeders;

use App\Models\Survey;
use Illuminate\Database\Seeder;

class SurveySeeder extends Seeder
{
    public function run()
    {
        // Créer quelques surveys de test
        Survey::create([
            'email' => 'test1@bigscreen.com',
            'is_completed' => false,
        ]);

        Survey::create([
            'email' => 'test2@bigscreen.com',
            'is_completed' => true,
        ]);

        Survey::create([
            'email' => 'test3@bigscreen.com',
            'is_completed' => false,
        ]);
    }
}