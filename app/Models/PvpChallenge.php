<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PvpChallenge extends Model
{
    protected $fillable = [
        'title', 'value_id', 'questions', 'time_limit', 'difficulty', 'is_active', 'created_by',
    ];

    protected $casts = [
        'questions' => 'array',
        'is_active' => 'boolean',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function matches()
    {
        return $this->hasMany(PvpMatch::class, 'challenge_id');
    }

    public function value()
    {
        return $this->belongsTo(Value::class, 'value_id');
    }

    /**
     * Scope: تحديات متاحة لطالب مدرسة معينة.
     * - إذا value_id = null → تحدي عام لكل المدارس
     * - وإلا → فقط إن كانت القيمة مفعّلة لمدرسة الطالب
     */
    public function scopeAvailableForSchool($query, ?int $schoolId)
    {
        if (! $schoolId) {
            return $query->where('is_active', true)->whereNull('value_id');
        }

        return $query->where('is_active', true)
            ->where(function ($q) use ($schoolId) {
                $q->whereNull('value_id')
                    ->orWhereHas('value', function ($vq) use ($schoolId) {
                        $vq->visibleForSchool($schoolId);
                    });
            });
    }

    public function getFullQuestionsAttribute()
    {
        $ids = $this->questions ?? [];

        return QuestionBank::whereIn('id', $ids)->get();
    }

    public function getQuestionCountAttribute()
    {
        return count($this->questions ?? []);
    }

    /**
     * توحيد الأسئلة إلى صيغة واحدة بغضّ النظر عن طريقة التخزين:
     *  - الصيغة الجديدة: مصفوفة كائنات {text,type,options,correct,points} (منشأة inline)
     *  - الصيغة القديمة: مصفوفة معرّفات تشير إلى QuestionBank
     * تُعيد Collection من مصفوفات موحّدة: [key,text,type,options,correct,points]
     */
    public function normalizedQuestions()
    {
        $qs = $this->questions ?? [];
        if (empty($qs)) {
            return collect();
        }

        // الصيغة الجديدة: أول عنصر مصفوفة (كائن سؤال)
        if (is_array($qs[0] ?? null)) {
            return collect($qs)->values()->map(function ($q, $i) {
                $type = $q['type'] ?? 'multiple_choice';
                $options = [];
                foreach (($q['options'] ?? []) as $opt) {
                    $options[] = ['text' => is_array($opt) ? ($opt['text'] ?? '') : (string) $opt];
                }

                return [
                    'key' => 'q' . $i,
                    'text' => $q['text'] ?? '',
                    'type' => $type,
                    'options' => $options,
                    'correct' => $q['correct'] ?? null, // فهرس (MC) أو 'true'/'false' (TF)
                    'points' => (int) ($q['points'] ?? 100),
                ];
            });
        }

        // الصيغة القديمة: معرّفات من بنك الأسئلة
        $bank = QuestionBank::whereIn('id', $qs)->get()->keyBy('id');

        return collect($qs)->map(function ($id) use ($bank) {
            $q = $bank->get($id);
            if (! $q) {
                return null;
            }
            $rawOpts = is_string($q->options) ? json_decode($q->options, true) : ($q->options ?? []);
            $options = [];
            $correct = null;
            foreach ((array) $rawOpts as $i => $opt) {
                $options[] = ['text' => is_array($opt) ? ($opt['text'] ?? '') : (string) $opt];
                if (is_array($opt) && ! empty($opt['is_correct'])) {
                    $correct = $i;
                }
            }

            return [
                'key' => (string) $q->id,
                'text' => $q->question_text ?? $q->title ?? '',
                'type' => $q->question_type ?? 'multiple_choice',
                'options' => $options,
                'correct' => ($q->question_type ?? '') === 'true_false' ? ($q->correct_answer ?? null) : $correct,
                'points' => 100,
            ];
        })->filter()->values();
    }
}
