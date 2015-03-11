<?php defined('SYSPATH') or die('No direct script access.');

class Huia_Helper_VivaLinda {

	const HEADER = "topo";
    const FOOTER = "rodape";
    const DOMAIN_VIVA_LINDA  = 'http://vivalinda.boticario.com.br/';
    const STAMP_ID = 1375;

    protected static function _cache($key, $value = NULL)
    {
        $result = Cache::instance()->get($key);
        if ($value != NULL)
        {
            $result = $value;
            Cache::instance()->set($key, $value);
        }
        return $result;
    }

    protected static function _get_content($url)
    {    	
        $cache = self::_cache($url);        
        if ($cache == NULL)
        {
            try
            {
                $request = Request::factory($url)->execute()->body();               
                if ($request == NULL)
                {
                    return "";
                }
                $cache = self::_cache($url, $request);
            }
            catch (Exception $e)
            {
                return "";
            }
        }
        return $cache;
    }

    protected static function _get_asset($url, $class = "")
    {
        $url = self::DOMAIN_VIVA_LINDA.$url;
        $html = self::_get_content($url);               
        $html = str_replace('replace_class', $class, $html);      
        return $html;        
    }

    public static function get_header($class = "")
    {
        return self::_get_asset(self::HEADER, $class);
    }

    public static function get_footer($class = "")
    {
        return self::_get_asset(self::FOOTER, $class);
    }

    public static function get_css()
    {
        $url = self::DOMAIN_VIVA_LINDA."wp-content/themes/twentyfourteen/css/footer-shared.css";
        $css = self::_get_content($url);

        $url = self::DOMAIN_VIVA_LINDA."wp-content/themes/twentyfourteen/css/header-shared.css";
        $css = $css . " " . self::_get_content($url);

        if ($css != null)
        {
        	$css = str_replace('../images',  self::DOMAIN_VIVA_LINDA . 'wp-content/themes/twentyfourteen/images', $css);
        }              
        return $css;
    }

    public static function get_js()
    {
        $url = self::DOMAIN_VIVA_LINDA."wp-content/themes/twentyfourteen/js/header-shared.js";
        return self::_get_content($url);
    }

    public static function get_news($page = 1, $rows = 18)
    {    
        $news = self::_cache('news'.$page.'rows'.$rows);
        
        if(!$news)
        {
            $url = self::DOMAIN_VIVA_LINDA.'wp-json/posts_by_stamp?stamp='.self::STAMP_ID.'&page='.$page.'&rows='.$rows;
            $json = json_decode(Request::factory($url)->execute()->body());
            $news = $json->posts;
            self::_cache('news'.$page.'rows'.$rows, $news);            
        }
        
        return $news;
    }

    public static function category_pagination($category, $offset = 0, $filter = NULL, $most_popular = 0, $limit = 6, $orderby = "date")
    {
        $key = 'Vivalinda::category_pagination->' . join(func_get_args(), '.');

        $cache = Cache::instance()->get($key);
        
        if ($cache === NULL OR ! Kohana::$caching)
        {
            $url = self::DOMAIN_VIVA_LINDA . 'wp-admin/admin-ajax.php?action=category_pagination';
            
            $request = Request::factory($url);
            $request->method(Request::POST);
            $request->post('category', $category);
            $request->post('most_popular', $most_popular);
            $request->post('limit', $limit);
            $request->post('offset', $offset);
            $request->post('orderby', $orderby);
            if ($filter)
            {
                $request->post('filter', $filter);
            }
            $request->query('json', 1);

            $response = $request->execute();

            $cache = (array) @json_decode($response->body());

            $cache['pagination'] = array(
                'key' => $key,
                'current_page' => $offset + 1,
                'limit' => $limit,
                'first_page' => $offset == 0,
                'last_page' => (($offset + 1) * $limit) >= $cache['count'],
                'total_pages' => round($cache['count'] / $limit)
            );
            
            Cache::instance()->set($key, $cache, 60);
        }

        return $cache;
    }

}
