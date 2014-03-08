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
		$head_length = 12;
		echo "Enter!\n";
		$log = Logger::getLogger('MessageHelper');
		$messages = array();
		$buf = ''; // MUST no head here
		$read = self::string_reader($input);
		while(true)
		{
			echo "LOOP\n";
			$current_bytes = $read($head_length);
			if ($current_bytes === false)
			{
				break;
			}
			$head = self::parse_head($current_bytes);
			if ($head === false)
			{
				$buf .= $current_bytes;
			} else {
				// process the last message
				if (strlen($buf) <> 0) { 
					$last_buf = $buf_get();
					if (empty($last_buf))
					{
						// current data ($buf + $current_bytes) has no head
						$log->warn("Illegal data: " . bin2hex($buf));
					} else {
						$msg = $self::parse_msg($last_buf . $buf . $current_bytes);
						// All data will be dropped if parse failed 
						if (gettype($msg) === 'array') $messages[] = $msg;
					}
					// Clean buffers
					$buf_set(NULL);
					$buf = '';
				} 
				// process the current message
				$body = $read($head['msg_len']);
				if ($head['msg_len'] === strlen($body)) {
					$messages[] = self::make_msg_obj($head, $body);
				} else {
					$buf .= $current_bytes . $body;
				}
			} // end if
		} // end while

		// Finally check buffers
		if (strlen($buf) <> 0) {
			$bytes = $buf_get() . $buf;
			$head = self::parse_head(substr($bytes, 0, $head_length));
			if ($head === false)
			{
				// No head, empty gbuf
				$buf_set(null);
			} else {
				$whole_len = $head['msg_len'] + $head_length;
				if (strlen($bytes) < $whole_len)
				{
					// a incomplete message
					echo "a incomplete message\n";
					$buf_set($bytes);
				} else if (strlen($bytes) >= $whole_len) {
					$msg = self::parse_msg(substr($bytes, 0, $whole_len));
					if (gettype($msg) === 'array') {
						// a complete message
						echo "a complete message\n";
						$messages[] = $msg;
						$buf_set(null);
					} else {
						// This msg is corrupt
						echo "This msg is corrupt\n";
						$buf_set(null);
					} 
					if (strlen($bytes) > $whole_len) 
					{
						echo "Store the rest bytes, length: $whole_len\n";
						$buf_set(substr($bytes, $whole_len));
					}
				}

			}
		} 
		return $messages;
	}

	public static function make_msg_obj($head, $body)
	{
		return array($head['msg_type'], $body);
	}

	// TODO: should get more return value
	// - -1 : illegal msg
	// - not complete msg
	// - message
	public static function parse_msg($msg)
	{
		$head = self::parse_head(substr($msg, 0, 12));
		if ($head === false) { // No head
			return -1;
		} else if ($head['msg_len'] <> strlen($msg) -12) {
			// todo: fix
			// Length not match
			return -2;
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
	public static function string_reader($input)
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