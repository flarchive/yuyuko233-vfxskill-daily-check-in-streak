<?php

namespace Yuyuko233\VfxskillDailyCheckInStreak\Tests\Unit\Extension;

use PHPUnit\Framework\TestCase;

final class ExtendConfigTest extends TestCase
{
    public function test_extend_registers_new_namespace_assets_settings_and_listener(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3).'/extend.php');

        $this->assertStringContainsString('Yuyuko233\\VfxskillDailyCheckInStreak\\Listener\\DoCheckin', $source);
        $this->assertStringContainsString("->js(__DIR__.'/js/dist/admin.js')", $source);
        $this->assertStringContainsString("->css(__DIR__.'/less/admin.less')", $source);
        $this->assertStringContainsString("->js(__DIR__.'/js/dist/forum.js')", $source);
        $this->assertStringContainsString('SettingsKeys::STREAK_REWARD_RULES', $source);
        $this->assertStringContainsString("hasPermission('checkin.allowCheckIn')", str_replace(' ', '', $source));
    }

    public function test_admin_initializer_uses_final_extension_id(): void
    {
        $source = file_get_contents(dirname(__DIR__, 3).'/js/src/admin/index.js');

        $this->assertStringContainsString("const EXTENSION_ID = 'yuyuko233-vfxskill-daily-check-in-streak'", $source);
        $this->assertStringContainsString('.for(EXTENSION_ID)', $source);
        $this->assertStringContainsString('streakRewardRules', $source);
        $this->assertStringContainsString('renderStreakRewardRuleEditor', $source);
        $this->assertStringContainsString('addRewardRule', $source);
        $this->assertStringContainsString('add-reward-rule', $source);
        $this->assertStringContainsString('removeRewardRule', $source);
        $this->assertStringContainsString('remove-reward-rule', $source);
        $this->assertStringContainsString('type="number"', $source);
        $this->assertStringContainsString('minDay', $source);
        $this->assertStringContainsString('amount', $source);
        $this->assertStringContainsString('reward-rule-day', $source);
        $this->assertStringContainsString('reward-rule-amount', $source);
        $this->assertStringNotContainsString('<textarea', $source);
    }
}
