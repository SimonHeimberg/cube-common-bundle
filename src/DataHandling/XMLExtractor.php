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
     * @var array data read from XML (each array element is one row: in each row key is element name and value is read from xml)  
     */
    protected $readData;
    
    /**
     * Constructor for XML extractor.
     * @param string $xpath path for xpath where subsequent elements are placed
     * @param array $transformations transformations (key is used to get element with the same name on $xpath level and place it under this key in output array)
     */
    public function __construct($xpath, $transformations)
    {
        $this->xpath = $xpath;
        $this->transformations = $transformations;
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
    
    public function readSource()
    {
        $this->readData = array();
        
        foreach ($this->crawler->filterXPath($this->xpath) as $domElement) {
            $readDataElement = array();
            
            foreach ($this->transformations as $elementName => $arrayValue) {
                $readDataElement[$elementName] = $domElement->getElementsByTagName($elementName)->item(0)->nodeValue;
            }
            
            $this->readData[] = $readDataElement;
        }
        
        return $this->readData;
    }
    
}

