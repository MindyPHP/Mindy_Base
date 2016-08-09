<?php

namespace Mindy\Base;

use ErrorException;
use Mindy\Helper\Traits\Accessors;
use Mindy\Helper\Traits\Configurator;

use Mindy\Exception\CompileErrorException;
use Mindy\Exception\CoreErrorException;
use Mindy\Exception\CoreWarningException;
use Mindy\Exception\DeprecatedException;
use Mindy\Exception\Exception;
use Mindy\Exception\HttpException;
use Mindy\Exception\NoticeException;
use Mindy\Exception\ParseException;
use Mindy\Exception\RecoverableErrorException;
use Mindy\Exception\StrictException;
use Mindy\Exception\UserDeprecatedException;
use Mindy\Exception\UserErrorException;
use Mindy\Exception\UserNoticeException;
use Mindy\Exception\UserWarningException;
use Mindy\Exception\WarningException;

/**
 * CErrorHandler handles uncaught PHP errors and exceptions.
 *
 * It displays these errors using appropriate views based on the
 * nature of the error and the mode the application runs at.
 * It also chooses the most preferred language for displaying the error.
 *
 * CErrorHandler uses two sets of views:
 * <ul>
 * <li>development views, named as <code>exception.php</code>;
 * <li>production views, named as <code>error&lt;StatusCode&gt;.php</code>;
 * </ul>
 * where &lt;StatusCode&gt; stands for the HTTP error code (e.g. error500.php).
 * Localized views are named similarly but located under a subdirectory
 * whose name is the language code (e.g. zh_cn/error500.php).
 *
 * Development views are displayed when the application is in debug mode
 * (i.e. MINDY_DEBUG is defined as true). Detailed error information with source code
 * are displayed in these views. Production views are meant to be shown
 * to end-users and are used when the application is in production mode.
 * For security reasons, they only display the error message without any
 * sensitive information.
 *
 * CErrorHandler looks for the view templates from the following locations in order:
 * <ol>
 * <li><code>themes/ThemeName/views/system</code>: when a theme is active.</li>
 * <li><code>protected/views/system</code></li>
 * <li><code>framework/views</code></li>
 * </ol>
 * If the view is not found in a directory, it will be looked for in the next directory.
 *
 * The property {@link maxSourceLines} can be changed to specify the number
 * of source code lines to be displayed in development views.
 *
 * CErrorHandler is a core application component that can be accessed via
 * {@link CApplication::getErrorHandler()}.
 *
 * @property array $error The error details. Null if there is no error.
 * @property Exception|null $exception exception instance. Null if there is no exception.
 *
 * @author Qiang Xue <qiang.xue@gmail.com>
 * @package system.base
 * @since 1.0
 */
class ErrorHandler
{
    use Configurator, Accessors;

    /**
     * @var integer maximum number of source code lines to be displayed. Defaults to 25.
     */
    public $maxSourceLines = 35;

    /**
     * @var integer maximum number of trace source code lines to be displayed. Defaults to 10.
     * @since 1.1.6
     */
    public $maxTraceSourceLines = 10;
    /**
     * @var boolean whether to discard any existing page output before error display. Defaults to true.
     */
    public $discardOutput = true;

    /**
     * @var array exception map
     */
    private $_levelClasses = [
        E_ERROR => ErrorException::class,
        E_WARNING => WarningException::class,
        E_PARSE => ParseException::class,
        E_NOTICE => NoticeException::class,
        E_CORE_ERROR => CoreErrorException::class,
        E_CORE_WARNING => CoreWarningException::class,
        E_COMPILE_ERROR => CompileErrorException::class,
        E_COMPILE_WARNING => CoreWarningException::class,
        E_USER_ERROR => UserErrorException::class,
        E_USER_WARNING => UserWarningException::class,
        E_USER_NOTICE => UserNoticeException::class,
        E_STRICT => StrictException::class,
        E_RECOVERABLE_ERROR => RecoverableErrorException::class,
        E_DEPRECATED => DeprecatedException::class,
        E_USER_DEPRECATED => UserDeprecatedException::class,
    ];

