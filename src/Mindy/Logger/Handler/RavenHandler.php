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
 * @date 12/06/14.06.2014 15:14
 */

namespace Mindy\Logger\Handler;

use Mindy\Base\Mindy;
use Monolog\Handler\RavenHandler as MonoRavenHandler;
use Raven_Client;

class RavenHandler extends ProxyHandler
{
    public $dsn;

    public function getHandler()
    {
        $raven = new Raven_Client($this->dsn);
        return new MonoRavenHandler($raven, $this->getLevel(), $this->bubble);
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        $options = [];
        $options['level'] = $this->logLevels[$record['level']];
        $options['tags'] = [];
        if (!empty($record['extra']['tags'])) {
            $options['tags'] = array_merge($options['tags'], $record['extra']['tags']);
            unset($record['extra']['tags']);
        }
        if (!empty($record['context']['tags'])) {
            $options['tags'] = array_merge($options['tags'], $record['context']['tags']);
            unset($record['context']['tags']);
        }
        if (!empty($record['context'])) {
            $options['extra']['context'] = $record['context'];
        }
        if (!empty($record['extra'])) {
            $options['extra']['extra'] = $record['extra'];
        }

        $options['extra'] = array_merge([
            'php_version' => phpversion(),
            'mindy_version' => Mindy::getVersion()
        ], $options['extra']);

        if (isset($record['context']['exception']) && $record['context']['exception'] instanceof \Exception) {
            $options['extra']['message'] = $record['formatted'];
            $this->ravenClient->captureException($record['context']['exception'], $options);

            return;
        }

        $this->ravenClient->captureMessage($record['formatted'], [], $options);
    }
}
