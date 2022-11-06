<?php

namespace Mpociot\Teamwork\Tests\Feature;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Event;
use Mockery as m;
use Mpociot\Teamwork\TeamInvite;
use Mpociot\Teamwork\TeamworkTeam;
use Mpociot\Teamwork\Tests\Support\User;
use Mpociot\Teamwork\Tests\TestCase;

class TeamworkTest extends TestCase
{
    protected $user;

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('teamwork.user_model', 'User');

        \Schema::create('users', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->user = new User();
        $this->user->name = 'Marcel';
        $this->user->save();
    }

    protected function getPackageProviders($app)
    {
        return [\Mpociot\Teamwork\TeamworkServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Teamwork' => \Mpociot\Teamwork\Facades\Teamwork::class,
        ];
    }

    protected function createInvite($team = null)
    {
        if (is_null($team)) {
            $team = TeamworkTeam::create(['name' => 'Test-Team 1']);
        }

        $invite = $this->app->make(Config::get('teamwork.invite_model'));
        $invite->user_id = $this->user->getKey();
        $invite->team_id = $team->getKey();
        $invite->type = 'invite';
        $invite->email = 'foo@bar.com';
        $invite->accept_token = md5(uniqid(microtime()));
        $invite->deny_token = md5(uniqid(microtime()));
        $invite->save();

        return $invite;
    }

    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function testUser()
    {
        $this->assertNull(\Teamwork::user());
        auth()->login($this->user);
        $this->assertEquals($this->user, \Teamwork::user());
    }

    public function testGetInviteFromTokens()
    {
        $invite = $this->createInvite();

        $this->assertEquals($invite->toArray(), \Teamwork::getInviteFromAcceptToken($invite->accept_token)->toArray());
        $this->assertEquals($invite->toArray(), \Teamwork::getInviteFromDenyToken($invite->deny_token)->toArray());
    }

    public function testDenyInvite()
    {
        $invite = $this->createInvite();
        \Teamwork::denyInvite($invite);
        $this->assertNull(TeamInvite::find($invite->getKey()));
    }

    public function testHasPendingInviteFalse()
    {
        $this->assertFalse(\Teamwork::hasPendingInvite('foo@bar.com', 1));
    }

    public function testHasPendingInviteTrue()
    {
        $invite = $this->createInvite();
        $this->assertTrue(\Teamwork::hasPendingInvite($invite->email, $invite->team_id));
    }

    public function testHasPendingInviteFromObject()
    {
        $team = TeamworkTeam::create(['name' => 'Test-Team 1']);
        $invite = $this->createInvite($team);
        $this->assertTrue(\Teamwork::hasPendingInvite($invite->email, $team));
    }

    public function testHasPendingInviteFromArray()
    {
        $team = TeamworkTeam::create(['name' => 'Test-Team 1']);
        $invite = $this->createInvite($team);
        $this->assertTrue(\Teamwork::hasPendingInvite($invite->email, $team->toArray()));
    }

    public function testCanNotInviteToUserWithoutEmail()
    {
        $team = TeamworkTeam::create(['name' => 'Test-Team 1']);
        $this->user->attachTeam($team);
        auth()->login($this->user);

        $this->expectException(Exception::class);
        $this->expectExceptionMessage('The provided object has no "email" attribute and is not a string.');
        \Teamwork::inviteToTeam($this->user);
    }

    public function testCanAcceptInvite()
    {
        $team = TeamworkTeam::create(['name' => 'Test-Team 1']);
        $invite = $this->createInvite($team);
        auth()->login($this->user);
        \Teamwork::acceptInvite($invite);

        $this->assertCount(1, $this->user->teams);
        $this->assertEquals($team->getKey(), $this->user->current_team_id);

        $this->assertNull(TeamInvite::find($invite->getKey()));
    }

