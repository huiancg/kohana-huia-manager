<?php defined('SYSPATH') OR die('No direct access allowed.');

class Huia_Model_App extends ORM {
	
	protected $_created_column = array('column' => 'created_at', 'format' => 'Y-m-d H:i:s');
	protected $_updated_column = array('column' => 'updated_at', 'format' => 'Y-m-d H:i:s');
	
	protected static function get_table()
	{
		return str_replace('Model_', '', get_called_class());
	}
	
	public static function all()
	{
		return ORM::factory(self::get_table())->find_all();
	}
	
	public function all_as_array($table_name = NULL)
	{
		$has_many = $this->has_many();
		$belongs_to = $this->belongs_to();

		$models = $this->find_all();

		$results = array();

		foreach ($models as $item)
		{
			$result = $item->as_array();

			foreach ($belongs_to as $key => $values)
			{
				$result[$key] = $item->$key->as_array();
			}

			foreach ($has_many as $key => $values)
			{
				// schmittless
				if ($key === $table_name)
				{
					continue;
				}
				$result[$key] = $item->$key->all_as_array($this->table_name());
			}
			
			$results[] = $result;
		}

		return $results;
	}
	
	public function get_image_url($attr = 'image')
	{
		return 'public/upload/'. strtolower(self::get_table()) .'/'.$this->$attr;
	}
	
	public function error($message, $values = array())
	{
		throw new ORM_Validation_Exception(
				NULL, 
				Validation::factory(array()), 
				$message, 
				$values
			);
	}

}