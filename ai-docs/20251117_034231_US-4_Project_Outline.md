# US-4: Project Outline and Skeleton Codebase

**Date:** November 17, 2025  
**Ticket:** US-4  
**Branch:** US-4

## Summary

This document outlines all changes made to create the project outline and basic skeleton codebase for the UraharaShop e-commerce platform. The implementation includes backend API structure, database schema, frontend pages, and API service layer.

---

## Backend Changes

### 1. Models Created (`backend/app/Models/`)

#### Category.php
- **Purpose:** Represents product categories with hierarchical support
- **Key Features:**
  - Self-referencing relationship for parent/child categories
  - `is_active` boolean flag for soft enable/disable
  - Relationship to products
- **Relationships:**
  - `products()` - HasMany relationship to Product model
  - `parent()` - BelongsTo relationship to parent Category
  - `children()` - HasMany relationship to child Categories

#### Product.php
- **Purpose:** Core product model for the e-commerce catalog
- **Key Features:**
  - Price fields with decimal casting (price, compare_at_price)
  - Inventory tracking with `quantity` field
  - SEO fields (meta_title, meta_description)
  - Featured product flag
  - Stock checking method `isInStock()`
  - Formatted price accessor
- **Relationships:**
  - `category()` - BelongsTo Category
  - `orderItems()` - HasMany OrderItem
  - `cartItems()` - HasMany CartItem

#### CartItem.php
- **Purpose:** Shopping cart items linked to users and products
- **Key Features:**
  - Unique constraint on user_id + product_id to prevent duplicates
  - Subtotal calculation accessor
- **Relationships:**
  - `user()` - BelongsTo User
  - `product()` - BelongsTo Product

#### Order.php
- **Purpose:** Customer orders with status tracking
- **Key Features:**
  - Status constants (pending, processing, shipped, delivered, cancelled)
  - Order number generation with `generateOrderNumber()` static method
  - JSON casting for address fields
  - Financial fields (subtotal, tax, shipping, total)
- **Relationships:**
  - `user()` - BelongsTo User
  - `items()` - HasMany OrderItem

#### OrderItem.php
- **Purpose:** Individual items within an order (snapshot of product at purchase time)
- **Key Features:**
  - Stores product details at time of purchase (name, SKU, price)
  - Prevents issues if product is deleted or changed after order
- **Relationships:**
  - `order()` - BelongsTo Order
  - `product()` - BelongsTo Product (nullable, product may be deleted)

### 2. Database Migrations (`backend/database/migrations/`)

#### create_categories_table.php
- **Fields:**
  - `id` - Primary key
  - `name` - Category name (string)
  - `slug` - URL-friendly identifier (unique)
  - `description` - Optional text description
  - `parent_id` - Foreign key to parent category (nullable, self-referencing)
  - `is_active` - Boolean flag (default: true)
  - `timestamps` - Created/updated timestamps
- **Indexes:** slug, parent_id, is_active for query optimization

#### create_products_table.php
- **Fields:**
  - `id` - Primary key
  - `name` - Product name
  - `slug` - URL-friendly identifier (unique)
  - `description` - Product description (nullable text)
  - `price` - Product price (decimal 10,2)
  - `compare_at_price` - Original/compare price (nullable decimal 10,2)
  - `sku` - Stock keeping unit (unique, nullable)
  - `barcode` - Barcode identifier (nullable)
  - `quantity` - Inventory quantity (integer, default: 0)
  - `category_id` - Foreign key to categories (nullable)
  - `is_active` - Active status flag
  - `is_featured` - Featured product flag
  - `meta_title` - SEO meta title (nullable)
  - `meta_description` - SEO meta description (nullable)
  - `timestamps`
- **Indexes:** slug, category_id, is_active, is_featured, sku

#### create_cart_items_table.php
- **Fields:**
  - `id` - Primary key
  - `user_id` - Foreign key to users (cascade delete)
  - `product_id` - Foreign key to products (cascade delete)
  - `quantity` - Item quantity (integer, default: 1)
  - `timestamps`
- **Constraints:**
  - Unique constraint on (user_id, product_id) to prevent duplicate cart entries
- **Indexes:** user_id, product_id

#### create_orders_table.php
- **Fields:**
  - `id` - Primary key
  - `user_id` - Foreign key to users (cascade delete)
  - `order_number` - Unique order identifier
  - `status` - Order status (string, default: 'pending')
  - `subtotal` - Order subtotal (decimal 10,2)
  - `tax` - Tax amount (decimal 10,2, default: 0)
  - `shipping` - Shipping cost (decimal 10,2, default: 0)
  - `total` - Total amount (decimal 10,2)
  - `shipping_address` - JSON field for shipping address
  - `billing_address` - JSON field for billing address
  - `notes` - Order notes (nullable text)
  - `timestamps`
- **Indexes:** user_id, order_number, status

