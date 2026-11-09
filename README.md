# AboJude Backend API

[![Laravel](https://img.shields.io/badge/Laravel-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Firebase](https://img.shields.io/badge/Firebase_FCM-FFCA28?style=for-the-badge&logo=firebase&logoColor=black)](https://firebase.google.com)
[![License](https://img.shields.io/badge/License-Proprietary-red.svg?style=for-the-badge)](#license)

A robust, enterprise-grade RESTful API backend for **AboJude**, a social marketplace and classified listings application. The platform enables users to post classified ads across categories, discover nearby listings, communicate via real-time messaging, manage wishlists, receive push notifications, and access multi-language localization.

---

## Key Features

- **Authentication & Social SSO**:
  - Email/Password registration with OTP verification.
  - Social authentication via Google and Apple Sign-In.
  - Guest user session support and secure JWT authentication.
- **Classified Ads & Posts**:
  - Category-based listings with multi-language attributes.
  - Featured, recent, and related post filtering.
  - Location and province-city cascading filters.
  - Post history tracking, bookmarks, and user wishes.
- **Real-Time Communication**:
  - Live chat and conversation management powered by Laravel Reverb WebSockets.
  - Message exchange, read statuses, and media sharing.
- **Notifications & Cloud Messaging**:
  - Firebase Cloud Messaging (FCM) push notifications.
  - In-app notification center and system announcements.
- **Reporting & Support**:
  - Post reporting mechanism and contact support module.
  - Dynamic legal, privacy, and CMS pages.
- **Admin Dashboard**:
  - Yajra DataTables integration for efficient moderation of users, posts, categories, and system reports.

---

## Tech Stack

- **Framework**: Laravel 11 / 12 (PHP 8.2+)
- **Authentication**: JWT Auth (`php-open-source-saver/jwt-auth`), Laravel Sanctum, Socialite (Google & Apple)
- **Database**: MySQL / MariaDB
- **Real-time WebSockets**: Laravel Reverb & Predis (Redis)
- **Push Notifications**: Firebase Cloud Messaging (`kreait/laravel-firebase`)
- **Cloud Storage**: AWS S3 (`league/flysystem-aws-s3-v3`)
- **Localization**: Spatie Translatable & Google Translate API
- **Mailing**: Brevo Mailer API (`symfony/brevo-mailer`)
- **Admin Tables**: Yajra DataTables

---

## Directory Structure

```plaintext
abojude_backend/
├── app/
│   ├── Helpers/            # Utility functions and formatting helpers
│   ├── Http/
│   │   ├── Controllers/
│   │   │   └── API/        # Auth, Post, Chat, Wish, Location, Notification APIs
│   │   ├── Middleware/     # JWT Auth, Role validation, Localization
│   │   └── Requests/       # Form request validation rules
│   ├── Models/             # Post, Category, Conversation, Message, Wish, User...
│   ├── Notifications/      # FCM & Database notification classes
│   └── Services/           # Business logic, Translation, and S3 Services
├── config/                 # Laravel configuration (jwt, firebase, reverb, etc.)
├── database/
│   ├── migrations/         # Database schema migrations
│   └── seeders/            # Initial categories, roles, and dummy data
├── routes/
│   ├── api.php             # Versioned REST API endpoints (/api/v1/...)
│   ├── web.php             # Admin panel & Web views
│   └── channels.php        # WebSocket broadcast channel authorization
└── tests/                  # Feature and Unit test suites
```

---

## Getting Started

### Prerequisites

- PHP >= 8.2
- Composer 2.x
- MySQL 8.x or MariaDB 10.x
- Redis (optional, recommended for queues & Reverb WebSockets)

### Installation

1. **Clone the repository**:
   ```bash
   git clone https://github.com/mainuromayer/abojuda_backend.git
   cd abojuda_backend
   ```

2. **Install PHP dependencies**:
   ```bash
   composer install
   ```

3. **Configure Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   php artisan jwt:secret
   ```

4. **Update `.env` configuration**:
   ```env
   APP_NAME="AboJude"
   APP_URL=http://localhost:8000

   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=abojude_backend
   DB_USERNAME=root
   DB_PASSWORD=

   # Firebase Credentials
   FIREBASE_CREDENTIALS=storage/firebase/firebase_credentials.json

   # AWS S3 Storage
   AWS_ACCESS_KEY_ID=
   AWS_SECRET_ACCESS_KEY=
   AWS_DEFAULT_REGION=
   AWS_BUCKET=
   ```

5. **Run Migrations & Seeders**:
   ```bash
   php artisan migrate --seed
   php artisan storage:link
   ```

6. **Start the Development Server**:
   ```bash
   php artisan serve
   ```

---

## Core API Endpoints

| Method | Endpoint | Description |
|:---|:---|:---|
| `POST` | `/api/v1/sign-up` | User account registration |
| `POST` | `/api/v1/verify/otp` | OTP code verification |
| `POST` | `/api/v1/login` | User login (returns JWT token) |
| `GET` | `/api/v1/posts` | Retrieve paginated classified posts |
| `GET` | `/api/v1/categories` | List active categories & subcategories |
| `POST` | `/api/v1/posts` | Create new ad listing |
| `GET` | `/api/v1/locations/provinces` | List provinces & cities |
| `GET` | `/api/v1/chat/conversations` | Retrieve user active chat conversations |
| `POST` | `/api/v1/chat/send` | Send a real-time message |
| `GET` | `/api/v1/wishes` | Get user saved wishlist / bookmarked items |

---

## Author & Maintainer

- **Mainur Rahaman** ([@mainuromayer](https://github.com/mainuromayer))
- Email: [mainuromayer@gmail.com](mailto:mainuromayer@gmail.com)

---

## License

This project is proprietary and confidential. Unauthorized copying, distribution, or modification is strictly prohibited.
