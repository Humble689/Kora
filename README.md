# KORA
KORA is a Yii 2 web application for managing school payments, administration, and day-to-day school finance operations. It provides role-based tools for school administrators, bursars, teachers, canteen staff, and platform administrators.

## Features

- Student registration, lookup, directory management, balances, and wallet updates
- School-term setup, tuition billing, term rollovers, billing history, and reversals
- Payment collection, transaction review, voiding, reconciliation, and financial reporting
- Point-of-sale device registration and synchronization
- Expense claims, petty-cash tracking, and approval workflows
- Teacher assignment and marks review workflows
- School, staff, user, and role administration
- PDF term and student reports through mPDF
- Email delivery using Yii's Symfony Mailer integration
- Login, signup, contact, and role-based access workflows

## Requirements

- PHP 8.2 or newer
- Composer
- MySQL or MariaDB
- Docker and Docker Compose, if using the containerized setup

## Installation

Install PHP dependencies:

```bash
composer install
```

Configure the database connection in `config/db.php`. Create the database before starting the application. Keep credentials and other deployment secrets outside version control.

For a local PHP server, run:

```bash
php yii serve --port=8080
```

Open <http://127.0.0.1:8080>.

To use Docker instead:

```bash
docker compose up -d
```

The default Docker setup is available at <http://127.0.0.1:8000>.

## Configuration

- `config/web.php` contains web application settings and the cookie validation key.
- `config/db.php` contains the database connection.
- `config/params.php` contains application parameters.
- `config/test.php` and `config/test_db.php` configure the test environment.

The application uses Yii session flash messages for operation results. Flash messages are rendered centrally by `app\widgets\Alert` in the main layout, so views should set a flash in the controller and should not render the same flash again.

## Testing

Build Codeception support classes before running tests:

```bash
vendor/bin/codecept build
```

Run the full test suite with the built-in PHP server environment:

```bash
vendor/bin/codecept run --env php-builtin
```

Run unit tests only:

```bash
vendor/bin/codecept run Unit --env php-builtin
```

Run static analysis and coding standards checks:

```bash
composer static
composer cs
```

## Project Structure

```text
controllers/   Web controllers and application workflows
models/        Active Record models and form models
views/         Web page and layout templates
widgets/       Reusable Yii widgets
config/        Application and test configuration
tests/         Unit, functional, and acceptance tests
web/           Public entry point and web assets
runtime/       Logs, cache, generated files, and local mail output
```

## License

This project is distributed under the license in [LICENSE.md](LICENSE.md).
