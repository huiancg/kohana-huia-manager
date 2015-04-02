<?php defined('SYSPATH') OR die('No direct script access.');

class Huia_Model_Log extends Model_App {

	public function rules()
	{
		return array(
			'level' => array(
				array('not_empty'),
			),
			'body' => array(
				array('not_empty'),
				array('max_length', array(':value', 255)),
			),
			'file' => array(
				array('not_empty'),
				array('max_length', array(':value', 255)),
			),
			'class' => array(
				array('not_empty'),
				array('max_length', array(':value', 255)),
			),
			'function' => array(
				array('max_length', array(':value', 255)),
			),
			'additional' => array(
				array('max_length', array(':value', 65535)),
			),
			'uri' => array(
				array('not_empty'),
				array('max_length', array(':value', 255)),
			),
			'agent' => array(
				array('not_empty'),
				array('max_length', array(':value', 255)),
			),
			'ip' => array(
				array('not_empty'),
				array('max_length', array(':value', 16)),
			),
			'referer' => array(
				array('max_length', array(':value', 255)),
			),
			'post' => array(
				array('not_empty'),
				array('max_length', array(':value', 65535)),
			),
			'timestamp' => array(
				array('not_empty'),
			),
			'time' => array(
				array('not_empty'),
			),
		);
	}


	public function labels()
	{
		return array(
			'level' => __('Level'),
			'body' => __('Body'),
			'file' => __('File'),
			'line' => __('Line'),
			'class' => __('Class'),
			'function' => __('Function'),
			'additional' => __('Additional'),
			'uri' => __('Uri'),
			'agent' => __('Agent'),
			'ip' => __('Ip'),
			'referer' => __('Referer'),
			'post' => __('Post'),
			'timestamp' => __('Timestamp'),
			'time' => __('Time'),
		);
	}

}