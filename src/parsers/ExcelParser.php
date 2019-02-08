<?php

namespace kozhindev\ListParser\parsers;

use PHPExcel_IOFactory;
use PHPExcel_Reader_Exception;
use Exception;

class ExcelParser extends BaseParser
{
    /**
     * @inheritdoc
     */
    public $template = '/\.(xlsx|xlsm|xltx|xltm|xls|xlt|ods|ots|slk|xml|gnumeric|htm|html|csv)$/';

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
            $excel = PHPExcel_IOFactory::load($path);
        } catch (PHPExcel_Reader_Exception $e) {
            return $this->error($e->getMessage());
        } catch (Exception $e) {
            return $this->error('Ошибка при открытии excel файла');
        }

        // Parse rows
        $sheets = [];
        $totalCount = 0;
        foreach ($excel->getSheetNames() as $name) { // lists
            foreach ($excel->getSheetByName($name)->toArray() as $row) {
                foreach ($row as $value) {
                    if ($value !== null) {
                        $sheets[$name][] = $row;
                        $totalCount++;
                        break;
                    }
                }
            }
        }
        $this->setTotalCount($totalCount);

        foreach ($sheets as $name => $rows) {
            $listCount = count($rows);
            foreach ($rows as $index => $row) {
                $this->addRow($row, $index, $path, $name, $listCount);
            }
        }

        return true;
    }
}
