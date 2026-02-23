<?php
// Installation wizard for Sacci Brand Hub
if (file_exists(__DIR__ . '/../.env')) {
    echo 'Application already installed.';
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dbHost = $_POST['db_host'] ?? '';
    $dbPort = $_POST['db_port'] ?? '3306';
    $dbName = $_POST['db_name'] ?? '';
    $dbUser = $_POST['db_user'] ?? '';
    $dbPass = $_POST['db_pass'] ?? '';
    $adminEmail = $_POST['admin_email'] ?? '';
    $adminPass = $_POST['admin_password'] ?? '';
    // Write .env file
    $env = "APP_NAME=\"Sacci Brand Hub\"\n";
    $env .= "DB_HOST={$dbHost}\nDB_PORT={$dbPort}\nDB_DATABASE={$dbName}\nDB_USERNAME={$dbUser}\nDB_PASSWORD={$dbPass}\n";
    file_put_contents(__DIR__ . '/../.env', $env);
    // Run migrations
    require_once __DIR__ . '/../migrations/001_create_tables.php';
    require_once __DIR__ . '/../core/Database.php';
    // Create admin user
    $hash = password_hash($adminPass, PASSWORD_DEFAULT);
    $pdo = Core\Database::getConnection();
    $pdo->exec("INSERT INTO users (email,password_hash) VALUES ('{$adminEmail}','{$hash}')");
    $userId = $pdo->lastInsertId();
    $roleId = $pdo->query("SELECT id FROM roles WHERE name='super_admin'")->fetchColumn();
    $pdo->exec("INSERT INTO user_roles (user_id, role_id) VALUES ({$userId}, {$roleId})");
    echo 'Installation complete. Please delete the install folder.';
    exit;
}
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sacci Brand Hub Installer</title>
    <style>
        body { font-family: Arial, sans-serif; background:#111; color:#eee; }
        form { max-width:400px; margin:50px auto; padding:20px; background:#222; border:1px solid #444; }
        label { display:block; margin-top:10px; }
        input { width:100%; padding:8px; margin-top:4px; }
        button { margin-top:20px; padding:10px; background:#31935f; color:#fff; border:none; cursor:pointer; }
    </style>
</head>
<body>
    <form method="post">
        <h2>Install Sacci Brand Hub</h2>
        <label>Database Host<input type="text" name="db_host" required></label>
        <label>Database Port<input type="text" name="db_port" value="3306" required></label>
        <label>Database Name<input type="text" name="db_name" required></label>
        <label>Database User<input type="text" name="db_user" required></label>
        <label>Database Password<input type="password" name="db_pass"></label>
        <label>Admin Email<input type="email" name="admin_email" required></label>
        <label>Admin Password<input type="password" name="admin_password" required></label>
        <button type="submit">Install</button>
    </form>
</body>
</html>