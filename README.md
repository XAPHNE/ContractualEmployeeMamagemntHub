# 🏢 CEMH — Contractual Employee Management Hub

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=for-the-badge&logo=laravel)](https://laravel.com)
[![Filament](https://img.shields.io/badge/Filament-5.x-F59E0B?style=for-the-badge&logo=filament)](https://filamentphp.com)
[![PHP](https://img.shields.io/badge/PHP-8.3%2B-777BB4?style=for-the-badge&logo=php)](https://php.net)
[![License](https://img.shields.io/badge/License-MIT-green?style=for-the-badge)](LICENSE)

**CEMH (Contractual Employee Management Hub)** is an enterprise administrative portal designed for tracking, managing, and auditing contractual employee records, Drawing & Disbursing Officers (DDOs), and organization-wide security governance.

---

## ✨ Key Features

### 📋 1. Employee & DDO Management
- **8-Digit ID Governance**: Masked input (`99999999`) and strict validation rules requiring exactly 8 digits for Employee IDs.
- **Comprehensive Profiles**: PAN, Department, Directorate, Post Name, Office Address, Mobile Number, Email, Treasury Code, and District.
- **Full Lifecycle Auditing**: Integrated Eloquent observers tracking `created_by`, `updated_by`, and `deleted_by` across all records.
- **Soft Deletion & Recovery**: Trash filtering, record restoration, and permanent force-delete protection.

### 🛡️ 2. Advanced Security & Password Governance
- **Email Multi-Factor Authentication (MFA)**: Built-in 2FA OTP verification delivered directly to the user's email with configurable code expiry.
- **Password History Restrictions**: Prevents credential reuse by checking new passwords against historical records via custom validation rules.
- **Configurable Password Complexity**: Enforce minimum/maximum length, uppercase, lowercase, numeric digits, and special character policies.
- **Lockout & Rate Limiting**: Protection against brute-force login attempts and 2FA spamming with automated lockout durations.

### 🔑 3. Role-Based Access Control (RBAC)
- **Filament Shield Integration**: Fine-grained role and permission management powered by Spatie Permission.
- **Granular Model Policies**: Automatically generated authorization policies covering all resources and pages.
- **Super Admin Management**: Dedicated Artisan CLI tools for granting super administrator privileges.

### 📊 4. Centralized Audit Hub
- **Authentication Logs**: Detailed records of successful and failed logins, client IP addresses, browser/user agent details, login timestamps, and session durations.
- **Activity Logs**: Automated tracking of model modifications, creation events, deletions, and state diffs.

### ⚙️ 5. System Settings Dashboard
- **Dynamic Configuration Hub**: Web-based administration for:
  - Application identification and access policies.
  - Password strength and expiry limits.
  - Security thresholds (max login attempts, lockout duration, MFA resend limits).

### 📈 6. Interactive Dashboard & Analytics
- **Live Statistics**: Real-time counts for active employees/DDOs, covered departments, mapped districts, and portal users.
- **Department Distribution Chart**: Visual bar chart breakdown of personnel distribution across departments.
- **Recent Registrations**: Quick-reference table highlighting the latest employee onboarding records.

### 🌐 7. Enterprise SAP ERP Integration & API Manager
- **Secured API Authentication**: Token authentication via `X-API-KEY` or `Authorization: Bearer <token>`.
- **IP Whitelisting**: Restrict API token access strictly to authorized SAP application server IP addresses.
- **Client Rate Limiting**: Per-client configurable request throttles preventing server overloads.
- **Delta Sync & Incremental Updates**: Timestamp query filtering (`?updated_since=...` / `?since=...`) for SAP batch processing.
- **Enterprise JSON Envelope**: Standardized status, UTC timestamp, total count, pagination metadata, and data arrays.
- **API Access Logging**: Full request latency tracking, client IP, method, parameters, response codes, and record counts.
- **API Manager Resource**: Filament management interface to create, activate/deactivate, rotate, and manage client API credentials.

---

## 🛠️ Technology Stack

- **Framework**: [Laravel 12](https://laravel.com)
- **Admin Panel**: [Filament v5](https://filamentphp.com)
- **Permissions**: [Filament Shield](https://github.com/bezhanSalleh/filament-shield) / [Spatie Permission](https://github.com/spatie/laravel-permission)
- **Database**: SQLite / MySQL / PostgreSQL
- **Frontend / Styling**: Tailwind CSS, Alpine.js, Blade Icons

---

## 🚀 Getting Started

### Prerequisites

Ensure the following are installed on your environment:
- **PHP** `>= 8.3` (with `pdo`, `mbstring`, `openssl`, `curl` extensions)
- **Composer** `>= 2.7`
- **Node.js & NPM** `>= 20.x`

### Installation

1. **Clone the repository**:
   ```bash
   git clone https://github.com/XAPHNE/ContractualEmployeeMamagemntHub.git cemh
   cd cemh
   ```

2. **Install PHP and Node dependencies**:
   ```bash
   composer install
   npm install
   npm run build
   ```

3. **Configure Environment**:
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Run Database Migrations & Seed Default Settings**:
   ```bash
   php artisan migrate
   php artisan db:seed --class=SettingSeeder
   ```

5. **Generate Shield Permissions & Setup Super Admin**:
   ```bash
   php artisan shield:generate --all
   php artisan shield:super-admin
   ```

6. **Serve the Application**:
   ```bash
   php artisan serve
   ```

   Access the admin portal at: **`http://localhost:8000/admin`**

---

## 📂 Project Structure

```text
app/
├── Filament/
│   ├── Pages/
│   │   ├── Auth/
│   │   │   └── EditProfile.php          # Custom profile management
│   │   └── SystemSettings.php           # Security & application settings
│   ├── Resources/
│   │   ├── ActivityLogs/                # Model activity audit resource
│   │   ├── AuthenticationLogs/          # Authentication tracking resource
│   │   ├── Ddos/                        # Contractual Employee / DDO resource
│   │   └── Users/                       # Portal user management resource
│   └── Widgets/
│       ├── DdoDepartmentChartWidget.php # Department breakdown chart
│       ├── DdoStatsOverviewWidget.php   # System-wide metrics & counters
│       └── LatestDdosWidget.php         # Recent employee registrations
├── Listeners/
│   └── LogAuthenticationEvent.php       # Login/logout audit listener
├── Models/
│   ├── ActivityLog.php
│   ├── AuthenticationLog.php
│   ├── Ddo.php
│   ├── PasswordHistory.php
│   ├── Setting.php
│   └── User.php
├── Observers/
│   ├── DdoObserver.php                  # Audit stamps & activity logging
│   └── UserObserver.php                 # Password history & user audit
├── Providers/
│   └── Filament/
│       └── AdminPanelProvider.php       # Filament panel configuration
└── Rules/
    └── PasswordHistoryRule.php          # Password rotation rule
```

---

## 🔒 Security & Contribution

- **Security Reporting**: If you discover any security vulnerabilities, please open a confidential advisory or contact the project maintainers directly.
- **License**: This software is open-sourced under the [MIT License](LICENSE).
