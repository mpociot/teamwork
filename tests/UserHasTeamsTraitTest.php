<?php

use Illuminate\Support\Facades\Config;
use Mockery as m;
use Mpociot\Teamwork\Traits\UserHasTeams;

class UserHasTeamsTraitTest extends PHPUnit_Framework_TestCase
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

        Config::shouldReceive('get')
            ->once()
            ->with('teamwork.team_user_table')
            ->andReturn('team_user');

        $stub = m::mock( 'TestUserHasTeamsTraitStub[belongsToMany]' );
        $stub->shouldReceive('belongsToMany')
            ->once()
            ->with('Team', 'team_user', 'user_id', 'team_id' )
            ->andReturn( [] );
        $this->assertEquals( [], $stub->teams() );
    }

    public function testGetCurrentTeam()
    {
        Config::shouldReceive('get')
            ->once()
            ->with('teamwork.team_model')
            ->andReturn('Team');

        $stub = m::mock( 'TestUserHasTeamsTraitStub[hasOne]' );
        $stub->shouldReceive('hasOne')
            ->once()
            ->with('Team', 'id', 'current_team_id'  )
            ->andReturn( [] );
        $this->assertEquals( [], $stub->currentTeam() );
    }

    public function testGetOwnedTeams()
    {
        Config::shouldReceive('get')
            ->once()
            ->with('teamwork.team_model')
            ->andReturn('Team');

        $stub = m::mock( 'TestUserHasTeamsTraitStub[teams,where,getKey]' );
        $stub->shouldReceive('teams')->andReturnSelf();
        $stub->shouldReceive('where')
            ->once()
            ->with('owner_id', '=', 'getKey'  )
            ->andReturn( [] );
        $stub->shouldReceive('getKey')
            ->once()
            ->andReturn( 'getKey' );
        $this->assertEquals( [], $stub->ownedTeams() );
    }


    public function testGetInvites()
    {
        Config::shouldReceive('get')
            ->once()
            ->with('teamwork.invite_model')
            ->andReturn('Invite');

        $stub = m::mock( 'TestUserHasTeamsTraitStub[hasMany]' );
        $stub->shouldReceive('hasMany')
            ->once()
            ->with('Invite', 'email', 'email'  )
            ->andReturn( [] );
        $this->assertEquals( [], $stub->invites() );
    }


    public function testIsTeamOwner()
    {
        $stub = m::mock( 'TestUserHasTeamsTraitStub[isOwner]' );
        $stub->shouldReceive('isOwner')
            ->once()
            ->andReturn( false );
        $this->assertFalse( $stub->isTeamOwner() );
    }

    public function testAttachTeams()
    {
        $teams = array(1,2,3,4,5,6,7,8);
        $stub = m::mock( 'TestUserHasTeamsTraitStub[attachTeam]' );
        $stub->shouldReceive('attachTeam')
            ->times(count($teams));
        $stub->attachTeams($teams);
    }


    public function testDetachTeams()
    {
        $teams = array(1,2,3,4,5,6,7,8);
        $stub = m::mock( 'TestUserHasTeamsTraitStub[detachTeam]' );
        $stub->shouldReceive('detachTeam')
            ->times(count($teams));
        $stub->detachTeams($teams);
    }

    public function testRetrieveTeamIdReturnsInteger()
    {
        $stub = m::mock( 'TestUserHasTeamsTraitStub' );
        $this->assertEquals(1, $stub->retrieveTeamId( 1 ) );
    }

    public function testRetrieveTeamIdReturnsArrayKey()
    {
        $stub = m::mock( 'TestUserHasTeamsTraitStub' );
        $this->assertEquals(1, $stub->retrieveTeamId( ["id" => 1] ) );
    }

    public function testRetrieveTeamIdReturnsObjectKey()
    {
        $team_id = 1;
        $team = m::mock('stdClass');
        $team->shouldReceive('getKey')->once()->andReturn( $team_id );
        $stub = m::mock( 'TestUserHasTeamsTraitStub' );
        $this->assertEquals($team_id, $stub->retrieveTeamId( $team ) );
    }

    public function testIsOwner()
    {
        $stub = m::mock( 'TestUserHasTeamsTraitStub[teams,first,getKey]' );
        $stub->shouldReceive('first')
            ->once()
            ->andReturn( true );

        $stub->shouldReceive('getKey')
            ->once()
            ->andReturn( "key" );

        $stub->shouldReceive('where')
            ->once()
            ->with( "owner_id" , "=", "key" )
            ->andReturnSelf();

        $stub->shouldReceive('teams')
            ->andReturnSelf();

        $this->assertTrue( $stub->isOwner() );
    }


}

class TestUserHasTeamsTraitStub extends Illuminate\Database\Eloquent\Model {

    use UserHasTeams;
}