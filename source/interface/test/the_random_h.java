import java.awt.*;
import java.applet.Applet;
import java.util.Random;

public class the_random_h extends Applet {

  public void paint( Graphics g )
  {
    //Random rand = new Random();
    //rand.setSeed(140623071111L);
    //Integer result = rand.nextInt();
    //g.drawString( "Random " +  result.toString(), 50, 50 );
    
    my_random rand = new my_random(140623071111L);
    g.drawString( "Seed A :" + rand.seed, 10, 10 );
    g.drawString( "Rand :" + rand.next(32), 10, 40 );
    g.drawString( "T1 :" + rand.testa(), 10, 60 );
    g.drawString( "T2 :" + rand.testb(), 10, 80 );
    g.drawString( "T3 :" + rand.testc(), 10, 100 );
  }

    private class my_random
    {
        public long seed;
        
        public my_random()
        {
            this(System.currentTimeMillis());
        }
        
        public my_random(long seed)
        {
            setSeed(seed);
        }
        
        public synchronized void setSeed(long seed)
        {
            this.seed = (seed ^ 0x5DEECE66DL) & ((1L << 48) - 1);
        }
        
        protected synchronized int next(int bits)
        {
            seed = (seed * 0x5DEECE66DL + 0xBL) & ((1L << 48) - 1);
            return (int) (seed >>> (48 - bits));
        }
        
        protected synchronized long testa()
        {
            return ((1L << 48) - 1);
        }
        
        protected synchronized long testb()
        {
            return (this.seed * 0x5DEECE66DL + 0xBL);
        }

        protected synchronized long testc()
        {
            return (0x5DEECE66DL + 0xBL);
        }
        
        
    }

}