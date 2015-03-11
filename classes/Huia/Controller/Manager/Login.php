<?php defined('SYSPATH') or die('No direct script access.');

class Huia_Controller_Manager_Login extends Controller_Manager_App {

	public $template = null;

	public function action_index()
	{
		$post = $this->request->post();
		if($this->request->method() == 'POST')
		{
			$success = Auth::instance()->login($post['username'], $post['password']);		
			if($success)
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