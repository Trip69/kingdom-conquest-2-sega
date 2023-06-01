<?php
class java_rand
{
    
    public $seed;
    
    public function __construct($seed=null)
    {
        if ($seed===null) $seed = (int) (time().str_pad(rand(0,9999),4,0,STR_PAD_LEFT));
        $this->set_seed($seed);
    }
    
    public function set_seed($seed)
    {
        $this->seed = ($seed ^ 0x5DEECE66D) & ((1 << 48) - 1);
    }
    
    public function next($bits)
    {
        //seed = (seed * 0x5DEECE66DL + 0xBL) & ((1L << 48) - 1);
        $this->seed = (int) ((int) $this->seed * (int) 0x5DEECE66D + (int) 0xB) & ((1 << 48) - 1);
        $ret=(int) $this->logical_right_shift($this->seed , (48 - $bits));
        return $ret;
    }
    
    public function nextInt()
    {
        return $this->next(32);
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
