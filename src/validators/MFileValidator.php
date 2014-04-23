<?php

//http://www.yiiframework.com/extension/file-upload-error-validator/

/*
 * To change this template, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of fileUploadErrorValidator
 *
 * @author jmariani
 */
class MFileValidator extends CValidator
{

    public function clientValidateAttribute($object, $attribute)
    {

    }

    /**
     * Validates the attribute of the object.
     * If there is any error, the error message is added to the object.
     *
     * @param CModel $object the object being validated
     * @param string $attribute the attribute being validated
     */
    protected function validateAttribute($object, $attribute)
    {
        $file = CUploadedFile::getInstance($object, $attribute);
        if ($file) {
            // If file has error
            if ($file->hasError) {
                if (!$object->hasErrors($attribute)) {
                    $errors = array();
                    $errors[] = yii::t('app', 'Error uploading file ":file"', array(':file' => $file->name));
                    switch ($file->error) {
                        case UPLOAD_ERR_INI_SIZE:
                        case UPLOAD_ERR_FORM_SIZE:
                            $errors[] = yii::t('app', 'File is too big.');
                            break;
                        case UPLOAD_ERR_PARTIAL:
                            $errors[] = yii::t('app', 'File upload aborted.');
                            break;
                        case UPLOAD_ERR_NO_FILE:
                            $errors[] = yii::t('app', 'No file was uploaded.');
                            break;
                        case UPLOAD_ERR_NO_TMP_DIR:
                            $errors[] = yii::t('app', 'No temporary directory defined.');
                            break;
                        case UPLOAD_ERR_CANT_WRITE:
                            $errors[] = yii::t('app', 'Cannot write file to disk.');
                            break;
                        case UPLOAD_ERR_EXTENSION:
                            $errors[] = yii::t('app', 'Upload aborted by an unknown extension.');
                            break;
                    }
                    $object->addErrors(array($attribute => $errors));
                }
            }
        }
    }

}

?>
