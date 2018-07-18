<?php

namespace Contributte\Mail\Tracy;

use Contributte\Mail\Mailer\TraceableMailer;
use Tracy\IBarPanel;

final class MailPanel implements IBarPanel
{

	const ICON_CLOSE = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAAdVBMVEUAAAAAff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff9u3TpDAAAAJnRSTlMAAQIEBQ4PEBgcHSUmS01PUV1eZHV+f4OGm6aytMHFzs/p6/f7/czO+8YAAABoSURBVBgZhcFJEoIwAEXBH6OCE6AojoACefc/oqmUi0QXdutHNRIZSw0kXjqROGp2I3I1Ks1u4mMqTCEey6wj6LLFHYE72BqvtnsHwnvm67Zd5T2eCM6b7YVAfJEj4dSQaCQ7j1j99wbHWRkFec3JOQAAAABJRU5ErkJggg==';
	const ICON_OPEN = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAABAAAAAQCAMAAAAoLQ9TAAAAclBMVEUAAAAAff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff8Aff/QW4CAAAAAJXRSTlMAAgMFBg0OEBITFBYXPD1ER09QUYaMj5ulprm61+Dk5ujp9fn987zOAwAAAG9JREFUGBldwYkWQkAAhtEfoUULiqnI+r3/KzbjOA7u1cR7wdPTIqmwqotmObNcTmBYmEA616zUJ/Vs9GJHjGnLrM1GRBcFhsn7EHUIhqtSrEy3AYRV+Mn3k/gFlnB+9zB8NDhiR+yoZKOU4uNKrD+K5BsoDXCXJAAAAABJRU5ErkJggg==';

	/** @var TraceableMailer */
	private $traceableMailer;

	/**
	 * @param TraceableMailer $traceableMailer
	 * @return void
	 */
	public function setTraceableMailer(TraceableMailer $traceableMailer)
	{
		$this->traceableMailer = $traceableMailer;
	}

	/**
	 * Renders HTML code for custom tab.
	 *
	 * @return string
	 */
	public function getTab()
	{
		$mailsCount = count($this->traceableMailer->getMails());
		return sprintf(
			'<span class="tracy-label"><img height="16" src="%s"/>&nbsp;(%d)</span>',
			$mailsCount ? self::ICON_OPEN : self::ICON_CLOSE,
			$mailsCount
		);
	}

	/**
	 * Renders HTML code for custom panel.
	 *
	 * @return string
	 */
	public function getPanel()
	{
		$mails = $this->traceableMailer->getMails();
		ob_start();
		require 'Template/MailPanel.phtml';
		return ob_get_clean();
	}

}
