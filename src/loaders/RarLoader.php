<?php

namespace kozhindev\ListParser\loaders;

use Exception;
use RarArchive;
use yii\helpers\FileHelper;

class RarLoader extends BaseLoader
{
    /**
     * @inheritdoc
     */
    public $template = '/\.rar$/';

    /**
     * @inheritdoc
     * @throws \yii\base\Exception
     */
    public function load($path)
    {
        // Check PHP extension
        if (!extension_loaded('rar')) {
            return $this->error('Расширение PHP "rar" не установлено');
        }

        // Check file access
        if (!file_exists($path)) {
            return $this->error('Файла не существует!');
        }
        if (!is_readable($path)) {
            return $this->error('Файл не доступен для чтения!');
        }

        // Open rar
        try {
            $rar = RarArchive::open($path);
        } catch (Exception $e) {
            return $this->error($e->getMessage());
        }
        if (!$rar) {
            return $this->error('Неизвестная ошибка Rar');
        }

        // Get entries
        $contentFiles = $rar->getEntries();
        if (!$contentFiles) {
            $rar->close();
            return $this->error('Не удалось получить данные архива Rar');
        }

        // Create temp directory
        $tmp = $this->tempPath . '/' . \Yii::$app->security->generateRandomString(8);
        FileHelper::createDirectory($tmp);

        // Extract
        foreach ($contentFiles as $entry) {
            try {
                $entry->extract($tmp);
            } catch (Exception $e) {
                $rar->close();
                return $this->error($e->getMessage());
            }
        }

        $rar->close();
        return $tmp;
    }
}