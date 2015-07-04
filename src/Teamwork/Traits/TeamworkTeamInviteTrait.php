<?php namespace Teamwork\Traits;

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
        return $this->hasOne( Config::get( 'teamwork.team_model' ), 'team_id', 'team_id' );
    }

    /**
     * Has-One relations with the zser model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function user()
    {
        return $this->hasOne( Config::get( 'auth.model' ), Config::get( 'teamwork.user_foreign_key' ), 'user_id' );
    }

}