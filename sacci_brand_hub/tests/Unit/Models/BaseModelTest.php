<?php

declare(strict_types=1);

namespace Tests\Unit\Models;

use App\Models\BaseModel;
use Core\Database;
use PDO;
use PDOStatement;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

/**
 * Tests for App\Models\BaseModel
 *
 * BaseModel public API (from app/Models/BaseModel.php):
 *   - static find(int $id): ?array
 *   - static findBy(array $conditions): array
 *   - static create(array $data): int
 *   - static update(int $id, array $data): void
 *
 * All methods obtain a PDO connection through the static singleton
 * Core\Database::getConnection(). To avoid a real DB connection we inject a
 * mock PDO instance directly into Database::$connection via Reflection.
 *
 * We test through a concrete subclass because BaseModel is abstract.
 */
class BaseModelTest extends TestCase
{
    // ── Concrete stub subclass ────────────────────────────────────────────────

    /**
     * Minimal concrete subclass of BaseModel for testing.
     * Mirrors the real pattern: declare protected static $table.
     */
    // Declared outside the test method to avoid eval/anonymous class issues.

    // ── PDO injection helpers ─────────────────────────────────────────────────

    /**
     * Inject a mock PDO into the Database singleton so that
     * BaseModel::db() returns our mock instead of attempting a real connection.
     */
    private function injectMockPdo(PDO&MockObject $mockPdo): void
    {
        $ref = new ReflectionClass(Database::class);
        $prop = $ref->getProperty('connection');
        $prop->setAccessible(true);
        $prop->setValue(null, $mockPdo);
    }

    /**
     * Reset the Database singleton connection to null after each test so
     * subsequent tests start with a clean slate.
     */
    protected function tearDown(): void
    {
        $ref = new ReflectionClass(Database::class);
        $prop = $ref->getProperty('connection');
        $prop->setAccessible(true);
        $prop->setValue(null, null);
    }

    /**
     * Create a PDO mock that will NOT attempt a real MySQL connection.
     * We mock PDO itself (not an interface) which requires `getMockBuilder`
     * with `disableOriginalConstructor()`.
     */
    private function makeMockPdo(): PDO&MockObject
    {
        return $this->getMockBuilder(PDO::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['prepare', 'lastInsertId'])
            ->getMock();
    }

    /**
     * Create a PDOStatement mock that satisfies the execute / fetch / fetchAll
     * call chain used inside BaseModel methods.
     */
    private function makeMockStmt(): PDOStatement&MockObject
    {
        return $this->getMockBuilder(PDOStatement::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['execute', 'fetch', 'fetchAll'])
            ->getMock();
    }

    // ── find() — record exists ────────────────────────────────────────────────

    /**
     * find() must return an associative array when the record exists in the DB.
     *
     * SQL executed: SELECT * FROM stub_items WHERE id = :id LIMIT 1
     * PDO::fetch() returns the row as an assoc array.
     */
    public function testFindReturnsCorrectRecordWhenItExists(): void
    {
        $expectedRow = ['id' => 7, 'name' => 'Test Item', 'active' => 1];

        $mockStmt = $this->makeMockStmt();
        $mockStmt->expects($this->once())
            ->method('execute')
            ->with(['id' => 7]);
        $mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn($expectedRow);

        $mockPdo = $this->makeMockPdo();
        $mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('WHERE id = :id'))
            ->willReturn($mockStmt);

        $this->injectMockPdo($mockPdo);

        $result = StubItemModel::find(7);

