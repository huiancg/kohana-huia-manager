<?php defined('SYSPATH') or die('No direct script access.');

class Huia_Controller_Manager_Schema extends Controller_Manager_App {

  public $title = 'Schema editor';

  protected $field_types = [
    'DEFAULT',
    'BOOL',
    'DATE',
    'DATETIME',
    'IMAGE',
    'NUMBER',
    'TEXT',
    'UPLOAD',
  ];

  protected $unalterable_field_types = [
    'IMAGE',
    'UPLOAD',
  ];

  protected function is_ignored_model($model_name)
  {
    return in_array($model_name, Kohana::$config->load('huia/manager.schema_ignored_models'));
  }

  protected function format_related(& $items)
  {
    foreach($items as $key => &$item)
    {
      $this->format_model_name($item);
    }
  }

  protected function format_model_name(& $model)
  {
    $model['has_parents'] = strpos($model['model'], '_') !== FALSE;
    $model['parents'] = explode('_', $model['model']);
    $model['name'] = array_pop($model['parents']);
  }

  protected function valid_models()
  {
    $models = [];
    foreach (Database::instance()->list_tables() as $table_name)
    {
      $model_name = ORM::get_model_name($table_name);

      if ( ! class_exists('Model_'.$model_name) OR $this->is_ignored_model($model_name))
      {
        continue;
      }

      $orm = ORM::factory($model_name);

      $model = [];

      $model['model'] = $model_name;

      $this->format_model_name($model);

      $model['table_name'] = $table_name;
      
      $model['belongs_to'] = $orm->belongs_to();
      
      $has_many = $orm->has_many();
      
      $model['has_many'] = array_filter($has_many, function($item) {
        return Arr::get($item, 'through') === NULL;
      });

      $model['through'] = array_filter($has_many, function($item) {
        if (Arr::get($item, 'through'))
        {
          $plural = Inflector::plural(preg_replace('@_id$@', '', $item['far_key']));
          return strpos($item['through'], $plural.'_') === FALSE;
        }
        return FALSE;
      });

      $this->format_related($model['belongs_to']);
      $this->format_related($model['has_many']);
      
      $models[] = $model;
    }

    array_multisort(Arr::path($models, '*.model'), SORT_ASC, $models);

    // dd($models);

    return $models;
  }

  public function before()
  {
    if ($this->request->query('model'))
    {
      $this->model_name = ORM::get_model_name($this->request->query('model'));
      $this->title .= ' - '. $this->model_name;
    }
    parent::before();
  }

  public function action_index()
  {
    View::set_global('models', $this->valid_models());
  }

  public function action_edit()
  {
    $this->content = View::factory('manager/schema/edit');

    $fields = [];

    $relations = array_keys($this->has_many) + array_keys($this->belongs_to);

    foreach ($this->labels as $field => $label)
    {
      if (in_array($field, $relations))
      {
        continue;
      }

      $attributes = ['class' => 'form-control'];
      $types = $this->field_types;

      if ($this->is_disable_type($field))
      {
        $attributes['disabled'] = 'disabled';
      }
      else
      {
        $types = $this->filter_valid_types($types);
      }

      $fields[] = [
        'field' => $field,
        'label' => $label,
        'type' => $this->get_field_type($field),
        'attributes' => $attributes,
        'types' => $types,
      ];
    }
    
    $this->content->fields = $fields;
    $this->content->field_types = $this->filter_valid_types($this->field_types);
  }

  protected function filter_valid_types($types)
  {
    return array_values(array_filter($types, function($item) {
      return !!! in_array($item, $this->unalterable_field_types);
    }));
  }

  protected function is_disable_type($field)
  {
    return (bool) preg_match('/^(image|thumb|file|upload)/', $field);
  }

  protected function get_field_type($field)
  {
    if (in_array($field, array_keys($this->content->text_fields)))
    {
      return 'TEXT';
    }
    else if (in_array($field, $this->boolean_fields))
    {
      return 'BOOL';
    }
    else if (in_array($field, $this->image_fields))
    {
      return 'IMAGE';
    }
    else if (in_array($field, $this->upload_fields))
    {
      return 'UPLOAD';
    }
    else if (in_array($field, $this->content->date_fields))
    {
      return 'DATE';
    }
    else
    {
      return 'DEFAULT';
    }
  }

  protected function get_data_type($type)
  {
    if ($type === 'TEXT')
    {
      return 'TEXT';
    }
    else if ($type === 'NUMBER')
    {
      return 'INT(11)';
    }
    else if ($type === 'BOOL')
    {
      return 'TINYINT(1)';
    }
    else if ($type === 'IMAGE')
    {
      return 'VARCHAR(128)';
    }
    else if ($type === 'UPLOAD')
    {
      return 'VARCHAR(128)';
    }
    else if ($type === 'DATE')
    {
      return 'DATE';
    }
    else if ($type === 'DATETIME')
    {
      return 'DATETIME';
    }
    else
    {
      return 'VARCHAR(128)';
    }
  }

