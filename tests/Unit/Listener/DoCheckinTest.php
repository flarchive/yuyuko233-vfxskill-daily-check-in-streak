<?php

namespace Yuyuko233\VfxskillDailyCheckInStreak\Tests\Unit\Listener;

use Flarum\Settings\SettingsRepositoryInterface;
use Flarum\User\Event\Saving;
use Flarum\User\User;
use Illuminate\Contracts\Events\Dispatcher;
use PHPUnit\Framework\TestCase;
use Yuyuko233\VfxskillDailyCheckInStreak\Event\CheckinUpdated;
use Yuyuko233\VfxskillDailyCheckInStreak\Listener\DoCheckin;
use Yuyuko233\VfxskillDailyCheckInStreak\Service\CheckinRewardApplier;
use Yuyuko233\VfxskillDailyCheckInStreak\Support\SettingsKeys;

final class DoCheckinTest extends TestCase
{
    public function test_checkin_uses_server_streak_instead_of_client_submitted_days(): void
    {
        $events = new ListenerFakeDispatcher();
        $listener = new DoCheckin(new CheckinRewardApplier(new ListenerFakeSettings(), $events));
        $actor = new ListenerActor(true);
        $actor->id = 7;
        $user = $this->user([
            'id' => 7,
            'last_checkin_time' => '2026-05-30 10:00:00',
            'total_checkin_count' => 4,
            'total_continuous_checkin_count' => 2,
            'money' => 10.0,
        ]);

        $listener->checkinSaved(new Saving($user, $actor, [
            'attributes' => [
                'canCheckin' => false,
                'totalContinuousCheckIn' => 999,
            ],
        ]));

        $this->assertSame(3, $user->total_continuous_checkin_count);
        $this->assertSame(5, $user->total_checkin_count);
        $this->assertSame(12.0, $user->money);
        $this->assertInstanceOf(CheckinUpdated::class, end($events->events));
    }

    public function test_checkin_ignores_payload_without_checkin_signal(): void
    {
        $events = new ListenerFakeDispatcher();
        $listener = new DoCheckin(new CheckinRewardApplier(new ListenerFakeSettings(), $events));
        $actor = new ListenerActor(true);
        $user = $this->user([
            'last_checkin_time' => '2026-05-30 10:00:00',
            'total_checkin_count' => 4,
            'total_continuous_checkin_count' => 2,
            'money' => 10.0,
        ]);

        $listener->checkinSaved(new Saving($user, $actor, [
            'attributes' => ['totalContinuousCheckIn' => 999],
        ]));

        $this->assertSame(2, $user->total_continuous_checkin_count);
        $this->assertSame(4, $user->total_checkin_count);
        $this->assertSame(10.0, $user->money);
        $this->assertSame([], $events->events);
    }

    public function test_checkin_respects_permission_denial(): void
    {
        $events = new ListenerFakeDispatcher();
        $listener = new DoCheckin(new CheckinRewardApplier(new ListenerFakeSettings(), $events));
        $user = $this->user([
            'last_checkin_time' => '2026-05-30 10:00:00',
            'total_checkin_count' => 4,
            'total_continuous_checkin_count' => 2,
            'money' => 10.0,
        ]);

        $listener->checkinSaved(new Saving($user, new ListenerActor(false), [
            'attributes' => ['canCheckin' => true],
        ]));

        $this->assertSame(2, $user->total_continuous_checkin_count);
        $this->assertSame(4, $user->total_checkin_count);
        $this->assertSame([], $events->events);
    }

    private function user(array $attributes): User
    {
        $user = new User();

        foreach ($attributes as $key => $value) {
            $user->{$key} = $value;
        }

        return $user;
    }
}

final class ListenerActor extends User
{
    public function __construct(private bool $allowed)
    {
        parent::__construct();
    }

    public function can($ability, $arguments = null)
    {
        return $ability === 'checkin.allowCheckIn' && $this->allowed;
    }
}

final class ListenerFakeSettings implements SettingsRepositoryInterface
{
    public function __construct(private array $values = [])
    {
        $this->values += [
            SettingsKeys::STREAK_REWARD_RULES => '[{"minDay":1,"amount":1},{"minDay":3,"amount":2}]',
        ];
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

final class ListenerFakeDispatcher implements Dispatcher
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