        $this->assertSame($expectedRow, $result,
            'find() must return the fetched row as an associative array');
    }

    /**
     * find() must include the correct table name in the SQL query.
     */
    public function testFindUsesCorrectTableName(): void
    {
        $mockStmt = $this->makeMockStmt();
        $mockStmt->method('execute');
        $mockStmt->method('fetch')->willReturn(['id' => 1, 'name' => 'row']);

        $mockPdo = $this->makeMockPdo();
        $mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('stub_items'))
            ->willReturn($mockStmt);

        $this->injectMockPdo($mockPdo);

        StubItemModel::find(1);
        // Assertion is on the `prepare` expectation above.
        $this->addToAssertionCount(1);
    }

    // ── find() — record does not exist ────────────────────────────────────────

    /**
     * find() must return null when the DB returns false (no row found).
     * PDOStatement::fetch() returns false for no result with FETCH_ASSOC.
     */
    public function testFindReturnsNullForNonExistentId(): void
    {
        $mockStmt = $this->makeMockStmt();
        $mockStmt->expects($this->once())
            ->method('execute')
            ->with(['id' => 9999]);
        $mockStmt->expects($this->once())
            ->method('fetch')
            ->willReturn(false); // PDO returns false when no row is found.

        $mockPdo = $this->makeMockPdo();
        $mockPdo->method('prepare')->willReturn($mockStmt);

        $this->injectMockPdo($mockPdo);

        $result = StubItemModel::find(9999);

        $this->assertNull($result,
            'find() must return null when PDO::fetch() returns false');
    }

    /**
     * find() must return null (not false, not an empty array) so that callers
     * can safely use `if ($record === null)` or `if (!$record)`.
     */
    public function testFindReturnTypeIsNullNotFalseWhenMissing(): void
    {
        $mockStmt = $this->makeMockStmt();
        $mockStmt->method('execute');
        $mockStmt->method('fetch')->willReturn(false);

        $mockPdo = $this->makeMockPdo();
        $mockPdo->method('prepare')->willReturn($mockStmt);

        $this->injectMockPdo($mockPdo);

        $result = StubItemModel::find(0);

        $this->assertNull($result);
        $this->assertNotFalse($result, 'find() must coerce false to null');
    }

    // ── findBy() ──────────────────────────────────────────────────────────────

    /**
     * findBy() must pass correct WHERE clauses and return all matching rows.
     */
    public function testFindByReturnsMatchingRows(): void
    {
        $rows = [
            ['id' => 1, 'name' => 'Alpha', 'active' => 1],
            ['id' => 3, 'name' => 'Beta',  'active' => 1],
        ];

        $mockStmt = $this->makeMockStmt();
        $mockStmt->expects($this->once())
            ->method('execute')
            ->with(['active' => 1]);
        $mockStmt->expects($this->once())
            ->method('fetchAll')
            ->willReturn($rows);

        $mockPdo = $this->makeMockPdo();
        $mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->logicalAnd(
                $this->stringContains('stub_items'),
                $this->stringContains('active = :active')
            ))
            ->willReturn($mockStmt);

        $this->injectMockPdo($mockPdo);

        $result = StubItemModel::findBy(['active' => 1]);

        $this->assertSame($rows, $result,
            'findBy() must return all rows returned by PDO::fetchAll()');
    }

    /**
     * findBy() with multiple conditions must AND them all in the SQL.
     */
    public function testFindByWithMultipleConditionsBuildsCorrectSql(): void
    {
        $mockStmt = $this->makeMockStmt();
        $mockStmt->method('execute');
        $mockStmt->method('fetchAll')->willReturn([]);

        $mockPdo = $this->makeMockPdo();
        $mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->logicalAnd(
                $this->stringContains('name = :name'),
                $this->stringContains('active = :active')
            ))
            ->willReturn($mockStmt);

        $this->injectMockPdo($mockPdo);

        StubItemModel::findBy(['name' => 'foo', 'active' => 0]);
        $this->addToAssertionCount(1); // covered by prepare() expectation
    }

    /**
     * findBy() must return an empty array when no rows match.
     */
    public function testFindByReturnsEmptyArrayWhenNoRowsMatch(): void
    {
        $mockStmt = $this->makeMockStmt();
        $mockStmt->method('execute');
        $mockStmt->method('fetchAll')->willReturn([]);

        $mockPdo = $this->makeMockPdo();
        $mockPdo->method('prepare')->willReturn($mockStmt);

        $this->injectMockPdo($mockPdo);

        $result = StubItemModel::findBy(['name' => 'nonexistent']);

        $this->assertSame([], $result,
            'findBy() must return an empty array when no rows are found');
    }

    // ── create() ─────────────────────────────────────────────────────────────

    /**
     * create() must build an INSERT statement with the correct table and columns,
     * and return the new row's ID from lastInsertId().
     */
    public function testCreateReturnsNewRowId(): void
    {
        $data = ['name' => 'New Item', 'active' => 1];

        $mockStmt = $this->makeMockStmt();
        $mockStmt->expects($this->once())
            ->method('execute')
            ->with($data);

        $mockPdo = $this->makeMockPdo();
        $mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->logicalAnd(
                $this->stringContains('INSERT INTO stub_items'),
                $this->stringContains(':name'),
                $this->stringContains(':active')
            ))
            ->willReturn($mockStmt);

        // lastInsertId() is called twice (once inside create, once outside),
        // but create() only calls it once — allow any number of calls.
        $mockPdo->expects($this->atLeastOnce())
            ->method('lastInsertId')
            ->willReturn('15');

        $this->injectMockPdo($mockPdo);

        $newId = StubItemModel::create($data);

        $this->assertSame(15, $newId,
            'create() must cast lastInsertId() to int and return it');
    }

    // ── update() ─────────────────────────────────────────────────────────────

    /**
     * update() must build an UPDATE statement that targets the given ID.
     */
    public function testUpdateExecutesCorrectSql(): void
    {
        $mockStmt = $this->makeMockStmt();
        $mockStmt->expects($this->once())
            ->method('execute')
            ->with($this->arrayHasKey('id')); // params must include :id

        $mockPdo = $this->makeMockPdo();
        $mockPdo->expects($this->once())
            ->method('prepare')
            ->with($this->logicalAnd(
                $this->stringContains('UPDATE stub_items'),
                $this->stringContains('WHERE id = :id'),
                $this->stringContains('name = :name')
            ))
            ->willReturn($mockStmt);

        $this->injectMockPdo($mockPdo);

        StubItemModel::update(42, ['name' => 'Updated Name']);
        $this->addToAssertionCount(1); // assertion on prepare() expectation
    }
}

/**
 * Concrete subclass of BaseModel used by all tests in this file.
 * Declared at file scope (outside the test class) to avoid eval().
 */
class StubItemModel extends BaseModel
{
    protected static string $table = 'stub_items';
}
