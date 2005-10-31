<?php
/* Copyright (C) 2003-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2003      Jean-Louis Bergamo   <jlb@j1b.org>
 * Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
 *
 * $Id$
 * $Source$
 *
 */

/**
        \file       htdocs/about.php
        \brief      Fichier page a propos
        \version    $Revision$
*/

require("./pre.inc.php");


llxHeader();


print_titre("Dolibarr");

print "<br>\n";

print $langs->trans("Dolibarr est publi� sous licence GNU/GPL");

print '<p>';
print $langs->trans("Dolibarr est d�velopp� par :");

print '<ul>';
print '<li><a target="blank" href="http://rodolphe.quiedeville.org">Rodolphe Qui�deville</a>';
print '</ul>';
print $langs->trans("D'autres d�veloppeurs y contribuent activement :");
print '<ul>';
print '<li><a target="blank" href="http://www.ipsyn.net">Jean-Louis Bergamo</a></li>';
print '<li><a target="blank" href="http://www.destailleur.fr/">Laurent Destailleur</a></li>';
print '<li>Eric Seigne</li>';
print '<li>Benoit Mortier</li>';
print '</ul>';

print '<p>';

print $langs->trans("Informations").' :';

print '<ul>';
print '<li>';
print '<a target="blank" href="http://www.dolibarr.com/">Site officiel</a>';
print '<li>';
print '<a target="blank" href="http://freshmeat.net/projects/dolibarr/">Page sur Freshmeat</a>';


print '<li>';
print 'Les t�ches en cours de r�alisation sur Dolibarr sont consultables dans le <a target="blank" href="http://savannah.nongnu.org/task/?group=dolibarr">gestionnaire de projet</a> sur Savannah.';
print '</li>';

print '<li>';
print 'Si vous trouvez un bogue dans Dolibarr, vous pouvez en informer les d�veloppeurs sur le <a target="blank" href="http://savannah.nongnu.org/bugs/?group=dolibarr">syst�me de gestion des bogues</a> de Savannah.';

print '</li>';

print '<li>';
print 'Le code source de Dolibarr est consultable par l\'<a target="blank" href="http://savannah.nongnu.org/cgi-bin/viewcvs/dolibarr/dolibarr/">interface web du cvs</a>.';
print '</li>';
print '</ul>';


// \todo Faut-il inviter l'utilisateur � aller sur le site en fran�ais si sa langue n'est pas le fran�ais ?
//if (eregi('^fr_',$langs->getDefaultLang())
//{
    print '<p>';
    print 'Vente / Support';
    print '<ul>';
    print '<li>';
    print 'Contactez Rodolphe Qui�deville sur <a target="blank" href="http://www.dolibarr.com/">www.dolibarr.com</a>';
    print '</li>';
    print '</ul>';
//}


llxFooter('$Date$ - $Revision$');

?>










