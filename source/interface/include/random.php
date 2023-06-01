<?php
//if (PHP_INT_MAX == 2147483647) throw new Exception('This only works on 64 bit system');

class java_rand
{
    
    public $seed;
    private $used;
    
    public function __construct($seed=null)
    {
        if ($seed===null) $seed = round(microtime(true) * 1000);
        $this->set_seed($seed);
    }
    
    public function set_seed($seed)
    {
        $this->seed = ($seed ^ 0x5DEECE66D) & ((1 << 48) - 1);
    }
    
    private function java_multi($large_a,$large_b)
    {
        $mul_str = bcmul($large_a,$large_b);
        $hex = substr($this->dec2hex($mul_str),-16);
        return (int) $this->hex2dec($hex);
    }
    
    public function next($bits)
    {
        $multi = $this->java_multi($this->seed,0x5DEECE66D);
        $this->seed = ($multi + 0xB) & ((1 << 48) - 1);
        $ret=(int) $this->logical_right_shift($this->seed , (48 - $bits));
        return $ret;
    }
    
    public function nextIntMax($max)
    {
        $bits=null;
        $val=null;
        do
        {
            $bits = $this->next(31);
            $val = $bits % $max;
        } while ($bits - $val + ($max-1) < 0);
        return $val;
    }

    function logical_right_shift( $int , $shft )
    {
        return ( $int >> $shft ) & ( PHP_INT_MAX >> ( $shft - 1 ) );
    }    

    private static $hexvalues = array('0','1','2','3','4','5','6','7',
                               '8','9','A','B','C','D','E','F');

    function dec2hex($number)
    {
        //this function is where the script times out on occasion
        $timer = new timer(true);
        $hexval = '';
         while($number != '0')
         {
            $hexval = java_rand::$hexvalues[bcmod($number,'16')].$hexval;
            $number = bcdiv($number,'16',0);
            if ($timer->get_time() > 10000)
            {
                file_put_contents('random_error.txt',"Random took more than 10 seconds\r\n",FILE_APPEND);
                return $hexval;
            }
        }
        return $hexval;
    }

    private static $decvalues = array('0' => '0', '1' => '1', '2' => '2',
                   '3' => '3', '4' => '4', '5' => '5',
                   '6' => '6', '7' => '7', '8' => '8',
                   '9' => '9', 'A' => '10', 'B' => '11',
                   'C' => '12', 'D' => '13', 'E' => '14',
                   'F' => '15');

    
    // Input: A hexadecimal number as a String.
    // Output: The equivalent decimal number as a String.
    function hex2dec($number)
    {
        $decval = '0';
        $number = strrev($number);
        for($i = 0; $i < strlen($number); $i++)
        {
            $decval = bcadd(bcmul(bcpow('16',$i,0),java_rand::$decvalues[$number{$i}]), $decval);
        }
        return $decval;
    }

}

class search_seeds
{
    
    public $range;
    public $high;
    public $low;
    public $count;
    public $seed;
    public $result = array();
    private $timer;
    //public static $future_secs = 10;
    
    public function __construct($range,$high,$low,$seed,$count)
    {
        $this->range = $range;
        $this->high = $high;
        $this->low = $low;
        $this->count = $count;
        $this->seed = $seed;
        $this->timer = new timer(true);
    }
    
    public function search()
    {
        $tests = 5000;
        $tries=0;
        $rand = new java_rand($this->seed);
        while (count($this->result) < $this->count && $tries < $tests)
        {
            if ($this->timer->get_time() > 30000)
                return round(microtime() * 1000);
            $num = $rand->nextIntMax($this->range);
            if ($num >= $this->low && $num <= $this->high)
            {
                $this->result[$this->seed] = $num;
                $this->seed+=500;
            }
            $tries++;
            $this->seed++;
            $rand->set_seed($this->seed);
        }
        return $this->result;
    }
}

?>
