<?php declare(strict_types = 1);

namespace Tests\Cases\Mailer;

use Contributte\Mail\Mailer\CompositeMailer;
use Contributte\Tester\Toolkit;
use Nette\Mail\Message;
use Tester\Assert;
use Tests\Fixtures\ModifyMailer;

require_once __DIR__ . '/../../bootstrap.php';

Toolkit::test(function (): void {
	$cm = new CompositeMailer(true);
	$cm->add(new ModifyMailer());

	$message = new Message();
	$message->setSubject('foobar');

	$cm->send($message);

	Assert::equal('foobar', $message->getSubject());
});
