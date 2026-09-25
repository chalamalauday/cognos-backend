<?php
/**
 * COGNOS 2K26 - Admin Analytics & Management Dashboard (Clean Light Theme)
 * Direct access only via /admin.php (Not linked in website UI)
 */

session_start();
require_once __DIR__ . '/backend/config.php';
require_once __DIR__ . '/backend/db.php';

// Handle Logout
if (isset($_GET['action']) && $_GET['action'] === 'logout') {
    unset($_SESSION['cognos_admin_logged_in']);
    unset($_SESSION['cognos_admin_user']);
    session_destroy();
    header('Location: admin.php');
    exit;
}

$loginError = '';

// Handle Login Form Submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_submit'])) {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');

    // 1. Verify against config.php credentials
    if ($username === ADMIN_USERNAME && $password === ADMIN_PASSWORD) {
        $_SESSION['cognos_admin_logged_in'] = true;
        $_SESSION['cognos_admin_user'] = $username;
        header('Location: admin.php');
        exit;
    }

    // 2. Fallback check against MySQL admin_users table
    try {
        $pdo = get_db_connection();
        $stmt = $pdo->prepare("SELECT * FROM `admin_users` WHERE `username` = ? LIMIT 1");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            $_SESSION['cognos_admin_logged_in'] = true;
            $_SESSION['cognos_admin_user'] = $user['username'];
            header('Location: admin.php');
            exit;
        } else {
            $loginError = 'Invalid username or password. Please try again.';
        }
    } catch (Exception $e) {
        $loginError = 'Authentication error. Check database or use default config credentials.';
    }
}

$isLoggedIn = isset($_SESSION['cognos_admin_logged_in']) && $_SESSION['cognos_admin_logged_in'] === true;

$initialStats = [
    'total_registrations' => 0,
    'vishleshana' => 0,
    'razzle_review' => 0,
    'data_dazzle' => 0,
    'unique_colleges' => 0,
    'teams_count' => 0,
    'boys_accommodation' => 0,
    'girls_accommodation' => 0
];
$initialRegistrations = [];

