<?php
/**
 * PDO 数据库连接单例
 * 提供 fetchAll / fetchOne / fetchColumn / insert / update / execute 等常用方法
 */
class Db
{
    private static ?Db $instance = null;
    private PDO $pdo;

    private function __construct(array $cfg)
    {
        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $cfg['host']    ?? '127.0.0.1',
            $cfg['port']    ?? 3306,
            $cfg['dbname'],
            $cfg['charset'] ?? 'utf8mb4'
        );
        $this->pdo = new PDO($dsn, $cfg['username'], $cfg['password'], [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ]);
    }

    public static function getInstance(?array $cfg = null): static
    {
        if (self::$instance === null) {
            if ($cfg === null) {
                throw new RuntimeException('Db 未初始化，请先传入配置');
            }
            self::$instance = new self($cfg);
        }
        return self::$instance;
    }

    public function getPdo(): PDO
    {
        return $this->pdo;
    }

    /** 返回所有行 */
    public function fetchAll(string $sql, array $bindings = []): array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchAll();
    }

    /** 返回第一行，不存在时返回 null */
    public function fetchOne(string $sql, array $bindings = []): ?array
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        $row = $stmt->fetch();
        return $row !== false ? $row : null;
    }

    /** 返回单列值 */
    public function fetchColumn(string $sql, array $bindings = []): mixed
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->fetchColumn();
    }

    /** 插入一行，返回新记录 ID */
    public function insert(string $table, array $data): string|int
    {
        $cols  = implode(', ', array_map(fn($k) => "`{$k}`", array_keys($data)));
        $marks = implode(', ', array_fill(0, count($data), '?'));
        $stmt  = $this->pdo->prepare("INSERT INTO `{$table}` ({$cols}) VALUES ({$marks})");
        $stmt->execute(array_values($data));
        return $this->pdo->lastInsertId();
    }

    /** 更新记录，返回受影响行数 */
    public function update(string $table, array $set, array $where): int
    {
        $setPart   = implode(', ', array_map(fn($k) => "`{$k}` = ?", array_keys($set)));
        $wherePart = implode(' AND ', array_map(fn($k) => "`{$k}` = ?", array_keys($where)));
        $stmt = $this->pdo->prepare("UPDATE `{$table}` SET {$setPart} WHERE {$wherePart}");
        $stmt->execute([...array_values($set), ...array_values($where)]);
        return $stmt->rowCount();
    }

    /** 执行任意 SQL，返回受影响行数 */
    public function execute(string $sql, array $bindings = []): int
    {
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($bindings);
        return $stmt->rowCount();
    }
}
