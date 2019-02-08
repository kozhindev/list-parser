<?php

namespace kozhindev\ListParser;

/**
 * @property-read array $row
 * @property-read string|null $file
 * @property-read string|null $list
 * @property-read int $index
 * @property-read int $totalCount
 * @property-read int $listCount
 */
interface IRowEvent extends \ArrayAccess
{
}
