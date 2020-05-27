<?php namespace Mpociot\Teamwork\Traits;

/**
 * This file is part of Teamwork
 *
 * @license MIT
 * @package Teamwork
 */

use Illuminate\Support\Facades\Config;

trait TeamworkTeamInviteTrait
{
    /**
     * Has-One relations with the team model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function team()
    {
        return $this->hasOne( Config::get( 'teamwork.team_model' ), 'id', 'team_id' );
    }

    /**
     * Has-One relations with the user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function user()
    {
        return $this->hasOne( Config::get( 'teamwork.user_model' ), 'email', 'email' );
    }

    /**
     * Has-One relations with the user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function inviter()
    {
        return $this->hasOne( Config::get( 'teamwork.user_model' ), 'id', 'user_id' );
    }

}
