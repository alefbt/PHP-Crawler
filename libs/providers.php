<?php
/**
 * Description of providers
 *
 * @author danniel
 */
class Providers {
    const URLS_TYPE_LEAD = 'lead';
    const URLS_TYPE_BLOCKED = 'blocked';
    const URLS_TYPE_INDEXED = 'indexed';
    const URLS_TYPE_ROBOTS_NOT_ALLOWED = 'robots_not_allowed';
    const URLS_TYPE_ERROR_NO_DATA = 'error_no_data';

    /**
     * Get host by url
     * @param string $url
     * @param boolean $force get from database
     * @return array
     */
    public static function get_host_by_url($url,$force=false)
    {
        $cache_id = implode('-', db::create_host_params_from_url($url));        
        if(!CacheManager::getInstance('hosts')->get($cache_id) || $force)
            CacheManager::getInstance('hosts')
            ->set ($cache_id, db::get_host_by_url($url));
        
        return CacheManager::getInstance('hosts')->get($cache_id);
    }
    /**
     * Get host by url
     * @param string $url
     * @param boolean $force get from database
     * @return array
     */
    public static function get_url_by_url($url,$force=false)
    {
        $host = self::get_or_create_host_by_url($url);
        
        if(!$host || !isset($host['id']) || $host['id'] == 0 )
            return false;
        
        $host_id = intval($host['id']);
        
        $cache_id = $host_id.'-'.implode('-', db::create_url_params_from_url($url)); 
        
        if(!CacheManager::getInstance('urls')->get($cache_id) || $force)
            CacheManager::getInstance('urls')
            ->set ( $cache_id, db::get_url_by_url( $host_id,$url));
        
        return CacheManager::getInstance('urls')->get($cache_id);        
    }    
    /**
     * Get or Create Host item by Host Url string
     * @param string $url
     * @return array|boolean
     */
    public static function get_or_create_host_by_url($url)
    {
        $host = self::get_host_by_url($url);        
        if($host) 
            return $host;
        
        db::create_host($url);        
        $host = self::get_host_by_url($url,true);
        
        if($host) 
            return $host;        

        return false;
    }
    /**
     * Get or Create url item by url string
     * @param string $url
     * @return array|boolean
     */
    public static function get_or_create_url_by_url($url)
    {
        if(self::get_url_by_url($url))
            return self::get_url_by_url($url);
        
        
        
        $host = self::get_or_create_host_by_url($url);                              
        db::create_url($host['id'], $url);
        
        $ourl = self::get_url_by_url($url,true);        

        if($ourl) 
            return $ourl;        

        return false;
    }
   

    /** 
     * Getting lead url
     * @return string
     */
    public static function get_lead_urls()
    {
        return db::get_lead_urls();
    }

    /**
     * Check is url is on black list
     * @param string $url
     * @return boolean
     */
    public static function isUrlAllowed($url)
    {
        //@TODO: You can do what ever you want
        return true;
    }
    /**
     * @see const URLS_TYPE_*
     * @param string $url
     * @param mixed $status
     */
    public static function change_url_status($url, $status)
    {
        $u = self::get_url_by_url($url);
        
        if(!$u)
            return false;
        
        db::update_url_status($u['id'], $status);
        return self::get_url_by_url($url,true);
    }
    /**
     * 
     * @param array $urls
     */
    public static function insert_url_list($urls)
    {        
        foreach($urls as $url => $desc)
        {            
            // Actualy i dont care about "$desc" :)
            if(self::get_url_by_url($url))
                continue;
            
            if(Providers::isUrlAllowed($url))
                self::get_or_create_url_by_url($url);            
        }
    }
    /**
     * Creating search item data
     * @param ContentAnalyzer $ca
     * @return type
     */
    public static function create_search_item(ContentAnalyzer $ca)
    {
        $desc = '';
        $meta =$ca->getMetaTags();
        
        if(isset($meta['description']))
            $desc = (is_array ($meta['description']))?implode (' ', $meta['description']):$meta['description'];
                        
        return db::create_fulltext_item($ca->getUrlId(), $ca->getTitle(), $desc,$ca->getPlainContent());        
    }   
}

?>
