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
    public function testParseMsg()
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

    public function testStringIO()
    {
        $read = MessageHelper::string_io("0123456789");
        echo $read(5)."\n";
        echo $read(6)."\n";
        echo $read(6)."\n";

        $read = MessageHelper::string_io("0123456789");
        echo $read(10)."\n";      
        
        $read = MessageHelper::string_io("0123456789");
        echo $read(-1)."\n";   
    }
} // END class MessageHelperTest extends PHPUnit_Framework_TestCase

 ?>