<?php

abstract class MActiveRecordIterator implements Iterator
{
    private $_model;
    private $_result;
    private $_row_num = 0;
    private $_total_rows = 0;
    private $_current_data = null; // Добавим временное хранение данных строки

    public function getModel()
    {
        return $this->_model;
    }

    /**
     * @param $model string or MActiveRecord instance
     * @return MActiveRecordIterator
     */
    public function setModel($model)
    {
        $this->_model = $model instanceof MActiveRecord ? $model : new $model();

        return $this->processData();
    }

    protected function processData()
    {
        $data = $this->_model->getData();

        $this->_result = $data;
        $this->_total_rows = count($data) - 1;
        $this->_current_data = array_shift($data);

        return $this;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the current element
     * @link http://php.net/manual/en/iterator.current.php
     * @return mixed Can return any type.
     */
    public function current()
    {
        $model = clone $this->_model; // тут переделаем
        $model->setAttributes($this->_current_data, false);
        return $model;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Move forward to next element
     * @link http://php.net/manual/en/iterator.next.php
     * @return void Any returned value is ignored.
     */
    public function next()
    {
        ++$this->_row_num;
        $this->_current_data = $this->_result[$this->_row_num];
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Return the key of the current element
     * @link http://php.net/manual/en/iterator.key.php
     * @return mixed scalar on success, or null on failure.
     */
    public function key()
    {
//        return $this->_current_data->getPrimaryKey();
        return $this->_row_num;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Checks if current position is valid
     * @link http://php.net/manual/en/iterator.valid.php
     * @return boolean The return value will be casted to boolean and then evaluated.
     * Returns true on success or false on failure.
     */
    public function valid()
    {
        return $this->_row_num < $this->_total_rows;
    }

    /**
     * (PHP 5 &gt;= 5.0.0)<br/>
     * Rewind the Iterator to the first element
     * @link http://php.net/manual/en/iterator.rewind.php
     * @return void Any returned value is ignored.
     */
    public function rewind()
    {
        $this->_row_num = 0;
    }
}