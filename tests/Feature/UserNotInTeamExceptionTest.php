<?php

namespace Mpociot\Teamwork\Tests\Feature;

use Mockery as m;
use Mpociot\Teamwork\Tests\TestCase;
use Mpociot\Teamwork\Tests\User;

class UserNotInTeamExceptionTest extends TestCase
{
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
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function tearDown(): void
    {
        m::close();
        parent::tearDown();
    }

    public function testGetTeam(): void
    {
        $exception = new \Mpociot\Teamwork\Exceptions\UserNotInTeamException();
        $exception->setTeam( "Test" );
        $this->assertEquals( "Test", $exception->getTeam() );
    }
}