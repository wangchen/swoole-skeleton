<?php
namespace controllers\login;
use \Application;
use \Logger;
use \MessageHelper;

function login($serv, $fd, $from_id, $input)
{
	$log = Logger::getLogger('app');
	$mc = Application::get_object('memcache');
	$log->debug("Memecache version is {$mc->getVersion()}");
	MessageHelper::send($serv, $fd, "done");
}
?>