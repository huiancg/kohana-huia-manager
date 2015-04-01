<?php defined('SYSPATH') OR die('No direct script access.');

class Huia_ORM extends Kohana_ORM {
	
	/**
     * Dynamic Finder:
     * 		$orm->find_by_name('eduardo');
     * 		$orm->find_all_by_name('eduardo');
     * 		$orm->count_by_name('eduardo');
     * 		$orm->find_all_by_name_or_email('eduardo');
     * 		$orm->find_all_by_name_and_email('eduardo', 'du@kanema.com.br');
     * 		$orm->find_all_by_name_and_email_and_is_active('eduardo', 'du@kanema.com.br', TRUE);
     * 		$orm->find_all_by_name_and_email_and_is_active_limit('eduardo', 'du@kanema.com.br', TRUE, 5);
     * 		$orm->first_by_name('eduardo');
     * 		$orm->last_by_name('eduardo');
     * 
     * @param 	string	$method	Call methods divide by underscore
     * @param 	array	$arguments	Parameters
     * @return	ORM OR void
     */
    protected function dynamic_finder($method, array $arguments)
    {
        if (preg_match('/^(?<find_type>(find|find_all|first|last|count))_by_/', $method, $matchs))
        {
            $find_type = $matchs['find_type'];
            $method = str_replace($matchs[0], '', $method);

            // Get the limit
            $limit = explode('_limit', $method);
            if (count($limit) === 2)
            {
                $this->limit(array_pop($arguments));
            }
            $method = $limit[0];

            // Get the first or last by primary key
            if ($find_type === 'first' OR $find_type === 'last')
            {
                $order = ($find_type === 'first') ? 'ASC' : 'DESC';
                $this->order_by($this->_table_name . '.' . $this->primary_key(), $order);
                $find_type = 'find';
            }
            else
            {
                // Get the order part
                $order_by = explode('_order_by_', $method);
                if (count($order_by) === 2)
                {
                    $this->order_by($this->_table_name . '.' . $order_by[1]);
                }
                $method = $order_by[0];
            }

            // Get the and parts
            $and_parts = explode('_and_', $method);
            foreach ($and_parts as $and_part)
            {
                // Get the or parts
                $or_parts = explode('_or_', $and_part);
                if (count($or_parts) === 1)
                {
                    $last_argument = (count($arguments) !== 0) ? array_shift($arguments) : $last_argument;
                    $this->where($this->_object_name . '.' . $or_parts[0], '=', $last_argument);
                }
                else
                {
                    foreach ($or_parts as $or_part)
                    {
                        $last_argument = (count($arguments) !== 0) ? array_shift($arguments) : $last_argument;
                        $this->or_where($this->_object_name . '.' . $or_part, '=', $last_argument);
                    }
                }
            }

            // Execute the query
            return $this->{$find_type}();
        }
    }

    public function __call($method, array $arguments)
    {
        $response = $this->dynamic_finder($method, $arguments);
        if ($response === NULL)
        {
            throw new Kohana_Exception('Call to undefined method :method()', array(':method' => $method));
        }
        return $response;
    }
	
	/**
     * Tests if a unique key value exists in the database.
     *
     * @param   mixed    the value to test
     * @param   string   field name
     * @return  boolean
     */
    public function unique($field, $value)
    {
        return!(bool) DB::select(array('COUNT("*")', 'total_count'))
                        ->from($this->_table_name)
                        ->where($field, '=', $value)
                        ->where($this->_primary_key, '!=', $this->pk())
                        ->execute($this->_db)
                        ->get('total_count');
    }

    /**
     * Find by primary key
     * 
     * @param  string  $field Primary key value
     * @return  ORM  
     */
    public function find_by_primary_key($field)
    {
        $primary_cols = array();
        foreach ($this->list_columns() as $row => $fields)
        {
            foreach ($fields as $key => $value)
            {
                if ($row === 'id' OR $key !== 'key' OR ($value !== 'PRI' AND $value !== 'UNI'))
                    continue;

                $primary_cols[] = $row;
            }
        };

        $this->or_where_open();
        foreach ($primary_cols as $col)
        {
            $this->or_where($col, '=', $field);
        }
        $this->or_where_close();

        return $this;
    }
	
	public static function generate_model($model)
	{
		$class_name = 'Model_'.$model;
		// Create if dont exists
		if ( ! class_exists($class_name))
		{
			$view = View::factory('template/orm');
			$view->set('class_name', $class_name);
			
			$file_name = str_replace('_', DIRECTORY_SEPARATOR, $model);
			
			$model_base = APPPATH.'classes'.DIRECTORY_SEPARATOR.'model'.DIRECTORY_SEPARATOR;
			$file_name = $model_base . $file_name . EXT;
			
			create_dir(dirname($file_name));
			
			$table_name = strtolower(Inflector::plural($model));
			$fields = DB::query(Database::SELECT, 'SHOW FIELDS FROM ' . $table_name)->execute();
			
			$rules = array();
			$labels = array();
			
			foreach ($fields as $field)
			{
				$name = Arr::get($field, 'Field');
				$title = ucfirst($name);
				$type = Arr::get($field, 'Type');
				
				// ignore id and _at$
				if ($name === 'id' OR preg_match('@_at$@', $name))
				{
					continue;
				}
				
				$field_rules = array();
				
				// not null
				if (Arr::get($field, 'Null') === 'NO')
				{
					$field_rules[] = "array('not_empty'),";
				}
				
				// max length
				if ($type)
				{
					preg_match('@varchar\(([0-9]+)\)@', $type, $type_matches);
					$field_rules[] = "array('max_length', array(':value', $type_matches[1])),";
				}
				
				$rules[$name] = $field_rules;
				$labels[$name] = $title;
			}
			
			$view->set('rules', $rules);
			$view->set('labels', $labels);
			
			file_put_contents($file_name, $view->render());
		}
	}
	
	/**
	 * Creates and returns a new model. 
	 * Model name must be passed with its' original casing, e.g.
	 * 
	 *    $model = ORM::factory('User_Token');
	 *
	 * @chainable
	 * @param   string  $model  Model name
	 * @param   mixed   $id     Parameter for find()
	 * @return  ORM
	 */
	public static function factory($model, $id = NULL)
	{
		$class_name = 'Model_'.$model;
		
		if (Kohana::$environment === Kohana::DEVELOPMENT)
		{
			self::generate_model($model);
		}
		
		return parent::factory($model, $id);
	}
	
}
