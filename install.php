<?php
/**
 * ===================================================================
 * Earthen Beauty by Nupur - 1-Click MySQL Database Installer & Seeder
 * ===================================================================
 * Easily initialize or repair all MySQL tables, 77 products, and admin accounts.
 */

// Error reporting
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Load existing .env if present
$envFile = __DIR__ . '/.env';
$envValues = [];
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (strpos($line, '=') !== false) {
            list($k, $v) = explode('=', $line, 2);
            $envValues[trim($k)] = trim($v);
        }
    }
}

$dbHost = $_POST['db_host'] ?? ($envValues['DB_HOST'] ?? '127.0.0.1');
$dbPort = $_POST['db_port'] ?? ($envValues['DB_PORT'] ?? '3306');
$dbName = $_POST['db_name'] ?? ($envValues['DB_NAME'] ?? 'earthen_beauty');
$dbUser = $_POST['db_user'] ?? ($envValues['DB_USER'] ?? 'root');
$dbPass = $_POST['db_pass'] ?? ($envValues['DB_PASS'] ?? '');

$message = '';
$messageType = '';
$installedTables = [];
$productCount = 0;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'install') {
    try {
        // Step 1: Connect to MySQL server (without selecting DB first in case DB does not exist yet)
        $rootDsn = "mysql:host={$dbHost};port={$dbPort};charset=utf8mb4";
        $pdo = new PDO($rootDsn, $dbUser, $dbPass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        // Step 2: Create database if not exists
        $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $pdo->exec("USE `{$dbName}`");

        // Step 3: Read and execute backend/schema.sql
        $schemaFile = __DIR__ . '/backend/schema.sql';
        if (!file_exists($schemaFile)) {
            throw new Exception("Schema file not found at: backend/schema.sql");
        }

        $sqlContent = file_get_contents($schemaFile);

        // Remove comments and split by semicolon
        $sqlContent = preg_replace('/--[^\n]*\n/', "\n", $sqlContent);
        $queries = preg_split('/;\s*[\r\n]+/', $sqlContent);

        $executedQueries = 0;
        foreach ($queries as $query) {
            $query = trim($query);
            if (!empty($query)) {
                $pdo->exec($query);
                $executedQueries++;
            }
        }

        // Verify product count
        $stmt = $pdo->query("SELECT COUNT(*) FROM `products`");
        $productCount = (int)$stmt->fetchColumn();

        // Get table list
        $tblStmt = $pdo->query("SHOW TABLES");
        $installedTables = $tblStmt->fetchAll(PDO::FETCH_COLUMN);

        // Update .env file with verified database credentials
        $updatedEnv = [];
        $hasHost = $hasPort = $hasName = $hasUser = $hasPass = false;

        $existingLines = file_exists($envFile) ? file($envFile, FILE_IGNORE_NEW_LINES) : [];
        foreach ($existingLines as $el) {
            $trimmed = trim($el);
            if (strpos($trimmed, 'DB_HOST=') === 0) { $updatedEnv[] = "DB_HOST={$dbHost}"; $hasHost = true; }
            elseif (strpos($trimmed, 'DB_PORT=') === 0) { $updatedEnv[] = "DB_PORT={$dbPort}"; $hasPort = true; }
            elseif (strpos($trimmed, 'DB_NAME=') === 0) { $updatedEnv[] = "DB_NAME={$dbName}"; $hasName = true; }
            elseif (strpos($trimmed, 'DB_USER=') === 0) { $updatedEnv[] = "DB_USER={$dbUser}"; $hasUser = true; }
            elseif (strpos($trimmed, 'DB_PASS=') === 0) { $updatedEnv[] = "DB_PASS={$dbPass}"; $hasPass = true; }
            else { $updatedEnv[] = $el; }
        }

        if (!$hasHost) $updatedEnv[] = "DB_HOST={$dbHost}";
        if (!$hasPort) $updatedEnv[] = "DB_PORT={$dbPort}";
        if (!$hasName) $updatedEnv[] = "DB_NAME={$dbName}";
        if (!$hasUser) $updatedEnv[] = "DB_USER={$dbUser}";
        if (!$hasPass) $updatedEnv[] = "DB_PASS={$dbPass}";

        file_put_contents($envFile, implode("\n", $updatedEnv));

        $message = "Database successfully initialized! All " . count($installedTables) . " tables created and {$productCount} catalog products seeded!";
        $messageType = "success";

    } catch (Exception $ex) {
        $message = "Installation Error: " . $ex->getMessage();
        $messageType = "error";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Earthen Beauty - MySQL Database Installer</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Cinzel:wght@500;600;700&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <style>
    :root {
      --terracotta: #b85c38;
      --terracotta-dark: #8c3f23;
      --sand-gold: #c9a96e;
      --bg-dark: #0f1115;
      --card-bg: #161a22;
      --border: rgba(255, 255, 255, 0.1);
      --text: #e8eaed;
      --text-muted: #9aa0a6;
      --success: #34a853;
      --error: #ea4335;
    }
    * { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      background: radial-gradient(circle at 50% 20%, #1f2430 0%, #0d0f14 100%);
      color: var(--text);
      font-family: 'Plus Jakarta Sans', sans-serif;
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 24px;
    }
    .installer-card {
      background: var(--card-bg);
      border: 1px solid var(--border);
      border-radius: 16px;
      width: 100%;
      max-width: 640px;
      padding: 40px;
      box-shadow: 0 20px 50px rgba(0,0,0,0.5);
    }
    .header { text-align: center; margin-bottom: 28px; }
    .header h1 { font-family: 'Cinzel', serif; font-size: 1.8rem; color: #fff; margin-bottom: 8px; }
    .header p { color: var(--text-muted); font-size: 0.95rem; }
    .alert {
      padding: 16px 20px;
      border-radius: 10px;
      margin-bottom: 24px;
      font-size: 0.95rem;
      line-height: 1.5;
    }
    .alert-success { background: rgba(52, 168, 83, 0.15); border: 1px solid var(--success); color: #81c995; }
    .alert-error { background: rgba(234, 67, 53, 0.15); border: 1px solid var(--error); color: #f28b82; }
    .form-group { margin-bottom: 18px; }
    label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); margin-bottom: 6px; text-transform: uppercase; letter-spacing: 0.5px; }
    input[type="text"], input[type="password"] {
      width: 100%;
      background: #0d1117;
      border: 1px solid var(--border);
      border-radius: 8px;
      padding: 12px 16px;
      color: #fff;
      font-size: 0.95rem;
      outline: none;
      transition: border-color 0.2s;
    }
    input[type="text"]:focus, input[type="password"]:focus { border-color: var(--terracotta); }
    .grid-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    .btn {
      width: 100%;
      background: linear-gradient(135deg, var(--terracotta) 0%, var(--terracotta-dark) 100%);
      color: #fff;
      border: none;
      border-radius: 8px;
      padding: 14px 20px;
      font-size: 1rem;
      font-weight: 600;
      cursor: pointer;
      transition: transform 0.1s, opacity 0.2s;
      margin-top: 10px;
    }
    .btn:hover { opacity: 0.95; transform: translateY(-1px); }
    .btn:active { transform: translateY(0); }
    .links-box {
      margin-top: 24px;
      padding-top: 20px;
      border-top: 1px solid var(--border);
      display: flex;
      justify-content: space-between;
      gap: 12px;
      flex-wrap: wrap;
    }
    .link-btn {
      flex: 1;
      text-align: center;
      padding: 10px 14px;
      border-radius: 6px;
      background: rgba(255,255,255,0.05);
      border: 1px solid var(--border);
      color: var(--text);
      text-decoration: none;
      font-size: 0.85rem;
      font-weight: 600;
      transition: background 0.2s;
    }
    .link-btn:hover { background: rgba(255,255,255,0.1); }
    .tables-badge {
      display: inline-block;
      background: rgba(201, 169, 110, 0.2);
      color: var(--sand-gold);
      padding: 2px 8px;
      border-radius: 4px;
      margin: 2px;
      font-size: 0.8rem;
    }
  </style>
</head>
<body>

<div class="installer-card">
  <div class="header">
    <h1>Earthen Beauty by Nupur</h1>
    <p>MySQL Database Setup & Seed Utility</p>
  </div>

  <?php if ($message): ?>
    <div class="alert alert-<?php echo $messageType; ?>">
      <strong><?php echo ($messageType === 'success') ? '✅ Success!' : '❌ Error!'; ?></strong><br>
      <?php echo htmlspecialchars($message); ?>
      <?php if (!empty($installedTables)): ?>
        <div style="margin-top: 12px;">
          <strong>Verified Tables:</strong><br>
          <?php foreach ($installedTables as $t): ?>
            <span class="tables-badge"><?php echo htmlspecialchars($t); ?></span>
          <?php endforeach; ?>
        </div>
      <?php endif; ?>
    </div>
  <?php endif; ?>

  <form method="POST" action="">
    <input type="hidden" name="action" value="install">

    <div class="grid-2">
      <div class="form-group">
        <label>MySQL Host</label>
        <input type="text" name="db_host" value="<?php echo htmlspecialchars($dbHost); ?>" required>
      </div>
      <div class="form-group">
        <label>MySQL Port</label>
        <input type="text" name="db_port" value="<?php echo htmlspecialchars($dbPort); ?>" required>
      </div>
    </div>

    <div class="form-group">
      <label>Database Name</label>
      <input type="text" name="db_name" value="<?php echo htmlspecialchars($dbName); ?>" required>
    </div>

    <div class="grid-2">
      <div class="form-group">
        <label>Database User</label>
        <input type="text" name="db_user" value="<?php echo htmlspecialchars($dbUser); ?>" required>
      </div>
      <div class="form-group">
        <label>Database Password</label>
        <input type="password" name="db_pass" value="<?php echo htmlspecialchars($dbPass); ?>" placeholder="Leave blank if none">
      </div>
    </div>

    <button type="submit" class="btn">🚀 Initialize & Seed Database</button>
  </form>

  <div class="links-box">
    <a href="index.html" class="link-btn">🛍️ Open Storefront</a>
    <a href="admin.html" class="link-btn">👑 Admin Portal</a>
    <a href="api/health" class="link-btn">🩺 Test API Health</a>
  </div>
</div>

</body>
</html>
