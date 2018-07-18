<?php declare(strict_types = 1);

namespace Tests\Mailer;

/**
 * Test: Mailer\CompositeMailer
 */

use Contributte\Mail\Mailer\CompositeMailer;
use Fixtures\ModifyMailer;
use Nette\Mail\Message;
use Tester\Assert;

require_once __DIR__ . '/../../bootstrap.php';


test(function (): void {
	$cm = new CompositeMailer(true);
	$cm->add(new ModifyMailer());

	$message = new Message();
	$message->setSubject('foobar');

	$cm->send($message);

	Assert::equal('foobar', $message->getSubject());
});
