<?php

namespace Mpociot\Teamwork\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class UserInvitedToTeam
{
    use SerializesModels;

    /**
     * @type Model
     */
    protected $invite;

    public function __construct($invite)
    {
        $this->invite = $invite;
    }

    /**
     * @return Model
     */
    public function getInvite()
    {
        return $this->invite;
    }

    /**
     * @return int
     */
    public function getTeamId()
    {
        return $this->invite->team_id;
    }
}