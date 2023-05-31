<?php

class AppScheduleBuilder implements \Zenstruck\ScheduleBundle\Schedule\ScheduleBuilder
{
    public function buildSchedule(\Zenstruck\ScheduleBundle\Schedule $schedule): void
    {
        $schedule
            ->timezone('UTC')
            ->environments('prod')
        ;

        $schedule->addCommand('app:inventory:cleanup')
            ->description('Clean up the inventory.')
            ->everyTenMinutes()
        ;
        $schedule->addCommand("messenger:consume async -vv --limit=10")
            ->everyFifteenMinutes()
        ;

        // ...
    }

}