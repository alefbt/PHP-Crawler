<?php
/**
 * Description of configs
 *
 * @author Yehuda Daniel Korotkin
 */
class Config {
    /**
     * Agent name
     * @var string
     */
    public static $agent_name = 'my_php_bot';
    /**
     * Agent host
     * @var string
     */
    public static $agent_host = 'http://changeme.com';
    /**
     * DB CONFIGS
     * @var array
     */
    public static $db_configs = array(
        'read'=>array(
            'connection_string'=>'mysql:host=127.0.0.1;dbname=crowler_db',
            'username'=>'root',
            'password'=>'$%4 my top secret password',
            'port'=>'3306'
        ),
        'write'=>array(
            'connection_string'=>'mysql:host=127.0.0.1;dbname=crowler_db',
            'username'=>'root',
            'password'=>'3@3 my top secret password',
            'port'=>'3306'
        ),
        'fulltext-write'=>array(
            'connection_string'=>'mysql:host=127.0.0.1;dbname=crowler_db',
            'username'=>'root',
            'password'=>'@#@ my top secret password',
            'port'=>'3306'
        )
    );
}



