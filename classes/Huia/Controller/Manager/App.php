<?php defined('SYSPATH') or die('No direct script access.');

class Huia_Controller_Manager_App extends Controller_App {

	public $template = 'manager';

	public $title = NULL;
	public $model_name = NULL;
	public $model = NULL;
	public $image_fields = array('image');
	public $boolean_fields = array('is_active');
	public $boolean_fields_labels = array('default' => array('Não', 'Sim'));
	public $ignore_actions = array();
	public $ignore_fields = array();
	public $actions = array();

	public $belongs_to = array();
	public $has_many = array();
	public $labels = array();

	public $parent = NULL;
	public $parent_id = NULL;
	public $parent_model = NULL;
	public $parent_controller = NULL;
	public $redirect = NULL;

	public $foreign_key = NULL;

	public $breadcrumbs = array();

	public function before()
	{
		if($this->request->controller() != 'Login' && !Auth::instance()->logged_in('admin'))
		{
			return HTTP::redirect('manager/login');
		}

		$success = Session::instance()->get_once('success');
		View::set_global('success', $success);

		if ( ! $this->model_name AND class_exists('Model_'.$this->request->controller()))
		{
			$this->model_name = $this->request->controller();
		}

		if ($this->title === NULL)
		{
			$this->title = $this->model_name;
		}

		if ( ! $this->parent)
		{
			$this->parent = $this->request->param('parent');
		}
		if ( ! $this->parent_id)
		{
			$this->parent_id = $this->request->param('parent_id');
		}

		if ($this->model_name)
		{
			$this->model = ORM::factory($this->model_name, $this->request->param('id'));

			if ($this->parent_id)
			{
				$this->foreign_key = $this->parent . '_id';
				$this->parent_controller = 'Controller_Manager_'.ucfirst($this->parent);
				$this->parent_controller = new $this->parent_controller($this->request, $this->response);

				if (isset($this->model->{$this->foreign_key}))
				{
					$this->model->where($this->foreign_key, '=', $this->parent_id);
				}
				
				$this->parent_model = ORM::factory($this->parent, $this->parent_id);
			}

			$text_fields = array();
			foreach ($this->model->table_columns() as $column => $values)
			{
				if (Arr::get($values, 'data_type') === 'text')
				{
					$text_fields[] = $column;		
				}	
			}
			View::set_global('text_fields', $text_fields);

			$this->belongs_to = Arr::merge($this->belongs_to, $this->model->belongs_to());
			View::set_global('belongs_to', $this->belongs_to);
			
			$this->has_many = Arr::merge($this->has_many, $this->model->has_many());
			View::set_global('has_many', $this->has_many);
			
			$this->labels = Arr::merge($this->labels, $this->model->labels());
			View::set_global('labels', $this->labels);
		}
		foreach($this->boolean_fields as $field)
		{
			if( ! isset($this->boolean_fields_labels[$field]))
				$this->boolean_fields_labels[$field] = $this->boolean_fields_labels['default'];
		}
		
		$model_classes = $this->get_models();
		View::set_global('model_classes', $model_classes);
		
		parent::before();
		
		// autogen controllers
		if (Kohana::$environment === Kohana::DEVELOPMENT)
		{
			self::generate_controllers($model_classes);
		}
	}
	
	public function get_models()
	{
		$dir = 'classes'.DIRECTORY_SEPARATOR.'Model'.DIRECTORY_SEPARATOR;
		$models = array();
		
		foreach ((Kohana::list_files($dir)) as $file => $path)
		{
			if (is_string($path) AND strpos($path, APPPATH) !== FALSE)
			{
				$models[] = str_replace(array($dir, APPPATH, EXT), '', $path);
			}
		}
		
		return $models;
	}
	
	public static function generate_controllers($model_classes)
	{
		foreach ($model_classes as $class_name)
		{
			if (class_exists('Controller_Manager_'.$class_name))
			{
				continue;
			}
			
			$view = View::factory('template/manager/controller');
			$view->set('class_name', $class_name);
			
			$base = APPPATH.'classes'.DIRECTORY_SEPARATOR.'Controller'.DIRECTORY_SEPARATOR.'Manager'.DIRECTORY_SEPARATOR;
			$file_name = $base . $class_name . EXT;
			
			create_dir(dirname($file_name));
			
			file_put_contents($file_name, $view->render());
		}
	}

