<?php defined('SYSPATH') or die('No direct script access.');

class Huia_Controller_Manager_Login extends Controller_Manager_App {

  public $template = NULL;

  protected function get_role($name, $description)
  {
    $role = ORM::factory('Role')->find_by_name($name);
    if ( ! $role->loaded())
    {
      $role = ORM::factory('Role');
      $role->name = $name;
      $role->description = $description;
      $role->create();
    }
    return (int) $role->id;
  }
  
  protected function huia_auth($username, $password)
  {
    $auth_url = Kohana::$config->load('huia/manager.auth_url');
    if ( ! $auth_url)
    {
      return FALSE;
    }
    
    try
    {
      $request = Request::factory($auth_url);
      $request->method(Request::POST);
      $request->post('username', $username);
      $request->post('password', $password);
      $response = $request->execute();
      
      $user = @json_decode($response->body());
      
      if ( ! $user OR isset($user->error) OR ! isset($user->email))
      {
        return FALSE;
      }
      
      $model = ORM::factory('User')->find_by_email($user->email);
      
      if ( ! $model->loaded())
      {
        $model->values((array) $user);
        $model->password = $password;
        $model = $model->create();

        $roles = array(
          $this->get_role('admin', 'Administrative user, has access to everything.'),
          $this->get_role('login', 'Login privileges, granted after account confirmation'),
        );

        $model->add('roles', $roles);
      }
      else if (Auth::instance()->hash($password) !== $model->password)
      {
        $model->password = $password;
        $model->update();
      }

      Auth::instance()->force_login($model->username);
      
      return TRUE; 
    }
    catch (Exception $e)
    {
      return FALSE;
    }
  }

  public function action_index()
  {
    if ($this->request->method() === Request::POST)
    {
      $username = $this->request->post('username');
      $password = $this->request->post('password');
      
      $local_login = Auth::instance()->login($username, $password);
      $huia_login = $this->huia_auth($username, $password);
      
      if ($local_login OR $huia_login)
      {
        $redirect = Session::instance()->get_once('manager_login_reference');
        $redirect = ($redirect) ? $redirect : 'manager';
        return HTTP::redirect($redirect);
      }
      else
      {
        View::set_global('error', 'Dados Incorretos');
      }
    }
  }

  public function action_logout()
  {
    Auth::instance()->logout(TRUE, TRUE);
    return HTTP::redirect('manager');
  }

}