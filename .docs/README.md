# Contributte Mail

Extra contribution to [`nette/mail`](https://github.com/nette/mail).

## Content

- [Setup](#setup)
- [Configuration](#configuration)
- [Mailers](#mailers)
	- [FileMailer](#filemailer)
	- [SendmailMailer](#sendmailmailer)
	- [DevOpsMailer](#devopsmailer)
	- [CompositeMailer](#compositemailer)
	- [DevNullMailer](#devnullmailer)
	- [TraceableMailer](#traceablemailer)
- [Message](#message)

## Setup

Install package

```bash
composer require contributte/mail
```

Register extension

```neon
extensions:
	# Native nette/mail
	mail: Nette\Bridges\MailDI\MailExtension

	# Our contributte/mail
	post: Contributte\Mail\DI\MailExtension
```

## Configuration

You have to manually register this extension in the first place.

Be careful, `nette/mail` is registered by default under the `mail` key, that's why we have picked the `post` key.

Simple example:

```neon
extensions:
	mail: Nette\Bridges\MailDI\MailExtension
	post: Contributte\Mail\DI\MailExtension
```

## Mailers

### FileMailer

Stores emails on your file system.

**Configuration**

```neon
services:
	# Dump mails in folder
	mail.mailer: Contributte\Mail\Mailer\FileMailer(%tempDir%/mails)
```

```php
$mailer = new FileMailer(__DIR__ . '/temp/mails');
```

### SendmailMailer

This is the default `Nette\Mail\SendmailMailer` with some extra methods and fields.

**Configuration**

```neon
services:
	# Polished sendmail
	mail.mailer:
		class: Contributte\Mail\Mailer\SendmailMailer
		setup:
			- setBounceMail(mail@contributte.org)
```

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

**Configuration**

```neon
services:
	# Redirect all mails to one address
	mail.mailer: Contributte\Mail\Mailer\DevOpsMailer(@originalMailer, dev@contributte.org)
```

```php
$mailer = new DevOpsMailer($originalMailer, 'dev@contributte.org');
```

### DevNullMailer

Does literally nothing.

```php
$mailer = new DevNullMailer();
```

### CompositeMailer

Combines more mailers together.

**Configuration**

```neon
services:
	# Send mails to multiple mailers
	mail.mailer:
		class: Contributte\Mail\Mailer\CompositeMailer
		arguments: [silent: false] # If silent is enabled then exceptions from mailers are catched
		setup:
			- add(@mailer1)
			- add(@mailer2)
```

```php
$mailer = new CompositeMailer($silent = false); // If silent is enabled then exceptions from mailers are caught
$mailer->add(new FileMailer(__DIR__ . '/temp/mails'));
$mailer->add(new DevOpsMailer('dev@contributte.org'));
```

### TraceableMailer

Internally wraps your mailer and displays sent mails when the `trace` option is set to `true` in a Tracy panel.

```neon
post:
	# Trace emails in Tracy
	trace: %debugMode%
```

## Message

### MessageFactory

You can rely on `IMessageFactory` message factory for creating mail messages.

```php
use Contributte\Mail\Message\IMessageFactory;

class Foo
{

	/** @inject */
	public IMessageFactory $messageFactory;

	public function sendMail(): void
	{
		$message = $this->messageFactory->create();
		//...
	}

}
```

### Message

`Message` extends `Nette\Mail\Message` and add more functions.

#### `$message->addTos(array $tos)`

Accepts an array of recipients and calls `addTo` on each one of them.
