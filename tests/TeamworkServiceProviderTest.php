<?php
namespace Mpociot\Teamwork;

function config_path( $path = null )
{
    return 'test_config_path/' . $path ;
}
function database_path( $path = null )
{
    return 'test_database_path/' . $path ;
}

function glob( $path )
{
    return TeamworkServiceProviderTest::$globResult;
}

use Mockery as m;
use PHPUnit_Framework_TestCase;

class TeamworkServiceProviderTest extends PHPUnit_Framework_TestCase
{
    public static $globResult = [];

    public function tearDown()
    {
        m::close();
    }

    public function testCanBoot()
    {
        $sp = m::mock('Mpociot\Teamwork\TeamworkServiceProvider[publishConfig,publishMigration]',['app']);
        $sp->shouldAllowMockingProtectedMethods();
        $sp->shouldReceive('publishConfig')
            ->once()
            ->withNoArgs();
        $sp->shouldReceive('publishMigration')
            ->once()
            ->withNoArgs();
        $sp->boot();
    }

    public function testCanPublishConfig()
    {
        $test = $this;

        $sp = m::mock('Mpociot\Teamwork\TeamworkServiceProvider[publishes]',['app'])
            ->shouldAllowMockingProtectedMethods()
            ->shouldDeferMissing();
        $sp->shouldReceive('publishes')
            ->once()
            ->with( m::type('array') )
            ->andReturnUsing(function ($array) use ($test) {
                $test->assertContains('test_config_path/teamwork.php', $array);
            });
        $sp->publishConfig();
    }

    public function testCanPublishMigrationOnce()
    {
        $test = $this;
        self::$globResult = [];
        $sp = m::mock('Mpociot\Teamwork\TeamworkServiceProvider[publishes]',['app'])
            ->shouldAllowMockingProtectedMethods()
            ->shouldDeferMissing();
        $sp->shouldReceive('publishes')
            ->once()
            ->with( m::type('array'), 'migrations' )
            ->andReturnUsing(function ($array) use ($test) {
                $values = array_values( $array);
                $target = array_pop( $values );
                $test->assertContains('_teamwork_setup_tables.php', $target);
            });
        $sp->publishMigration();
    }

    public function testCanNotPublishMigrationWhenItExists()
    {
        $test = $this;
        self::$globResult = [1,2,3];
        $sp = m::mock('Mpociot\Teamwork\TeamworkServiceProvider[publishes]',['app'])
            ->shouldAllowMockingProtectedMethods()
            ->shouldDeferMissing();
        $sp->shouldReceive('publishes')
            ->never();
        $sp->publishMigration();
    }

    public function testCanRegister()
    {
        $sp = m::mock('Mpociot\Teamwork\TeamworkServiceProvider[mergeConfig,registerTeamwork,registerFacade]',['app']);
        $sp->shouldAllowMockingProtectedMethods();
        $sp->shouldReceive('mergeConfig')
            ->once()
            ->withNoArgs();
        $sp->shouldReceive('registerTeamwork')
            ->once()
            ->withNoArgs();
        $sp->shouldReceive('registerFacade')
            ->once()
            ->withNoArgs();
        $sp->register();
    }

    public function testCanRegisterTeamwork()
    {
        $test = $this;
        $app = m::mock('App');
        $sp = m::mock('Mpociot\Teamwork\TeamworkServiceProvider', [$app]);

        $app->shouldReceive('bind')
            ->once()->andReturnUsing(
                function ($name, $closure) use ($test, $app) {
                    $test->assertEquals('teamwork', $name);
                    $test->assertInstanceOf(
                        'Mpociot\Teamwork\Teamwork',
                        $closure($app)
                    );
                }
            );
        $sp->registerTeamwork();
    }

    public function testCanRegisterFacade()
    {
        $test = $this;
        $app = m::mock('App');
        $app->shouldReceive('booting')
            ->once()
            ->with( m::type('callable') );

        $sp = m::mock('Mpociot\Teamwork\TeamworkServiceProvider',[$app])
            ->shouldDeferMissing();
        $sp->registerFacade();
    }

    public function testShouldMergeConfig()
    {
        $test = $this;
        $sp = m::mock('Mpociot\Teamwork\TeamworkServiceProvider',['app'])
            ->shouldDeferMissing()
            ->shouldAllowMockingProtectedMethods();

        $sp->shouldReceive('mergeConfigFrom')
            ->once()
            ->with(m::type('string'),'teamwork');

        $sp->mergeConfig();
    }


}