#### create_order_items_table.php
- **Fields:**
  - `id` - Primary key
  - `order_id` - Foreign key to orders (cascade delete)
  - `product_id` - Foreign key to products (nullable, null on delete)
  - `product_name` - Product name at time of order
  - `product_sku` - Product SKU at time of order (nullable)
  - `quantity` - Item quantity
  - `price` - Price at time of order (decimal 10,2)
  - `subtotal` - Line item subtotal (decimal 10,2)
  - `timestamps`
- **Indexes:** order_id, product_id

### 3. Controllers Created (`backend/app/Http/Controllers/Api/`)

#### ProductController.php
- **Methods:**
  - `index()` - List products with filtering (category, search, featured)
  - `store()` - Create new product (admin only - needs middleware)
  - `show()` - Get single product details
  - `update()` - Update product (admin only - needs middleware)
  - `destroy()` - Delete product (admin only - needs middleware)
- **Features:**
  - Eager loading of category relationship
  - Search functionality on name and description
  - Pagination support
  - Validation for all inputs

#### CategoryController.php
- **Methods:**
  - `index()` - List active categories
  - `store()` - Create category (admin only - needs middleware)
  - `show()` - Get category with optional products
  - `update()` - Update category (admin only - needs middleware)
  - `destroy()` - Delete category (admin only - needs middleware)
- **Features:**
  - Optional product count/product list inclusion
  - Active category filtering

#### CartController.php
- **Methods:**
  - `index()` - Get user's cart with totals
  - `store()` - Add item to cart
  - `update()` - Update cart item quantity
  - `destroy()` - Remove item from cart
  - `clear()` - Clear entire cart
- **Features:**
  - Stock validation before adding/updating
  - Automatic quantity update if item already exists
  - Cart totals calculation
  - Requires authentication (auth:sanctum middleware)

#### OrderController.php
- **Methods:**
  - `index()` - Get user's orders (paginated)
  - `store()` - Create order from cart
  - `show()` - Get order details
  - `update()` - Update order status (admin only - needs middleware)
  - `destroy()` - Cancel order
- **Features:**
  - Transaction-based order creation
  - Stock validation before order creation
  - Automatic inventory decrement on order
  - Order number generation
  - Cart clearing after successful order
  - Product quantity restoration on cancellation
  - Requires authentication

### 4. API Routes (`backend/routes/api.php`)

- **Public Routes:**
  - `GET /api/products` - List products
  - `GET /api/products/{id}` - Get product
  - `GET /api/categories` - List categories
  - `GET /api/categories/{id}` - Get category

- **Authenticated Routes (auth:sanctum):**
  - `GET /api/cart` - Get cart
  - `POST /api/cart` - Add to cart
  - `PUT /api/cart/{id}` - Update cart item
  - `DELETE /api/cart/{id}` - Remove cart item
  - `DELETE /api/cart` - Clear cart
  - `GET /api/orders` - List orders
  - `POST /api/orders` - Create order
  - `GET /api/orders/{id}` - Get order
  - `DELETE /api/orders/{id}` - Cancel order

- **Admin Routes (needs admin middleware):**
  - `POST /api/admin/products` - Create product
  - `PUT /api/admin/products/{id}` - Update product
  - `DELETE /api/admin/products/{id}` - Delete product
  - `POST /api/admin/categories` - Create category
  - `PUT /api/admin/categories/{id}` - Update category
  - `DELETE /api/admin/categories/{id}` - Delete category
  - `PUT /api/admin/orders/{id}` - Update order status

### 5. Bootstrap Configuration (`backend/bootstrap/app.php`)

- Added API routes configuration to enable `/api` prefix routing
- Routes are loaded from `routes/api.php`

---

## Frontend Changes

### 1. API Service Layer (`frontend/app/services/api.ts`)

- **Purpose:** Centralized API client for backend communication
- **Features:**
  - Base URL configuration via environment variable
  - Authentication token handling (from cookie)
  - Error handling wrapper
  - Type-safe API methods

- **API Modules:**
  - `productApi` - Product listing, details
  - `categoryApi` - Category listing, details
  - `cartApi` - Cart management (add, update, remove, clear)
  - `orderApi` - Order management (list, create, cancel)

### 2. Pages Created (`frontend/app/pages/`)

#### index.vue (Home Page)
- **Features:**
  - Featured products section (4 products)
  - Categories grid
  - Loading states with skeleton UI
  - SEO metadata

#### products/index.vue (Product Listing)
- **Features:**
  - Product grid with pagination
  - Search functionality (debounced)
  - Category filtering
  - Loading states
  - Query parameter synchronization with URL

#### cart.vue (Shopping Cart)
- **Features:**
  - Cart items display
  - Quantity update controls
  - Item removal
  - Order summary with totals
  - Empty cart state
  - Checkout button

