<?php
class Application
{
	private static $_settings;
	private static $_store;

	private function __construct() { /*empty*/ }
	private function __clone() { /*empty*/ }

	public static function load_yml($path)
	{
		self::load(yaml_parse_file($path));
	}

	public static function load($settings)
	{
		if (!isset(self::$_settings)) {
			self::$_settings = $settings;
		} else {
			self::$_settings = array_replace_recursive(self::$_settings, $settings);
		}
	}

	public static function set_objects($settings)
	{
		foreach ($settings as $key => $constractor) 
		{
			self::set_object($key, $constractor);
		}
	}

	public static function set_object($key, $constractor)
	{
		if (!isset(self::$_store)) self::$_store = array();
		self::$_store[$key] = array(
			'constractor' => $constractor,
			'instance' => null
		);
	}

	public static function get($key) 
	{
		$pos = strpos($key, ".");
		$val = null;
		if ($pos === false)
		{
			$val = self::$_settings[$key];
		} else {
			$key_arr = preg_split('.', $key);
			foreach($key_arr as $k)
			{
				echo "$k\n";
			}
		}
		return $val;
	}

	public static function set($key, $val)
	{
		self::$_settings[$key] = $val;
	}

	public static function get_object($key)
	{
		$entry =& self::$_store[$key]; // Here must use reference
		$instance = $entry['instance'];
		var_dump($entry);
		if (!isset($entry['instance'])) {
			$instance = $entry['constractor']();
			$entry['instance'] = $instance;
		}
		return $instance;
	}

	public static function get_objects() { return self::$_store; }
}
?>