    /**
     * Handles the exception/error event.
     * This method is invoked by the application whenever it captures
     * an exception or PHP error.
     */
    public function process()
    {
        if ($this->discardOutput) {
            $gzHandler = false;
            foreach (ob_list_handlers() as $h) {
                if (strpos($h, 'gzhandler') !== false) {
                    $gzHandler = true;
                }
            }
            // the following manual level counting is to deal with zlib.output_compression set to On
            // for an output buffer created by zlib.output_compression set to On ob_end_clean will fail
            for ($level = ob_get_level(); $level > 0; --$level) {
                if (!@ob_end_clean()) {
                    ob_clean();
                }
            }
            // reset headers in case there was an ob_start("ob_gzhandler") before
            if ($gzHandler && !headers_sent() && ob_list_handlers() === array()) {
                header('Vary:');
                header('Content-Encoding:');
            }
        }
    }

    /**
     * Handles the exception.
     * @param Exception $exception the exception captured
     */
    public function handleException($exception)
    {
        $this->process();
        if ($trace = $this->getExactTrace($exception)) {
            $fileName = $trace['file'];
            $errorLine = $trace['line'];
        } else {
            $fileName = $exception->getFile();
            $errorLine = $exception->getLine();
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

        if ($exception instanceof HttpException) {
            $code = $exception->statusCode;
        } else {
            $code = 500;
        }

        $data = [
            'code' => $code,
            'type' => get_class($exception),
            'errorCode' => $exception->getCode(),
            'message' => $exception->getMessage(),
            'file' => $fileName,
            'line' => $errorLine,
            'trace' => $exception->getTraceAsString(),
            'traces' => $trace,
        ];

        if (Mindy::app()->hasComponent('logger')) {
            $newData = $data;
            unset($newData['traces']);
            if (Mindy::app()->hasComponent('logger')) {
                Mindy::app()->logger->critical($exception->getMessage(), $newData);
            }
        }

        if (!headers_sent()) {
            header("HTTP/1.0 {$code} " . $this->getHttpHeader($code, get_class($exception)));
        }

        ob_get_clean();
        if ($this->isAjax() || $this->isCli()) {
            $this->displayException($exception);
            Mindy::app()->end();
        } else {
            $this->render('exception', $data);
            Mindy::app()->end();
        }
    }

    /**
     * @return bool
     */
    public function isCli()
    {
        return php_sapi_name() === 'cli';
    }

    /**
     * @return bool
     */
    public function isAjax()
    {
        return isset($_SERVER['HTTP_X_REQUESTED_WITH']) &&
        $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }

    /**
     * Displays the captured PHP error.
     * This method displays the error in HTML when there is
     * no active error handler.
     * @param integer $code error code
     * @param string $message error message
     * @param string $file error file
     * @param string $line error line
     */
    public function displayError($code, $message, $file, $line)
    {
        if (MINDY_DEBUG) {
            if ($this->isCli()) {
                echo "PHP Error [$code]" . PHP_EOL;
                echo "$message ($file:$line)" . PHP_EOL;
            } else {
                echo "<h1>PHP Error [$code]</h1>\n";
                echo "<p>$message ($file:$line)</p>\n";
                echo '<pre>';
            }

            $trace = debug_backtrace();
            // skip the first 3 stacks as they do not tell the error position
            if (count($trace) > 3)
                $trace = array_slice($trace, 3);
            foreach ($trace as $i => $t) {
                if (!isset($t['file']))
                    $t['file'] = 'unknown';
                if (!isset($t['line']))
                    $t['line'] = 0;
                if (!isset($t['function']))
                    $t['function'] = 'unknown';
                echo "#$i {$t['file']}({$t['line']}): ";
                if (isset($t['object']) && is_object($t['object']))
                    echo get_class($t['object']) . '->';
                echo "{$t['function']}()\n";
            }

            if (!$this->isCli()) {
                echo '</pre>';
            }
        } else {
            if ($this->isCli()) {
                echo "PHP Error [$code]\n" . PHP_EOL;
                echo "$message\n" . PHP_EOL;
            } else {
                echo "<h1>PHP Error [$code]</h1>\n";
                echo "<p>$message</p>\n";
            }
        }
    }

    /**
     * Displays the uncaught PHP exception.
     * This method displays the exception in HTML when there is
     * no active error handler.
     * @param Exception $exception the uncaught exception
     */
    public function displayException($exception)
    {
        if ($this->isCli()) {
            if (MINDY_DEBUG) {
                echo get_class($exception) . PHP_EOL;
                echo $exception->getMessage() . ' (' . $exception->getFile() . ':' . $exception->getLine() . ')' . PHP_EOL;
                echo $exception->getTraceAsString() . PHP_EOL;
            } else {
                echo get_class($exception) . PHP_EOL;
                echo $exception->getMessage() . PHP_EOL;
            }
        } else {
            if (MINDY_DEBUG) {
                echo '<h1>' . get_class($exception) . "</h1>\n";
                echo '<p>' . $exception->getMessage() . ' (' . $exception->getFile() . ':' . $exception->getLine() . ')</p>';
                echo '<pre>' . $exception->getTraceAsString() . '</pre>';
            } else {
                echo '<h1>' . get_class($exception) . "</h1>\n";
                echo '<p>' . $exception->getMessage() . '</p>';
            }
        }
    }

    /**
     * Handles the PHP error.
     * @param $code
     * @param $message
     * @param $file
     * @param $line
     * @param array $errcontext
     */
    public function handleError($code, $message, $file, $line, array $errcontext = [])
    {
        if (isset($this->_levelClasses[$code])) {
            $exceptionClass = $this->_levelClasses[$code];
            $exception = new $exceptionClass($message, 0, $code, $file, $line);
            $this->handleException($exception);
        } else {
            $this->process();
            $trace = debug_backtrace();
            // skip the first 3 stacks as they do not tell the error position
            if (count($trace) > 3) {
                $trace = array_slice($trace, 3);
            }
            $traceString = '';
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

                $traceString .= "#$i {$trace[$i]['file']}({$trace[$i]['line']}): ";
                if (isset($t['object']) && is_object($t['object'])) {
                    $traceString .= get_class($t['object']) . '->';
                }
                $traceString .= "{$trace[$i]['function']}()\n";

                unset($trace[$i]['object']);
            }

            switch ($code) {
                case E_WARNING:
                    $type = 'PHP warning';
                    break;
                case E_NOTICE:
                    $type = 'PHP notice';
                    break;
                case E_USER_ERROR:
                    $type = 'User error';
                    break;
                case E_USER_WARNING:
                    $type = 'User warning';
                    break;
                case E_USER_NOTICE:
                    $type = 'User notice';
                    break;
                case E_RECOVERABLE_ERROR:
                    $type = 'Recoverable error';
                    break;
                default:
                    $type = 'PHP error';
            }

            $data = [
                'code' => 500,
                'type' => $type,
                'message' => $message,
                'file' => $file,
                'line' => $line,
                'trace' => $traceString,
                'traces' => $trace,
                'errorContext' => $errcontext
            ];

            if (Mindy::app()->has('logger')) {
                Mindy::app()->logger->error($type, $data);
            }

            if ($this->isCli()) {
                $this->displayError($code, $message, $file, $line);
            } else {
                if (!headers_sent()) {
                    header("HTTP/1.0 500 Internal Server Error");
                }
                $this->render('error', $data);
            }
        }
    }

