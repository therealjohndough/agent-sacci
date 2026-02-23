<?php

use Core\Database;

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';
Database::init();
$pdo = Database::getConnection();

// Insert roles
$roles = [
    ['name' => 'super_admin', 'description' => 'Super Administrator'],
    ['name' => 'admin', 'description' => 'Marketing Lead'],
    ['name' => 'staff', 'description' => 'Marketing Staff'],
    ['name' => 'retailer_manager', 'description' => 'Retailer Manager'],
    ['name' => 'retailer_user', 'description' => 'Retailer User'],
];
$stmt = $pdo->prepare('INSERT INTO roles (name, description) VALUES (:name, :description)');
foreach ($roles as $role) {
    $stmt->execute($role);
}

// Insert permissions
$permissions = [
    ['name' => 'user.manage', 'description' => 'Manage users'],
    ['name' => 'content.manage', 'description' => 'Manage content blocks'],
    ['name' => 'tickets.manage', 'description' => 'Manage tickets'],
    ['name' => 'assets.manage', 'description' => 'Manage assets'],
    ['name' => 'organizations.manage', 'description' => 'Manage organizations'],
];
$stmt = $pdo->prepare('INSERT INTO permissions (name, description) VALUES (:name, :description)');
foreach ($permissions as $perm) {
    $stmt->execute($perm);
}

// Assign permissions to roles
function assignPermissions(PDO $pdo, string $roleName, array $permNames): void {
    $roleId = $pdo->query("SELECT id FROM roles WHERE name='{$roleName}'")->fetchColumn();
    foreach ($permNames as $p) {
        $permId = $pdo->query("SELECT id FROM permissions WHERE name='{$p}'")->fetchColumn();
        $pdo->exec("INSERT INTO role_permissions (role_id, permission_id) VALUES ({$roleId}, {$permId})");
    }
}

assignPermissions($pdo, 'super_admin', array_column($permissions, 'name'));
assignPermissions($pdo, 'admin', ['tickets.manage','assets.manage','content.manage','organizations.manage']);
assignPermissions($pdo, 'staff', ['tickets.manage','assets.manage']);
assignPermissions($pdo, 'retailer_manager', []);
assignPermissions($pdo, 'retailer_user', []);

// Insert organizations
$pdo->exec("INSERT INTO organizations (name, contact_name, contact_email) VALUES
('Dispensary One','Alice Manager','alice@dispone.com'),
('Dispensary Two','Bob Manager','bob@disptwo.com')");

// Insert super admin user
$passwordHash = password_hash('password', PASSWORD_DEFAULT);
$pdo->exec("INSERT INTO users (name,email,password_hash) VALUES ('Super Admin','admin@houseofsacci.com','{$passwordHash}')");
$superId = $pdo->lastInsertId();
$roleId = $pdo->query("SELECT id FROM roles WHERE name='super_admin'")->fetchColumn();
$pdo->exec("INSERT INTO user_roles (user_id, role_id) VALUES ({$superId}, {$roleId})");

// Insert sample staff user
$hash = password_hash('password', PASSWORD_DEFAULT);
$pdo->exec("INSERT INTO users (name,email,password_hash) VALUES ('Staff User','staff@houseofsacci.com','{$hash}')");
$staffId = $pdo->lastInsertId();
$staffRoleId = $pdo->query("SELECT id FROM roles WHERE name='staff'")->fetchColumn();
$pdo->exec("INSERT INTO user_roles (user_id, role_id) VALUES ({$staffId}, {$staffRoleId})");

// Insert retailer manager user
$hash = password_hash('password', PASSWORD_DEFAULT);
$pdo->exec("INSERT INTO users (name,email,password_hash,organization_id) VALUES ('Retail Manager','manager@dispone.com','{$hash}',1)");
$rmId = $pdo->lastInsertId();
$roleId = $pdo->query("SELECT id FROM roles WHERE name='retailer_manager'")->fetchColumn();
$pdo->exec("INSERT INTO user_roles (user_id, role_id) VALUES ({$rmId}, {$roleId})");

// Insert sample assets
$pdo->exec("INSERT INTO assets (name, description, category, file_type, filepath, visibility) VALUES
('Sacci Logo Gold','Primary gold logo','Logo','svg','01_LOGOS/Sacci_Logo_Primary_Gold.svg','public'),
('Airmail Flower Hero','Hero shot of Airmail Flower 3.5g','Flower','jpg','02_FLOWER_ASSETS/Airmail/Sacci_Airmail_Flower_3.5g_Hero.jpg','public'),
('Trop Cherry Flower Macro','Macro shot of Trop Cherry Flower','Flower','jpg','02_FLOWER_ASSETS/TropCherry/Sacci_TropCherry_Flower_3.5g_Macro_01.jpg','public')");

// Insert sample tickets
$pdo->exec("INSERT INTO tickets (title, description, request_type, priority, due_date, requester_id, assigned_to, status) VALUES
('Create new Airmail promo graphic','Need a square graphic for IG','Design','High','2026-03-01',{$superId},{$staffId},'New'),
('Update COA for Trop Cherry','Upload latest COA PDF','Other','Medium','2026-02-25',{$staffId},{$superId},'Triaged')");

// Insert content blocks seed (example: design tokens)
$content = addslashes("<h2>Design Tokens</h2><p>Refer to the brand system for color values.</p>");
$pdo->exec("INSERT INTO content_blocks (slug, title, content) VALUES ('design-tokens','Design Tokens','{$content}')");

echo "Seed data inserted.";