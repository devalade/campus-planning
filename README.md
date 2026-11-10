# Campus Planning

A weekly timetable app for a university. Professors manage their courses, students see the ones they are enrolled in, and everyone gets an email when a course is created, changed, or cancelled.

Built with Laravel 13, Livewire 4, Flux UI and Tailwind CSS 4.

## Features

- Weekly calendar (Monday to Saturday) with side-by-side course layout
- Professor and student roles with scoped course visibility
- Create, edit and cancel courses, with a free-slot check (room, day, time)
- Enroll students in a course, with email notifications
- Authentication with email verification, two-factor auth and passkeys

## Requirements

- PHP 8.4
- Composer
- Node.js and npm
- SQLite (default), or any database Laravel supports

## Setup

```bash
composer setup
```

This installs dependencies, creates `.env`, generates the app key, migrates, and builds assets.

To seed demo data:

```bash
php artisan db:seed
```

## Development

```bash
composer run dev
```

Runs the PHP server, Vite and the queue listener together. The app is available at `http://localhost:8000`.

## Demo accounts

Password for all seeded users: `password`.

| Role      | Email                    |
| --------- | ------------------------ |
| Professor | `awa.sossou@univ.test`   |
| Professor | `koffi.dossou@univ.test` |
| Student   | `alice.houngbo@univ.test` |
| Student   | `bob.adjovi@univ.test`    |

## Tests

```bash
composer test
```

Runs Pint, PHPStan and the Pest suite.

To run only the tests:

```bash
php artisan test
```
