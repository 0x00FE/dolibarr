<?PHP
/* Copyright (C) 2002-2003 Rodolphe Quiedeville <rodolphe@quiedeville.org> 
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
require ($GLOBALS["DOCUMENT_ROOT"]."/conf/conf.class.php3");
require ($GLOBALS["DOCUMENT_ROOT"]."/lib/mysql.lib.php3");
require ($GLOBALS["DOCUMENT_ROOT"]."/lib/functions.inc.php3");
require ($GLOBALS["DOCUMENT_ROOT"]."/product.class.php3");
require ($GLOBALS["DOCUMENT_ROOT"]."/user.class.php3");
require ($GLOBALS["DOCUMENT_ROOT"]."/menu.class.php3");
require ($GLOBALS["DOCUMENT_ROOT"]."/societe.class.php3");
require ($GLOBALS["DOCUMENT_ROOT"]."/html.form.class.php");
require ($GLOBALS["DOCUMENT_ROOT"]."/rtplang.class.php");
require ($GLOBALS["DOCUMENT_ROOT"]."/boxes.php");
require ($GLOBALS["DOCUMENT_ROOT"]."/address.class.php");

$conf = new Conf();

$db = new Db();

$user = new User($db);

$user->fetch($GLOBALS["REMOTE_USER"]);

if ($user->limite_liste <> $conf->liste_limit) {
  $conf->liste_limit = $user->limite_liste;
}
/*
 * Definition de toutes les Constantes globales d'envirronement
 *
 */
$sql = "SELECT name, value FROM llx_const";
$result = $db->query($sql);
if ($result) 
{
  $numr = $db->num_rows();
  $i = 0;
  
  while ($i < $numr)
    {
      $objp = $db->fetch_object( $i);
      define ("$objp->name", $objp->value);
      $i++;
    }
}
/*
 *
 *
 */

$db->close();

/*
 * Inclusion de librairies d�pendantes de param�tres de conf
 */

if (defined("FACTURE_ADDON"))
{
  require($GLOBALS["DOCUMENT_ROOT"]."/includes/modules/facture/".FACTURE_ADDON."/".FACTURE_ADDON.".modules.php");
}


// Modification de quelques variable de conf en fonction des Constantes
/*
 * SIZE_LISTE_LIMIT : constante de taille maximale des listes
 */
if (defined("SIZE_LISTE_LIMIT"))
{
  $conf->liste_limit=SIZE_LISTE_LIMIT;
}

if (defined("MAIN_THEME"))
{
  $conf->theme=MAIN_THEME;
  $conf->css  = "theme/".$conf->theme."/".$conf->theme.".css";
}
if (defined("MAIN_MAIL_RESIL"))
{
  $conf->adherent->email_resil=MAIN_MAIL_RESIL;
}
if (defined("MAIN_MAIL_RESIL_SUBJECT"))
{
  $conf->adherent->email_resil_subject=MAIN_MAIL_RESIL_SUBJECT;
}
if (defined("MAIN_MAIL_VALID"))
{
  $conf->adherent->email_valid=MAIN_MAIL_VALID;
}
if (defined("MAIN_MAIL_VALID_SUBJECT"))
{
  $conf->adherent->email_valid_subject=MAIN_MAIL_VALID_SUBJECT;
}
if (defined("MAIN_MAIL_EDIT"))
{
  $conf->adherent->email_edit=MAIN_MAIL_EDIT;
}
if (defined("MAIN_MAIL_EDIT_SUBJECT"))
{
  $conf->adherent->email_edit_subject=MAIN_MAIL_EDIT_SUBJECT;
}
if (defined("MAIN_MAIL_NEW"))
{
  $conf->adherent->email_new=MAIN_MAIL_NEW;
}
if (defined("MAIN_MAIL_NEW_SUBJECT"))
{
  $conf->adherent->email_new_subject=MAIN_MAIL_NEW_SUBJECT;
}

if (defined("MAIN_MODULE_COMMANDE"))
{
  $conf->commande->enabled=MAIN_MODULE_COMMANDE;
}

if (defined("MAIN_MODULE_COMMERCIAL"))
{
  $conf->commercial->enabled=MAIN_MODULE_COMMERCIAL;
}

if (defined("MAIN_MODULE_COMPTABILITE"))
{
  $conf->compta->enabled=MAIN_MODULE_COMPTABILITE;
}

if (defined("MAIN_MODULE_DON"))
{
  $conf->don->enabled=MAIN_MODULE_DON;
}

if (defined("MAIN_MODULE_FOURNISSEUR"))
{
  $conf->fournisseur->enabled=MAIN_MODULE_FOURNISSEUR;
}

if (defined("MAIN_MODULE_FICHEINTER"))
{
  $conf->fichinter->enabled=MAIN_MODULE_FICHEINTER;
}

if (defined("MAIN_MODULE_ADHERENT"))
{
  $conf->adherent->enabled=MAIN_MODULE_ADHERENT;
}

if (defined("MAIN_MODULE_PRODUIT"))
{
  $conf->produit->enabled=MAIN_MODULE_PRODUIT;
}

if (defined("MAIN_MODULE_BOUTIQUE"))
{
  $conf->boutique->enabled=MAIN_MODULE_BOUTIQUE;
}

if (defined("BOUTIQUE_LIVRE"))
{
  $conf->boutique->livre->enabled=BOUTIQUE_LIVRE;
}

if (defined("BOUTIQUE_ALBUM"))
{
  $conf->boutique->album->enabled=BOUTIQUE_ALBUM;
}

