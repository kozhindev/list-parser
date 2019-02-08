<?php

namespace app\loaders;

use app\ILogEvent;
use yii\base\BaseObject;

abstract class BaseLoader extends BaseObject
{
    /**
     * @var string
     */
    public $tempPath;

    /**
     * @var string
     */
    public $template;

    /**
     * @var callable
     */
    public $logHandler;

    /**
     * @param string $path
     * @return bool|string|string[]
     */
    abstract public function load($path);

    public function test($path)
    {
        return $this->template && preg_match($this->template, basename($path)) === 1;
    }

    protected function log($message)
    {
        if ($this->logHandler) {
            /** @var ILogEvent $data */
            $data = [
                'level' => 'info',
                'message' => $message,
            ];
            call_user_func($this->logHandler, $data);
        }
    }

    protected function error($message)
    {
        if ($this->logHandler) {
            /** @var ILogEvent $data */
            $data = [
                'level' => 'error',
                'message' => $message,
            ];
            call_user_func($this->logHandler, $data);
        }
        return false;
    }
}