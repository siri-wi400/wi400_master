// Per lanciare
CHHJOB CCSID(280)
CALL QP2TERM

cd /www/zendsvr/htdocs/wi400_lzovi/test-install/  

.. Se configurato eseguibile php
run-tests.php -p /usr/local/php_7_1_7/bin/php /www/zendsvr/htdocs/WI400_LZOVI/Test-Install 
.. Se non è configurato l'eseguibile PHP
/usr/local/zendphp7/bin/php /www/zendsvr/htdocs/WI400_LZOVI/Test-Install/run-tests.php -p /usr/local/php_7_1_7/bin/php /www/zendsvr/htdocs/WI400_LZOVI/Test-Install  

/usr/local/zendphp7/bin/php /www/zendsvr/htdocs/WI400/Test-Install/run-tests.php -p /usr/local/zendphp7/bin/php /www/zendphp7/htdocs/WI400/Test-Install  
  
.. Se macchina LINUX
php run-tests.php -p /usr/bin/php /var/www/html/WI400/Test-Install  
  
** ATTENZIONE SU LINUX IL FILE DI CONFIGURAZIONE DEL CLI POTREBBE ESSERE DIVERSO RISPETTO A QUELLO HTTP
** verificare il paremtro tag
short_open_tag = On  
Su ubuntu la configurazione si trova 
/etc/php5/cli
Concedere permessi di scrittura con chmod

* Parametri da configurare (possono essere inseriti nel config.inc nella cartella dei test)
http_port <Porta di ascolto dell'HTTP>
http_server <Indirizzo IP dell'HTTP>
http_user <utente di default per test connessione>

