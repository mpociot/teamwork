<?php

use Illuminate\Support\Facades\Config;
use Mockery as m;
use Mpociot\Teamwork\Traits\UserHasTeams;

class TeamworkTeamTest extends PHPUnit_Framework_TestCase
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
            ->with('teamwork.teams_table')
            ->andReturn('teams');

        $team = new Mpociot\Teamwork\TeamworkTeam();
        $this->assertEquals( 'teams', $team->getTable() );
    }

}