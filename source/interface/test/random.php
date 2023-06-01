<?php
class java_rand
{
    
    public $seed;
    private $used;
    
    public function __construct($seed=null)
    {
        if ($seed===null) $seed = (int) (time().str_pad(rand(0,9999),4,0,STR_PAD_LEFT));
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
        //echo $this->seed;exit();
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

    function dec2hex($number)
    {
        $hexvalues = array('0','1','2','3','4','5','6','7',
                   '8','9','A','B','C','D','E','F');
        $hexval = '';
         while($number != '0')
         {
            $hexval = $hexvalues[bcmod($number,'16')].$hexval;
            $number = bcdiv($number,'16',0);
        }
        return $hexval;
    }

    // Input: A hexadecimal number as a String.
    // Output: The equivalent decimal number as a String.
    function hex2dec($number)
    {
        $decvalues = array('0' => '0', '1' => '1', '2' => '2',
                   '3' => '3', '4' => '4', '5' => '5',
                   '6' => '6', '7' => '7', '8' => '8',
                   '9' => '9', 'A' => '10', 'B' => '11',
                   'C' => '12', 'D' => '13', 'E' => '14',
                   'F' => '15');
        $decval = '0';
        $number = strrev($number);
        for($i = 0; $i < strlen($number); $i++)
        {
            $decval = bcadd(bcmul(bcpow('16',$i,0),$decvalues[$number{$i}]), $decval);
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
    public $result = array();
    public static $future_secs = 10;
    
    public function __construct($range,$high,$low,$count)
    {
        $this->range = $range;
        $this->high = $high;
        $this->low = $low;
        $this->count = $count;
    }
    
    public function search()
    {
        $sanity = 100000;
        $tries=0;
        $milis = (int) (time()  + search_seeds::$future_secs) * 1000 + rand(0,999);
        //echo $milis;exit();
        while (count($this->result) < $this->count && $tries < $sanity)
        {
            $rand = new java_rand($milis);
            $num = $rand->nextIntMax($this->range);
            if ($num >= $this->low && $num <= $this->high)
                $this->result[$milis] = $num;
            $milis++;
            $tries++;
        }
        return $this->result;
    }
}


/*
$rand=new java_rand();
$rand_test->set_seed(140623071111);
echo 'PHP INT MAX '.PHP_INT_MAX .'<BR>';
echo 'Random with seed 140623071111<BR>';
echo $rand_test->seed . '<BR>';
echo 'Rand ' . $rand_test->nextInt() . '<BR>';
//return ((1L << 48) - 1)
echo 'TestA ' . ((1 << 48) - 1) . '<BR>';
//return (this.seed * 0x5DEECE66DL + 0xBL);
echo 'TestB ' . ($rand_test->seed * 0x5DEECE66D + 0xB) . '<BR>';
echo 'TestC ' . (0x5DEECE66D + 0xB) . '<BR>';
echo 'TestD ' . ($rand_test->seed * 0x5DEECE66D) . '<BR>';
echo 'TestE ' . (0x5DEECE66D) . '<BR>';

//echo $rand_test->nextInt() . '<BR>';

exit();
*/

if (PHP_INT_MAX == 2147483647)
    throw new Exception('This only works on 64 bit system');

$search = new search_seeds(100000,100000,99995,10);
$results = $search -> search();

//echo (time()  + search_seeds::$future_secs) * 1000 + rand(0,999) . '<BR>';

echo "Listed below are upto 10 milliseconds epoch dates that a number<BR>between 99995 and 100000 is produced by java Random using unix epoc in ms <BR>starting from 10 seconds in the future<BR><BR>";

foreach ($results as $key => $value)
{
    echo "milli : $key, value : $value<BR>";
}
if (count($results) < 10)
    echo '100000  tests done, stopping<BR>';

echo 'Complete<BR>';

/*
$rand=new java_rand(140623075555);
echo 'max Int ' . PHP_INT_MAX . '<BR>';
echo 'Seed' . $rand->seed . '<BR>';
echo 'Test A: '. (int)((int) $rand->seed * (int) 0x5DEECE66D + 0xB) . '<BR>';
echo 'Test A.1: '. ($rand->seed) . '<BR>';
echo 'Test A.2: '. (0x5DEECE66D) . '<BR>';
echo 'Test A.3: '.(int)($rand->seed * 0x5DEECE66D) . 'overflow <BR>';
//echo 'Test A.4: '. (int)(160577175182 * 25214903917) .'<BR>';
echo 'Test A.4: '. bcmul (160577175182 , 25214903917) .'<BR>';
echo 'Test B: '. (0xB) . '<BR>';
echo 'Test V: '. ((1 << 48) - 1) . '<BR>'; //Correct
echo 'Next Int 9999 : ' . $rand->nextIntMax(9999) . '<BR>';
echo "Seed is now :" . $rand->seed;
echo '<BR><BR><BR>';

for ($a=0;$a<10;$a++)
{
    $rand->nextIntMax(9999);
    echo $rand->seed . '<BR>';
}
echo '<BR>';
/*
$rand=new java_rand();
for ($a=0;$a<10;$a++)
{
    $rand->nextIntMax(9999);
    echo $rand->seed  . '<BR>';
}
*/
?>
