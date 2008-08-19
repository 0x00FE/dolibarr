#!/bin/sh
#
# R�gis Houssin - regis@dolibarr.fr
#
# ---------------------------- globales
# ---------------------------- base mysql
DIALOG=${DIALOG=dialog}
fichtemp=`tempfile 2>/dev/null` || fichtemp=/tmp/test$$
trap "rm -f $fichtemp" 0 1 2 5 15
$DIALOG --title "Purge de Dolibarr" --clear \
        --inputbox "Nom de la base Mysql :" 16 51 2> $fichtemp
valret=$?
case $valret in
  0)
base=`cat $fichtemp`;;
  1)
exit;;
  255)
exit;;
esac
# ---------------------------- compte admin mysql
DIALOG=${DIALOG=dialog}
fichtemp=`tempfile 2>/dev/null` || fichtemp=/tmp/test$$
trap "rm -f $fichtemp" 0 1 2 5 15
$DIALOG --title "Purge de Dolibarr" --clear \
        --inputbox "Compte Admin Mysql (ex: root):" 16 51 2> $fichtemp

valret=$?

case $valret in
  0)
admin=`cat $fichtemp`;;
  1)
exit;;
  255)
exit;;
esac
# ---------------------------- mot de passe admin mysql
DIALOG=${DIALOG=dialog}
fichtemp=`tempfile 2>/dev/null` || fichtemp=/tmp/test$$
trap "rm -f $fichtemp" 0 1 2 5 15
$DIALOG --title "Purge de Dolibarr" --clear \
        --inputbox "Mot de passe du compte Admin Mysql :" 16 51 2> $fichtemp

valret=$?

case $valret in
  0)
passwd=`cat $fichtemp`;;
  1)
exit;;
  255)
exit;;
esac
# ---------------------------- chemin d'acc�s du r�pertoire documents
DIALOG=${DIALOG=dialog}
fichtemp=`tempfile 2>/dev/null` || fichtemp=/tmp/test$$
trap "rm -f $fichtemp" 0 1 2 5 15
$DIALOG --title "Purge de Dolibarr" --clear \
        --inputbox "Chemin complet du r�pertoire documents (ex: /var/www/dolibarr/documents)- pas de / � la fin :" 16 51 2> $fichtemp

valret=$?

case $valret in
  0)
docs=`cat $fichtemp`;;
  1)
exit;;
  255)
exit;;
esac
# ---------------------------- confirmation
DIALOG=${DIALOG=dialog}
$DIALOG --title "Purge de Dolibarr" --clear \
        --yesno "confirmez-vous ces informations ? \n base Mysql : '$base' \n compte admin : '$admin' \n mot de passe : '$passwd' \n r�pertoire documents : '$docs'" 15 40

case $? in
        0)      echo "Ok, d�but du processus...";;
        1)      exit;;
        255)    exit;;
esac

# ---------------------------- run sql file
mysql -u$admin -p$passwd $base < data_demo.sql
