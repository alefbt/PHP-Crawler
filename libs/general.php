<?php
define('ROOT_PATH', dirname(dirname(__FILE__)));
define('WRITABLE_PATH', ROOT_PATH . '/writable');

// Auto loader
spl_autoload_register(function ($class) {
    include ROOT_PATH . '/libs/' 
            . str_replace('_', DIRECTORY_SEPARATOR, strtolower($class))  . '.php';
});


// Write out
function _w($t,$show_styled=true)
{
    $text = '';
    if($show_styled)
        $text = "CRON#" . CronManager::getCronProcessIndex () . " [" .date ("d H:i:s") . "] \t - $t.\n";
    else
        $text = $t;
        
    if(defined('STDOUT'))
        fwrite(STDOUT, $text);     
    else
        echo $text;    
}

ini_set('user_agent', Config::$agent_name . ' (' . Config::$agent_host . ')');

