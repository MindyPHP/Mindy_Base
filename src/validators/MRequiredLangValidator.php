<?php

class MRequiredLangValidator extends CRequiredValidator
{

    protected function validateAttribute($object, $attribute)
    {
        $value = $object->$attribute;
        if ($this->requiredValue !== null) {
            if (!$this->strict && $value != $this->requiredValue || $this->strict && $value !== $this->requiredValue) {
                $message = $this->message !== null ? $this->message : Yii::t('MindyModule.mindy', '{attribute} must be {value}. Language: {lang}',
                    array('{value}' => $this->requiredValue));
                $this->addError($object, $attribute, $message);
            }
        } else if ($this->isEmpty($value, true)) {
            $message = $this->message !== null ? $this->message : Yii::t('MindyModule.mindy', '{attribute} cannot be blank. Language: {lang}');
            $this->addError($object, $attribute, $message);
        }
    }

    /**
     * Adds an error about the specified attribute to the active record.
     * This is a helper method that performs message selection and internationalization.
     * @param CModel $object the data object being validated
     * @param string $attribute the attribute being validated
     * @param string $message the error message
     * @param array $params values for the placeholders in the error message
     */
    protected function addError($object,$attribute,$message,$params=array())
    {
        $lang = explode('_',$attribute);
        $params['{attribute}']=$object->getAttributeLabel($attribute);
        $params['{lang}']=end($lang);
        $object->addError($attribute,strtr($message,$params));
    }

    /**
     * Returns the JavaScript needed for performing client-side validation.
     * @param CModel $object the data object being validated
     * @param string $attribute the name of the attribute to be validated.
     * @return string the client-side validation script.
     * @see CActiveForm::enableClientValidation
     * @since 1.1.7
     */
    public function clientValidateAttribute($object, $attribute)
    {
        $message = $this->message;
        if ($this->requiredValue !== null) {
            if ($message === null)
                $message = Yii::t('MindyModule.mindy', '{attribute} must be {value}. Language: {lang}');
            $message = strtr($message, array(
                '{value}' => $this->requiredValue,
                '{attribute}' => $object->getAttributeLabel($attribute)
            ));
            return "
if(value!=" . CJSON::encode($this->requiredValue) . ") {
	messages.push(" . CJSON::encode($message) . ");
}
";
        } else {
            if ($message === null)
                $message = Yii::t('yii', 'MindyModule.mindy', '{attribute} cannot be blank. Language: {lang}');
            $message = strtr($message, array(
                '{attribute}' => $object->getAttributeLabel($attribute),
            ));
            return "
if($.trim(value)=='') {
	messages.push(" . CJSON::encode($message) . ");
}
";
        }
    }
}
