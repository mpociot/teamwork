<?php namespace Mpociot\Teamwork\Traits;

/**
 * This file is part of Teamwork,
 *
 * @license MIT
 * @package Teamwork
 */

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Config;
use Mpociot\Teamwork\Exceptions\UserNotInTeamException;

trait UserHasTeams
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
     * @return mixed
     */
    public function ownedTeams()
    {
        return $this->teams()->where( "owner_id", "=", $this->getKey() );
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
    public static function bootUserHasTeams()
    {
        static::deleting( function ( Model $user )
        {
            if ( !method_exists( \Config::get( 'auth.model' ), 'bootSoftDeletes' ) )
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
     * Wrapper method for "isOwner"
     *
     * @return bool
     */
    public function isTeamOwner()
    {
        return $this->isOwner();
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
        $teamModel   = \Config::get( 'teamwork.team_model' );
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
     * @return $this
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
        return $this;
    }

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param mixed $team
     * @return $this
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
        return $this;
    }

    /**
     * Attach multiple teams to a user
     *
     * @param mixed $teams
     * @return $this
     */
    public function attachTeams( $teams )
    {
        foreach ( $teams as $team )
        {
            $this->attachTeam( $team );
        }
        return $this;
    }

    /**
     * Detach multiple teams from a user
     *
     * @param mixed $teams
     * @return $this
     */
    public function detachTeams( $teams )
    {
        foreach ( $teams as $team )
        {
            $this->detachTeam( $team );
        }
        return $this;
    }

    /**
     * Switch the current team of the user
     *
     * @param object|array|integer $team
     * @return $this
     * @throws ModelNotFoundException
     * @throws UserNotInTeamException
     */
    public function switchTeam( $team )
    {
        if( $team !== 0 && $team !== null )
        {
            if ( is_object( $team ) && method_exists( $team, 'getKey' ) )
            {
                $team = $team->getKey();
            }
            if ( is_array( $team ) && isset( $team[ "id" ] ) )
            {
                $team = $team[ "id" ];
            }
            $teamModel   = \Config::get( 'teamwork.team_model' );
            $teamObject  = ( new $teamModel() )->find( $team );
            if( !$teamObject )
            {
                $exception = new ModelNotFoundException();
                $exception->setModel( $teamModel );
                throw $exception;
            }
            if( !$teamObject->users->contains( $this->getKey() ) )
            {
                $exception = new UserNotInTeamException();
                $exception->setTeam( $teamObject->name );
                throw $exception;
            }
        }
        $this->current_team_id = $team;
        $this->save();
        return $this;
    }
}
