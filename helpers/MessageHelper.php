<?php
class MessageHelper
{
	const MSG_ILLEGAL = 'Illegal message!';
	/**
	 * Verify message
	 *
	 * @return bool
	 * @author kitta
	 **/
	public static function verify($msg)
	{
		return true;
	}

	/**
	 * Unpack message
	 * 
	 * @return array, contains key 'cmd' at the least
	 * @author 
	 **/
	public static function unpack($msg)
	{
		$arr= explode(':', $msg);
		return array(
			'cmd' => intval($arr[0]),
			'data' => $arr[1]
		);
	}

	/**
	 * Pack message
	 *
	 * @return string
	 * @author kitta
	 **/
	public static function pack($msg)
	{
		return $msg;
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 * @author 
	 **/
	public static function encrypt($msg)
	{
		return $msg;
	}

	/**
	 * undocumented function
	 *
	 * @return void
	 * @author 
	 **/
	public static function decrypt($msg)
	{
		return $msg;
	}

	public static function send($serv, $fd, $output)
	{
        Logger::getLogger('traffic')->info(' DOWN ' . $output);
        $output_pack = MessageHelper::pack($output);
        $output_encrypt = MessageHelper::encrypt($output);
        $serv->send($fd, $output_encrypt);
	}
}

?>