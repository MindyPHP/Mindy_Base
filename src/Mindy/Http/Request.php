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
 * @date 12/06/14.06.2014 19:46
 */

namespace Mindy\Http;


use Mindy\Base\CookieCollection;
use Mindy\Base\Mindy;
use Mindy\Core\Object;


class Request extends Object
{
    /**
     * @var CookieCollection
     */
    public $cookies;
    /**
     * @var HttpCollection
     */
    public $get;
    /**
     * @var HttpCollection
     */
    public $post;
    /**
     * @var HttpCollection
     */
    public $put;
    /**
     * @var HttpCollection
     */
    public $delete;
    /**
     * @var HttpCollection
     */
    public $patch;
    /**
     * @var FilesCollection
     */
    public $files;
    /**
     * @var SessionCollection
     */
    public $session;
    /**
     * @var \Mindy\Base\HttpRequest
     */
    public $http;

    public function init()
    {
        $this->http = Mindy::app()->request;

        $this->get = new HttpCollection($_GET);
        $this->post = new HttpCollection($_POST);
        $this->files = new FilesCollection($_FILES);
        $this->put = new HttpCollection($this->http->getIsPutRequest() ? $_POST : []);
        $this->delete = new HttpCollection($this->http->getIsDeleteRequest() ? $_POST : []);
        $this->patch = new HttpCollection($this->http->getIsPatchRequest() ? $_POST : []);

        $this->cookies = new CookieCollection($this->http);
        $this->session = Mindy::app()->session;
    }

    public function redirect($url, $statusCode = 302)
    {
        return $this->http->redirect($url, true, $statusCode);
    }

    public function getDomain()
    {
        return $this->http->getHostInfo();
    }
}
