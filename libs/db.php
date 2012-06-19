<?php
/**
 * Description of db
 *
 * @author danniel
 */
class db {
    /**
     * Instances
     * @var array 
     */
    private static $_instances = array();
    /**
     * Mysqli Driver
     * @var PDO 
     */
    private $_db = null;
    /**
     * Configuration name
     * @var string
     */
    private $_config_name='';    
    /** 
     * getting instance
     * @param string $config_name
     * @return db|null
     */
    protected static function getInstance($config_name)
    {
        if(isset(db::$_instances[$config_name]))
            return db::$_instances[$config_name];
        
        if(!isset(Config::$db_configs[$config_name]))
            return null;
        
        return db::$_instances[$config_name] = new db($config_name);        
    }    
    /**
     * Singleton
     * @param string $config_name
     */
    private function __construct($config_name) 
    {
        $this->_config_name = $config_name;
    }
    /** 
     * Getiing current connection
     * @return PDO|null
     */
    private function getConnection()
    {
        if(!$this->_db)
            if(!$this->load_connection ())
                return null;
        
        return $this->_db;
    }
    /** 
     * Loading connection
     * @return boolean
     */
    private function load_connection()
    {
        if(!isset(Config::$db_configs[$this->_config_name]))
            return false;
                
        $config = Config::$db_configs[$this->_config_name];
        
        $this->_db = new PDO( 
                        $config['connection_string'], 
                        $config['username'], 
                        $config['password'], 
                        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8") 
        );
        
        return true;
        

    }
    /** 
     * Prepare read sql
     * @param string $sql
     * @param array|null $input_parmas
     * @return PDOStatement
     */
    private static function getRead_PrepareSql($sql,$input_parmas=null)
    {
        $pdo = self::getInstance('read')->getConnection();        
        $sth = $pdo->prepare($sql);
        $sth->execute($input_parmas);
        return $sth;
    }
    /**
     * Prepare write sql
     * @param string $sql
     * @param array|null $input_parmas
     * @return PDOStatement
     */
    private static function getWrite_PrepareSql($sql,$input_parmas=null)
    {
        $pdo = self::getInstance('write')->getConnection();        
        $sth = $pdo->prepare($sql);
        $sth->execute($input_parmas);
        return $sth;
    }
    /**
     * Prepare write fulltext sql
     * @param string $sql
     * @param array|null $input_parmas
     * @return PDOStatement
     */
    private static function getWriteFT_PrepareSql($sql,$input_parmas=null)
    {
        $pdo = self::getInstance('fulltext-write')->getConnection();        
        $sth = $pdo->prepare($sql);
        $sth->execute($input_parmas);
        return $sth;
    }
    /** 
     * Get Leads url
     * @return array
     */
    public static function get_lead_urls()            
    {
        $sql_statement = self::getRead_PrepareSql('SELECT * FROM random_leads');
        return $sql_statement->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * get host record by url
     * @param string $url
     * @return array|false
     */
    public static function get_host_by_url($url)
    {
        $params = self::create_host_params_from_url($url);        
        $sql_statement = self::getRead_PrepareSql(
                'SELECT * 
                    FROM hosts 
                    WHERE 
                        ( https=:https )
                    AND ( port =:port  )
                    AND ( host =:host  )
                 LIMIT 1',$params);
        
        
        return ($sql_statement->fetch(PDO::FETCH_ASSOC));
    }    
    /**
     * Creating params
     * @param string $url
     * @return array
     */
    public static function create_host_params_from_url($url)
    {        
        $parsed_url = parse_url($url);        
        $params = array();
        $params['port']  = (isset($parsed_url['port'])&&$parsed_url['port']!=80) ? $parsed_url['port'] : 80;
        $params['https'] = (isset($parsed_url['scheme'])&&$parsed_url['scheme']=='https')?1:0;
        $params['host']  = $parsed_url['host'];
        return $params;
    }
    /**
     * Creating params
     * @param string $url
     * @return array
     */
    public static function create_url_params_from_url($url)
    {        
        $parsed_url = parse_url($url);        
        $params = array();
        $params['path']  = (isset($parsed_url['path'])) ? $parsed_url['path'] : '';
        $params['query']  = (isset($parsed_url['query'])) ? $parsed_url['query'] : null;
        
        return $params;
    }
    /**
     * 
     * @param int $host_id
     * @param type $url
     */
    public static function get_url_by_url($host_id, $url)
    {
        $params = self::create_url_params_from_url($url);    
        $params['host_id']=$host_id;
        
        $params_EQ = '';
        if(isset($params['query']) && $params['query'] )
        {
            $params_EQ = "AND get_params = :query ";
        }
        else
        {
            $params_EQ = "AND get_params IS NULL ";
            unset($params['query']);
        }
            
        $sql_statement = self::getRead_PrepareSql(
                "SELECT * 
                    FROM urls 
                    WHERE 
                        ( host_id    = :host_id )
                    AND ( path       = :path  )
                    $params_EQ
                    
                 LIMIT 1",$params);     

        return ($sql_statement->fetch(PDO::FETCH_ASSOC));        
    }
    /**
     * creating url
     * @param int $host_id
     * @param string $url
     * @return mixed
     */
    public static function create_url($host_id, $url)
    {
        $params = self::create_url_params_from_url($url);
        $params['host_id']=$host_id;
        self::getWrite_PrepareSql(
                "INSERT IGNORE urls 
                    (  host_id,  path,  get_params, type  ) 
                 VALUES 
                    ( :host_id, :path, :query ,     'lead'  )",
                $params);      
        return self::getInstance('write')->getConnection()->lastInsertId();
    }

    /**
     * Create host in host  table
     * @param string $url
     */
    public static function create_host($url)
    {
        $params = self::create_host_params_from_url($url);
        
        @self::getWrite_PrepareSql(
                'INSERT IGNORE hosts 
                    (  https,  host,  port ) 
                 VALUES 
                    ( :https, :host, :port )',
                $params);      
             
        return self::getInstance('write')->getConnection()->lastInsertId();
    }
    /**
     * update url status
     * @param int $url_id
     * @param string $status
     */
    public static function update_url_status($url_id,$status)
    {
        $params = array('status'=>$status,'url_id'=>$url_id);
        
        @self::getWrite_PrepareSql(
                'UPDATE urls SET type = :status WHERE id = :url_id',
                $params);     
        
        return self::getInstance('write')->getConnection()->lastInsertId();
    }

    /**
     * create fulltext data
     * @param int $url_id
     * @param string $title
     * @param string $description
     * @param string $text
     * @return mixed
     */
    public static function create_fulltext_item($url_id, $title,$description,$text)
    {
        $params = array(
            'url_id'        =>$url_id,
            'title'         =>$title,
            'description'   =>$description,
            'text'          =>$text
        );
        
        @self::getWriteFT_PrepareSql(
                'INSERT INTO search_table 
                    ( url_id, title, description, text ) 
                    VALUES 
                    (
                        :url_id , 
                        :title , 
                        :description ,
                        :text 
                    )
                 ON DUPLICATE KEY UPDATE 
                 title = :title, 
                 description = :description,
                 text = :text 
                ',                
        $params);   

        return self::getInstance('fulltext-write')->getConnection()->lastInsertId();
    }    
}

?>
