<?php
/**
 * Managing cron service
 *
 * @author Yehuda Daniel Korotkin
 */
class CronManager {
    /**
     * singleton object
     * @var CronManager 
     */
    private static $singleton = null;
    /**
     * Singleton RunID
     * @var string 
     */
    private $RUN_ID = '';
    /**
     * is object has inited
     * @var boolean 
     */
    private $init = false;
    /**
     * Cront process index
     * @var int 
     */
    private $cron_process_index = 0;
    /**
     * Cron proccess index 
     * @return int
     */
    public static function getCronProcessIndex()
    {
        if(!self::$singleton)
            return 0;
        
        return self::$singleton->cron_process_index;
    }

    /**
     * singleton constructor
     * @param string $run_id
     */
    private function __construct($run_id='cron',$max_proccesses=1) 
    {   
        $is_all_processes_used = true;
        
        for($spid = 1; $spid <= $max_proccesses ; $spid++)
        {
            $this->RUN_ID = md5("$run_id-$spid");

            if(!is_file(WRITABLE_PATH . "/{$this->RUN_ID}.run.pid"))
            {
                $this->cron_process_index = $spid;
                $is_all_processes_used = false;
                break;
            }

        }
        
        if($is_all_processes_used)
        {
            _w('Cron alerady running ... Wait for next time');            
            exit(2);
        }
          
        
        try
        {
            @file_put_contents(WRITABLE_PATH . "/{$this->RUN_ID}.run.pid", 'x'.getmypid());
            
            if(!is_file(WRITABLE_PATH . "/{$this->RUN_ID}.run.pid"))
                throw new Exception('Cannot create file');
            
            $this->init = TRUE;
        }
        catch(Exception $ex)
        {
            _w('ERROR !!! Cannot create pid file ! ' . $ex->getMessage() );
            exit(2);
        }        
        
        register_shutdown_function(array($this, 'callRegisteredShutdown'));
    }
    /**
     * Shutdown func.
     * @throws Exception
     */
    public function callRegisteredShutdown() 
    {
        if(!$this->init)
        {
            _w('shutting down - no need');
            return;
        }
        
        try
        {            
            _w('shutting down');
            unlink(WRITABLE_PATH . "/{$this->RUN_ID}.run.pid");
            if(is_file(WRITABLE_PATH . "/{$this->RUN_ID}.run.pid"))
                throw new Exception('Cannot REMOVE file');
        }
        catch(Exception $ex)
        {
            _w('ERROR !!! Cannot REMOVE pid file ! ' . $ex->getMessage() );            
        }             
    }
    /**
     * Geting singleton
     * @param string $run_id
     * @return CronManager
     */
    private static function getSingleton($run_id='cron',$max_processes=1)
    {
        if(!self::$singleton)
            self::$singleton = new CronManager($run_id,$max_processes);
        
        return self::$singleton;
    }

    /**
     * Check mandatory configs
     */
    public static function check_configs()
    {
        if(!is_dir(WRITABLE_PATH))
        {
            _w('No WRITABLE dir : ' . WRITABLE_PATH);
            try
            {
                @mkdir(WRITABLE_PATH, 0777);
            }
            catch(Exception $ex)
            { 
                _w('ERROR !!!' . $ex->getMessage());
            }
            
            if(!is_dir(WRITABLE_PATH))
            {
                _w('ERROR !!! Cannot Create dir !');
                exit(2);
            }
        }        
    }

    /**
     * init function
     * @param string $_prefix
     * @param int $max_processes
     * @return CronManager
     */
    public static function init($_prefix='cron',$max_processes=1)
    {        
        CronManager::check_configs();
        return CronManager::getSingleton($_prefix,$max_processes);                
        
    }
}

?>
