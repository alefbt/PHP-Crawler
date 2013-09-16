<?php
/**
 * Description of robots
 *
 * @author danniel
 */
class Robots {
    /**
     * all robots
     * @var array 
     */
    protected static $robots = array();
    /**
     * Check if robots allowed
     * 
     * @see http://www.the-art-of-web.com/php/parse-robots/
     * @param type $url
     * @param type $useragent
     * @return boolean
     */
    public static function robots_allowed($url, $useragent=false)
    {
        // parse url to retrieve host and path
        $parsed = parse_url($url);
        
        $agents = array(preg_quote('*'));
        if($useragent) $agents[] = preg_quote($useragent, '/');
        $agents = implode('|', $agents);

        if(isset(self::$robots[$parsed['host']]))
        {
            $robotstxt = self::$robots[$parsed['host']];
        }
        else
        {
            
            // location of robots.txt file, only pay attention to it if the server says it exists
            if(function_exists('curl_init')) 
            {
                $handle = curl_init("http://{$parsed['host']}/robots.txt");
            
                curl_setopt($handle,  CURLOPT_RETURNTRANSFER, TRUE);
            
                $response = curl_exec($handle);
                $httpCode = curl_getinfo($handle, CURLINFO_HTTP_CODE);
            
                if($httpCode == 200) 
                {
                    $robotstxt = explode("\n", $response);
                } 
                else 
                {
                    $robotstxt = false;
                }
                
                curl_close($handle);
            } 
            else 
            {
                $robotstxt = @file("http://{$parsed['host']}/robots.txt");        
            }
            
            if($robotstxt)
                self::$robots[$parsed['host']] = $robotstxt;
        }

    // if there isn't a robots, then we're allowed in
    if(empty($robotstxt)) return true;

    
    $rules = array();
    $ruleApplies = false;
    
    foreach($robotstxt as $line) 
    {    
        // skip blank lines
        if(!$line = trim($line)) continue;

        // following rules only apply if User-agent matches $useragent or '*'
        if(preg_match('/^\s*User-agent: (.*)/i', $line, $match)) 
        {
            $ruleApplies = preg_match("/($agents)/i", $match[1]);
            continue;
        }
        
        if($ruleApplies) 
        {            
            $rule = explode(':', $line, 2);
            
            $type = trim(strtolower($rule[0]));
                        
            // add rules that apply to array for testing
            if(count($rule)>1)
            {
                $rules[] = array(
                    'type' => $type,
                    'match' => str_replace('\*', '.*',preg_quote(trim($rule[1]), '/'))
                );
            }
        }
    }

    $isAllowed = true;    
    $cu_st = 0;
    
    foreach($rules as $rule) 
    {
        
        // check if page hits on a rule
        if( @preg_match("/^{$rule['match']}/", $parsed['path']) ) 
        {       
            
            // prefer longer (more specific) rules and Allow trumps Disallow if rules same length        
            $strength = strlen($rule['match']);
        
            if($cu_st < $strength) 
            {
                $cu_st = $strength;
                $isAllowed = ($rule['type'] == 'allow') ? true : false;
            } 
            elseif($cu_st == $strength && $rule['type'] == 'allow') 
            {
                $cu_st = $strength;
                $isAllowed = true;
            }
        }
    }

    return $isAllowed;
  }
}

?>
