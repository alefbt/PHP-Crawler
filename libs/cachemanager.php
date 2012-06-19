<?php
/**
 * Description of cachemanager
 *
 * @author danniel
 */
class CacheManager {
    /**
     * instances list
     * @var array
     */
    private static $_instances = array();
    /**
     * Singleton
     */
    private function __clone() { }    
    /**
     * Singleton
     * @param string $instance_name
     * @return CacheManager
     */
    public static function getInstance($instance_name = 'default')
    {
        if(!isset(self::$_instances[$instance_name]))
            self::$_instances[$instance_name] = new CacheManager($instance_name);
        
        return self::$_instances[$instance_name];
    }

    private $instance_name= '';

    /**
     * Singleton
     */
    private function __construct($instance_name) 
    { 
        $this->instance_name = $instance_name;
    }
    /**
     * Cahce object
     * @var array 
     */
    private $_cache = array();
    /**
     * Setting cache obje
     * @param string $name
     * @param object $value
     */
    public function set($name,$value)
    {
        $this->_cache[$name] = $value;
    }
    /**
     * 
     * @param type $name
     * @return type
     */
    public function get($name)
    {
        $nn = (string)$name;
        if(!isset($this->_cache[$nn]))
            return null;
        
        return $this->_cache[$nn];
    }
}

?>
