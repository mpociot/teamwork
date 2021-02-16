<?php

namespace Mpociot\Teamwork\Tests\Feature;

use Exception;
use Mockery as m;
use Mpociot\Teamwork\TeamInvite;
use Mpociot\Teamwork\TeamworkTeam;
use Mpociot\Teamwork\Tests\Support\User;
use Mpociot\Teamwork\Tests\TestCase;

class TeamworkTeamInviteTraitTest extends TestCase
{
    protected $user;
    protected $invite;
    protected $team;
    protected $inviter;

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     *
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('teamwork.user_model', User::class);

        \Schema::create('users', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->user = new User();
        $this->user->name = 'Julia';
        $this->user->email = 'foo@baz.com';
        $this->user->save();

        $this->inviter = new User();
        $this->inviter->name = 'Marcel';
        $this->inviter->email = 'foo@bar.com';
        $this->inviter->save();

        $this->team = TeamworkTeam::create(['name' => 'Test-Team 2']);

        $this->invite = new TeamInvite();
        $this->invite->team_id = $this->team->getKey();
        $this->invite->user_id = $this->inviter->getKey();
        $this->invite->email = $this->user->email;
        $this->invite->type = 'invite';
        $this->invite->accept_token = md5(uniqid(microtime()));
        $this->invite->deny_token = md5(uniqid(microtime()));
        $this->invite->save();
    }

    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function testGetTeams()
    {
        $this->assertEquals($this->team->getKey(), $this->invite->team->getKey());
    }

    public function testGetUser()
    {
        $this->assertEquals($this->user->getKey(), $this->invite->user->getKey());
    }

    public function testGetInviter()
    {
        $this->assertEquals($this->inviter->getKey(), $this->invite->inviter->getKey());
    }
}
