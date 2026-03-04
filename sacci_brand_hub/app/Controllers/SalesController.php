<?php

namespace App\Controllers;

use Core\Auth;
use Core\Csrf;
use Core\Database;
use PDOException;

class SalesController extends BaseController
{
    public function index(): void
    {
        $this->requireLogin();
        $pdo = Database::getConnection();

        try {
            // Totals per product over the last 90 days
            $summary = $pdo->query(
                "SELECT p.id AS product_id, p.sku, p.product_name, p.format,
                        SUM(se.units_sold) AS total_units,
                        SUM(se.revenue_cents) AS total_revenue_cents,
                        MAX(se.reporting_date) AS last_entry
                 FROM sales_entries se
                 JOIN products p ON p.id = se.product_id
                 WHERE se.reporting_date >= DATE_SUB(CURDATE(), INTERVAL 90 DAY)
                 GROUP BY p.id
                 ORDER BY total_units DESC"
            )->fetchAll(\PDO::FETCH_ASSOC);
        } catch (PDOException) {
            $summary = [];
        }

        try {
            // Recent individual entries
            $recent = $pdo->query(
                "SELECT se.id, se.reporting_date, se.units_sold, se.revenue_cents,
                        se.channel, se.notes,
                        p.sku, p.product_name,
                        u.name AS recorded_by_name
                 FROM sales_entries se
                 JOIN products p ON p.id = se.product_id
                 LEFT JOIN users u ON u.id = se.recorded_by
                 ORDER BY se.reporting_date DESC, se.id DESC
                 LIMIT 50"
            )->fetchAll(\PDO::FETCH_ASSOC);
        } catch (PDOException) {
            $recent = [];
        }

        $this->render('app/sales/index', [
            'summary' => $summary,
            'recent'  => $recent,
        ]);
    }

    public function entryForm(): void
    {
        $this->requireLogin();
        $products = $this->loadProducts();

        $this->render('app/sales/entry', [
            'csrf'     => $this->csrfToken(),
            'products' => $products,
        ]);
    }

    public function storeEntry(): void
    {
        $this->requireLogin();

        $token = (string) ($_POST['_csrf'] ?? '');
        if (!Csrf::validate($token)) {
            die('Invalid CSRF token');
        }

        $productId     = (int) ($_POST['product_id'] ?? 0);
        $reportingDate = trim($_POST['reporting_date'] ?? '');
        $unitsSold     = (int) ($_POST['units_sold'] ?? 0);
        $revenueRaw    = trim($_POST['revenue'] ?? '');
        $channel       = trim($_POST['channel'] ?? '') ?: null;
        $notes         = trim($_POST['notes'] ?? '') ?: null;

        // Validate
        if ($productId <= 0 || $reportingDate === '' || $unitsSold < 0) {
            $this->render('app/sales/entry', [
                'csrf'     => $this->csrfToken(),
                'products' => $this->loadProducts(),
                'error'    => 'Product, date, and units sold are required.',
            ]);
            return;
        }

        // Revenue: store as cents
        $revenueCents = null;
        if ($revenueRaw !== '') {
            $revenueCents = (int) round((float) str_replace(['$', ','], '', $revenueRaw) * 100);
        }

        $user = Auth::user();

        try {
            Database::getConnection()->prepare(
                'INSERT INTO sales_entries
                 (product_id, reporting_date, units_sold, revenue_cents, channel, notes, recorded_by)
                 VALUES (:product_id, :reporting_date, :units_sold, :revenue_cents, :channel, :notes, :recorded_by)'
            )->execute([
                'product_id'     => $productId,
                'reporting_date' => $reportingDate,
                'units_sold'     => $unitsSold,
                'revenue_cents'  => $revenueCents,
                'channel'        => $channel,
                'notes'          => $notes,
                'recorded_by'    => $user['id'],
            ]);
        } catch (\Exception $e) {
            $this->render('app/sales/entry', [
                'csrf'     => $this->csrfToken(),
                'products' => $this->loadProducts(),
                'error'    => 'Could not save entry: ' . htmlspecialchars($e->getMessage()),
            ]);
            return;
        }

        $this->redirect('/sales');
    }

    private function loadProducts(): array
    {
        try {
            return Database::getConnection()
                ->query("SELECT id, sku, product_name, format
                         FROM products
                         WHERE internal_status = 'active'
                         ORDER BY product_name")
                ->fetchAll(\PDO::FETCH_ASSOC);
        } catch (PDOException) {
            return [];
        }
    }
}
