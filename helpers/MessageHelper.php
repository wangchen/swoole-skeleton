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

	/**
	 * Parse the message
	 *
	 * @return void
	 * @author 
	 **/
	public static function parse($input, $buf_get, $buf_set)
	{
		$log = Logger::getLogger('MessageHelper');
		$messages = array();
		$buf = '';
		$read = self::string_io($input);
		while(true)
		{
			$head_bytes = $read(12);
			if ($head_bytes === false)
			{
				break;
			}
			$head = self::parse_head($head_bytes);
			if ($head === false)
			{
				$buf .= $head;
			} else {
				if (strlen($buf) <> 0) {
					$last_buf = $buf_get();
					$buf_set(NULL);
					if (empty($last_buf))
					{
						$log->warn("Illegal data: " . bin2hex($buf));
					} else {
						// handle the HEAD + BODY
						$msg = $self::parse_msg($last_buf . $buf);
						if ($msg) $messages[] = $msg;
					}

				} 
				$body = $read($head['msg_len']);
				if ($head['msg_len'] === strlen($body)) {
					$messages[] = self::make_msg_obj($head, $body);
				} else {
					$buf_set($head_bytes . $body);
				}
			}
		}
		return $messages;
	}

	public static function make_msg_obj($head, $body)
	{
		return array($head['msg_type'], $body);
	}

	public static function parse_msg($msg)
	{
		$head = self::parse_head(substr($msg, 0, 12));
		if ($head === false // No head
			|| ($head['msg_len'] <> strlen($msg) -12) // Length not match
		) {
			return false;
		}
		return self::make_msg_obj($head, substr($msg, 12));
	}

	public static function parse_head($input) 
	{
		if (strlen($input) >= 12) {
			$magic_bytes = self::bin2str(unpack('c4', substr($input, 0, 4)));
			if ($magic_bytes === 'mrch') {
				$msg_len = unpack('I', substr($input, 4, 8))[1];
				$msg_type = unpack('I', substr($input, 8, 12))[1];
				return array(
					'msg_len' => $msg_len,
					'msg_type' => $msg_type
				);
			}
		}
		return false;
	}

	public static function bin2str($bytes) {
		$str = '';
		foreach ($bytes as $byte)
		{
			$str .= chr($byte);
		}
		return $str;
	}
	public static function string_io($input)
	{
		$pos = 0;
		$len = strlen($input);
		return function($read_length) use ($input, &$pos, $len)
		{
			$ret = false;
			if ($read_length > 0 && $pos < $len)
			{
				$offest = ($pos + $read_length > $len) ? $len : $read_length;
				$ret = substr($input, $pos, $offest);
				$pos += $offest;
			}
			return $ret;
		};
	}
}
?>