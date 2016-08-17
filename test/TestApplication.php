<?php

namespace Mindy\Base\Tests;

use Mindy\Base\Application;
use Mindy\Base\Mindy;

class TestApplication extends Application
{
    public function __construct($config = null)
    {
        Mindy::setApplication(null);
        clearstatcache();
        parent::__construct($config);
    }

    public function reset()
    {
        $this->removeDirectory($this->getRuntimePath());
    }

    protected function removeDirectory($path)
    {
        if (is_dir($path) && ($folder = @opendir($path)) !== false) {
            while ($entry = @readdir($folder)) {
                if ($entry[0] === '.') {
                    continue;
                }
                $p = $path . DIRECTORY_SEPARATOR . $entry;
                if (is_dir($p)) {
                    $this->removeDirectory($p);
                }
                @unlink($p);
            }
            @closedir($folder);
        }
    }

    public function getRuntimePath()
    {
        return $this->getBasePath() . '/runtime';
    }

    public function getBasePath()
    {
        return __DIR__ . '/app';
    }

    public function setBasePath($value)
    {
    }

    public function loadGlobalState()
    {
        parent::loadGlobalState();
    }

    public function saveGlobalState()
    {
        parent::saveGlobalState();
    }
}
