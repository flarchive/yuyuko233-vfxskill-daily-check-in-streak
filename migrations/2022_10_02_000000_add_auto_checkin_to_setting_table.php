<?php

use Flarum\Database\Migration;
use Yuyuko233\VfxskillDailyCheckInStreak\Support\SettingsKeys;

return Migration::addSettings([
    SettingsKeys::AUTO_CHECK_IN => 0,
    SettingsKeys::AUTO_CHECK_IN_DELAY => 0,
    SettingsKeys::TIMEZONE => 0,
]);
