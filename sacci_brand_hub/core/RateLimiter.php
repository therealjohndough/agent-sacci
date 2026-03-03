<?php

namespace Core;

use PDO;

/**
 * Database-backed rate limiter.
 *
 * Tracks attempts per (IP address, action) pair using the rate_limit_log
 * table. All timestamps are stored as UTC via MySQL's NOW() function so
 * the window comparison is server-side and immune to PHP timezone config.
 *
 * Usage example (max 5 login attempts per 15 minutes):
 *
 *   $limiter = new RateLimiter(Database::getConnection());
 *   if (!$limiter->check($ip, 'login', 5, 900)) {
 *       http_response_code(429);
 *       echo 'Too many attempts. Please wait before trying again.';
 *       return;
 *   }
 *   $limiter->record($ip, 'login');
 */
class RateLimiter
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Check whether the given IP is under the rate limit for an action.
     *
     * Returns true when the request is allowed (attempts within the limit),
     * or false when the limit has been exceeded and the request should be
     * blocked.
     *
     * @param string $ip            IPv4 or IPv6 address of the client.
     * @param string $action        Logical action name (e.g. 'login').
     * @param int    $maxAttempts   Maximum number of attempts permitted.
     * @param int    $windowSeconds Rolling window length in seconds.
     */
    public function check(string $ip, string $action, int $maxAttempts, int $windowSeconds): bool
    {
        $stmt = $this->pdo->prepare(
            'SELECT COUNT(*) AS attempt_count
               FROM rate_limit_log
              WHERE ip_address  = :ip
                AND action      = :action
                AND attempted_at >= NOW() - INTERVAL :window SECOND'
        );
        $stmt->bindValue(':ip',     $ip,            PDO::PARAM_STR);
        $stmt->bindValue(':action', $action,        PDO::PARAM_STR);
        $stmt->bindValue(':window', $windowSeconds, PDO::PARAM_INT);
        $stmt->execute();
        $row = $stmt->fetch();

        return ((int) ($row['attempt_count'] ?? 0)) < $maxAttempts;
    }

    /**
     * Record a single attempt for the given IP and action.
     *
     * Call this after check() returns true, immediately before processing
     * the request, so that the attempt is counted even if the request
     * ultimately fails.
     *
     * @param string $ip     IPv4 or IPv6 address of the client.
     * @param string $action Logical action name (e.g. 'login').
     */
    public function record(string $ip, string $action): void
    {
        $stmt = $this->pdo->prepare(
            'INSERT INTO rate_limit_log (ip_address, action, attempted_at)
             VALUES (:ip, :action, NOW())'
        );
        $stmt->bindValue(':ip',     $ip,    PDO::PARAM_STR);
        $stmt->bindValue(':action', $action, PDO::PARAM_STR);
        $stmt->execute();
    }

    /**
     * Reset all recorded attempts for the given IP and action.
     *
     * Call this after a successful login so a legitimate user is not
     * penalised by earlier failed attempts in the same window.
     *
     * @param string $ip     IPv4 or IPv6 address of the client.
     * @param string $action Logical action name (e.g. 'login').
     */
    public function reset(string $ip, string $action): void
    {
        $stmt = $this->pdo->prepare(
            'DELETE FROM rate_limit_log
              WHERE ip_address = :ip
                AND action     = :action'
        );
        $stmt->bindValue(':ip',     $ip,    PDO::PARAM_STR);
        $stmt->bindValue(':action', $action, PDO::PARAM_STR);
        $stmt->execute();
    }
}
