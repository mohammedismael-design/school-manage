<?php

use Illuminate\Support\Facades\Schedule;

// Run subscription expiry checks daily
Schedule::command('subscriptions:check-expiry')->daily();
