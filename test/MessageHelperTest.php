<?php 
require('log4php/Logger.php');
require __DIR__ . '/../helpers/MessageHelper.php';

/**
 * MessageHelper test cases
 * 
 * @package default
 * @author 
 **/
class MessageHelperTest extends PHPUnit_Framework_TestCase
{
    public function testParseMsgBasic()
    {
        $input = pack('c4I2a*', ord('m'), ord('r'), ord('c'), ord('h'), 11, 2, "hello world");
        echo 'INPUT:' . bin2hex($input) . "\n";
        $msg_get = function(){
            return NULL;
        };
        $msg_set = function(){
            return NULL;
        };
        $msg_array = MessageHelper::parse($input, $msg_get, $msg_set);
        var_dump($msg_array);

    }

    public function testParseMsgOneMessageInTwoFrames()
    {
        $cache = null;
        $msg_get = function() use (&$cache) {
            return $cache;
        };
        $msg_set = function($buf) use (&$cache){
            $cache = $buf;
        };
        // part 1
        $input = pack('c4I2a*', ord('m'), ord('r'), ord('c'), ord('h'), 11, 2, "hello");
        $msg_array = MessageHelper::parse($input, $msg_get, $msg_set);
        // incomplete message
        $this->assertEquals(strlen($input), strlen($cache));
        // Get nothing, things stored in 
        $this->assertEquals(0, count($msg_array));
        $input = pack('a*', ' world');
        $msg_array = MessageHelper::parse($input, $msg_get, $msg_set);
        $this->assertEquals(1, count($msg_array));
    }

    public function testParseMsgTwoMessagesInThreeFrames1()
    {
        $cache = null;
        $msg_get = function() use (&$cache) {
            return $cache;
        };
        $msg_set = function($buf) use (&$cache){
            $cache = $buf;
        };
        $magic_bytes = pack("c4", ord('m'), ord('r'), ord('c'), ord('h'));
        // Frame 1
        $input = $magic_bytes . pack('I2a*', 13, 2, "hello");
        $msg_array = MessageHelper::parse($input, $msg_get, $msg_set);
        // incomplete message
        $this->assertEquals(strlen($input), strlen($cache)); // len: 17
        // Get nothing, things stored in 
        $this->assertEquals(0, count($msg_array));

        // Frame 2
        $input = pack('a*', ' world 1') . $magic_bytes;
        // The 1st complete msg
        $msg_array = MessageHelper::parse($input, $msg_get, $msg_set);
        echo "cache: " . strlen($cache) . "\n";
        $this->assertEquals(strlen($magic_bytes), strlen($cache));
        $this->assertEquals(1, count($msg_array));

        // Frame 3
        $input = pack('I2a*', 13, 2, "hello world 2");
        $msg_array = MessageHelper::parse($input, $msg_get, $msg_set);
        // The 1st complete msg
        $this->assertEquals(null, $cache);
        $this->assertEquals(1, count($msg_array));
    }

    public function testParseMsgTwoMessagesInThreeFrames2()
    {
        $cache = null;
        $msg_get = function() use (&$cache) {
            return $cache;
        };
        $msg_set = function($buf) use (&$cache){
            $cache = $buf;
        };
        $magic_bytes = pack("c4", ord('m'), ord('r'), ord('c'), ord('h'));
        // Frame 1
        $input = $magic_bytes . pack('I2a*', 13, 2, "hello");
        $msg_array = MessageHelper::parse($input, $msg_get, $msg_set);
        // incomplete message
        $this->assertEquals(strlen($input), strlen($cache)); // len: 17
        // Get nothing, things stored in 
        $this->assertEquals(0, count($msg_array));

        // Frame 2
        $input = pack('a*', ' world 1') . $magic_bytes. pack("I2", 13, 2);
        // The 1st complete msg
        $msg_array = MessageHelper::parse($input, $msg_get, $msg_set);
        echo "cache: " . strlen($cache) . "\n";
        $this->assertEquals(strlen($magic_bytes) + 8, strlen($cache));
        $this->assertEquals(1, count($msg_array));

        // Frame 3
        $input = pack('a*', "hello world 2");
        $msg_array = MessageHelper::parse($input, $msg_get, $msg_set);
        // The 1st complete msg
        $this->assertEquals(null, $cache);
        $this->assertEquals(1, count($msg_array));
    }

    public function testStringIO()
    {
        $read = MessageHelper::string_reader("0123456789");
        echo $read(5)."\n";
        echo $read(6)."\n";
        echo $read(6)."\n";

        $read = MessageHelper::string_reader("0123456789");
        echo $read(10)."\n";      
        
        $read = MessageHelper::string_reader("0123456789");
        echo $read(-1)."\n";   
    }
} // END class MessageHelperTest extends PHPUnit_Framework_TestCase

 ?>