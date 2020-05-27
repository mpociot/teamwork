<?php namespace Mpociot\Teamwork\Contracts;

/**
 * This file is part of Teamwork
 *
 * @license MIT
 * @package Teamwork
 */
interface TeamworkUserInterface
{
    /**
     * Many-to-Many relations with the user model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function teams();

    /**
     * has-one relation with the current selected team model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function currentTeam();

    /**
     * One-to-Many relation with the invite model
     * @return mixed
     */
    public function invites();


    /**
     * Returns if the user owns a team
     *
     * @return bool
     */
    public function isOwner();


    /**
     * Returns if the user owns the given team
     *
     * @param mixed $team
     * @return bool
     */
    public function isOwnerOfTeam( $team );

    /**
     * Alias to eloquent many-to-many relation's attach() method.
     *
     * @param mixed $team
     * @param array $pivotData
     */
    public function attachTeam( $team, $pivotData = [] );

    /**
     * Alias to eloquent many-to-many relation's detach() method.
     *
     * @param mixed $team
     */
    public function detachTeam( $team );

    /**
     * Attach multiple teams to a user
     *
     * @param mixed $teams
     */
    public function attachTeams( $teams );

    /**
     * Detach multiple teams from a user
     *
     * @param mixed $teams
     */
    public function detachTeams( $teams );

    /**
     * Switch the current team of the user
     *
     * @param object|array|integer $team
     * @throws ModelNotFoundException
     * @throws UserNotInTeamException
     */
    public function switchTeam( $team );
}
