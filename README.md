# DineHub 🍽️ - Premium Dine-In Digital Menu & Table Ordering Platform

DineHub is an ultra-premium, modern, and fully responsive web application designed for restaurants to digitize their menus and streamline table-specific orders. Customers can sit at a table, browse the digital menu, filter dishes instantly, and place orders directly to their specific table number. 

The vendor panel features a real-time live polling system that updates orders instantly and triggers an automatic sound notification (bell ring) whenever a new booking arrives.

---

## ✨ Core Features

### 🌐 1. Premium Customer Marketplace (`index.php`)
- **Modern UI:** Built with an ultra-premium, minimalist design featuring modern glassmorphism components.
- **Advanced Search:** Allows users to search for approved partner restaurants by name or address instantly.
- **Fully Responsive:** Optimizes seamlessly for Mobile, Tablet, and Desktop layouts.

### 🪑 2. Table-Specific Ordering & Interactive Cart (`restaurant_menu.php`)
- **Instant Menu Filter:** Fast JavaScript-powered keyup filter to search for specific dishes (e.g., Pizza, Burger) without reloading the page.
- **Client-Side Cart:** Dynamic `+` / `-` quantities addition with live total amount calculator.
- **Table Tracking:** Captures the customer's exact table number or name during checkout so staff know exactly where to serve.

### 🔔 3. Live Vendor Orders Tracker with Sound Alerts (`vendor_orders.php`)
- **AJAX Polling:** Automatically syncs and polls the database every 10 seconds in the background.
- **Live Sound Notification:** Triggers an automated bell audio ring whenever a new order is received, prompting an auto-refresh for the vendor.
- **Order Lifecycle Management:** Vendors can dynamically update order statuses (`Pending`, `Preparing`, `Completed`, `Cancelled`).

### 🛠️ 4. Vendor Menu & Category Management (`manage_menu.php`)
- Full CRUD system for vendors to create custom categories and add menu items with dynamic image uploads.

### 🛡️ 5. Centralized Admin Panel (`admin_dashboard.php`)
- **Vendor Verification:** Admins have sole authority to review and approve newly registered restaurants before they go live on the marketplace.
- **User Directory Control:** View all registered buyers/sellers and securely delete spam or inactive accounts with data integrity safeguards.

### 🧱 6. Universal Clean Architecture (`header.php`)
- Adheres to the **DRY (Don't Repeat Yourself)** principle by utilizing a unified global navbar file with context-aware session logic.

---

## 🛠️ Tech Stack

- **Backend:** PHP (Session-based state management, Procedural/Relational architecture)
- **Database:** MySQL (Relational structure with cascade safety controls)
- **Frontend:** Tailwind CSS (via CDN) with customized Google Fonts (Plus Jakarta Sans)
- **Real-Time Engine:** Native JavaScript Fetch API & AJAX Polling


---
🧑‍💻 Author
Email:** [wajahathussain3335@gmail.com](mailto:wajahathussain3335@gmail.com)
- **🌐 Portfolio Website:** [http://codecraft.infy.click/](http://codecraft.infy.click/)
- **🌐 Live Website:** [https://dinehub.infy.click/](https://dinehub.infy.click/)
## 📂 Project Database Structure

Run the following queries in your phpMyAdmin SQL terminal to set up the relational database structure:

```sql
-- Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('customer', 'vendor', 'admin') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Restaurants Table
CREATE TABLE IF NOT EXISTS restaurants (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    logo VARCHAR(255) NULL,
    address TEXT NOT NULL,
    status ENUM('pending', 'approved') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Categories Table
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    restaurant_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    FOREIGN KEY (restaurant_id) REFERENCES restaurants(id) ON DELETE CASCADE
);

-- Menu Items Table
CREATE TABLE IF NOT EXISTS menu_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    category_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    image VARCHAR(255) NULL,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE CASCADE
);

-- Orders Table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    restaurant_id INT NOT NULL,
    table_number VARCHAR(50) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    status ENUM('pending', 'preparing', 'completed', 'cancelled') NOT NULL DEFAULT 'pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Order Items Table
CREATE TABLE IF NOT EXISTS order_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    menu_item_id INT NOT NULL,
    quantity INT NOT NULL,
    price DECIMAL(10,2) NOT NULL
);
🚀 How to Run Locally
Clone this repository into your local XAMPP root folder (htdocs):

Bash
git clone [https://github.com/your-username/dinehub.git](https://github.com/your-username/dinehub.git)
Open phpMyAdmin, create a new database named dinehub_db.

Import the SQL table code block provided above into the database terminal.

Open config.php and configure your database host, user, password, and database name.

Start XAMPP Apache and MySQL services.

Open your browser and navigate to: http://localhost/dinehub/index.php.

👤 Developer
Name: Wajahat Awan

Role: Full-Stack Web Developer & Freelancer

Focus: High-End Business Web Applications & Custom Portfolio Ecosystems
