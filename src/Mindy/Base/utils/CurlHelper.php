<?php
class CurlHelper
{
    protected $url;
    protected $ch;

    public $options = array();
    public $info = array();
    public $error_code = 0;
    public $error_string = '';

    protected $validOptions = array(
        'timeout' => array('type' => 'integer'),
        'login' => array('type' => 'array'),
        'proxy' => array('type' => 'array'),
        'proxylogin' => array('type' => 'array'),
        'setOptions' => array('type' => 'array'),
    );

    public function __construct($options = array())
    {
        if (!function_exists('curl_init'))
            throw new CException(Yii::t('Curl', 'You must have CURL enabled in order to use this extension.'));

        if ($options !== array())
            $this->options = $options;
    }


    public function setOption($key, $value)
    {
        curl_setopt($this->ch, $key, $value);
    }


    public function setUrl($url)
    {
        if (!preg_match('!^\w+://! i', $url)) {
            $url = 'http://' . $url;
        }
        $this->url = $url;
    }


    public function setCookies($values)
    {
        if (!is_array($values))
            throw new CException(Yii::t('Curl', 'options must be an array'));
        else
            $params = $this->cleanPost($values);
        $this->setOption(CURLOPT_COOKIE, $params);
    }


    public function setHttpLogin($username = '', $password = '')
    {
        $this->setOption(CURLOPT_USERPWD, $username . ':' . $password);
    }


    public function setProxy($url, $port = 80)
    {
        $this->setOption(CURLOPT_HTTPPROXYTUNNEL, true);
        $this->setOption(CURLOPT_PROXY, $url . ':' . $port);
    }


    public function setProxyLogin($username = '', $password = '')
    {
        $this->setOption(CURLOPT_PROXYUSERPWD, $username . ':' . $password);
    }


    protected static function checkOptions($value, $validOptions)
    {
        if (!empty($validOptions)) {
            foreach ($value as $key => $val) {

                if (!array_key_exists($key, $validOptions)) {
                    throw new CException(Yii::t('Curl', '{k} is not a valid option', array('{k}' => $key)));
                }
                $type = gettype($val);
                if ((!is_array($validOptions[$key]['type']) && ($type != $validOptions[$key]['type'])) || (is_array($validOptions[$key]['type']) && !in_array($type, $validOptions[$key]['type']))) {
                    throw new CException(Yii::t('Curl', '{k} must be of type {t}',
                        array('{k}' => $key, '{t}' => $validOptions[$key]['type'])));
                }

                if (($type == 'array') && array_key_exists('elements', $validOptions[$key])) {
                    self::checkOptions($val, $validOptions[$key]['elements']);
                }
            }
        }
    }


    protected function defaults()
    {
        !isset($this->options['timeout']) ? $this->setOption(CURLOPT_TIMEOUT, 30) : $this->setOption(CURLOPT_TIMEOUT, $this->options['timeout']);
        isset($this->options['setOptions'][CURLOPT_HEADER]) ? $this->setOption(CURLOPT_HEADER, $this->options['setOptions'][CURLOPT_HEADER]) : $this->setOption(CURLOPT_HEADER, false);
        isset($this->options['setOptions'][CURLOPT_RETURNTRANSFER]) ? $this->setOption(CURLOPT_RETURNTRANSFER, $this->options['setOptions'][CURLOPT_RETURNTRANSFER]) : $this->setOption(CURLOPT_RETURNTRANSFER, true);
        isset($this->options['setOptions'][CURLOPT_FOLLOWLOCATION]) ? $this->setOption(CURLOPT_FOLLOWLOCATIO, $this->options['setOptions'][CURLOPT_FOLLOWLOCATION]) : $this->setOption(CURLOPT_FOLLOWLOCATION, true);
        isset($this->options['setOptions'][CURLOPT_FAILONERROR]) ? $this->setOption(CURLOPT_FAILONERROR, $this->options['setOptions'][CURLOPT_FAILONERROR]) : $this->setOption(CURLOPT_FAILONERROR, true);
    }


    //@TODO реализовать алгоритм с любым параметром CURLOPT_CUSTOMREQUEST
    public function run($url, $type, $data = array())
    {
        $this->setUrl($url);
        if (!$this->url)
            throw new CException(Yii::t('Curl', 'You must set Url.'));
        $this->ch = curl_init();
        self::checkOptions($this->options, $this->validOptions);
        switch ($type) {
            case "POST":
                curl_setopt($this->ch, CURLOPT_POST, true);
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
                break;
            case "GET":
                $url = $url . '?' . http_build_query($data);
                curl_setopt($this->ch, CURLOPT_HEADER, false);
                break;
            case "DELETE":
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "DELETE");
                break;
            case "PUT":
                curl_setopt($this->ch, CURLOPT_POSTFIELDS, $data);
                curl_setopt($this->ch, CURLOPT_CUSTOMREQUEST, "PUT");
                break;
        }
        curl_setopt($this->ch, CURLOPT_URL, $url);
        curl_setopt($this->ch, CURLOPT_RETURNTRANSFER, true);
        $return = curl_exec($this->ch);
        if ($return) {
            $this->info = curl_getinfo($this->ch);
            curl_close($this->ch);
        } else {
            $this->error_code = curl_errno($this->ch);
            $this->error_string = curl_error($this->ch);
            curl_close($this->ch);
        }
        return $return;
    }


    protected
    function &cleanPost(&$string, $name = null)
    {
        $thePostString = '';
        $thePrefix = $name;

        if (is_array($string)) {
            foreach ($string as $k => $v) {
                if ($thePrefix === null) {
                    $thePostString .= '&' . self::cleanPost($v, $k);
                } else {
                    $thePostString .= '&' . self::cleanPost($v, $thePrefix . '[' . $k . ']');
                }
            }

        } else {
            $thePostString .= '&' . urlencode((string)$thePrefix) . '=' . urlencode($string);
        }

        $r =& substr($thePostString, 1);

        return $r;
    }

}