<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Response;
use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class ResponseController extends Controller
{
    /**
     * Afficher les réponses publiquement avec le token
     * GET /responses/{token}
     */
    public function showPublic($token)
    {
        $survey = Survey::where('unique_token', $token)->first();

        if (!$survey) {
            return response()->json([
                'message' => 'Survey non trouvé'
            ], 404);
        }

        $responses = $survey->getResponsesWithQuestions();
        $formattedResponses = [];

        foreach ($responses as $response) {
            $formattedResponses[] = [
                'question_number' => $response->question->number,
                'question_content' => $response->question->content,
                'answer' => $response->getFormattedAnswer()
            ];
        }

        return response()->json([
            'success' => true,
            'survey' => [
                'email' => $survey->email,
                'is_completed' => $survey->is_completed,
                'created_at' => $survey->created_at
            ],
            'responses' => $formattedResponses
        ]);
    }

    /**
     * Soumettre toutes les réponses d'un survey
     * POST /api/surveys/{token}/responses
     */
    public function store(Request $request, $token)
    {
        $survey = Survey::where('unique_token', $token)->first();

        if (!$survey) {
            return response()->json([
                'success' => false,
                'message' => 'Survey not found'
            ], 404);
        }

        if ($survey->is_completed) {
            return response()->json([
                'success' => false,
                'message' => 'Survey already completed'
            ], 400);
        }

        // Valider toutes les réponses
        $questions = Question::orderBy('number')->get();
        $validator = $this->validateAllResponses($request->all(), $questions);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Supprimer les anciennes réponses (si elles existent)
            $survey->responses()->delete();

            // Créer les nouvelles réponses
            foreach ($questions as $question) {
                $fieldName = "question_{$question->number}";
                $answer = $request->input($fieldName);

                // Créer une réponse même si elle est vide, sauf si elle est strictement null
                if ($answer !== null) {
                    // Convertir les réponses vides en chaîne vide
                    if ($answer === '') {
                        $answer = '';
                    }

                    Response::create([
                        'survey_id' => $survey->id,
                        'question_id' => $question->id,
                        'answer' => (string) $answer,
                    ]);
                }
            }

            // Marquer le survey comme terminé
            $survey->markAsCompleted();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Responses saved successfully',
                'survey' => [
                    'id' => $survey->id,
                    'token' => $survey->unique_token,
                    'response_url' => $survey->getResponseUrl(),
                    'is_completed' => $survey->is_completed,
                ],
                'total_responses' => $survey->responses()->count()
            ]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'success' => false,
                'message' => 'Error saving responses: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Consulter les réponses d'un survey
     * GET /api/surveys/{token}/responses
     */
    public function show($token)
    {
        $survey = Survey::where('unique_token', $token)->first();

        if (!$survey) {
            return response()->json([
                'success' => false,
                'message' => 'Survey not found'
            ], 404);
        }

        $responses = $survey->getResponsesWithQuestions();

        return response()->json([
            'success' => true,
            'survey' => [
                'id' => $survey->id,
                'email' => $survey->email,
                'token' => $survey->unique_token,
                'is_completed' => $survey->is_completed,
                'created_at' => $survey->created_at,
            ],
            'responses' => $responses->map(function ($response) {
                return [
                    'question_number' => $response->question->number,
                    'question_content' => $response->question->content,
                    'question_type' => $response->question->type,
                    'answer' => $response->getFormattedAnswer(),
                ];
            })
        ]);
    }

    /**
     * Obtenir toutes les questions pour le formulaire
     * GET /api/questions
     */
    public function getQuestions()
    {
        $questions = Question::orderBy('number')->get();

        return response()->json([
            'success' => true,
            'questions' => $questions->map(function ($question) {
                return [
                    'id' => $question->id,
                    'number' => $question->number,
                    'content' => $question->content,
                    'type' => $question->type,
                    'options' => $question->options,
                    'is_required' => $question->is_required,
                ];
            })
        ]);
    }

    /**
     * Valider toutes les réponses
     */
    private function validateAllResponses($data, $questions)
    {
        $rules = [];
        $messages = [];

        foreach ($questions as $question) {
            $fieldName = "question_{$question->number}";
            $rules[$fieldName] = $question->getValidationRules();
            $messages["{$fieldName}.required"] = "La question {$question->number} est obligatoire.";
            $messages["{$fieldName}.email"] = "L'adresse email n'est pas valide.";
            $messages["{$fieldName}.in"] = "La réponse sélectionnée n'est pas valide pour la question {$question->number}.";
            $messages["{$fieldName}.between"] = "La note doit être comprise entre 1 et 5.";
        }

        return Validator::make($data, $rules, $messages);
    }
}
