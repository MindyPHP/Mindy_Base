<?php

/**
 *
 * CoreHttpRequest class file.
 *
 * @author Falaleev Maxim <max@studio107.com>
 * @link http://studio107.ru/
 * @copyright Copyright &copy; 2010-2012 Studio107
 * @license http://www.cms107.com/license/
 * @package modules.core.components
 * @since 1.1.1
 * @version 1.0
 *
 */
class MHttpRequest extends CHttpRequest
{
    /**
     * @var bool
     */
    public $enableCsrfValidation = true;
    /**
     * @var bool
     */
    public $enableCookieValidation = true;
    /**
     * @var string
     */
    public $csrfTokenName = 'csrf_token';
    /**
     * @var
     */
    private $_csrfToken;
    /**
     * @var
     */
    public $is_ajax;

    public function init()
    {
        parent::init();
        $this->is_ajax = $this->getIsAjaxRequest();
    }

    /**
     * Returns the random token used to perform CSRF validation.
     * The token will be read from cookie first. If not found, a new token
     * will be generated.
     * @return string the random token for CSRF validation.
     * @see enableCsrfValidation
     */
    public function getCsrfToken()
    {
        if ($this->_csrfToken === null) {
            $session = Yii::app()->session;
            $cookie = $this->getCookies()->itemAt($this->csrfTokenName);
            if (!$cookie || ($this->_csrfToken = $cookie->value) == null) {
                $cookie = $this->createCsrfCookie();
                $this->_csrfToken = $cookie->value;
                $this->getCookies()->add($cookie->name, $cookie);
                $session->add($this->csrfTokenName, $this->_csrfToken);
            }
        }

        return $this->_csrfToken;
    }

    /**
     * Performs the CSRF validation.
     * This is the event handler responding to {@link CApplication::onBeginRequest}.
     * The default implementation will compare the CSRF token obtained
     * from a cookie and from a POST field. If they are different, a CSRF attack is detected.
     * @param CEvent $event event parameter
     * @throws CHttpException if the validation fails
     */
    public function validateCsrfToken($event)
    {
        if ($this->getIsPostRequest() ||
            $this->getIsPutRequest() ||
            $this->getIsDeleteRequest()
        ) {
            $cookies = $this->getCookies();

            $method = $this->getRequestType();
            switch ($method) {
                case 'POST':
                    $userToken = $this->getPost($this->csrfTokenName);
                    break;
                case 'PUT':
                    $userToken = $this->getPut($this->csrfTokenName);
                    break;
                case 'DELETE':
                    $userToken = $this->getDelete($this->csrfTokenName);
                    break;
                case 'GET':
                    $userToken = $this->getGet($this->csrfTokenName);
                    break;
            }

            if (empty($userToken)) {
                $session = Yii::app()->session;

                // check token in $_SERVER variable
                if (isset($_SERVER["HTTP_" . strtoupper($this->csrfTokenName)]))
                    $userToken = $_SERVER["HTTP_" . strtoupper($this->csrfTokenName)];
                else if ($session->contains($this->csrfTokenName)) {
                    // check token in session
                    $userToken = $session->itemAt($this->csrfTokenName);
                }
            }


            if (!empty($userToken) && $cookies->contains($this->csrfTokenName)) {
                $cookieToken = $cookies->itemAt($this->csrfTokenName)->value;
                $valid = $cookieToken === $userToken;
            } else
                $valid = false;
            if (!$valid)
                throw new CHttpException(400, Yii::t('yii', 'The CSRF token could not be verified.'));
        }
    }

    /**
     * Returns the named GET parameter value.
     * If the GET parameter does not exist, the second parameter to this method will be returned.
     * @param string $name the GET parameter name
     * @param mixed $defaultValue the default parameter value if the GET parameter does not exist.
     * @return mixed the GET parameter value
     * @see getParam
     * @see getQuery
     */
    public function getGet($name, $defaultValue = null)
    {
        return isset($_GET[$name]) ? $_GET[$name] : $defaultValue;
    }

    protected function normalizeRequest()
    {
        // normalize request
        if (function_exists('get_magic_quotes_gpc') && get_magic_quotes_gpc()) {
            if (isset($_GET))
                $_GET = $this->stripSlashes($_GET);
            if (isset($_POST))
                $_POST = $this->stripSlashes($_POST);
            if (isset($_REQUEST))
                $_REQUEST = $this->stripSlashes($_REQUEST);
            if (isset($_COOKIE))
                $_COOKIE = $this->stripSlashes($_COOKIE);
        }

        $urlManager = Yii::app()->getUrlManager();

        // @TODO: fix me please
        try {
            $url = $urlManager->parseUrl($this);
        } catch (Exception $e) {
            return null;
        }

        if ($this->enableCsrfValidation && array_search($url, $urlManager->rulesCsrfExcluded) === false) {
            Yii::app()->attachEventHandler('onBeginRequest', array($this, 'validateCsrfToken'));
        }
    }

    public function getPath()
    {
        return $this->getRequestUri();
    }

    public function getIsAjax()
    {
        return $this->getIsAjaxRequest();
    }
}
