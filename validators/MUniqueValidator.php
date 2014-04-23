<?php

/*
public function rules() {
    return array(
        array('firstKey', 'unique', 'criteria'=>array(
            'condition'=>'`secondKey`=:secondKey',
            'params'=>array(
                ':secondKey'=>$this->secondKey
            )
        )),
    );
}
 */

class MUniqueValidator extends CValidator
{

    /**
     * The attributes boud in the unique contstraint with attribute
     *
     * @var string
     */
    public $with;

    /**
     * Validates the attribute of the object.
     * If there is any error, the error message is added to the object.
     *
     * @param CModel $object the object being validated
     * @param string $attribute the attribute being validated
     */
    protected function validateAttribute($object, $attribute)
    {
        $with = explode(",", $this->with);
        if (count($with) < 1)
            throw new Exception("Attribute 'with' not set");
        $uniqueValidator = new CUniqueValidator();
        $uniqueValidator->attributes = array($attribute);
        $message = $this->message !== null ? $this->message : Yii::t('yii', '{attribute} "{value}" has already been taken.');
        $uniqueValidator->message = $message;
        $uniqueValidator->on = $this->on;
        $conditionParams = array();
        $params = array();
        foreach ($with as $attribute) {
            $attribute = trim($attribute);
            $conditionParams[] = "`{$attribute}`=:{$attribute}";
            $params[":{$attribute}"] = $object->$attribute;
        }
        $condition = implode(" AND ", $conditionParams);
        $uniqueValidator->criteria = array(
            'condition' => $condition,
            'params' => $params
        );
        $uniqueValidator->validate($object);
    }

}