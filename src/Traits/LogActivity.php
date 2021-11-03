<?php

namespace Mindz\LaravelActivityLog\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\App;
use Mindz\LaravelActivityLog\Helpers\ArrayDiffMultidimensional;
use Mindz\LaravelActivityLog\Models\Activity;

trait LogActivity
{
    public static $old;

    public static function bootLogActivity()
    {
        if (App::environment('testing')) {
            return;
        }

        static::saving(function (Model $model) {
            $changes = array_merge($model->getAttributes(), $model->getRawOriginal());
            $originalModel = $model->replicate()->setRawAttributes($changes);

            if (!$model->exists) {
                return;
            }

            $originalModelBlueprint = $originalModel->logBlueprint();

            if ($originalModelBlueprint instanceof JsonResource) {
                $originalModelBlueprint = static::convertResourceToArray($originalModelBlueprint);
            }

            static::$old = $originalModelBlueprint;
        });

        static::saved(function (Model $model) {
            $delta = $model->getDelta(static::$old, $model->logBlueprint());
            $model->logChanges($delta, $model->wasRecentlyCreated ? 'created' : 'updated');
        });

        static::deleted(function (Model $model) {
            $model->logChanges(null, 'deleted');
        });
    }

    public static function convertResourceToArray(\Illuminate\Http\Resources\Json\JsonResource $jsonResource)
    {
        return json_decode($jsonResource->toJson(), true);
    }

    public function logBlueprint(): array|JsonResource
    {
        if (method_exists($this, 'logStructure')) {
            return $this->logStructure();
        }

        return Arr::except($this->getAttributes(), [$this->getUpdatedAtColumn()]);
    }

    public function getDelta(null|array|JsonResource $old, null|array|JsonResource $current)
    {
        if ($current instanceof JsonResource) {
            $current = static::convertResourceToArray($current);
        }

        $diffOld = $old ? ArrayDiffMultidimensional::compare($old, $current) : null;
        $diffCurrent = $current ? ArrayDiffMultidimensional::compare($current ?? [], $old) : null;

        return [
            'old' => $diffOld,
            'new' => $diffCurrent,
        ];
    }

    public function logChanges($changes, $event)
    {
        if (is_null($changes) && $event == 'deleted') {
            return Activity::log($this, null, $event);
        }

        if (is_null($changes['old']) && is_null($changes['new'])) {
            throw new \Exception("old and new values cannot be empty at the same time");
        }

        if (!$changes['old'] && !$changes['new']) {
            return;
        }

        Activity::log($this, $changes, $event);
    }
}
