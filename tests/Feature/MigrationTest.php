<?php

namespace Mpociot\Teamwork\Tests\Feature;

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Mpociot\Teamwork\Tests\TestCase;

class MigrationTest extends TestCase
{
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'testing');
        $app['config']->set('database.connections.testing', [
            'driver' => 'sqlite',
            'database' => ':memory:',
            'prefix' => '',
            'foreign_key_constraints' => true,
        ]);

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
        });
    }

    public function testMigrationCanBeFreshlyInstalledOnSqlite()
    {
        $this->assertSame('sqlite', DB::getDriverName());
        $this->assertTrue(Schema::hasColumn('users', 'current_team_id'));
        $this->assertTrue(Schema::hasTable(config('teamwork.teams_table')));
        $this->assertTrue(Schema::hasTable(config('teamwork.team_user_table')));
        $this->assertTrue(Schema::hasTable(config('teamwork.team_invites_table')));
    }

    public function testMigrationCanBeRolledBackOnSqlite()
    {
        $this->artisan('migrate:rollback', [
            '--database' => 'testing',
            '--path' => realpath(__DIR__.'/../../database/migrations'),
            '--realpath' => true,
        ])->assertExitCode(0);

        $this->assertFalse(Schema::hasColumn('users', 'current_team_id'));
        $this->assertFalse(Schema::hasTable(config('teamwork.teams_table')));
        $this->assertFalse(Schema::hasTable(config('teamwork.team_user_table')));
        $this->assertFalse(Schema::hasTable(config('teamwork.team_invites_table')));
    }
}
