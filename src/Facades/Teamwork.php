<?php

namespace Mpociot\Teamwork\Facades;

use Mpociot\Teamwork\TeamInvite;
use Illuminate\Support\Facades\Facade;

/**
 * Class Teamwork.
 *
 * @method static user()
 * @method static inviteToTeam($user, $team = null, callable $success = null)
 * @method static hasPendingInvite($email, $team)
 * @method static getInviteFromAcceptToken($token)
 * @method static acceptInvite(TeamInvite $invite)
 * @method static getInviteFromDenyToken($token)
 * @method static denyInvite(TeamInvite $invite)
 *
 * @see \Mpociot\Teamwork\Teamwork
 */
class Teamwork extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'teamwork';
    }
}
