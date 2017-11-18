# Mail

## Content

- [MailExtension - registration](#mailextension)
- Mailers
    - [FileMailer](#filemailer)
    - [SendmailMailer](#sendmailmailer)
    - [DevOpsMailer](#devopsmailer)
    - [CompositeMailer](#compositemailer)
- [Message](#message)

## MailExtension

You have to manually register this extension at first place.

Be careful `nette/mail` is registered by default under key `mail`, that's why we have picked key `post`.

```yaml
extensions:
    post: Contributte\Mail\DI\MailExtension
```

There is a several implementation of mailers.

```yaml
post:
    mailer: Contributte\Mail\Mailer\FileMailer(%tempDir%)
    
    mailer:
      class: Contributte\Mail\Mailer\SendmailMailer
      setup:
        - setBounceMail(mail@contributte.org)
        
    mailer: Contributte\Mail\Mailer\DevOpsMailer(dev@contributte.org)
    
    mailer: Contributte\Mail\Mailer\CompositeMailer([@mailer1, @mailer2])
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

## Mailers

### FileMailer

Stores emails at your file system.

```php
$mailer = new FileMailer(__DIR__ . '/temp');
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

Send all emails to one address with preserved original attributes.

```php
$mailer = new DevOpsMailer('dev@contributte.org');
```

### CompositeMailer

Combine more mailers together.

```php
$mailer = new CompositeMailer([
    new FileMailer(__DIR__ . '/temp'),
    new DevOpsMailer('dev@contributte.org'),
]);
```

## Message

### `Message::addTos(array $tos)`

This is wrapper that accepts array of receivers and call `addTo` with each of them.
