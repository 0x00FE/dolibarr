<?php
/* Copyright (C) 2005-2007 Regis Houssin        <regis.houssin@cap-networks.com>
 * Copyright (C) 2007      Laurent Destailleur  <eldy@users.sourceforge.net>
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
        \file       htdocs/ajaxbox.php
        \brief      Fichier de reponse sur evenement Ajax deplacement boxes
        \version    $Revision$
*/

require('master.inc.php');
require_once(DOL_DOCUMENT_ROOT."/boxes.php");

// Enregistrement de la position des boxes
if((isset($_GET['boxorder']) && !empty($_GET['boxorder'])) && (isset($_GET['userid']) && !empty($_GET['userid'])))
{
	dolibarr_syslog("AjaxBox boxorder=".$_GET['boxorder']." userid=".$_GET['userid'], LOG_DEBUG);

	$infobox=new InfoBox($db);
	$result=$infobox->saveboxorder("0",$_GET['boxorder'],$_GET['userid']);
}

?>
