<?php

use Illuminate\Support\Facades\Config;
use Mockery as m;
use Mpociot\Teamwork\Traits\TeamworkTeamTrait;

class TeamworkTeamTraitTest extends PHPUnit_Framework_TestCase
{

    public function tearDown()
    {
        m::close();
    }

    public function testGetInvites()
    {
        Config::shouldReceive('get')
            ->once()
            ->with('teamwork.invite_model')
            ->andReturn('Invite');

        $stub = m::mock( 'TestUserTeamTraitStub[hasMany]' );
        $stub->shouldReceive('hasMany')
            ->once()
            ->with('Invite', 'team_id', 'id' )
            ->andReturn( [] );
        $this->assertEquals( [], $stub->invites() );
    }

    public function testGetUsers()
    {
        Config::shouldReceive('get')
            ->once()
            ->with('auth.model')
            ->andReturn('User');

        Config::shouldReceive('get')
            ->once()
            ->with('teamwork.team_user_table')
            ->andReturn('TeamUser');

        $stub = m::mock( 'TestUserTeamTraitStub[belongsToMany]' );
        $stub->shouldReceive('belongsToMany')
            ->once()
            ->with('User', 'TeamUser', 'team_id', 'user_id' )
            ->andReturn( [] );
        $this->assertEquals( [], $stub->users() );
    }

    public function testGetOwner()
    {
        Config::shouldReceive('get')
            ->once()
            ->with('auth.model')
            ->andReturn('TestUser');


        $stub = m::mock( 'TestUserTeamTraitStub[hasOne]' );
        $stub->shouldReceive('hasOne')
            ->once()
            ->with('User', 'user_id', 'owner_id' )
            ->andReturn( [] );
        $this->assertEquals( [], $stub->owner() );
    }

    public function testHasUser()
    {
        $stub = m::mock( 'TestUserTeamTraitStub[users,first]' );

        $user = m::mock( 'TestUser[getKey]' );
        $user->shouldReceive('getKey')
            ->once()
            ->andReturn('key');

        $stub->shouldReceive('first')
            ->once()
            ->andReturn( true );

        $stub->shouldReceive('where')
            ->once()
            ->with( "user_id" , "=", "key" )
            ->andReturnSelf();

        $stub->shouldReceive('users')
            ->andReturnSelf();

        $this->assertTrue( $stub->hasUser( $user ) );
    }

    public function testHasUserReturnsFalse()
    {
        $stub = m::mock( 'TestUserTeamTraitStub[users,first]' );

        $user = m::mock( 'TestUser[getKey]' );
        $user->shouldReceive('getKey')
            ->once()
            ->andReturn('key');

        $stub->shouldReceive('first')
            ->once()
            ->andReturn( false );

        $stub->shouldReceive('where')
            ->once()
            ->with( "user_id" , "=", "key" )
            ->andReturnSelf();

        $stub->shouldReceive('users')
            ->andReturnSelf();

        $this->assertFalse( $stub->hasUser( $user ) );
    }

}

class TestUser extends Illuminate\Database\Eloquent\Model {
    public function getKeyName()
    {
        return "user_id";
    }
}
class TestUserTeamTraitStub extends Illuminate\Database\Eloquent\Model {

    use TeamworkTeamTrait;
}