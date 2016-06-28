<?php
/**
 * User: Warlof Tutsimo <loic.leuilliot@gmail.com>
 * Date: 17/06/2016
 * Time: 19:04
 */

namespace Seat\Slackbot\Jobs;

use Seat\Eveapi\Models\Eve\ApiKey;
use Seat\Slackbot\Models\SlackUser;

class SlackAssKicker extends AbstractSlack
{
    function call()
    {
        // call the parent call method in order to load the Slack Api Token
        parent::call();

        // get all Api Key owned by the user
        $keys = ApiKey::where('user_id', $this->user->id)->get();
        // get the Slack Api User
        $slackUser = SlackUser::where('user_id', $this->user->id)
            ->where('invited', true)
            ->whereNotNull('slack_id')
            ->first();

        if ($slackUser != null) {

            // get channels into which current user is already member
            $channels = $this->getSlackApi()->memberOf($slackUser->slack_id, 'channels');
            $groups = $this->getSlackApi()->memberOf($slackUser->slack_id, 'groups');

            // if key are not valid OR account no longer paid
            // kick the user from all channels to which he's member
            if ($this->isEnabledKey($keys) == false || $this->isActive($keys) == false) {
                $this->processChannelsKick($slackUser, $channels);
                $this->processGroupsKick($slackUser, $groups);

            // in other way, compute the gap and kick only the user
            // to channel from which he's no longer granted to be in
            } else {
                $allowedChannels = $this->allowedChannels($slackUser, false);
                $allowedGroups = $this->allowedChannels($slackUser, true);

                // remove channels in which user is already in from all granted channels and invite him
                $this->processChannelsKick($slackUser, array_diff($channels, $allowedChannels));
                // remove granted channels from channels in which user is already in and kick him
                $this->processGroupsKick($slackUser, array_diff($groups, $allowedGroups));
            }
        }

        return;
    }

    /**
     * Kick an user from each channel
     *
     * @param SlackUser $slackUser
     * @param $channels
     * @throws \Seat\Slackbot\Exceptions\SlackChannelException
     */
    function processChannelsKick(SlackUser $slackUser, $channels)
    {
        // iterate channel ID and call kick method from Slack Api
        foreach ($channels as $channelId) {
            $this->getSlackApi()->kickFromChannel($slackUser->slack_id, $channelId);
        }
    }

    /**
     * Kick an user from each group
     *
     * @param SlackUser $slackUser
     * @param $groups
     * @throws \Seat\Slackbot\Exceptions\SlackGroupException
     */
    function processGroupsKick(SlackUser $slackUser, $groups)
    {
        // iterate group ID and call kick method from Slack Api
        foreach ($groups as $groupId) {
            $this->getSlackApi()->kickFromGroup($slackUser->slack_id, $groupId);
        }
    }

}
