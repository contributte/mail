# Contributte > Mail

:sparkles: Extra contribution to [`nette/mail`](https://github.com/nette/mail).

## Content

- [MailExtension - registration](#mailextension)
- [Mailers](#mailers)
    - [FileMailer](#filemailer)
    - [SendmailMailer](#sendmailmailer)
    - [DevOpsMailer](#devopsmailer)
    - [CompositeMailer](#compositemailer)
    - [DevNullMailer](#devnullmailer)
    - [TraceableMailer](#traceablemailer)
- [Message](#message)

## MailExtension

You have to manually register this extension at first place.

Be careful `nette/mail` is registered by default under key `mail`, that's why we have picked key `post`.

Simple example
```yaml
extensions:
    post: Contributte\Mail\DI\MailExtension
    
post:
    # Required option
    mailer: Contributte\Mail\Mailer\FileMailer(%tempDir%/mails)
    
    # Optional options
    debug: %debugMode% #shows sent emails in Tracy
    mode: standalone

```

There is a several implementation of mailers.

```yaml
post:
    mailer: Contributte\Mail\Mailer\FileMailer(%tempDir%/mails)
    
    mailer:
      class: Contributte\Mail\Mailer\SendmailMailer
      setup:
        - setBounceMail(mail@contributte.org)
        
    mailer: Contributte\Mail\Mailer\DevOpsMailer(dev@contributte.org)
    
    mailer:
      class: Contributte\Mail\Mailer\CompositeMailer
      arguments: [silent: false] # If silent is enabled then exceptions from mailers are catched
      setup:
        - add(@mailer1)
        - add(@mailer2)
```

As you can see, extension has two modes:

```yaml
post:
  mode: standalone
  # OR
  mode: override
```

- standalone (by default)
- override 

### Standalone 

It disables autowiring of `nette.mailer` and `mail.mailer`.

### Override

It drops `nette.mailer`, `mail.mailer` services and alias them to `post.mailer`.

### Debug

Extension has also optional `debug` option that show Tracy panel with sent mails headers and their full preview.

## Mailers

### FileMailer

Stores emails at your file system.

```php
$mailer = new FileMailer(__DIR__ . '/temp/mails');
```

### SendmailMailer

This is default `Nette\Mail\SendmailMailer` with some extra methods and fields.

**Bounce mail**

```php
$mailer->setBounceMail(mail@contributte.org)
```

**Events**

```php
$mailer->onSend[] = function($mailer, $message) {}
```

### DevOpsMailer

Sends all emails to one address with preserved original attributes.

```php
$mailer = new DevOpsMailer('dev@contributte.org');
```

### DevNullMailer

It does literally nothing.

```php
$mailer = new DevNullMailer();
```

### CompositeMailer

Combine more mailers together.

```php
$mailer = new CompositeMailer($silent = false); // If silent is enabled then exceptions from mailers are catched
$mailer->add(new FileMailer(__DIR__ . '/temp/mails'));
$mailer->add(new DevOpsMailer('dev@contributte.org'));
```

### TraceableMailer

Internally wraps your mailer and displays sent mails when `debug` option is true Tracy panel. 

## Message

### `Message::addTos(array $tos)`

This is wrapper that accepts array of receivers and call `addTo` with each of them.
