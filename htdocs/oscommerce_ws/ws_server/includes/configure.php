<?php
/*---------------------------------------------
/ Webservices OSC pour dolibarr
/ configuration et param�tres 
/
/ Jean Heimburger			juin 2006
----------------------------------------------*/

/* param�tres de connexion � OSC */
define("DB_SERVER","localhost");
define("DB_SERVER_USERNAME", "root");
define("DB_SERVER_PASSWORD", "");
define("DB_DATABASE", "tahitirimai");
// chemin vers sources OSC admin 
define('OSCADMIN', '/home/jean/projets/osc_tiaris/admin/');

/* constantes utiles */
define("OSC_LANGUAGE_ID",1);

define(OSC_URL, 'http://osc-tiaris/'); // url du site OSC
?>
