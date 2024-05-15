<?php

namespace Core\database;

class Connector {
    protected  $HOSTNAME = NULL;
    protected  $DATABASE_USERNAME = NULL;
    protected  $DATABASE_PASSWORD = NULL;
    protected  $DATABASE_NAME = NULL;
    protected  $conn = NULL;
    protected  $sqlQuery = NULL;
    protected  $dataSet = NULL;

    protected $params = [];

    public  function connect () {
        $this->HOSTNAME = env("HOSTNAME");
        $this->DATABASE_USERNAME = env("DATABASE_USERNAME");
        $this->DATABASE_PASSWORD = env("DATABASE_PASSWORD");
        $this->DATABASE_NAME = env("DATABASE_NAME");

        if (!$this->conn) {
            $this->conn = mysqli_connect(
                $this->HOSTNAME,
                $this->DATABASE_USERNAME,
                $this->DATABASE_PASSWORD,
                $this->DATABASE_NAME
            );
        }
        if (!$this->conn) {
            die("Database connection failed: " . mysqli_connect_error());
        }
    }

    public  function getConnection() {
        if (!$this->conn) {
            $this->connect();
        }
        return $this->conn;
    }

    // Ma'lumot bazasi bilan aloqada vujudga kelishi mumkin bo'lgan xatolikdan qochib qutulish
    public  function escapeString($value)
    {
        $this->connect();
        return mysqli_real_escape_string($this->conn, $value);
    }

    // Ma'lumotlar bazasidan barcha ma'lumotlarni olish
    public  function selectAll($tablename)
    {
        $this->connect();
        $this->sqlQuery = 'SELECT * FROM ' . $this->DATABASE_NAME . '.' . $tablename;
        $this->dataSet = mysqli_query($this->conn, $this->sqlQuery);
        return $this;
    }

    // Ma'lumotlar bazasidan biror shartga ko'ra ma'lumot olish
    public  function selectWhere($tablename, $condition, $extra="")
    {
        $this->connect();
        $this->sqlQuery = 'SELECT * FROM ' . $tablename . ' WHERE ';
        if (gettype($condition) == "array") {
            foreach ($condition as $keys => $values) {
                foreach ($values as $key => $value) {
                    if ($key !== 'cn') {
                        $this->sqlQuery .= $this->escapeString($key) . " " . $values['cn'];
                        $this->sqlQuery .= $values[$key]!==null ? "'" . $this->escapeString($values[$key]) . "'" : "NULL";
                        $this->sqlQuery .= " and ";
                    }
                }
            }
            $this->sqlQuery = substr($this->sqlQuery, 0,strlen($this->sqlQuery)-4);
        }else{
            $this->sqlQuery .= $condition;
        }
        $this->dataSet = mysqli_query($this->conn, $this->sqlQuery);
        return $this;
    }

    // Ma'lumotlar bazasiga ma'lumot kiritish
    public  function insertInto($tablename, $data=[])
    {
        $this->connect();
        $this->sqlQuery = 'INSERT INTO ' . $tablename;
        $columns = '(';
        $values = "(";
        foreach ($data as $key => $value) {
            $columns .= $this->escapeString($key) . ',';
            $values .= $value!==null ? "'" . $this->escapeString($value) . "'," : "NULL";
        }
        $columns = substr($columns, 0, strlen($columns)-1);
        $values = substr($values, 0, strlen($values)-1);
        $columns .= ')';
        $values .= ')';
        $this->sqlQuery .= $columns . ' VALUES ' . $values;
        if(mysqli_query($this->conn, $this->sqlQuery)) {
            return $this->selectWhere($tablename, [['id'=>$this->conn->insert_id,'cn'=>'=']])->fetch();
        }
        return false;
    }

    public  function create (array $data) {
        return $this->insertInto($data);
    }

    // Ma'lumotlar bazasidan biror ma'lumotni shartga ko'ra o'chirish
    public  function deleteWhere($tablename, $condition=[],$extra="")
    {
        $this->connect();
        $this->sqlQuery = 'DELETE FROM ' . $tablename . ' WHERE ';
        foreach ($condition as $values) {
            foreach ($values as $key => $value) {
                if ($key != 'cn') {
                    $this->sqlQuery .= $this->escapeString($key) . " " . $values['cn'];
                    $this->sqlQuery .= $values[$key]!==null ? "'" . $this->escapeString($values[$key]) . "'" : "NULL";
                    $this->sqlQuery .= ' and ';
                }
            }
        }
        $this->sqlQuery = substr($this->sqlQuery, 0, strlen($this->sqlQuery)-4);
        $this->sqlQuery .= $extra;
        $this->dataSet = mysqli_query($this->conn, $this->sqlQuery);
        echo $this->sqlQuery;
        return ($this->dataSet) ? true : false;
    }

    // Ma'lumotlar bazisadan biror shartga ko'ra ma'lumotni yangilash
    public  function updateWhere($tablename, $values=[], $condition=[], $extra="")
    {
        $this->connect();
        $this->sqlQuery = 'UPDATE ' . $tablename . ' SET ';
        foreach ($values as $key => $value) {
            $this->sqlQuery .= $this->escapeString($key);
            $this->sqlQuery .= $value!==null ? "='" .$this->escapeString($value) . "'," : "=NULL,";
        }
        $this->sqlQuery = substr($this->sqlQuery, 0,strlen($this->sqlQuery)-1);
        $this->sqlQuery .= ' WHERE ';
        foreach ($condition as $keys => $val) {
            if ($keys != 'cn') {
                $this->sqlQuery .= $this->escapeString($keys) . $condition['cn'] = "='";
                $this->sqlQuery .= $this->escapeString($condition[$keys]) . "' ";
                $this->sqlQuery .= " and ";
            }
        }
        $this->sqlQuery = substr($this->sqlQuery, 0, strlen($this->sqlQuery)-4);
        $this->sqlQuery .= $extra;
        return (mysqli_query($this->conn, $this->sqlQuery)) ? true : false;
    }

    // Ma'lumotlar bazasiga qo'lda so'rov yuborish
    public  function withSqlQuery($query)
    {
        $this->connect();
        $this->dataSet = mysqli_query($this->conn, $this->escapeString($query));
        return $this;
    }

    // Ma'lumotlar bazasiga qo'lda so'rov yuborish
    public  function withSqlQueryWithOutEscapeString($query)
    {
        $this->connect();
        $this->dataSet = mysqli_query($this->conn, $query);
        return $this;
    }

    // Ma'lumotni tartib bilan o'qib olish
    public function  get() {
        $results = $this->dataSet;
        foreach ($results as $result => $value) {
            $this->params[$result] = $value;
        }
        return $this->params;
    }
    public  function fetch()
    {
        $this->connect();
        if ($this->dataSet) {
            return mysqli_fetch_assoc($this->dataSet) ?? false;
        }
        return false;
    }

    public  function rowCount () {
        $this->connect();
        if ($this->dataSet) {
            return mysqli_num_rows($this->dataSet);
        }
        return false;
    }
}