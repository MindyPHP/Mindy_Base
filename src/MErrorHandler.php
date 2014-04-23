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
 * @date 13/04/14.04.2014 17:27
 */
class MErrorHandler extends CErrorHandler
{
    protected function handleException($exception)
    {
        Yii::app()->middleware->processException($exception);
        parent::handleException($exception);
    }

    protected function handleError($event)
    {
        $msg = "Error: {$event->message}\nFile: {$event->file}\nLine: {$event->line}";
        Yii::app()->middleware->processException(new Exception($msg));
        parent::handleError($event);
    }

    /**
     * Renders the view.
     * @param string $view the view name (file name without extension).
     * See {@link getViewFile} for how a view file is located given its name.
     * @param array $data data to be passed to the view
     */
    protected function render($view, $data)
    {
        // additional information to be passed to view
        $data['version'] = $this->getVersionInfo();
        $data['time'] = time();
        $data['admin'] = $this->adminInfo;

        $template = $this->getViewFile($view, $data['code']);

        if ($template === null) {
            $template = $this->getViewFile($view, '');
        }

        if ($template === null) {
            $ext = Yii::app()->viewRenderer->fileExtension;
            $views = implode(' ', [$view . $data['code'] . $ext, $view . $ext]);
            throw new Exception("Template not found: $views. Search paths:\n" . implode("\n", Yii::app()->finder->getPaths()));
        }

        echo Yii::app()->viewRenderer->render($template, [
            'data' => $data,
            'this' => $this
        ]);
    }

    public function argsToString($args)
    {
        return parent::argumentsToString($args);
    }

    public function isCore($trace)
    {
        return parent::isCoreCode($trace);
    }

    public function renderSource($file,$errorLine,$maxLines)
    {
        return $this->renderSourceCode($file,$errorLine,$maxLines);
    }

    protected function getViewFile($view, $code)
    {
        $ext = Yii::app()->viewRenderer->fileExtension;
        return Yii::app()->finder->find('core/' . $view . $code . $ext);
    }

    /**
     * Renders the current error information.
     * This method will display information from current {@link error} value.
     */
    protected function renderError()
    {
        $data=$this->getError();
        if(YII_DEBUG)
            $this->render('exception',$data);
        else
            $this->render('error',$data);
    }
}
