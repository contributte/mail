<?php declare(strict_types = 1);

namespace Contributte\Mail\Tracy;

use Contributte\Mail\Mailer\TraceableMailer;
use Tracy\IBarPanel;

class MailPanel implements IBarPanel
{

	private TraceableMailer $traceableMailer;

	public function setTraceableMailer(TraceableMailer $traceableMailer): void
	{
		$this->traceableMailer = $traceableMailer;
	}

	/**
	 * Renders HTML code for custom tab.
	 */
	public function getTab(): string
	{
		$mailsCount = count($this->traceableMailer->getMails());

		if ($mailsCount === 0) {
			return '';
		}

		ob_start();

		require 'templates/tab.phtml';

		return (string) ob_get_clean();
	}

	/**
	 * Renders HTML code for custom panel.
	 */
	public function getPanel(): string
	{
		// phpcs:disable
		$mails = $this->traceableMailer->getMails();
		ob_start();
		require 'templates/panel.phtml';
		return (string) ob_get_clean();
	}

}
