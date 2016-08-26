<?php

namespace Mpociot\Teamwork\Events;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Queue\SerializesModels;

class UserLeftTeam
{
    use SerializesModels;

    /**
     * @type Model
     */
    protected $user;

    /**
     * @type int
     */
    protected $team_id;

    public function __construct($user, $team_id)
    {
        $this->user = $user;
        $this->team_id = $team_id;
    }

    /**
     * @return Model
     */
    public function getUser()
    {
        return $this->user;
    }

    /**
     * @return int
     */
    public function getTeamId()
    {
        return $this->team_id;
    }
}
