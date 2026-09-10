# Earthen Beauty by Nupur - CloudPanel Deployment Guide

Follow these simple 4 steps to deploy Earthen Beauty to your CloudPanel server (`168.231.103.36:8443`):

---

### Step 1: Create a PHP Site in CloudPanel
1. Log into your CloudPanel dashboard: **`https://168.231.103.36:8443/`**
2. Click **+ Add Site** ➔ **Create a PHP Site**.
3. Fill in:
   * **Domain Name**: Your assigned domain or subdomain (or IP address if configured).
   * **Site User**: `teama8` (or default).
   * **PHP Version**: **PHP 8.1** or **PHP 8.2**.
4. Click **Create**.

---

### Step 2: Create a MySQL Database in CloudPanel
1. In CloudPanel left sidebar, click **Databases** ➔ **+ Add Database**.
2. Fill in:
   * **Database Name**: `earthen_beauty`
   * **Database User**: `earthen_user` (or any username)
   * **Password**: (Choose a password and copy it)
3. Click **Create Database**.

---

### Step 3: Upload Website Files
1. In CloudPanel, click **Sites** ➔ click on your site ➔ open the **File Manager** tab.
2. Navigate inside your site directory (e.g. `htdocs/<your-domain>/`).
3. If there is a default `index.html` or `index.php` created by CloudPanel, delete it.
4. Click **Upload** and select **`earthen-beauty-live.zip`**.
5. Right-click the uploaded zip and click **Extract**.

---

### Step 4: 1-Click Database Setup (Browser)
1. Open your website in your browser and visit:
   ```
   https://<your-domain>/install.php
   ```
2. Enter the Database Name, User, and Password you created in Step 2.
3. Click **🚀 Initialize & Seed Database**.
4. That's it! It automatically creates all 7 tables and seeds all 77 catalog products!
5. Now visit `https://<your-domain>/` to enjoy your live store!

---

### Admin Login Credentials:
* **Admin Portal**: `https://<your-domain>/admin.html`
* **Email**: `earthenbeauty@gmail.com`
* **Password**: `EARTHENBEAUTY`
