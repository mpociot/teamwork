<?php

use Illuminate\Support\Facades\Config;
use Mockery as m;
use Mpociot\Teamwork\TeamInvite;
use Mpociot\Teamwork\TeamworkTeam;
use Mpociot\Teamwork\Traits\TeamworkTeamInviteTrait;

class TeamworkTeamInviteTraitTest  extends Orchestra\Testbench\TestCase
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
        $app['config']->set('database.default', 'testing');
        $app['config']->set('teamwork.user_model', 'User');

        \Schema::create('users', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function setUp()
    {
        parent::setUp();

        $this->artisan('migrate', [
            '--database' => 'testing',
            '--realpath' => realpath(__DIR__.'/../src/database/migrations'),
        ]);

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
        $this->invite->type         = 'invite';
        $this->invite->accept_token = md5( uniqid( microtime() ) );
        $this->invite->deny_token   = md5( uniqid( microtime() ) );
        $this->invite->save();
    }

    protected function getPackageProviders($app)
    {
        return [\Mpociot\Teamwork\TeamworkServiceProvider::class];
    }

    protected function getPackageAliases($app)
    {
        return [
            'Teamwork' => \Mpociot\Teamwork\Facades\Teamwork::class
        ];
    }

    public function tearDown()
    {
        m::close();
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