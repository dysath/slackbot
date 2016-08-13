<?php

/**
 * User: Warlof Tutsimo <loic.leuilliot@gmail.com>
 * Date: 09/08/2016
 * Time: 16:25
 */

namespace Seat\Slackbot\Tests;

use Orchestra\Testbench\TestCase;
use Seat\Services\Settings\Seat;
use Seat\Slackbot\Commands\SlackChannelsUpdate;
use Seat\Slackbot\Commands\SlackLogsClear;
use Seat\Slackbot\Helpers\SlackApi;
use Seat\Slackbot\Models\SlackChannel;
use Seat\Slackbot\Models\SlackLog;

class SlackLogsClearTest extends TestCase
{
    /**
     * @var SlackApi
     */
    private $slackApi;

    public function getEnvironmentSetUp($app)
    {
        $app['config']->set('database.default', 'mysql');
        $app['config']->set('database.connections.mysql', [
            'driver' => 'mysql',
            'host' => getenv('database_host'),
            'database' => getenv('database_name'),
            'username' => getenv('database_user'),
            'password' => getenv('database_pass'),
            'charset' => 'utf8',
            'collation' => 'utf8_unicode_ci',
            'prefix' => ''
        ]);
    }

    public function testChannelUpdate()
    {
        $logAmount = SlackLog::all()->count();
        $this->assertGreaterThan(0, $logAmount);

        $command = new SlackLogsClear();
        $command->handle();

        $logAmount = SlackLog::all()->count();

        // compare both array
        $this->assertEquals(0, $logAmount);
    }

    /**
     * @expectedException Seat\Slackbot\Exceptions\SlackSettingException
     */
    public function testTokenException()
    {
        // pre test
        Seat::set('slack_token', '');

        // test
        $job = new SlackChannelsUpdate();
        $job->handle();
    }
}
