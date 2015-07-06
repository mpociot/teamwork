<?php namespace Mpociot\Teamwork\Traits;

/**
 * This file is part of Teamwork,
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
        return $this->belongsToMany( \Config::get( 'teamwork.team_model' ),\Config::get( 'teamwork.team_user_table' ), 'user_id', 'team_id' );
    }

    /**
     * has-one relation with the current selected team model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function currentTeam()
    {
        return $this->hasOne( \Config::get( 'teamwork.team_model' ), 'id', 'current_team_id' );
    }

    /**
     * One-to-Many relation with the invite model
     * @return mixed
     */
    public function invites()
    {
        return $this->hasMany( \Config::get('teamwork.invite_model'), 'email', 'email' );
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
        static::deleting( function ( $user )
        {
            if ( !method_exists( \Config::get( 'auth.model' ), 'bootSoftDeletingTrait' ) )
            {
                $user->teams()->sync( [ ] );
            }
            return true;
        } );
    }


    /**
     * Returns if the user owns a team
     *
     * @return bool
     */
    public function isOwner()
    {
        return ( $this->teams()->where( "owner_id", "=", $this->getKey() )->first() ) ? true : false;
    }


    /**
     * Returns if the user owns the given team
     *
     * @param mixed $team
     * @return bool
     */
    public function isOwnerOfTeam( $team )
    {
        if ( is_object( $team ) && method_exists( $team, 'getKey' ) )
        {
            $team = $team->getKey();
        }
        if ( is_array( $team ) && isset( $team[ "id" ] ) )
        {
            $team = $team[ "id" ];
        }
        $teamModel   = \Config::get( 'teamwork.team' );
        $teamKeyName = ( new $teamModel() )->getKeyName();
        return ( ( new $teamModel )
            ->where( "owner_id", "=", $this->getKey() )
            ->where( $teamKeyName, "=", $team )->first()
        ) ? true : false;
    }

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param mixed $team
     */
    public function attachTeam( $team )
    {
        if ( is_object( $team ) && method_exists( $team, 'getKey' ) )
        {
            $team = $team->getKey();
        }
        if ( is_array( $team ) && isset( $team[ "id" ] ) )
        {
            $team = $team[ "id" ];
        }
        /**
         * If the user has no current team,
         * use the attached one
         */
        if( is_null( $this->current_team_id ) )
        {
            $this->current_team_id = $team;
            $this->save();
        }
        if( !$this->teams->contains( $team ) )
        {
            $this->teams()->attach( $team );
        }
    }

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param mixed $team
     */
    public function detachTeam( $team )
    {
        if ( is_object( $team ) && method_exists( $team, 'getKey' ) )
        {
            $team = $team->getKey();
        }
        if ( is_array( $team ) && isset( $team[ "id" ] ) )
        {
            $team = $team[ "id" ];
        }
        $this->teams()->detach( $team );
        /**
         * If the user has no more teams,
         * unset the current_team_id
         */
        if( count( $this->teams ) === 0 )
        {
            $this->current_team_id = null;
            $this->save();
        }
    }

    /**
     * Attach multiple teams to a user
     *
     * @param mixed $teams
     */
    public function attachTeams( $teams )
    {
        foreach ( $teams as $team )
        {
            $this->attachTeam( $team );
        }
    }

    /**
     * Detach multiple teams from a user
     *
     * @param mixed $teams
     */
    public function detachTeams( $teams )
    {
        foreach ( $teams as $team )
        {
            $this->detachTeam( $team );
        }
    }

    /**
     * Switch the current team of the user
     *
     * @param object|array|integer $team
     */
    public function switchTeam( $team )
    {
        if ( is_object( $team ) && method_exists( $team, 'getKey' ) )
        {
            $team = $team->getKey();
        }
        if ( is_array( $team ) && isset( $team[ "id" ] ) )
        {
            $team = $team[ "id" ];
        }
        $this->current_team_id = $team;
        $this->save();
    }
}