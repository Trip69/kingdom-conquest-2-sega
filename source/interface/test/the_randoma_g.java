import java.awt.*;
import java.applet.Applet;
import java.util.Random;

public class the_randoma_g extends Applet {

  public void paint( Graphics g )
  {
    
    my_random rand = new my_random(140623075555L);
    
    g.drawString( "Seed First :" + rand.seed, 10, 15 );

    long testa = (rand.seed * 0x5DEECE66DL + 0xBL);
    long testb = ((1L << 48) - 1);
    long testc = (rand.seed * 0x5DEECE66DL);
    g.drawString( "(rand.seed * 0x5DEECE66DL + 0xBL) :" + testa, 10, 30 );
    g.drawString( "((1L << 48) - 1) :" + testb, 10, 45 );
    g.drawString( "(rand.seed * 0x5DEECE66DL) :" + testc, 10, 60 );
    g.drawString( "(160577175182 * 25214903917) :" + (160577175182L * 25214903917L), 10, 75 );
    
    
    g.drawString( "Next Int 9999 :" + rand.nextInt(9999), 10, 200 );
    g.drawString( "Seed is now :" + rand.seed, 10, 215 );
/*
    for (int a=1;a<11;a++)
    {
        rand.nextInt(9999);
        g.drawString( "Seed :" + rand.seed , 10, a * 15 + 15 );
    }
    
    g.drawString( "Seed A :" + rand.seed, 10, 15 );
    g.drawString( "T1 ((1L << 48) - 1) :" + rand.testa(), 10, 45 );
    g.drawString( "T2 (this.seed * 0x5DEECE66DL + 0xBL) :" + rand.testb(), 10, 60 );
    g.drawString( "T3 (0x5DEECE66DL + 0xBL) :" + rand.testc(), 10, 75 );
    g.drawString( "T4 (this.seed * 0x5DEECE66DL) :" + rand.testd(), 10, 90 );
    g.drawString( "T5 (0x5DEECE66DL) :" + rand.teste(), 10, 105 );
    g.drawString( "T6 (160577167850 * 0x5DEECE66DL) :" + rand.testf(), 10, 120 );
    g.drawString( "T7 (this.seed) :" + rand.testg(), 10, 135 );
    
    
    g.drawString( "T99  :" + rand.nextInt(), 10, 180 );    
*/

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
        
        public int nextInt()
        {
            return next(32);
        }
        
        public int nextInt(int n)
        {
            int bits, val;
            do
            {
                bits = next(31);
                val = bits % n;
            } while (bits - val + (n - 1) < 0);
            return val;
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
            return (this.seed * 0x5DEECE66DL);
        }
        
        protected synchronized long testd()
        {
            return (this.seed * 0x5DEECE66DL);
        }

        protected synchronized long teste()
        {
            return (0x5DEECE66DL);
        }

        protected synchronized long testf()
        {
            return (160577167850L * 0x5DEECE66DL);
        }

        protected synchronized long testg()
        {
            return (this.seed);
        }
        
    }

}