<?php declare(strict_types = 1);

namespace Tests\Cases\Tracy;

use Contributte\Mail\Mailer\DevNullMailer;
use Contributte\Mail\Mailer\TraceableMailer;
use Contributte\Mail\Tracy\MailPanel;
use Contributte\Tester\Toolkit;
use Nette\Mail\Message;
use Tester\Assert;
use Tracy\IBarPanel;

require_once __DIR__ . '/../../bootstrap.php';

// Test that MailPanel implements IBarPanel interface
Toolkit::test(function (): void {
	$panel = new MailPanel();
	Assert::type(IBarPanel::class, $panel);
});

// Test that getTab returns empty string when no mails
Toolkit::test(function (): void {
	$traceableMailer = new TraceableMailer(new DevNullMailer());

	$panel = new MailPanel();
	$panel->setTraceableMailer($traceableMailer);

	Assert::same('', $panel->getTab());
});

// Test that getTab returns content when mails exist
Toolkit::test(function (): void {
	$traceableMailer = new TraceableMailer(new DevNullMailer());

	$message = new Message();
	$message->setFrom('from@example.com');
	$message->addTo('to@example.com');
	$message->setSubject('Test');
	$traceableMailer->send($message);

	$panel = new MailPanel();
	$panel->setTraceableMailer($traceableMailer);

	$tab = $panel->getTab();
	Assert::notSame('', $tab);
	Assert::contains('(1)', $tab);
	Assert::contains('svg', $tab);
});

// Test that getTab shows correct mail count
Toolkit::test(function (): void {
	$traceableMailer = new TraceableMailer(new DevNullMailer());

	for ($i = 0; $i < 5; $i++) {
		$message = new Message();
		$message->setFrom('from@example.com');
		$message->addTo('to@example.com');
		$message->setSubject('Test ' . $i);
		$traceableMailer->send($message);
	}

	$panel = new MailPanel();
	$panel->setTraceableMailer($traceableMailer);

	$tab = $panel->getTab();
	Assert::contains('(5)', $tab);
});

// Test that getPanel returns content with mail data
Toolkit::test(function (): void {
	$traceableMailer = new TraceableMailer(new DevNullMailer());

	$message = new Message();
	$message->setFrom('sender@example.com', 'Sender');
	$message->addTo('recipient@example.com', 'Recipient');
	$message->setSubject('Test Subject');
	$message->setBody('Plain text body');
	$traceableMailer->send($message);

	$panel = new MailPanel();
	$panel->setTraceableMailer($traceableMailer);

	$panelContent = $panel->getPanel();
	Assert::contains('Mails', $panelContent);
	Assert::contains('Subject', $panelContent);
	Assert::contains('Test Subject', $panelContent);
});

// Test that getPanel shows HTML body in iframe
Toolkit::test(function (): void {
	$traceableMailer = new TraceableMailer(new DevNullMailer());

	$message = new Message();
	$message->setFrom('from@example.com');
	$message->addTo('to@example.com');
	$message->setSubject('HTML Test');
	$message->setHtmlBody('<html><body><h1>Hello</h1></body></html>');
	$traceableMailer->send($message);

	$panel = new MailPanel();
	$panel->setTraceableMailer($traceableMailer);

	$panelContent = $panel->getPanel();
	Assert::contains('iframe', $panelContent);
	Assert::contains('srcdoc', $panelContent);
	Assert::contains('Hello', $panelContent);
});

// Test that getPanel shows multiple mails
Toolkit::test(function (): void {
	$traceableMailer = new TraceableMailer(new DevNullMailer());

	$message1 = new Message();
	$message1->setFrom('from@example.com');
	$message1->addTo('to@example.com');
	$message1->setSubject('First Mail');
	$traceableMailer->send($message1);

	$message2 = new Message();
	$message2->setFrom('from@example.com');
	$message2->addTo('to@example.com');
	$message2->setSubject('Second Mail');
	$traceableMailer->send($message2);

	$panel = new MailPanel();
	$panel->setTraceableMailer($traceableMailer);

	$panelContent = $panel->getPanel();
	Assert::contains('First Mail', $panelContent);
	Assert::contains('Second Mail', $panelContent);
});
