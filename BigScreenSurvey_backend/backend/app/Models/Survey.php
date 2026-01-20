<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Survey extends Model
{
    use HasFactory;

    protected $fillable = [
        'email',
        'unique_token',
        'is_completed',
    ];

    protected $casts = [
        'is_completed' => 'boolean',
    ];

    /**
     * Boot method to generate unique token automatically
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($survey) {
            if (empty($survey->unique_token)) {
                $survey->unique_token = $survey->generateUniqueToken();
            }
        });
    }

    /**
     * Generate a unique token for the survey
     */
    public function generateUniqueToken()
    {
        do {
            $token = Str::random(64);
        } while (self::where('unique_token', $token)->exists());

        return $token;
    }

    /**
     * Get the URL for consulting responses
     */
    public function getResponseUrl()
    {
        return url("/responses/{$this->unique_token}");
    }

    /**
     * Relation with responses (à créer plus tard)
     */
    public function responses()
    {
        return $this->hasMany(Response::class);
    }

    /**
     * Get response by question ID
     */
    public function getResponseByQuestion($questionId)
    {
        return $this->responses()->where('question_id', $questionId)->first();
    }

    /**
     * Check if survey is completed
     */
    public function isCompleted()
    {
        return $this->is_completed;
    }

    /**
     * Mark survey as completed
     */
    public function markAsCompleted()
    {
        $this->update(['is_completed' => true]);
    }

    /**
     * Get responses with their associated questions
     */
    public function getResponsesWithQuestions()
    {
        return $this->responses()
            ->with(['question' => function($query) {
                $query->orderBy('number');
            }])
            ->join('questions', 'responses.question_id', '=', 'questions.id')
            ->orderBy('questions.number')
            ->select('responses.*')
            ->get();
    }
}