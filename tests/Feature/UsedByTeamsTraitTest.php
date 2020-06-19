<?php

namespace Mpociot\Teamwork\Tests\Feature;

use Mpociot\Teamwork\TeamworkTeam;
use Mpociot\Teamwork\Tests\Task;
use Mpociot\Teamwork\Tests\TestCase;
use Mpociot\Teamwork\Tests\User;

class UsedByTeamsTraitTest extends TestCase
{
    protected $user;
    protected $team;

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

        \Schema::create('tasks', function ($table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('team_id');
            $table->timestamps();
        });
    }

    public function setUp(): void
    {
        parent::setUp();

        $this->user = new User();
        $this->user->name = 'Marcel';
        $this->user->save();

        $this->team = TeamworkTeam::create(['name' => 'Test-Team']);
        $this->user->attachTeam($this->team);
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

    public function testThrowsExceptionWhenUnauthorized()
    {
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('No authenticated user with selected team present.');

        $task = new Task();
        $task->name = 'Buy milk';
        $task->save();
    }

    public function testGetsCurrentTeamTasks()
    {
        auth()->login($this->user);

        $task = new Task();
        $task->team_id = $this->user->currentTeam->getKey();
        $task->name = 'Buy milk';
        $task->save();

        $task2 = new Task();
        $task2->team_id = $this->user->currentTeam->getKey() + 1;
        $task2->name = 'Buy steaks';
        $task2->save();

        $tasks = Task::all();
        $this->assertCount(1, $tasks);
        $this->assertEquals($task->id, $tasks->first()->id);
        $this->assertEquals($task->team_id, $tasks->first()->team_id);
        $this->assertEquals($task->name, $tasks->first()->name);
    }

    public function testGetsAllTasks()
    {
        auth()->login($this->user);

        $task = new Task();
        $task->team_id = $this->user->currentTeam->getKey();
        $task->name = 'Buy milk';
        $task->save();

        $task2 = new Task();
        $task2->team_id = $this->user->currentTeam->getKey() + 1;
        $task2->name = 'Buy steaks';
        $task2->save();

        $tasks = Task::allTeams()->get();
        $this->assertCount(2, $tasks);
    }

    public function testScopeAutomaticallyAddsCurrentTeam()
    {
        auth()->login($this->user);

        $task = new Task();
        $task->name = 'Buy milk';
        $task->save();

        $this->assertDatabaseHas('tasks', [
            'name' => 'Buy milk',
            'team_id' => $this->user->currentTeam->getKey(),
        ]);
    }
}
