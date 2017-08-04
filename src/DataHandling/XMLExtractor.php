<?php
namespace CubeTools\CubeCommonBundle\DataHandling;

use Symfony\Component\DomCrawler\Crawler;

class XMLExtractor
{
    /**
     * @var \Symfony\Component\DomCrawler\Crawler
     */
    protected $crawler;
    
    /**
     * @var string path where iteration is made
     */
    protected $xpath;
    
    /**
     * @var array key is column name (value not important); key is used to get element with the same name on $xpath level and place it under this key in output array
     */
    protected $transformations;
    
    /**
     * @var array key is name of XML source element, value - name of column in output 
     */
    protected $translations;
    
    /**
     * @var array data read from XML (each array element is one row: in each row key is element name and value is read from xml)  
     */
    protected $readData;
    
    /**
     * Constructor for XML extractor.
     * @param string $xpath path for xpath where subsequent elements are placed
     * @param array $transformations transformations (key is used to get element with the same name on $xpath level and place it under this key in output array)
     * @param array $translations key is name of XML source element, value - name of column in output 
     */
    public function __construct($xpath, $transformations, $translations=array())
    {
        $this->xpath = $xpath;
        $this->transformations = $transformations;
        $this->translations = $translations;
    }
    
    /**
     * Method providing XML content for object.
     * @param string $source XML content to be parsed
     * @return \Ebbe\TestManager\EvaluationBundle\Logic\XMLExtractor object, on which this method was executed
     */
    public function setSource($source)
    {
        $this->crawler = new Crawler($source);
        return $this;
    }
    
    /**
     * Example:
     *      transformation has element 'country'
     *      method is executed with ('location', 'country')
     *      in that case we will be looking for element 'location' in XML and output it as 'country'
     *      in XML we will not be looking for 'country' element anymore
     * Convenient to use if we want dynamicaly change source of data without changing further code.  
     * @param string $sourceElementName name of element in XML source
     * @param string $outputElementName name of element in output from XML extraction (can replace current source element)
     * @return \Ebbe\TestManager\EvaluationBundle\Logic\XMLExtractor object, on which this method was executed
     */
    public function addOrReplaceSourceElement($sourceElementName, $outputElementName=null)
    {
        if (is_null($outputElementName)) {
            $outputElementName = $sourceElementName;
        }
        
        if (isset($this->transformations[$outputElementName])) {
            unset($this->transformations[$outputElementName]);
        }
        
        $this->transformations[$sourceElementName] = true;  // value is not important
        $this->addSourceTranslation($sourceElementName, $outputElementName);
        return $this;
    }
    
    /**
     * Method for adding translations.
     * @param string $sourceElementName name of element in XML source
     * @param string $outputElementName name of element in output from XML extraction
     * @return \Ebbe\TestManager\EvaluationBundle\Logic\XMLExtractor object, on which this method was executed
     */
    public function addSourceTranslation($sourceElementName, $outputElementName)
    {
        $this->translations[$sourceElementName] = $outputElementName;
        return $this;
    }
    
    /**
     * Method extracting data from XML
     * @return array extracted data from XML
     */
    public function readSource()
    {
        $this->readData = array();
        
        foreach ($this->crawler->filterXPath($this->xpath) as $domElement) {
            $readDataElement = array();
            
            foreach ($this->transformations as $elementName => $arrayValue) {
                if(stripos($elementName, '/@') !== false){
                    $elementArray = explode('/@', $elementName);
                    $elementNodeName = $elementArray[0];
                    $elementAttribute = $elementArray[1];
                                        
                    if (empty($elementNodeName)) { // getting attribute from actual element
                        $elementDom = $domElement;
                    } else {
                        $elementDom = $domElement->getElementsByTagName($elementNodeName)->item(0);
                    }
                    
                    $elementValue = $elementDom->getAttribute($elementAttribute);                    
                } else {
                    $elementValue = $domElement->getElementsByTagName($elementName)->item(0)->nodeValue;
                }      
                
                if (isset($this->translations[$elementName])) { // make translation
                    $elementKey = $this->translations[$elementName];
                } else {
                    $elementKey = $elementName;
                }
                
                $readDataElement[$elementKey] = $elementValue;
            }
            
            $this->readData[] = $readDataElement;
        }
        
        return $this->readData;
    }
    
}

