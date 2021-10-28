<?php

namespace Mindz\LaravelActivityLog\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    const UPDATED_AT = null;

    protected $casts = [
        'old' => 'json',
        'new' => 'json',
    ];

    public static function log(Model $object, null|array $changes, string $event)
    {
        $data = [
            'event' => $event,
            'subject_type' => get_class($object),
            'subject_id' => $object->id,
            'old' => $changes['old'] ?? null,
            'new' => $changes['new'] ?? null,
        ];

        if (auth()->user()) {
            $data = array_merge($data, [
                'causer_type' => get_class(auth()->user()),
                'causer_id' => auth()->id()
            ]);
        }

        return static::forceCreate($data);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    public function causer(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopeCausedBy(Builder $query, Model $causer): Builder
    {
        return $query
            ->where('causer_type', $causer->getMorphClass())
            ->where('causer_id', $causer->getKey());
    }

    public function scopeForSubject(Builder $query, Model $subject): Builder
    {
        return $query
            ->where('subject_type', $subject->getMorphClass())
            ->where('subject_id', $subject->getKey());
    }
}
