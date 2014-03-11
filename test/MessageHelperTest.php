<?php 
require('log4php/Logger.php');
Logger::configure(__DIR__ . '/../logging.xml');
require __DIR__ . '/../helpers/MessageHelper.php';

/**
 * MessageHelper test cases
 * 
 * @package default
 * @author 
 **/
class MessageHelperTest extends PHPUnit_Framework_TestCase
{
    public function testParseMsgOneMessageInTwoFrames()
    {
        $mh = new MessageHelper();
        // part 1
        $input = pack('N2na*', 21, 1, 2, "hello");
        $msg_array = $mh->parse(1, $input);
        // incomplete message
        $this->assertEquals(0, count($msg_array));
        $input = pack('a*', ' world');
        $msg_array = $mh->parse(1, $input);
        $this->assertEquals(1, count($msg_array));
    }

    public function testParseMsgTwoMessagesInThreeFrames1()
    {
        $mh = new MessageHelper();
        $input = pack('N2na*', 23, 1, 2, "hello");
        $msg_array = $mh->parse(1, $input);
        // incomplete message
        $this->assertEquals(0, count($msg_array));

        // Frame 2
        $input = pack('a*', ' world 1');
        // The 1st complete msg
        $msg_array = $mh->parse(1, $input);
        $this->assertEquals(1, count($msg_array));

        // Frame 3
        $input = pack('N2na*', 23, 1, 2, "hello world 2");
        $msg_array = $mh->parse(1, $input);
        // The 1st complete msg
        $this->assertEquals(1, count($msg_array));
    }

    public function testParseMsgTwoMessagesInThreeFrames2()
    {
        $mh = new MessageHelper();
        $msg_get = function() use (&$cache) {
            return $cache;
        };
        $msg_set = function($buf) use (&$cache){
            $cache = $buf;
        };
        // Frame 1
        $input =  pack('N2na*', 23, 1, 2, "hello");
        var_dump(pack("N", 1));
        $msg_array = $mh->parse(1, $input);
        // incomplete message
        // Get nothing, things stored in 
        $this->assertEquals(0, count($msg_array));

        // Frame 2
        $input = pack('a*', ' world 1') . pack("N2", 23, 1);
        // The 1st complete msg
        $msg_array = $mh->parse(1, $input);
        echo "cache: " . strlen($cache) . "\n";
        $this->assertEquals(1, count($msg_array));

        // Frame 3
        $input = pack('na*', 2, "hello world 2");
        $msg_array = $mh->parse(1, $input);
        // The 1st complete msg
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