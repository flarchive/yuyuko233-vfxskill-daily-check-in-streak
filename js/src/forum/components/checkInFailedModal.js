import Modal from 'flarum/components/Modal';

export default class checkInResultModal extends Modal {
  oninit(vnode) {
    super.oninit(vnode);
  }

  className() {
    return 'checkInResultModal Modal--small';
  }

  title() {
    return <div className="checkInResultModal failedTitleText">{app.translator.trans('vfxskill-checkin-streak.forum.check-in-failed')}</div>;
  }

  content() {
    return (
      <div className="Modal-body">
        <div className="checkInResultModal successText">{app.translator.trans('vfxskill-checkin-streak.forum.try-again-later')}</div>
      </div>
    );
  }
}
