<?php
/**
 * General Utils
 *
 * @author Yehuda Daniel Korotkin
 */
class urls {
    /**
     * Create URL
     * @param type $array
     * @return type
     */
    public static function create_url($array)
    {
        return (isset($array['https'])&&$array['https']==true?'https':'http')
            .'://' . $array['host'] . (isset($array['port'])&&$array['port']!=80?':'.$array['port']:'')
            . (isset($array['path'])?$array['path']:'/')
            . (isset($array['get_params'])&&$array['get_params']?'?'.$array['get_params']:'');
    }
    /**
     * From list as
     *  array('url'=>'some name','url2'=>'some name'); 
     * to 
     *  real full url list
     * @param array $url_list
     * @param string $real_url
     * @return array
     */
    public static function create_full_url_list($url_list,$real_url)
    {
        $realUrl = parse_url($real_url);        
        
        $newArr = array();
        
        foreach ($url_list as $_key=>$_value) 
        {
            $key =   HTML::convert_spatial_symbols(HTML::clear_whitespaces($_key));
            $value = HTML::convert_spatial_symbols(HTML::clear_whitespaces($_value));

            /**
             * Skip all hashes
             */
            if($key=='#') continue;
            /**
             * ignore javascript
             */
            if(substr(strtolower($key), 0, strlen('javascript:'))=='javascript:')                    
            {
                continue;
            }                

            /**
             * Add as-it-is to list
             */
            if(substr(strtolower($key), 0, 7)=='http://')
            {
                $newArr[$key]=$value;
                continue;
            }                

            /**
             * Add as-it-is to list
             */
            if(substr(strtolower($key), 0, 8)=='https://')
            {
                $newArr[$key]=$value;
                continue;
            }                


            /**
             * Check is relative url from ROOT
             */
            if(substr($key, 0, 1)=='/')
            {
                $newUrl = $realUrl['scheme'] . '://' .$realUrl['host'] . 
                        (isset($realUrl['port'])?':'.$realUrl['port']:'') . $key;
                $newArr[$newUrl]=$value;
                continue;
            }

            
            $xUrl =array();
            if(isset($realUrl['path']))
            {
                $xUrl = explode('/', $realUrl['path']);
                if(count($xUrl)>0)
                    unset($xUrl[count($xUrl)-1]);
            }  
            else 
            {
                if(substr($key, 0, 2)=='..')
                    continue;
            }
            
            $nUrl=$realUrl['scheme'] . '://' .$realUrl['host'] . 
                        (isset($realUrl['port'])?':'.$realUrl['port']:'').implode('/', $xUrl);
            $newArr["$nUrl/$key"]=$value;
        }
        
        return $newArr;
    }
}

?>
