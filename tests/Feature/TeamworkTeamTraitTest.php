<?php

namespace Mpociot\Teamwork\Tests\Feature;

use Illuminate\Support\Facades\Config;
use Mockery as m;
use Mpociot\Teamwork\Traits\TeamworkTeamTrait;

class TeamworkTeamTraitTest extends \PHPUnit\Framework\TestCase
{
    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function testGetInvites()
    {
        Config::shouldReceive('get')
            ->once()
            ->with('teamwork.invite_model')
            ->andReturn('Invite');

        $stub = m::mock('\Mpociot\Teamwork\Tests\Feature\TestUserTeamTraitStub[hasMany]');
        $stub->shouldReceive('hasMany')
            ->once()
            ->with('Invite', 'team_id', 'id')
            ->andReturn([]);
        $this->assertEquals([], $stub->invites());
    }

    public function testGetUsers()
    {
        Config::shouldReceive('get')
            ->once()
            ->with('teamwork.user_model')
            ->andReturn('User');

        Config::shouldReceive('get')
            ->once()
            ->with('teamwork.team_user_table')
            ->andReturn('TeamUser');

        $stub = m::mock('\Mpociot\Teamwork\Tests\Feature\TestUserTeamTraitStub[belongsToMany,withTimestamps]');

        $stub->shouldReceive('withTimestamps')
            ->once()
            ->andReturn([]);

        $stub->shouldReceive('belongsToMany')
            ->once()
            ->with('User', 'TeamUser', 'team_id', 'user_id')
            ->andReturnSelf();

        $this->assertEquals([], $stub->users());
    }

    public function testGetOwner()
    {
        Config::shouldReceive('get')
            ->once()
            ->with('teamwork.user_model')
            ->andReturn('\Mpociot\Teamwork\Tests\Feature\TestUser');

        $stub = m::mock('\Mpociot\Teamwork\Tests\Feature\TestUserTeamTraitStub[belongsTo]');
        $stub->shouldReceive('belongsTo')
            ->once()
            ->with('\Mpociot\Teamwork\Tests\Feature\TestUser', 'owner_id', 'user_id')
            ->andReturn([]);

        $this->assertEquals([], $stub->owner());
    }

    public function testHasUser()
    {
        $stub = m::mock('\Mpociot\Teamwork\Tests\Feature\TestUserTeamTraitStub[users,first]');

        $user = m::mock('\Mpociot\Teamwork\Tests\Feature\TestUser[getKey, getTable]');

        $user->shouldReceive('getKey')
            ->once()
            ->andReturn('key');

        $user->shouldReceive('getTable')
            ->once()
            ->andReturn('users');

        $stub->shouldReceive('first')
            ->once()
            ->andReturn(true);

        $stub->shouldReceive('where')
            ->once()
            ->with('users.user_id', '=', $this->user->getKey())
            ->andReturnSelf();

        $stub->shouldReceive('users')
            ->andReturnSelf();

        $this->assertTrue($stub->hasUser($user));
    }

    public function testHasUserReturnsFalse()
    {
        $stub = m::mock('\Mpociot\Teamwork\Tests\Feature\TestUserTeamTraitStub[users,first]');

        $user = m::mock('\Mpociot\Teamwork\Tests\Feature\TestUser[getKey, getTable]');

        $user->shouldReceive('getKey')
            ->once()
            ->andReturn('key');

        $user->shouldReceive('getTable')
            ->once()
            ->andReturn('users');

        $stub->shouldReceive('first')
            ->once()
            ->andReturn(false);

        $stub->shouldReceive('where')
            ->once()
            ->with('users.user_id', '=', $this->user->getKey())
            ->andReturnSelf();

        $stub->shouldReceive('users')
            ->andReturnSelf();

        $this->assertFalse($stub->hasUser($user));
    }
}

class TestUser extends \Illuminate\Database\Eloquent\Model
{
    public function getKeyName()
    {
        return 'user_id';
    }

    public function getTable()
    {
        return 'users';
    }
}

class TestUserTeamTraitStub extends \Illuminate\Database\Eloquent\Model
{
    use TeamworkTeamTrait;
}
