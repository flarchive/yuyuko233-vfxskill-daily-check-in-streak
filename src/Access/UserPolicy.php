<?php

namespace Yuyuko233\VfxskillDailyCheckInStreak\Access;

use Carbon\Carbon;
use Flarum\User\Access\AbstractPolicy;
use Flarum\User\User;

final class UserPolicy extends AbstractPolicy
{
    public function can(User $actor, string $ability, User $user)
    {
        if ($ability !== 'checkin.allowCheckIn') {
            return null;
        }

        return $actor->id === $user->id
            && $actor->hasPermission('checkin.allowCheckIn')
            && ! $this->isSuspended($user);
    }

    private function isSuspended(User $user): bool
    {
        return $user->suspended_until instanceof Carbon && $user->suspended_until->isFuture();
    }
}
