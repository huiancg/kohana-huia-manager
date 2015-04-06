<?php defined('SYSPATH') or die('No direct script access.');

// unique salt
Cookie::$salt = md5(Kohana::$base_url . '342353a7be5589aa0bed2ef76ce1a4a1');

// DB by env
Database::$default = Kohana::$environment;

// global create dir
function create_dir($path)
{
	if ( ! is_dir($path))
	{
		try
		{
			// Create the directory
			mkdir($path, 0755, TRUE);

			// Set permissions (must be manually set to fix umask issues)
			chmod($path, 0755);
		}
		catch (Exception $e)
		{
			throw new Kohana_Exception('Could not create directory :dir',
				array(':dir' => Debug::path($path)));
		}
	}
}

// create log path
create_dir(APPPATH.'logs/'.gethostname());

// Auto base_url
if (Kohana::$base_url === '/')
{
    $cache = (Kohana::$caching) ? Kohana::cache('Kohana::$base_url') : FALSE;
    if ( ! $cache)
    {
        preg_match('/index.php[\/]*(.*)/', ( ! empty($_SERVER['SUPHP_URI'])) ? $_SERVER['SUPHP_URI'] : $_SERVER['PHP_SELF'], $match);
        $protocol = '';
        $base_url = preg_split("/\?/", str_ireplace(((isset($match[1])) ? trim($match[1], '/') : ''), '', urldecode(trim((( ! empty($_SERVER['REQUEST_URI'])) ? $_SERVER['REQUEST_URI'] : '/'), '/'))));
        $port = (Arr::get($_SERVER, 'SERVER_PORT', 80) != 80) ? ':'.Arr::get($_SERVER, 'SERVER_PORT') : ''; 
        $cache = trim(sprintf("http".$protocol."://%s/%s", (( ! empty($_SERVER['HTTP_HOST'])) ? $_SERVER['HTTP_HOST'] : 'localhost'.$port), reset($base_url)),'/') . '/';
        unset($match, $base_url);
        
        if (Kohana::$caching)
        {
            Kohana::cache('Kohana::$base_url', $cache);
        }
    }
    Kohana::$base_url = $cache;
}
	
Route::set('manager_parent', 'manager/<parent>/<parent_id>/<controller>(/<action>(/<id>))', array(
		'id' => '\d+',
		'parent_id' => '\d+',
	))
	->defaults(array(
		'controller' => 'user',
		'action'     => 'index',
		'directory' => 'manager'
	));

Route::set('manager', 'manager(/<controller>(/<action>(/<id>)))', array(
		'id' => '\d+'
	))
	->defaults(array(
		'controller' => 'user',
		'action'     => 'index',
		'directory' => 'manager'
	));