<?php

use Illuminate\Support\Facades\Config;
use Mockery as m;
use Mpociot\Teamwork\Traits\UserHasTeams;

class UserHasTeamsTraitTest extends PHPUnit_Framework_TestCase
{

    public function tearDown()
    {
        Config::clearResolvedInstances();
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



    public function testAttachTeamSetsCurrentTeamId()
    {
        $team = 1;

        $stub = m::mock( 'TestUserHasTeamsTraitStub[teams,save,attach,load]' );
        $stub->teams = m::mock('stdClass');
        $stub->teams->shouldReceive('contains')
            ->with( $team )
            ->andReturn( false );
        $stub->shouldReceive('save')
            ->once();

        $stub->shouldReceive('teams')
            ->once()
            ->andReturnSelf();

        $stub->shouldReceive('load')
            ->once()
            ->with('teams');

        $stub->shouldReceive('attach')
            ->once()
            ->with($team);

        $stub->current_team_id = null;
        $stub->attachTeam( $team );

        $this->assertEquals( $team, $stub->current_team_id );
    }

    public function testAttachTeamDoesNotOverrideCurrentTeamId()
    {
        $team = 1;

        $stub = m::mock( 'TestUserHasTeamsTraitStub[teams,save,attach,load]' );
        $stub->teams = m::mock('stdClass');
        $stub->teams->shouldReceive('contains')
            ->with( $team )
            ->andReturn( false );
        $stub->shouldReceive('save')
            ->never();

        $stub->shouldReceive('teams')
            ->once()
            ->andReturnSelf();

        $stub->shouldReceive('load')
            ->once()
            ->with('teams');

        $stub->shouldReceive('attach')
            ->once()
            ->with($team);

        $stub->current_team_id = 2;
        $stub->attachTeam( $team );

        $this->assertEquals( 2, $stub->current_team_id );
    }

    public function testAttachTeamDoesNotAttachTeamIdWhenItExists()
    {
        $team = 1;

        $stub = m::mock( 'TestUserHasTeamsTraitStub[teams,save,attach,load]' );
        $stub->teams = m::mock('stdClass');
        $stub->teams->shouldReceive('contains')
            ->with( $team )
            ->andReturn( true );
        $stub->shouldReceive('save')
            ->never();

        $stub->shouldReceive('teams')
            ->never()
            ->andReturnSelf();

        $stub->shouldReceive('load')
            ->once()
            ->with('teams');

        $stub->shouldReceive('attach')
            ->never();

        $stub->current_team_id = 2;
        $stub->attachTeam( $team );

        $this->assertEquals( 2, $stub->current_team_id );
    }

    public function testDetachTeamUnsetsCurrentTeamId()
    {
        $team = 1;

        $stub = m::mock( 'TestUserHasTeamsTraitStub[teams,save,detach]' );
        $stub->teams = [];

        $stub->shouldReceive('save')
            ->once();

        $stub->shouldReceive('teams')
            ->once()
            ->andReturnSelf();
        $stub->shouldReceive('detach')
            ->once()
            ->with($team);

        $stub->current_team_id = 1;
        $stub->detachTeam( $team );

        $this->assertNull( $stub->current_team_id );
    }

    public function testDetachTeamDoesNotUnsetsCurrentTeamIdWhenSet()
    {
        $team = 1;

        $stub = m::mock( 'TestUserHasTeamsTraitStub[teams,save,detach]' );
        $stub->teams = [2];

        $stub->shouldReceive('save')
            ->never();

        $stub->shouldReceive('teams')
            ->once()
            ->andReturnSelf();
        $stub->shouldReceive('detach')
            ->once()
            ->with($team);

        $stub->current_team_id = 1;
        $stub->detachTeam( $team );

        $this->assertNotNull( $stub->current_team_id );
    }

    public function testSwitchTeamToNull()
    {
        Config::shouldReceive('get')
            ->never();
        $stub = m::mock( 'TestUserHasTeamsTraitStub[save]' );
        $stub->shouldReceive('save')
            ->once();
        $stub->current_team_id = 1;
        $stub->switchTeam( 0 );
        $this->assertEquals( 0, $stub->current_team_id );
    }

    public function testSwitchTeamToNullWithNull()
    {
        Config::shouldReceive('get')
            ->never();
        $stub = m::mock( 'TestUserHasTeamsTraitStub[save]' );
        $stub->shouldReceive('save')
            ->once();
        $stub->current_team_id = 1;
        $stub->switchTeam( null );
        $this->assertNull( $stub->current_team_id );
    }

    public function testSwitchTeamFails()
    {
        $team = 1;

        Config::shouldReceive('get')
            ->once()
            ->with( 'teamwork.team_model' )
            ->andReturn( 'TestTeamworkTeamModelStub' );


        $stub = m::mock( 'TestUserHasTeamsTraitStub[save]' );
        $stub->shouldReceive('save')
            ->never();
        $stub->current_team_id = null;
        $this->setExpectedException('Illuminate\Database\Eloquent\ModelNotFoundException');
        $stub->switchTeam( $team );
    }

    public function testSwitchTeamUserNotInTeam()
    {
        $team = 1;

        Config::shouldReceive('get')
            ->once()
            ->with( 'teamwork.team_model' )
            ->andReturn( 'TestTeamworkTeamModelNotInTeamStub' );


        $stub = m::mock( 'TestUserHasTeamsTraitStub[save,getKey]' );
        $stub->shouldReceive('getKey')
            ->once()
            ->andReturn( 5 );

        $stub->shouldReceive('save')
            ->never();
        $this->setExpectedException('Mpociot\Teamwork\Exceptions\UserNotInTeamException',
            'The user is not in the team Test Team');
        $stub->switchTeam( $team );
    }


}

class TestTeamworkTeamModelStub {
    public function find(){}
}
class TestTeamworkTeamModelNotInTeamStub {

    public function find(){
        $mock = m::mock('stdClass');
        $mock->name = "Test Team";
        $mock->users = m::mock('stdClass');
        $mock->users->shouldReceive('contains')
            ->once()
            ->with( 5 )
            ->andReturn( false );
        return $mock;
    }
}

class TestUserHasTeamsTraitStub extends Illuminate\Database\Eloquent\Model {

    use UserHasTeams;
}