<?php
/* Copyright (C) 2002-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2004      Laurent Destailleur  <eldy@users.sourceforge.net>
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
 */

/** 	\file       htdocs/docsoc.php
		\brief      Fichier onglet documents li�s � la soci�t�
		\ingroup    societe
		\version    $Revision$
*/

require("./pre.inc.php");

$langs->load("companies");


llxHeader();

$mesg = "";
$socid=$_GET["socid"];


/*
 * Creation r�pertoire si n'existe pas
 */
if (! is_dir($conf->societe->dir_output)) { mkdir($conf->societe->dir_output); }
$upload_dir = $conf->societe->dir_output . "/" . $socid ;
if (! is_dir($upload_dir))
{
  umask(0);
  if (! mkdir($upload_dir, 0755))
    {
      print "Impossible de cr�er $upload_dir";
    }
}


/*
 * Action envoie fichier
 */
if ( $_POST["sendit"] && defined('MAIN_UPLOAD_DOC') && MAIN_UPLOAD_DOC == 1)
{
  if (is_dir($upload_dir))
    {
      if (doliMoveFileUpload($_FILES['userfile']['tmp_name'], $upload_dir . "/" . $_FILES['userfile']['name']))
	{
	  $mesg = "Le fichier est valide, et a &eacute;t&eacute; t&eacute;l&eacute;charg&eacute; avec succ&egrave;s.\n";
	  //print_r($_FILES);
	}
      else
	{
	  $mesg = "Le fichier n'a pas �t� t�l�charg�";
	  // print_r($_FILES);
	}
      
    }
}

if ( $error_msg )
{ 
  print '<font class="error">'.$error_msg.'</font><br><br>';
}


/*
 * Action suppression fichier
 */

if ($_GET["action"]=='delete')
{
  $file = $upload_dir . "/" . urldecode($_GET["urlfile"]);
  dol_delete_file($file);
  $mesg = "Le fichier a �t� supprim�";
}

/*
 *
 * Mode fiche
 *
 *
 */

if ($socid > 0)
{
  $societe = new Societe($db);
  if ($societe->fetch($socid))
    {
      $h = 0;
      
      $head[$h][0] = DOL_URL_ROOT.'/soc.php?socid='.$societe->id;
      $head[$h][1] = $langs->trans("Company");
      $h++;
      
      if ($societe->client==1)
	{
	  $head[$h][0] = DOL_URL_ROOT.'/comm/fiche.php?socid='.$societe->id;
	  $head[$h][1] = $langs->trans("Customer");
	  $h++;
	}
      
      if ($societe->client==2)
	{
	  $head[$h][0] = DOL_URL_ROOT.'/comm/prospect/fiche.php?id='.$societe->id;
	  $head[$h][1] = $langs->trans("Prospect");
	  $h++;
	}
      if ($societe->fournisseur)
	{
	  $head[$h][0] = DOL_URL_ROOT.'/fourn/fiche.php?socid='.$societe->id;
	  $head[$h][1] = $langs->trans("Supplier");
	  $h++;
	}

      if ($conf->compta->enabled) {
          $langs->load("compta");
          $head[$h][0] = DOL_URL_ROOT.'/compta/fiche.php?socid='.$societe->id;
          $head[$h][1] = $langs->trans("Accountancy");
          $h++;
      }

      $head[$h][0] = DOL_URL_ROOT.'/socnote.php?socid='.$societe->id;
      $head[$h][1] = $langs->trans("Note");
      $h++;

      if ($user->societe_id == 0)
	{
	  $head[$h][0] = DOL_URL_ROOT.'/docsoc.php?socid='.$societe->id;
	  $head[$h][1] = $langs->trans("Documents");
      $hselected = $h;
	  $h++;
	}
      
      $head[$h][0] = DOL_URL_ROOT.'/societe/notify/fiche.php?socid='.$societe->id;
      $head[$h][1] = $langs->trans("Notifications");
      
      dolibarr_fiche_head($head, $hselected, $societe->nom);


      /*
       * 
       */

      print_titre("Documents associ�s");

      if (defined('MAIN_UPLOAD_DOC') && MAIN_UPLOAD_DOC == 1)
	{
	  echo '<form name="userfile" action="docsoc.php?socid='.$socid.'" enctype="multipart/form-data" METHOD="POST">';      

      print '<table class="noborder" width="100%">';
      print '<tr><td width="50%" valign="top">';

	  print '<input type="hidden" name="max_file_size" value="2000000">';
	  print '<input type="file"   name="userfile" size="40" maxlength="80">';
	  print '<br>';
	  print '<input type="submit" value="'.$langs->trans("Upload").'" name="sendit"> &nbsp; ';
	  print '<input type="submit" value="'.$langs->trans("Cancel").'" name="cancelit"><br>';

      print "</td></tr>";
      print "</table>";

	  print '</form>';
	}
      else
	{
	  print "La gestion des fichiers associ�s est d�sactiv�e sur ce serveur";
	}
      print '<br></div>';


      if ($mesg) { print "$mesg<br><br>"; }


      // Affiche liste des documents existant

      clearstatcache();

      $handle=opendir($upload_dir);

      if ($handle)
	{
	  print '<table width="100%" class="noborder">';
      print '<tr class="liste_titre"><td>'.$langs->trans("Document").'</td><td align="right">'.$langs->trans("Size").'</td><td align="center">'.$langs->trans("Date").'</td><td>&nbsp;</td></tr>';

      $var=true;
	  while (($file = readdir($handle))!==false)
	    {
	      if (!is_dir($dir.$file) && substr($file, 0, 1) <> '.' && substr($file, 0, 3) <> 'CVS')
		{
		  $var=!$var;
		  print "<tr $bc[$var]><td>";
		  echo '<a href="'.$conf->societe->dir_output. '/'.$socid.'/'.$file.'">'.$file.'</a>';
		  print "</td>\n";
		  
		  print '<td align="right">'.filesize($upload_dir."/".$file). ' bytes</td>';
		  print '<td align="center">'.dolibarr_print_date(filemtime($upload_dir."/".$file),"%d %b %Y %H:%M:%S").'</td>';
		  
		  print '<td align="center">';
		  echo '<a href="docsoc.php?socid='.$socid.'&action=delete&urlfile='.urlencode($file).'">'.img_delete().'</a>';
		  print "</td></tr>\n";
		}
	    }

	  print "</table>";

	  closedir($handle);
	}
      else
	{
	  print "Impossible d'ouvrir : <b>".$upload_dir."</b>";
	}
    }
  else
    {
      dolibarr_print_error($db);
    }
}
else
{
      dolibarr_print_error();
}

$db->close();

llxFooter("<em>Derni&egrave;re modification $Date$ r&eacute;vision $Revision$</em>");
?>
