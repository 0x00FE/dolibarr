<?PHP
$subject = "Subject: EUCD.INFO promesse de don de $don->amount euros pour sauver la copie priv�e";

$body ="
EUCD.INFO  vous  remercie  de  la  promesse  de  don  que  vous  venez
d'enregistrer:

Pr�nom: $don->prenom
Nom: $don->nom
Montant: $don->amount
Au plus tard le: $date_limite

L'initiative EUCD.INFO[1]  a pour objet de  pr�server l'int�r�t public
menac�  par  la   transposition  de  la  directive  du   22  mai  2001
(EUCD)[2]. Pour y parvenir elle entend:

    * Produire et proposer un avant-projet de loi alternatif.
    * Le promouvoir aupr�s des personnes responsables.
    * Entrer dans le cercle de consultation.

Votre  contribution  financi�re  servira  principalement �  payer  des
juristes dont les t�ches seront d'analyser la situation et de proposer
un avant-projet  de loi transposant  la directive dans le  respect des
droits des utilisateurs et du public.

Vous pourrez suivre  les progr�s de notre action  gr�ce � l'�ch�ancier
que nous remettons  r�guli�rement � jour sur la page  de garde du site
http://eucd.info/. N'h�sitez  pas � nous poser des  questions par mail
si vous le souhaitez, � l'adresse contact@eucd.info.

L'association loi 1901 FSF France est d'int�r�t g�n�ral[3] et les dons
qui lui sont fait ouvrent  droit � une r�duction d'imp�t. Vous pourrez
en b�n�ficier gr�ce au re�u[4]  qui vous sera adress� d�s reception du
montant promis de $don->amount euros.


[1] EUCD.INFO: http://eucd.info/
[2] Directive du 22 mai 2001:
    http://europa.eu.int/smartapi/cgi/sga_doc?smartapi!celexapi!prod!CELEXnumdoc&lg=fr&numdoc=32001L0029&model=guichett
[3] FSF France et dons: http://france.fsfeurope.org/donations/
[4] Mod�le de re�u: http://france.fsfeurope.org/donations/formulaire.fr.html
";
?>
