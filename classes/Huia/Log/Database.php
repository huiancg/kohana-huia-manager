<?php defined('SYSPATH') OR die('No direct script access.');

class Huia_Log_Database extends Log_Writer {

	public function write(array $messages)
	{
		foreach ($messages as $message)
		{
			$additional = Arr::get($message, 'additional');
			
			$ip = Request::$client_ip;
			$uri = Arr::get($_SERVER, 'SERVER_NAME').Arr::get($_SERVER, 'REQUEST_URI');
			$referer = Arr::get($_SERVER, 'HTTP_REFERER');
			$agent = Arr::get($_SERVER, 'HTTP_USER_AGENT');
			$post = var_export($_POST, TRUE);
			$file = str_replace(DOCROOT, '', Arr::get($message, 'file'));

			// Write each message into the log database table
			DB::insert('logs', array(
				'time', 'level', 'body', 'file', 'line', 'class', 'function', 'additional', 'ip', 'uri', 'referer', 'agent', 'post'
			))->values(array(
				Arr::get($message, 'time'),
				Arr::get($message, 'level'),
				Arr::get($message, 'body'),
				$file,
				Arr::get($message, 'line'),
				Arr::get($message, 'class'),
				Arr::get($message, 'function'),
				empty($additional) ? NULL : print_r($additional, TRUE),
				$ip,
				$uri,
				$referer,
				$agent,
				$post
			))
			->execute();
		}
	}

}
