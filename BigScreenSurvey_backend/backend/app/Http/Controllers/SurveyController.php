<?php

namespace App\Http\Controllers;

use App\Models\Survey;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SurveyController extends Controller
{
    /**
     * Store a new survey with basic info (email)
     * POST /api/surveys
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $survey = Survey::create([
                'email' => $request->email,
            ]);

            return response()->json([
                'success' => true,
                'survey' => [
                    'id' => $survey->id,
                    'email' => $survey->email,
                    'token' => $survey->unique_token,
                    'response_url' => $survey->getResponseUrl(),
                    'is_completed' => $survey->is_completed,
                    'created_at' => $survey->created_at
                ],
                'message' => 'Survey created successfully'
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error creating survey: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Complete a survey (when user submits all responses)
     * PUT /api/surveys/{token}/complete
     */
    public function complete($token)
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

        try {
            $survey->markAsCompleted();

            return response()->json([
                'success' => true,
                'survey' => [
                    'id' => $survey->id,
                    'email' => $survey->email,
                    'token' => $survey->unique_token,
                    'response_url' => $survey->getResponseUrl(),
                    'is_completed' => $survey->is_completed,
                    'completed_at' => $survey->updated_at
                ],
                'message' => 'Survey completed successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error completing survey: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get survey by token (for response consultation)
     * GET /api/surveys/{token}
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

        return response()->json([
            'success' => true,
            'survey' => [
                'id' => $survey->id,
                'email' => $survey->email,
                'token' => $survey->unique_token,
                'response_url' => $survey->getResponseUrl(),
                'is_completed' => $survey->is_completed,
                'created_at' => $survey->created_at,
                'updated_at' => $survey->updated_at
            ]
        ]);
    }

    /**
     * Get all surveys (for admin)
     * GET /api/surveys
     */
    public function index()
    {
        try {
            $surveys = Survey::orderBy('created_at', 'desc')->get();

            return response()->json([
                'success' => true,
                'surveys' => $surveys->map(function ($survey) {
                    return [
                        'id' => $survey->id,
                        'email' => $survey->email,
                        'token' => $survey->unique_token,
                        'response_url' => $survey->getResponseUrl(),
                        'is_completed' => $survey->is_completed,
                        'created_at' => $survey->created_at,
                        'updated_at' => $survey->updated_at
                    ];
                }),
                'total' => $surveys->count()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching surveys: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete a survey (for admin)
     * DELETE /api/surveys/{token}
     */
    public function destroy($token)
    {
        $survey = Survey::where('unique_token', $token)->first();

        if (!$survey) {
            return response()->json([
                'success' => false,
                'message' => 'Survey not found'
            ], 404);
        }

        try {
            $survey->delete();

            return response()->json([
                'success' => true,
                'message' => 'Survey deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error deleting survey: ' . $e->getMessage()
            ], 500);
        }
    }
}