    public function testCanInviteToTeam()
    {
        auth()->login($this->user);

        $email = 'asd@fake.com';
        $team = TeamworkTeam::create(['name' => 'test']);

        $callback = m::mock('stdClass');
        $callback->shouldReceive('callback')->once()
            ->with(m::type(TeamInvite::class))->andReturn();
        \Teamwork::inviteToTeam($email, $team->getKey(), [$callback, 'callback']);

        $this->assertDatabaseHas(config('teamwork.team_invites_table'), [
            'email' => 'asd@fake.com',
            'user_id' => $this->user->getKey(),
            'team_id' => $team->getKey(),
        ]);
    }

    public function testCanInviteToTeamWithObject()
    {
        auth()->login($this->user);

        $email = 'asd@fake.com';
        $team = TeamworkTeam::create(['name' => 'test']);

        $callback = m::mock('stdClass');
        $callback->shouldReceive('callback')->once()
            ->with(m::type(TeamInvite::class))->andReturn();
        \Teamwork::inviteToTeam($email, $team, [$callback, 'callback']);

        $this->assertDatabaseHas(config('teamwork.team_invites_table'), [
            'email' => 'asd@fake.com',
            'user_id' => $this->user->getKey(),
            'team_id' => $team->getKey(),
        ]);
    }

    public function testCanInviteToTeamWithArray()
    {
        auth()->login($this->user);

        $email = 'asd@fake.com';
        $team = TeamworkTeam::create(['name' => 'test']);

        $callback = m::mock('stdClass');
        $callback->shouldReceive('callback')->once()
            ->with(m::type(TeamInvite::class))->andReturn();
        \Teamwork::inviteToTeam($email, $team->toArray(), [$callback, 'callback']);

        $this->assertDatabaseHas(config('teamwork.team_invites_table'), [
            'email' => 'asd@fake.com',
            'user_id' => $this->user->getKey(),
            'team_id' => $team->getKey(),
        ]);
    }

    public function testCanInviteToTeamWithUser()
    {
        auth()->login($this->user);
        $this->user->email = 'asd@fake.com';
        $team = TeamworkTeam::create(['name' => 'test']);

        $callback = m::mock('stdClass');
        $callback->shouldReceive('callback')->once()
            ->with(m::type(TeamInvite::class))->andReturn();
        \Teamwork::inviteToTeam($this->user, $team->toArray(), [$callback, 'callback']);

        $this->assertDatabaseHas(config('teamwork.team_invites_table'), [
            'email' => 'asd@fake.com',
            'user_id' => $this->user->getKey(),
            'team_id' => $team->getKey(),
        ]);
    }

    public function testCanInviteToTeamWithNull()
    {
        auth()->login($this->user);

        $email = 'asd@fake.com';
        $team = TeamworkTeam::create(['name' => 'test']);
        $this->user->attachTeam($team);

        $callback = m::mock('stdClass');
        $callback->shouldReceive('callback')->once()
            ->with(m::type(TeamInvite::class))->andReturn();
        \Teamwork::inviteToTeam($email, null, [$callback, 'callback']);

        $this->assertDatabaseHas(config('teamwork.team_invites_table'), [
            'email' => 'asd@fake.com',
            'team_id' => $team->getKey(),
        ]);
    }

    public function testCanInviteToTeamWithoutCallback()
    {
        auth()->login($this->user);

        $email = 'asd@fake.com';
        $team = TeamworkTeam::create(['name' => 'test']);
        $this->user->attachTeam($team);

        \Teamwork::inviteToTeam($email);

        $this->assertDatabaseHas(config('teamwork.team_invites_table'), [
            'email' => 'asd@fake.com',
            'team_id' => $team->getKey(),
        ]);
    }

    public function testInviteToTeamFiresEvent()
    {
        Event::fake();

        auth()->login($this->user);

        $email = 'asd@fake.com';
        $team = TeamworkTeam::create(['name' => 'test']);
        $this->user->attachTeam($team);

        \Teamwork::inviteToTeam($email, $team, function ($invite) {
        });

        Event::assertDispatched(\Mpociot\Teamwork\Events\UserInvitedToTeam::class);
    }
}
