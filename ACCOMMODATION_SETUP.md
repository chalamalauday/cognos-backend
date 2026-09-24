# Accommodation setup

The registration form now collects:

- Accommodation group: `Boys` or `Girls`
- Distance from college in kilometres
- Whether accommodation is required

Accommodation requests are accepted only when the distance is greater than 100 km. The PHP endpoint enforces this rule even if someone bypasses the browser form.

## Existing XAMPP database

1. Start Apache and MySQL in XAMPP.
2. Open `http://localhost/phpmyadmin`.
3. Select the `cognos_2k26` database in the left sidebar.
4. Open the **Import** tab.
5. Choose `C:\xampp\htdocs\TECH_FEST\accommodation_migration.sql`.
6. Click **Import** and confirm that all statements succeed.
7. Open the `registrations` table and check that these columns exist:
   - `gender`
   - `distance_from_college_km`
   - `accommodation_required`
8. Open `http://localhost/TECH_FEST/admin.php`, log in, and verify the accommodation counters and table column.

Do not import the complete `database.sql` into an existing database unless you intend to recreate the tables; that file drops and recreates the registration tables.

## New database

For a fresh installation, import `database.sql`. It already includes the accommodation columns and index, so the migration file is not needed.

## Admin exports

Use **Export Excel Sheets** in the admin dashboard:

- **Boys Accommodation Sheet** exports only requests marked Boys with accommodation required.
- **Girls Accommodation Sheet** exports only requests marked Girls with accommodation required.

The downloads are UTF-8 CSV files and open directly in Microsoft Excel.

## Vishleshana participant upgrade

If `accommodation_migration.sql` was already imported, import `vishleshana_migration.sql` separately in phpMyAdmin. It adds `primary_vishleshana` and `teammate_vishleshana` without repeating the accommodation columns.

If the Vishleshana flag migration was already imported, import `participant_migration.sql` instead. It adds teammate email storage and the normalized `registration_participants` table.

When Vishleshana and a team event are selected together, the form asks whether only the primary participant, only the teammate, or both participate in Vishleshana. Both are stored as separate participant records when selected. The Vishleshana export contains one row per individual participant, including each participant email.
