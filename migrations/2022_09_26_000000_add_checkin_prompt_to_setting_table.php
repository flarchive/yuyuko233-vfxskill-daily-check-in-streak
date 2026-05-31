<?php

use Flarum\Database\Migration;
use Yuyuko233\VfxskillDailyCheckInStreak\Support\SettingsKeys;

return Migration::addSettings([
    SettingsKeys::SUCCESS_PROMPT_TYPE => 0,
    SettingsKeys::SUCCESS_PROMPT_TEXT => '',
    SettingsKeys::SUCCESS_PROMPT_REWARD_TEXT => 'Gained [reward] reward',
]);
