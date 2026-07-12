<?php
require_once __DIR__ . '/../config.php';

class DB {
    private $db;
    private $isPDO;

    public function __construct() {
        $this->db = getDB();
        $this->isPDO = $this->db instanceof PDO;
    }

    public function query(string $sql): array {
        if ($this->isPDO) {
            $stmt = $this->db->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
        $result = $this->db->query($sql);
        $rows = [];
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function querySingle(string $sql): mixed {
        if ($this->isPDO) {
            $stmt = $this->db->query($sql);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            return $row ? array_values($row)[0] : false;
        }
        return $this->db->querySingle($sql);
    }

    public function prepare(string $sql): DBStatement {
        return new DBStatement($this->db->prepare($sql), $this->isPDO);
    }

    public function lastInsertRowID(): int {
        if ($this->isPDO) return (int)$this->db->lastInsertId();
        return $this->db->lastInsertRowID();
    }

    public function exec(string $sql): bool {
        if ($this->isPDO) return $this->db->exec($sql) !== false;
        return $this->db->exec($sql) !== false;
    }
}

class DBStatement {
    private $stmt;
    private $isPDO;
    private $result = null;

    public function __construct($stmt, bool $isPDO) {
        $this->stmt = $stmt;
        $this->isPDO = $isPDO;
    }

    public function bindValue(string $param, mixed $value, int $type = null): bool {
        if ($this->isPDO) {
            $pdoType = PDO::PARAM_STR;
            if ($type === SQLITE3_INTEGER) $pdoType = PDO::PARAM_INT;
            return $this->stmt->bindValue($param, $value, $pdoType);
        }
        return $this->stmt->bindValue($param, $value, $type ?? SQLITE3_TEXT);
    }

    public function execute(): bool|SQLite3Result {
        if ($this->isPDO) {
            return $this->stmt->execute();
        }
        $this->result = $this->stmt->execute();
        return $this->result !== false;
    }

    public function fetchArray(int $mode = SQLITE3_ASSOC): mixed {
        if ($this->isPDO) {
            $fetchMode = PDO::FETCH_ASSOC;
            if ($mode === SQLITE3_NUM) $fetchMode = PDO::FETCH_NUM;
            return $this->stmt->fetch($fetchMode);
        }
        if ($this->result === null) {
            $this->result = $this->stmt->execute();
        }
        return $this->result->fetchArray($mode);
    }

    public function fetchAll(int $mode = SQLITE3_ASSOC): array {
        if ($this->isPDO) {
            $fetchMode = PDO::FETCH_ASSOC;
            if ($mode === SQLITE3_NUM) $fetchMode = PDO::FETCH_NUM;
            return $this->stmt->fetchAll($fetchMode);
        }
        if ($this->result === null) {
            $this->result = $this->stmt->execute();
        }
        $rows = [];
        while ($row = $this->result->fetchArray($mode)) {
            $rows[] = $row;
        }
        return $rows;
    }
}