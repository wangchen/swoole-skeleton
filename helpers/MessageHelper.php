<?php
class MessageHelper
{
	const MSG_ILLEGAL = 'Illegal message!';

	public function __construct() 
	{
		$this->buf = array();
	}

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

	private function set_buf($fd, $bytes) {
		$this->buf[$fd] = $bytes;
	}

	private function get_buf($fd) {
		return array_key_exists($fd, $this->buf) ? $this->buf[$fd] : null;	
	}

	/**
	 * Parse the message
	 * unsigned int   : 4 bytes, total length
	 * unsigned int   : 4 bytes, UID
	 * unsigned short : 2 bytes, message type
	 * bytes          : body
	 *
	 * body_length = total_length - 10
	 * @return void
	 * @author 
	 **/
	public function parse($fd, $input)
	{
		echo "Enter! " . strlen($input) . " $input\n";
		$log = Logger::getLogger('MessageHelper');
		$messages = array();
		$read = self::string_reader($input);

		$gbuf = $this->get_buf($fd);

		if (!empty($gbuf)) {
			echo "Found thins in gbuf\n";
			$gbuf_length = strlen($gbuf);
			if ($gbuf_length >= 4) {
				echo "gbuf contains length field\n";
				$total_length = unpack("N", substr($gbuf, 0, 4))[1];
				$rest_length = $total_length - $gbuf_length;
				$rest_bytes = $read($rest_length);
				echo "read $rest_length == " . strlen($rest_bytes) . "\n";
				switch ( strlen($rest_bytes) - $rest_length )
				{
					case 0:
						// a complete msg
						$this->set_buf($fd, null);
						echo "MSG LEN: " . strlen($gbuf . $rest_bytes) ."\n";
						$msg = self::parse_msg($gbuf . $rest_bytes);
						if (gettype($msg) === 'array') {
							echo "a complete message\n";
							$messages[] = $msg;
						} else {
							echo "WTF! $msg \n";
						}
						break;
					case -1:
						// a incomplete msg
						$this->set_buf($fd, $gbuf . $rest_bytes);
						break;
					default;
						; // should not happen
				}
			}
		}
		// Here gbuf should be empty
		if (empty($gubf) === false) {
			die("Global buf should be empty!");
		}
		while(true)
		{
			echo "LOOP\n";
			$current_bytes = $read(4);
			if ($current_bytes === false)
			{
				break;
			}
			$length = unpack("N", $current_bytes)[1];
			if ($length == false) continue; // next byte
			$bytes = $read($length);
			$cmp = strlen($bytes) - $length + 4;
			echo strlen($bytes) . "<=>" . $length . "\n";
			if ($cmp === 0){
				// a complete msg
				$msg = self::parse_msg($current_bytes . $bytes);
				if (gettype($msg) === 'array') {
					echo "a complete message\n";
					$messages[] = $msg;
				}
			} else if ($cmp < 0) {
				// a incomplete msg
				echo "a incomplete msg\n";
				$this->set_buf($fd, $current_bytes . $bytes);
			}else {
				echo "WTF!\n";
				; // should not happen
			}

		} // end while
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
	public function parse_msg($msg)
	{
		$head = self::parse_head(substr($msg, 0, 10));
		if ($head === false) { // No head
			return -1;
		} else if ($head['msg_len'] <> strlen($msg)) {
			// todo: fix
			// Length not match
			return -2;
		}
		return self::make_msg_obj($head, substr($msg, 10));
	}

	public function parse_head($input) 
	{
		if (strlen($input) >= 10) {
			$msg_len = unpack('N', substr($input, 0, 4))[1];
			$uid = unpack('N', substr($input, 4, 8))[1];
			$msg_type = unpack('n', substr($input, 8, 10))[1];
			return array(
				'uid' => $uid,
				'msg_len' => $msg_len,
				'msg_type' => $msg_type
			);
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