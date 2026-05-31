<?php

namespace Yuyuko233\VfxskillDailyCheckInStreak\Support;

final class StreakRewardRules
{
    public const DEFAULT_RULES = [
        ['minDay' => 1, 'amount' => 1.0],
        ['minDay' => 3, 'amount' => 2.0],
        ['minDay' => 7, 'amount' => 5.0],
        ['minDay' => 15, 'amount' => 8.0],
        ['minDay' => 30, 'amount' => 15.0],
    ];

    /**
     * @param array<int, array{minDay:int, amount:float}> $rules
     */
    private function __construct(private array $rules)
    {
    }

    public static function fromJson(mixed $raw): self
    {
        if (is_string($raw) && trim($raw) !== '') {
            $decoded = json_decode($raw, true);
            $raw = json_last_error() === JSON_ERROR_NONE ? $decoded : null;
        }

        if (! is_array($raw)) {
            return self::defaults();
        }

        $rules = [];

        foreach ($raw as $row) {
            if (! is_array($row)) {
                continue;
            }

            $minDay = self::positiveInteger($row['minDay'] ?? $row['day'] ?? $row['min_day'] ?? null);
            $amount = self::nonNegativeFloat($row['amount'] ?? $row['reward'] ?? null);

            if ($minDay === null || $amount === null) {
                continue;
            }

            $rules[$minDay] = ['minDay' => $minDay, 'amount' => $amount];
        }

        if ($rules === []) {
            return self::defaults();
        }

        ksort($rules, SORT_NUMERIC);

        return new self(array_values($rules));
    }

    public static function defaults(): self
    {
        return new self(self::DEFAULT_RULES);
    }

    public function rewardForDay(int $continuousDays): float
    {
        $reward = 0.0;

        foreach ($this->rules as $rule) {
            if ($continuousDays < $rule['minDay']) {
                break;
            }

            $reward = $rule['amount'];
        }

        return $reward;
    }

    /**
     * @return array<int, array{minDay:int, amount:float}>
     */
    public function toArray(): array
    {
        return $this->rules;
    }

    public function toJson(): string
    {
        return json_encode($this->rules, JSON_UNESCAPED_SLASHES);
    }

    private static function positiveInteger(mixed $value): ?int
    {
        if (! is_numeric($value)) {
            return null;
        }

        $integer = (int) $value;

        return $integer >= 1 ? $integer : null;
    }

    private static function nonNegativeFloat(mixed $value): ?float
    {
        if (! is_numeric($value)) {
            return null;
        }

        $float = (float) $value;

        return $float >= 0 ? $float : null;
    }
}
