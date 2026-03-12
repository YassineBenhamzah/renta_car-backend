<div align="center">

# 🚗 RentaCar — Backend API

**A production-ready car rental management REST API**

[![Laravel](https://img.shields.io/badge/Laravel-12-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://php.net)
[![MySQL](https://img.shields.io/badge/MySQL-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://mysql.com)
[![Docker](https://img.shields.io/badge/Docker-2496ED?style=for-the-badge&logo=docker&logoColor=white)](https://docker.com)
[![Sanctum](https://img.shields.io/badge/Sanctum-Auth-FF2D20?style=for-the-badge&logo=laravel&logoColor=white)](https://laravel.com/docs/sanctum)

[Frontend Repo](https://github.com/YassineBenhamzah/renta_car-frontend) · [Report Bug](https://github.com/YassineBenhamzah/renta_car-backend/issues) · [API Docs](#-api-endpoints)

</div>

---

## 📌 About The Project

**RentaCar** is a full-stack car rental management system designed for rental agencies. It provides a complete backend REST API that powers:

- A **public catalog** where customers browse and check car availability
- A **user portal** for making bookings, uploading payments & downloading contracts
- An **agent panel** for managing rentals and on-site bookings
- An **admin dashboard** with analytics, user management, and full control

> 🔗 Frontend (React 18 + Vite) → [renta_car-frontend](https://github.com/YassineBenhamzah/renta_car-frontend)

---

## ✨ Key Features

| Feature                      | Description                                                           |
| ---------------------------- | --------------------------------------------------------------------- |
| 🔐 **Auth System**           | Register, login, logout with Sanctum token-based auth                 |
| 👥 **3 Roles**               | `admin`, `agent`, `user` — each with different permissions            |
| 🚘 **Car Catalog**           | Listing with filters: brand, category, price range, date availability |
| 📅 **Availability Calendar** | Real-time booked dates per car                                        |
| 📋 **Rental Workflow**       | Request → Approve → Activate → Complete or Cancel                     |
| 💳 **Payment Upload**        | Users upload payment proof, agents verify it                          |
| 📝 **PDF Contracts**         | Auto-generated rental contract downloadable as PDF                    |
| 🧑‍💼 **On-Site Booking**       | Agents create bookings for walk-in customers directly                 |
| 📊 **Admin Analytics**       | Revenue charts, top cars, rental status breakdown                     |
| 🔔 **Notifications**         | In-app alerts for bookings, payments, status changes, expiries        |

---

## 🏗️ System Architecture

```
[React Frontend] ──── HTTPS ────► [Laravel 12 API]
                                        │
                    ┌───────────────────┼───────────────────┐
                    │                   │                   │
               [MySQL DB]       [File Storage]       [Notifications]
               Users, Cars,     Payment proofs,      NewBooking,
               Rentals          Documents, Images     StatusChanged...
```

---

## 🛠️ Tech Stack

| Layer            | Technology                   |
| ---------------- | ---------------------------- |
| Framework        | Laravel 12                   |
| Authentication   | Laravel Sanctum 4.0          |
| Database         | MySQL                        |
| PDF Generation   | barryvdh/laravel-dompdf 3.1  |
| File Storage     | Laravel Storage (local disk) |
| Containerization | Docker + Docker Compose      |
| Language         | PHP 8.2+                     |

---

## 🗂️ Project Structure

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php          # register, login, logout
│   │   │   ├── CarController.php           # CRUD + filters + availability
│   │   │   ├── RentalController.php        # booking, status, payment, PDF
│   │   │   ├── DashboardController.php     # admin analytics & stats
│   │   │   ├── UserController.php          # user listing & deletion
│   │   │   └── NotificationController.php  # in-app notifications
│   │   └── Middleware/
│   │       └── RoleMiddleware.php          # role:admin, role:agent
│   ├── Models/
│   │   ├── User.php
│   │   ├── Car.php
│   │   └── Rental.php
│   └── Notifications/
│       ├── NewBooking.php
│       ├── PaymentSubmitted.php
│       ├── RentalStatusChanged.php
│       └── RentalEndingSoon.php
├── database/
│   ├── migrations/                         # 12 migration files
│   ├── factories/
│   └── seeders/
├── routes/
│   └── api.php                             # all API routes
├── Dockerfile
└── docker-compose.yml
```

---

## 🚀 Getting Started

### Prerequisites

- PHP >= 8.2
- Composer
- MySQL
- Docker (optional)

### Installation

```bash
# 1. Clone the repo
git clone https://github.com/YassineBenhamzah/renta_car-backend.git
cd renta_car-backend

# 2. Install dependencies
composer install

# 3. Copy environment file
cp .env.example .env

# 4. Generate app key
php artisan key:generate

# 5. Configure your DB in .env, then run migrations
php artisan migrate

# 6. Start the server
php artisan serve
```

### 🐳 Run with Docker

```bash
docker-compose up --build
```

---

## 📡 API Endpoints

### 🔓 Public

| Method | Endpoint                      | Description                |
| ------ | ----------------------------- | -------------------------- |
| `POST` | `/api/register`               | Register a new user        |
| `POST` | `/api/login`                  | Login & receive token      |
| `GET`  | `/api/cars`                   | List all cars with filters |
| `GET`  | `/api/cars/{id}`              | Get car detail             |
| `GET`  | `/api/cars/{id}/availability` | Get booked date ranges     |

### 🔒 Protected (auth token required)

| Method | Endpoint                       | Description               |
| ------ | ------------------------------ | ------------------------- |
| `POST` | `/api/logout`                  | Logout                    |
| `POST` | `/api/rentals`                 | Submit rental request     |
| `GET`  | `/api/my-rentals`              | My rental history         |
| `POST` | `/api/rentals/{id}/payment`    | Upload payment proof      |
| `GET`  | `/api/rentals/{id}/pdf`        | Download PDF contract     |
| `GET`  | `/api/notifications`           | Get unread notifications  |
| `POST` | `/api/notifications/{id}/read` | Mark notification as read |
| `POST` | `/api/notifications/read-all`  | Mark all as read          |

### 🛡️ Agent & Admin only

| Method   | Endpoint                   | Description            |
| -------- | -------------------------- | ---------------------- |
| `POST`   | `/api/cars`                | Add a car              |
| `PUT`    | `/api/cars/{id}`           | Update a car           |
| `DELETE` | `/api/cars/{id}`           | Delete a car           |
| `GET`    | `/api/rentals`             | List all rentals       |
| `PUT`    | `/api/rentals/{id}/status` | Update rental status   |
| `POST`   | `/api/rentals/on-site`     | Create on-site booking |

### 👑 Admin only

| Method   | Endpoint           | Description         |
| -------- | ------------------ | ------------------- |
| `GET`    | `/api/admin/stats` | Dashboard analytics |
| `GET`    | `/api/users`       | List all users      |
| `DELETE` | `/api/users/{id}`  | Delete a user       |

---

## 🔒 Environment Variables

Copy `.env.example` to `.env` and configure:

```env
APP_NAME=RentaCar
APP_ENV=local
APP_KEY=         # generated by: php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database — MySQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=renta_car
DB_USERNAME=root
DB_PASSWORD=your_password

# Mail (optional)
MAIL_MAILER=log
MAIL_FROM_ADDRESS="hello@rentacar.com"
MAIL_FROM_NAME="RentaCar"

# CORS — set to your Vercel frontend URL
FRONTEND_URL=https://your-app.vercel.app
```

---

## 📦 Deployment

- **Backend** → hosted on **Hostinger** with CI/CD via GitHub
- **Frontend** → hosted on **Vercel** → [renta_car-frontend](https://github.com/YassineBenhamzah/renta_car-frontend)

---

## 👤 Author

**Yassine Benhamzah**

- GitHub: [@YassineBenhamzah](https://github.com/YassineBenhamzah)
- LinkedIn: [linkedin.com/in/your-profile](https://linkedin.com/in/your-profile)

---

<div align="center">
⭐ If you found this project useful, give it a star!
</div>
