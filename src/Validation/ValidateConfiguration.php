<?php
/**
 * User: Warlof Tutsimo <loic.leuilliot@gmail.com>
 * Date: 20/06/2016
 * Time: 22:12
 */

namespace Seat\Slackbot\Validation;

use App\Http\Requests\Request;

class ValidateConfiguration extends Request
{
    public function rules()
    {
        return [
            'slack-configuration-token' => 'required|string'
        ];
    }
}