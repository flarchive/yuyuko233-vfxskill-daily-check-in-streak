<?php

use Flarum\Extend;
use Flarum\Api\Serializer\ForumSerializer;
use Flarum\Api\Serializer\UserSerializer;
use Flarum\User\Event\Saving;
use Flarum\User\User;
use Yuyuko233\VfxskillDailyCheckInStreak\Access\UserPolicy;
use Yuyuko233\VfxskillDailyCheckInStreak\Api\AddUserCheckinAttributes;
use Yuyuko233\VfxskillDailyCheckInStreak\Listener\DoCheckin;
use Yuyuko233\VfxskillDailyCheckInStreak\Support\SettingsKeys;
use Yuyuko233\VfxskillDailyCheckInStreak\Support\StreakRewardRules;

$extend = [
    (new Extend\Frontend('admin'))->js(__DIR__.'/js/dist/admin.js')->css(__DIR__.'/less/admin.less'),
    (new Extend\Frontend('forum'))->js(__DIR__.'/js/dist/forum.js')->css(__DIR__.'/less/forum.less'),

    (new Extend\Locales(__DIR__.'/locale')),

    (new Extend\Policy())->modelPolicy(User::class, UserPolicy::class),

    (new Extend\Event())->listen(Saving::class, [DoCheckin::class, 'checkinSaved']),
    (new Extend\ApiSerializer(UserSerializer::class))->attributes(AddUserCheckinAttributes::class),
    (new Extend\Settings())
        ->serializeToForum('forumCheckinRewardMoney', SettingsKeys::LEGACY_REWARD_MONEY, 'floatval', 0)
        ->serializeToForum('forumCheckinStreakRewardRules', SettingsKeys::STREAK_REWARD_RULES, function ($raw) {
            return StreakRewardRules::fromJson($raw)->toArray();
        })
        ->serializeToForum('forumAutoCheckin', SettingsKeys::AUTO_CHECK_IN, 'intval', 0)
        ->serializeToForum('forumAutoCheckinDelay', SettingsKeys::AUTO_CHECK_IN_DELAY, 'intval', 0)
        ->serializeToForum('forumCheckinTimeZone', SettingsKeys::TIMEZONE, 'intval', 0)
        ->serializeToForum('forumCheckinSuccessPromptType', SettingsKeys::SUCCESS_PROMPT_TYPE, 'intval', 0)
        ->serializeToForum('forumCheckinSuccessPromptText', SettingsKeys::SUCCESS_PROMPT_TEXT, 'strval')
        ->serializeToForum('forumCheckinSuccessPromptRewardText', SettingsKeys::SUCCESS_PROMPT_REWARD_TEXT, 'strval'),

    (new Extend\ApiSerializer(ForumSerializer::class))
        ->attribute('allowCheckIn', function (ForumSerializer $serializer) {
            return $serializer->getActor()->hasPermission('checkin.allowCheckIn');
        }),
];

return $extend;
