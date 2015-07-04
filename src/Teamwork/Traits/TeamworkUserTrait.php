<?php namespace Teamwork\Traits;

/**
 * This file is part of Entrust,
 *
 * @license MIT
 * @package Teamwork
 */

use Illuminate\Support\Facades\Config;

trait TeamworkUserTrait
{
    /**
     * Many-to-Many relations with the user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function teams()
    {
        return $this->belongsToMany(Config::get('teamwork.team'), Config::get('teamwork.team_user_table'), Config::get('teamwork.user_foreign_key'), 'team_id');
    }

    /**
     * Returns if the user owns a team
     *
     * @return bool
     */
    public function isOwner()
    {
        return ( $this->teams()->where( "owner_id", "=" , $this->getKey() )->first() ) ? true : false;
    }


    /**
     * Returns if the user owns the given team
     *
     * @param int $team_id
     * @return bool
     */
    public function isOwnerOfTeam( $team_id )
    {
        $teamModel   = Config::get('teamwork.team');
        $teamKeyName = (new $teamModel())->getKeyName();
        return ( (new $teamModel)
                        ->where( "owner_id", "=" , $this->getKey() )
                        ->where( $teamKeyName, "=" , $team_id )->first()
        ) ? true : false;
    }

    /**
     * Boot the user model
     * Attach event listener to remove the many-to-many records when trying to delete
     * Will NOT delete any records if the user model uses soft deletes.
     *
     * @return void|bool
     */
    public static function boot()
    {
        parent::boot();
        static::deleting(function($user) {
            if (!method_exists(Config::get('auth.model'), 'bootSoftDeletingTrait')) {
                $user->teams()->sync([]);
            }
            return true;
        });
    }

}