    /**
     * Returns the exact trace where the problem occurs.
     * @param Exception $exception the uncaught exception
     * @return array the exact trace where the problem occurs
     */
    protected function getExactTrace($exception)
    {
        $traces = $exception->getTrace();

        foreach ($traces as $trace) {
            // property access exception
            if (isset($trace['function']) && ($trace['function'] === '__get' || $trace['function'] === '__set')) {
                return $trace;
            }
        }
        return null;
    }

    /**
     * Renders the view.
     * @param $_viewFile_
     * @param array $_data_
     */
    protected function render($_viewFile_, array $_data_ = [])
    {
        // we use special variable names here to avoid conflict when extracting data
        extract(['data' => array_merge($_data_, [
            'this' => $this, 'time' => time(),
            'version' => $this->getVersionInfo()])
        ]);
        ob_implicit_flush(false);
        require(__DIR__ . '/templates/' . $_viewFile_ . '.php');
        echo ob_get_clean();
    }

    /**
     * Returns server version information.
     * If the application is in production mode, empty string is returned.
     * @return string server version information. Empty if in production mode.
     */
    protected function getVersionInfo()
    {
        if (MINDY_DEBUG) {
            $version = '<a href="http://mindy-cms.com/">Mindy Framework</a>/' . Mindy::getVersion();
            if (isset($_SERVER['SERVER_SOFTWARE'])) {
                $version = $_SERVER['SERVER_SOFTWARE'] . ' ' . $version;
            }
        } else {
            $version = '';
        }
        return $version;
    }

