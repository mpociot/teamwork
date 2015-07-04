<?php namespace Teamwork\Traits;

/**
 * This file is part of Teamwork
 *
 * @license MIT
 * @package Teamwork
 */

use Illuminate\Support\Facades\Config;

trait TeamworkTeamTrait
{

    /**
     * One-to-Many relation with the invite model
     * @return mixed
     */
    public function invites()
    {
        return $this->hasMany( Config::get('teamwork.invite_model'), 'id', 'team_id');
    }
    
    /**
     * Many-to-Many relations with the user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function users()
    {
        return $this->belongsToMany(Config::get('auth.model'), Config::get('teamwork.team_user_table'));
    }

    /**
     * Has-One relation with the user model.
     * This indicates the owner of the team
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function owner()
    {
        $userModel   = Config::get( 'auth.model' );
        $userKeyName = ( new $userModel() )->getKeyName();
        return $this->hasOne(Config::get('auth.model'), $userKeyName, "owner_id");
    }

}