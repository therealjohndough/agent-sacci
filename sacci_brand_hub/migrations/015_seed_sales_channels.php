<?php

/**
 * Seed baseline distribution partners.
 */

use Core\Database;

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

Database::init();
$pdo = Database::getConnection();

$channels = [
    [
        'name' => 'LeafLink',
        'slug' => 'leaflink',
        'active' => 1,
    ],
    [
        'name' => 'Sitru',
        'slug' => 'sitru',
        'active' => 1,
    ],
];

$stmt = $pdo->prepare(
    'INSERT INTO sales_channels (name, slug, active)
     VALUES (:name, :slug, :active)
     ON DUPLICATE KEY UPDATE
        name = VALUES(name),
        active = VALUES(active)'
);

foreach ($channels as $channel) {
    $stmt->execute($channel);
}

echo "Migration 015 completed successfully.";
