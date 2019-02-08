<?php

namespace kozhindev\ListParser\parsers;

use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Exception as ReaderException;

use Exception;

class ExcelParser extends BaseParser
{
    /**
     * @inheritdoc
     */
    public $template = '/\.(xlsx|xlsm|xltx|xltm|xls|xlt|ods|ots|slk|xml|gnumeric|htm|html|csv)$/';

    public $limitColumnsRange = false;

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
            $excel = IOFactory::load($path);
        } catch (ReaderException $e) {
            return $this->error($e->getMessage());
        } catch (Exception $e) {
            return $this->error('Ошибка при открытии excel файла');
        }

        // Parse rows
        $sheets = [];
        $totalCount = 0;
        foreach ($excel->getSheetNames() as $name) { // lists
            $currentSheet = $excel->getSheetByName($name);

            if ($this->limitColumnsRange) {
                // Select only first not null columns
                $firstRow = $currentSheet->rangeToArray('A1:' . $currentSheet->getHighestColumn() . '1')[0];
                $lastColumnIndex = static::getLastNotNullIndex($firstRow);
                $range = 'A1:' . Coordinate::stringFromColumnIndex($lastColumnIndex +1) . $currentSheet->getHighestRow();

                $sheetData = $currentSheet->rangeToArray($range);
            } else {
                $sheetData = $currentSheet->toArray();
            }

            foreach ($sheetData as $row) {
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

    public static function getLastNotNullIndex($array, $validateCallback = null)
    {
        $step = 10;
        $keys = array_keys($array);
        $startIndex = 0;
        $lastNotNullIndex = null;

        do {
            $allValuesInStepAreNull = true;

            for ($index = $startIndex; $index < $step + $startIndex; $index++) {
                if (!isset($keys[$index])) {
                    break;
                }

                $value = $array[$keys[$index]];

                $isNull = is_callable($validateCallback)
                    ? call_user_func($validateCallback, $value)
                    : $value === null;

                if (!$isNull) {
                    $lastNotNullIndex = $keys[$index];
                }

                $allValuesInStepAreNull = $allValuesInStepAreNull && $isNull;
            }

            $startIndex += $step;
        } while (!$allValuesInStepAreNull);

        return $lastNotNullIndex;
    }
}
