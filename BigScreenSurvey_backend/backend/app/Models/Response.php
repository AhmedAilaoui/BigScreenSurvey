<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Response extends Model
{
    use HasFactory;

    protected $fillable = [
        'survey_id',
        'question_id',
        'answer',
    ];

    /**
     * Relation avec le survey
     */
    public function survey()
    {
        return $this->belongsTo(Survey::class);
    }

    /**
     * Relation avec la question
     */
    public function question()
    {
        return $this->belongsTo(Question::class);
    }

    /**
     * Obtenir la réponse formatée selon le type de question
     */
    public function getFormattedAnswer()
    {
        $question = $this->question;

        switch ($question->type) {
            case 'A': // Choix multiple - retourner tel quel
                return $this->answer;
            case 'B': // Texte - retourner tel quel
                return $this->answer;
            case 'C': // Échelle - convertir en entier
                return (int) $this->answer;
            default:
                return $this->answer;
        }
    }
}
