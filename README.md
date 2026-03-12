# 🚗 RentaCar — Backend API

> RESTful API for a car rental management platform, built with **Laravel 12** and secured with **Laravel Sanctum**.

[![Laravel](https://img.shields.io/badge/Laravel-12-red?logo=laravel)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-8.2+-blue?logo=php)](https://php.net)
[![Docker](https://img.shields.io/badge/Docker-ready-2496ED?logo=docker)](https://docker.com)

---

## 📌 Overview

RentaCar is a full-stack car rental system. This repository contains the **backend API** which handles authentication, car management, rental bookings, payments, and admin analytics.

The frontend is hosted separately → [renta_car-frontend](https://github.com/YassineBenhamzah/renta_car-frontend)

---

## ✨ Features

### 🔐 Authentication

- User registration & login with **Laravel Sanctum** token authentication
- Role-based access control: `admin`, `agent`, `user`

### 🚘 Car Management

- Public car listing with filters (brand, category, price range, date availability)
- Car detail and availability calendar
- Add / Edit / Delete cars (admin & agent only)
- Image upload support

### 📋 Rental System

- Users submit rental requests with date selection
- Agents/Admins approve, reject, activate or complete rentals
- On-site booking by agents for walk-in customers
- PDF rental contract generation & download

### 💳 Payments

- Users upload payment proof (image)
- Agents/Admins can view payment proof
- Document upload support (CIN, permis de conduire)

### 📊 Admin Dashboard

- Total stats: cars, rentals, revenue, users
- Monthly revenue analytics (chartable data)
- Top 5 rented cars & most profitable cars
- Rental status breakdown
- Filterable by date range, year, month

### 🔔 Notifications

- In-app notifications for: new booking, payment submitted, rental status changed, rental ending soon
- Mark as read / Mark all as read

---

## 🛠️ Tech Stack

| Layer            | Tech                    |
| ---------------- | ----------------------- |
| Framework        | Laravel 12              |
| Auth             | Laravel Sanctum         |
| Database         | MySQL                   |
| File Storage     | Laravel Storage (local) |
| PDF              | DomPDF                  |
| Containerization | Docker + Docker Compose |

---

## 🗂️ Project Structure

```
backend/
├── app/
│   ├── Http/
│   │   ├── Controllers/        # AuthController, CarController, RentalController...
│   │   └── Middleware/         # RoleMiddleware
│   ├── Models/                 # User, Car, Rental
│   └── Notifications/          # NewBooking, PaymentSubmitted, RentalStatusChanged...
├── database/
│   ├── migrations/             # 12 migration files
│   ├── factories/
│   └── seeders/
├── routes/
│   └── api.php                 # All API routes
├── Dockerfile
└── docker-compose.yml
```

---

## 🚀 Getting Started

### Prerequisites

- PHP >= 8.2
- Composer
- MySQL or SQLite

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

### Run with Docker

```bash
docker-compose up --build
```

---

## 📡 API Endpoints

### Public

| Method | Endpoint                      | Description                  |
| ------ | ----------------------------- | ---------------------------- |
| POST   | `/api/register`               | Register a new user          |
| POST   | `/api/login`                  | Login                        |
| GET    | `/api/cars`                   | List all cars (with filters) |
| GET    | `/api/cars/{id}`              | Get car detail               |
| GET    | `/api/cars/{id}/availability` | Get booked dates             |

### Protected (requires auth token)

| Method | Endpoint                    | Description              |
| ------ | --------------------------- | ------------------------ |
| POST   | `/api/logout`               | Logout                   |
| POST   | `/api/rentals`              | Submit rental request    |
| GET    | `/api/my-rentals`           | My rental history        |
| POST   | `/api/rentals/{id}/payment` | Upload payment proof     |
| GET    | `/api/rentals/{id}/pdf`     | Download PDF contract    |
| GET    | `/api/notifications`        | Get unread notifications |

### Admin/Agent only

| Method | Endpoint                   | Description            |
| ------ | -------------------------- | ---------------------- |
| POST   | `/api/cars`                | Add a car              |
| PUT    | `/api/cars/{id}`           | Update a car           |
| DELETE | `/api/cars/{id}`           | Delete a car           |
| GET    | `/api/rentals`             | List all rentals       |
| PUT    | `/api/rentals/{id}/status` | Update rental status   |
| POST   | `/api/rentals/on-site`     | Create on-site booking |
| GET    | `/api/admin/stats`         | Dashboard analytics    |
| GET    | `/api/users`               | List all users         |
| DELETE | `/api/users/{id}`          | Delete a user          |

---

## 🔒 Environment Variables

Copy `.env.example` to `.env` and fill in:

```env
APP_NAME=RentaCar
APP_ENV=local
APP_KEY=         # generated by: php artisan key:generate
APP_DEBUG=true
APP_URL=http://localhost:8000

# Database — MySQL (dev & prod)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=renta_car
DB_USERNAME=root
DB_PASSWORD=your_password

# Mail (optional — for email notifications)
MAIL_MAILER=log
MAIL_FROM_ADDRESS="hello@rentacar.com"
MAIL_FROM_NAME="RentaCar"

# CORS — set to your Vercel frontend URL
FRONTEND_URL=https://your-app.vercel.app
```

---

## 📦 Deployment

Hosted on **Hostinger** with CI/CD via GitHub.

---

## 👤 Author

**Yassine Benhamzah**  
GitHub: [@YassineBenhamzah](https://github.com/YassineBenhamzah)
