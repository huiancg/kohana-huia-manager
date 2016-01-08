<?php defined('SYSPATH') or die('No direct script access.');

class Huia_Controller_Manager_App extends Controller_App {

  public $template = 'manager';

  public $bootstrap_css = '//maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css';
  public $title = NULL;
  public $model_name = NULL;
  
  /**
   * @var Model_App 
   */
  public $model = NULL;
  
  public $image_fields = NULL;
  public $upload_fields = NULL;
  public $boolean_fields = NULL;
  public $boolean_fields_labels = array('default' => array('Não', 'Sim'));
  public $ignore_actions = array();
  public $ignore_fields = array();
  public $can_export = TRUE;
  public $can_search = TRUE;
  public $actions = array();

  public $belongs_to = array();
  public $has_many = array();
  public $labels = array();

  public $is_public = FALSE;

  public $parent = NULL;
  public $parents = NULL;
  public $parent_id = NULL;
  public $parent_model = NULL;
  public $parent_controller = NULL;
  public $redirect = NULL;

  public $cached = FALSE;

  public $foreign_key = NULL;

  public $breadcrumbs = array();

  public function before()
  {
    if ( ! Can::show())
    {
      // Record only when invalid session, prevent ximite.
      if ( ! Auth::instance()->logged_in())
      {
        Session::instance()->set('manager_login_reference', URL::current());
      }
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

    if ( ! $this->parents)
    {
      $this->parents = $this->request->param('parents');
      $this->parents = explode('/', $this->parents);
      $parents = array();
      if (count($this->parents) > 1)
      {
        foreach ($this->parents as $index => $value)
        {
            if ($index % 2) continue;
            $parents[] = array(
              'model' => $value,
              'table' => Inflector::plural($value),
              'model_id' => $this->parents[$index + 1]
            );
        }
      }
      $this->parents = array_reverse($parents);
    }
    
    if ( ! $this->parent)
    {
      $this->parent = $this->request->param('parent');
    }
    if ( ! $this->parent_id)
    {
      $this->parent_id = $this->request->param('parent_id');
    }

    $boolean_fields = array();
    $image_fields = array();
    $upload_fields = array();
    $text_fields = array();
    $date_fields = array();

    if ($this->model_name)
    {
      $this->model = ORM::factory(ORM::get_model_name($this->model_name), $this->request->param('id'));
	  
      if ($this->parents)
      {
        $current_parent_table = strtolower($this->model_name);
        foreach ($this->parents as $index => $values)
        {
          $this->model->join(Arr::get($values, 'table'));
          $this->model->on(Arr::get($values, 'table').'.id', '=', $current_parent_table.'.'.Arr::get($values, 'model').'_id');
          $this->model->where(Arr::get($values, 'table').'.id', '=', Arr::get($values, 'model_id'));

          $current_parent_table = Arr::get($values, 'table');
        }
      }
      
      if ($this->parent_id)
      {
        $this->foreign_key = strtolower($this->parent) . '_id';

        $this->parent_model = ORM::factory(ORM::get_model_name($this->parent), $this->parent_id);

        $model_has_many = Inflector::plural(strtolower($this->model_name));

        if (in_array($this->foreign_key, array_keys($this->model->as_array())))
        {
          $this->model->where($this->foreign_key, '=', $this->parent_id);
        }
        else if (in_array($model_has_many, array_keys($this->parent_model->as_array())))
        {
          $this->model = $this->parent_model->{$model_has_many};
        }
      }

      $this->model->reload_columns(TRUE);
      foreach ($this->model->table_columns() as $column => $values)
      {
        if (Arr::get($values, 'data_type') === 'text')
        {
          $text_fields[] = $column;   
        }
        else if (Arr::get($values, 'data_type') === 'tinyint' AND Arr::get($values, 'display') == 1)
        {
          $boolean_fields[] = $column;
        }
        else if (preg_match('/^(image|thumb)/', $column))
        {
          $image_fields[] = $column;
        }
        else if (preg_match('/^(file|upload)_/', $column))
        {
          $upload_fields[] = $column;
        }
        else if (Arr::get($values, 'data_type') === 'date')
        {
          $date_fields[] = $column;
        }
      }

      View::set_global('date_fields', $date_fields);
      
      View::set_global('text_fields', $text_fields);

      $this->belongs_to = Arr::merge($this->belongs_to, $this->model->belongs_to());
      
      $this->has_many = Arr::merge($this->has_many, $this->model->has_many());
      
      $model_labels = $this->model->labels();
      foreach ($model_labels as $key => $value)
      {
        // ignore through secundary
        $has_many_key = Arr::get($this->has_many, $key);
        if ($has_many_key)
        {
          $through = Arr::get($has_many_key, 'through');
          $is_secundary = preg_match('/^'.$key.'_/', $through);
          $same_table = $through === ($key . '_' . $key);
          if ($is_secundary AND ! $same_table)
          {
            unset($model_labels[$key]);
          }
        }
        // ignore composite
        if (preg_match('/^id_/', $key))
        {
          unset($model_labels[$key]);
        }
      }
      
      $this->labels = Arr::merge($this->labels, $model_labels);
    }

    // auto upload
    if ($this->upload_fields === NULL)
    {
      $this->upload_fields = $upload_fields;
    }

    // auto booleans
    if ($this->boolean_fields === NULL)
    {
      $this->boolean_fields = $boolean_fields;
    }

    // auto images
    if ($this->image_fields === NULL)
    {
      $this->image_fields = $image_fields;
    }

    foreach($this->boolean_fields as $field)
    {
      if ( ! isset($this->boolean_fields_labels[$field]))
      {
        $this->boolean_fields_labels[$field] = $this->boolean_fields_labels['default'];
      }
    }
    
    $model_classes = ORM_Autogen::get_models();
    View::set_global('model_classes', $model_classes);
    
    parent::before();
    
    // autogen controllers
    if (Kohana::$environment === Kohana::DEVELOPMENT)
    {
      self::generate_controllers($model_classes);
    }
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
	  
	  $file = str_replace('_', DIRECTORY_SEPARATOR, $class_name);
      
      $base = APPPATH.'classes'.DIRECTORY_SEPARATOR.'Controller'.DIRECTORY_SEPARATOR.'Manager'.DIRECTORY_SEPARATOR;
      $file_name = $base . $file . EXT;
      
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

    View::set_global('belongs_to', $this->belongs_to);
    View::set_global('has_many', $this->has_many);
    
    View::set_global('labels', $this->labels);
    View::set_global('model_name', $this->model_name);
    View::set_global('image_fields', $this->image_fields);
    View::set_global('upload_fields', $this->upload_fields);
    View::set_global('boolean_fields', $this->boolean_fields);
    View::set_global('boolean_fields_labels', $this->boolean_fields_labels);
    View::set_global('ignore_actions', $this->ignore_actions);
    View::set_global('form_actions', $this->form_actions());
    View::set_global('breadcrumb', $this->breadcrumb());
    View::set_global('search_view', $this->search_view());
    View::set_global('scripts', $this->scripts());
    View::set_global('breadcrumbs', $this->breadcrumbs);
    View::set_global('actions', $this->actions);
    View::set_global('ignore_fields', $this->ignore_fields);
    View::set_global('can_export', $this->can_export);
    View::set_global('can_search', $this->can_search);
    View::set_global('bootstrap_css', $this->bootstrap_css);

    if ($this->parent)
    {
      View::set_global('parent_title', ucfirst($this->parent));
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
      $parent = '/' . $this->parent . '/' . $this->parent_id;
    }
    else if ($this->parents)
    {
      $parent = '/';
      $parents = array_reverse($this->parents);
      foreach ($parents as $values)
      {
        $parent .= Arr::get($values, 'model') . '/' . Arr::get($values, 'model_id') . '/';
      }
      $parent = preg_replace('@/$@', '', $parent);
    }

    $url = Kohana::$base_url . strtolower($this->request->directory() . $parent . '/' . $this->model_name);
    return $url;
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

  protected function search_view()
  {
    if ($this->view_exists('_search'))
    {
      return $this->view_dir().'/_search';
    }
    return 'template/manager/_search';
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
        if ($value)
        {
          $this->model->where($key, 'LIKE', '%' . $value . '%');
        }
      }
    }
    
    $query = $this->request->query('q');
    if($query)
    {
      $this->search($query);
    }

    if ($this->request->query('_export'))
    {
      return $this->export();
    }
    
    $this->pagination();
    
    View::set_global('rows', $this->model->find_all());

    if ( ! $this->view_exists())
    {
      $this->content = View::factory('template/manager/index');
    }
  }
  
