<?php
/**
 * User: Warlof Tutsimo <loic.leuilliot@gmail.com>
 * Date: 17/06/2016
 * Time: 18:43
 */

namespace Warlof\Seat\Slackbot\Database\Seeds;


use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ScheduleSeeder extends Seeder
{
    protected $schedule = [
        [
            'command'           => 'slack:update',
            'expression'        => '*/5 * * * * *',
            'allow_overlap'     => false,
            'allow_maintenance' => false,
            'ping_before'       => null,
            'ping_after'        => null
        ]
    ];

    public function run()
    {
        foreach ($this->schedule as $job) {
            $existing = DB::table('schedules')
                ->where('command', $job['command'])
                ->where('expression', $job['expression'])
                ->first();

            if (!$existing) {
                DB::table('schedules')->insert($job);
            }
        }
    }
}
