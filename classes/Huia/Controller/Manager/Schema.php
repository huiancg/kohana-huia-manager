<?php defined('SYSPATH') or die('No direct script access.');

class Huia_Controller_Manager_Schema extends Controller_Manager_App {

  public $title = 'Schema editor';

  protected function valid_models()
  {
    $models = [];
    foreach (Database::instance()->list_tables() as $table_name)
    {
      $model_name = ORM::get_model_name($table_name);

      if ( ! class_exists('Model_'.$model_name))
      {
        continue;
      }

      $orm = ORM::factory($model_name);

      $model = [];

      $model['model'] = $model_name;
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
      
      $models[] = $model;
    }

    return $models;
  }

  public function action_index()
  {
    View::set_global('models', $this->valid_models());
  }

  public function action_delete_table()
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
    }

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
    }

    $this->response->json($data);
  }

}