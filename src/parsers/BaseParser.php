<?php

namespace kozhindev\ListParser\parsers;

use kozhindev\ListParser\ILogEvent;
use kozhindev\ListParser\IRowEvent;
use yii\base\BaseObject;
use yii\helpers\ArrayHelper;

abstract class BaseParser extends BaseObject
{
    /**
     * @var string
     */
    public $template;

    /**
     * @var callable
     */
    public $logHandler;

    /**
     * @var callable
     */
    public $rowHandler;

    protected $_totalCount;

    /**
     * @param string $path
     * @return bool
     */
    abstract public function parse($path);

    public function test($path)
    {
        return $this->template && preg_match($this->template, basename($path)) === 1;
    }

    protected function setTotalCount($value)
    {
        $this->_totalCount = $value;
    }

    protected function addRow($value, $index, $fileName = null, $listName = null, $listCount = null)
    {
        if ($this->rowHandler) {
            /** @var IRowEvent $data */
            $data = [
                'row' => $value,
                'file' => $fileName,
                'list' => $listName,
                'listCount' => $listCount,
                'index' => $index,
                'totalCount' => $this->_totalCount,
            ];
            call_user_func($this->rowHandler, $data);
        }
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

    /**
     * @param array $list Source array of arrays e.g.:
     * [
     *      ['foo', 2, 'test'],
     *      ['bar', 10, 'test'],
     * ]
     * @param array $keys Column names for the hash, e.g.: ['col1', 'col2', 'col3']
     * @return array Hashed array, e.g.:
     * [
     *      ['col1' => 'foo', 'col2' => 2, 'col3' => 'test']
     *      ['col1' => 'bar', 'col2' => 10, 'col3' => 'test']
     * ]
     */
    public static function convertListToHash($list, $keys)
    {
        $newList = [];
        foreach ($list as $row) {
            $newRow = [];
            foreach ($row as $index => $value) {
                if ($key = ArrayHelper::getValue($keys, $index)) {
                    $newRow[$key] = $value;
                }
            }
            $newList[] = $newRow;
        }
        return $newList;
    }
}
