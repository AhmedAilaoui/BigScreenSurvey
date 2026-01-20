<?php

namespace App\Http\Controllers;

use App\Models\Question;
use App\Models\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StatisticsController extends Controller
{
    /**
     * Obtenir toutes les statistiques pour le dashboard admin
     * GET /api/admin/statistics
     */
    public function index()
    {
        try {
            $stats = [
                'equipment' => $this->getEquipmentStats(),
                'quality' => $this->getQualityStats(),
                'summary' => $this->getSummaryStats(),
            ];

            return response()->json([
                'success' => true,
                'statistics' => $stats
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching statistics: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Statistiques équipement (Questions 6, 7, 10) - Pie Charts
     */
    private function getEquipmentStats()
    {
        $equipmentQuestions = [6, 7, 10];
        $stats = [];

        foreach ($equipmentQuestions as $questionNumber) {
            $question = Question::where('number', $questionNumber)->first();

            if ($question) {
                $responses = Response::whereHas('question', function ($query) use ($questionNumber) {
                    $query->where('number', $questionNumber);
                })
                    ->select('answer', DB::raw('count(*) as count'))
                    ->groupBy('answer')
                    ->get();

                $stats["question_{$questionNumber}"] = [
                    'question' => $question->content,
                    'type' => 'pie',
                    'data' => $responses->map(function ($response) {
                        return [
                            'label' => $response->answer,
                            'value' => $response->count
                        ];
                    })
                ];
            }
        }

        return $stats;
    }

    /**
     * Statistiques qualité (Questions 11-15) - Radar Chart
     */
    private function getQualityStats()
    {
        $qualityQuestions = [11, 12, 13, 14, 15];
        $qualityLabels = [
            11 => 'Qualité image',
            12 => 'Confort interface',
            13 => 'Connexion réseau',
            14 => 'Graphismes 3D',
            15 => 'Qualité audio'
        ];

        $radarData = [];

        foreach ($qualityQuestions as $questionNumber) {
            $question = Question::where('number', $questionNumber)->first();

            if ($question) {
                $average = Response::whereHas('question', function ($query) use ($questionNumber) {
                    $query->where('number', $questionNumber);
                })
                    ->avg(DB::raw('CAST(answer AS SIGNED)'));

                $radarData[] = [
                    'label' => $qualityLabels[$questionNumber],
                    'value' => round($average, 2)
                ];
            }
        }

        return [
            'type' => 'radar',
            'data' => $radarData,
            'max' => 5 // Échelle de 1 à 5
        ];
    }

    /**
     * Statistiques générales
     */
    private function getSummaryStats()
    {
        $totalSurveys = DB::table('surveys')->count();
        $completedSurveys = DB::table('surveys')->where('is_completed', true)->count();
        $totalResponses = DB::table('responses')->count();
        $avgCompletionTime = DB::table('surveys')
            ->where('is_completed', true)
            ->avg(DB::raw('TIMESTAMPDIFF(MINUTE, created_at, updated_at)'));

        return [
            'total_surveys' => $totalSurveys,
            'completed_surveys' => $completedSurveys,
            'completion_rate' => $totalSurveys > 0 ? round(($completedSurveys / $totalSurveys) * 100, 1) : 0,
            'total_responses' => $totalResponses,
            'avg_completion_time_minutes' => round($avgCompletionTime, 1),
        ];
    }

    /**
     * Obtenir les données pour un graphique spécifique
     * GET /api/admin/statistics/{type}
     */
    public function getSpecificStats($type)
    {
        try {
            switch ($type) {
                case 'equipment':
                    $data = $this->getEquipmentStats();
                    break;
                case 'quality':
                    $data = $this->getQualityStats();
                    break;
                case 'summary':
                    $data = $this->getSummaryStats();
                    break;
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Invalid statistics type'
                    ], 400);
            }

            return response()->json([
                'success' => true,
                'statistics' => $data
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error fetching statistics: ' . $e->getMessage()
            ], 500);
        }
    }
}