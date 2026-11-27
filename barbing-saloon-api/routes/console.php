<?php

use Illuminate\Support\Facades\Schedule;

Schedule::command('bookings:release-unpaid --minutes=15')->everyFiveMinutes();
Schedule::command('bookings:send-reminders --minutes=30')->everyMinute()->withoutOverlapping();
Schedule::command('bookings:process-no-shows')->everyMinute()->withoutOverlapping();
Schedule::command('payments:reconcile --minutes=10 --limit=50')->everyFifteenMinutes();
Schedule::command('db:backup')->dailyAt('02:00');
Schedule::command('sanctum:prune-expired --hours=24')->daily();
