Interface OSC et Dolibarr
-------------------------

on va l'appeler version 0.

INSTALATION SUR SITE OSC :

copier le r�pertoire sur le seveur web
le r�pertoire lib qui  contient la librairie nusoap
le r�pertoire includes : le fichier configure.php
les fichiers ws_*

Configuration :
Tout est dans le fichier configure.php sous forme de define (acc�s � la BDD OSC et def du langage par d�faut)

C'est tout !

TEST DE L'INSTALLATION

Pour tester l'installation utiliser le client basique fourni � installer sur un serveur web avec php4.

r�pertoire includes : Par d�faut on pointe sur le site osc.tiris.info o� j'ai mis � disposition les web services sur un environnement de test. D�finir le r�pertoire o� se trouvent les web_services (www.siteosc/webservices)

Ouvrir la page index.html
les liens acc�dent � certaines m�thodes des webservices
si on obtient une r�ponse Fault il y a un probl�me (en principe le message perlet de trouver!!


TEST DEPUIS DOLIBARR

L'int�gration dans Dolibarr sera dispo via le cvs.

Une boutique OSC pour tester (avec les webservices install�s) est ici http://osc.tiaris.info.
Cr�ez des clients, commandes... Ca fera plus r�el. Ca ne vous co�tera rien, mais vous n'aurez rien non plus !

********************
ATTENTION : ce n'est que le tout d�but de ce d�veloppement. Entre autre il n'y a pas encore de contr�le d'acc�s, donc n'installer que sur des syst�mes en tests et non sur des sites en production.
********************

Consulter le wiki pour la doc et le suivi : 
	http://www.dolibarr.com/wikidev/index.php/Discussion_Utilisateur:Tiaris
Suivez la mailing list et le forum pour les discussions sur le sujet (et participez!).

Jean Heimburger			jean@tiaris.info







