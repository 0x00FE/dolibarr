<?php
/*---------------------------------------------
/ Webservices OSC pour dolibarr
/ configuration des clients
/
/ Jean Heimburger			juin 2006
----------------------------------------------*/

//base url des webservices
define(OSCWS_DIR,'http://www.tiaris.info/catalog/ws_OSC/');
//affichages dans la page d'accueil
define(OSC_MAXNBCOM, 5);
define(OSC_ORDWAIT,'4'); // code du statut de commande en attente
define(OSC_ORDPROCESS,'1'); // code du statut de commande en traitement
//
define(OSC_ENTREPOT, 1); //l'entrepot li� au stock du site web
define(TX_CURRENCY, 1); // le taux de conversion monnaie site osc - monnaie dolibarr
define(NB_DECIMALS, 0);
define(FK_PORT, 0); // l'id du service frais de port d�fini. 

// fonctions
/**
*      \brief      assure la conversion en monnaie de dolibarr
*      \param      oscid      Id du produit dans OsC 
*	   \param	   prodid	  champ r�f�rence 	
*      \return     int     <0 si ko, >0 si ok
*/
	function convert_price($price)
	{
		return round($price * TX_CURRENCY, NB_DECIMALS);
	}
?>