	public function after()
	{
		View::set_global('model', $this->model);

		View::set_global('parent', $this->parent);
		View::set_global('parent_id', $this->parent_id);
		
		View::set_global('title', $this->title);

		View::set_global('model_name', $this->model_name);
		View::set_global('image_fields', $this->image_fields);
		View::set_global('boolean_fields', $this->boolean_fields);
		View::set_global('boolean_fields_labels', $this->boolean_fields_labels);
		View::set_global('ignore_actions', $this->ignore_actions);
		View::set_global('form_actions', $this->form_actions());
		View::set_global('breadcrumb', $this->breadcrumb());
		View::set_global('scripts', $this->scripts());
		View::set_global('breadcrumbs', $this->breadcrumbs);
		View::set_global('actions', $this->actions);
		View::set_global('ignore_fields', $this->ignore_fields);

		if ($this->parent)
		{
			View::set_global('parent_title', $this->parent_controller->title);
			View::set_global('foreign_key', $this->parent);
			View::set_global('parent_model', $this->parent_model);
		}

		View::set_global('url', $this->url());

		parent::after();
	}

	public function url()
	{
		if ( ! $this->model_name)
		{
			return NULL;
		}

		$parent = '';
		if ($this->parent)
		{
			$parent = '/' . $this->parent_id;
		}

		return Kohana::$base_url . strtolower($this->request->directory() . $parent . '/' . $this->model_name);
	}

	protected function view_dir()
	{
		$directory = ($this->request->directory() ? $this->request->directory().'/' : '');	
		return str_replace('_', '/', strtolower($directory).strtolower($this->request->controller()));
	}

	protected function view_exists($file = 'index')
	{
		return Kohana::find_file('views/'.$this->view_dir(), $file);
	}

	protected function form_actions()
	{
		if ($this->view_exists('_form_actions'))
		{
			return $this->view_dir().'/_form_actions';
		}
		return 'template/manager/_form_actions';
	}

	protected function breadcrumb()
	{
		if ($this->view_exists('_breadcrumb'))
		{
			return $this->view_dir().'/_breadcrumb';
		}
		return 'template/manager/_breadcrumb';
	}

	protected function scripts()
	{
		if ($this->view_exists('_scripts'))
		{
			return $this->view_dir().'/_scripts';
		}
		return 'template/manager/_scripts';
	}

	protected function show_form()
	{
		if ($this->request->method() === 'POST')
		{
			$this->save();
		}

		View::set_global('model', $this->model);

		if ($this->view_exists('_form'))
		{
			$this->content = View::factory('manager/'.str_replace('_', '/', strtolower($this->model_name)).'/_form');
		}
		else
		{
			$this->content = View::factory('template/manager/_form');
		}
	}

	public function action_index()
	{
		if ( ! $this->model_name)
		{
			return;
		}

		$filters = $this->request->query('filters');
		if(count($filters))
		{
			foreach($filters as $key => $value)
			{
				if($value)
					$this->model->where($key, '=', $value);
			}
		}

		View::set_global('rows', $this->model->find_all());

		if ( ! $this->view_exists())
		{
			$this->content = View::factory('template/manager/index');
		}
	}

	public function action_new()
	{
		$this->show_form();
	}

	public function action_edit()
	{
		$this->show_form();
	}

	public function action_delete()
	{
		$this->model->delete();
		Session::instance()->set('success', 'Registro removido com sucesso!');
		return HTTP::redirect('manager/'.strtolower($this->model_name));
	}

	protected function save()
	{
		$this->model->values($this->request->post());

		try
		{
			if (isset($_FILES))       	
			{     
				foreach($_FILES as $name => $file)
				{
					if (Upload::not_empty($file))
					{
						$filename = uniqid().'_'.$file['name'];
						$filename = preg_replace('/\s+/u', '_', $filename);
						$dir = DOCROOT.'public'.DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR.strtolower($this->model_name);

						if ( ! is_dir($dir))
						{
							mkdir($dir, 0755, TRUE);
							chmod($dir, 0755);
						}

						Upload::save($file, $filename, $dir);
						$this->model->$name = $filename;
					}
				}
			}
			
			if ($this->parent_id)
			{
				$this->model->{$this->parent.'_id'} = $this->parent_id;
			}

			$this->model->save();
			
			// add has many
			foreach ($this->has_many as $name => $values)
			{
				// through
				if (Arr::get($values, 'through'))
				{
					$ids = $this->request->post($name);

					if ( ! $ids)
					{
						continue;
					}
					
					$this->model->remove($name);
					$this->model->add($name, $ids);
				}
			}


			Session::instance()->set('success', 'Registro salvo com sucesso!');

			if ($this->redirect === NULL)
			{
				return HTTP::redirect($this->url());
			}
			else
			{
				return HTTP::redirect($this->redirect);
			}
		}
		catch (ORM_Validation_Exception $e)
		{
			$errors = $e->errors('models');
			if ( ! $errors)
			{
				$errors = array($e->getMessage());
			}
			View::set_global('errors', $errors);
		}
	}
}
