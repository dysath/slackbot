<?php
/**
 * User: Warlof Tutsimo <loic.leuilliot@gmail.com>
 * Date: 17/06/2016
 * Time: 19:04
 */

namespace Seat\Slackbot\Bot;

use Seat\Eveapi\Models\Eve\ApiKey;
use Seat\Slackbot\Exceptions\SlackChannelException;
use Seat\Slackbot\Exceptions\SlackGroupException;
use Seat\Slackbot\Models\SlackUser;
use Seat\Web\Models\User;

class SlackAssKicker extends AbstractSlack
{
    function call()
    {
        // todo load team and token

        foreach (User::where('active', true)->get() as $user) {

            $keys = ApiKey::where('user_id', $user->id)->get();
            $slack_user = SlackUser::where('user_id', $user->id)->get();

            if ($this->isInvited($user)) {
                
                $channels = $this->memberOfChannels($slack_user);
                
                if (!$this->isEnabledKey($keys) || !$this->isActive($keys)) {
                    $this->processChannelsKick($slack_user, $channels);
                    $this->processGroupsKick($slack_user, $channels);
                } else {
                    $allowed_channels = $this->allowedChannels($slack_user);

                    // remove channels in which user is already in from all granted channels and invite him
                    $this->processChannelsKick($slack_user, array_diff($channels, $allowed_channels));
                    // remove granted channels from channels in which user is already in and kick him
                    $this->processGroupsKick($slack_user, array_diff($channels, $allowed_channels));
                }
            }
        }

        return;
    }

    /**
     * Determine in which channels an user is in
     *
     * @param SlackUser $slackUser
     * @throws SlackChannelException
     * @return array
     */
    function memberOfChannels(SlackUser $slackUser)
    {
        $inChannels = [];
        
        // get all channels from the attached slack team
        $result = $this->processSlackApiPost('/channels.list');

        if ($result == null || $result['ok'] == false) {
            throw new SlackChannelException("An error occurred while trying to kick the member.");
        }
        
        // iterate over channels and check if the current slack user is part of channel
        foreach ($result['channels'] as $channel) {
            if (in_array($slackUser->slack_id, $channel['members']))
                $inChannels[] = $channel['id'];
        }

        return $inChannels;
    }

    /**
     * Kick an user from each channel
     *
     * @param SlackUser $slackUser
     * @param $channels
     * @throws SlackChannelException
     */
    function processChannelsKick(SlackUser $slackUser, $channels)
    {
        $params = [
            'channel' => '',
            'user' => $slackUser->slack_id
        ];

        foreach ($channels as $channel) {
            $params['channel'] = $channel;

            $result = $this->processSlackApiPost('/channels.kick', $params);

            if ($result == null || $result['ok'] == false) {
                throw new SlackChannelException("An error occurred while trying to kick the member.");
            }
        }
    }

    /**
     * Kick an user from each group
     *
     * @param SlackUser $slackUser
     * @param $groups
     * @throws SlackGroupException
     */
    function processGroupsKick(SlackUser $slackUser, $groups)
    {
        $params = [
            'channel' => '',
            'user' => $slackUser->slack_id
        ];

        foreach ($groups as $group) {
            $params['channel'] = $group;

            $result = $this->processSlackApiPost('/groups.kick', $params);

            if ($result['ok'] == false) {
                throw new SlackGroupException("An error occurred while trying to kick the member.");
            }
        }
    }

}