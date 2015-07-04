<?php

/**
 * This file is part of Teamwork
 *
 * @license MIT
 * @package Teamwork
 */

return [
    /*
    |--------------------------------------------------------------------------
    | Teamwork Team Model
    |--------------------------------------------------------------------------
    |
    | This is the Team model used by Teamwork to create correct relations.  Update
    | the team if it is in a different namespace.
    |
    */
    'team' => 'App\Team',

    /*
    |--------------------------------------------------------------------------
    | Teamwork teams Table
    |--------------------------------------------------------------------------
    |
    | This is the teams table name used by Teamwork to save teams to the database.
    |
    */
    'teams_table' => 'teams',

    /*
    |--------------------------------------------------------------------------
    | Teamwork team_user Table
    |--------------------------------------------------------------------------
    |
    | This is the team_user table used by Teamwork to save assigned teams to the
    | database.
    |
    */
    'team_user_table' => 'team_user',

    /*
    |--------------------------------------------------------------------------
    | User Foreign key on Teamwork's team_user Table (Pivot)
    |--------------------------------------------------------------------------
    */
    'user_foreign_key' => 'id'

];