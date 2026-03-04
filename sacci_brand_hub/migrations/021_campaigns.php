<?php

/**
 * Migration 021: Campaigns table.
 *
 * A campaign groups related marketing requests and tracks progress
 * toward a goal (launch, promotion, event, etc.).
 *
 * Run via CLI:
 *   php migrations/021_campaigns.php
 */

use Core\Database;

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../core/Database.php';

Database::init();
$pdo = Database::getConnection();

// campaigns table
$pdo->exec("CREATE TABLE IF NOT EXISTS campaigns (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(200) NOT NULL,
    description TEXT NULL,
    status ENUM('draft','active','completed','archived') NOT NULL DEFAULT 'draft',
    start_date DATE NULL,
    end_date DATE NULL,
    owner_id INT UNSIGNED NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_campaigns_status (status),
    INDEX idx_campaigns_start (start_date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

// Link marketing request tickets to campaigns
// No FK on campaign_id or ticket_id — avoids dependency issues with other migrations
$pdo->exec("CREATE TABLE IF NOT EXISTS campaign_tickets (
    campaign_id INT UNSIGNED NOT NULL,
    ticket_id INT UNSIGNED NOT NULL,
    PRIMARY KEY (campaign_id, ticket_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4");

echo "  campaigns: table ensured.\n";
echo "  campaign_tickets: table ensured.\n";
echo "Migration 021 completed successfully.\n";
