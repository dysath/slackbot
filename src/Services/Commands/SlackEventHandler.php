<?php
/**
 * User: Warlof Tutsimo <loic.leuilliot@gmail.com>
 * Date: 26/06/2016
 * Time: 10:46
 */

namespace Seat\Slackbot\Services\Commands;


use PhpSlackBot\Command\BaseCommand;
use Seat\Services\Settings\Seat;
use Seat\Slackbot\Exceptions\SlackSettingException;
use Seat\Slackbot\Helpers\SlackApi;
use Seat\Slackbot\Models\SlackChannel;
use Seat\Slackbot\Models\SlackUser;

class SlackEventHandler extends BaseCommand
{
    protected function configure()
    {
        // We don't have to configure a command name in this case
    }

    protected function execute($data, $context)
    {
        switch ($data['type']) {
            // if the event is of type "team_join", then update our Slack user table using the new slack user id
            case 'team_join':
                $slackUser = SlackUser::join('users', 'users.id', 'slack_users.user_id')
                ->where('email', $data['user']['profile']['email'])
                ->first();

                if ($slackUser != null) {
                    $slackUser->update(['slack_id' => $data['user']['id']]);
                }
                
                break;
            // if the event is of type "channel_created", then update our Slack channel table using new slack channel id
            case 'channel_created':
                $slackChannel = new SlackChannel();
                $slackChannel->id = $data['channel']['id'];
                $slackChannel->name = $data['channel']['name'];
                $slackChannel->save();
                break;
            // if the event is of type "channel_delete", then remove the record from our Slack channel table
            case 'channel_deleted':
                $channel = SlackChannel::find($data['channel']);

                if ($channel != null)
                    $channel->delete();
                
                break;
            case 'group_joined':
                $group = SlackChannel::find($data['channel']);
                
                if ($group == null) {
                    $group = new SlackChannel();
                    $group->id = $data['channel']['id'];
                    $group->name = $data['channel']['name'];
                    $group->is_group = true;
                    $group->save();
                }
                
                break;
            case 'group_archive':
                $group = SlackChannel::find($data['channel']);

                if ($group != null)
                    $group->delete();
                
                break;
            case 'group_unarchive':
                // load token and team uri from settings
                $token = Seat::get('slack_token');

                if ($token == null)
                    throw new SlackSettingException("missing slack_token in settings");

                $slackApi = new SlackApi($token);
                $apiGroup = $slackApi->groupInfo($data['channel']);

                $group = new SlackChannel();
                $group->id = $apiGroup['group']['id'];
                $group->name = $apiGroup['group']['name'];
                $group->is_group = true;
                $group->save();
                break;
        }
    }
}