# VFXSkill Daily Check In Streak

A Flarum extension based on `ziiven/flarum-daily-check-in`. It adds a daily check-in button and rewards users through `antoinefr/flarum-ext-money` using configurable streak tiers.

## Features

- Daily check-in button on the forum index sidebar.
- Permission-controlled check-in access.
- Timezone and optional auto check-in settings.
- Configurable success prompts.
- Streak reward tiers managed through the admin settings form.
- Existing Ziiven check-in user columns are reused for easier migration.

## Reward Rules

The admin setting `Streak reward rules` lets administrators add or remove
reward tiers. Each tier has a minimum continuous-day threshold and a reward
amount. The setting is still stored internally as a JSON string for backend
compatibility:

```json
[
  {"minDay": 1, "amount": 1},
  {"minDay": 3, "amount": 2},
  {"minDay": 7, "amount": 5},
  {"minDay": 15, "amount": 8},
  {"minDay": 30, "amount": 15}
]
```

The reward uses the highest `minDay` reached by the current streak. For example, day 6 receives the day 3 reward, while day 7 receives the day 7 reward. If the stored JSON is invalid, the default rules above are used.

## Installation

Install directly from this GitHub repository:

```sh
composer config repositories.vfxskill-daily-check-in-streak vcs https://github.com/popoopendoor/vfxskill-daily-check-in-streak
composer require yuyuko233/vfxskill-daily-check-in-streak:"dev-main"
php flarum migrate
php flarum cache:clear
```

If the package is later published to Packagist, the repository configuration
step can be removed and the package can be installed directly with:

```sh
composer require yuyuko233/vfxskill-daily-check-in-streak
```

For local development in this repository, the package is installed through a Composer path repository:

```sh
composer require yuyuko233/vfxskill-daily-check-in-streak *@dev
```

## Migration Notes

This extension keeps the original user columns:

- `total_checkin_count`
- `total_continuous_checkin_count`
- `last_checkin_time`

If you are replacing `ziiven/flarum-daily-check-in`, disable the original extension before enabling this one to avoid registering duplicate check-in buttons and duplicate listeners.

## Attribution

Original extension: [Ziiven/flarum-daily-check-in](https://github.com/Ziiven/flarum-daily-check-in), licensed under MIT.