#### checkout.vue (Checkout Page)
- **Features:**
  - Shipping address form
  - Order notes field
  - Order summary display
  - Form validation
  - Order submission
  - Redirect to order confirmation

### 3. Components Created (`frontend/app/components/`)

#### ProductCard.vue
- **Purpose:** Reusable product card component
- **Features:**
  - Product image placeholder
  - Name, description, price display
  - Compare price display (if available)
  - Link to product detail page

### 4. App Layout (`frontend/app/app.vue`)

- **Features:**
  - Navigation header with logo and links
  - Main content area with NuxtPage
  - Footer
  - Basic styling with Tailwind CSS

---

## Key Design Decisions

1. **Database Schema:**
   - Used JSON fields for addresses to allow flexibility
   - Stored product details in order_items to preserve order history
   - Added indexes on frequently queried fields for performance
   - Used cascade deletes appropriately (cart items, order items)

2. **API Design:**
   - RESTful structure with clear resource naming
   - Public routes for browsing, protected routes for user actions
   - Admin routes separated for future middleware implementation
   - Consistent error handling and validation

3. **Frontend Architecture:**
   - Centralized API service layer for maintainability
   - Reusable components (ProductCard)
   - Server-side rendering with Nuxt's useAsyncData
   - Loading states for better UX

4. **Code Quality:**
   - Added helpful comments explaining purpose and functionality
   - DRY principles (reusable API methods, components)
   - Type safety where applicable
   - Consistent naming conventions

---

## Additional Features to Consider

1. **Authentication:**
   - Implement Laravel Sanctum for API authentication
   - Add login/register pages
   - User profile management

2. **Product Images:**
   - Image upload functionality
   - Multiple images per product
   - Image optimization

3. **Search & Filtering:**
   - Advanced search with multiple criteria
   - Price range filtering
   - Sort options (price, name, date)

4. **Payment Integration:**
   - Payment gateway integration (Stripe, PayPal, etc.)
   - Payment status tracking
   - Refund handling

5. **Inventory Management:**
   - Low stock alerts
   - Automatic out-of-stock handling
   - Inventory history

6. **Order Management:**
   - Order status email notifications
   - Shipping tracking
   - Order history with filters

7. **Admin Dashboard:**
   - Product management UI
   - Order management UI
   - Analytics and reports

8. **User Features:**
   - Wishlist functionality
   - Product reviews and ratings
   - Saved addresses
   - Order tracking

9. **Performance:**
   - Caching strategy (Redis)
   - Image CDN integration
   - Database query optimization

10. **Testing:**
    - Unit tests for models
    - Feature tests for API endpoints
    - Frontend component tests
    - E2E tests for critical flows

---

## Files Created/Modified

### Backend
- `app/Models/Category.php` (new)
- `app/Models/Product.php` (new)
- `app/Models/CartItem.php` (new)
- `app/Models/Order.php` (new)
- `app/Models/OrderItem.php` (new)
- `app/Http/Controllers/Api/ProductController.php` (new)
- `app/Http/Controllers/Api/CategoryController.php` (new)
- `app/Http/Controllers/Api/CartController.php` (new)
- `app/Http/Controllers/Api/OrderController.php` (new)
- `routes/api.php` (new)
- `bootstrap/app.php` (modified - added API routes)
- `database/migrations/2025_11_16_213929_create_categories_table.php` (new)
- `database/migrations/2025_11_16_213935_create_products_table.php` (new)
- `database/migrations/2025_11_16_213937_create_cart_items_table.php` (new)
- `database/migrations/2025_11_16_213940_create_orders_table.php` (new)
- `database/migrations/2025_11_16_213942_create_order_items_table.php` (new)

### Frontend
- `app/services/api.ts` (new)
- `app/pages/index.vue` (new)
- `app/pages/products/index.vue` (new)
- `app/pages/cart.vue` (new)
- `app/pages/checkout.vue` (new)
- `app/components/ProductCard.vue` (new)
- `app/app.vue` (modified - added layout)

### Documentation
- `README.md` (new - project overview)
- `ai-docs/20251117_034231_US-4_Project_Outline.md` (this file)

---

## Next Steps

1. Run database migrations: `php artisan migrate`
2. Set up authentication (Laravel Sanctum)
3. Configure API base URL in frontend environment
4. Add admin middleware for protected routes
5. Implement product image handling
6. Add form validation on frontend
7. Set up error handling and user feedback (toasts/notifications)
8. Add loading states and error boundaries
9. Implement pagination component
10. Add product detail page

---

## Notes

- All admin routes need middleware protection (not yet implemented)
- Authentication is assumed but not yet implemented
- Product images are placeholders - need image upload functionality
- Tax and shipping calculations are placeholders - need business logic
- Frontend uses basic styling - can be enhanced with design system
- Error handling is basic - should be enhanced with proper error messages
- No tests written yet - should be added in future tickets

