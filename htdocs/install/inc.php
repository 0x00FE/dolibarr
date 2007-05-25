<?php
/* Copyright (C) 2004 Rodolphe Quiedeville <rodolphe@quiedeville.org> 
 * Copyright (C) 2004 Benoit Mortier       <benoit.mortier@opensides.be>
 * Copyright (C) 2004 Sebastien DiCintio   <sdicintio@ressource-toi.org>
 * Copyright (C) 2007 Laurent Destailleur  <eldy@users.sourceforge.net>
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

/**	    \file       htdocs/install/inc.php
		\brief      Fichier include du programme d'installation
		\version    $Revision$
*/

require_once('../translate.class.php');
require_once('../lib/functions.inc.php');

// Correction PHP_SELF (ex pour apache via caudium) car PHP_SELF doit valoir URL relative
// et non path absolu.
if (isset($_SERVER["DOCUMENT_URI"]) && $_SERVER["DOCUMENT_URI"])
{
	$_SERVER["PHP_SELF"]=$_SERVER["DOCUMENT_URI"];
}


$conffile = "../conf/conf.php";
$charset="ISO-8859-1";
if (file_exists($conffile))
{
	include_once($conffile);	// Fichier conf charg�
	$charset=$character_set_client;
	if ($dolibarr_main_document_root)
	{
		require_once($dolibarr_main_document_root . "/conf/conf.class.php");
		$conf=new Conf();
		if (! isset($character_set_client) || ! $character_set_client) $character_set_client='ISO-8859-1';
		$conf->character_set_client=$character_set_client;
	}
	if ($dolibarr_main_document_root && $dolibarr_main_db_type && ! defined('DONOTLOADCONF'))
	{
		require_once($dolibarr_main_document_root . "/lib/databases/".$dolibarr_main_db_type.".lib.php");
	}
}
if (! isset($dolibarr_main_db_prefix) || ! $dolibarr_main_db_prefix) $dolibarr_main_db_prefix='llx_'; 
define('MAIN_DB_PREFIX',$dolibarr_main_db_prefix);


define('DOL_DOCUMENT_ROOT','../');


// Forcage du log pour les install et mises a jour
$conf->syslog->enabled=1;
$conf->global->SYSLOG_LEVEL=constant('LOG_DEBUG');
if (file_exists('/tmp')) define('SYSLOG_FILE','/tmp/dolibarr_install.log');
else define('SYSLOG_FILE','/dolibarr_install.log');
define('SYSLOG_FILE_NO_ERROR',1);


// Forcage du parametrage PHP magic_quots_gpc (Sinon il faudrait a chaque POST, conditionner
// la lecture de variable par stripslashes selon etat de get_magic_quotes).
// En mode off (recommande il faut juste faire addslashes au moment d'un insert/update.
function stripslashes_deep($value)
{
   return (is_array($value) ? array_map('stripslashes_deep', $value) : stripslashes($value));
}
if (get_magic_quotes_gpc())
{
   $_GET    = array_map('stripslashes_deep', $_GET);
   $_POST  = array_map('stripslashes_deep', $_POST);
   $_COOKIE = array_map('stripslashes_deep', $_COOKIE);
   $_REQUEST = array_map('stripslashes_deep', $_REQUEST);
}
@set_magic_quotes_runtime(0);


// Defini objet langs
$langs = new Translate('../langs',$conf);
$langs->setDefaultLang('auto');
$langs->setPhpLang();

$bc[false]=' class="bg1"';
$bc[true]=' class="bg2"';


function pHeader($soutitre,$next,$action='set')
{
	
	global $charset;
    global $langs;
    $langs->load("main");
    $langs->load("admin");

	// On force contenu en ISO-8859-1
	header("Content-type: text/html; charset=".$charset);
    //header("Content-type: text/html; charset=UTF-8");

    print '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">'."\n";
    print '<html>';
    print '<head>';
    print '<meta http-equiv="content-type" content="text/html; charset='.$charset.'">';
    print '<link rel="stylesheet" type="text/css" href="./default.css">';
    print '<title>'.$langs->trans("DolibarrSetup").'</title>';
    print '</head>';
    print '<body>';
    print '<span class="titre">'.$langs->trans("DolibarrSetup").'</span>';

    print '<form action="'.$next.'.php" method="POST">';
    print '<input type="hidden" name="testpost" value="ok">';
    print '<input type="hidden" name="action" value="'.$action.'">';

	print '<table class="main" width="100%"><tr><td>';
    if ($soutitre) {
        print '<div class="soustitre">'.$soutitre.'</div>';
    }

	print '<table class="main-inside" width="100%"><tr><td>';

}

function pFooter($nonext=0,$setuplang='')
{
    global $langs;
    $langs->load("main");
    $langs->load("admin");
    
    print '</td></tr></table>';
    print '</td></tr></table>';
    
    if (! $nonext)
    {
        print '<div class="barrebottom"><input type="submit" value="'.$langs->trans("NextStep").' ->"></div>';
    }
    if ($setuplang)
    {
        print '<input type="hidden" name="selectlang" value="'.$setuplang.'">';
    }

    print '</form>';
    print '</body>';
    print '</html>';
}


function dolibarr_install_syslog($message)
{
	if (! defined('LOG_DEBUG')) define('LOG_DEBUG',6);
	dolibarr_syslog($message,constant('LOG_DEBUG'));
}

?>