  public function search($query)
  {
    $has_fields = FALSE;
    $object = $this->model->object();
    foreach($this->labels as $key => $value)
    {
      if (in_array($key, $this->ignore_fields))
      {
        continue;
      }
      
      if (array_key_exists($key, $object))
      {
        if ( ! $has_fields)
        {
          $this->model->where_open();
          $has_fields = TRUE;
        }
        $this->model->or_where($key, 'LIKE', '%' . $query . '%');
      }
    }
    if ($has_fields)
    {
      $this->model->where_close();
    }
  }

  public function export_data()
  {
    $rows = array();
    foreach ($this->model->find_all() as $row)
    {
      $rows[] = $row->as_array();
    }
    return $rows;
  }

  public function export()
  {
    $rows = $this->export_data();
    $this->response->body($this->array2table($rows));
    $this->response->send_file(TRUE, $this->model_name . '.' . time() . '.xls');
  }

  public function array2table(array $array)
  {
    if ( ! count($array))
    {
      return NULL;
    }
    $html = '<table>';
    
    $head = array_keys(reset($array));
    $html .= '<tr>';
    foreach ($head as $row)
    {
      $html .= '<td>' . __($row) . '</td>';
    }
    $html .= '</tr>';
    
    foreach ($array as $rows)
    {
      $html .= '<tr>';
      foreach ($rows as $row)
      {
        $html .= '<td>'.$row.'</td>';
      }
      $html .= '<tr>';
    }
    $html .= '</table>';
    
    return $html;
  }
  
