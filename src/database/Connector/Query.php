<?php

namespace App\database\Connector;

use Exception;
use PDO;

class Query {
    protected PDO $pdo;
    protected ?string $table;
    protected static $static_pdo;
    protected static $static_table;
    protected static $static_query;
    protected static $static_conditions;

    public function __construct ($table_name = null) {
        $this->pdo = new PDO($_ENV['DB_CONNECTION'] . ":host" . $_ENV['HOSTNAME'] . ";dbname=" . $_ENV['DATABASE_NAME'], $_ENV['DATABASE_USERNAME'], $_ENV['DATABASE_PASSWORD'], [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ]);
        $this->table = $table_name;
    }

    /**
     *
     * @message static methods
     *
     */

    public static function setTable (string $table): string {
        return self::$static_table = $table;
    }

    /**
     * @throws Exception
     */
    public static function getTable (): string {
        if (isset(static::$table_name)) {
            return static::$table_name;
        }
        return self::$static_table ?: throw new Exception("Please set table name: Query::setTable('your_table_name)");
    }

    public static function dbname () {
        return $_ENV['DATABASE_NAME'];
    }

    public static function connect (): PDO {
        if (!self::$static_pdo) {
            self::$static_pdo = new PDO($_ENV['DB_CONNECTION'] . ":host" . $_ENV['HOSTNAME'] . ";dbname=" . $_ENV['DATABASE_NAME'], $_ENV['DATABASE_USERNAME'], $_ENV['DATABASE_PASSWORD'], [PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_OBJ]);
        }
        return self::$static_pdo;
    }

    public static function getQuery (): ?string {
        return self::$static_query;
    }

    public static function execute (array $params): bool|object {
        try {
            $pdo = self::$static_pdo->prepare(self::$static_query);
            $pdo->execute($params);
            return $pdo;
        } catch (Exception $exception) {
            echo "<p style='color: #e85151'>" . self::getQuery() . "</p>";
            echo $exception;
        }
        return false;
    }

    /**
     * @throws Exception
     */
    public static function create (array $values): object|bool {
        self::connect();
        $columns = implode(', ', array_keys($values));
        $placeholders = implode(', ', array_fill(0, count($values), '?'));
        $values = array_values($values);
        self::$static_query = "INSERT INTO " . self::dbname() . "." . self::getTable() . " ($columns) VALUES ($placeholders)";
        return self::execute($values);
    }

    /**
     * @throws Exception
     */
    public static function update (array $values): object|bool {
        self::connect();
        $columns = implode('=?,', array_keys($values)) . '=?';
        $merged = array_values($values);
        self::$static_query = "UPDATE " . self::dbname() . "." . self::getTable() . " SET $columns";
        if (self::$static_conditions) {
            $conditions = "";
            $conditions_array = [];
            foreach (self::$static_conditions as $column => $static_condition) {
                if (strlen($conditions)) {
                    $conditions .= $static_condition['logical_operator'] . " ";
                }
                $conditions .= $column . $static_condition['condition'] . "? ";
                $conditions_array[] = $static_condition['value'];
            }
            self::$static_query .= " WHERE " . $conditions;
            $merged = array_merge(array_values($values), $conditions_array);
        }
        return self::execute($merged);
    }

    /**
     * @param $condition_or_value mixed condition || value
     */
    public static function where (string $column, mixed $condition_or_value, mixed $value = null): static {
        self::connect();
        $condition_data = '=';
        if (func_num_args() == 3) {
            $condition_data = $condition_or_value;
            $condition_or_value = $value;
        }
        self::$static_conditions[$column] = ['condition' => $condition_data, 'value' => $condition_or_value, 'logical_operator' => 'AND'];
        return new static;
    }

    /**
     * @param $condition_or_value mixed condition || value
     */
    public static function orWhere (string $column, mixed $condition_or_value, mixed $value = null): static {
        self::connect();
        $condition_data = '=';
        if (func_num_args() == 3) {
            $condition_data = $condition_or_value;
            $condition_or_value = $value;
        }
        self::$static_conditions[$column] = ['condition' => $condition_data, 'value' => $condition_or_value, 'logical_operator' => 'OR'];
        return new static;
    }

    /**
     * @throws Exception
     */
    public static function first (): object|bool {
        $conditions = "";
        $conditions_array = [];
        foreach (self::$static_conditions as $column => $static_condition) {
            if (strlen($conditions)) {
                $conditions .= $static_condition['logical_operator'] . " ";
            }
            $conditions .= $column . $static_condition['condition'] . "? ";
            $conditions_array[] = $static_condition['value'];
        }
        self::$static_query = "SELECT * FROM " . self::dbname() . "." . self::getTable() . " WHERE " . $conditions;
        $stmt = self::execute($conditions_array);
        static::$static_query = null;
        static::$static_conditions = null;
        return $stmt ? $stmt->fetch() : false;
    }

    /**
     * @throws Exception
     */
    public static function get () {
        $conditions = "";
        $conditions_array = [];
        foreach (self::$static_conditions as $column => $static_condition) {
            if (strlen($conditions)) {
                $conditions .= $static_condition['logical_operator'] . " ";
            }
            $conditions .= $column . $static_condition['condition'] . "? ";
            $conditions_array[] = $static_condition['value'];
        }
        self::$static_query = "SELECT * FROM " . self::dbname() . "." . self::getTable() . " WHERE " . $conditions;
        $stmt = self::execute($conditions_array);
        static::$static_query = null;
        static::$static_conditions = null;
        return $stmt ? $stmt->fetchAll() : false;
    }

    /**
     * @throws Exception
     */
    public static function all (): bool|array {
        self::$static_query = "SELECT * FROM " . self::dbname() . "." . self::getTable();
        $stmt = self::execute([]);
        static::$static_query = null;
        static::$static_conditions = null;
        return $stmt ? $stmt->fetchAll() : false;
    }

    /**
     * @throws Exception
     */
    public static function count ($column) {
        if (static::$static_conditions) {
            $conditions = "";
            $conditions_array = [];
            foreach (static::$static_conditions as $column_name => $static_condition) {
                if (strlen($conditions)) {
                    $conditions .= $static_condition['logical_operator'] . " ";
                }
                $conditions .= $column_name . $static_condition['condition'] . "? ";
                $conditions_array[] = $static_condition['value'];
            }
            self::$static_query = "SELECT count($column) as $column FROM " . self::dbname() . "." . self::getTable() . " WHERE " . $conditions;
            $stmt = self::execute($conditions_array);
            return $stmt ? $stmt->fetch() : false;
        }
        self::$static_query = "SELECT count($column) as $column FROM " . self::dbname() . "." . self::getTable();
        $stmt = self::execute([]);
        return $stmt ? $stmt->fetch() : false;
    }

    public static function delete () {
        $conditions = "";
        $conditions_array = [];
        foreach (static::$static_conditions as $column_name => $static_condition) {
            if (strlen($conditions)) {
                $conditions .= $static_condition['logical_operator'] . " ";
            }
            $conditions .= $column_name . $static_condition['condition'] . "? ";
            $conditions_array[] = $static_condition['value'];
        }
        self::$static_query = "DELETE FROM " . self::dbname() . "." . self::getTable() . " WHERE " . $conditions;
        return self::execute($conditions_array);
    }
}