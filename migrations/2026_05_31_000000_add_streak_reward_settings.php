<?php

use Flarum\Database\Migration;
use Yuyuko233\VfxskillDailyCheckInStreak\Support\SettingsKeys;
use Yuyuko233\VfxskillDailyCheckInStreak\Support\StreakRewardRules;

return Migration::addSettings([
    SettingsKeys::STREAK_REWARD_RULES => StreakRewardRules::defaults()->toJson(),
    SettingsKeys::LEGACY_REWARD_MONEY => 0,
]);