/*
 */
if(!isset($application_lang))
  $application_lang = "fr";

$rtplang = new rtplang($GLOBALS["DOCUMENT_ROOT"]."/langs", "en", "en", $application_lang);
$rtplang->debug=1;
/*
 */
$bc[0]="class=\"impair\"";
$bc[1]="class=\"pair\"";

setlocale(LC_TIME, "fr_FR");

/*
 * Barre de menu sup�rieure
 *
 *
 */

function top_menu($head) 
{
  global $user, $conf, $rtplang;

  print $rtplang->lang_header();

  //  print "<HTML><HEAD>";
  print $head;
  //  print '<META HTTP-EQUIV="Content-Type" CONTENT="text/html; charset=iso-8859-1">';
  print '<LINK REL="stylesheet" TYPE="text/css" HREF="/'.$conf->css.'">';
  print "\n";
  if (defined("MAIN_TITLE")){
    print "<title>".MAIN_TITLE."</title>";
  }else{
    print '<title>Dolibarr</title>';
  }
  print "\n";


  print "</HEAD>\n";
  print '<BODY TOPMARGIN="0" BOTTOMMARGIN="0" LEFTMARGIN="0" RIGHTMARGIN="0" MARGINHEIGHT="0" MARGINWIDTH="0">';
  
  /*
   * Barre superieure
   *
   */

  print '<TABLE class="topbarre" width="100%">';

  print "<tr>";
  print '<td width="15%" class="menu" align="center"><A class="menu" href="/">Accueil</A></TD>';

  if (!defined(MAIN_MENU_BARRETOP))
  {
    define("MAIN_MENU_BARRETOP","default.php");
  }

  require($GLOBALS["DOCUMENT_ROOT"]."/includes/menus/barre_top/".MAIN_MENU_BARRETOP);

  print '<TD width="15%" class="menu" align="center">'.strftime(" %d %B - %H:%M",time()).'</TD>';

  print '<td width="10%" class="menu" align="center">'.$user->login.'</td>';
  print '</tr>';

  //    print '</table>';
  /*
   * Table principale
   *
   */
  //  print '<TABLE border="0" width="100%" cellspacing="0" cellpadding="3">';

  print '<tr><td valign="top" align="right">';

}

/*
 * Barre de menu gauche
 *
 *
 *
 *
 */
Function left_menu($menu) 
{
  global $user, $conf, $rtplang;

  /*
   * Colonne de gauche
   *
   */
  print '<table class="leftmenu" border="0" width="100%" cellspacing="1" cellpadding="4">';


  for ($i = 0 ; $i < sizeof($menu) ; $i++) 
    {

      print '<tr><td class="barre" valign="top">';
      print '<A class="leftmenu" href="'.$menu[$i][0].'">'.$menu[$i][1].'</a>';

      for ($j = 2 ; $j < sizeof($menu[$i]) - 1 ; $j = $j +2) 
	{
	  print '<br>&nbsp;-&nbsp;<a class="submenu" href="'.$menu[$i][$j].'">'.$menu[$i][$j+1].'</A>';
	}
      print '</td></tr>';
      
    }

  if ((defined("MAIN_SEARCHFORM_SOCIETE") && MAIN_SEARCHFORM_SOCIETE > 0)|| (defined("MAIN_SEARCHFORM_CONTACT") && MAIN_SEARCHFORM_CONTACT > 0))
    {
      print '<tr><td class="barre" valign="top" align="right">';
      
      if (defined("MAIN_SEARCHFORM_SOCIETE") && MAIN_SEARCHFORM_SOCIETE > 0)
	{
	  //	  print constant("MAIN_SEARCHFORM_SOCIETE");
	  //	  echo MAIN_SEARCHFORM_SOCIETE."==MAIN_SEARCHFORM_SOCIETE; le type est " . gettype( MAIN_SEARCHFORM_SOCIETE ) . "<br>\n";
	  print '<A class="menu" href="/comm/clients.php3">Societes</A>';
	  print '<form action="/comm/clients.php3">';
	  print '<input type="hidden" name="mode" value="search">';
	  print '<input type="hidden" name="mode-search" value="soc">';
	  print '<input type="text" name="socname" class="flat" size="10">&nbsp;';
	  print '<input type="submit" class="flat" value="go">';
	  print '</form>';
	}
      
      if (defined("MAIN_SEARCHFORM_CONTACT") && MAIN_SEARCHFORM_CONTACT > 0)
	{
	  print '<A class="menu" href="/comm/contact.php3">Contacts</A>';
	  print '<form action="/comm/contact.php3">';
	  print '<input type="hidden" name="mode" value="search">';
	  print '<input type="hidden" name="mode-search" value="contact">';
	  print '<input type="text" class="flat" name="contactname" size="10">&nbsp;';
	  print '<input type="submit" class="flat" value="go">';
	  print '</form>';
	}
      print '</td></tr>';
    }
  print '</table>';

  /*
   *
   *
   */
  print '</td><td valign="top" width="85%" colspan="6">';


}
/*
 * Impression du pied de page
 *
 *
 *
 */
function llxFooter($foot='') 
{
  print "</TD></TR>";
  /*
   *
   */
  print "</TABLE>\n";
  print "<div>";
  print '[<a href="http://savannah.gnu.org/bugs/?group_id=1915">Bug report</a>]&nbsp;';
  print '[<a href="http://savannah.gnu.org/projects/dolibarr/">Source Code</a>]&nbsp;'.$foot.'</div>';
  print "</BODY></HTML>";
}


?>