    /**
     * Converts arguments array to its string representation
     *
     * @param array $args arguments array to be converted
     * @return string string representation of the arguments array
     */
    protected function argumentsToString($args)
    {
        $count = 0;

        $isAssoc = $args !== array_values($args);

        foreach ($args as $key => $value) {
            $count++;
            if ($count >= 5) {
                if ($count > 5)
                    unset($args[$key]);
                else
                    $args[$key] = '...';
                continue;
            }

            if (is_object($value))
                $args[$key] = get_class($value);
            elseif (is_bool($value))
                $args[$key] = $value ? 'true' : 'false';
            elseif (is_string($value)) {
                if (strlen($value) > 64)
                    $args[$key] = '"' . substr($value, 0, 64) . '..."';
                else
                    $args[$key] = '"' . $value . '"';
            } elseif (is_array($value))
                $args[$key] = 'array(' . $this->argumentsToString($value) . ')';
            elseif ($value === null)
                $args[$key] = 'null';
            elseif (is_resource($value))
                $args[$key] = 'resource';

            if (is_string($key)) {
                $args[$key] = '"' . $key . '" => ' . $args[$key];
            } elseif ($isAssoc) {
                $args[$key] = $key . ' => ' . $args[$key];
            }
        }
        $out = implode(", ", $args);

        return $out;
    }

    /**
     * Renders the source code around the error line.
     * @param string $file source file path
     * @param integer $errorLine the error line number
     * @param integer $maxLines maximum number of lines to display
     * @return string the rendering result
     */
    protected function renderSourceCode($file, $errorLine, $maxLines)
    {
        $errorLine--; // adjust line number to 0-based from 1-based
        if ($errorLine < 0 || ($lines = @file($file)) === false || ($lineCount = count($lines)) <= $errorLine)
            return '';

        $halfLines = (int)($maxLines / 2);
        $beginLine = $errorLine - $halfLines > 0 ? $errorLine - $halfLines : 0;
        $endLine = $errorLine + $halfLines < $lineCount ? $errorLine + $halfLines : $lineCount - 1;

        $output = '';
        for ($i = $beginLine; $i <= $endLine; ++$i) {
            $code = sprintf("%s", htmlentities(str_replace("\t", '    ', $lines[$i]), ENT_QUOTES, Mindy::app()->locale['charset']));
            $output .= $code;
        }
        return strtr('<pre class="brush: php; highlight: {errorLine}; first-line: {beginLine}; toolbar: false;">{content}</pre>', [
            '{beginLine}' => $beginLine + 1,
            '{errorLine}' => $errorLine + 1,
            '{content}' => $output
        ]);
    }

    /**
     * Return correct message for each known http error code
     * @param integer $httpCode error code to map
     * @param string $replacement replacement error string that is returned if code is unknown
     * @return string the textual representation of the given error code or the replacement string if the error code is unknown
     */
    protected function getHttpHeader($httpCode, $replacement = '')
    {
        $httpCodes = [
            100 => 'Continue',
            101 => 'Switching Protocols',
            102 => 'Processing',
            118 => 'Connection timed out',
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',
            207 => 'Multi-Status',
            210 => 'Content Different',
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            307 => 'Temporary Redirect',
            310 => 'Too many Redirect',
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Time-out',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested range unsatisfiable',
            417 => 'Expectation failed',
            418 => 'I’m a teapot',
            422 => 'Unprocessable entity',
            423 => 'Locked',
            424 => 'Method failure',
            425 => 'Unordered Collection',
            426 => 'Upgrade Required',
            449 => 'Retry With',
            450 => 'Blocked by Windows Parental Controls',
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway ou Proxy Error',
            503 => 'Service Unavailable',
            504 => 'Gateway Time-out',
            505 => 'HTTP Version not supported',
            507 => 'Insufficient storage',
            509 => 'Bandwidth Limit Exceeded',
        ];
        return isset($httpCodes[$httpCode]) ? $httpCodes[$httpCode] : $replacement;
    }

    /**
     * @deprecated
     * @param $args
     * @return string
     */
    public function argsToString($args)
    {
        return $this->argumentsToString($args);
    }

    /**
     * @deprecated
     * @param $file
     * @param $errorLine
     * @param $maxLines
     * @return string
     */
    public function renderSource($file, $errorLine, $maxLines)
    {
        return $this->renderSourceCode($file, $errorLine, $maxLines);
    }
}
