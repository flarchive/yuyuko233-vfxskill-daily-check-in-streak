import { extend } from 'flarum/extend';
import IndexPage from 'flarum/forum/components/IndexPage';
import Button from 'flarum/components/Button';
import Alert from 'flarum/common/components/Alert';
import checkInSuccessModal from './components/checkInSuccessModal';
import checkInFailedModal from './components/checkInFailedModal';

function rewardText() {
  const rewardMoney = app.session.user.attribute('lastCheckinRewardMoney') || 0;
  const moneyName = app.forum.attribute('antoinefr-money.moneyname') || '[money]';

  return moneyName.replace('[money]', rewardMoney);
}

app.initializers.add('yuyuko233-vfxskill-daily-check-in-streak', () => {
  extend(IndexPage.prototype, 'sidebarItems', function (items) {
    if (app.session.user === null || app.forum.attribute('allowCheckIn') !== true) {
      return;
    }

    const routeName = app.current.get('routeName');
    const totalContinuousCheckIn = app.session.user.attribute('totalContinuousCheckIn') || 0;
    const canCheckin = app.session.user.attribute('canCheckin');
    const canCheckinContinuous = app.session.user.attribute('canCheckinContinuous');
    const forumCheckinSuccessPromptType = app.forum.attribute('forumCheckinSuccessPromptType');
    const checkInCompatibleExtensions = app.session.user.attribute('checkInCompatibleExtensions') || [];
    let itemName = 'forum-checkin';

    if (routeName === 'tag' && checkInCompatibleExtensions.indexOf('fof-follow-tags') !== -1) {
      itemName = 'forum-checkin-1';
    }

    if (canCheckin === true) {
      items.add(
        itemName,
        Button.component(
          {
            icon: 'fas fa-calendar',
            className: 'Button CheckInButton--yellow',
            itemClassName: 'App-primaryControl',
            id: 'checkInButton',
            onclick: () => {
              app.session.user
                .save({
                  canCheckin: false,
                  totalContinuousCheckIn: canCheckinContinuous === true ? totalContinuousCheckIn + 1 : 1,
                })
                .then(() => {
                  const forumCheckinSuccessPromptText = app.forum.attribute('forumCheckinSuccessPromptText') || '';
                  const forumCheckinSuccessPromptRewardText = app.forum.attribute('forumCheckinSuccessPromptRewardText') || '';
                  const canCheckinRecheck = app.session.user.attribute('canCheckin');

                  if (forumCheckinSuccessPromptText === '' && forumCheckinSuccessPromptRewardText === '') {
                    return;
                  }

                  if (forumCheckinSuccessPromptType === 1) {
                    if (canCheckinRecheck === false) {
                      if (forumCheckinSuccessPromptText !== '') {
                        app.alerts.show(
                          Alert,
                          { type: 'success' },
                          forumCheckinSuccessPromptText.replace('[days]', app.session.user.attribute('totalContinuousCheckIn'))
                        );
                      }

                      if (app.forum.attribute('antoinefr-money.moneyname') !== undefined && forumCheckinSuccessPromptRewardText !== '') {
                        app.alerts.show(Alert, { type: 'success' }, forumCheckinSuccessPromptRewardText.replace('[reward]', rewardText()));
                      }
                    } else {
                      app.alerts.show(
                        Alert,
                        { type: 'error' },
                        `${app.translator.trans('vfxskill-checkin-streak.forum.check-in-failed')} ${app.translator.trans(
                          'vfxskill-checkin-streak.forum.try-again-later'
                        )}`
                      );
                    }
                  } else if (forumCheckinSuccessPromptType === 2) {
                    app.modal.show(canCheckinRecheck === false ? checkInSuccessModal : checkInFailedModal);
                  }
                });
            },
          },
          app.translator.trans('vfxskill-checkin-streak.forum.check-in')
        ),
        50
      );

      const forumAutoCheckIn = app.forum.attribute('forumAutoCheckin');
      const forumAutoCheckInDelay = app.forum.attribute('forumAutoCheckinDelay');

      if (forumAutoCheckIn === 1) {
        setTimeout(() => {
          $('#checkInButton').click();
        }, forumAutoCheckInDelay);
      }

      return;
    }

    const checkinButtonText =
      totalContinuousCheckIn <= 1
        ? app.translator.trans('vfxskill-checkin-streak.forum.checked-in-day', { count: totalContinuousCheckIn })
        : app.translator.trans('vfxskill-checkin-streak.forum.checked-in-days', { count: totalContinuousCheckIn });

    items.add(
      itemName,
      Button.component(
        {
          icon: 'fas fa-calendar-check',
          className: 'Button CheckInButton--green',
          itemClassName: 'App-primaryControl',
          disabled: true,
        },
        checkinButtonText
      ),
      50
    );
  });
});
