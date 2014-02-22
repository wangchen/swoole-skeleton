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
	function verify($msg)
	{
		return true;
	}

	/**
	 * Unpack message
	 * 
	 * @return array, contains key 'cmd' at the least
	 * @author 
	 **/
	function unpack($msg)
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
	function pack($msg)
	{
		return $msg;
	}
}

?>