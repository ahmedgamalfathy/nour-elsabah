# Nour-Elsabah - Enterprise E-Commerce API 🚀

Nour-Elsabah is a high-performance, enterprise-grade RESTful API built with **Laravel 12**. The project follows an **API-first architecture**, delivering fully customized and secure JSON responses tailored for both multi-role Admin Dashboards and customer-facing E-Commerce websites.

---

## 🛠️ Key Architectural Decisions & Patterns

Rather than writing traditional monolithic controller code, this project focuses heavily on **Scalability**, **Clean Code (SOLID)**, and **Maintainable Architecture**:

*   **Service Layer & Thin Controllers:** All business logic is completely isolated from controllers and contained within dedicated services (`app/Services`), maintaining highly readable and testable controllers.
*   **Laravel Pipelines for Checkout Flow:** The checkout process requires multiple critical steps (checking stock, validating coupons, calculating loyalty points, and processing orders). To maintain scalability, the flow runs sequentially through a **Pipeline** pattern inside `CheckoutService.php`.
*   **Polymorphic Payment System:** Implemented a robust `PaymentGatewayInterface`. The `PaymentServiceProvider` dynamically resolves the gateway (PayPal, Stripe, etc.) at runtime based on the order configuration, following the *Open-Closed Principle*.
*   **Real-time Admin Notifications (Laravel Reverb):** For Cash-on-Delivery (COD) orders, a real-time event (`CreatedOrderEvent`) is immediately broadcasted via WebSockets using **Laravel Reverb** using `ShouldBroadcastNow`, eliminating the need for the Admin Dashboard to poll or refresh.

---

## 🏗️ Technical Stack & Ecosystem

*   **Core:** PHP ^8.2 | Laravel ^12
*   **Authentication:** Laravel Sanctum (Customized dual-guards: `api` for system users, `client` for customers).
*   **Authorization:** `spatie/laravel-permission` for robust role-based access control (RBAC).
*   **Advanced Filtering:** `spatie/laravel-query-builder` for out-of-the-box sorting, searching, and filtering on massive endpoints (Products, Clients, Orders).
*   **Type-Safety:** Full utilization of **PHP Enums** for maintaining standard application statuses (`OrderStatus`, `ProductStatus`).
*   **Database & Optimization:** Eloquent ORM with proactive **Eager Loading** (`with`) implemented everywhere to eliminate the $N+1$ query problem.
*   **Code Quality:** Formatted using `laravel/pint` to ensure uniform PSR-12 coding standards.

---

## 🔄 Request-Response Flow

Every API interaction follows a strict architectural cycle:

Route ➡️ Custom Form Request (Validation) ➡️ Controller ➡️ Service Layer (Business Logic) ➡️ Eloquent Model ➡️ API Resource/Collection (Formatting) ➡️ Clean JSON Response

---

## ⚡ Real-time Broadcasting Setup (Reverb)

When a customer confirms a **Cash on Delivery** order:
1. The status is updated to `OrderStatus::CASHONDELIVERY`.
2. An event is broadcasted immediately:

How to Run the Project Locally
1. Clone the Repository
   git clone [https://github.com/your-username/nour-elsabah.git](https://github.com/your-username/nour-elsabah.git)
   cd nour-elsabah
   
2. Install Dependencies
   composer install
   npm install
   
3. Environment Setup
   cp .env.example .env
   php artisan key:generate
   
5. Run migrations & seed database
   php artisan migrate --seed
