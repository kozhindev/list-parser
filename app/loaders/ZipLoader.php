<?php

namespace app\loaders;

use yii\helpers\FileHelper;
use ZipArchive;

class ZipLoader extends BaseLoader
{
    /**
     * @inheritdoc
     */
    public $template = '/\.zip$/';

    /**
     * @inheritdoc
     */
    public function load($path)
    {
        // Check PHP extension
        if (!extension_loaded('zip')) {
            return $this->error('Расширение PHP "zip" не установлено');
        }

        // Check file access
        if (!file_exists($path)) {
            return $this->error('Файла не существует!');
        }
        if (!is_readable($path)) {
            return $this->error('Файл не доступен для чтения!');
        }

        // Open archive
        $zip = new ZipArchive();
        $code = $zip->open($path);
        if ($code !== true) {
            return $this->error(static::zipCodeToMessage($code));
        }

        // Create temp directory
        $tmp = $this->tempPath . '/' . \Yii::$app->security->generateRandomString(8);
        FileHelper::createDirectory($tmp);

        if (!$zip->extractTo($tmp)) {
            $zip->close();
            return $this->error('Не удалось разархивировать');
        }

        $zip->close();

        return $tmp;
    }

    /**
     * For translate zip errors code
     *
     * @param number $code
     * @return string
     */
    public static function zipCodeToMessage($code)
    {
        $codes = [
            0 => 'No error',
            1 => 'Multi-disk zip archives not supported',
            2 => 'Renaming temporary file failed',
            3 => 'Closing zip archive failed',
            4 => 'Seek error',
            5 => 'Read error',
            6 => 'Write error',
            7 => 'CRC error',
            8 => 'Containing zip archive was closed',
            9 => 'No such file',
            10 => 'File already exists',
            11 => 'Can\'t open file',
            12 => 'Failure to create temporary file',
            13 => 'Zlib error',
            14 => 'Malloc failure',
            15 => 'Entry has been changed',
            16 => 'Compression method not supported',
            17 => 'Premature EOF',
            18 => 'Invalid argument',
            19 => 'Not a zip archive',
            20 => 'Internal error',
            21 => 'Zip archive inconsistent',
            22 => 'Can\'t remove file',
            23 => 'Entry has been deleted',
        ];

        return isset($codes[$code])
            ? $codes[$code]
            : 'An unknown error has occurred(' . intval($code) . ')';
    }
}