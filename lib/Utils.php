<?php 
class Utils {
	public static function require_all($base, $dir)
	{
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