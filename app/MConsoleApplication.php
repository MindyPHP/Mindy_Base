<?php

class MConsoleApplication extends CConsoleApplication
{
    public function __construct($config = null)
    {
        Yii::setApplication($this);

        // set basePath at early as possible to avoid trouble
        if (is_string($config)) {
            $config = require($config);
        }

        if (isset($config['basePath'])) {
            $this->setBasePath($config['basePath']);
            unset($config['basePath']);
        } else {
            $this->setBasePath('protected');
        }

        Yii::setPathOfAlias('application', $this->getBasePath());
        Yii::setPathOfAlias('webroot', dirname($_SERVER['SCRIPT_FILENAME']));
        Yii::setPathOfAlias('ext', $this->getBasePath() . DIRECTORY_SEPARATOR . 'extensions');

        $this->preinit();

        $this->initSystemHandlers();
        $this->registerCoreComponents();

        $this->configure($config);
        $this->attachBehaviors($this->behaviors);
        $this->preloadComponents();

        $this->init();
    }
}
