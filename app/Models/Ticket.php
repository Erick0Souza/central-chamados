<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ticket extends Model
{
    public const STATUSES = [
        'aberto' => 'Aberto',
        'em_atendimento' => 'Em atendimento',
        'resolvido' => 'Resolvido',
        'encerrado' => 'Encerrado',
    ];

    public const PRIORITIES = [
        'baixa' => 'Baixa',
        'media' => 'Média',
        'alta' => 'Alta',
        'urgente' => 'Urgente',
    ];

    protected $fillable = [
        'title',
        'description',
        'category_id',
        'priority',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'user_id'
        );
    }

    public function assignee(): BelongsTo
    {
        return $this->belongsTo(
            User::class,
            'assigned_to'
        );
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(
            Category::class
        );
    }

    public function comments(): HasMany
    {
        return $this->hasMany(
            Comment::class
        );
    }

    public function activities(): HasMany
    {
        return $this->hasMany(
            Activity::class
        );
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(
            Attachment::class
        );
    }

    public function scopeVisibleTo(
        Builder $query,
        User $user
    ): Builder {
        return $user->isAdmin()
            ? $query
            : $query->where(
                function (Builder $q) use ($user) {
                    $q->where(
                        'user_id',
                        $user->id
                    );

                    if ($user->isStaff()) {
                        $q->orWhere(
                            'assigned_to',
                            $user->id
                        );
                    }
                }
            );
    }

    public function code(): string
    {
        return 'CH-'.str_pad(
            (string) $this->id,
            4,
            '0',
            STR_PAD_LEFT
        );
    }

    public function statusLabel(): string
    {
        return self::STATUSES[
            $this->status
        ];
    }

    public function priorityLabel(): string
    {
        return self::PRIORITIES[
            $this->priority
        ];
    }

    public function transitionsFor(
        User $user
    ): array {
        if (
            $user->isAdmin()
            || (
                $user->role === 'tecnico'
                && $this->assigned_to === $user->id
            )
        ) {
            return match ($this->status) {
                'aberto' => [
                    'em_atendimento',
                ],

                'em_atendimento' => [
                    'resolvido',
                ],

                'resolvido' => [
                    'encerrado',
                ],

                'encerrado' => [],

                default => [],
            };
        }

        if (
            $this->user_id === $user->id
            && $this->status === 'resolvido'
        ) {
            return [
                'encerrado',
            ];
        }

        return [];
    }
}