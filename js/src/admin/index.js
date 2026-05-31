const EXTENSION_ID = 'yuyuko233-vfxskill-daily-check-in-streak';
const SETTING_PREFIX = 'yuyuko233-vfxskill-daily-check-in-streak';

const DEFAULT_REWARD_RULES = [
  { minDay: 1, amount: 1 },
  { minDay: 3, amount: 2 },
  { minDay: 7, amount: 5 },
  { minDay: 15, amount: 8 },
  { minDay: 30, amount: 15 },
];

function t(key, params = {}) {
  return app.translator.trans(`vfxskill-checkin-streak.admin.settings.${key}`, params);
}

function parseRewardRules(raw) {
  let decoded = raw;

  if (typeof raw === 'string' && raw.trim() !== '') {
    try {
      decoded = JSON.parse(raw);
    } catch (error) {
      decoded = null;
    }
  }

  if (!Array.isArray(decoded)) {
    return DEFAULT_REWARD_RULES.map((rule) => ({ ...rule }));
  }

  const rules = decoded
    .map((rule) => ({
      minDay: rule && (rule.minDay ?? rule.day ?? rule.min_day ?? ''),
      amount: rule && (rule.amount ?? rule.reward ?? ''),
    }))
    .filter((rule) => rule.minDay !== '' || rule.amount !== '');

  return rules.length > 0 ? rules : DEFAULT_REWARD_RULES.map((rule) => ({ ...rule }));
}

function persistRewardRules(setting, rules) {
  setting(
    JSON.stringify(
      rules.map((rule) => ({
        minDay: rule.minDay,
        amount: rule.amount,
      }))
    )
  );
}

function updateRewardRule(setting, index, patch) {
  const rules = parseRewardRules(setting());
  rules[index] = { ...rules[index], ...patch };
  persistRewardRules(setting, rules);
}

function addRewardRule(setting) {
  const rules = parseRewardRules(setting());
  const numericDays = rules.map((rule) => Number(rule.minDay)).filter(Number.isFinite);
  const lastAmount = rules.length > 0 ? rules[rules.length - 1].amount : 0;
  rules.push({
    minDay: numericDays.length > 0 ? Math.max(...numericDays) + 1 : 1,
    amount: lastAmount,
  });
  persistRewardRules(setting, rules);
}

function removeRewardRule(setting, index) {
  const rules = parseRewardRules(setting());

  if (rules.length <= 1) {
    return;
  }

  rules.splice(index, 1);
  persistRewardRules(setting, rules);
}

function renderStreakRewardRuleEditor(context) {
  const setting = context.setting(`${SETTING_PREFIX}.streakRewardRules`);
  const rules = parseRewardRules(setting());

  return (
    <div className="Form-group VfxCheckinRewardRules">
      <div className="VfxCheckinRewardRules-header">
        <div>
          <label>{t('streak-reward-rules')}</label>
          <p className="helpText">{t('streak-reward-rules-help')}</p>
        </div>
        <button className="Button Button--secondary" type="button" onclick={() => addRewardRule(setting)}>
          {t('add-reward-rule')}
        </button>
      </div>

      <div className="VfxCheckinRewardRules-table">
        <div className="VfxCheckinRewardRules-row VfxCheckinRewardRules-row--head">
          <span>{t('reward-rule-day')}</span>
          <span>{t('reward-rule-amount')}</span>
          <span>{t('reward-rule-actions')}</span>
        </div>

        {rules.map((rule, index) => (
          <div className="VfxCheckinRewardRules-row" key={index}>
            <label className="VfxCheckinRewardRules-field">
              <span>{t('reward-rule-day')}</span>
              <input
                className="FormControl"
                type="number"
                min="1"
                step="1"
                value={rule.minDay}
                oninput={(event) => updateRewardRule(setting, index, { minDay: event.target.value })}
              />
            </label>
            <label className="VfxCheckinRewardRules-field">
              <span>{t('reward-rule-amount')}</span>
              <input
                className="FormControl"
                type="number"
                min="0"
                step="any"
                value={rule.amount}
                oninput={(event) => updateRewardRule(setting, index, { amount: event.target.value })}
              />
            </label>
            <button
              className="Button Button--danger"
              type="button"
              disabled={rules.length <= 1}
              onclick={() => removeRewardRule(setting, index)}
            >
              {t('remove-reward-rule')}
            </button>
          </div>
        ))}
      </div>
    </div>
  );
}

app.initializers.add(EXTENSION_ID, () => {
  app.extensionData
    .for(EXTENSION_ID)
    .registerSetting(function () {
      return renderStreakRewardRuleEditor(this);
    })
    .registerSetting({
      setting: `${SETTING_PREFIX}.checkinTimeZone`,
      label: app.translator.trans('vfxskill-checkin-streak.admin.settings.timezone'),
      type: 'number',
    })
    .registerSetting({
      setting: `${SETTING_PREFIX}.autoCheckIn`,
      label: app.translator.trans('vfxskill-checkin-streak.admin.settings.auto-check-in'),
      type: 'switch',
    })
    .registerSetting({
      setting: `${SETTING_PREFIX}.autoCheckInDelay`,
      label: app.translator.trans('vfxskill-checkin-streak.admin.settings.auto-check-in-delay'),
      type: 'number',
    })
    .registerSetting({
      setting: `${SETTING_PREFIX}.checkinSuccessPromptType`,
      label: app.translator.trans('vfxskill-checkin-streak.admin.settings.check-in-success-prompt-type'),
      type: 'select',
      options: {
        0: app.translator.trans('vfxskill-checkin-streak.admin.settings.None'),
        1: app.translator.trans('vfxskill-checkin-streak.admin.settings.Alert'),
        2: app.translator.trans('vfxskill-checkin-streak.admin.settings.Modal'),
      },
    })
    .registerSetting(function () {
      return (
        <div className="Form-group">
          <label>{app.translator.trans('vfxskill-checkin-streak.admin.settings.check-in-success-prompt-text')}</label>
          <p className="helpText">{app.translator.trans('vfxskill-checkin-streak.admin.settings.check-in-success-prompt-example-text')}</p>
          <input type="text" className="FormControl" bidi={this.setting(`${SETTING_PREFIX}.checkinSuccessPromptText`)} />
        </div>
      );
    })
    .registerSetting(function () {
      return (
        <div className="Form-group">
          <label>{app.translator.trans('vfxskill-checkin-streak.admin.settings.check-in-success-prompt-reward-text')}</label>
          <p className="helpText">{app.translator.trans('vfxskill-checkin-streak.admin.settings.reward-money-requirement')}</p>
          <p className="helpText">{app.translator.trans('vfxskill-checkin-streak.admin.settings.check-in-success-prompt-example-reward-text')}</p>
          <input type="text" className="FormControl" bidi={this.setting(`${SETTING_PREFIX}.checkinSuccessPromptRewardText`)} />
        </div>
      );
    })
    .registerPermission(
      {
        icon: 'fas fa-id-card',
        label: app.translator.trans('vfxskill-checkin-streak.admin.settings.allow-check-in'),
        permission: 'checkin.allowCheckIn',
      },
      'moderate',
      90
    );
});
