<?php
/* Copyright (C) 2003 Rodolphe Quiedeville <rodolphe@quiedeville.org>
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
			\file       htdocs/compta/dons/formulaire/fsfe.fr.php
			\ingroup    don
			\brief      Formulaire de don
			\version    $Revision$
*/

require("../../../main.inc.php");

echo '
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN" "http://www.w3.org/TR/REC-html40/loose.dtd">
<html>
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1">
    <link rel="stylesheet" type="text/css" href="../blue.css">
    <title>FSFE France - Formulaire</title>
  </head>
  <body topmargin="0" bottommargin="0" leftmargin="0" rightmargin="0" marginheight="0" marginwidth="0">
';



require("../../../projetdon.class.php");
require("../../../don.class.php");

setlocale(LC_ALL,$conf->langage);

$don = new Don($db);
$don->id = $_GET["rowid"];
$don->fetch($_GET["rowid"]);

?>


      <blockquote>
	

<p></p>
<p></p>
<p></p>
<p></p>
<table width="100%" border="0" cellspacing="0" cellpadding="1" bgcolor="#ffffff">
  <tr>
    <td width="20%" nowrap>Cerfa No 11580 01</td>
    <td nowrap>
      <center>
	<font size="+2">Re�u dons aux oeuvres</font>
              <br>
	<font size="+1">
                <b>(Article 200-5 du Code G�n�ral des Imp�ts)</b>
              </font>
              <br>
	+ article 238 bis
      </center>
    </td>
      <td width="15%" nowrap>
	<center>
	  Num�ro d'ordre du re�u<p></p>
	  <table border="0" cellspacing="0" cellpadding="1" bgcolor="#000000">
	    <tr>
	      <td valign="bottom" align="center">
		<table border="0" bgcolor="#ffffff" cellspacing="0" cellpadding="3">
		  <tr>
		    <td width="100" height="20">
		      No: 
		    </td>
		  </tr>
		</table>
	      </td>
	    </tr>
	  </table>
	</center>
      </td>
    </tr>
</table>
<p></p>
<table width="100%" border="0" cellspacing="0" cellpadding="1" bgcolor="#000000">
  <tr>
    <td valign="bottom">
      <table width="100%" border="0" bgcolor="#ffffff" cellspacing="0" cellpadding="3">
	<tr bgcolor="#e0e0e0" align="center">
	  <td nowrap>
	    <font size="+2">
                    <b>B�n�ficiaire des versements</b>
                  </font>
	  </td>
	</tr>
      </table>
    </td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="1" bgcolor="#000000">
    <tr>
      <td valign="bottom">
	<table width="100%" border="0" bgcolor="#ffffff" cellspacing="0" cellpadding="3">
	  <tr>
	    <td>
	      <b>Nom ou d�nomination :</b>
                  <br>
	      Free Software Foundation Europe Chapter France (FSF France) <br>
	      <b>Adresse :</b>
                  <br>
	      <b>No</b> 8 <b>Rue</b> de Valois <br>
	      <b>Code postal</b> 75001 <b>Commune</b> Paris <br>
	      <b>Objet</b> : <br>
	      Le soutien � tous les organismes d'Etat et priv�s dans
	      toutes les questions de Logiciels Libres, la
	      collaboration coordonn�e avec les associations
	      nationales poursuivant les m�mes objectifs et avec la
	      FSF Europe, le soutien de projets d�veloppant des
	      Logiciels Libres, la diss�mination des id�aux
	      philosophiques des Logiciels Libres.  La FSF France se
	      d�die aux seules et imm�diates finalit�s scientifiques
	      et d'utilit� publique. 
	      <p></p>
	      <b>Organisme d'int�r�t g�n�ral ayant un caract�re
		scientifique et �ducatif.</b>
	    </td>
	  </tr>
	</table>
      </td>
    </tr>
</table>
<p></p>
<table width="100%" border="0" cellspacing="0" cellpadding="1" bgcolor="#000000">
  <tr>
    <td valign="bottom">
      <table width="100%" border="0" bgcolor="#ffffff" cellspacing="0" cellpadding="3">
	<tr bgcolor="#e0e0e0" align="center">
	  <td nowrap>
	    <font size="+2">
                    <b>Donateur</b>
                  </font>
	  </td>
	</tr>
      </table>
    </td>
  </tr>
</table>
<table width="100%" border="0" cellspacing="0" cellpadding="1" bgcolor="#000000">
    <tr>
      <td valign="bottom">
	<table width="100%" border="0" bgcolor="#ffffff" cellspacing="0" cellpadding="3">
	  <tr>
	    <td>
	      <b>Nom :</b>
<?php print "$don->prenom $don->nom $don->societe" ?><br>
	      <b>Adresse :</b><?php print "$don->adresse" ?><br>
	      <b>No</b> ______ <b>Rue</b> _________________________________<br>
	      <b>Code postal</b> <?php print $don->cp; ?> <b>Commune</b> <?php print $don->ville; ?><br>
	    </td>
	  </tr>
	</table>
      </td>
    </tr>
</table>
<p></p>
<table width="100%" border="0" cellspacing="0" cellpadding="1" bgcolor="#000000">
    <tr>
      <td valign="bottom">
	<table width="100%" border="0" bgcolor="#ffffff" cellspacing="0" cellpadding="3">
	  <tr>
	    <td>
	      Le b�n�ficiaire reconnait avoir re�u au titre des 
	      versements ouvrant droit � r�duction d'imp�t, la
	      somme de : <br>
	      <table width="100%">
		<tr align="center">
		  <td>
		    <?php print price($don->amount); ?> euros
		  </td>
		</tr>
	      </table>
	      Somme en toutes lettres (<b>en euros</b>): <?php print transcoS2L(number_format($don->amount, 2, ',', ' '), "euros"); ?><br>
	      Date du paiement : <?php print strftime("%d %B %Y", $don->date); ?> <br>
	      Mode de versement :
	      <table width="100%">
		<tr align="center">
		  <td valign="top"><?php
if ($don->modepaiementid == 4)
{
print "( Num�raire )";
}
else
{
print "<strike>Num�raire</strike>";
} ?>
		  </td>
		  <td valign="top">
		    <?php 
if ($don->modepaiementid == 7 or $don->modepaiementid == 2)
{
print "( Ch�que ou virement )";
}
else
{
print "<strike>Ch�que ou virement</strike>";
}
                    ?>
		  </td>
		  <td valign="top">
<?php
if ($don->modepaiementid <> 4 && $don->modepaiementid <> 7 && $don->modepaiementid <> 2)
{
print "( Autres )";
}
else
{
print "<strike>Autres</strike>";
} ?>
		  </td>
		  <td align="right">
		    <table border="0" cellspacing="0" cellpadding="1" bgcolor="#000000">
		      <tr>
			<td valign="bottom" align="center">
			  <table border="0" bgcolor="#ffffff" cellspacing="0" cellpadding="3">
			    <tr>
			      <td width="200" height="100" valign="top">
				<center>Date et signature</center>
			      </td>
			    </tr>
			  </table>
			</td>
		      </tr>
		    </table>
		    
		  </td>
		</tr>
	      </table>
	    </td>
	  </tr>
	</table>
      </td>
    </tr>
</table>

      </blockquote>

  </body>
</html>