  public function action_rename_table()
  {
    $data = Arr::extract($this->request->post(), ['table_name', 'table_name_to']);

    $data['table_name_to'] = str_replace('-', '_', URL::slug($data['table_name_to']));

    $data['query'] = "RENAME TABLE `{$data["table_name"]}` TO `{$data["table_name_to"]}`;";

    try
    {
      DB::query(NULL, $data['query'])->execute();
    }
    catch (Database_Exception $e)
    {
      $data['error'] = $e->getMessage();
    }

    $data['token'] = Security::token();

    $this->response->json($data);
  }

  public function action_alter_table()
  {
    $data = Arr::extract($this->request->post(), ['table_name', 'name', 'type', 'last']);

    $data['name'] = str_replace('-', '_', URL::slug($data['name']));

    $data['datatype'] = $this->get_data_type($data['type']);

    $data['query'] = 'ALTER TABLE `'.$data['table_name'].'` ADD COLUMN `'.$data['name'].'` '.$data['datatype'].' NULL AFTER `'.$data['last'].'`;';

    try
    {
      DB::query(NULL, $data['query'])->execute();
    }
    catch (Database_Exception $e)
    {
      $data['error'] = $e->getMessage();
    }

    $data['token'] = Security::token();

    $this->response->json($data);
  }

  public function action_create_table()
  {
    $data = Arr::extract($this->request->post(), ['table_name']);
    
    $query = "
    CREATE TABLE `{$data['table_name']}` (
      `id` int(11) NOT NULL AUTO_INCREMENT,
      `updated_at` datetime DEFAULT NULL,
      `created_at` datetime NOT NULL,
      PRIMARY KEY (`id`),
      UNIQUE KEY `id` (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8;
    ";

    DB::query(NULL, $query)->execute();
    $data['query'] = $query;
    $data['token'] = Security::token();

    $this->response->json($data);
  }

  public function action_delete_relation()
  {
    $data = Arr::extract($this->request->post(), ['type', 'table_name', 'foreign_key']);
    
    $query = NULL;

    if (Arr::get($data, 'type') === 'belongs_to')
    {
      $query = 'ALTER TABLE `'.$data['table_name'].'` DROP `'.$data['foreign_key'].'`;';
    }
    else if (Arr::get($data, 'type') === 'through')
    {
      $query = 'DROP TABLE `' . $data['table_name'] . '`;';
    }

    if ($query)
    {
      DB::query(NULL, $query)->execute();
      $data['query'] = $query;
      $data['executed'] = TRUE;
      $data['token'] = Security::token();
    }

    $this->response->json($data);
  }

  public function action_delete_field()
  {
    $data = Arr::extract($this->request->post(), ['table_name', 'field']);
    
    $query = 'ALTER TABLE `'.$data['table_name'].'` DROP COLUMN `'.$data['field'].'`;';
    
    DB::query(NULL, $query)->execute();
    $data['query'] = $query;
    $data['token'] = Security::token();

    $this->response->json($data);
  }

  public function action_update_field()
  {
    $data = Arr::extract($this->request->post(), ['table_name', 'from', 'to', 'type']);

    $data['to'] = str_replace('-', '_', URL::slug($data['to']));

    $data['type'] = $this->get_data_type($data['type']);
    
    $query = 'ALTER TABLE `'.$data['table_name'].'` CHANGE `'.$data['from'].'` `'.$data['to'].'` '.$data['type'].';';
    
    DB::query(NULL, $query)->execute();
    $data['query'] = $query;
    $data['token'] = Security::token();

    $this->response->json($data);
  }

  public function action_upset_table()
  {
    $data = Arr::extract($this->request->post(), ['type', 'model', 'table', 'table_name']);
    
    $query = NULL;

    if (Arr::get($data, 'type') === 'belongs_to')
    {
      $field =  Inflector::singular($data['model']).'_id';

      $query = "
        ALTER TABLE `{$data['table']}` 
        ADD COLUMN `{$field}` int(11) NULL after `id`
      ";
    }
    else if (Arr::get($data, 'type') === 'through')
    {
      $field_1 =  Inflector::singular($data['table_name']).'_id';
      $field_2 =  Inflector::singular($data['model']).'_id';

      $query = "
        CREATE TABLE `{$data['table']}`( 
         `{$field_1}` int(11) NOT NULL, 
         `{$field_2}` int(11) NOT NULL, 
         PRIMARY KEY (`{$field_1}`, `{$field_2}`)
        );
      ";
    }

    if ($query)
    {
      DB::query(NULL, $query)->execute();
      $data['query'] = $query;
      $data['executed'] = TRUE;
      $data['token'] = Security::token();
    }

    $this->response->json($data);
  }

  public function action_delete_table()
  {
    $query = 'DROP TABLE `' . $this->request->post('table_name') . '`;';
    DB::query(NULL, $query)->execute();
    
    $this->response->json(['token' => Security::token()]);
  }

}