<?php
/* Copyright (C) 2005 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2005 Laurent Destailleur  <eldy@users.sourceforge.net>
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

/**
        \file       htdocs/comm/mailing/index.php
        \ingroup    mailing
        \brief      Page accueil de la zone mailing
        \version    $Revision$
*/
 
require("./pre.inc.php");

$langs->load("commercial");
$langs->load("orders");

$user->getrights("mailing");

if (! $user->rights->mailing->lire || $user->societe_id > 0)
  accessforbidden();
	  


llxHeader('','Mailing');

/*
 *
 */

print_fiche_titre($langs->trans("MailingArea"));

print '<table class="notopnoleftnoright" width="100%">';

print '<tr><td valign="top" width="30%" class="notopnoleft">';


// Recherche emails
$var=false;
print '<form method="post" action="'.DOL_URL_ROOT.'/comm/mailing/liste.php">';
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td colspan="3">'.$langs->trans("SearchAMailing").'</td></tr>';
print '<tr '.$bc[$var].'><td nowrap>';
print $langs->trans("Ref").':</td><td><input type="text" class="flat" name="sref" size="18"></td>';
print '<td rowspan="2"><input type="submit" value="'.$langs->trans("Search").'" class="button"></td></tr>';
print '<tr '.$bc[$var].'><td nowrap>';
print $langs->trans("Other").':</td><td><input type="text" class="flat" name="sall" size="18"></td>';

print "</table></form><br>\n";


// Affiche stats de tous les modules de destinataires mailings
print '<table class="noborder" width="100%">';
print '<tr class="liste_titre"><td colspan="3">'.$langs->trans("TargetsStatistics").'</td></tr>';

$dir=DOL_DOCUMENT_ROOT."/includes/modules/mailings";
$handle=opendir($dir);

$var=True;
while (($file = readdir($handle))!==false)
{
    if (substr($file, 0, 1) <> '.' && substr($file, 0, 3) <> 'CVS')
    {
        if (eregi("(.*)\.(.*)\.(.*)",$file,$reg))
        {
            $modulename=$reg[1];

            // Chargement de la classe
            $file = $dir."/".$modulename.".modules.php";
            $classname = "mailing_".$modulename;
            require_once($file);
            $mailmodule = new $classname($db);
            
            $qualified=1;
            foreach ($mailmodule->require_module as $key)
            {
                if (! $conf->$key->enabled || (! $user->admin && $mailmodule->require_admin))
                {
                    $qualified=0;
                    //print "Les pr�requis d'activation du module mailing ne sont pas respect�s. Il ne sera pas actif";
                    break;
                }
            }

            // Si le module mailing est qualifi�
            if ($qualified)
            {
                $var = !$var;

                foreach ($mailmodule->statssql as $sql) 
                {
                    print '<tr '.$bc[$var].'>';
        
                    $result=$db->query($sql);
                    if ($result) 
                    {
                      $num = $db->num_rows($result);
                    
                      $i = 0;
                    
                      while ($i < $num ) 
                        {
                          $obj = $db->fetch_object($result);
                          print '<td>'.img_object('',$mailmodule->picto).' '.$obj->label.'</td><td>'.$obj->nb.'<td>';
                          $i++;
                        }
                    
                      $db->free($result);
                    } 
                    else
                    {
                      dolibarr_print_error($db);
                    }
                    print '</tr>';
                }
            }            
        }
    }
}
closedir($handle);
 


print "</table><br>";

print '</td><td valign="top" width="70%" class="notopnoleftnoright">';


/*
 * Liste des derniers mailings
 */

$sql  = "SELECT m.rowid, m.titre, m.nbemail, m.statut";
$sql .= " FROM ".MAIN_DB_PREFIX."mailing as m";
$sql .= " LIMIT 10";
$result=$db->query($sql);
if ($result) 
{
  print '<table class="noborder" width="100%">';
  print '<tr class="liste_titre">';
  print '<td colspan="2">'.$langs->trans("LastMailings",10).'</td>';
  print '<td align="right">'.$langs->trans("NbOfEMails").'</td>';
  print '<td align="center">'.$langs->trans("Status").'</td></tr>';

  $num = $db->num_rows($result);
  if ($num > 0)
    { 
      $var = true;
      $i = 0;
      
      while ($i < $num ) 
	{
	  $obj = $db->fetch_object($result);
	  $var=!$var;
	  
	  print "<tr $bc[$var]>";
	  print '<td><a href="fiche.php?id='.$obj->rowid.'">'.img_object($langs->trans("ShowEMail"),"email").' '.$obj->rowid.'</a></td>';
	  print '<td>'.$obj->titre.'</td>';
	  print '<td align="right">'.($obj->nbemail?$obj->nbemail:"0").'</td>';
	  $mail=new Mailing($db);
	  print '<td align="center">'.$mail->statuts[$obj->statut].'</td>';
      print '</tr>';
	  $i++;
	}

    }
  else 
    {
     print '<tr><td>'.$langs->trans("None").'</td></tr>';   
    }
  print "</table><br>";
  $db->free($result);
} 
else
{
  dolibarr_print_error($db);
}



print '</td></tr>';
print '</table>';

$db->close();


if ($langs->file_exists("html/spam.html",0)) {
    print "<br><br><br><br>".img_warning().' '.$langs->trans("Note")."<br>";
    print '<div style="padding: 4px; background: #FAFAFA; border: 1px solid #BBBBBB;" >';
    $langs->print_file("html/spam.html",0);
    print '</div>';
    
    print '<br>';
 }


llxFooter('$Date$ - $Revision$');

?>
