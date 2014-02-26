<?php 
class Utils {
	public static function require_one($path)
	{
		$base = Application::get('app_dir');	
		require($base . '/' . $path);
	}

	public static function require_all($dir)
	{
		$base = Application::get('app_dir');
		if ($handle = opendir($base . '/' . $dir)) {
	        while (false !== ($entry = readdir($handle))) {
	            if ($entry != "." && $entry != "..") {
	                require($dir . '/' . $entry);
	            }
	        }
	        closedir($handle);
	   }
	}
}

?>