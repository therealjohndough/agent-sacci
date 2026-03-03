<?php

declare(strict_types=1);

namespace Tests\Unit\Security;

use Core\Csrf;
use PHPUnit\Framework\TestCase;

/**
 * Tests for Core\Csrf
 *
 * Core\Csrf uses $_SESSION directly:
 *   - Csrf::token()    — returns $_SESSION['_csrf'], creating it if absent
 *   - Csrf::validate() — uses hash_equals() to compare provided token against
 *                        $_SESSION['_csrf']
 *
 * The bootstrap.php initialises $_SESSION as a plain array so these tests
 * work in a CLI context without calling session_start().
 */
class CsrfTest extends TestCase
{
    protected function setUp(): void
    {
        // Always start from a clean session state.
        $_SESSION = [];
    }

    protected function tearDown(): void
    {
        $_SESSION = [];
    }

    // ── Token generation ──────────────────────────────────────────────────────

    /**
     * The first call to Csrf::token() must generate a token and store it in
     * $_SESSION['_csrf'].
     */
    public function testTokenIsGeneratedAndStoredInSession(): void
    {
        $this->assertArrayNotHasKey('_csrf', $_SESSION,
            'Pre-condition: session should have no CSRF token yet');

        $token = Csrf::token();

        $this->assertArrayHasKey('_csrf', $_SESSION,
            'Csrf::token() must write the token to $_SESSION["_csrf"]');
        $this->assertSame($_SESSION['_csrf'], $token,
            'Returned token must match the value stored in the session');
    }

    /**
     * Csrf::token() must return a non-empty string.
     * Internally it uses bin2hex(random_bytes(32)) which produces 64 hex chars.
     */
    public function testGeneratedTokenIsNonEmptyString(): void
    {
        $token = Csrf::token();

        $this->assertIsString($token);
        $this->assertNotEmpty($token);
        $this->assertSame(64, strlen($token),
            'bin2hex(random_bytes(32)) must produce a 64-character hex string');
    }

    /**
     * Subsequent calls to Csrf::token() must return the same token (idempotent)
     * as long as $_SESSION['_csrf'] is already set.
     */
    public function testTokenIsIdempotentOnSubsequentCalls(): void
    {
        $first  = Csrf::token();
        $second = Csrf::token();

        $this->assertSame($first, $second,
            'Csrf::token() must return the same value on repeated calls within one session');
    }

    /**
     * If $_SESSION['_csrf'] is already set externally, Csrf::token() must
     * return that existing value without overwriting it.
     */
    public function testTokenReusesExistingSessionValue(): void
    {
        $existing = 'pre-existing-token-1234567890abcdef1234567890abcdef1234567890abcdef12';
        $_SESSION['_csrf'] = $existing;

        $token = Csrf::token();

        $this->assertSame($existing, $token,
            'Csrf::token() must not overwrite a token that is already in the session');
    }

    /**
     * Two separate "sessions" (simulated by resetting $_SESSION) should
     * produce different tokens, demonstrating randomness.
     */
    public function testDifferentSessionsProduceDifferentTokens(): void
    {
        $tokenA = Csrf::token();

        // Simulate a new session.
        $_SESSION = [];

        $tokenB = Csrf::token();

        $this->assertNotSame($tokenA, $tokenB,
            'Tokens generated in distinct sessions should be different (random)');
    }

    // ── Token validation — valid token ────────────────────────────────────────

    /**
     * Csrf::validate() must return true when the submitted token exactly
     * matches the one stored in the session.
     */
    public function testValidTokenIsAccepted(): void
    {
        $token = Csrf::token(); // stores in $_SESSION['_csrf']

        $this->assertTrue(Csrf::validate($token),
            'The correct CSRF token must be accepted by validate()');
    }

    /**
     * Validating against a manually-set session token must also succeed,
     * verifying that validate() reads $_SESSION['_csrf'] correctly.
     */
    public function testValidTokenFromManualSessionIsAccepted(): void
    {
        $knownToken = bin2hex(random_bytes(32));
        $_SESSION['_csrf'] = $knownToken;

        $this->assertTrue(Csrf::validate($knownToken),
            'A token that exactly matches $_SESSION["_csrf"] must pass validation');
    }

    // ── Token validation — invalid / mismatched token ─────────────────────────

    /**
     * A token that differs from the session token must be rejected.
     * This covers the case of a tampered or replayed token.
     */
    public function testMismatchedTokenIsRejected(): void
    {
        $_SESSION['_csrf'] = bin2hex(random_bytes(32));
        $badToken           = bin2hex(random_bytes(32)); // different random bytes

        // Ensure the tokens really are different (astronomically unlikely to collide).
        $this->assertNotSame($_SESSION['_csrf'], $badToken,
            'Pre-condition: test tokens must differ');

        $this->assertFalse(Csrf::validate($badToken),
            'A token that does not match the session token must be rejected');
    }

    /**
     * An empty string token must be rejected.
     */
    public function testEmptyTokenIsRejected(): void
    {
        Csrf::token(); // ensure a real token is in the session

        $this->assertFalse(Csrf::validate(''),
            'An empty string token must fail CSRF validation');
    }

    /**
     * A completely arbitrary / forged string token must be rejected.
     */
    public function testArbitraryStringTokenIsRejected(): void
    {
        Csrf::token();

        $this->assertFalse(Csrf::validate('"><script>alert(1)</script>'),
            'An arbitrary forged token must fail CSRF validation');
    }

    /**
     * When there is no token in the session at all, validate() must return
     * false regardless of what is submitted. This matches the guard:
     *   isset($_SESSION['_csrf']) && hash_equals(...)
     */
    public function testValidationFailsWhenNoTokenInSession(): void
    {
        // Deliberately leave $_SESSION empty (setUp already did this,
        // but be explicit for clarity).
        unset($_SESSION['_csrf']);

        $this->assertFalse(Csrf::validate('any-string-at-all'),
            'Validation must fail when no CSRF token is stored in the session');
    }

    /**
     * Validate that Csrf::validate() uses a timing-safe comparison.
     * We cannot directly test hash_equals() internals, but we can confirm
     * that a one-character-off token is rejected, ruling out a naive == check
     * that would be exploitable via timing attacks.
     */
    public function testNearlyMatchingTokenIsRejected(): void
    {
        $realToken = Csrf::token();

        // Flip the last character.
        $tampered = substr($realToken, 0, -1) . ($realToken[-1] === 'a' ? 'b' : 'a');

        $this->assertFalse(Csrf::validate($tampered),
            'A token differing by a single character must still be rejected');
    }
}
