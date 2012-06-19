<?php
/**
 * Description of crowler
 *
 * @author danniel
 */
class ContentAnalyzer {
    /**
     * Current URL
     * @var string  
     */
    protected $_url = '';
    /**
     * Full Content
     * @var string
     */
    protected $_content = '';         
    /**
     * All a links
     * @see function getLinks()
     * @var array
     */
    private $_all_links = null;
    /**
     * Plain texts
     * @var string 
     */
    private $_plain_content = null;
    /**
     * Metatags container
     * @var array
     */
    private $meta_tags = null;
    /**
     * Get Title
     * @var string
     */
    private $meta_title = null;
    /**
     * Private Constructor
     * @param string $url
     */
    private function __construct($url) 
    {
        $this->_url = $url;
    }    
    /**
     * Getting Current url id
     * @return boolean|int
     */
    public function getUrlId()
    {
        $url_info = Providers::get_url_by_url($this->_url);
        return ( ! $url_info && isset($url_info['id'])) ? false : $url_info['id'];
    }
    /**
     * Getting crowler
     * @param string $url
     * @return boolean|\Crowler
     */
    public static function getAnalyzer($url)
    {
        
        // Check is robots allowed
        if(!Robots::robots_allowed($url, Config::$agent_name))
        {
            Providers::change_url_status($url, Providers::URLS_TYPE_ROBOTS_NOT_ALLOWED);                        
            _w('Robots not allowed');
            return false;
        }                               

        // Create object
        $obj = new ContentAnalyzer($url);

        if(!$obj->getCONTENT_DATA())
        {
            Providers::change_url_status($url, Providers::URLS_TYPE_ERROR_NO_DATA);                        
            return false;
        }
        
        return $obj;
    }
    /**
     * Getting all content data
     * @return type
     */
    protected function getCONTENT_DATA()
    {
        
        $ch = curl_init($this->_url);
        
        $options = array(
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_ENCODING => 'UTF-8',
            CURLOPT_TIMEOUT=> 30,
            CURLOPT_CONNECTTIMEOUT=>30
        
        );
        curl_setopt_array($ch, $options);
        
        $this->_content = Languages::curl_exec_utf8($ch);                                         
        
        return trim($this->_content)!='';
    }
    /**
     * Extracting links
     * @return array
     */
    public function getLinks()
    {       
        
        if(!$this->_all_links)
            $this->_all_links = urls::create_full_url_list(
                    HTML::extract_links($this->_content), 
                    $this->_url);
        
        return $this->_all_links;
    }
    /**
     * Getting plain text without html tags
     * @return string
     */
    public function getPlainContent()
    {
        if($this->_plain_content)
            return $this->_plain_content;
        
        
        $this->_plain_content = HTML::clear_whitespaces(HTML::stripTags($this->_content));
        
        return $this->_plain_content;                
    }
    /**
     * Getting meta tags from content
     * @return array
     */
    public function getMetaTags()
    {
        if(!$this->meta_tags)
            $this->meta_tags = HTML::getMetaTags($this->_content);
        
        return $this->meta_tags;
    }
    /**
     * Extract Title from html
     * @return string
     */
    public function getTitle()
    {        
        if(!$this->meta_title)
        {
            $btgs = HTML::getTextBetweenTags($this->_content,'title');
            $this->meta_title = (is_array($btgs))?implode (' ', $btgs):$btgs;
        }        

        return $this->meta_title;
    }
}

?>
