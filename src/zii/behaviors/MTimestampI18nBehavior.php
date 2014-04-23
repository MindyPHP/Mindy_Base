<?php

/*
 * DateTimeI18NBehavior
 * Automatically converts date and datetime fields to I18N format
 * 
 * Author: Ricardo Grana <rickgrana@yahoo.com.br>, <ricardo.grana@pmm.am.gov.br>
 * Version: 1.1
 * Requires: Yii 1.0.9 version 
 */

class MTimestampI18nBehavior extends CTimestampBehavior
{
    public $dateOutcomeFormat = 'Y-m-d';
    public $dateTimeOutcomeFormat = 'Y-m-d H:i:s';

    public $dateIncomeFormat = 'yyyy-MM-dd';
    public $dateTimeIncomeFormat = 'yyyy-MM-dd hh:mm:ss';
 
    public function beforeSave($event)
    {
        parent::beforeSave($event);

        //search for date/datetime columns. Convert it to pure PHP date format
        foreach ($event->sender->tableSchema->columns as $columnName => $column)
        {
            if (($column->dbType != 'date') and ($column->dbType != 'datetime'))
                continue;

            if (!strlen($event->sender->$columnName)) {
                $event->sender->$columnName = null;
                continue;
            }

            if (($column->dbType == 'date')) {
                $parsed = CDateTimeParser::parse($event->sender->$columnName, Yii::app()->locale->dateFormat);
                $event->sender->$columnName = date($this->dateOutcomeFormat, $parsed);
            } else {
                $parsed = CDateTimeParser::parse($event->sender->$columnName, strtr(Yii::app()->locale->dateTimeFormat,
                    array(
                        "{0}" => Yii::app()->locale->timeFormat,
                        "{1}" => Yii::app()->locale->dateFormat
                    )
                ));
                $event->sender->$columnName = date($this->dateTimeOutcomeFormat, $parsed);
            }

        }

        return true;
    }

    public function afterFind($event)
    {
        parent::afterFind($event);

        foreach ($event->sender->tableSchema->columns as $columnName => $column)
        {
            if (($column->dbType != 'date') and ($column->dbType != 'datetime'))
                continue;

            if (!strlen($event->sender->$columnName)) {
                $event->sender->$columnName = null;
                continue;
            }

            if ($column->dbType == 'date') {
                $parsed = CDateTimeParser::parse($event->sender->$columnName, $this->dateIncomeFormat);
                $event->sender->$columnName = Yii::app()->dateFormatter->formatDateTime($parsed, 'medium', null);
            } else {
                $parsed = CDateTimeParser::parse($event->sender->$columnName, $this->dateTimeIncomeFormat);
                $event->sender->$columnName = Yii::app()->dateFormatter->formatDateTime($parsed, 'medium', 'medium');
            }
        }

        return true;
    }
}