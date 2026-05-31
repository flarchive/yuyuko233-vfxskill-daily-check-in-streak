<?php

namespace Yuyuko233\VfxskillDailyCheckInStreak\Tests\Unit\Service;

use AntoineFr\Money\Event\MoneyUpdated;
use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\User;
use Illuminate\Contracts\Events\Dispatcher;
use PHPUnit\Framework\TestCase;
use Yuyuko233\VfxskillDailyCheckInStreak\Event\CheckinUpdated;
use Yuyuko233\VfxskillDailyCheckInStreak\Service\CheckinRewardApplier;
use Yuyuko233\VfxskillDailyCheckInStreak\Support\SettingsKeys;

final class CheckinRewardApplierTest extends TestCase
{
    private FakeDispatcher $events;

    protected function setUp(): void
    {
        parent::setUp();

        $this->events = new FakeDispatcher();
    }

    public function test_apply_continues_streak_and_adds_matching_reward(): void
    {
        $user = $this->userWithMoney([
            'last_checkin_time' => '2026-05-30 10:00:00',
            'total_checkin_count' => 4,
            'total_continuous_checkin_count' => 2,
            'money' => 10.0,
        ]);

        $reward = $this->service()->apply($user, strtotime('2026-05-31 09:00:00'));

        $this->assertSame(2.0, $reward);
        $this->assertSame(3, $user->total_continuous_checkin_count);
        $this->assertSame(5, $user->total_checkin_count);
        $this->assertSame(12.0, $user->money);
        $this->assertInstanceOf(MoneyUpdated::class, $this->events->events[0] ?? null);
        $this->assertInstanceOf(CheckinUpdated::class, $this->events->events[1] ?? null);
    }

    public function test_apply_resets_broken_streak_to_day_one_reward(): void
    {
        $user = $this->userWithMoney([
            'last_checkin_time' => '2026-05-28 10:00:00',
            'total_checkin_count' => 4,
            'total_continuous_checkin_count' => 12,
            'money' => 10.0,
        ]);

        $reward = $this->service()->apply($user, strtotime('2026-05-31 09:00:00'));

        $this->assertSame(1.0, $reward);
        $this->assertSame(1, $user->total_continuous_checkin_count);
        $this->assertSame(11.0, $user->money);
    }

    public function test_apply_without_money_attribute_does_not_dispatch_money_event(): void
    {
        $user = new User();
        $user->last_checkin_time = null;
        $user->total_checkin_count = 0;
        $user->total_continuous_checkin_count = 0;

        $reward = $this->service()->apply($user, strtotime('2026-05-31 09:00:00'));

        $this->assertSame(1.0, $reward);
        $this->assertCount(1, $this->events->events);
        $this->assertInstanceOf(CheckinUpdated::class, $this->events->events[0]);
    }

    public function test_zero_reward_updates_counts_without_money_event(): void
    {
        $service = new CheckinRewardApplier(new FakeSettings([
            SettingsKeys::STREAK_REWARD_RULES => '[{"minDay":1,"amount":0}]',
        ]), $this->events);
        $user = $this->userWithMoney([
            'last_checkin_time' => null,
            'total_checkin_count' => 0,
            'total_continuous_checkin_count' => 0,
            'money' => 10.0,
        ]);

        $reward = $service->apply($user, strtotime('2026-05-31 09:00:00'));

        $this->assertSame(0.0, $reward);
        $this->assertSame(10.0, $user->money);
        $this->assertCount(1, $this->events->events);
        $this->assertInstanceOf(CheckinUpdated::class, $this->events->events[0]);
    }

    public function test_can_checkin_today_blocks_second_same_day_checkin(): void
    {
        $service = $this->service();

        $this->assertFalse($service->canCheckinToday('2026-05-31 08:00:00', strtotime('2026-05-31 12:00:00')));
        $this->assertTrue($service->canCheckinToday('2026-05-30 23:00:00', strtotime('2026-05-31 01:00:00')));
    }

    private function service(): CheckinRewardApplier
    {
        return new CheckinRewardApplier(new FakeSettings(), $this->events);
    }

    private function userWithMoney(array $attributes): User
    {
        $user = new User();

        foreach ($attributes as $key => $value) {
            $user->{$key} = $value;
        }

        return $user;
    }
}

final class FakeSettings implements SettingsRepositoryInterface
{
    public function __construct(private array $values = [])
    {
    }

    public function all(): array
    {
        return $this->values;
    }

    public function get($key, $default = null)
    {
        return $this->values[$key] ?? $default;
    }

    public function set($key, $value)
    {
        $this->values[$key] = $value;
    }

    public function delete($keyLike)
    {
        unset($this->values[$keyLike]);
    }
}

final class FakeDispatcher implements Dispatcher
{
    public array $events = [];

    public function listen($events, $listener = null)
    {
    }

    public function hasListeners($eventName)
    {
        return false;
    }

    public function subscribe($subscriber)
    {
    }

    public function until($event, $payload = [])
    {
        return null;
    }

    public function dispatch($event, $payload = [], $halt = false)
    {
        $this->events[] = $event;

        return [];
    }

    public function push($event, $payload = [])
    {
    }

    public function flush($event)
    {
    }

    public function forget($event)
    {
    }

    public function forgetPushed()
    {
    }
}
