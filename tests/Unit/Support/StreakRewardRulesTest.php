<?php

namespace Yuyuko233\VfxskillDailyCheckInStreak\Tests\Unit\Support;

use PHPUnit\Framework\TestCase;
use Yuyuko233\VfxskillDailyCheckInStreak\Support\StreakRewardRules;

final class StreakRewardRulesTest extends TestCase
{
    public function test_defaults_reward_highest_reached_min_day(): void
    {
        $rules = StreakRewardRules::defaults();

        $this->assertSame(1.0, $rules->rewardForDay(1));
        $this->assertSame(2.0, $rules->rewardForDay(3));
        $this->assertSame(2.0, $rules->rewardForDay(6));
        $this->assertSame(5.0, $rules->rewardForDay(7));
        $this->assertSame(15.0, $rules->rewardForDay(45));
    }

    public function test_json_accepts_aliases_sorts_and_deduplicates_rules(): void
    {
        $rules = StreakRewardRules::fromJson(json_encode([
            ['day' => 7, 'amount' => 9],
            ['min_day' => 1, 'reward' => 2],
            ['minDay' => 7, 'amount' => 10],
            ['minDay' => 3, 'amount' => 5],
        ]));

        $this->assertSame([
            ['minDay' => 1, 'amount' => 2.0],
            ['minDay' => 3, 'amount' => 5.0],
            ['minDay' => 7, 'amount' => 10.0],
        ], $rules->toArray());
        $this->assertSame(5.0, $rules->rewardForDay(6));
        $this->assertSame(10.0, $rules->rewardForDay(7));
    }

    public function test_invalid_config_falls_back_to_defaults(): void
    {
        $this->assertSame(StreakRewardRules::DEFAULT_RULES, StreakRewardRules::fromJson('not-json')->toArray());
        $this->assertSame(StreakRewardRules::DEFAULT_RULES, StreakRewardRules::fromJson([['minDay' => 0, 'amount' => -1]])->toArray());
    }
}
