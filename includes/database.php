<?php
/**
 * AR Tourism Explorer - Database Connection
 * PDO-based database connection with error handling
 */

require_once __DIR__ . '/config.php';

/**
 * Get PDO database connection
 * @return PDO
 * @throws PDOException
 */
function getDB(): PDO {
    static $pdo = null;
    
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4 COLLATE utf8mb4_unicode_ci"
        ];
        
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            error_log('Database connection error: ' . $e->getMessage());
            die('Database connection failed. Please check your configuration.');
        }
    }
    
    return $pdo;
}

/**
 * Execute a prepared statement and return all results
 * @param string $sql
 * @param array $params
 * @return array
 */
function dbQuery(string $sql, array $params = []): array {
    try {
        $stmt = getDB()->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log('Query error: ' . $e->getMessage());
        return [];
    }
}

/**
 * Execute a prepared statement and return one result
 * @param string $sql
 * @param array $params
 * @return array|null
 */
function dbQueryOne(string $sql, array $params = []): ?array {
    try {
        $stmt = getDB()->prepare($sql);
        $stmt->execute($params);
        $result = $stmt->fetch();
        return $result ?: null;
    } catch (PDOException $e) {
        error_log('Query error: ' . $e->getMessage());
        return null;
    }
}

/**
 * Execute a prepared statement (INSERT, UPDATE, DELETE)
 * @param string $sql
 * @param array $params
 * @return bool|int
 */
function dbExecute(string $sql, array $params = []): bool|int {
    try {
        $stmt = getDB()->prepare($sql);
        $stmt->execute($params);
        
        // Return affected rows for INSERT/UPDATE/DELETE
        if (stripos($sql, 'INSERT') === 0) {
            return getDB()->lastInsertId();
        }
        
        return $stmt->rowCount();
    } catch (PDOException $e) {
        error_log('Execute error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Get the last inserted ID
 * @return string
 */
function dbLastInsertId(): string {
    return getDB()->lastInsertId();
}

/**
 * Begin a transaction
 * @return bool
 */
function dbBeginTransaction(): bool {
    try {
        return getDB()->beginTransaction();
    } catch (PDOException $e) {
        error_log('Transaction begin error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Commit a transaction
 * @return bool
 */
function dbCommit(): bool {
    try {
        return getDB()->commit();
    } catch (PDOException $e) {
        error_log('Transaction commit error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Rollback a transaction
 * @return bool
 */
function dbRollback(): bool {
    try {
        return getDB()->rollBack();
    } catch (PDOException $e) {
        error_log('Transaction rollback error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Escape string for safe output
 * @param string $string
 * @return string
 */
function e(string $string): string {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * Sanitize input
 * @param string $input
 * @return string
 */
function sanitize(string $input): string {
    return trim(htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8'));
}
?>
