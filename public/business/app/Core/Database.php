<?php
/**
 * ONYX Accounting System - Database Class
 * Handles database connections and queries
 */

namespace App\Core;

class Database
{
    private static $instance;
    private $connection;
    private $config;
    private $lastQuery;

    /**
     * Get singleton instance
     */
    public static function getInstance()
    {
        return self::$instance;
    }

    /**
     * Set connection
     */
    public static function setConnection($connection)
    {
        self::$instance = $connection;
    }

    /**
     * Constructor
     */
    public function __construct($config = [])
    {
        $this->config = $config;
        $this->connect();
    }

    /**
     * Establish database connection
     */
    private function connect()
    {
        try {
            $dsn = sprintf(
                'mysql:host=%s;port=%s;dbname=%s;charset=%s',
                $this->config['host'] ?? 'localhost',
                $this->config['port'] ?? 3306,
                $this->config['database'] ?? 'accounting',
                $this->config['charset'] ?? 'utf8mb4'
            );

            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
            ];

            $this->connection = new \PDO(
                $dsn,
                $this->config['username'] ?? 'root',
                $this->config['password'] ?? '',
                $options
            );
        } catch (\PDOException $e) {
            error_log('Database Connection Error: ' . $e->getMessage());
            throw new \Exception('Database connection failed');
        }
    }

    /**
     * Get PDO connection
     */
    public function getConnection()
    {
        return $this->connection;
    }

    /**
     * Execute query
     */
    public function query($sql, $bindings = [])
    {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($bindings);
            $this->lastQuery = $sql;
            return $stmt;
        } catch (\PDOException $e) {
            error_log('Query Error: ' . $e->getMessage() . ' SQL: ' . $sql);
            throw new \Exception('Query execution failed');
        }
    }

    /**
     * Fetch single row
     */
    public function fetch($sql, $bindings = [])
    {
        $stmt = $this->query($sql, $bindings);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    /**
     * Fetch all rows
     */
    public function fetchAll($sql, $bindings = [])
    {
        $stmt = $this->query($sql, $bindings);
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }

    /**
     * Insert record
     */
    public function insert($table, $data)
    {
        $columns = array_keys($data);
        $values = array_values($data);
        $placeholders = array_fill(0, count($columns), '?');

        $sql = "INSERT INTO {$table} (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $placeholders) . ")";
        
        $this->query($sql, $values);
        return $this->connection->lastInsertId();
    }

    /**
     * Update record
     */
    public function update($table, $data, $where = [])
    {
        $set = [];
        $values = [];

        foreach ($data as $column => $value) {
            $set[] = "{$column} = ?";
            $values[] = $value;
        }

        $whereConditions = [];
        foreach ($where as $column => $value) {
            $whereConditions[] = "{$column} = ?";
            $values[] = $value;
        }

        $sql = "UPDATE {$table} SET " . implode(', ', $set);
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $whereConditions);
        }

        $stmt = $this->query($sql, $values);
        return $stmt->rowCount();
    }

    /**
     * Delete record
     */
    public function delete($table, $where = [])
    {
        $whereConditions = [];
        $values = [];

        foreach ($where as $column => $value) {
            $whereConditions[] = "{$column} = ?";
            $values[] = $value;
        }

        $sql = "DELETE FROM {$table}";
        if (!empty($where)) {
            $sql .= " WHERE " . implode(' AND ', $whereConditions);
        }

        $stmt = $this->query($sql, $values);
        return $stmt->rowCount();
    }

    /**
     * Begin transaction
     */
    public function beginTransaction()
    {
        return $this->connection->beginTransaction();
    }

    /**
     * Commit transaction
     */
    public function commit()
    {
        return $this->connection->commit();
    }

    /**
     * Rollback transaction
     */
    public function rollback()
    {
        return $this->connection->rollBack();
    }

    /**
     * Get last query
     */
    public function lastQuery()
    {
        return $this->lastQuery;
    }
}
