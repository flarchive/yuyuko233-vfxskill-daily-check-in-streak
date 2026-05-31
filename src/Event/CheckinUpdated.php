<?php

namespace Yuyuko233\VfxskillDailyCheckInStreak\Event;

use Flarum\User\User;

final class CheckinUpdated
{
    public function __construct(
        public User $user,
        public float $rewardMoney
    ) {
    }
}