if ($isLoggedIn) {
    try {
        $pdo = get_db_connection();

        // 1. Live stats
        $totalReg = (int)$pdo->query("SELECT COUNT(*) FROM `registrations`")->fetchColumn();
        $eventCounts = [];
        foreach (array_keys($COGNOS_EVENTS) as $ev) {
            $stmt = $pdo->prepare("SELECT COUNT(*) FROM `registration_events` WHERE `event_name` = ?");
            $stmt->execute([$ev]);
            $eventCounts[$ev] = (int)$stmt->fetchColumn();
        }
        $uniqueColleges = (int)$pdo->query("SELECT COUNT(DISTINCT `college_name`) FROM `registrations`")->fetchColumn();
        $teammateCount = (int)$pdo->query("SELECT COUNT(*) FROM `registrations` WHERE `has_teammate` = 1")->fetchColumn();
        $boysAccommodation = (int)$pdo->query("SELECT COUNT(*) FROM `registrations` WHERE `accommodation_required` = 1 AND `gender` = 'Boys'")->fetchColumn();
        $girlsAccommodation = (int)$pdo->query("SELECT COUNT(*) FROM `registrations` WHERE `accommodation_required` = 1 AND `gender` = 'Girls'")->fetchColumn();
        $vCount = (int)$pdo->query("SELECT COUNT(*) FROM `registration_participants` WHERE `participates_vishleshana` = 1")->fetchColumn();

        $initialStats = [
            'total_registrations' => $totalReg,
            'vishleshana' => $vCount,
            'razzle_review' => $eventCounts['Razzle Review'] ?? 0,
            'data_dazzle' => $eventCounts['Data Dazzle'] ?? 0,
            'unique_colleges' => $uniqueColleges,
            'teams_count' => $teammateCount,
            'boys_accommodation' => $boysAccommodation,
            'girls_accommodation' => $girlsAccommodation
        ];

        // 2. Live registrations
        $sql = "
            SELECT 
                r.id,
                r.reg_code,
                r.student_name,
                r.roll_no,
                r.branch,
                r.college_name,
                r.gender,
                r.distance_from_college_km,
                r.accommodation_required,
                r.primary_vishleshana,
                r.teammate_vishleshana,
                r.email,
                r.id_card_path,
                r.has_teammate,
                r.teammate_name,
                r.teammate_email,
                r.teammate_roll_no,
                r.teammate_branch,
                r.teammate_college,
                r.created_at,
                GROUP_CONCAT(re.event_name ORDER BY re.event_name SEPARATOR ', ') AS events_list
            FROM `registrations` r
            LEFT JOIN `registration_events` re ON r.id = re.registration_id
            GROUP BY r.id
            ORDER BY r.id DESC
        ";
        $initialRegistrations = $pdo->query($sql)->fetchAll();
    } catch (Exception $e) {
        // Fallback gracefully
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo FEST_NAME; ?> - Admin Management Portal</title>
    <link rel="icon" type="image/svg+xml" href="frontend/assets/images/favicon.svg">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --royal-blue: #0b57d0;
            --royal-blue-hover: #0842a0;
            --header-blue: #0052a5;
            --brand-pink: #e11d48;
            --bg-page: #f8fafc;
            --bg-card: #ffffff;
            --border-card: #e2e8f0;
            --border-light: #f1f5f9;
            --text-heading: #0f172a;
            --text-body: #334155;
            --text-muted: #64748b;
            --font-main: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
            --shadow-sm: 0 1px 3px rgba(0, 0, 0, 0.05);
            --shadow-md: 0 4px 12px -2px rgba(0, 0, 0, 0.08);
            --shadow-lg: 0 14px 28px -4px rgba(0, 0, 0, 0.1);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
            font-family: var(--font-main);
        }

        body {
            background-color: var(--bg-page);
            color: var(--text-body);
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }

        /* Top Header Banner */
        .top-header-banner {
            background: #ffffff;
            border-bottom: 1px solid var(--border-card);
            padding: 10px 0;
            text-align: center;
            width: 100%;
        }

        .college-header-logo {
            max-width: 100%;
            height: auto;
            max-height: 76px;
            display: block;
            margin: 0 auto;
            object-fit: contain;
        }

        /* Solid Blue Navbar */
        .main-navbar {
            background: var(--header-blue);
            width: 100%;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
            padding: 0 24px;
        }

        .nav-container {
            max-width: 1400px;
            margin: 0 auto;
            display: flex;
            justify-content: space-between;
            align-items: center;
            min-height: 52px;
        }

        .nav-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            color: #ffffff;
            text-decoration: none;
        }

        .nav-brand-title {
            font-size: 18px;
            font-weight: 800;
            letter-spacing: 0.5px;
        }

        .nav-badge-pill {
            background: rgba(255, 255, 255, 0.2);
            color: #ffffff;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 4px;
            text-transform: uppercase;
        }

        .nav-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .btn-nav-action {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
            font-size: 13px;
            font-weight: 600;
            padding: 6px 14px;
            border-radius: 6px;
            text-decoration: none;
            transition: all 0.2s;
            border: 1px solid rgba(255, 255, 255, 0.25);
        }

        .btn-nav-action:hover {
            background: rgba(255, 255, 255, 0.25);
        }

        .btn-nav-logout {
            background: #ef4444;
            color: #ffffff;
            border-color: #ef4444;
        }

        .btn-nav-logout:hover {
            background: #dc2626;
        }

        /* Login Screen */
        .login-wrap {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 40px 20px;
        }

        .login-card {
            background: #ffffff;
            border: 1px solid var(--border-card);
            border-radius: 12px;
            padding: 36px 32px;
            max-width: 440px;
            width: 100%;
            box-shadow: var(--shadow-lg);
            text-align: center;
        }

        .login-badge {
            display: inline-block;
            background: #eff6ff;
            color: var(--royal-blue);
            border: 1px solid #bfdbfe;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 11px;
            font-weight: 800;
            margin-bottom: 16px;
            text-transform: uppercase;
        }

        .login-card h2 {
            font-size: 28px;
            font-weight: 800;
            color: var(--text-heading);
            margin-bottom: 8px;
        }

        .login-card p {
            font-size: 16px;
            color: var(--text-muted);
            margin-bottom: 24px;
            line-height: 1.5;
        }

        .form-group {
            margin-bottom: 18px;
            text-align: left;
        }

        .form-group label {
            display: block;
            font-size: 14px;
            font-weight: 700;
            color: var(--text-body);
            margin-bottom: 6px;
            text-transform: uppercase;
        }

        .form-control {
            width: 100%;
            padding: 11px 14px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            font-size: 16px;
            color: var(--text-heading);
            outline: none;
            transition: all 0.2s;
        }

        .form-control:focus {
            border-color: var(--royal-blue);
            box-shadow: 0 0 0 3px rgba(11, 87, 208, 0.15);
        }

        .btn-login-submit {
            width: 100%;
            padding: 12px;
            background: var(--royal-blue);
            color: #ffffff;
            font-weight: 700;
            font-size: 17px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .btn-login-submit:hover {
            background: var(--royal-blue-hover);
        }

        .error-banner {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #b91c1c;
            padding: 10px 14px;
            border-radius: 6px;
            font-size: 13px;
            margin-bottom: 18px;
            text-align: left;
        }

        /* Dashboard Container */
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px 24px;
            width: 100%;
            flex: 1;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 18px;
            margin-bottom: 28px;
        }

        .stat-card {
            background: #ffffff;
            border: 1px solid var(--border-card);
            border-radius: 12px;
            padding: 20px;
            box-shadow: var(--shadow-sm);
            transition: all 0.2s;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
        }

        .stat-card.primary { border-left: 4px solid var(--royal-blue); }
        .stat-card.v-card { border-left: 4px solid #8b5cf6; }
        .stat-card.r-card { border-left: 4px solid #0284c7; }
        .stat-card.d-card { border-left: 4px solid #059669; }
        .stat-card.teams { border-left: 4px solid #d97706; }

        .stat-label {
            font-size: 13px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 700;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .stat-value {
            font-size: 38px;
            font-weight: 800;
            color: var(--text-heading);
            line-height: 1.1;
        }

        .stat-sub {
            font-size: 14px;
            color: var(--text-muted);
            margin-top: 6px;
        }

        /* Toolbar */
        .toolbar {
            background: #ffffff;
            border: 1px solid var(--border-card);
            border-radius: 12px;
            padding: 16px 20px;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            margin-bottom: 24px;
            box-shadow: var(--shadow-sm);
        }

        .filter-tabs {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .filter-btn {
            background: #f8fafc;
            border: 1px solid #cbd5e1;
            color: var(--text-body);
            padding: 7px 16px;
            border-radius: 20px;
            font-size: 15px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s;
        }

        .filter-btn:hover, .filter-btn.active {
            background: #eff6ff;
            color: var(--royal-blue);
            border-color: #93c5fd;
        }

        .tools-right {
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }

        .search-box {
            position: relative;
        }

        .search-input {
            padding: 8px 14px 8px 34px;
            background: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 6px;
            color: var(--text-heading);
            font-size: 15.5px;
            width: 280px;
            outline: none;
        }

        .search-input:focus {
            border-color: var(--royal-blue);
        }

        .search-icon {
            position: absolute;
            left: 10px;
            top: 50%;
            transform: translateY(-50%);
            font-size: 13px;
            color: #94a3b8;
        }

        /* Excel Export Dropdown */
        .export-dropdown {
            position: relative;
            display: inline-block;
        }

        .btn-export {
            background: #059669;
            color: #ffffff;
            padding: 8px 16px;
            border-radius: 6px;
            font-size: 15px;
            font-weight: 700;
            border: none;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: all 0.2s;
        }

        .btn-export:hover {
            background: #047857;
        }

        .dropdown-menu {
            display: none;
            position: absolute;
            right: 0;
            top: 100%;
            background: #ffffff;
            border: 1px solid var(--border-card);
            border-radius: 8px;
            padding: 8px 0;
            min-width: 260px;
            box-shadow: var(--shadow-lg);
            z-index: 100;
            margin-top: 4px;
        }

        .export-dropdown:hover .dropdown-menu {
            display: block;
        }

        .dropdown-menu a {
            display: block;
            padding: 9px 16px;
            color: var(--text-body);
            font-size: 13px;
            text-decoration: none;
            font-weight: 600;
        }

        .dropdown-menu a:hover {
            background: #eff6ff;
            color: var(--royal-blue);
        }

        /* Table */
        .table-wrap {
            background: #ffffff;
            border: 1px solid var(--border-card);
            border-radius: 12px;
            overflow-x: auto;
            box-shadow: var(--shadow-sm);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 15px;
            text-align: left;
        }

        thead {
            background: #f8fafc;
            border-bottom: 1px solid var(--border-card);
        }

        th {
            padding: 14px 16px;
            font-size: 13.5px;
            text-transform: uppercase;
            letter-spacing: 0.6px;
            color: var(--text-muted);
            font-weight: 700;
        }

        td {
            padding: 14px 16px;
            border-bottom: 1px solid var(--border-light);
            color: var(--text-body);
            vertical-align: middle;
        }

        tbody tr:hover {
            background-color: #f8fafc;
        }

        .reg-badge {
            background: #eff6ff;
            color: var(--royal-blue);
            border: 1px solid #bfdbfe;
            padding: 4px 8px;
            border-radius: 4px;
            font-family: monospace;
            font-weight: 700;
            font-size: 12px;
            white-space: nowrap;
        }

        .event-tag {
            display: inline-block;
            background: #f1f5f9;
            color: var(--text-body);
            padding: 2px 8px;
            border-radius: 4px;
            font-size: 11px;
            margin: 2px;
            font-weight: 600;
        }

        .tag-v { border-left: 3px solid #8b5cf6; }
        .tag-r { border-left: 3px solid #0284c7; }
        .tag-d { border-left: 3px solid #059669; }

        .btn-view-id {
            background: #eff6ff;
            color: var(--royal-blue);
            border: 1px solid #bfdbfe;
            padding: 4px 10px;
            border-radius: 4px;
            font-size: 11.5px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
        }

        .btn-view-id:hover {
            background: #dbeafe;
        }

        .btn-del {
            background: #fee2e2;
            border: 1px solid #fca5a5;
            color: #b91c1c;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            cursor: pointer;
            font-weight: 600;
        }

        .btn-del:hover {
            background: #ef4444;
            color: #ffffff;
        }

        /* Modal Preview */
        .id-modal {
            display: none;
            position: fixed;
            top: 0; left: 0; width: 100%; height: 100%;
            background: rgba(15, 23, 42, 0.7);
            backdrop-filter: blur(4px);
            z-index: 9999;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .id-modal.active {
            display: flex;
        }

        .modal-content-wrap {
            background: #ffffff;
            border: 1px solid var(--border-card);
            border-radius: 12px;
            max-width: 800px;
            width: 100%;
            padding: 20px;
            position: relative;
            text-align: center;
            max-height: 90vh;
            display: flex;
            flex-direction: column;
            box-shadow: var(--shadow-lg);
        }

        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid var(--border-light);
        }

        .modal-header h3 {
            font-size: 16px;
            color: var(--text-heading);
        }

        .close-btn {
            background: transparent;
            border: none;
            color: var(--text-muted);
            font-size: 24px;
            cursor: pointer;
        }

        .id-preview-frame {
            max-width: 100%;
            max-height: 70vh;
            object-fit: contain;
            border-radius: 6px;
        }
    </style>
</head>
<body>

    <!-- 1. Top Header Banner -->
    <div class="top-header-banner">
        <img src="header_logo.png" alt="R.V.R. &amp; J.C. COLLEGE OF ENGINEERING" class="college-header-logo">
    </div>

    <!-- 2. Solid Blue Navbar -->
    <header class="main-navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <a href="frontend/index.html" style="color: #ffffff; display: flex; align-items: center;" title="View Public Website">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M10 20v-6h4v6h5v-8h3L12 3 2 12h3v8z"/></svg>
                </a>
                <span class="nav-brand-title"><?php echo FEST_NAME; ?> Admin Portal</span>
                <span class="nav-badge-pill">Confidential</span>
            </div>
            
            <?php if ($isLoggedIn): ?>
            <div class="nav-actions">
                <span style="font-size: 13px; color: rgba(255,255,255,0.9);">
                    Logged in as <strong><?php echo htmlspecialchars($_SESSION['cognos_admin_user']); ?></strong>
                </span>
                <a href="frontend/index.html" class="btn-nav-action" target="_blank">🌐 Live Fest Site</a>
                <a href="admin.php?action=logout" class="btn-nav-action btn-nav-logout">Logout 🚪</a>
            </div>
            <?php endif; ?>
        </div>
    </header>

<?php if (!$isLoggedIn): ?>
    <!-- 3. LOGIN SCREEN -->
    <div class="login-wrap">
        <div class="login-card">
            <span class="login-badge">COGNOS 2K26 ADMIN</span>
            <h2>Administrator Login</h2>
            <p>Enter your administrative credentials to manage registrations and download official participant lists.</p>

            <?php if (!empty($loginError)): ?>
                <div class="error-banner">⚠️ <?php echo htmlspecialchars($loginError); ?></div>
            <?php endif; ?>

            <form method="POST" action="admin.php">
                <div class="form-group">
                    <label for="username">Username</label>
                    <input type="text" name="username" id="username" class="form-control" placeholder="admin" required autofocus>
                </div>
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" name="password" id="password" class="form-control" placeholder="••••••••••••" required>
                </div>
                <button type="submit" name="login_submit" class="btn-login-submit">Unlock Admin Portal &rarr;</button>
            </form>
        </div>
    </div>

<?php else: ?>
    <!-- 4. LOGGED-IN ADMIN DASHBOARD -->
    <main class="container">
        <!-- Live Analytics Stats -->
        <section class="stats-grid">
            <div class="stat-card primary">
                <div class="stat-label">Total Registrations</div>
                <div class="stat-value" id="stat-total"><?php echo (int)($initialStats['total_registrations'] ?? 0); ?></div>
                <div class="stat-sub">Confirmed candidates</div>
            </div>
            <div class="stat-card v-card">
                <div class="stat-label">Vishleshana (GD)</div>
                <div class="stat-value" id="stat-v"><?php echo (int)($initialStats['vishleshana'] ?? 0); ?></div>
                <div class="stat-sub">Oct 9 &bull; 2:00 &ndash; 5:00 PM</div>
            </div>
            <div class="stat-card r-card">
                <div class="stat-label">Razzle Review (Paper)</div>
                <div class="stat-value" id="stat-r"><?php echo (int)($initialStats['razzle_review'] ?? 0); ?></div>
                <div class="stat-sub">Oct 9 &bull; 11:00 AM onwards</div>
            </div>
            <div class="stat-card d-card">
                <div class="stat-label">Data Dazzle (BI Story)</div>
                <div class="stat-value" id="stat-d"><?php echo (int)($initialStats['data_dazzle'] ?? 0); ?></div>
                <div class="stat-sub">Oct 9 &bull; 1:00 &ndash; 3:00 PM</div>
            </div>
            <div class="stat-card teams">
                <div class="stat-label">Colleges &amp; Teams</div>
                <div class="stat-value" id="stat-colleges"><?php echo (int)($initialStats['unique_colleges'] ?? 0); ?></div>
                <div class="stat-sub" id="stat-teams-sub"><?php echo (int)($initialStats['teams_count'] ?? 0); ?> team registrations</div>
            </div>
            <div class="stat-card" style="border-left: 4px solid #f97316;">
                <div class="stat-label">Boys Accommodation</div>
                <div class="stat-value" id="stat-boys-accommodation"><?php echo (int)($initialStats['boys_accommodation'] ?? 0); ?></div>
                <div class="stat-sub">Requests above 100 km</div>
            </div>
            <div class="stat-card" style="border-left: 4px solid #db2777;">
                <div class="stat-label">Girls Accommodation</div>
                <div class="stat-value" id="stat-girls-accommodation"><?php echo (int)($initialStats['girls_accommodation'] ?? 0); ?></div>
                <div class="stat-sub">Requests above 100 km</div>
            </div>
        </section>

        <!-- Toolbar / Filters & Excel Export -->
        <section class="toolbar">
            <div class="filter-tabs">
                <button class="filter-btn active" onclick="setEventFilter('all', this)">All Registrations</button>
                <button class="filter-btn" onclick="setEventFilter('Vishleshana', this)">Vishleshana</button>
                <button class="filter-btn" onclick="setEventFilter('Razzle Review', this)">Razzle Review</button>
                <button class="filter-btn" onclick="setEventFilter('Data Dazzle', this)">Data Dazzle</button>
            </div>

            <div class="tools-right">
                <div class="search-box">
                    <span class="search-icon">🔍</span>
                    <input type="text" id="searchInput" class="search-input" placeholder="Search name, roll, college..." oninput="handleSearch()">
                </div>

                <!-- Excel Export Dropdown -->
                <div class="export-dropdown">
                    <button class="btn-export">
                        📊 Export Excel Sheets ▾
                    </button>
                    <div class="dropdown-menu">
                        <a href="backend/export.php?event=all" target="_blank" class="export-link" data-sub="export.php?event=all">📥 Master Sheet (All Registrations)</a>
                        <a href="backend/export.php?event=Vishleshana" target="_blank" class="export-link" data-sub="export.php?event=Vishleshana">📥 Vishleshana Individual Participants</a>
                        <a href="backend/export.php?event=Razzle+Review" target="_blank" class="export-link" data-sub="export.php?event=Razzle+Review">📥 Razzle Review (Paper) Sheet</a>
                        <a href="backend/export.php?event=Data+Dazzle" target="_blank" class="export-link" data-sub="export.php?event=Data+Dazzle">📥 Data Dazzle (BI) Sheet</a>
                        <a href="backend/export.php?accommodation=boys" target="_blank" class="export-link" data-sub="export.php?accommodation=boys">📥 Boys Accommodation Sheet</a>
                        <a href="backend/export.php?accommodation=girls" target="_blank" class="export-link" data-sub="export.php?accommodation=girls">📥 Girls Accommodation Sheet</a>
                    </div>
                </div>
            </div>
        </section>

        <!-- Registrations Table -->
        <section class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Reg ID</th>
                        <th>Student Name &amp; Roll No</th>
                        <th>College &amp; Branch</th>
                        <th>Email ID</th>
                        <th>Accommodation</th>
                        <th>Vishleshana Participants</th>
                        <th>Registered Challenges</th>
                        <th>Teammate Details</th>
                        <th>ID Card</th>
                        <th>Timestamp</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="registrationsTbody">
                    <tr>
                            <td colspan="11" style="text-align: center; padding: 40px; color: #64748b;">
                            Loading registrations...
                        </td>
                    </tr>
                </tbody>
            </table>
        </section>
    </main>

    <!-- ID Card Modal Preview -->
    <div id="idModal" class="id-modal" onclick="closeIdModal()">
        <div class="modal-content-wrap" onclick="event.stopPropagation()">
            <div class="modal-header">
                <h3 id="idModalTitle">College ID Card Document</h3>
                <button class="close-btn" onclick="closeIdModal()">&times;</button>
            </div>
            <div id="idModalBody" style="display:flex; align-items:center; justify-content:center; min-height: 250px;">
                <!-- Dynamically loaded preview image or pdf iframe -->
            </div>
        </div>
    </div>

    <script>
        const API_BASE = (function() {
            const p = window.location.pathname;
            const base = p.replace(/\/admin\.php.*$/, '');
            return (base ? base : '') + '/backend';
        })();

        let allRegistrations = <?php echo json_encode($initialRegistrations, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?: '[]'; ?>;
        let currentFilter = 'all';
        let searchTimer = null;

        document.addEventListener('DOMContentLoaded', () => {
            // Update export links to use API_BASE dynamically
            document.querySelectorAll('.export-link').forEach(link => {
                const sub = link.getAttribute('data-sub');
                if (sub) link.href = `${API_BASE}/${sub}`;
            });

            // Immediately render the preloaded registrations
            if (Array.isArray(allRegistrations) && allRegistrations.length > 0) {
                renderRegistrations();
            } else {
                fetchRegistrations();
            }
        });

        async function fetchStats() {
            try {
                const res = await fetch(`${API_BASE}/admin_api.php?action=stats`);
                const text = await res.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch(e) {
                    console.warn('Stats fetch returned non-JSON response:', text.substring(0, 200));
                    return;
                }
                if (data.success) {
                    const stats = data.stats || data;
                    const breakdown = stats.events_breakdown || data.events_breakdown || {};
                    document.getElementById('stat-total').innerText = stats.total_registrations ?? 0;
                    document.getElementById('stat-v').innerText = stats.vishleshana ?? breakdown['Vishleshana'] ?? 0;
                    document.getElementById('stat-r').innerText = stats.razzle_review ?? breakdown['Razzle Review'] ?? 0;
                    document.getElementById('stat-d').innerText = stats.data_dazzle ?? breakdown['Data Dazzle'] ?? 0;
                    document.getElementById('stat-colleges').innerText = stats.unique_colleges ?? 0;
                    document.getElementById('stat-teams-sub').innerText = (stats.teams_count ?? 0) + ' team registrations';
                    document.getElementById('stat-boys-accommodation').innerText = stats.boys_accommodation ?? 0;
                    document.getElementById('stat-girls-accommodation').innerText = stats.girls_accommodation ?? 0;
                }
            } catch (err) {
                console.error('Stats fetch error:', err);
            }
        }

        async function fetchRegistrations() {
            try {
                const search = (document.getElementById('searchInput').value || '').trim();
                const params = new URLSearchParams({ action: 'list', event: currentFilter, search });
                const res = await fetch(`${API_BASE}/admin_api.php?${params.toString()}`);
                const text = await res.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch(e) {
                    console.warn('Registrations fetch returned non-JSON response:', text.substring(0, 200));
                    if (!allRegistrations || allRegistrations.length === 0) {
                        document.getElementById('registrationsTbody').innerHTML = `<tr><td colspan="11" style="text-align:center; padding:30px; color:#ef4444;">Server communication error. Please refresh the page.</td></tr>`;
                    }
                    return;
                }
                if (data.success) {
                    allRegistrations = data.registrations || [];
                    renderRegistrations();
                } else {
                    document.getElementById('registrationsTbody').innerHTML = `<tr><td colspan="11" style="text-align:center; padding:30px; color:#ef4444;">${data.message || 'Failed to load records'}</td></tr>`;
                }
            } catch (err) {
                if (!allRegistrations || allRegistrations.length === 0) {
                    document.getElementById('registrationsTbody').innerHTML = `<tr><td colspan="11" style="text-align:center; padding:30px; color:#ef4444;">Connection error while fetching records.</td></tr>`;
                }
            }
        }

        function setEventFilter(event, btn) {
            currentFilter = event;
            document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            fetchRegistrations();
        }

        function handleSearch() {
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(fetchRegistrations, 250);
        }

        function renderRegistrations() {
            const query = (document.getElementById('searchInput').value || '').toLowerCase().trim();
            const tbody = document.getElementById('registrationsTbody');

            const filtered = allRegistrations.filter(row => {
                // Event filter
                if (currentFilter !== 'all') {
                    const evArray = (row.events_list || '').split(',');
                    const hasEv = evArray.some(e => e.trim().toLowerCase() === currentFilter.toLowerCase());
                    if (!hasEv) return false;
                }

                // Search query filter
                if (query) {
                    const hay = [
                        row.reg_code,
                        row.student_name,
                        row.roll_no,
                        row.college_name,
                        row.branch,
                        row.email,
                        row.gender,
                        row.distance_from_college_km,
                        row.accommodation_required,
                        row.events_list,
                        row.primary_vishleshana ? row.student_name : '',
                        row.teammate_vishleshana ? row.teammate_name : '',
                        row.teammate_name,
                        row.teammate_email,
                        row.teammate_roll_no
                    ].join(' ').toLowerCase();

                    return hay.includes(query);
                }

                return true;
            });

            if (filtered.length === 0) {
                tbody.innerHTML = `<tr><td colspan="11" style="text-align:center; padding:40px; color:#64748b;">No registrations found matching the criteria.</td></tr>`;
                return;
            }

            tbody.innerHTML = filtered.map(row => {
                // Events Pills
                const events = (row.events_list || '').split(',').map(e => e.trim()).filter(Boolean);
                const evPills = events.map(e => {
                    let c = '';
                    if (e.includes('Vishleshana')) c = 'tag-v';
                    else if (e.includes('Razzle')) c = 'tag-r';
                    else if (e.includes('Data')) c = 'tag-d';
                    return `<span class="event-tag ${c}">${escapeHtml(e)}</span>`;
                }).join('');

                // Teammate formatting
                let tmHtml = '<span style="color:#94a3b8; font-size:12px;">None (Solo)</span>';
                if (parseInt(row.has_teammate, 10) === 1 && row.teammate_name) {
                    tmHtml = `
                        <div style="font-size:12.5px;">
                            <strong>${escapeHtml(row.teammate_name)}</strong>
                            <div style="color:#64748b; font-size:11px;">Roll: ${escapeHtml(row.teammate_roll_no || '')} (${escapeHtml(row.teammate_branch || '')})</div>
                            <div style="color:#64748b; font-size:11px;">${escapeHtml(row.teammate_email || '')}</div>
                            <div style="color:#64748b; font-size:11px;">${escapeHtml(row.teammate_college || '')}</div>
                        </div>
                    `;
                }

                const accommodationHtml = parseInt(row.accommodation_required, 10) === 1
                    ? `<strong style="color:#047857;">${escapeHtml(row.gender || '')}</strong><div style="font-size:11px; color:#64748b;">${escapeHtml(row.distance_from_college_km || '')} km · Requested</div>`
                    : `<span style="color:#64748b; font-size:12px;">${escapeHtml(row.gender || 'N/A')}<br>Not requested<br>${escapeHtml(row.distance_from_college_km || 'N/A')} km</span>`;

                const vishleshanaParticipants = [];
                if (parseInt(row.primary_vishleshana, 10) === 1) {
                    vishleshanaParticipants.push(`${escapeHtml(row.student_name)}<br><span style="color:#64748b;">${escapeHtml(row.roll_no)} · ${escapeHtml(row.email || '')}</span>`);
                }
                if (parseInt(row.teammate_vishleshana, 10) === 1 && row.teammate_name) {
                    vishleshanaParticipants.push(`${escapeHtml(row.teammate_name)}<br><span style="color:#64748b;">${escapeHtml(row.teammate_roll_no || '')} · ${escapeHtml(row.teammate_email || '')}</span>`);
                }
                const vishleshanaHtml = vishleshanaParticipants.length
                    ? vishleshanaParticipants.map(participant => `<div style="margin-bottom:5px;">${participant}</div>`).join('')
                    : '<span style="color:#94a3b8; font-size:12px;">None</span>';

                // ID Card View
                let idHtml = '<span style="color:#94a3b8; font-size:11px;">No file</span>';
                if (row.id_card_path) {
                    idHtml = `<button class="btn-view-id" onclick="viewIdCard('${escapeHtml(row.id_card_path)}', '${escapeHtml(row.student_name)}')">View ID Card 📎</button>`;
                }

                return `
                    <tr>
                        <td><span class="reg-badge">${escapeHtml(row.reg_code)}</span></td>
                        <td>
                            <strong style="color:#0f172a;">${escapeHtml(row.student_name)}</strong>
                            <div style="font-size:11.5px; color:#64748b; font-family:monospace;">${escapeHtml(row.roll_no)}</div>
                        </td>
                        <td>
                            <div>${escapeHtml(row.college_name)}</div>
                            <div style="font-size:11.5px; color:#64748b;">${escapeHtml(row.branch)}</div>
                        </td>
                        <td>
                            <a href="mailto:${escapeHtml(row.email)}" style="color:#0b57d0; font-size:12.5px;">${escapeHtml(row.email)}</a>
                        </td>
                        <td>${accommodationHtml}</td>
                        <td>${vishleshanaHtml}</td>
                        <td>${evPills}</td>
                        <td>${tmHtml}</td>
                        <td>${idHtml}</td>
                        <td style="font-size:11.5px; color:#64748b; white-space:nowrap;">
                            ${escapeHtml(row.created_at || '')}
                        </td>
                        <td>
                            <button class="btn-del" onclick="deleteReg(${row.id}, '${escapeHtml(row.reg_code)}')">Delete</button>
                        </td>
                    </tr>
                `;
            }).join('');
        }

        function viewIdCard(path, name) {
            const modal = document.getElementById('idModal');
            const title = document.getElementById('idModalTitle');
            const body = document.getElementById('idModalBody');

            title.innerText = `College ID Card – ${name}`;
            const ext = path.split('.').pop().toLowerCase();

            let fullUrl = path;
            if (!path.startsWith('http://') && !path.startsWith('https://')) {
                if (path.startsWith('/')) {
                    fullUrl = path;
                } else if (path.startsWith('backend/uploads/')) {
                    const clean = path.replace(/^backend\//, '');
                    fullUrl = `${API_BASE}/${clean}`;
                } else if (path.startsWith('uploads/')) {
                    fullUrl = `${API_BASE}/${path}`;
                } else {
                    fullUrl = `${API_BASE}/${path}`;
                }
            }

            if (ext === 'pdf') {
                body.innerHTML = `<iframe src="${fullUrl}" style="width:100%; height:70vh; border:none;"></iframe>`;
            } else {
                body.innerHTML = `<img src="${fullUrl}" class="id-preview-frame" alt="ID Card">`;
            }

            modal.classList.add('active');
        }

        function closeIdModal() {
            document.getElementById('idModal').classList.remove('active');
        }

        async function deleteReg(id, code) {
            if (!confirm(`Are you sure you want to permanently delete registration ${code}?`)) return;

            try {
                const formData = new FormData();
                formData.append('id', id);

                const res = await fetch(`${API_BASE}/admin_api.php?action=delete`, {
                    method: 'POST',
                    body: formData
                });
                const text = await res.text();
                let data;
                try {
                    data = JSON.parse(text);
                } catch(e) {
                    alert('Server error while deleting record.');
                    return;
                }
                if (data.success) {
                    allRegistrations = allRegistrations.filter(r => r.id !== id);
                    renderRegistrations();
                    fetchStats();
                } else {
                    alert(data.message || 'Failed to delete record');
                }
            } catch (err) {
                alert('Connection error while deleting record.');
            }
        }

        function escapeHtml(str) {
            if (!str) return '';
            return String(str)
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;')
                .replace(/"/g, '&quot;')
                .replace(/'/g, '&#039;');
        }
    </script>
<?php endif; ?>

</body>
</html>
