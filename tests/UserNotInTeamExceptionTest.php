<?php

use Mockery as m;

class UserNotInTeamExceptionTest extends PHPUnit_Framework_TestCase
{

    public function tearDown()
    {
        m::close();
    }

    public function testGetTeam()
    {
        $exception = new \Mpociot\Teamwork\Exceptions\UserNotInTeamException();
        $exception->setTeam( "Test" );
        $this->assertEquals( "Test", $exception->getTeam() );
    }
}