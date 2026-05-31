<?php

namespace Yuyuko233\VfxskillDailyCheckInStreak\Listener;

use Flarum\User\Event\Saving;
use Illuminate\Support\Arr;
use Yuyuko233\VfxskillDailyCheckInStreak\Service\CheckinRewardApplier;

final class DoCheckin
{
    public function __construct(private CheckinRewardApplier $rewardApplier)
    {
    }

    public function checkinSaved(Saving $event): void
    {
        $actor = $event->actor;
        $user = $event->user;

        if (! $actor->can('checkin.allowCheckIn', $user)) {
            return;
        }

        $attributes = Arr::get($event->data, 'attributes', []);

        if (! array_key_exists('canCheckin', $attributes) || ! $this->rewardApplier->canCheckinToday($user->last_checkin_time)) {
            return;
        }

        $this->rewardApplier->apply($user);
    }
}
