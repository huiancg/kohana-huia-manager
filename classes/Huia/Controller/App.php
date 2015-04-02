<?php defined('SYSPATH') or die('No direct script access.');

abstract class Huia_Controller_App extends Controller {

	/**
	 * @var  View  page template
	 */
	public $template = 'index';
	public $title = '';    
	public $description = '';
	public $auto_ajax = TRUE;
	
	/**
	 * @var  View  page content
	 */
	public $content = '';
	public $loadViewSection = false;
	private $pathViewSections = "template/sections";
	
	/**
	 * Loads the template [View] object and content [View] object.
	 */
	public function before()
	{
		// autogen database
		if (Kohana::$environment === Kohana::DEVELOPMENT AND class_exists('ORM'))
		{
			ORM::generate_models();
		}
		
		// Template View auto load
		if ($this->template !== NULL)
		{
			// Load the template
			$this->template = View::factory('template/'.$this->template);
		}
		
		// Content View auto load
		$directory = ($this->request->directory() ? $this->request->directory().'/' : '');	
		$dir = str_replace('_', '/', strtolower($directory).strtolower($this->request->controller()));
		$file = str_replace('_', '/', $this->request->action());
		
		// Set default template file
		if (Kohana::find_file('views/'.$dir, $file))
		{
			$this->content = View::factory($dir.'/'.$file);
		}

		View::set_global('controller', strtolower($this->request->controller()));
		View::set_global('action', strtolower($this->request->action()));

		parent::before();
	}

	/**
	 * Assigns the template [View] as the request response.
	 */
	public function after()
	{		
		View::set_global('title', $this->title);
		View::set_global('description', $this->description);
		
		if ($this->auto_ajax AND $this->request->is_ajax())
		{
			$this->template = NULL;
		}
		
		if (isset($this->content) AND $this->response->body() === '')
		{
			if (($this->template === NULL) AND isset($this->content))
			{
				$this->response->body($this->content);
			}
			else
			{
				$this->template->content = $this->content;
				$this->response->body($this->template);
			}
		}
		
		parent::after();
	}

	public function json($data)
	{
		$this->template = NULL;
		$this->response->headers('Content-Type', 'application/json; charset=utf-8');
		$this->response->body(json_encode($data));
	}
	

} // End App