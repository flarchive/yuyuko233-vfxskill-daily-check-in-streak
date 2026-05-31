<?php

namespace Yuyuko233\VfxskillDailyCheckInStreak\Service;

use AntoineFr\Money\Event\MoneyUpdated;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use Illuminate\Contracts\Events\Dispatcher;
use Yuyuko233\VfxskillDailyCheckInStreak\Event\CheckinUpdated;
use Yuyuko233\VfxskillDailyCheckInStreak\Support\SettingsKeys;
use Yuyuko233\VfxskillDailyCheckInStreak\Support\StreakRewardRules;

final class CheckinRewardApplier
{
    public function __construct(
        private SettingsRepositoryInterface $settings,
        private Dispatcher $events
    ) {
    }

    public function canCheckinToday(?string $lastCheckinTime, ?int $currentTimestamp = null): bool
    {
        if ($lastCheckinTime === null || trim($lastCheckinTime) === '') {
            return true;
        }

        $lastTimestamp = strtotime($lastCheckinTime);

        if ($lastTimestamp === false) {
            return true;
        }

        return $this->todayMidnightTimestamp($currentTimestamp) > strtotime(date('Y-m-d', $lastTimestamp).' 00:00:00');
    }

    public function apply(User $user, ?int $currentTimestamp = null): float
    {
        $currentTimestamp ??= $this->currentTimestamp();
        $nextContinuousDays = $this->nextContinuousDays(
            $user->last_checkin_time,
            $currentTimestamp,
            (int) $user->total_continuous_checkin_count
        );
        $rewardMoney = $this->rewardMoneyForDays($nextContinuousDays);

        $user->total_continuous_checkin_count = $nextContinuousDays;
        $user->last_checkin_time = date('Y-m-d H:i:s', $currentTimestamp);
        $user->total_checkin_count += 1;

        if ($rewardMoney > 0 && isset($user->money)) {
            $user->money += $rewardMoney;

            if (class_exists(MoneyUpdated::class)) {
                $this->events->dispatch(new MoneyUpdated($user));
            }
        }

        $this->events->dispatch(new CheckinUpdated($user, $rewardMoney));

        return $rewardMoney;
    }

    public function rewardMoneyForDays(int $continuousDays): float
    {
        return StreakRewardRules::fromJson($this->settings->get(SettingsKeys::STREAK_REWARD_RULES))->rewardForDay($continuousDays);
    }

    private function nextContinuousDays(?string $lastCheckinTime, int $currentTimestamp, int $currentContinuousDays): int
    {
        $lastTimestamp = $lastCheckinTime ? strtotime($lastCheckinTime) : false;

        if ($lastTimestamp === false) {
            return 1;
        }

        $hoursSinceLastCheckin = ($currentTimestamp - strtotime(date('Y-m-d', $lastTimestamp).' 00:00:00')) / 3600;

        return $hoursSinceLastCheckin < 48 ? $currentContinuousDays + 1 : 1;
    }

    private function currentTimestamp(): int
    {
        return time() + $this->timezoneOffsetSeconds();
    }

    private function todayMidnightTimestamp(?int $currentTimestamp = null): int
    {
        return strtotime(date('Y-m-d', $currentTimestamp ?? $this->currentTimestamp()).' 00:00:00');
    }

    private function timezoneOffsetSeconds(): int
    {
        return (int) $this->settings->get(SettingsKeys::TIMEZONE, 0) * 60 * 60;
    }
}
