<?php
use Mindy\Helper\Console;

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
    private $_error;
    private $_exception;

    public function getError()
    {
        return $this->_error;
    }

    protected function handleException($exception)
    {
        Yii::app()->middleware->processException($exception);

        $app = Mindy::app();
        if (Console::isCli() === false) {
            if (($trace = $this->getExactTrace($exception)) === null) {
                $fileName = $exception->getFile();
                $errorLine = $exception->getLine();
            } else {
                $fileName = $trace['file'];
                $errorLine = $trace['line'];
            }

            $trace = $exception->getTrace();

            foreach ($trace as $i => $t) {
                if (!isset($t['file'])) {
                    $trace[$i]['file'] = 'unknown';
                }

                if (!isset($t['line'])) {
                    $trace[$i]['line'] = 0;
                }

                if (!isset($t['function'])) {
                    $trace[$i]['function'] = 'unknown';
                }

                unset($trace[$i]['object']);
            }

            $this->_exception = $exception;
            $this->_error = $data = array(
                'code' => ($exception instanceof CHttpException) ? $exception->statusCode : 500,
                'type' => get_class($exception),
                'errorCode' => $exception->getCode(),
                'message' => $exception->getMessage(),
                'file' => $fileName,
                'line' => $errorLine,
                'trace' => $exception->getTraceAsString(),
                'traces' => $trace,
            );

            if (!headers_sent()) {
                header("HTTP/1.0 {$data['code']} " . $this->getHttpHeader($data['code'], get_class($exception)));
            }

            $this->renderException();
        } else
            $app->displayException($exception);
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

        if(!isset($data['code'])) {
            $template = $this->getViewFile($view, '');
        } else {
            $template = $this->getViewFile($view, $data['code']);
        }

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

    public function renderSource($file, $errorLine, $maxLines)
    {
        return $this->renderSourceCode($file, $errorLine, $maxLines);
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
        $data = $this->getError();
        if (YII_DEBUG)
            $this->render('exception', $data);
        else
            $this->render('error', $data);
    }
}
