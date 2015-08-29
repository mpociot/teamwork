<?php

use Mpociot\Teamwork\Teamwork;
use Mockery as m;

class TeamworkTest extends PHPUnit_Framework_TestCase
{
    public function tearDown()
    {
        m::close();
    }


    public function testUser()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $app = new stdClass();
        $app->auth = m::mock('Auth');
        $teamwork = new Teamwork($app);
        $user = m::mock('_mockedUser');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $app->auth->shouldReceive('user')
            ->andReturn($user)
            ->once();

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertSame($user, $teamwork->user());
    }

    public function testGetInviteFromAcceptToken()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $app = m::mock('App');
        $teamwork = new Teamwork($app);
        $token = "asd";
        $teaminvite = m::mock('TeamInvite');
        $app->shouldReceive('make')->with('Mpociot\Teamwork\TeamInvite')->once()->andReturn( $teaminvite );

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $teaminvite->shouldReceive('where')->once()->with('accept_token', '=', $token)->andReturnSelf();
        $teaminvite->shouldReceive('first')->andReturn( false );

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertFalse( $teamwork->getInviteFromAcceptToken( $token ) );
    }


    public function testGetInviteFromDenyToken()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $app = m::mock('App');
        $teamwork = new Teamwork($app);
        $token = "asd";
        $teaminvite = m::mock('Mpociot\Teamwork\TeamInvite');
        $app->shouldReceive('make')->with('Mpociot\Teamwork\TeamInvite')->once()->andReturn( $teaminvite );

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $teaminvite->shouldReceive('where')->once()->with('deny_token', '=', $token)->andReturnSelf();
        $teaminvite->shouldReceive('first')->andReturn( false );

        /*
        |------------------------------------------------------------
        | Assertion
        |------------------------------------------------------------
        */
        $this->assertFalse( $teamwork->getInviteFromDenyToken( $token ) );
    }


    public function testDenyInvite()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $app = m::mock('App');
        $teamwork = new Teamwork($app);
        $token = "asd";
        $teaminvite = m::mock('Mpociot\Teamwork\TeamInvite');

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $teaminvite->shouldReceive('delete')->once()->andReturn( true );

        $teamwork->denyInvite( $teaminvite );
    }


    public function testHasPendingInviteFalse()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $email = "asd@fake.com";
        $team_id = 1;

        $app = m::mock('App');
        $teamwork = new Teamwork($app);
        $token = "asd";
        $teaminvite = m::mock('Mpociot\Teamwork\TeamInvite');
        $app->shouldReceive('make')->with('Mpociot\Teamwork\TeamInvite')->once()->andReturn( $teaminvite );

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $teaminvite->shouldReceive('where')->once()->with('email', "=", $email)->andReturnSelf();
        $teaminvite->shouldReceive('where')->once()->with('team_id', "=", $team_id)->andReturnSelf();
        $teaminvite->shouldReceive('first')->once()->andReturn( false );

        $this->assertFalse( $teamwork->hasPendingInvite( $email, $team_id ) );
    }


    public function testHasPendingInviteTrue()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $email = "asd@fake.com";
        $team_id = 1;

        $app = m::mock('App');
        $teamwork = new Teamwork($app);
        $token = "asd";
        $teaminvite = m::mock('Mpociot\Teamwork\TeamInvite');
        $app->shouldReceive('make')->with('Mpociot\Teamwork\TeamInvite')->once()->andReturn( $teaminvite );

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $teaminvite->shouldReceive('where')->once()->with('email', "=", $email)->andReturnSelf();
        $teaminvite->shouldReceive('where')->once()->with('team_id', "=", $team_id)->andReturnSelf();
        $teaminvite->shouldReceive('first')->once()->andReturnSelf();

        $this->assertTrue( $teamwork->hasPendingInvite( $email, $team_id ) );
    }

    public function testCanInviteToTeam()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $email = "asd@fake.com";
        $team_id = 1;

        $app = m::mock('App');
        $teamwork = new Teamwork($app);
        $app->auth = m::mock('Auth');
        $user = m::mock('User');
        $user->shouldReceive('getKey')->once()->andReturn(1);

        $app->auth->shouldReceive('user')
            ->andReturn($user)
            ->once();
        $teaminvite = m::mock('Mpociot\Teamwork\TeamInvite');
        $app->shouldReceive('make')->with('Mpociot\Teamwork\TeamInvite')->once()->andReturn( $teaminvite );

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $teaminvite->shouldReceive('setAttribute')->andReturnSelf();
        $teaminvite->shouldReceive('save')->once()->andReturnSelf();

        $callback = m::mock('stdClass');
        $callback->shouldReceive('callback')->once()
            ->with( $teaminvite )->andReturn();

        $teamwork->inviteToTeam( $email, $team_id, array($callback,'callback') );

    }

    public function testCanInviteToTeamWithObject()
    {
        /*
        |------------------------------------------------------------
        | Set
        |------------------------------------------------------------
        */
        $email = "asd@fake.com";
        $team_id = 1;

        $app = m::mock('App');
        $teamwork = new Teamwork($app);
        $app->auth = m::mock('Auth');
        $user = m::mock('User');
        $user->email = "test@mail.de";
        $user->shouldReceive('getKey')->once()->andReturn(1);

        $app->auth->shouldReceive('user')
            ->andReturn($user)
            ->once();
        $teaminvite = m::mock('Mpociot\Teamwork\TeamInvite');
        $app->shouldReceive('make')->with('Mpociot\Teamwork\TeamInvite')->once()->andReturn( $teaminvite );

        /*
        |------------------------------------------------------------
        | Expectation
        |------------------------------------------------------------
        */
        $teaminvite->shouldReceive('setAttribute')->andReturnSelf();
        $teaminvite->shouldReceive('save')->once()->andReturnSelf();

        $callback = m::mock('stdClass');
        $callback->shouldReceive('callback')->once()
            ->with( $teaminvite )->andReturn();

        $teamwork->inviteToTeam( $user, $team_id, array($callback,'callback') );

    }

}
