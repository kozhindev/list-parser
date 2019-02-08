<?php

namespace kozhindev\ListParser\loaders;

class FolderLoader extends BaseLoader
{
    /**
     * @inheritdoc
     */
    public $template = '/^[^.]+$/';

    /**
     * @inheritdoc
     */
    public function load($path)
    {
        // Check file access
        if (!is_dir($path)) {
            return $this->error('Это не директория');
        }
        if (!is_readable($path)) {
            return $this->error('Директория не доступена для чтения!');
        }

        $files = [];
        foreach (scandir($path) as $dirElement) {
            if (!in_array($dirElement, ['.', '..'])) {
                $child = $path . DIRECTORY_SEPARATOR . $dirElement;
                if (is_dir($child)) {
                    $files = array_merge($files, $this->load($child));
                } else {
                    $files[] = $child;
                }
            }
        }
        return $files;
    }
}