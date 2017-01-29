<?php

use Event;
use Mpociot\Teamwork\TeamworkTeam;

class UserHasTeamsTraitTest extends Orchestra\Testbench\TestCase
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
        $app['config']->set('database.default', 'testing');
        $app['config']->set('teamwork.user_model', 'User');

        \Schema::create('users', function ($table) {
            $table->increments('id');
            $table->string('name');
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
            'Teamwork' => \Mpociot\Teamwork\Facades\Teamwork::class
        ];
    }

    public function testNewUserHasNoTeams()
    {
        $user = new User();
        $user->name = 'Marcel';
        $user->save();

        $this->assertCount(0, $user->teams);
        $this->assertEquals(0, $user->current_team_id);
        $this->assertNull($user->currentTeam);
        $this->assertCount(0, $user->ownedTeams);
        $this->assertCount(0, $user->invites);
    }

    public function testAttachingTeamSetsCurrentTeam()
    {
        $team = TeamworkTeam::create(['name' => 'Test-Team']);
        $this->assertNull($this->user->currentTeam);

        $this->user->attachTeam($team);

        $this->assertEquals(1, $this->user->currentTeam->getKey());
    }

    public function testCanAttachTeamToUser()
    {
        $team = TeamworkTeam::create(['name' => 'Test-Team']);

        $this->user->attachTeam($team);

        // Reload relation
        $this->assertCount(1, $this->user->teams);
        $this->assertEquals(TeamworkTeam::find(1)->toArray(), $this->user->currentTeam->toArray());
    }

    public function testCanAttachTeamAsArrayToUser()
    {
        $team = TeamworkTeam::create(['name' => 'Test-Team']);

        $this->user->attachTeam($team->toArray());

        // Reload relation
        $this->assertCount(1, $this->user->teams);
        $this->assertEquals(TeamworkTeam::find(1)->toArray(), $this->user->currentTeam->toArray());
    }

    public function testCanAttachTeamAsIDToUser()
    {
        $team = TeamworkTeam::create(['name' => 'Test-Team']);

        $this->user->attachTeam($team->getKey());

        // Reload relation
        $this->assertCount(1, $this->user->teams);
        $this->assertEquals(TeamworkTeam::find(1)->toArray(), $this->user->currentTeam->toArray());
    }

    public function testCanSetPivotDataOnAttachTeamMethod()
    {
        \Schema::table(config( 'teamwork.team_user_table' ), function ($table) {
            $table->boolean('pivot_set')->default(false);
        });

        $team = TeamworkTeam::create(['name' => 'Test-Team']);
        $pivotData = ['pivot_set' => true];

        $this->user->attachTeam($team, $pivotData);

        $this->assertDatabaseHas(config('teamwork.team_user_table'), [
            'user_id' => $this->user->getKey(),
            'team_id' => $team->getKey(),
            'pivot_set' => true
        ]);
    }

    public function testIsTeamOwner()
    {
        $team = TeamworkTeam::create(['name' => 'Test-Team']);
        $this->user->attachTeam($team->getKey());

        $this->assertFalse($this->user->isTeamOwner());
        $this->assertFalse($this->user->isOwner());

        $team2 = TeamworkTeam::create(['name' => 'Test-Team', 'owner_id' => $this->user->getKey()]);
        $this->user->attachTeam($team2->getKey());

        $this->assertTrue($this->user->isTeamOwner());
        $this->assertTrue($this->user->isOwner());
    }

    public function testIsOwnerOfTeam()
    {
        $team = TeamworkTeam::create(['name' => 'Test-Team']);
        $this->user->attachTeam($team->getKey());

        $this->assertFalse($this->user->isOwnerOfTeam($team));

        $team = TeamworkTeam::create(['name' => 'Test-Team', 'owner_id' => $this->user->getKey()]);
        $this->user->attachTeam($team->getKey());

        $this->assertTrue($this->user->isOwnerOfTeam($team));
    }

    public function testGetOwnedTeams()
    {
        $team = TeamworkTeam::create(['name' => 'Test-Team', 'owner_id' => $this->user->getKey()]);
        $this->user->attachTeam($team->getKey());
        $this->assertCount(1, $this->user->ownedTeams);
    }

    public function testCanDetachTeam()
    {
        $team1 = TeamworkTeam::create(['name' => 'Test-Team 1']);
        $team2 = TeamworkTeam::create(['name' => 'Test-Team 2']);
        $team3 = TeamworkTeam::create(['name' => 'Test-Team 3']);

        $this->user->attachTeam($team1);
        $this->user->attachTeam($team2);
        $this->user->attachTeam($team3);

        $this->assertCount(3, $this->user->teams()->get());

        $this->user->detachTeam($team2);
        $this->assertCount(2, $this->user->teams()->get());
    }

    public function testDetachTeamResetsCurrentTeam()
    {
        $team = TeamworkTeam::create(['name' => 'Test-Team 1']);

        $this->user->attachTeam($team);

        $this->assertEquals($team->getKey(), $this->user->currentTeam->getKey());

        $this->user->detachTeam($team);
        $this->assertNull($this->user->currentTeam);
    }

    public function testAttachTeamFiresEvent()
    {
        Event::fake();

        $team1 = TeamworkTeam::create(['name' => 'Test-Team 1']);
        $this->user->attachTeam($team1);

        Event::assertDispatched(\Mpociot\Teamwork\Events\UserJoinedTeam::class, function ($e) use ($team1) {
            return $e->getTeamId() === $team1->id && $e->getUser()->id === $this->user->id;
        });
        Event::assertNotDispatched(\Mpociot\Teamwork\Events\UserLeftTeam::class);
    }

    public function testDetachTeamFiresEvent()
    {
        Event::fake();

        $team1 = TeamworkTeam::create(['name' => 'Test-Team 1']);
        $this->user->attachTeam($team1);
        $this->user->detachTeam($team1);

        Event::assertDispatched(\Mpociot\Teamwork\Events\UserLeftTeam::class, function ($e) use ($team1) {
            return $e->getTeamId() === $team1->id && $e->getUser()->id === $this->user->id;
        });
    }

    public function testCanAttachMultipleTeams()
    {
        $team1 = TeamworkTeam::create(['name' => 'Test-Team 1']);
        $team2 = TeamworkTeam::create(['name' => 'Test-Team 2']);
        $team3 = TeamworkTeam::create(['name' => 'Test-Team 3']);

        $this->user->attachTeams([
            $team1,
            $team2,
            $team3
        ]);

        $this->assertCount(3, $this->user->teams()->get());
    }

    public function testCanDetachMultipleTeams()
    {
        $team1 = TeamworkTeam::create(['name' => 'Test-Team 1']);
        $team2 = TeamworkTeam::create(['name' => 'Test-Team 2']);
        $team3 = TeamworkTeam::create(['name' => 'Test-Team 3']);

        $this->user->attachTeams([
            $team1,
            $team2,
            $team3
        ]);

        $this->assertCount(3, $this->user->teams()->get());

        $this->user->detachTeams([
            $team1,
            $team3
        ]);

        $this->assertCount(1, $this->user->teams()->get());
    }

    public function testCurrentTeamGetsResetWhenDetached()
    {
        $team1 = TeamworkTeam::create(['name' => 'Test-Team 1']);
        $team2 = TeamworkTeam::create(['name' => 'Test-Team 2']);
        $team3 = TeamworkTeam::create(['name' => 'Test-Team 3']);

        $this->user->attachTeams([
            $team1,
            $team2,
            $team3
        ]);

        $this->assertEquals($team1->getKey(), $this->user->currentTeam->getKey());

        $this->user->detachTeam($team1);

        $this->assertNull($this->user->currentTeam);
    }

    public function testUserCanSwitchTeam()
    {
        $team1 = TeamworkTeam::create(['name' => 'Test-Team 1']);
        $team2 = TeamworkTeam::create(['name' => 'Test-Team 2']);
        $team3 = TeamworkTeam::create(['name' => 'Test-Team 3']);

        $this->user->attachTeams([
            $team1,
            $team2,
            $team3
        ]);
        $this->assertEquals($team1->getKey(), $this->user->currentTeam->getKey());
        $this->user->switchTeam($team2);
        $this->assertEquals($team2->getKey(), $this->user->currentTeam->getKey());
    }

    public function testUserCannotSwitchToInvalidTeam()
    {

        $team1 = TeamworkTeam::create(['name' => 'Test-Team 1']);
        $team2 = TeamworkTeam::create(['name' => 'Test-Team 2']);
        $team3 = TeamworkTeam::create(['name' => 'Test-Team 3']);

        $this->user->attachTeams([
            $team1,
            $team2
        ]);

        $this->setExpectedException('Mpociot\Teamwork\Exceptions\UserNotInTeamException',
            'The user is not in the team Test-Team 3');
        $this->user->switchTeam($team3);
    }

    public function testUserCannotSwitchToNotExistingTeam()
    {

        $team1 = TeamworkTeam::create(['name' => 'Test-Team 1']);
        $team2 = TeamworkTeam::create(['name' => 'Test-Team 2']);

        $this->user->attachTeams([
            $team1,
            $team2
        ]);

        $this->setExpectedException('Illuminate\Database\Eloquent\ModelNotFoundException');
        $this->user->switchTeam(3);
    }

}