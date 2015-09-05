<?php

use Illuminate\Support\Facades\Config;
use Mockery as m;
use Mpociot\Teamwork\Traits\UserHasTeams;

class TeamInviteTest extends PHPUnit_Framework_TestCase
{

    public function tearDown()
    {
        m::close();
        Config::clearResolvedInstances();
    }


    public function testCanInitializeTeamInvite()
    {
        Config::shouldReceive('get')
            ->once()
            ->with('teamwork.team_invites_table')
            ->andReturn('team_invites');

        $teamInvite = new Mpociot\Teamwork\TeamInvite();
        $this->assertEquals( 'team_invites', $teamInvite->getTable() );
    }

}