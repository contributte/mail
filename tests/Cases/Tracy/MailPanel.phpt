<?php declare(strict_types = 1);

namespace Tests\Cases\Tracy;

use Contributte\Mail\Mailer\DevNullMailer;
use Contributte\Mail\Mailer\TraceableMailer;
use Contributte\Mail\Tracy\MailPanel;
use Contributte\Tester\Toolkit;
use Nette\Mail\Message;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';

// Test getTab returns empty string when no mails
Toolkit::test(function (): void {
	$innerMailer = new DevNullMailer();
	$traceableMailer = new TraceableMailer($innerMailer);

	$panel = new MailPanel();
	$panel->setTraceableMailer($traceableMailer);

	Assert::same('', $panel->getTab());
});

// Test getTab returns content when mails exist
Toolkit::test(function (): void {
	$innerMailer = new DevNullMailer();
	$traceableMailer = new TraceableMailer($innerMailer);

	$message = new Message();
	$message->setSubject('Test');
	$message->setFrom('from@test.com');
	$message->addTo('to@test.com');
	$traceableMailer->send($message);

	$panel = new MailPanel();
	$panel->setTraceableMailer($traceableMailer);

	$tab = $panel->getTab();

	Assert::notSame('', $tab);
	Assert::contains('(1)', $tab);
	Assert::contains('svg', $tab);
});

// Test getTab shows correct count with multiple mails
Toolkit::test(function (): void {
	$innerMailer = new DevNullMailer();
	$traceableMailer = new TraceableMailer($innerMailer);

	for ($i = 0; $i < 5; $i++) {
		$message = new Message();
		$message->setSubject('Test ' . $i);
		$message->setFrom('from@test.com');
		$message->addTo('to@test.com');
		$traceableMailer->send($message);
	}

	$panel = new MailPanel();
	$panel->setTraceableMailer($traceableMailer);

	$tab = $panel->getTab();

	Assert::contains('(5)', $tab);
});

// Test getPanel returns valid HTML
Toolkit::test(function (): void {
	$innerMailer = new DevNullMailer();
	$traceableMailer = new TraceableMailer($innerMailer);

	$message = new Message();
	$message->setSubject('Test Subject');
	$message->setFrom('from@test.com', 'From Name');
	$message->addTo('to@test.com', 'To Name');
	$message->setBody('Test body');
	$traceableMailer->send($message);

	$panel = new MailPanel();
	$panel->setTraceableMailer($traceableMailer);

	$panelContent = $panel->getPanel();

	Assert::contains('Mails', $panelContent);
	Assert::contains('tracy-inner', $panelContent);
	Assert::contains('<table>', $panelContent);
});

// Test getPanel shows mail headers
Toolkit::test(function (): void {
	$innerMailer = new DevNullMailer();
	$traceableMailer = new TraceableMailer($innerMailer);

	$message = new Message();
	$message->setSubject('Custom Subject');
	$message->setFrom('sender@test.com', 'Sender');
	$message->addTo('recipient@test.com', 'Recipient');
	$traceableMailer->send($message);

	$panel = new MailPanel();
	$panel->setTraceableMailer($traceableMailer);

	$panelContent = $panel->getPanel();

	// Headers should be displayed
	Assert::contains('Subject', $panelContent);
	Assert::contains('From', $panelContent);
	Assert::contains('To', $panelContent);
});

// Test getPanel with HTML body includes iframe
Toolkit::test(function (): void {
	$innerMailer = new DevNullMailer();
	$traceableMailer = new TraceableMailer($innerMailer);

	$message = new Message();
	$message->setSubject('HTML Email');
	$message->setFrom('from@test.com');
	$message->addTo('to@test.com');
	$message->setHtmlBody('<html><body><h1>Hello</h1></body></html>');
	$traceableMailer->send($message);

	$panel = new MailPanel();
	$panel->setTraceableMailer($traceableMailer);

	$panelContent = $panel->getPanel();

	Assert::contains('iframe', $panelContent);
	Assert::contains('srcdoc=', $panelContent);
});

// Test getPanel with multiple mails
Toolkit::test(function (): void {
	$innerMailer = new DevNullMailer();
	$traceableMailer = new TraceableMailer($innerMailer);

	for ($i = 0; $i < 3; $i++) {
		$message = new Message();
		$message->setSubject('Mail ' . $i);
		$message->setFrom('from@test.com');
		$message->addTo('to@test.com');
		$traceableMailer->send($message);
	}

	$panel = new MailPanel();
	$panel->setTraceableMailer($traceableMailer);

	$panelContent = $panel->getPanel();

	// Should contain multiple tables (one per mail)
	Assert::same(3, substr_count($panelContent, '<table>'));
});

// Test getPanel with no mails
Toolkit::test(function (): void {
	$innerMailer = new DevNullMailer();
	$traceableMailer = new TraceableMailer($innerMailer);

	$panel = new MailPanel();
	$panel->setTraceableMailer($traceableMailer);

	$panelContent = $panel->getPanel();

	Assert::contains('Mails', $panelContent);
	Assert::notContains('<table>', $panelContent);
});
