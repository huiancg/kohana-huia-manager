<?php defined('SYSPATH') or die('No direct script access.');

class Huia_Can {

  protected static function role_exists($name)
  {
    return (bool) Model_Role::factory('Role', array('name' => $name))->id;
  }

  protected static function check($required)
  {
    return Can::role_exists($required) AND Auth::instance()->logged_in($required);
  }

  public static function show($controller = NULL, $action = NULL)
  {
    $controller = ($controller) ? $controller : Request::current()->controller();
    $action = ($action) ? $action : Request::current()->action();

    if ($controller === 'Login')
    {
      return TRUE;
    }
    else if ( ! Auth::instance()->logged_in('admin'))
    {
      return FALSE;
    }

    // is manager
    if (Can::role_exists('manager') AND Auth::instance()->logged_in('manager'))
    {
      return TRUE;
    }

    $require_controller = 'manager-' . strtolower($controller);

    // can action
    if (Can::check($require_controller . '-' . $action))
    {
      return TRUE;
    }

    // can *
    if (Can::check($require_controller))
    {
      return TRUE;
    }

    // is protected
    return ! Can::role_exists($require_controller);
  }

}