<?php

/**
 * Add native product, compliance, and distribution core tables.
 *
 * This establishes the hub-and-spoke model:
 * strains -> batches -> COAs
 * strains -> products -> channel listings
 */

use Core\Database;

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

Database::init();
$pdo = Database::getConnection();

$sql = [
    "CREATE TABLE IF NOT EXISTS strains (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(150) NOT NULL,
        slug VARCHAR(150) NOT NULL UNIQUE,
        lineage VARCHAR(255) NULL,
        category VARCHAR(100) NULL,
        breeder VARCHAR(150) NULL,
        description TEXT NULL,
        status ENUM('draft','active','archived') NOT NULL DEFAULT 'draft',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_strains_status (status),
        INDEX idx_strains_name (name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS batches (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        strain_id INT UNSIGNED NOT NULL,
        batch_code VARCHAR(100) NOT NULL UNIQUE,
        harvest_date DATE NULL,
        production_status ENUM('planned','active','testing','approved','archived') NOT NULL DEFAULT 'planned',
        thc_percent DECIMAL(5,2) NULL,
        cbd_percent DECIMAL(5,2) NULL,
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (strain_id) REFERENCES strains(id) ON DELETE CASCADE,
        INDEX idx_batches_strain (strain_id),
        INDEX idx_batches_status (production_status),
        INDEX idx_batches_harvest (harvest_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS coas (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        batch_id INT UNSIGNED NOT NULL,
        lab_name VARCHAR(150) NULL,
        certificate_number VARCHAR(150) NULL,
        received_date DATE NULL,
        tested_date DATE NULL,
        status ENUM('pending','received','approved','expired','archived') NOT NULL DEFAULT 'pending',
        file_path VARCHAR(255) NULL,
        source_url VARCHAR(255) NULL,
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (batch_id) REFERENCES batches(id) ON DELETE CASCADE,
        INDEX idx_coas_batch (batch_id),
        INDEX idx_coas_status (status),
        INDEX idx_coas_received (received_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS products (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        strain_id INT UNSIGNED NOT NULL,
        current_batch_id INT UNSIGNED NULL,
        sku VARCHAR(100) NOT NULL UNIQUE,
        product_name VARCHAR(200) NOT NULL,
        format VARCHAR(100) NULL,
        weight_label VARCHAR(100) NULL,
        internal_status ENUM('draft','active','paused','archived') NOT NULL DEFAULT 'draft',
        description TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (strain_id) REFERENCES strains(id) ON DELETE CASCADE,
        FOREIGN KEY (current_batch_id) REFERENCES batches(id) ON DELETE SET NULL,
        INDEX idx_products_strain (strain_id),
        INDEX idx_products_batch (current_batch_id),
        INDEX idx_products_status (internal_status),
        INDEX idx_products_name (product_name)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS sales_channels (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL UNIQUE,
        slug VARCHAR(100) NOT NULL UNIQUE,
        active TINYINT(1) NOT NULL DEFAULT 1,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;",

    "CREATE TABLE IF NOT EXISTS product_channel_listings (
        id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
        product_id INT UNSIGNED NOT NULL,
        sales_channel_id INT UNSIGNED NOT NULL,
        external_id VARCHAR(150) NULL,
        listing_status ENUM('draft','live','paused','archived') NOT NULL DEFAULT 'draft',
        price_cents INT UNSIGNED NULL,
        published_at DATETIME NULL,
        last_synced_at DATETIME NULL,
        notes TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE,
        FOREIGN KEY (sales_channel_id) REFERENCES sales_channels(id) ON DELETE CASCADE,
        UNIQUE KEY uniq_product_channel (product_id, sales_channel_id),
        INDEX idx_product_channel_status (listing_status),
        INDEX idx_product_channel_published (published_at)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;"
];

foreach ($sql as $query) {
    $pdo->exec($query);
}

echo "Migration 014 completed successfully.";
