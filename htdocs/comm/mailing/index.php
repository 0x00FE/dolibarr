<?php
/* Copyright (C) 2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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

/*!
  \file       htdocs/comm/index.php
  \ingroup    commercial
  \brief      Page acceuil de la zone mailing
  \version    $Revision$
*/
 
require("./pre.inc.php");

if ($user->societe_id > 0)
{
  accessforbidden();
}
	  
$langs->load("commercial");
$langs->load("orders");

llxHeader('','Mailing');

/*
 *
 */

print_titre("Espace mailing");

print '<table border="0" width="100%">';

print '<tr><td valign="top" width="30%">';

/*
 * Derni�res actions commerciales effectu�es
 *
 */

$sql = "SELECT count(*), client";
$sql .= " FROM ".MAIN_DB_PREFIX."societe";
$sql .= " WHERE client in (1,2)";
$sql .= " GROUP BY client";



if ( $db->query($sql) ) 
{
  $num = $db->num_rows();

  print '<table class="noborder" width="100%">';
  print '<tr class="liste_titre"><td colspan="4">Statistiques</td></tr>';
  $var = true;
  $i = 0;

  while ($i < $num ) 
    {
      $row = $db->fetch_row();
 
      $st[$row[1]] = $row[0];

      $i++;
    }
  
  print "<tr $bc[$var]>";
  print '<td>Clients</td><td align="center">'.$st[1]."</td></tr>";
  print '<td>Prospects</td><td align="center">'.$st[2]."</td></tr>";

  print "</table><br>";

  $db->free();
} 
else
{
  dolibarr_print_error($db);
}

/*
 *
 *
 */

/*
 *
 *
 */
print '</td><td valign="top" width="70%">';




/*
 * 
 *
 */

$sql = "SELECT m.rowid, m.titre, m.nbemail";
$sql .= " FROM ".MAIN_DB_PREFIX."mailing as m";
$sql .= " LIMIT 10";
if ( $db->query($sql) ) 
{
  $num = $db->num_rows();
  if ($num > 0)
    { 
      print '<table class="noborder" width="100%">';
      print '<tr class="liste_titre"><td colspan="4">10 derniers mailing</td></tr>';
      $var = true;
      $i = 0;
      
      while ($i < $num ) 
	{
	  $obj = $db->fetch_object();
	  $var=!$var;
	  
	  print "<tr $bc[$var]>";
	  print '<td><a href="fiche.php?id='.$obj->rowid.'">'.$obj->titre.'</a></td>';
	  print '<td>'.$obj->nbemail.'</td>';

	  $i++;
	}

      print "</table><br>";
    }
  $db->free();
} 
else
{
  dolibarr_print_error($db);
}



print '</td></tr>';
print '</table>';

$db->close();


?>
<span class="titrepage">SPAM : L'�tat du droit en France</span> : <a class="extern" href="http://www.cnil.fr/index.php?id=1272">http://www.cnil.fr/index.php?id=1272</a>

<br><br>
Une adresse de messagerie �lectronique est une donn�e nominative, soit directement lorsque le nom de l'internaute figure dans le libell� de l'adresse soit indirectement dans la mesure o� toute adresse �lectronique peut �tre associ�e � une personne physique. D�s lors, toute op�ration de prospection par courrier �lectronique est soumise � la l�gislation de protection des donn�es.

<p>1. La loi&nbsp; pour la confiance dans l'�conomie num�rique</p>

L'utilisation d'adresses de courriers �lectroniques dans les op�rations de prospection commerciale est subordonn�e au recueil du consentement pr�alable des personnes concern�es.
<P class="bodytext">Le dispositif juridique applicable a �t� introduit par l'article 22 de la <A HREF="http://www.legifrance.gouv.fr/WAspad/UnTexteDeJorf?numjo=ECOX0200175L" target="legifrance">loi du 21 juin 2004</a>&nbsp; pour la confiance dans l'�conomie num�rique. 
</P>
<P class="bodytext">Les dispositions applicables sont d�finies par les articles <A HREF="http://www.legifrance.gouv.fr/WAspad/UnArticleDeCode?commun=CPOSTE&art=l34-5" target="legifrance">L. 34-5</a> du code des postes et des t�l�communications et <A HREF="http://www.legifrance.gouv.fr/WAspad/UnArticleDeCode?commun=CCONSO&art=L121-20-5" target="legifrance">L. 121-20-5</a> du code de la consommation. L'application du principe du consentement pr�alable en droit fran�ais r�sulte de la transposition de l'article 13 de la Directive europ�enne du 12 juillet 2002 � Vie priv�e et communications �lectroniques �. 
</P>
Il est interdit d'utiliser l'adresse de courrier �lectronique d'une personne physique � des fins de prospection commerciale sans avoir pr�alablement obtenu son consentement.

<P class="bodytext">L'expression de ce consentement doit �tre libre, sp�cifique et inform�e. En cons�quence, son recueil ne doit pas �tre dilu� dans une acceptation des conditions g�n�rales ou coupl� � une demande de bons de r�duction.&nbsp; La CNIL recommande � cet �gard <B>qu'il soit recueilli par le biais d'une case � cocher </B>et rappelle qu'une case pr�-coch�e est contraire � l'esprit de la loi. 
</P>
La loi a pr�vu une d�rogation au principe du consentement pr�alable en maintenant un r�gime de droit d'opposition : 
<P class="bodytext">il s'agit de l'hypoth�se dans laquelle la prospection concerne des � produits ou services analogues � � ceux d�j� fournis par la m�me personne physique ou morale qui aura recueilli les coordonn�es �lectroniques de l'int�ress�. <BR>Par exemple, une entreprise qui a vendu un livre pourra solliciter cet acheteur pour l'acquisition d'un disque, � la condition toutefois que la personne d�march�e ait �t� express�ment inform�e, lors de la collecte de son adresse de courrier �lectronique, de l'utilisation de celle-ci � des fins commerciales et qu'elle ait �t� mise en mesure de s'y opposer de mani�re simple. 
</P>
<P class="bodytext">Dans tous les cas de figure, <B>chaque message �lectronique envoy� doit pr�voir des modalit�s de d�sinscription</B> et pr�ciser l'identit� de la personne pour le compte de laquelle le message a �t� envoy�. 
</P>
Enfin, la loi pour la confiance dans l'�conomie num�rique a am�nag� une p�riode transitoire d'une dur�e de 6 mois � compter de sa publication, � savoir le 22 juin 2004. 

<P class="bodytext">Ainsi, les entreprises peuvent jusqu'au 22 d�cembre 2004 adresser, � partir de fichiers constitu�s dans le respect des dispositions de la loi Informatique et libert�s du 6 janvier 1978, un courrier �lectronique afin de recueillir le consentement des personnes. L'absence de r�ponse de celles-ci dans la p�riode des 6 mois �quivaudra � un refus d'�tre d�march�.&nbsp; </P>
<P class="bodytext">Ind�pendamment des r�gles sp�cifiques pr�vues dans le code des postes et des t�l�communications et dans celui de la consommation, les op�rations de prospection par courrier �lectronique, quelque soit leur nature (commerciale, caritative, politique, religieuse ou associative par exemple), sont soumises au respect de la l�gislation relative � la protection des donn�es personnelles, � savoir la <a href="http://www.cnil.fr/index.php?id=301" target="cnil">loi Informatique et Libert�s du 6 janvier 1978</a>. 
</P>
<?PHP
 

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
