# Deployment Guide - COGNOS 2K26

## Critical Architecture Notice: InfinityFree & Cross-Origin APIs

> **IMPORTANT**: InfinityFree's Free Tier enforces a mandatory bot security challenge (`aes.js` / `__test` cookie). 
> This security system **strictly blocks cross-origin API fetch/AJAX requests** coming from an external domain (e.g. from `rvrjcce.ac.in` calling `cognos.ifree.page`).
> Attempting to call an InfinityFree-hosted backend from a frontend hosted on `rvrjcce.ac.in` will always result in `Non-JSON Server Output (aes.js HTML challenge)` error.

---

## Option 1: College Server Deployment (Recommended & Simplest)

Since `rvrjcce.ac.in` already runs Apache and PHP, hosting both `frontend` and `backend` together on the college server eliminates all CORS, cookie, and SSL issues.

1. **Upload Structure on College Server**:
   ```
   innovex2026/
   ├── frontend/        <-- Contains index.html, css/, js/, assets/
   └── backend/         <-- Contains register.php, config.php, db.php, mailer.php, uploads/
   ```
2. **Access URLs**:
   - Frontend: `https://rvrjcce.ac.in/innovex2026/frontend/index.html`
   - Backend API: `https://rvrjcce.ac.in/innovex2026/backend/register.php`
3. **Configuration**:
   - `frontend/js/config.js` will automatically detect the `/innovex2026/backend` route.
   - Configure MySQL credentials in `backend/config.php` and import `database.sql`.

---

## Option 2: Full InfinityFree Hosting (Same-Domain)

If the college server does not provide a database, you can host **both** the frontend and backend on InfinityFree:

1. Upload both `frontend/` and `backend/` to the InfinityFree `htdocs/` directory:
   ```
   htdocs/
   ├── frontend/
   ├── backend/
   └── admin.php
   ```
2. Users access: `https://your-domain.ifree.page/frontend/index.html`
   - Because the browser loads the page directly from InfinityFree, it solves the `aes.js` challenge on initial page load.
   - Subsequent `register.php` fetch requests from the same domain succeed without restriction.
3. On the college server (`https://rvrjcce.ac.in/innovex2026/`), simply redirect or link to your InfinityFree URL.

---

## Option 3: Decoupled with an External Cloud API Host

If the frontend MUST stay on `rvrjcce.ac.in` and the backend cannot be hosted there, deploy the backend to a cloud host that natively supports CORS and REST APIs without anti-bot cookies:
- **Alwaysdata** (free 100MB PHP + MySQL + full CORS)
- **Render.com** (free Web Service with PHP Docker)
- **Railway.app**

Then set `window.COGNOS_API_BASE_URL` in `frontend/js/config.js` to your deployed backend URL:
```javascript
window.COGNOS_API_BASE_URL = "https://your-backend.alwaysdata.net/backend";
```
