<?php

namespace Mpociot\Teamwork\Traits;

use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Config;

/**
 * Class UsedByTeams.
 */
trait UsedByTeams
{
    /**
     * Boot the global scope.
     */
    protected static function bootUsedByTeams()
    {
        static::addGlobalScope(
            'team', function (Builder $builder) {
                static::teamGuard();

                $builder->where($builder->getQuery()->from.'.team_id', auth()->user()->currentTeam->getKey());
            }
        );

        static::saving(
            function (Model $model) {
                if (! isset($model->team_id)) {
                    static::teamGuard();

                    $model->team_id = auth()->user()->currentTeam->getKey();
                }
            }
        );
    }

    /**
     * @param  Builder $query
     * @return Builder
     */
    public function scopeAllTeams(Builder $query)
    {
        return $query->withoutGlobalScope('team');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function team()
    {
        return $this->belongsTo(Config::get('teamwork.team_model'));
    }

    /**
     * @throws Exception
     */
    protected static function teamGuard()
    {
        if (auth()->guest() || ! auth()->user()->currentTeam) {
            throw new Exception('No authenticated user with selected team present.');
        }
    }
}
