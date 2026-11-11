<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PvpMatch extends Model
{
    protected $fillable = [
        'challenge_id', 'player1_id', 'player2_id',
        'player1_answers', 'player2_answers',
        'player1_score', 'player2_score',
        'player1_time', 'player2_time',
        'winner_id', 'status', 'started_at', 'completed_at',
    ];

    protected $casts = [
        'player1_answers' => 'array',
        'player2_answers' => 'array',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function challenge()
    {
        return $this->belongsTo(PvpChallenge::class, 'challenge_id');
    }

    public function player1()
    {
        return $this->belongsTo(User::class, 'player1_id');
    }

    public function player2()
    {
        return $this->belongsTo(User::class, 'player2_id');
    }

    public function winner()
    {
        return $this->belongsTo(User::class, 'winner_id');
    }

    /**
     * التحدّيات تُفصل بالجنس: لاعبا المباراة من جنسٍ واحد (بنون مع بنين، بنات مع بنات).
     * غير المحدَّد (null) لا يختلط بمحدَّد أبداً — خلافاً لـg() التي تعامله مذكّراً — كي لا تظهر
     * طالبةٌ بلا جنس مسجَّل للذكور. اللاعب الثاني قد يغيب (مباراة waiting).
     */
    public function scopeAmongGender($query, ?string $gender)
    {
        return $query
            ->whereHas('player1', fn ($p) => $p->where('gender', $gender))
            ->where(fn ($w) => $w->whereNull('player2_id')
                ->orWhereHas('player2', fn ($p) => $p->where('gender', $gender)));
    }

    public function isWaiting()
    {
        return $this->status === 'waiting';
    }

    public function isPlaying()
    {
        return $this->status === 'playing';
    }

    public function isCompleted()
    {
        return $this->status === 'completed';
    }

    // تحديد الفائز — atomic مع lockForUpdate لمنع race condition
    public function determineWinner()
    {
        \DB::transaction(function () {
            $fresh = static::lockForUpdate()->find($this->id);
            if (! $fresh || $fresh->status === 'completed') {
                return; // تم تحديد الفائز مسبقًا — تجاهل
            }

            if ($fresh->player1_score > $fresh->player2_score) {
                $fresh->winner_id = $fresh->player1_id;
            } elseif ($fresh->player2_score > $fresh->player1_score) {
                $fresh->winner_id = $fresh->player2_id;
            } elseif ($fresh->player1_time < $fresh->player2_time) {
                $fresh->winner_id = $fresh->player1_id;
            } elseif ($fresh->player2_time < $fresh->player1_time) {
                $fresh->winner_id = $fresh->player2_id;
            }

            $fresh->status = 'completed';
            $fresh->completed_at = now();
            $fresh->save();

            $this->setRawAttributes($fresh->getAttributes(), true);
        });
    }
}
