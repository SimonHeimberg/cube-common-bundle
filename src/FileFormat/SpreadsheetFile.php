<?php

namespace CubeTools\CubeCommonBundle\FileFormat;

class SpreadsheetFile
{
    /**
     * Iterates over the cell values of the worksheet.
     *
     * Iterates over each row and returns the values as array.
     *
     * @param \PHPExcel_Worksheet $xlSheet   worksheet to iterate over
     * @param type                $startCell defaults to 'A1'
     * @param type                $endCell   defaults to lowermost outermost cell
     *
     * @return \Iterable
     */
    public static function iterateOverTable(\PHPExcel_Worksheet $xlSheet, $startCell = 'A1', $endCell = null)
    {
        if (null === $endCell) {
            $endCol = $xlSheet->getHighestDataColumn();
            $endRow = $xlSheet->getHighestDataRow();
        } else {
            $endCol = $endCell[0];
            $endRow = $endCell[1];
        }
        $startCol = $startCell[0];
        $startRow = $startCell[1];

        $colsTempl = array();
        for ($col = $startCol; $col <= $endCol; ++$col) {
            $colsTempl[$col] = null;
        }
        for ($row = $startRow; $row <= $endRow; ++$row) {
            $oneRow = $colsTempl;
            for ($col = $startCol; $col <= $endCol; ++$col) {
                $oneRow[$col] = $xlSheet->getCell($col.$row)->getValue();
            }
            yield $row => $oneRow;
        }
    }
}
