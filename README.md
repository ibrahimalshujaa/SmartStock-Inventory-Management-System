# SmartStock – Inventory Management System

SmartStock is a modern, responsive, and secure web-based inventory management system developed using PHP, MySQL, and Bootstrap. Designed for large-scale enterprise tracking, it enables companies to manage products, organize categories, log stock movements, view reports, and monitor critical stock alerts.

---

## 🌟 Main Features

*   **Role-Based Access Control (RBAC):** Separate permissions for Admin (full system access) and normal User (read-only view access).
*   **Dual-Language Support (i18n):** Clean language switcher supporting English (EN) and Turkish (TR) throughout the entire system.
*   **Comprehensive Dashboard:** Visual overview displaying total products, categories, millions in stock value, recent movements, and low stock warnings.
*   **Catalog & Category Management:** Create, view, edit, and delete products and categories (Admin only).
*   **Stock Movement Logging:** Log stock intake (Stock IN) and dispatch (Stock OUT) with transaction notes, auto-calculating stock levels and preventing negative balances.
*   **Audit History:** Chronological history logs of all stock adjustments, filterable by date, type, or product.
*   **Reports & Analytics:** In-depth breakdown of inventory value by category, value-at-risk, and critical stock listings.
*   **Mobile-Responsive Design:** Tailored CSS tables that scroll horizontally on mobile screens, preserving readability without cutting off columns.

---

## 🛠️ Technologies Used

*   **Backend:** PHP 8.x
*   **Database:** MySQL / MariaDB
*   **Frontend:** Bootstrap 5.3, Bootstrap Icons, Google Fonts (Inter)
*   **Styling & Scripting:** Vanilla CSS3 (Custom Variables, CSS Grid, Flexbox), Vanilla JavaScript (ES6)

---

## 📂 Project Structure

```text
SmartStock/
├── assets/
│   ├── css/
│   │   └── style.css       # Custom enterprise-themed styling
│   └── js/
│       └── main.js         # Interactive triggers, notifications, counter animations
├── auth/
│   ├── login.php           # User login validation
│   ├── logout.php          # Session termination
│   └── register.php        # Normal User self-registration
├── categories/
│   └── index.php           # Category CRUD management
├── config/
│   └── database.php        # Database credentials & PDO connection helper
├── includes/
│   ├── auth.php            # RBAC helper functions (requireAdmin, requireUser, etc.)
│   ├── footer.php          # Footer links and common scripts
│   ├── header.php          # Main head tag, stylesheets, and viewports
│   └── sidebar.php         # Navigation links & EN/TR language switcher
├── lang/
│   ├── en.php              # English dictionary strings
│   ├── lang.php            # Language initialization & translation helper __t()
│   └── tr.php              # Turkish dictionary strings
├── products/
│   ├── add.php             # New product entry form
│   ├── delete.php          # Delete product action handler
│   ├── edit.php            # Edit product form
│   └── index.php           # Catalog table with filters and search
├── reports/
│   └── index.php           # Valuation breakdowns and warning tables
├── stock/
│   ├── add.php             # Record Stock IN/OUT form
│   └── history.php         # Full log of movement records
├── database.sql            # Base database schema and starting data
├── index.php               # Front controller redirecting to login/dashboard
└── README.md               # Project documentation
```

---

## ⚙️ Installation Steps (XAMPP)

Follow these instructions to run SmartStock locally using XAMPP:

### 1. Place Project Files
1. Download or clone this repository.
2. Copy the `SmartStock` folder and paste it into your XAMPP installation directory under:
   ```text
   C:\xampp\htdocs\SmartStock
   ```

### 2. Start Apache and MySQL
1. Open the **XAMPP Control Panel**.
2. Click **Start** next to **Apache** and **MySQL**.

### 3. Import Database using phpMyAdmin
1. Open your web browser and navigate to `http://localhost/phpmyadmin/`.
2. Click on **New** in the left sidebar to create a database.
3. Name the database **`smartstock`** and choose `utf8mb4_unicode_ci` as collation, then click **Create**.
4. Click on the newly created `smartstock` database.
5. Go to the **Import** tab in the top menu.
6. Click **Choose File** and select the `database.sql` file located in the project's root folder (`C:\xampp\htdocs\SmartStock\database.sql`).
7. Click **Import** (or **Go** at the bottom) to load the schema and seed data.

---

## 🔑 Demo Accounts

SmartStock includes two pre-configured accounts:

| Role | Username | Password | Access Details |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin` | `password` | Full system access (All CRUD actions, Stock Movement, Categories, Reports) |
| **User** | `john` | `password` | Read-only access (Dashboard, View Products, switch language). Blocked from editing. |

*Note: You can also self-register a new account (e.g. `testuser` with password `password`) from the **Register** link on the login screen. All self-registered accounts are assigned the normal **User** role by default.*

---

## 🔒 Role-Based Access Details

*   **Admin Permissions:**
    *   Full dashboard access.
    *   View, Add, Edit, and Delete categories and products.
    *   Record Stock IN/OUT transactions.
    *   View full stock movement history.
    *   View financial reports and category breakdown charts.
*   **User Permissions:**
    *   Access to the main Dashboard.
    *   View catalog products (List view and search).
    *   Cannot access Category Management, Reports, or Stock Movement recording.
    *   Blocked from editing, adding, or deleting any catalog items.

---

## 📸 Screenshots

*(Place system screenshots here to showcase your dashboard, tables, and responsive mobile layouts)*

### Desktop Dashboard Overview
![Dashboard Placeholder](https://via.placeholder.com/800x450.png?text=SmartStock+Desktop+Dashboard)

### Responsive Mobile Table Layout
![Mobile View Placeholder](https://via.placeholder.com/350x650.png?text=SmartStock+Mobile+Responsive+Table)

---

## 🚀 Future Improvements

*   **PDF/Excel Exports:** Add a button to download the inventory list and history reports.
*   **Email Alerts:** Send email notifications to the admin when an item falls below its critical limit.
*   **Barcode Printing:** Print barcode labels directly from the products management view.
*   **Supplier Directory:** Link products to a database of active vendors and suppliers.

## ✍ Author

Developed by Ibrahim Alshujaa  
Computer Technology and Information Systems Student  

*Project developed for academic coursework and personal portfolio.*
