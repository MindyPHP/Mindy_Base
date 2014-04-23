<?php
/**
 * 
 *
 * All rights reserved.
 * 
 * @author Falaleev Maxim
 * @email max@studio107.ru
 * @version 1.0
 * @company Studio107
 * @site http://studio107.ru
 * @date 14/04/14.04.2014 18:05
 */

abstract class MTreeActiveRecord extends MActiveRecord
{
    public function relations()
    {
        return [
            'parent' => [self::BELONGS_TO, get_class($this), 'parent_id'],
        ];
    }

    /**
     * @return array validation rules for model attributes.
     */
    public function rules()
    {
        return [
            ['parent_id', 'numerical', 'integerOnly' => true],
            ['parent_id', 'safe', 'on' => 'search'],
        ];
    }

    public function behaviors()
    {
        return [
            'tree' => [
                'class' => 'mindy.behaviors.NestedSet.NestedSetBehavior'
            ],
        ];
    }

    public function save($runValidation = true, $attributes = null)
    {
        if($this->parent_id) {
            return $this->tree->appendTo($this->parent);
        } else {
            return $this->tree->saveNode($runValidation, $attributes);
        }
    }

    public function delete()
    {
        return $this->tree->delete();
    }
}
