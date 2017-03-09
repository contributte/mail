# Mail

## Content

- [MailExtension - registration](#mailextension)
- Mailers
    - [FileMailer](#filemailer)
    - [SendmailMailer](#sendmailmailer)
    - [DevOpsMailer](#devopsmailer)
    - [CompositeMailer](#compositemailer)

## MailExtension

You have to register it at first.

Be careful `nette/mail` is registered by default under key `mail`, that's why we have picked key `post`.

```yaml
extensions:
    post: Contributte\Mail\MailExtension
```

There is a serveral implementation of mailers.

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

Extensions has two modes:

```yaml
post:
  mode: standalone
  # OR
  mode: override
```

- standalone (by default)
- override 

### Stanalone 

Disable autowiring for `nette.mailer` and `mail.mailer`.

### Override

Drop `nette.mailer`, `mail.mailer` services and alias them to `post.mailer`.

## Mailers

### FileMailer

Store emails at your file system.

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
$mailer = DevOpsMailer('dev@contributte.org');
```

### CompositeMailer

Combine more mailers together.

```php
$mailer = new CompositeMailer([
    new FileMailer(__DIR__ . '/temp'),
    new DevOpsMailer('dev@contributte.org'),
]);
```
