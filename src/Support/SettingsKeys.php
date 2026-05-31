<?php

namespace Yuyuko233\VfxskillDailyCheckInStreak\Support;

final class SettingsKeys
{
    public const PREFIX = 'yuyuko233-vfxskill-daily-check-in-streak';

    public const AUTO_CHECK_IN = self::PREFIX.'.autoCheckIn';
    public const AUTO_CHECK_IN_DELAY = self::PREFIX.'.autoCheckInDelay';
    public const LEGACY_REWARD_MONEY = self::PREFIX.'.legacyCheckinRewardMoney';
    public const STREAK_REWARD_RULES = self::PREFIX.'.streakRewardRules';
    public const SUCCESS_PROMPT_REWARD_TEXT = self::PREFIX.'.checkinSuccessPromptRewardText';
    public const SUCCESS_PROMPT_TEXT = self::PREFIX.'.checkinSuccessPromptText';
    public const SUCCESS_PROMPT_TYPE = self::PREFIX.'.checkinSuccessPromptType';
    public const TIMEZONE = self::PREFIX.'.checkinTimeZone';
}
