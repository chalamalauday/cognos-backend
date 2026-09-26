# 🚀 COGNOS 2K26 - Official Full-Stack Technical Fest Website
> **"LET THE DATA SPEAK"**  
> Organized by the **Departments of Computer Science and Engineering(Data Science) & Artificial Intelligence & Data Science (AI&DS)**.  
> **Event Date:** October 9, Friday · Decennial Block – III Floor

---

## 🌟 Highlights & Features
- **Modern Cyberpunk / Data Aesthetic**: Futuristic dark theme with glowing neon cyan/blue accents, live countdown timer, interactive challenges showcase, and 2025 gallery.
- **3 Core Technical Challenges**:
  1. **Vishleshana** (Group Discussion Rounds | ₹6,000 Prize Pool | Individual | 2:00 – 5:00 PM)
  2. **Razzle Review** (Paper Presentation | ₹6,000 Prize Pool | Max 2 Members | 11:00 AM onwards)
  3. **Data Dazzle** (Data Storytelling & BI Dashboard | ₹6,000 Prize Pool | 2 Members | 1:00 – 3:00 PM)
- **Unified Multi-Event Registration**:
  - Single registration form with checkboxes allowing participants to register for 1, 2, or all 3 events simultaneously.
  - Automatic student name capitalization, complete roll number formatting, branch, and college name.
  - Secure drag-and-drop College ID Card upload.
  - Dynamic expandable Teammate section (for Razzle Review and Data Dazzle).
- **Automated Email Notifications (PHPMailer + Gmail SMTP)**:
  - Dispatches an official HTML registration pass to the participant's email.
  - Contains assigned Registration ID (e.g. `COG26-8941`), venue details, lab allotments, timings, guidelines, coordinator contacts, and official WhatsApp Community invite button.
  - Bundled standalone PHPMailer (zero composer installation needed).
- **Hidden Admin Analytics & Management Dashboard (`admin.php`)**:
  - Direct URL access only (not linked anywhere in the website UI).
  - Secure session-based authentication.
  - Real-time metric cards (Total Registrations, Vishleshana count, Razzle Review count, Data Dazzle count, Participating Colleges, Teams count).
  - Live search & filter by Name, Roll No, Reg Code, and College.
  - In-browser College ID Card preview modal (inspect student IDs without downloading).
  - **Individual Excel Export (.csv)**: One-click download of individual event participant sheets (`Vishleshana`, `Razzle Review`, `Data Dazzle`) and Master list.
- **Future-Proof & Modular Architecture**:
  - Decoupled `frontend/` and `backend/` directories.
  - Easily customizable for future fest editions (`COGNOS 2K27`, etc.) via `backend/config.php`.

---

## 📁 Project Directory Structure
```
TECH_FEST/
│
├── frontend/                      # Decoupled Static Frontend (HTML, CSS, JS, Assets)
│   ├── index.html                 # Main Tech Fest Landing Page
│   ├── css/
│   │   ├── style.css              # Cyberpunk Design System & Layouts
│   │   ├── components.css         # Modals, Form Controls, Checkbox Cards, Badges
│   │   └── responsive.css         # Mobile & Tablet Responsiveness
│   ├── js/
│   │   ├── events-data.js         # Centralized Event Dataset (Rules, Prizes, Coordinators)
│   │   ├── main.js                # Countdown Timer, Modal Logic, Lightbox
│   │   └── registration.js        # Multi-part AJAX Form Submission & Validation
│   └── assets/
│       └── images/                # Fest Logo & 2025 Flashback Gallery Assets
│
├── backend/                       # Backend API & Business Logic (PHP & MySQL)
│   ├── config.php                 # Central Config (DB, Gmail SMTP, Fest Details, Admin Credentials)
│   ├── db.php                     # PDO Database Connection Handler
│   ├── register.php               # Multi-Event Registration Endpoint (Inserts DB & Calls Mailer)
│   ├── mailer.php                 # PHPMailer HTML Confirmation Email Dispatcher
│   ├── admin_api.php              # Admin Dashboard Data API (Stats, Search, Delete)
│   ├── export.php                 # Dynamic UTF-8 CSV Generator for Microsoft Excel
│   ├── uploads/
│   │   └── id_cards/              # Uploaded College ID Cards (.htaccess protected)
│   └── phpmailer/                 # Standalone PHPMailer Source Files (No Composer Required)
│
├── admin.php                      # Hidden Admin Portal (Direct URL Access: /admin.php)
├── index.php                      # Root Entry Router (Redirects to frontend/)
├── index.html                     # Fallback Static Redirector
├── database.sql                   # MySQL Schema & Initial Admin User
└── README.md                      # Documentation & Step-by-Step Setup Guide
```

---

## 🛠️ Step-by-Step Setup Guide (XAMPP)

### Step 1: Place Files in XAMPP
1. Move or copy this `TECH_FEST` folder into your XAMPP web root:
   ```
   C:\xampp\htdocs\TECH_FEST
   ```
