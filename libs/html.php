<?php
/**
 * HTML Manipulators
 *
 * @author Yehuda Daniel Korotkin
 */
class HTML {
    /**
     * convert symbols to chars
     * @param string $string
     * @return string
     */
    public static function convert_spatial_symbols($string)
    {
        return str_replace(
                array(
                    '&acute;',
                    "&quot;",
                    '&apos;',
                    '&amp;',
                ), 
                array(
                    "'",
                    '"',
                    "'",
                    '&',
                ), 
                $string);
    }
    /**
     * Clear white spaces
     * @param string  $string
     * @return string
     */
    public static function clear_whitespaces($string)
    {
        return trim(preg_replace('/\s+/', ' ', $string));
    }
    /**
     * extract meta tags from html
     * @param string $html
     * @return array
     */
    public static function getMetaTags($html)
    {
        $matches = array();
        preg_match_all('/<[\s]*meta[\s]*name="?' . '([^>"]*)"?[\s]*' . 'content="?([^>"]*)"?[\s]*[\/]?[\s]*>/si', $html, $matches);        
        if(count($matches[0])>0)
            return array_combine($matches[1], $matches[2]);
        else
            return array();
       
    }

    public static function getTextBetweenTags($string, $tagname)
    {
        $pattern = "/<$tagname>(.*?)<\/$tagname>/";
        preg_match($pattern, $string, $matches);
        if(isset($matches[1]))
            return $matches[1];
        else
            return array();
    }
    /**
     * Getting plain text from html
     * @param string $content
     * @return string
     */
    public static function stripTags($content)
    {        
        //return strip_tags ( $content ); 

        $search = array ("'<script[^>]*?>.*?</script>'si",  // Strip out javascript 
                 "'<style[^>]*?>.*?</style>'si",
                 "'<[/!]*?[^<>]*?>'si",          // Strip out HTML tags 
                 "'([rn])[s]+'",                // Strip out white space 
                 "'&(quot|#34);'i",                // Replace HTML entities 
                 "'&(amp|#38);'i", 
                 "'&(lt|#60);'i", 
                 "'&(gt|#62);'i", 
                 "'&(nbsp|#160);'i", 
                 "'&(iexcl|#161);'i", 
                 "'&(cent|#162);'i", 
                 "'&(pound|#163);'i", 
                 "'&(copy|#169);'i", 
                 "'&#(d+);'e");                    // evaluate as php 

        $replace = array (" ", 
                        " ", 
                        " ", 
                        " ", 
                        " \" ", 
                        " & ", 
                        " ", 
                        " ", 
                        " ", 
                        ' ', 
                        ' ', 
                        ' ', 
                        ' ', 
                        ' ');                     
        return strip_tags ( HTML::clear_whitespaces(preg_replace($search, $replace, $content)) ); 
    }
    /**
     * 
     * @param string $tag
     * @param string $key_attribute
     * @param string $html
     * @return array
     */
    public static function extract_tag_values($tag,$key_attribute,$html)
    {
        $matches = array();
        $reg = "#<$tag.*$key_attribute\s*=\s*(\"|')?([^\"'>]+).*>(.+)</$tag>#i";
        if (preg_match_all($reg, $html, $matches)) 
            return array_combine($matches[2], $matches[3]);  
        
        return array();        
    }
    /**
     * getting all <IFRAME SRC="{LINK}"></SRC> 
     * @param string $html
     * @return array
     */
    public static function extract_iframe_links($html)
    {
        return HTML::extract_tag_values('iframe', 'src', $html);        
    }
    /**
     * getting all <A HREF="{LINK}">{aa}</A> 
     * @param string $html
     * @return array
     */
    public static function extract_links($html)
    {
        return HTML::extract_tag_values('a', 'href', $html);        
    }
}

?>
