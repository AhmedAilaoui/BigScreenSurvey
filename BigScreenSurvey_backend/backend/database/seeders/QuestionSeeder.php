<?php

namespace Database\Seeders;

use App\Models\Question;
use Illuminate\Database\Seeder;

class QuestionSeeder extends Seeder
{
    public function run()
    {
        $questions = [
            [
                'number' => 1,
                'content' => 'Votre adresse mail',
                'type' => 'B',
                'options' => null,
            ],
            [
                'number' => 2,
                'content' => 'Votre âge',
                'type' => 'B',
                'options' => null,
            ],
            [
                'number' => 3,
                'content' => 'Votre sexe',
                'type' => 'A',
                'options' => ['Homme', 'Femme', 'Préfère ne pas répondre'],
            ],
            [
                'number' => 4,
                'content' => 'Nombre de personne dans votre foyer (adulte & enfants)',
                'type' => 'C',
                'options' => [1, 2, 3, 4, 5],
            ],
            [
                'number' => 5,
                'content' => 'Votre profession',
                'type' => 'B',
                'options' => null,
            ],
            [
                'number' => 6,
                'content' => 'Quel marque de casque VR utilisez-vous ?',
                'type' => 'A',
                'options' => ['Oculus Quest', 'Oculus Rift/s', 'HTC Vive', 'Windows Mixed Reality', 'Valve index'],
            ],
            [
                'number' => 7,
                'content' => 'Sur quel magasin d\'application achetez vous des contenus VR ?',
                'type' => 'A',
                'options' => ['SteamVR', 'Occulus store', 'Viveport', 'Windows store'],
            ],
            [
                'number' => 8,
                'content' => 'Quel casque envisagez-vous d\'acheter dans un futur proche ?',
                'type' => 'A',
                'options' => ['Occulus Quest', 'Occulus Go', 'HTC Vive Pro', 'PSVR', 'Autre', 'Aucun'],
            ],
            [
                'number' => 9,
                'content' => 'Au sein de votre foyer, combien de personnes utilisent votre casque VR pour regarder Bigscreen ?',
                'type' => 'C',
                'options' => [1, 2, 3, 4, 5],
            ],
            [
                'number' => 10,
                'content' => 'Vous utilisez principalement Bigscreen pour :',
                'type' => 'A',
                'options' => ['regarder la TV en direct', 'regarder des films', 'travailler', 'jouer en solo', 'jouer en équipe'],
            ],
            [
                'number' => 11,
                'content' => 'Combien donnez-vous de point pour la qualité de l\'image sur Bigscreen ?',
                'type' => 'C',
                'options' => [1, 2, 3, 4, 5],
            ],
            [
                'number' => 12,
                'content' => 'Combien donnez-vous de point pour le confort d\'utilisation de l\'interface Bigscreen ?',
                'type' => 'C',
                'options' => [1, 2, 3, 4, 5],
            ],
            [
                'number' => 13,
                'content' => 'Combien donnez-vous de point pour la connexion réseau de Bigscreen ?',
                'type' => 'C',
                'options' => [1, 2, 3, 4, 5],
            ],
            [
                'number' => 14,
                'content' => 'Combien donnez-vous de point pour la qualité des graphismes 3D dans Bigscreen ?',
                'type' => 'C',
                'options' => [1, 2, 3, 4, 5],
            ],
            [
                'number' => 15,
                'content' => 'Combien donnez-vous de point pour la qualité audio dans Bigscreen ?',
                'type' => 'C',
                'options' => [1, 2, 3, 4, 5],
            ],
            [
                'number' => 16,
                'content' => 'Aimeriez-vous avoir des notifications plus précises au cours de vos sessions Bigscreen ?',
                'type' => 'A',
                'options' => ['Oui', 'Non'],
            ],
            [
                'number' => 17,
                'content' => 'Aimeriez-vous pouvoir inviter un ami à rejoindre votre session via son smartphone ?',
                'type' => 'A',
                'options' => ['Oui', 'Non'],
            ],
            [
                'number' => 18,
                'content' => 'Aimeriez-vous pouvoir enregistrer des émissions TV pour pouvoir les regarder ultérieurement ?',
                'type' => 'A',
                'options' => ['Oui', 'Non'],
            ],
            [
                'number' => 19,
                'content' => 'Aimeriez-vous jouer à des jeux exclusifs sur votre Bigscreen ?',
                'type' => 'A',
                'options' => ['Oui', 'Non'],
            ],
            [
                'number' => 20,
                'content' => 'Quelle nouvelle fonctionnalité devrait exister sur Bigscreen ?',
                'type' => 'B',
                'options' => null,
            ],
        ];

        foreach ($questions as $questionData) {
            Question::create($questionData);
        }
    }
}