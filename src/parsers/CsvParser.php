<?php

namespace kozhindev\ListParser\parsers;

use Exception;

class CsvParser extends BaseParser
{
    /**
     * @inheritdoc
     */
    public $template = '/\.csv$/';

    /**
     * Columns delimiter
     * @var string
     */
    public $delimiter;

    /**
     * @inheritdoc
     */
    public function parse($path)
    {
        // Check file access
        if (!file_exists($path)) {
            return $this->error('Файла не существует');
        }
        if (!is_readable($path)) {
            return $this->error('Файл не доступен для чтения');
        }

        // Open file
        try {
            $file = fopen($path, 'r');
        } catch (Exception $error) {
            return $this->error($error->getMessage());
        }

        // Get first row and define delimiter
        $delimiter = $this->delimiter;
        if (!$delimiter) {
            $this->log('Поиск разделителя столбцов...');

            $header = fgets($file);
            if (!$header) {
                $this->log('Файл пуст - пропускаем');
                return [];
            }

            $delimiter = static::getDelimiterCsv($header);
            if (!$delimiter) {
                return $this->error('Разделитель столбцов не найден');
            }
            $this->log('Столбцы разделяются символом: "' . $delimiter . '"');
        }

        // Get total count
        $total = 0;
        while (!feof($file)) {
            $total += substr_count(fread($file, 8192), "\n");
        }
        $this->log('Всего записей: ' . $total);
        $this->setTotalCount($total);

        // Get rows
        $index = 0;
        rewind($file);
        while($rowData = fgetcsv($file,1024, $delimiter)) {
            $this->addRow($rowData, $index++, $path, 'Worksheet');
        }

        $this->log('Готово.');
        fclose($file);

        return true;
    }

    /**
     * Definition delimiter from [',' or ';' or '|']
     * @param $textFile
     * @return mixed|string
     */
    protected static function getDelimiterCsv($textFile)
    {
        $pregTextFile = preg_replace('/".+"/isU', '*', $textFile);
        $delimiters = [',', ';', '|'];
        $resDelimiter = '';
        $resCountParts = -1;

        //find max count parts text
        //for each delimiter
        foreach ($delimiters as $delimiter) {
            if (($countParts = sizeof(explode($delimiter, $pregTextFile))) > $resCountParts) {
                $resCountParts = $countParts;
                $resDelimiter = $delimiter;
            }
        }
        return $resDelimiter;
    }

}