  public function pagination()
  {
    $pagination_model = clone $this->model;
    $pagination_config = array(
      'total_items' => $pagination_model->count_all()
    );
    $pagination = Pagination::factory($pagination_config);
    $this->model->offset($pagination->offset);
    $this->model->limit($pagination->items_per_page);
    View::set_global('pagination', $pagination);
  }

  public function action_new()
  {
    $this->show_form();
  }

  public function action_edit()
  {
    $this->show_form();
  }
  
  public function action_save_draft()
  {
    if ($this->request->method() !== Request::POST)
    {
      return;
    }
    
    $this->model->set_composite_draft_actived();

    $this->response->json(TRUE);
  }

  public function action_delete_draft()
  {
    if ($this->request->method() !== Request::POST)
    {
      return;
    }
    
    $this->model->clean_draft();

    $this->response->json(TRUE);
  }
  
  public function flush()
  {
    if (class_exists('Cache'))
    {
      // Flush all cache
      Cache::instance()->delete_all();
    }
  }

  public function action_delete()
  {
    $this->model->delete();
    Session::instance()->set('success', 'Registro removido com sucesso!');
    $this->flush();
    return HTTP::redirect('manager/'.strtolower($this->model_name));
  }

  protected function save_after()
  {
    //
  }

  protected function save_before()
  {
    //
  }

  protected function save()
  {
    $this->model->values($this->request->post());
	
    // clean null values
    foreach ($this->model->table_columns() as $field => $values)
    {
      $is_boolean = Arr::get($values, 'data_type') === 'tinyint' AND Arr::get($values, 'display') == 1;
      $is_nullable = Arr::get($values, 'is_nullable');
      $has_value = (bool) $this->model->{$field} AND $this->model->{$field} !== NULL;
      if ($is_nullable AND ! $is_boolean AND ! $has_value)
      {
        $this->model->{$field} = NULL;
      }
    }

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

            create_dir($dir);

            Upload::save($file, $filename, $dir);
            $this->model->$name = $filename;
          }
        }
      }
      
      if ($this->parent_id)
      {
        $this->model->{$this->parent.'_id'} = $this->parent_id;
      }

      $this->save_before();

      $this->model->save();

      $this->save_after();
      
      // add has many
      foreach ($this->has_many as $name => $values)
      {
        // through
        if (Arr::get($values, 'through'))
        {
          $ids = $this->request->post($name);

          $this->model->remove($name);

          if ( ! $ids)
          {
            continue;
          }
          
          $this->model->add($name, $ids);
        }
      }
      
      $this->flush();
      
      if ($this->request->is_ajax())
      {
        $this->response->json($this->model->all_as_array());
        return;
      }

      Session::instance()->set('success', 'Registro salvo com sucesso!');

      if ($this->redirect === NULL)
      {
        HTTP::redirect($this->url());
      }
      else
      {
        HTTP::redirect($this->redirect);
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

      if ($this->request->is_ajax())
      {
        $this->response->json(array('errors' => $errors));
      }
    }
  }
}