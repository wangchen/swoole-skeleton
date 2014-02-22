<?php
namespace controllers\login;
use \Application;
use \Logger;
function login()
{
	$log = Logger::getLogger('app');
	$mc = Application::get_object('memcache');
	$log->debug("Memecache version is {$mc->getVersion()}");
	return 'OK';
}
?>