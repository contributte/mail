# Contributte Mail

Extra contribution to [`nette/mail`](https://github.com/nette/mail).

## Content

- [Setup](#setup)
- [MailExtension](#mailextension)
- [Mailers](#mailers)
	- [FileMailer](#filemailer)
	- [SendmailMailer](#sendmailmailer)
	- [DevOpsMailer](#devopsmailer)
	- [CompositeMailer](#compositemailer)
	- [DevNullMailer](#devnullmailer)
	- [TraceableMailer](#traceablemailer)
- [Message](#message)

## Setup

```bash
composer require contributte/mail
```

## MailExtension

You have to manually register this extension in the first place.

Be careful, `nette/mail` is registered by default under the `mail` key, that's why we have picked the `post` key.

Simple example:

```neon
extensions:
	mail: Nette\Bridges\MailDI\MailExtension
	post: Contributte\Mail\DI\MailExtension

post:
	# Trace emails in Tracy
	trace: %debugMode%
```

There are several mailer implementations:

```neon
services:
	# Dump mails in folder
	mail.mailer: Contributte\Mail\Mailer\FileMailer(%tempDir%/mails)

	# Polished sendmail
	mail.mailer:
		class: Contributte\Mail\Mailer\SendmailMailer
		setup:
			- setBounceMail(mail@contributte.org)

	# Redirect all mails to one address
	mail.mailer: Contributte\Mail\Mailer\DevOpsMailer(dev@contributte.org)

	# Send mails to multiple mailers
	mail.mailer:
		class: Contributte\Mail\Mailer\CompositeMailer
		arguments: [silent: false] # If silent is enabled then exceptions from mailers are catched
		setup:
			- add(@mailer1)
			- add(@mailer2)
```

## Mailers

### FileMailer

Stores emails on your file system.

```php
$mailer = new FileMailer(__DIR__ . '/temp/mails');
```

### SendmailMailer

This is the default `Nette\Mail\SendmailMailer` with some extra methods and fields.

**Bounce mail**

```php
$mailer->setBounceMail('mail@contributte.org');
```

**Events**

```php
$mailer->onSend[] = function($mailer, $message) {};
```

### DevOpsMailer

Sends all emails to one address with preserved original attributes.

```php
$mailer = new DevOpsMailer('dev@contributte.org');
```

### DevNullMailer

Does literally nothing.

```php
$mailer = new DevNullMailer();
```

### CompositeMailer

Combines more mailers together.

```php
$mailer = new CompositeMailer($silent = false); // If silent is enabled then exceptions from mailers are caught
$mailer->add(new FileMailer(__DIR__ . '/temp/mails'));
$mailer->add(new DevOpsMailer('dev@contributte.org'));
```

### TraceableMailer

Internally wraps your mailer and displays sent mails when the `trace` option is set to `true` in a Tracy panel.

## Message

### MessageFactory

DIC-generated `Message` factory

```php
use Contributte\Mail\Message\IMessageFactory;

class Foo
{

	/** @var IMessageFactory @inject */
	public $messageFactory;

	public function sendMail(): void
	{
		$message = $this->messageFactory->create();
		//...
	}

}
```

### `Message::addTos(array $tos)`

This wrapper accepts an array of recipients and calls `addTo` on each one of them.
