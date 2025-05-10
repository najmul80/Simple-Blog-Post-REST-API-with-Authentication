# 📘 Simple Blog Post REST API with Authentication

This project is a RESTful API built with Laravel for user authentication and blog post management. It uses Sanctum for token-based authentication.



---

## 🚀 API Endpoints

### 🔐 Authentication

| Method | Endpoint           | Description                  |
|--------|--------------------|------------------------------|
| POST   | `/api/v1/register` | Register a new user          |
| POST   | `/api/v1/login`    | Login and get auth token     |
| POST   | `/api/v1/logout`   | Logout authenticated user    |

> **Note**: Use the returned Bearer token for accessing protected routes.

### 📝 Posts (Protected - Requires Token)

| Method | Endpoint              | Description            |
|--------|-----------------------|------------------------|
| GET    | `/api/v1/posts`       | List all posts         |
| POST   | `/api/v1/posts`       | Create a new post      |
| GET    | `/api/v1/posts/{id}`  | Show a specific post   |
| PUT    | `/api/v1/posts/{id}`  | Update a post          |
| DELETE | `/api/v1/posts/{id}`  | Delete a post          |

---

## ⚙️ Installation & Setup

```bash
git clone https://github.com/najmul80/Simple-Blog-Post-REST-API-with-Authentication.git

cd blog-api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
