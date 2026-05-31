<?php

namespace Yuyuko233\VfxskillDailyCheckInStreak\Api;

use Flarum\Api\Serializer\UserSerializer;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use Yuyuko233\VfxskillDailyCheckInStreak\Support\SettingsKeys;
use Yuyuko233\VfxskillDailyCheckInStreak\Support\StreakRewardRules;

final class AddUserCheckinAttributes
{
    public function __construct(private SettingsRepositoryInterface $settings)
    {
    }

    public function __invoke(UserSerializer $serializer, User $user): array
    {
        $lastCheckinTime = $user->last_checkin_time;
        $timeDifference = $this->hoursUntilTodayEndsFromLastCheckin($lastCheckinTime);

        $canCheckin = $lastCheckinTime === null ? true : $timeDifference >= 24;

        return [
            'lastCheckinTime' => $lastCheckinTime,
            'totalContinuousCheckIn' => (int) $user->total_continuous_checkin_count,
            'lastCheckinRewardMoney' => $canCheckin ? 0.0 : $this->rewardMoneyForDays((int) $user->total_continuous_checkin_count),
            'nextCheckinRewardMoney' => $this->nextRewardMoney($user, $timeDifference),
            'checkInCompatibleExtensions' => $this->compatibleExtensions(),
            'canCheckin' => $canCheckin,
            'canCheckinContinuous' => $timeDifference < 48,
        ];
    }

    private function nextRewardMoney(User $user, float $timeDifference): float
    {
        $currentDays = (int) $user->total_continuous_checkin_count;
        $nextDays = $timeDifference < 48 ? $currentDays + 1 : 1;

        return $this->rewardMoneyForDays($nextDays);
    }

    private function rewardMoneyForDays(int $continuousDays): float
    {
        return StreakRewardRules::fromJson($this->settings->get(SettingsKeys::STREAK_REWARD_RULES))->rewardForDay($continuousDays);
    }

    /**
     * @return array<int, string>
     */
    private function compatibleExtensions(): array
    {
        $enabled = json_decode((string) $this->settings->get('extensions_enabled', '[]'), true);

        if (! is_array($enabled)) {
            return [];
        }

        return array_values(array_intersect(['fof-follow-tags'], $enabled));
    }

    private function hoursUntilTodayEndsFromLastCheckin(?string $lastCheckinTime): float
    {
        $timezone = (int) $this->settings->get(SettingsKeys::TIMEZONE, 0);
        $currentTime = time() + $timezone * 60 * 60;
        $lastTimestamp = $lastCheckinTime === null ? 0 : strtotime($lastCheckinTime);

        if ($lastTimestamp === false) {
            $lastTimestamp = 0;
        }

        return (strtotime(date('Y-m-d', $currentTime).' 23:59:59') - $lastTimestamp) / 3600;
    }
}
