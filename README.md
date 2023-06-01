![KC2](https://github.com/Trip69/kingdom-conquest-2-sega/blob/main/source/interface/images/kc2.jpg?raw=true)
# kingdom-conquest-2-sega
PHP bot to play Kingdom Conquest 2 by SEGA. Designed to play thousands of bots via direct connection or by proxy. KC2 has been shut down by SEGA, so this code is just for reference.

The code is written for PHP 7.2. It only uses one library, httpful, for HTTP interactions.
The code uses a MySQL server to save the details of all the bots and progression. I have lost the schematics for these, sorry.

The code was used to control over 2000 bots on SEGA servers and includes many exploits that I found while coding it. The greatest of these was the ability to log on to ANY user account due to bad coding by SEGA in the authentication process.

 The code is designed to run a shared server by cron. If proxies are to be used then the outbound connection to 8080 8081 and  whatever others are needed to be set.
 
 # Render Engine
 
 I I wrote my own (very simple) rendering engine to generate the output. Using html files with XXXfooXXX as placesholders for generated text.
 in this project it is located [here](source/interface/include/template.php)
