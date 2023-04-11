<?php declare(strict_types = 1);

namespace Tests\Cases\Mailer;

/**
 * Test: Mailer\CompositeMailer
 */

use Contributte\Mail\Mailer\CompositeMailer;
use Nette\Mail\Message;
use Tester\Assert;
use Tests\Fixtures\ModifyMailer;

require_once __DIR__ . '/../../bootstrap.php';


test(function (): void {
	$cm = new CompositeMailer(true);
	$cm->add(new ModifyMailer());

	$message = new Message();
	$message->setSubject('foobar');

	$cm->send($message);

	Assert::equal('foobar', $message->getSubject());
});
