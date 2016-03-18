<?php

use Illuminate\Support\Facades\Config;
use Mockery as m;
use Mpociot\Teamwork\Traits\TeamworkTeamInviteTrait;

class TeamworkTeamInviteTraitTest extends PHPUnit_Framework_TestCase
{

    public function tearDown()
    {
        m::close();
    }

    public function testGetTeams()
    {
        Config::shouldReceive('get')
            ->once()
            ->with('teamwork.team_model')
            ->andReturn('Team');

        $stub = m::mock( 'TestUserTeamInviteTraitStub[hasOne]' );
        $stub->shouldReceive('hasOne')
            ->once()
            ->with('Team', 'id', 'team_id' )
            ->andReturn( [] );
        $this->assertEquals( [], $stub->team() );
    }

    public function testGetUser()
    {
        Config::shouldReceive('get')
            ->once()
            ->with('teamwork.user_model')
            ->andReturn('User');

        $stub = m::mock( 'TestUserTeamInviteTraitStub[hasOne]' );
        $stub->shouldReceive('hasOne')
            ->once()
            ->with('User', 'email', 'email' )
            ->andReturn( [] );
        $this->assertEquals( [], $stub->user() );
    }

}

class TestUserTeamInviteTraitStub extends Illuminate\Database\Eloquent\Model {

    use TeamworkTeamInviteTrait;
}