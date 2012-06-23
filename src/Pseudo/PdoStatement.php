<?php
namespace Pseudo;

class PdoStatement extends \PDOStatement
{

    /**
     * @var Result;
     */
    private $result;
    private $fetchMode;

    public function __construct($result = null)
    {
        if (!($result instanceof Result)) {
            $result = new Result();
        }
        $this->result = $result;;
    }

    public function setResult(Result $result)
    {
        $this->result = $result;
    }

    public function execute(array $input_parameters = null)
    {
        parent::execute($input_parameters);
    }

    public function fetch($fetch_style = null, $cursor_orientation = PDO::FETCH_ORI_NEXT, $cursor_offset = 0)
    {
        // scrolling cursors not implemented
        $row = $this->result->nextRow();
        if ($row) {
            return $this->proccessFetchedRow($row, $fetch_style);
        }
        return false;
    }

    public function bindParam($parameter, &$variable, $data_type = PDO::PARAM_STR, $length = null, $driver_options = null)
    {
        parent::bindParam($parameter, $variable, $data_type, $length, $driver_options);
    }

    public function bindColumn($column, &$param, $type = null, $maxlen = null, $driverdata = null)
    {
        parent::bindColumn($column, $param, $type, $maxlen, $driverdata);
    }

    public function bindValue($parameter, $value, $data_type = PDO::PARAM_STR)
    {
        parent::bindValue($parameter, $value, $data_type);
    }

    public function rowCount()
    {
        return $this->result->getAffectedRowCount();
    }

    public function fetchColumn($column_number = 0)
    {
        parent::fetchColumn($column_number);
    }

    public function fetchAll($fetch_style = \PDO::FETCH_BOTH, $fetch_argument = null, $ctor_args = 'array()')
    {
        $rows = $this->result->getRows();
        $returnArray = [];
        foreach ($rows as $row) {
            $returnArray[] = $this->proccessFetchedRow($row, $fetch_style);
        }
        return $returnArray;
    }

    private function proccessFetchedRow($row, $fetchMode)
    {
        switch ($fetchMode ?: $this->fetchMode) {
            case \PDO::FETCH_BOTH:
                $returnRow = [];
                $keys = array_keys($row);
                $c = 0;
                foreach ($keys as $key) {
                    $returnRow[$key] = $row[$key];
                    $returnRow[$c++] = $row[$key];
                }
                return $returnRow;
            case \PDO::FETCH_ASSOC:
                return $row;
            case \PDO::FETCH_NUM:
                $returnRow = [];
                $keys = array_keys($row);
                $c = 0;
                foreach ($keys as $key) {
                    $returnRow[$c++] = $row[$key];
                }
                return $returnRow;
            case \PDO::FETCH_OBJ:
                return (object) $row;
        }
        return null;
    }

    public function fetchObject($class_name = "stdClass", array $ctor_args = null)
    {
        parent::fetchObject($class_name, $ctor_args);
    }

    public function errorCode()
    {
        return $this->result->getErrorCode();
    }

    public function errorInfo()
    {
        return $this->result->getErrorInfo();
    }

    public function setAttribute($attribute, $value)
    {
        parent::setAttribute($attribute, $value);
    }

    public function getAttribute($attribute)
    {
        parent::getAttribute($attribute);
    }

    public function columnCount()
    {
        $rows = $this->result->getRows();
        if ($rows) {
            $row = array_shift($rows);
            return count(array_keys($row));
        }
        return 0;
    }

    public function getColumnMeta($column)
    {
        // not implemented
    }

    public function setFetchMode($mode)
    {
        $r = new \ReflectionClass(new Pdo());
        $constants = $r->getConstants();
        $constantNames = array_keys($constants);
        $allowedConstantNames = array_filter($constantNames, function($val) {
            return strpos($val, 'FETCH_') === 0;
        });
        $allowedConstantVals = [];
        foreach ($allowedConstantNames as $name) {
            $allowedConstantVals[] = $constants[$name];
        }

        if (in_array($mode, $allowedConstantVals)) {
            $this->fetchMode = $mode;
            return 1;
        }
        return false;
    }

    public function nextRowset()
    {
        parent::nextRowset();
    }

    public function closeCursor()
    {
        parent::closeCursor();
    }

    public function debugDumpParams()
    {
        parent::debugDumpParams();
    }

}