2. Open the **XAMPP Control Panel** (`C:\xampp\xampp-control.exe`).
3. Start **Apache** and **MySQL** by clicking **Start** next to each.

---

### Step 2: Import the Database
1. Open your browser and navigate to:  
   👉 [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. Click on the **Import** tab at the top.
3. Click **Choose File** and select `database.sql` located inside `TECH_FEST/database.sql`.
4. Click **Import** (or **Go**) at the bottom.
5. You will see a newly created database named `cognos_2k26` with tables:
   - `registrations`
   - `registration_events`
   - `admin_users`

---

### Step 3: Configure Gmail App Password for Confirmation Emails
To allow the website to automatically dispatch confirmation emails from your Gmail account:

1. Go to your Google Account: [https://myaccount.google.com/security](https://myaccount.google.com/security)
2. Ensure **2-Step Verification** is turned **ON**.
3. In the search box at the top of your Google Account page, type **"App passwords"** and click on it.
4. Enter an App Name (e.g., `COGNOS Tech Fest`) and click **Create**.
5. Google will generate a **16-character password** (e.g., `abcd efgh ijkl mnop`). Copy this password.
6. Open `backend/config.php` in any text editor (VS Code, Notepad, etc.) and update lines 28–31:
   ```php
   define('SMTP_USERNAME', 'your_email@gmail.com');       // Your Gmail address
   define('SMTP_PASSWORD', 'abcdefghijklmnop');           // Your 16-character App Password (without spaces)
   define('SMTP_FROM_EMAIL', 'your_email@gmail.com');
   ```
7. *(Optional)* Update `WHATSAPP_COMMUNITY_LINK` with your real WhatsApp group invite link in line 39.

> **Note on Testing without Email:**  
> If you leave `SMTP_PASSWORD` as `YOUR_GMAIL_APP_PASSWORD`, the registration system will still store registrations into the database and show the successful registration code on screen. Live email dispatch activates immediately once you paste your App Password!

---

### Step 4: Open and Test the Website
1. Open your browser and go to:
   ```
   http://localhost/TECH_FEST/
   ```
   *(Or `http://localhost/TECH_FEST/frontend/`)*
2. Check the **Countdown Timer** counting down to **October 9, 2026**.
3. Click on any event card (**Vishleshana**, **Razzle Review**, or **Data Dazzle**) to view full rules, coordinator contacts, and prize breakdowns.
4. Click **Register Now** or **Register for Our Events** to open the unified registration form.
5. Fill out the details, check your desired events, upload an ID card, and submit.
6. A success dialog will appear displaying your official Registration ID (e.g. `COG26-8941`) and the WhatsApp join link!

---

### Step 5: Access the Hidden Admin Dashboard
1. The admin page is hidden from the main site UI. Open it directly in your browser:
   ```
   http://localhost/TECH_FEST/admin.php
   ```
2. **Default Credentials**:
   - **Username**: `admin`
   - **Password**: `admin@cognos2026`
   *(You can change this password in `backend/config.php`)*
3. **Features inside the Admin Panel**:
   - **Overview Metrics**: Total count, Vishleshana count, Razzle Review count, Data Dazzle count, Unique Colleges, and Teams.
   - **Event Filter Tabs**: Switch between All, Vishleshana, Razzle Review, and Data Dazzle.
   - **Live Search**: Instant search by student name, roll number, college, or registration ID.
   - **ID Card Preview**: Click **View ID** to inspect the uploaded college ID card in a modal without leaving the page.
   - **Excel Export**: Click **Export Excel Sheets ▾** to download `.csv` sheets formatted for Microsoft Excel:
     - `📥 Export All Registrations (Master)`
     - `📥 Export Vishleshana List`
     - `📥 Export Razzle Review Teams`
     - `📥 Export Data Dazzle Teams`

---

## 🔄 Reusing for Future Years (e.g., COGNOS 2K27)
This codebase was designed to be reusable in upcoming years:
1. Open `backend/config.php`:
   - Change `FEST_NAME` to `'COGNOS 2K27'`.
   - Change `FEST_DATE_TEXT` and `FEST_DATE_ISO` to the new year's dates.
   - Update coordinator phone numbers and WhatsApp community link.
2. Open `frontend/js/events-data.js`:
   - Adjust dates, prize pools, or domain topics.
3. Open `frontend/index.html`:
   - Update timeline slots or replace photos in `frontend/assets/images/`.

---

## ☁️ Separate Deployment (Decoupled Hosting)
If you decide to deploy the frontend and backend on separate servers:
- **Frontend** (Vercel, Netlify, GitHub Pages, or S3):  
  Deploy the `frontend/` folder. In `frontend/js/registration.js`, configure `window.COGNOS_API_URL` to point to your live backend domain:
  ```js
  window.COGNOS_API_URL = "https://your-backend-server.com/backend/register.php";
  ```
- **Backend** (cPanel, Hostinger, VPS, or Apache/PHP hosting):  
  Deploy the `backend/` folder and `admin.php`. The backend already has built-in CORS headers (`apply_cors_headers()`) to allow cross-origin registration requests securely!
