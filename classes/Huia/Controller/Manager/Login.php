<?php defined('SYSPATH') or die('No direct script access.');

class Huia_Controller_Manager_Login extends Controller_Manager_App {

	public $template = NULL;
	
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
			$user->password = $password;
			
			if ( ! $model->loaded())
			{
				$model->values((array) $user);
				$model->save();
				$model->add('roles', array(1, 2));
			}
			else
			{
				$model->update();
			}
			
			return Auth::instance()->login($username, $password);
			
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
			
			$success = Auth::instance()->login($username, $password);
			
			if ($success OR $this->huia_auth($username, $password))
			{
				return HTTP::redirect('manager');
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