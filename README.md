# UraharaShop - E-Commerce Platform

A modern, full-stack e-commerce platform built with Laravel (backend) and Nuxt.js (frontend).

## 🏗️ Project Structure

```
UraharaShop/
├── backend/          # Laravel 12 API backend
│   ├── app/
│   │   ├── Http/
│   │   │   └── Controllers/    # API Controllers
│   │   ├── Models/             # Eloquent Models
│   │   └── Services/           # Business Logic Services
│   ├── database/
│   │   ├── migrations/         # Database Migrations
│   │   └── seeders/            # Database Seeders
│   └── routes/
│       └── api.php             # API Routes
│
└── frontend/        # Nuxt 4 Frontend
    ├── app/
    │   ├── components/         # Vue Components
    │   ├── pages/              # Nuxt Pages (File-based routing)
    │   ├── composables/        # Vue Composables
    │   ├── services/           # API Service Layer
    │   └── stores/             # State Management
    └── public/                 # Static Assets
```

## 🚀 Tech Stack

### Backend
- **Framework**: Laravel 12
- **PHP**: 8.2+
- **Database**: MySQL/PostgreSQL/SQLite
- **Testing**: Pest PHP
- **API**: RESTful API

### Frontend
- **Framework**: Nuxt 4
- **Language**: TypeScript
- **UI Library**: Vue 3 + shadcn-nuxt
- **Styling**: Tailwind CSS 4
- **State Management**: Pinia (recommended)
- **Form Validation**: VeeValidate + Zod

## 📋 Features

### Core E-Commerce Features
- ✅ Product Catalog
- ✅ Category Management
- ✅ Shopping Cart
- ✅ Order Management
- ✅ User Authentication
- ✅ Product Search & Filtering
- ✅ Payment Integration (Planned)
- ✅ Inventory Management (Planned)
- ✅ Admin Dashboard (Planned)

## 🛠️ Setup Instructions

### Prerequisites
- PHP 8.2+
- Composer
- Node.js 20+
- npm/pnpm/yarn/bun
- MySQL/PostgreSQL (or SQLite for development)

### Backend Setup

```bash
cd backend
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

### Frontend Setup

```bash
cd frontend
npm install
npm run dev
```

## 📁 Database Schema

### Core Tables
- `users` - User accounts
- `products` - Product catalog
- `categories` - Product categories
- `orders` - Customer orders
- `order_items` - Order line items
- `cart_items` - Shopping cart items
- `reviews` - Product reviews (Planned)
- `addresses` - User addresses (Planned)

## 🔌 API Endpoints

### Products
- `GET /api/products` - List all products
- `GET /api/products/{id}` - Get product details
- `POST /api/products` - Create product (Admin)
- `PUT /api/products/{id}` - Update product (Admin)
- `DELETE /api/products/{id}` - Delete product (Admin)

### Categories
- `GET /api/categories` - List all categories
- `GET /api/categories/{id}` - Get category with products

### Cart
- `GET /api/cart` - Get user's cart
- `POST /api/cart` - Add item to cart
- `PUT /api/cart/{id}` - Update cart item
- `DELETE /api/cart/{id}` - Remove cart item

### Orders
- `GET /api/orders` - Get user's orders
- `POST /api/orders` - Create new order
- `GET /api/orders/{id}` - Get order details

## 🧪 Testing

### Backend Tests
```bash
cd backend
php artisan test
```

### Frontend Tests
```bash
cd frontend
npm run test
```

## 📝 Development Guidelines

- Follow PSR-12 coding standards for PHP
- Use TypeScript for frontend code
- Write tests for critical features
- Follow DRY (Don't Repeat Yourself) principles
- Add helpful comments where necessary
- Make frequent, small commits

## 🤝 Contributing

1. Create a feature branch from `dev`
2. Make your changes
3. Write/update tests
4. Submit a pull request

## 📄 License

See LICENSE file for details.

