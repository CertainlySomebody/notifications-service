# Notification Service

This service provides simple notification system capable of sending messages via multiple channels (e.g., SMS, Email), with support for **provider failover**, **rate limiting**, **channel configuration** and basic **tracking**.

---

## Architecture
- **Domain-driven Design** structure `Domain`, `Infrastructure`, `Application`
- **NotificationSender** dispatches notifications to one or more channels
- Each channel uses **FailoverNotifier**, which cascades through providers on failure
- **NotifierRegistry** manages channel-to-notifier maping
- Doctrine is used track notifications

---

## Features

### Multi-channel notifications
- Send a single notification via multiple channels (e.g. `email` and `sms`)
- Channel list is part of the `Notification` model

### Failover support
- Providers (Twilio, Vonage) are wrapped in a `FailoverNotifier`
- Retry attempts and delay are configurable

### Rate limiting
- Uses Symfony RateLimiter to prevent over-notifying users
- Example: max 300 SMS per hour per user

### Pluggable notifiers
- Easily add new notifiers by implementing new `NotifierInterface`
- Auto-discovered and registered via Symfony service tags

### Provider support
- **Email**: SES, Mailgun (via Symfony Mailer)
- **SMS**: Twilio Vonage

## Getting Started

### Prerequisites
- PHP 8.2+
- Symfony 6+
- Docker

### Install dependencies
```bash
composer install
```

### Environment variables
Configure `.env` with:
```dotenv
MAIL_FROM=<mail to be used as sender>
NUMBER_FROM=<phone number to be used as sender>

SES_MAILER_DSN=ses+smtp://username:password@default?region=region
MAILGUN_MAILER_DSN=mailgun+smtp://username:password@smtp.mailgun.org:port

TWILIO_SID=<twilio sid>
TWILIO_TOKEN=<twilio token>

NEXMO_SECRET=<vonage secret>
NEXMO_KEY=<vonage key>
```

---

## Key components

### `NotifierInterface`
All providers implement:
```php
interface NotifierInterface {
    public funciton send(Notification $notification): void;
    public function supportsChannel(string $channel): bool;
    public function getChannel(): string;
}
```

### `FailoverNotifier`
Wraps a list of notifiers, tries them in order, retries if needed.

### `NotifierRegistry`
Maps each channel (e.g. `sms`, `email`) to a `FailoverNotifier`

### `NotificationSender`
Coordinates sending notifications to all specified channels.

### `Notification` Model
```php
class Notification
{
    public function __construct(
        public readonly string $userId,
        public readonly string $message,
        public array $channels,
        public readonly ?string $phone = ''
    ) {
    }
}
```

---

## Testing
Tests are located in `tests`. Currently, there's only a few basic tests implemented.

- `FailoverNotifierTest.php`
- `NotifierRegistryTest.php`

Running tests:
```bash
php bin/phpunit /path/to/testfile.php
```

## Extending the system

### Add a new notifier
1. Implement new notifier with `NotifierInterface`
2. Configure new record in `services.yaml`

---

## Notes
- Symfony Mailer handles email failover natively via DSN string

---


## License
MIT — Use freely for any purpose.
