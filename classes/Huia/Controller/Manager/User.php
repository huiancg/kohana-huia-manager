<?php defined('SYSPATH') or die('No direct script access.');

class Huia_Controller_Manager_User extends Controller_Manager_App {
  
  public $ignore_actions = array('user_tokens');
  
  public $labels = array(
    'roles' => 'Papel',
  );
  
  public function action_index()
  {
    $this->ignore_fields[] = 'password';
    return parent::action_index();
  }

  protected function save()
  {
    if ($this->request->post('password') === '')
    {
      $values = $this->request->post();
      unset($values['password']);
      $this->request->post($values);
    }
    parent::save();
  }

}