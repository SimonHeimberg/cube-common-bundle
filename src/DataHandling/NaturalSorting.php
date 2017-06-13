<?php

namespace CubeTools\CubeCommonBundle\DataHandling;

class NaturalSorting
{
    /**
     * @var integer number of digits for digital part
     */
    protected $integerPartLength = 4;
    
    /**
     * @var integer number of digits for float part
     */
    protected $floatPartLength = 3;
    
    /**
     * @var integer maximum number of chars allowed in string for natural sorting (0 - unlimited)
     */
    protected $naturalSortingValueMaximalLength = 255;
    
    /**
     * Setter for number of digits for digital part.
     * @param integer $integerPartLength number of digits for digital part
     * @return NaturalSorting object, on which this method was executed
     */
    public function setIntegerPartLength($integerPartLength)
    {
        $this->integerPartLength = $integerPartLength;
        return $this;
    }
    
    /**
     * Setter for number of digits for float part.
     * @param integer $floatPartLength number of digits for float part
     * @return NaturalSorting object, on which this method was executed
     */
    public function setFloatPartLength($floatPartLength)
    {
        $this->floatPartLength = $floatPartLength;
        return $this;
    }
    
    /**
     * Setter for maximum number of chars allowed in string for natural sorting.
     * @param integer $naturalSortingValueMaximalLength maximum number of chars allowed in string for natural sorting (0 - unlimited)
     * @return NaturalSorting object, on which this method was executed
     */
    public function setNaturalSortingValueMaximalLength($naturalSortingValueMaximalLength)
    {
        $this->naturalSortingValueMaximalLength = $naturalSortingValueMaximalLength;
        return $this;
    }
    
    /**
     * Callback for natural sorting formatting.
     * @param array $matches
     * @return string
     */
    protected function naturalSortingCallback($matches)
    {
        $integerPart = sprintf('%0' . strval($this->integerPartLength) . 'd', floor($matches[0]));
        
        if(isset($matches[1])){
            $floatPart = sprintf('%0' . strval($this->floatPartLength) . 'd', str_replace('.', '', $matches[1]));
        } else {
            $floatPart = str_repeat('0', $this->floatPartLength);
        }
        
        return $integerPart.$floatPart;
    }
    
    /**
     * Method returning strings for natural formatting.
     * @param string $valueForSorting value before formatting
     * @return string value after formatting, ready to be sorted alphabetically (for example by MYSQL) with same results as natural sorting
     */
    public function getValueForNaturalSorting($valueForSorting)
    {
        $output = preg_replace_callback('/\d+(\.\d+)?/', 
                array($this, 'naturalSortingCallback'), $valueForSorting);
        
        if (is_numeric($this->naturalSortingValueMaximalLength) && $this->naturalSortingValueMaximalLength != '0') {
            $output = substr($output, 0, (intval($this->naturalSortingValueMaximalLength)));
        }
        
        return $output;
    }
}