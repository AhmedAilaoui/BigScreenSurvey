<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Question extends Model
{
    use HasFactory;

    protected $fillable = [
        'number',
        'content',
        'type',
        'options',
        'is_required',
    ];

    protected $casts = [
        'options' => 'array',
        'is_required' => 'boolean',
    ];

    /**
     * Relation avec les réponses
     */
    public function responses()
    {
        return $this->hasMany(Response::class);
    }

    /**
     * Vérifier si c'est un choix multiple
     */
    public function isMultipleChoice()
    {
        return $this->type === 'A';
    }

    /**
     * Vérifier si c'est un champ texte
     */
    public function isTextInput()
    {
        return $this->type === 'B';
    }

    /**
     * Vérifier si c'est une échelle numérique
     */
    public function isScaleRating()
    {
        return $this->type === 'C';
    }

    /**
     * Obtenir les règles de validation Laravel
     */
    public function getValidationRules()
    {
        $rules = [];

        if ($this->is_required) {
            $rules[] = 'required';
        }

        switch ($this->type) {
            case 'A': // Choix multiple
                $rules[] = 'string';
                $rules[] = 'in:' . implode(',', $this->options);
                break;
            case 'B': // Champ texte
                if ($this->number === 1) { // Email question
                    $rules[] = 'email';
                }
                $rules[] = 'string';
                $rules[] = 'max:255';
                break;
            case 'C': // Échelle 1-5
                $rules[] = 'integer';
                $rules[] = 'between:1,5';
                break;
        }

        return $rules;
    }
}
