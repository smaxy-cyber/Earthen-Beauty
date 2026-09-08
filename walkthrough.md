# Earthen Beauty by Nupur - Full-Stack PHP & MySQL Backend, Razorpay, Shiprocket & Admin Portal Walkthrough

## 1. Executive Summary

We have fully built and configured the **PHP + MySQL** backend and luxury **Studio Admin Portal** for **Earthen Beauty by Nupur**, complete with:
- **Database**: Complete MySQL schema and seed data in [`backend/schema.sql`](file:///C:/Users/hp/.gemini/antigravity-ide/scratch/earthen-beauty/backend/schema.sql) containing all 73 catalog products, initial Super Admin account, and orders system.
- **Payment Gateway**: **Razorpay** integration for all standard cart orders and Custom Gift Boxes with HMAC-SHA256 signature verification and sandbox demo mode.
- **Logistics & Courier**: **Shiprocket** integration with token management, ad-hoc shipment creation, and automatic tracking code assignment upon successful payment.
- **Customer Authentication & Orders**: Profile icon `👤` in navbar with login/registration modal, session persistence, delivery address collection, and customer order history.
- **Customization Section Updates**:
  - **Bulk Orders**: Interactive quantity stepper with pre-filled WhatsApp message.
  - **Custom Gift Box**: Custom item picker with instant checkout through **Razorpay** + **Shiprocket**.
  - **Custom Design Candle, Name Candle, and Special Request**: Final action is **"Enquire Price on WhatsApp"** with all custom specifications pre-filled.
- **Studio Admin Portal (`admin.html`)**:
  - Initial Super Admin credentials: `earthenbeauty@gmail.com` / `EARTHENBEAUTY`.
  - Overview Dashboard with 4 live metric stat cards (Total Revenue, Customer Orders, Active Products, Total Customers) and recent orders.
  - Full Product Catalog Management: search, category filters, instant inline price editing, in-stock toggle, image photograph uploader, and product creation/editing/deletion.
  - Orders & Fulfillment Management: customer addresses, items, Razorpay payment status, and Shiprocket status updates (Processing, Shipped, Delivered).
  - Studio Team Management: list, add, and remove administrators.
- **Live Catalog Synchronization**: The storefront (`index.html`, `shop.html`, `product.html`, `search.html`) automatically syncs live product changes (price, stock, photos) from the database in real time.

---

## 2. Directory Structure & Architecture

```
earthen-beauty/
├── admin.html                    # Luxury Studio Admin Portal Interface
├── css/
│   ├── admin.css                 # Admin Theme (Terracotta #b85c38 & Forest Green #1b382b)
│   ├── style.css                 # Storefront styles + Auth & Profile dropdown
│   └── responsive.css            # Mobile responsive styles
├── js/
│   ├── admin.js                  # Admin client controller (Auth, Dashboard, Catalog, Orders, Team)
│   ├── products.js               # Product catalog + Live Database Sync (syncProductsFromBackend)
│   ├── cart.js                   # Cart management + Razorpay & Shiprocket checkout pipeline
│   ├── main.js                   # Navbar profile icon injection, auth modal, address modal
│   └── motion-effects.js         # Smooth animations
├── backend/
│   ├── config.php                # MySQL database connection (PDO), Razorpay & Shiprocket keys
│   ├── helpers.php               # CORS, JSON formatting, password hashing, JWT tokens
│   ├── auth.php                  # Customer & Admin authentication
│   ├── products.php              # Catalog CRUD & image upload
│   ├── orders.php                # Customer order history & admin order management
│   ├── razorpay.php              # Razorpay order generation & HMAC-SHA256 signature verification
│   ├── shiprocket.php            # Shiprocket token authentication & ad-hoc shipment creation
│   ├── dashboard.php             # Admin sales revenue & analytics metrics
│   ├── team.php                  # Administrator team management
│   ├── schema.sql                # Complete MySQL database creation script with 73 seeded products
│   └── php/                      # Local portable PHP 8.2 runtime with PDO, MySQL & cURL enabled
├── api/
│   ├── index.php                 # Unified REST API router (/api/...)
│   ├── auth.php                  # Direct endpoint: api/auth.php
│   ├── products.php              # Direct endpoint: api/products.php
│   ├── orders.php                # Direct endpoint: api/orders.php
│   ├── razorpay.php              # Direct endpoint: api/razorpay.php
│   ├── shiprocket.php            # Direct endpoint: api/shiprocket.php
│   └── admin.php                 # Direct endpoint: api/admin.php
├── router.php                    # Local PHP server router (php -S 127.0.0.1:8000 router.php)
└── .htaccess                     # Apache / XAMPP / cPanel clean URL rewrite rules
```

---

## 3. How to Set Up MySQL Database

### Using phpMyAdmin (XAMPP / WAMP / cPanel)
1. Open **phpMyAdmin** in your browser (`http://localhost/phpmyadmin`).
2. Click on the **Import** tab at the top.
3. Click **Choose File** and select:
   `C:\Users\hp\.gemini\antigravity-ide\scratch\earthen-beauty\backend\schema.sql`
4. Click **Go** / **Import**.
5. It will automatically:
   - Create the `earthen_beauty` database.
   - Create tables: `users`, `admins`, `products`, `orders`.
   - Seed the initial Super Admin (`earthenbeauty@gmail.com`).
   - Seed all 73 catalog products.

### Using MySQL Command Line
```bash
mysql -u root -p < backend/schema.sql
```

---

## 4. How to Connect Live Razorpay & Shiprocket

Open [`backend/config.php`](file:///C:/Users/hp/.gemini/antigravity-ide/scratch/earthen-beauty/backend/config.php) to configure your credentials:

### A. Razorpay Setup
1. Log in to [dashboard.razorpay.com](https://dashboard.razorpay.com/).
2. Navigate to **Settings** → **API Keys** → **Generate Key**.
3. Copy your **Key Id** and **Key Secret**.
4. In `backend/config.php`, update:
   ```php
   define('RAZORPAY_KEY_ID', 'rzp_live_xxxxxxxxxxxxxx');
   define('RAZORPAY_KEY_SECRET', 'your_razorpay_secret_key');
   ```
   *(While testing, you can use your `rzp_test_...` key. In test mode, Razorpay will present the official test payment modal with test UPI, NetBanking, and Cards).*

### B. Shiprocket Setup
1. Log in to [app.shiprocket.in](https://app.shiprocket.in/).
2. Go to **Settings** → **API** → **Configure** / **Create User**.
3. Create API user credentials (email & password).
4. In `backend/config.php`, update:
   ```php
   define('SHIPROCKET_EMAIL', 'your_shiprocket_account_email@example.com');
   define('SHIPROCKET_PASSWORD', 'your_shiprocket_password');
   ```
5. When a customer completes payment via Razorpay, the backend automatically logs into Shiprocket, generates an ad-hoc pickup shipment order, and saves the tracking code / AWB in the database.

---

## 5. Studio Admin Portal Access

- **URL**: `http://127.0.0.1:8000/admin.html` (or click **"Admin Portal 🔒"** in any page footer)
- **Initial Email**: `earthenbeauty@gmail.com`
- **Initial Password**: `EARTHENBEAUTY`
- **Features**:
  - **Dashboard Overview**: View live total revenue, order count, active products count, and customer count.
  - **Catalog Management**:
    - Update product price with 1-click inline **Save** button.
    - Toggle stock status (`In Stock` / `Out of Stock`).
    - Add new products with photograph upload (drag & drop JPG, PNG, WEBP).
    - Edit existing product descriptions, categories, and titles.
    - Delete products.
  - **Order Management**:
    - View full customer address, phone number, and items ordered.
    - Check Razorpay transaction IDs and Shiprocket shipment tracking codes.
    - Update order fulfillment status to **Processing**, **Shipped**, or **Delivered**.
  - **Team Management**:
    - Add new admin members and assign roles (`admin` or `manager`).
    - Remove administrators (with safety protection preventing deletion of the last admin).

---

## 6. How to Run the Website Locally

You can run the PHP server at any time with the built-in PHP binary:
```powershell
backend\php\php.exe -S 127.0.0.1:8000 router.php
```
Then open:
- Storefront: `http://127.0.0.1:8000/index.html`
- Shop: `http://127.0.0.1:8000/shop.html`
- Customization: `http://127.0.0.1:8000/customizable.html`
- Studio Admin: `http://127.0.0.1:8000/admin.html`
