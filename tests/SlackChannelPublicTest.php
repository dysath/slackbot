<?php
/**
 * User: Warlof Tutsimo <loic.leuilliot@gmail.com>
 * Date: 10/08/2016
 * Time: 14:31
 */

namespace Warlof\Seat\Slackbot\Tests;


use Orchestra\Testbench\TestCase;
use Warlof\Seat\Slackbot\Models\SlackChannel;
use Warlof\Seat\Slackbot\Models\SlackChannelPublic;

class SlackChannelPublicTest extends TestCase
{
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

    public function testChannel()
    {
        $permission = SlackChannelPublic::where('channel_id', '=', 'C1Z920QKC')->first();
        $artifact = SlackChannel::find('C1Z920QKC');

        $this->assertEquals($artifact, $permission->channel);
    }
}
