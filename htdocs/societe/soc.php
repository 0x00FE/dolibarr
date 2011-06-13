<?php
/* Copyright (C) 2001-2007 Rodolphe Quiedeville <rodolphe@quiedeville.org>
 * Copyright (C) 2003      Brian Fraval         <brian@fraval.org>
 * Copyright (C) 2004-2011 Laurent Destailleur  <eldy@users.sourceforge.net>
 * Copyright (C) 2005      Eric Seigne          <eric.seigne@ryxeo.com>
 * Copyright (C) 2005-2011 Regis Houssin        <regis@dolibarr.fr>
 * Copyright (C) 2008	   Patrick Raguin       <patrick.raguin@auguria.net>
 * Copyright (C) 2010      Juanjo Menent        <jmenent@2byte.es>
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
 */

/**
 *  \file       htdocs/societe/soc.php
 *  \ingroup    societe
 *  \brief      Third party card page
 *  \version    $Id$
 */

require("../main.inc.php");
require_once(DOL_DOCUMENT_ROOT."/lib/company.lib.php");
require_once(DOL_DOCUMENT_ROOT."/lib/images.lib.php");
require_once(DOL_DOCUMENT_ROOT."/lib/files.lib.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/html.formadmin.class.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/html.formcompany.class.php");
require_once(DOL_DOCUMENT_ROOT."/core/class/html.formfile.class.php");
require_once(DOL_DOCUMENT_ROOT."/contact/class/contact.class.php");
if ($conf->adherent->enabled) require_once(DOL_DOCUMENT_ROOT."/adherents/class/adherent.class.php");

$langs->load("companies");
$langs->load("commercial");
$langs->load("bills");
$langs->load("banks");
$langs->load("users");
if ($conf->notification->enabled) $langs->load("mails");

$action = GETPOST('action');
$confirm = GETPOST('confirm');

// Security check
$socid = GETPOST("socid");
if ($user->societe_id) $socid=$user->societe_id;

$soc = new Societe($db);
// Get object canvas (By default, this is not defined, so standard usage of dolibarr)
if (!empty($socid)) $soc->getCanvas($socid);
$canvas = (!empty($soc->canvas)?$soc->canvas:GETPOST("canvas"));

if (! empty($canvas))
{
    require_once(DOL_DOCUMENT_ROOT."/core/class/canvas.class.php");
    $objcanvas = new Canvas($db,$action);
    $objcanvas->getCanvas('thirdparty','card',$canvas);
    // Security check
    $result = $objcanvas->restrictedArea($user, 'societe', $socid);
}
else
{
    // Security check
    $result = restrictedArea($user, 'societe', $socid);
}

$error=0; $errors=array();


/*
 * Actions
 */

// If canvas actions are defined, because on url, or because contact was created with canvas feature on, we use the canvas feature.
// If canvas actions are not defined, we use standard feature.
if (method_exists($objcanvas->control,'doActions'))
{
    // -----------------------------------------
    // When used with CANVAS
    // -----------------------------------------
    $objcanvas->doActions($socid);

    if (empty($objcanvas->error) && (empty($objcanvas->errors) || sizeof($objcanvas->errors) == 0))
    {
        if ($action=='add')    { $objcanvas->action='create'; $action='create'; }
        if ($action=='update') { $objcanvas->action='view';   $action='view'; }
    }
    else
    {
        $error=$objcanvas->error; $errors=$objcanvas->errors;
        if ($action=='add')    { $objcanvas->action='create'; $action='create'; }
        if ($action=='update') { $objcanvas->action='edit';   $action='edit'; }
    }
}
else
{
    // -----------------------------------------
    // When used in standard mode
    // -----------------------------------------

    if ($_POST["getcustomercode"])
    {
        // We defined value code_client
        $_POST["code_client"]="Acompleter";
    }

    if ($_POST["getsuppliercode"])
    {
        // We defined value code_fournisseur
        $_POST["code_fournisseur"]="Acompleter";
    }

    // Add new third party
    if ((! $_POST["getcustomercode"] && ! $_POST["getsuppliercode"])
    && ($action == 'add' || $action == 'update') && $user->rights->societe->creer)
    {
        require_once(DOL_DOCUMENT_ROOT."/lib/functions2.lib.php");

        if ($action == 'update')
        {
            $soc->fetch($socid);
        }
        else if ($canvas) $soc->canvas=$canvas;

        if ($_REQUEST["private"] == 1)
        {
            $soc->particulier           = $_REQUEST["private"];

            $soc->name                  = empty($conf->global->MAIN_FIRSTNAME_NAME_POSITION)?trim($_POST["prenom"].' '.$_POST["nom"]):trim($_POST["nom"].' '.$_POST["prenom"]);
            $soc->nom                   = $soc->name;     // TODO obsolete
            $soc->nom_particulier       = $_POST["nom"];
            $soc->prenom                = $_POST["prenom"];
            $soc->civilite_id           = $_POST["civilite_id"];
        }
        else
        {
            $soc->name                  = $_POST["nom"];
            $soc->nom                   = $soc->name;     // TODO obsolete
        }
        $soc->address               = $_POST["adresse"];
        $soc->adresse               = $_POST["adresse"]; // TODO obsolete
        $soc->zip                   = $_POST["zipcode"];
        $soc->cp                    = $_POST["zipcode"]; // TODO obsolete
        $soc->town                  = $_POST["town"];
        $soc->ville                 = $_POST["town"];    // TODO obsolete
        $soc->pays_id               = $_POST["pays_id"];
        $soc->departement_id        = $_POST["departement_id"];
        $soc->tel                   = $_POST["tel"];
        $soc->fax                   = $_POST["fax"];
        $soc->email                 = trim($_POST["email"]);
        $soc->url                   = trim($_POST["url"]);
        $soc->siren                 = $_POST["idprof1"];
        $soc->siret                 = $_POST["idprof2"];
        $soc->ape                   = $_POST["idprof3"];
        $soc->idprof4               = $_POST["idprof4"];
        $soc->prefix_comm           = $_POST["prefix_comm"];
        $soc->code_client           = $_POST["code_client"];
        $soc->code_fournisseur      = $_POST["code_fournisseur"];
        $soc->capital               = $_POST["capital"];
        $soc->gencod                = $_POST["gencod"];

        $soc->tva_intra             = $_POST["tva_intra"];
        $soc->tva_assuj             = $_POST["assujtva_value"];
        $soc->status                = $_POST["status"];

        // Local Taxes
        $soc->localtax1_assuj       = $_POST["localtax1assuj_value"];
        $soc->localtax2_assuj       = $_POST["localtax2assuj_value"];

        $soc->forme_juridique_code  = $_POST["forme_juridique_code"];
        $soc->effectif_id           = $_POST["effectif_id"];
        if ($_REQUEST["private"] == 1)
        {
            $soc->typent_id             = 8; // TODO predict another method if the field "special" change of rowid
        }
        else
        {
            $soc->typent_id             = $_POST["typent_id"];
        }

        $soc->client                = $_POST["client"];
        $soc->fournisseur           = $_POST["fournisseur"];
        $soc->fournisseur_categorie = $_POST["fournisseur_categorie"];

        $soc->commercial_id         = $_POST["commercial_id"];
        $soc->default_lang          = $_POST["default_lang"];

        if (GETPOST('deletephoto')) $soc->logo = '';
        $soc->logo = dol_sanitizeFileName($_FILES['photo']['name']);

        // Check parameters
        if (empty($_POST["cancel"]))
        {
            if (! empty($soc->email) && ! isValidEMail($soc->email))
            {
                $langs->load("errors");
                $error++; $errors[] = $langs->trans("ErrorBadEMail",$soc->email);
                $action = ($action=='add'?'create':'edit');
            }
            if (! empty($soc->url) && ! isValidUrl($soc->url))
            {
                $langs->load("errors");
                $error++; $errors[] = $langs->trans("ErrorBadUrl",$soc->url);
                $action = ($action=='add'?'create':'edit');
            }
            if ($soc->fournisseur && ! $conf->fournisseur->enabled)
            {
                $langs->load("errors");
                $error++; $errors[] = $langs->trans("ErrorSupplierModuleNotEnabled");
                $action = ($action=='add'?'create':'edit');
            }
        }

        if (! $error)
        {
            if ($action == 'add')
            {
                $db->begin();

                if (empty($soc->client))      $soc->code_client='';
                if (empty($soc->fournisseur)) $soc->code_fournisseur='';

                $result = $soc->create($user);
                if ($result >= 0)
                {
                    if ($soc->particulier)
                    {
                        dol_syslog("This thirdparty is a personal people",LOG_DEBUG);
                        $contact=new Contact($db);

                        $contact->civilite_id = $soc->civilite_id;
                        $contact->name=$soc->nom_particulier;
                        $contact->firstname=$soc->prenom;
                        $contact->address=$soc->address;
                        $contact->zip=$soc->zip;
                        $contact->cp=$soc->cp;
                        $contact->town=$soc->town;
                        $contact->ville=$soc->ville;
                        $contact->fk_pays=$soc->fk_pays;
                        $contact->socid=$soc->id;                   // fk_soc
                        $contact->status=1;
                        $contact->email=$soc->email;
                        $contact->priv=0;

                        $result=$contact->create($user);
                        if (! $result >= 0)
                        {
                            $error=$contact->error; $errors=$contact->errors;
                        }
                    }

                    ### Gestion du logo de la société
                    $dir     = $conf->societe->dir_output."/".$soc->id."/logos/";
                    $file_OK = is_uploaded_file($_FILES['photo']['tmp_name']);
                    if ($file_OK)
                    {
                        if (image_format_supported($_FILES['photo']['name']))
                        {
                            create_exdir($dir);

                            if (@is_dir($dir))
                            {
                                $newfile=$dir.'/'.dol_sanitizeFileName($_FILES['photo']['name']);
                                $result = dol_move_uploaded_file($_FILES['photo']['tmp_name'], $newfile, 1);

                                if (! $result > 0)
                                {
                                    $errors[] = "ErrorFailedToSaveFile";
                                }
                                else
                                {
                                    // Create small thumbs for company (Ratio is near 16/9)
                                    // Used on logon for example
                                    $imgThumbSmall = vignette($newfile, $maxwidthsmall, $maxheightsmall, '_small', $quality);

                                    // Create mini thumbs for company (Ratio is near 16/9)
                                    // Used on menu or for setup page for example
                                    $imgThumbMini = vignette($newfile, $maxwidthmini, $maxheightmini, '_mini', $quality);
                                }
                            }
                        }
                    }
                    ### Gestion du logo de la société
                }
                else
                {
                    $error=$soc->error; $errors=$soc->errors;
                }

                if ($result >= 0)
                {
                    $db->commit();

                    $url=$_SERVER["PHP_SELF"]."?socid=".$soc->id;
                    if (($soc->client == 1 || $soc->client == 3) && empty($conf->global->SOCIETE_DISABLE_CUSTOMERS)) $url=DOL_URL_ROOT."/comm/fiche.php?socid=".$soc->id;
                    else if ($soc->fournisseur == 1) $url=DOL_URL_ROOT."/fourn/fiche.php?socid=".$soc->id;
                    Header("Location: ".$url);
                    exit;
                }
                else
                {
                    $db->rollback();
                    $action='create';
                }
            }

            if ($action == 'update')
            {
                if ($_POST["cancel"])
                {
                    Header("Location: ".$_SERVER["PHP_SELF"]."?socid=".$socid);
                    exit;
                }

                $oldsoc=new Societe($db);
                $result=$oldsoc->fetch($socid);

                // To not set code if third party is not concerned. But if it had values, we keep them.
                if (empty($soc->client) && empty($oldsoc->code_client))          $soc->code_client='';
                if (empty($soc->fournisseur)&& empty($oldsoc->code_fournisseur)) $soc->code_fournisseur='';
                //var_dump($soc);exit;

                $result = $soc->update($socid,$user,1,$oldsoc->codeclient_modifiable(),$oldsoc->codefournisseur_modifiable());
                if ($result <=  0)
                {
                    $error = $soc->error; $errors = $soc->errors;
                }

                ### Gestion du logo de la société
                $dir     = $conf->societe->dir_output."/".$soc->id."/logos/";
                $file_OK = is_uploaded_file($_FILES['photo']['tmp_name']);
                if ($file_OK)
                {
                    if (GETPOST('deletephoto') && $soc->logo)
                    {
                        $fileimg=$conf->societe->dir_output.'/'.$soc->id.'/logos/'.$soc->logo;
                        $dirthumbs=$conf->societe->dir_output.'/'.$soc->id.'/logos/thumbs';
                        dol_delete_file($fileimg);
                        dol_delete_dir_recursive($dirthumbs);
                    }

                    if (image_format_supported($_FILES['photo']['name']))
                    {
                        create_exdir($dir);

                        if (@is_dir($dir))
                        {
                            $newfile=$dir.'/'.dol_sanitizeFileName($_FILES['photo']['name']);
                            $result = dol_move_uploaded_file($_FILES['photo']['tmp_name'], $newfile, 1);

                            if (! $result > 0)
                            {
                                $errors[] = "ErrorFailedToSaveFile";
                            }
                            else
                            {
                                // Create small thumbs for company (Ratio is near 16/9)
                                // Used on logon for example
                                $imgThumbSmall = vignette($newfile, $maxwidthsmall, $maxheightsmall, '_small', $quality);

                                // Create mini thumbs for company (Ratio is near 16/9)
                                // Used on menu or for setup page for example
                                $imgThumbMini = vignette($newfile, $maxwidthmini, $maxheightmini, '_mini', $quality);
                            }
                        }
                    }
                }
                ### Gestion du logo de la société

                if (! $error && ! sizeof($errors))
                {

                    Header("Location: ".$_SERVER["PHP_SELF"]."?socid=".$socid);
                    exit;
                }
                else
                {
                    $soc->id = $socid;
                    $action= "edit";
                }
            }
        }
    }

    // Delete third party
    if ($action == 'confirm_delete' && $confirm == 'yes' && $user->rights->societe->supprimer)
    {
        $soc->fetch($socid);
        $result = $soc->delete($socid);

        if ($result >= 0)
        {
            Header("Location: ".DOL_URL_ROOT."/societe/societe.php?delsoc=".$soc->nom."");
            exit;
        }
        else
        {
            $langs->load("errors");
            $error=$langs->trans($soc->error); $errors = $soc->errors;
            $action='';
        }
    }


    /*
     * Generate document
     */
    if ($action == 'builddoc')  // En get ou en post
    {
        if (is_numeric(GETPOST('model')))
        {
            $error=$langs->trans("ErrorFieldRequired",$langs->transnoentities("Model"));
        }
        else
        {
            require_once(DOL_DOCUMENT_ROOT.'/includes/modules/societe/modules_societe.class.php');

            $soc = new Societe($db);
            $soc->fetch($socid);
            $soc->fetch_thirdparty();

            // Define output language
            $outputlangs = $langs;
            $newlang='';
            if ($conf->global->MAIN_MULTILANGS && empty($newlang) && ! empty($_REQUEST['lang_id'])) $newlang=$_REQUEST['lang_id'];
            if ($conf->global->MAIN_MULTILANGS && empty($newlang)) $newlang=$fac->client->default_lang;
            if (! empty($newlang))
            {
                $outputlangs = new Translate("",$conf);
                $outputlangs->setDefaultLang($newlang);
            }
            $result=thirdparty_doc_create($db, $soc->id, '', $_REQUEST['model'], $outputlangs);
            if ($result <= 0)
            {
                dol_print_error($db,$result);
                exit;
            }
            else
            {
                Header ('Location: '.$_SERVER["PHP_SELF"].'?socid='.$soc->id.(empty($conf->global->MAIN_JUMP_TAG)?'':'#builddoc'));
                exit;
            }
        }
    }
}



/*
 *  View
 */

$help_url='EN:Module_Third_Parties|FR:Module_Tiers|ES:Empresas';
llxHeader('',$langs->trans("ThirdParty"),$help_url);

$form = new Form($db);
$formfile = new FormFile($db);
$formadmin = new FormAdmin($db);
$formcompany = new FormCompany($db);

$countrynotdefined=$langs->trans("ErrorSetACountryFirst").' ('.$langs->trans("SeeAbove").')';

if (is_object($objcanvas) && $objcanvas->displayCanvasExists($action))
{
    // -----------------------------------------
    // When used with CANVAS
    // -----------------------------------------
    if ($action == 'create')
    {
        $objcanvas->assign_post();            // Assign POST data
        $objcanvas->assign_values($action);   // Set value for templates
        $objcanvas->display_canvas($action);  // Show template
    }
    elseif ($action == 'edit')
    {
        $objcanvas->control->object=$objcanvas->getObject($socid);  // Load object
        if (empty($objcanvas->control->object))
        {
            $object = new Societe($db);
            $object->fetch($socid);
            $objcanvas->control->object=$object;
        }
        $objcanvas->assign_post();            // Assign POST data
        $objcanvas->assign_values($action);   // Set value for templates
        $objcanvas->display_canvas($action);  // Show template
    }
    else
    {
        $objcanvas->control->object=$objcanvas->getObject($socid);  // Load object
        if (empty($objcanvas->control->object))
        {
            $object = new Societe($db);
            $object->fetch($socid);
            $objcanvas->control->object=$object;
        }
        $objcanvas->assign_values('view');   			// Assign values
        $objcanvas->display_canvas('view');  			// Show template
    }
}
else
{
    // -----------------------------------------
    // When used in standard mode
    // -----------------------------------------
    if ($action == 'create')
    {
        /*
         *  Creation
         */

        // Load object modCodeTiers
        $module=$conf->global->SOCIETE_CODECLIENT_ADDON;
        if (! $module) dolibarr_error('',$langs->trans("ErrorModuleThirdPartyCodeInCompanyModuleNotDefined"));
        if (substr($module, 0, 15) == 'mod_codeclient_' && substr($module, -3) == 'php')
        {
            $module = substr($module, 0, dol_strlen($module)-4);
        }
        require_once(DOL_DOCUMENT_ROOT ."/includes/modules/societe/".$module.".php");
        $modCodeClient = new $module;
        $module=$conf->global->SOCIETE_CODEFOURNISSEUR_ADDON;
        if (! $module) $module=$conf->global->SOCIETE_CODECLIENT_ADDON;
        if (substr($module, 0, 15) == 'mod_codeclient_' && substr($module, -3) == 'php')
        {
            $module = substr($module, 0, dol_strlen($module)-4);
        }
        require_once(DOL_DOCUMENT_ROOT ."/includes/modules/societe/".$module.".php");
        $modCodeFournisseur = new $module;

        //if ($_GET["type"]=='cp') { $soc->client=3; }
        if (GETPOST("type")!='f') $soc->client=3;
        if (GETPOST("type")=='c')  { $soc->client=1; }
        if (GETPOST("type")=='p')  { $soc->client=2; }
        if ($conf->fournisseur->enabled && (GETPOST("type")=='f' || GETPOST("type")==''))  { $soc->fournisseur=1; }
        if (GETPOST("private")==1) { $soc->particulier=1; }

        $soc->nom=$_POST["nom"];
        $soc->prenom=$_POST["prenom"];
        $soc->particulier=$_REQUEST["private"];
        $soc->prefix_comm=$_POST["prefix_comm"];
        $soc->client=$_POST["client"]?$_POST["client"]:$soc->client;
        $soc->code_client=$_POST["code_client"];
        $soc->fournisseur=$_POST["fournisseur"]?$_POST["fournisseur"]:$soc->fournisseur;
        $soc->code_fournisseur=$_POST["code_fournisseur"];
        $soc->adresse=$_POST["adresse"]; // TODO obsolete
        $soc->address=$_POST["adresse"];
        $soc->cp=$_POST["zipcode"];
        $soc->ville=$_POST["town"];
        $soc->departement_id=$_POST["departement_id"];
        $soc->tel=$_POST["tel"];
        $soc->fax=$_POST["fax"];
        $soc->email=$_POST["email"];
        $soc->url=$_POST["url"];
        $soc->capital=$_POST["capital"];
        $soc->gencod=$_POST["gencod"];
        $soc->siren=$_POST["idprof1"];
        $soc->siret=$_POST["idprof2"];
        $soc->ape=$_POST["idprof3"];
        $soc->idprof4=$_POST["idprof4"];
        $soc->typent_id=$_POST["typent_id"];
        $soc->effectif_id=$_POST["effectif_id"];

        $soc->tva_assuj = $_POST["assujtva_value"];
        $soc->status= $_POST["status"];

        //Local Taxes
        $soc->localtax1_assuj       = $_POST["localtax1assuj_value"];
        $soc->localtax2_assuj       = $_POST["localtax2assuj_value"];

        $soc->tva_intra=$_POST["tva_intra"];

        $soc->commercial_id=$_POST["commercial_id"];
        $soc->default_lang=$_POST["default_lang"];

        $soc->logo = dol_sanitizeFileName($_FILES['photo']['name']);

        ### Gestion du logo de la société
        $dir     = $conf->societe->dir_output."/".$soc->id."/logos";
        $file_OK = is_uploaded_file($_FILES['photo']['tmp_name']);
        if ($file_OK)
        {
            if (image_format_supported($_FILES['photo']['name']))
            {
                create_exdir($dir);

                if (@is_dir($dir))
                {
                    $newfile=$dir.'/'.dol_sanitizeFileName($_FILES['photo']['name']);
                    $result = dol_move_uploaded_file($_FILES['photo']['tmp_name'], $newfile, 1);

                    if (! $result > 0)
                    {
                        $errors[] = "ErrorFailedToSaveFile";
                    }
                    else
                    {
                        // Create small thumbs for company (Ratio is near 16/9)
                        // Used on logon for example
                        $imgThumbSmall = vignette($newfile, $maxwidthsmall, $maxheightsmall, '_small', $quality);

                        // Create mini thumbs for company (Ratio is near 16/9)
                        // Used on menu or for setup page for example
                        $imgThumbMini = vignette($newfile, $maxwidthmini, $maxheightmini, '_mini', $quality);
                    }
                }
            }
        }
        ### Gestion du logo de la société

        // We set pays_id, pays_code and label for the selected country
        $soc->pays_id=$_POST["pays_id"]?$_POST["pays_id"]:$mysoc->pays_id;
        if ($soc->pays_id)
        {
            $sql = "SELECT code, libelle";
            $sql.= " FROM ".MAIN_DB_PREFIX."c_pays";
            $sql.= " WHERE rowid = ".$soc->pays_id;
            $resql=$db->query($sql);
            if ($resql)
            {
                $obj = $db->fetch_object($resql);
            }
            else
            {
                dol_print_error($db);
            }
            $soc->pays_code=$obj->code;
            $soc->pays=$obj->libelle;
        }
        $soc->forme_juridique_code=$_POST['forme_juridique_code'];

        /* Show create form */

        print_fiche_titre($langs->trans("NewCompany"));

        if ($conf->use_javascript_ajax)
        {
            print "\n".'<script type="text/javascript" language="javascript">';
            print 'jQuery(document).ready(function () {
						id_te_private=8;
                        id_ef15=1;
                        is_private='.(GETPOST("private")?GETPOST("private"):0).';
						if (is_private) {
							jQuery(".individualline").show();
						} else {
							jQuery(".individualline").hide();
						}
                         jQuery("#radiocompany").click(function() {
                               jQuery(".individualline").hide();
                               jQuery("#typent_id").val(0);
                               jQuery("#effectif_id").val(0);
                               document.formsoc.private.value=0;
                         });
                          jQuery("#radioprivate").click(function() {
                               jQuery(".individualline").show();
                               jQuery("#typent_id").val(id_te_private);
                               jQuery("#effectif_id").val(id_ef15);
                               document.formsoc.private.value=1;
                         });
                         jQuery("#selectpays_id").change(function() {
                           document.formsoc.action.value="create";
                           document.formsoc.submit();
                         });
                     });';
            print '</script>'."\n";

            print "<br>\n";
            print $langs->trans("ThirdPartyType").': &nbsp; ';
            print '<input type="radio" id="radiocompany" class="flat" name="private" value="0"'.(! GETPOST("private")?' checked="true"':'');
            print '> '.$langs->trans("Company/Fundation");
            print ' &nbsp; &nbsp; ';
            print '<input type="radio" id="radioprivate" class="flat" name="private" value="1"'.(! GETPOST("private")?'':' checked="true"');
            print '> '.$langs->trans("Individual");
            print ' ('.$langs->trans("ToCreateContactWithSameName").')';
            print "<br>\n";
            print "<br>\n";
        }


        dol_htmloutput_errors($error,$errors);

        print '<form enctype="multipart/form-data" action="'.$_SERVER["PHP_SELF"].'" method="post" name="formsoc">';

        print '<input type="hidden" name="action" value="add">';
        print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
        print '<input type="hidden" name="private" value='.$soc->particulier.'>';
        print '<input type="hidden" name="type" value='.GETPOST("type").'>';
        if ($modCodeClient->code_auto || $modCodeFournisseur->code_auto) print '<input type="hidden" name="code_auto" value="1">';

        print '<table class="border" width="100%">';

        // Name, firstname
        if ($soc->particulier)
        {
            print '<tr><td><span class="fieldrequired">'.$langs->trans('LastName').'</span></td><td'.(empty($conf->global->SOCIETE_USEPREFIX)?' colspan="3"':'').'><input type="text" size="30" maxlength="60" name="nom" value="'.$soc->nom.'"></td>';
            if (! empty($conf->global->SOCIETE_USEPREFIX))  // Old not used prefix field
            {
                print '<td>'.$langs->trans('Prefix').'</td><td><input type="text" size="5" maxlength="5" name="prefix_comm" value="'.$soc->prefix_comm.'"></td>';
            }
            print '</tr>';
        }
        else
        {
            print '<tr><td><span class="fieldrequired">'.$langs->trans('ThirdPartyName').'</span></td><td'.(empty($conf->global->SOCIETE_USEPREFIX)?' colspan="3"':'').'><input type="text" size="30" maxlength="60" name="nom" value="'.$soc->nom.'"></td>';
            if (! empty($conf->global->SOCIETE_USEPREFIX))  // Old not used prefix field
            {
                print '<td>'.$langs->trans('Prefix').'</td><td><input type="text" size="5" maxlength="5" name="prefix_comm" value="'.$soc->prefix_comm.'"></td>';
            }
            print '</tr>';
        }
        // If javascript on, we show option individual
        if ($conf->use_javascript_ajax)
        {
            print '<tr class="individualline"><td>'.$langs->trans('FirstName').'</td><td><input type="text" size="30" name="prenom" value="'.$soc->firstname.'"></td>';
            print '<td colspan=2>&nbsp;</td></tr>';
            print '<tr class="individualline"><td>'.$langs->trans("UserTitle").'</td><td>';
            print $formcompany->select_civilite($contact->civilite_id).'</td>';
            print '<td colspan=2>&nbsp;</td></tr>';
        }

        // Prospect/Customer
        print '<tr><td width="25%"><span class="fieldrequired">'.$langs->trans('ProspectCustomer').'</span></td><td width="25%"><select class="flat" name="client">';
        if (empty($conf->global->SOCIETE_DISABLE_PROSPECTS)) print '<option value="2"'.($soc->client==2?' selected="selected"':'').'>'.$langs->trans('Prospect').'</option>';
        if (empty($conf->global->SOCIETE_DISABLE_PROSPECTS)) print '<option value="3"'.($soc->client==3?' selected="selected"':'').'>'.$langs->trans('ProspectCustomer').'</option>';
        print '<option value="1"'.($soc->client==1?' selected="selected"':'').'>'.$langs->trans('Customer').'</option>';
        print '<option value="0"'.($soc->client==0?' selected="selected"':'').'>'.$langs->trans('NorProspectNorCustomer').'</option>';
        print '</select></td>';

        print '<td width="25%">'.$langs->trans('CustomerCode').'</td><td width="25%">';
        print '<table class="nobordernopadding"><tr><td>';
        $tmpcode=$soc->code_client;
        if ($modCodeClient->code_auto) $tmpcode=$modCodeClient->getNextValue($soc,0);
        print '<input type="text" name="code_client" size="16" value="'.$tmpcode.'" maxlength="15">';
        print '</td><td>';
        $s=$modCodeClient->getToolTip($langs,$soc,0);
        print $form->textwithpicto('',$s,1);
        print '</td></tr></table>';

        print '</td></tr>';

        if ($conf->fournisseur->enabled)
        {
            // Supplier
            print '<tr>';
            print '<td><span class="fieldrequired">'.$langs->trans('Supplier').'</span></td><td>';
            print $form->selectyesno("fournisseur",$soc->fournisseur,1);
            print '</td>';
            print '<td>'.$langs->trans('SupplierCode').'</td><td>';
            print '<table class="nobordernopadding"><tr><td>';
            $tmpcode=$soc->code_fournisseur;
            if ($modCodeFournisseur->code_auto) $tmpcode=$modCodeFournisseur->getNextValue($soc,1);
            print '<input type="text" name="code_fournisseur" size="16" value="'.$tmpcode.'" maxlength="15">';
            print '</td><td>';
            $s=$modCodeFournisseur->getToolTip($langs,$soc,1);
            print $form->textwithpicto('',$s,1);
            print '</td></tr></table>';
            print '</td></tr>';

            // Category
            if ($soc->fournisseur)
            {
                $load = $soc->LoadSupplierCateg();
                if ( $load == 0)
                {
                    if (sizeof($soc->SupplierCategories) > 0)
                    {
                        print '<tr>';
                        print '<td>'.$langs->trans('SupplierCategory').'</td><td colspan="3">';
                        print $form->selectarray("fournisseur_categorie",$soc->SupplierCategories,$_POST["fournisseur_categorie"],1);
                        print '</td></tr>';
                    }
                }
            }
        }

        // Status
        print '<tr><td>'.$langs->trans('Status').'</td><td colspan="3">';
        print $form->selectarray('status', array('0'=>$langs->trans('ActivityCeased'),'1'=>$langs->trans('InActivity')),1);
        print '</td></tr>';

        // Barcode
        if ($conf->global->MAIN_MODULE_BARCODE)
        {
            print '<tr><td>'.$langs->trans('Gencod').'</td><td colspan="3"><input type="text" name="gencod">';
            print $soc->gencod;
            print '</textarea></td></tr>';
        }

        // Address
        print '<tr><td valign="top">'.$langs->trans('Address').'</td><td colspan="3"><textarea name="adresse" cols="40" rows="3" wrap="soft">';
        print $soc->address;
        print '</textarea></td></tr>';

        // Zip / Town
        print '<tr><td>'.$langs->trans('Zip').'</td><td>';
        print $formcompany->select_ziptown($soc->cp,'zipcode',array('town','selectpays_id','departement_id'),6);
        print '</td><td>'.$langs->trans('Town').'</td><td>';
        print $formcompany->select_ziptown($soc->ville,'town',array('zipcode','selectpays_id','departement_id'));
        print '</td></tr>';

        // Country
        print '<tr><td width="25%">'.$langs->trans('Country').'</td><td colspan="3">';
        $form->select_pays($soc->pays_id,'pays_id');
        if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionnarySetup"),1);
        print '</td></tr>';

        // State
        if (empty($conf->global->SOCIETE_DISABLE_STATE))
        {
            print '<tr><td>'.$langs->trans('State').'</td><td colspan="3">';
            if ($soc->pays_id)
            {
                $formcompany->select_departement($soc->departement_id,$soc->pays_code);
            }
            else
            {
                print $countrynotdefined;
            }
            print '</td></tr>';
        }

        // Phone / Fax
        print '<tr><td>'.$langs->trans('Phone').'</td><td><input type="text" name="tel" value="'.$soc->tel.'"></td>';
        print '<td>'.$langs->trans('Fax').'</td><td><input type="text" name="fax" value="'.$soc->fax.'"></td></tr>';

        print '<tr><td>'.$langs->trans('EMail').($conf->global->SOCIETE_MAIL_REQUIRED?'*':'').'</td><td><input type="text" name="email" size="32" value="'.$soc->email.'"></td>';
        print '<td>'.$langs->trans('Web').'</td><td><input type="text" name="url" size="32" value="'.$soc->url.'"></td></tr>';

        print '<tr>';
        // IdProf1 (SIREN for France)
        $idprof=$langs->transcountry('ProfId1',$soc->pays_code);
        if ($idprof!='-')
        {
            print '<td>'.$idprof.'</td><td>';
            print $formcompany->get_input_id_prof(1,'idprof1',$soc->siren,$soc->pays_code);
            print '</td>';
        }
        else print '<td>&nbsp;</td><td>&nbsp;</td>';
        // IdProf2 (SIRET for France)
        $idprof=$langs->transcountry('ProfId2',$soc->pays_code);
        if ($idprof!='-')
        {
            print '<td>'.$idprof.'</td><td>';
            print $formcompany->get_input_id_prof(2,'idprof2',$soc->siret,$soc->pays_code);
            print '</td>';
        }
        else print '<td>&nbsp;</td><td>&nbsp;</td>';
        print '</tr>';
        print '<tr>';
        // IdProf3 (APE for France)
        $idprof=$langs->transcountry('ProfId3',$soc->pays_code);
        if ($idprof!='-')
        {
            print '<td>'.$idprof.'</td><td>';
            print $formcompany->get_input_id_prof(3,'idprof3',$soc->ape,$soc->pays_code);
            print '</td>';
        }
        else print '<td>&nbsp;</td><td>&nbsp;</td>';
        // IdProf4 (NU for France)
        $idprof=$langs->transcountry('ProfId4',$soc->pays_code);
        if ($idprof!='-')
        {
            print '<td>'.$idprof.'</td><td>';
            print $formcompany->get_input_id_prof(4,'idprof4',$soc->idprof4,$soc->pays_code);
            print '</td>';
        }
        else print '<td>&nbsp;</td><td>&nbsp;</td>';
        print '</tr>';

        // Assujeti TVA
        $html = new Form($db);
        print '<tr><td>'.$langs->trans('VATIsUsed').'</td>';
        print '<td>';
        print $html->selectyesno('assujtva_value',1,1);     // Assujeti par defaut en creation
        print '</td>';
        print '<td nowrap="nowrap">'.$langs->trans('VATIntra').'</td>';
        print '<td nowrap="nowrap">';
        $s = '<input type="text" class="flat" name="tva_intra" size="12" maxlength="20" value="'.$soc->tva_intra.'">';

        if (empty($conf->global->MAIN_DISABLEVATCHECK))
        {
            $s.=' ';

            if ($conf->use_javascript_ajax)
            {
                print "\n";
                print '<script language="JavaScript" type="text/javascript">';
                print "function CheckVAT(a) {\n";
                print "newpopup('".DOL_URL_ROOT."/societe/checkvat/checkVatPopup.php?vatNumber='+a,'".dol_escape_js($langs->trans("VATIntraCheckableOnEUSite"))."',500,230);\n";
                print "}\n";
                print '</script>';
                print "\n";
                $s.='<a href="#" onclick="javascript: CheckVAT(document.formsoc.tva_intra.value);">'.$langs->trans("VATIntraCheck").'</a>';
                $s = $form->textwithpicto($s,$langs->trans("VATIntraCheckDesc",$langs->trans("VATIntraCheck")),1);
            }
            else
            {
                $s.='<a href="'.$langs->transcountry("VATIntraCheckURL",$soc->id_pays).'" target="_blank">'.img_picto($langs->trans("VATIntraCheckableOnEUSite"),'help').'</a>';
            }
        }
        print $s;
        print '</td>';
        print '</tr>';

        // Type - Size
        print '<tr><td>'.$langs->trans("ThirdPartyType").'</td><td>'."\n";
        print $form->selectarray("typent_id",$formcompany->typent_array(0), $soc->typent_id);
        if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionnarySetup"),1);
        print '</td>';
        print '<td>'.$langs->trans("Staff").'</td><td>';
        print $form->selectarray("effectif_id",$formcompany->effectif_array(0), $soc->effectif_id);
        if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionnarySetup"),1);
        print '</td></tr>';

        // Legal Form
        print '<tr><td>'.$langs->trans('JuridicalStatus').'</td>';
        print '<td colspan="3">';
        if ($soc->pays_id)
        {
            $formcompany->select_forme_juridique($soc->forme_juridique_code,$soc->pays_code);
        }
        else
        {
            print $countrynotdefined;
        }
        print '</td></tr>';

        // Capital
        print '<tr><td>'.$langs->trans('Capital').'</td><td colspan="3"><input type="text" name="capital" size="10" value="'.$soc->capital.'"> '.$langs->trans("Currency".$conf->monnaie).'</td></tr>';

        // Local Taxes
        // TODO add specific function by country
        if($mysoc->pays_code=='ES')
        {
            if($mysoc->localtax1_assuj=="1" && $mysoc->localtax2_assuj=="1")
            {
                print '<tr><td>'.$langs->trans("LocalTax1IsUsedES").'</td><td>';
                print $html->selectyesno('localtax1assuj_value',0,1);
                print '</td><td>'.$langs->trans("LocalTax2IsUsedES").'</td><td>';
                print $html->selectyesno('localtax2assuj_value',0,1);
                print '</td></tr>';

            }
            elseif($mysoc->localtax1_assuj=="1")
            {
                print '<tr><td>'.$langs->trans("LocalTax1IsUsedES").'</td><td colspan="3">';
                print $html->selectyesno('localtax1assuj_value',0,1);
                print '</td><tr>';
            }
            elseif($mysoc->localtax2_assuj=="1")
            {
                print '<tr><td>'.$langs->trans("LocalTax2IsUsedES").'</td><td colspan="3">';
                print $html->selectyesno('localtax2assuj_value',0,1);
                print '</td><tr>';
            }
        }

        if ($conf->global->MAIN_MULTILANGS)
        {
            print '<tr><td>'.$langs->trans("DefaultLang").'</td><td colspan="3">'."\n";
            print $formadmin->select_language(($soc->default_lang?$soc->default_lang:$conf->global->MAIN_LANG_DEFAULT),'default_lang',0,0,1);
            print '</td>';
            print '</tr>';
        }

        if ($user->rights->societe->client->voir)
        {
            // Assign a Name
            print '<tr>';
            print '<td>'.$langs->trans("AllocateCommercial").'</td>';
            print '<td colspan="3">';
            $form->select_users($soc->commercial_id,'commercial_id',1);
            print '</td></tr>';
        }

        // Ajout du logo
        print '<tr>';
        print '<td>'.$langs->trans("Logo").'</td>';
        print '<td colspan="3">';
        print '<input class="flat" type="file" name="photo" id="photoinput" />';
        print '</td>';
        print '</tr>';

        print '<tr><td colspan="4" align="center">';
        print '<input type="submit" class="button" value="'.$langs->trans('AddThirdParty').'">';
        print '</td></tr>'."\n";

        print '</table>'."\n";
        print '</form>'."\n";
    }
    elseif ($action == 'edit')
    {
        /*
         * Edition
         */
        print_fiche_titre($langs->trans("EditCompany"));

        if ($socid)
        {
            // Load object modCodeTiers
            $module=$conf->global->SOCIETE_CODECLIENT_ADDON;
            if (! $module) dolibarr_error('',$langs->trans("ErrorModuleThirdPartyCodeInCompanyModuleNotDefined"));
            if (substr($module, 0, 15) == 'mod_codeclient_' && substr($module, -3) == 'php')
            {
                $module = substr($module, 0, dol_strlen($module)-4);
            }
            require_once(DOL_DOCUMENT_ROOT ."/includes/modules/societe/".$module.".php");
            $modCodeClient = new $module;
            // We verified if the tag prefix is used
            if ($modCodeClient->code_auto)
            {
                $prefixCustomerIsUsed = $modCodeClient->verif_prefixIsUsed();
            }
            $module=$conf->global->SOCIETE_CODEFOURNISSEUR_ADDON;
            if (! $module) $module=$conf->global->SOCIETE_CODECLIENT_ADDON;
            if (substr($module, 0, 15) == 'mod_codeclient_' && substr($module, -3) == 'php')
            {
                $module = substr($module, 0, dol_strlen($module)-4);
            }
            require_once(DOL_DOCUMENT_ROOT ."/includes/modules/societe/".$module.".php");
            $modCodeFournisseur = new $module;
            // On verifie si la balise prefix est utilisee
            if ($modCodeFournisseur->code_auto)
            {
                $prefixSupplierIsUsed = $modCodeFournisseur->verif_prefixIsUsed();
            }

            if (! $_POST["nom"])
            {
                $soc = new Societe($db);
                $soc->fetch($socid);
            }
            else
            {
                $soc->id=$_POST["socid"];
                $soc->nom=$_POST["nom"];
                $soc->prefix_comm=$_POST["prefix_comm"];
                $soc->client=$_POST["client"];
                $soc->code_client=$_POST["code_client"];
                $soc->fournisseur=$_POST["fournisseur"];
                $soc->code_fournisseur=$_POST["code_fournisseur"];
                $soc->adresse=$_POST["adresse"]; // TODO obsolete
                $soc->address=$_POST["adresse"];
                $soc->cp=$_POST["zipcode"];
                $soc->ville=$_POST["town"];
                $soc->pays_id=$_POST["pays_id"]?$_POST["pays_id"]:$mysoc->pays_id;
                $soc->departement_id=$_POST["departement_id"];
                $soc->tel=$_POST["tel"];
                $soc->fax=$_POST["fax"];
                $soc->email=$_POST["email"];
                $soc->url=$_POST["url"];
                $soc->capital=$_POST["capital"];
                $soc->siren=$_POST["idprof1"];
                $soc->siret=$_POST["idprof2"];
                $soc->ape=$_POST["idprof3"];
                $soc->idprof4=$_POST["idprof4"];
                $soc->typent_id=$_POST["typent_id"];
                $soc->effectif_id=$_POST["effectif_id"];
                $soc->gencod=$_POST["gencod"];
                $soc->forme_juridique_code=$_POST["forme_juridique_code"];
                $soc->default_lang=$_POST["default_lang"];

                $soc->tva_assuj = $_POST["assujtva_value"];
                $soc->tva_intra=$_POST["tva_intra"];
                $soc->status=$_POST["status"];

                //Local Taxes
                $soc->localtax1_assuj       = $_POST["localtax1assuj_value"];
                $soc->localtax2_assuj       = $_POST["localtax2assuj_value"];

                // We set pays_id, and pays_code label of the chosen country
                if ($soc->pays_id)
                {
                    $sql = "SELECT code, libelle from ".MAIN_DB_PREFIX."c_pays where rowid = ".$soc->pays_id;
                    $resql=$db->query($sql);
                    if ($resql)
                    {
                        $obj = $db->fetch_object($resql);
                    }
                    else
                    {
                        dol_print_error($db);
                    }
                    $soc->pays_code=$obj->code;
                    $soc->pays=$langs->trans("Country".$obj->code)?$langs->trans("Country".$obj->code):$obj->libelle;
                }
            }

            dol_htmloutput_errors($error,$errors);

            if ($conf->use_javascript_ajax)
            {
                print "\n".'<script type="text/javascript" language="javascript">';
                print 'jQuery(document).ready(function () {
                            jQuery("#selectpays_id").change(function() {
                                document.formsoc.action.value="edit";
                                document.formsoc.submit();
                            });
                       })';
                print '</script>'."\n";
            }

            print '<form enctype="multipart/form-data" action="'.$_SERVER["PHP_SELF"].'?socid='.$soc->id.'" method="post" name="formsoc">';
            print '<input type="hidden" name="action" value="update">';
            print '<input type="hidden" name="token" value="'.$_SESSION['newtoken'].'">';
            print '<input type="hidden" name="socid" value="'.$soc->id.'">';
            if ($modCodeClient->code_auto || $modCodeFournisseur->code_auto) print '<input type="hidden" name="code_auto" value="1">';

            print '<table class="border" width="100%">';

            // Name
            print '<tr><td><span class="fieldrequired">'.$langs->trans('ThirdPartyName').'</span></td><td colspan="3"><input type="text" size="40" maxlength="60" name="nom" value="'.$soc->nom.'"></td></tr>';

            // Prefix
            if (! empty($conf->global->SOCIETE_USEPREFIX))  // Old not used prefix field
            {
                print '<tr><td>'.$langs->trans("Prefix").'</td><td colspan="3">';
                // It does not change the prefix mode using the auto numbering prefix
                if (($prefixCustomerIsUsed || $prefixSupplierIsUsed) && $soc->prefix_comm)
                {
                    print '<input type="hidden" name="prefix_comm" value="'.$soc->prefix_comm.'">';
                    print $soc->prefix_comm;
                }
                else
                {
                    print '<input type="text" size="5" maxlength="5" name="prefix_comm" value="'.$soc->prefix_comm.'">';
                }
                print '</td>';
            }

            // Prospect/Customer
            print '<tr><td width="25%"><span class="fieldrequired">'.$langs->trans('ProspectCustomer').'</span></td><td width="25%"><select class="flat" name="client">';
            if (empty($conf->global->SOCIETE_DISABLE_PROSPECTS)) print '<option value="2"'.($soc->client==2?' selected="selected"':'').'>'.$langs->trans('Prospect').'</option>';
            if (empty($conf->global->SOCIETE_DISABLE_PROSPECTS)) print '<option value="3"'.($soc->client==3?' selected="selected"':'').'>'.$langs->trans('ProspectCustomer').'</option>';
            print '<option value="1"'.($soc->client==1?' selected="selected"':'').'>'.$langs->trans('Customer').'</option>';
            print '<option value="0"'.($soc->client==0?' selected="selected"':'').'>'.$langs->trans('NorProspectNorCustomer').'</option>';
            print '</select></td>';
            print '<td width="25%">'.$langs->trans('CustomerCode').'</td><td width="25%">';

            print '<table class="nobordernopadding"><tr><td>';
            if ((!$soc->code_client || $soc->code_client == -1) && $modCodeClient->code_auto)
            {
                $tmpcode=$soc->code_client;
                if (empty($tmpcode) && $modCodeClient->code_auto) $tmpcode=$modCodeClient->getNextValue($soc,0);
                print '<input type="text" name="code_client" size="16" value="'.$tmpcode.'" maxlength="15">';
            }
            else if ($soc->codeclient_modifiable())
            {
                print '<input type="text" name="code_client" size="16" value="'.$soc->code_client.'" maxlength="15">';
            }
            else
            {
                print $soc->code_client;
                print '<input type="hidden" name="code_client" value="'.$soc->code_client.'">';
            }
            print '</td><td>';
            $s=$modCodeClient->getToolTip($langs,$soc,0);
            print $form->textwithpicto('',$s,1);
            print '</td></tr></table>';

            print '</td></tr>';

            // Supplier
            if ($conf->fournisseur->enabled)
            {
                print '<tr>';
                print '<td><span class="fieldrequired">'.$langs->trans('Supplier').'</span></td><td>';
                print $form->selectyesno("fournisseur",$soc->fournisseur,1);
                print '</td>';
                print '<td>'.$langs->trans('SupplierCode').'</td><td>';

                print '<table class="nobordernopadding"><tr><td>';
                if ((!$soc->code_fournisseur || $soc->code_fournisseur == -1) && $modCodeFournisseur->code_auto)
                {
                    $tmpcode=$soc->code_fournisseur;
                    if (empty($tmpcode) && $modCodeFournisseur->code_auto) $tmpcode=$modCodeFournisseur->getNextValue($soc,1);
                    print '<input type="text" name="code_fournisseur" size="16" value="'.$tmpcode.'" maxlength="15">';
                }
                else if ($soc->codefournisseur_modifiable())
                {
                    print '<input type="text" name="code_fournisseur" size="16" value="'.$soc->code_fournisseur.'" maxlength="15">';
                }
                else
                {
                    print $soc->code_fournisseur;
                    print '<input type="hidden" name="code_fournisseur" value="'.$soc->code_fournisseur.'">';
                }
                print '</td><td>';
                $s=$modCodeFournisseur->getToolTip($langs,$soc,1);
                print $form->textwithpicto('',$s,1);
                print '</td></tr></table>';

                print '</td></tr>';

                // Category
                if ($conf->categorie->enabled && $soc->fournisseur)
                {
                    $load = $soc->LoadSupplierCateg();
                    if ( $load == 0)
                    {
                        if (sizeof($soc->SupplierCategories) > 0)
                        {
                            print '<tr>';
                            print '<td>'.$langs->trans('SupplierCategory').'</td><td colspan="3">';
                            print $form->selectarray("fournisseur_categorie",$soc->SupplierCategories,'',1);
                            print '</td></tr>';
                        }
                    }
                }
            }

            // Status
            print '<tr><td>'.$langs->trans("Status").'</td><td colspan="3">';
            print $form->selectarray('status', array('0'=>$langs->trans('ActivityCeased'),'1'=>$langs->trans('InActivity')),$soc->status);
            print '</td></tr>';

            // Barcode
            if ($conf->global->MAIN_MODULE_BARCODE)
            {
                print '<tr><td valign="top">'.$langs->trans('Gencod').'</td><td colspan="3"><input type="text" name="gencod" value="'.$soc->gencod.'">';
                print '</td></tr>';
            }

            // Address
            print '<tr><td valign="top">'.$langs->trans('Address').'</td><td colspan="3"><textarea name="adresse" cols="40" rows="3" wrap="soft">';
            print $soc->address;
            print '</textarea></td></tr>';

            // Zip / Town
            print '<tr><td>'.$langs->trans('Zip').'</td><td>';
            print $formcompany->select_ziptown($soc->cp,'zipcode',array('town','selectpays_id','departement_id'),6);
            print '</td><td>'.$langs->trans('Town').'</td><td>';
            print $formcompany->select_ziptown($soc->ville,'town',array('zipcode','selectpays_id','departement_id'));
            print '</td></tr>';

            // Country
            print '<tr><td>'.$langs->trans('Country').'</td><td colspan="3">';
            $form->select_pays($soc->pays_id,'pays_id');
            if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionnarySetup"),1);
            print '</td></tr>';

            // State
            if (empty($conf->global->SOCIETE_DISABLE_STATE))
            {
                print '<tr><td>'.$langs->trans('State').'</td><td colspan="3">';
                $formcompany->select_departement($soc->departement_id,$soc->pays_code);
                print '</td></tr>';
            }

            // Phone / Fax
            print '<tr><td>'.$langs->trans('Phone').'</td><td><input type="text" name="tel" value="'.$soc->tel.'"></td>';
            print '<td>'.$langs->trans('Fax').'</td><td><input type="text" name="fax" value="'.$soc->fax.'"></td></tr>';

            // EMail / Web
            print '<tr><td>'.$langs->trans('EMail').($conf->global->SOCIETE_MAIL_REQUIRED?'*':'').'</td><td><input type="text" name="email" size="32" value="'.$soc->email.'"></td>';
            print '<td>'.$langs->trans('Web').'</td><td><input type="text" name="url" size="32" value="'.$soc->url.'"></td></tr>';

            print '<tr>';
            // IdProf1 (SIREN for France)
            $idprof=$langs->transcountry('ProfId1',$soc->pays_code);
            if ($idprof!='-')
            {
                print '<td>'.$idprof.'</td><td>';
                print $formcompany->get_input_id_prof(1,'idprof1',$soc->siren,$soc->pays_code);
                print '</td>';
            }
            else print '<td>&nbsp;</td><td>&nbsp;</td>';
            // IdProf2 (SIRET for France)
            $idprof=$langs->transcountry('ProfId2',$soc->pays_code);
            if ($idprof!='-')
            {
                print '<td>'.$idprof.'</td><td>';
                print $formcompany->get_input_id_prof(2,'idprof2',$soc->siret,$soc->pays_code);
                print '</td>';
            }
            else print '<td>&nbsp;</td><td>&nbsp;</td>';
            print '</tr>';
            print '<tr>';
            // IdProf3 (APE for France)
            $idprof=$langs->transcountry('ProfId3',$soc->pays_code);
            if ($idprof!='-')
            {
                print '<td>'.$idprof.'</td><td>';
                print $formcompany->get_input_id_prof(3,'idprof3',$soc->ape,$soc->pays_code);
                print '</td>';
            }
            else print '<td>&nbsp;</td><td>&nbsp;</td>';
            // IdProf4 (NU for France)
            $idprof=$langs->transcountry('ProfId4',$soc->pays_code);
            if ($idprof!='-')
            {
                print '<td>'.$idprof.'</td><td>';
                print $formcompany->get_input_id_prof(4,'idprof4',$soc->idprof4,$soc->pays_code);
                print '</td>';
            }
            else print '<td>&nbsp;</td><td>&nbsp;</td>';
            print '</tr>';

            // VAT payers
            print '<tr><td>'.$langs->trans('VATIsUsed').'</td><td>';
            print $form->selectyesno('assujtva_value',$soc->tva_assuj,1);
            print '</td>';

            // VAT Code
            print '<td nowrap="nowrap">'.$langs->trans('VATIntra').'</td>';
            print '<td nowrap="nowrap">';
            $s ='<input type="text" class="flat" name="tva_intra" size="12" maxlength="20" value="'.$soc->tva_intra.'">';

            if (empty($conf->global->MAIN_DISABLEVATCHECK))
            {
                $s.=' &nbsp; ';

                if ($conf->use_javascript_ajax)
                {
                    print "\n";
                    print '<script language="JavaScript" type="text/javascript">';
                    print "function CheckVAT(a) {\n";
                    print "newpopup('".DOL_URL_ROOT."/societe/checkvat/checkVatPopup.php?vatNumber='+a,'".dol_escape_js($langs->trans("VATIntraCheckableOnEUSite"))."',500,285);\n";
                    print "}\n";
                    print '</script>';
                    print "\n";
                    $s.='<a href="#" onclick="javascript: CheckVAT(document.formsoc.tva_intra.value);">'.$langs->trans("VATIntraCheck").'</a>';
                    $s = $form->textwithpicto($s,$langs->trans("VATIntraCheckDesc",$langs->trans("VATIntraCheck")),1);
                }
                else
                {
                    $s.='<a href="'.$langs->transcountry("VATIntraCheckURL",$soc->id_pays).'" target="_blank">'.img_picto($langs->trans("VATIntraCheckableOnEUSite"),'help').'</a>';
                }
            }
            print $s;
            print '</td>';
            print '</tr>';

            // Local Taxes
            // TODO add specific function by country
            if($mysoc->pays_code=='ES')
            {
                if($mysoc->localtax1_assuj=="1" && $mysoc->localtax2_assuj=="1")
                {
                    print '<tr><td>'.$langs->trans("LocalTax1IsUsedES").'</td><td>';
                    print $form->selectyesno('localtax1assuj_value',$soc->localtax1_assuj,1);
                    print '</td><td>'.$langs->trans("LocalTax2IsUsedES").'</td><td>';
                    print $form->selectyesno('localtax2assuj_value',$soc->localtax2_assuj,1);
                    print '</td></tr>';

                }
                elseif($mysoc->localtax1_assuj=="1")
                {
                    print '<tr><td>'.$langs->trans("LocalTax1IsUsedES").'</td><td colspan="3">';
                    print $form->selectyesno('localtax1assuj_value',$soc->localtax1_assuj,1);
                    print '</td></tr>';

                }
                elseif($mysoc->localtax2_assuj=="1")
                {
                    print '<tr><td>'.$langs->trans("LocalTax2IsUsedES").'</td><td colspan="3">';
                    print $form->selectyesno('localtax2assuj_value',$soc->localtax2_assuj,1);
                    print '</td></tr>';
                }
            }

            // Type - Size
            print '<tr><td>'.$langs->trans("ThirdPartyType").'</td><td>';
            print $form->selectarray("typent_id",$formcompany->typent_array(0), $soc->typent_id);
            if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionnarySetup"),1);
            print '</td>';
            print '<td>'.$langs->trans("Staff").'</td><td>';
            print $form->selectarray("effectif_id",$formcompany->effectif_array(0), $soc->effectif_id);
            if ($user->admin) print info_admin($langs->trans("YouCanChangeValuesForThisListFromDictionnarySetup"),1);
            print '</td></tr>';

            print '<tr><td>'.$langs->trans('JuridicalStatus').'</td><td colspan="3">';
            $formcompany->select_forme_juridique($soc->forme_juridique_code,$soc->pays_code);
            print '</td></tr>';

            // Capital
            print '<tr><td>'.$langs->trans("Capital").'</td><td colspan="3"><input type="text" name="capital" size="10" value="'.$soc->capital.'"> '.$langs->trans("Currency".$conf->monnaie).'</td></tr>';

            // Default language
            if ($conf->global->MAIN_MULTILANGS)
            {
                print '<tr><td>'.$langs->trans("DefaultLang").'</td><td colspan="3">'."\n";
                print $formadmin->select_language($soc->default_lang,'default_lang',0,0,1);
                print '</td>';
                print '</tr>';
            }

            // Logo
            print '<tr>';
            print '<td>'.$langs->trans("Logo").'</span></td>';
            print '<td colspan="3">';
            if ($soc->logo) print $form->showphoto('societe',$soc,50);
            $caneditfield=1;
            if ($caneditfield)
            {
                if ($soc->logo) print "<br>\n";
                print '<table class="nobordernopadding">';
                if ($soc->logo) print '<tr><td><input type="checkbox" class="flat" name="deletephoto" id="photodelete"> '.$langs->trans("Delete").'<br><br></td></tr>';
                //print '<tr><td>'.$langs->trans("PhotoFile").'</td></tr>';
                print '<tr><td><input type="file" class="flat" name="photo" id="photoinput"></td></tr>';
                print '</table>';
            }
            print '</td>';
            print '</tr>';

            print '</table>';
            print '<br>';

            print '<center>';
            print '<input type="submit" class="button" name="save" value="'.$langs->trans("Save").'">';
            print ' &nbsp; &nbsp; ';
            print '<input type="submit" class="button" name="cancel" value="'.$langs->trans("Cancel").'">';
            print '</center>';

            print '</form>';
        }
    }
    else
    {
        /*
         * View
         */
        $soc = new Societe($db);
        $result=$soc->fetch($socid);
        if ($result < 0)
        {
            dol_print_error($db,$soc->error);
            exit;
        }

        $head = societe_prepare_head($soc);

        dol_fiche_head($head, 'card', $langs->trans("ThirdParty"),0,'company');

        $html = new Form($db);


        // Confirm delete third party
        if ($action == 'delete' || $conf->use_javascript_ajax)
        {
            $html = new Form($db);
            $ret=$html->form_confirm($_SERVER["PHP_SELF"]."?socid=".$soc->id,$langs->trans("DeleteACompany"),$langs->trans("ConfirmDeleteCompany"),"confirm_delete",'',0,"action-delete");
            if ($ret == 'html') print '<br>';
        }

        dol_htmloutput_errors($error,$errors);

        print '<table class="border" width="100%">';

        // Ref
        /*
        print '<tr><td width="25%" valign="top">'.$langs->trans("Ref").'</td>';
        print '<td colspan="2">';
        print $fuser->id;
        print '</td>';
        print '</tr>';
        */

        // Name
        print '<tr><td width="20%">'.$langs->trans('ThirdPartyName').'</td>';
        print '<td colspan="3">';
        print $form->showrefnav($soc,'socid','',($user->societe_id?0:1),'rowid','nom');
        print '</td>';
        print '</tr>';

        // Logo
        $rowspan=4;
        if (! empty($conf->global->SOCIETE_USEPREFIX)) $rowspan++;
        if ($soc->client) $rowspan++;
        if ($conf->fournisseur->enabled && $soc->fournisseur) $rowspan++;
        if ($conf->global->MAIN_MODULE_BARCODE) $rowspan++;
        if (empty($conf->global->SOCIETE_DISABLE_STATE)) $rowspan++;
        $showlogo='';
        if ($soc->logo)
        {
            $showlogo.='<td rowspan="'.$rowspan.'" style="text-align: center;" width="25%">';
            $showlogo.=$html->showphoto('societe',$soc,50);
            $showlogo.='</td>';
        }

        if (! empty($conf->global->SOCIETE_USEPREFIX))  // Old not used prefix field
        {
            print '<tr><td>'.$langs->trans('Prefix').'</td><td colspan="'.(2+($soc->logo?0:1)).'">'.$soc->prefix_comm.'</td>';
            print $showlogo; $showlogo='';
            print '</tr>';
        }

        if ($soc->client)
        {
            print '<tr><td>';
            print $langs->trans('CustomerCode').'</td><td colspan="'.(2+($soc->logo?0:1)).'">';
            print $soc->code_client;
            if ($soc->check_codeclient() <> 0) print ' <font class="error">('.$langs->trans("WrongCustomerCode").')</font>';
            print '</td>';
            print $showlogo; $showlogo='';
            print '</tr>';
        }

        if ($conf->fournisseur->enabled && $soc->fournisseur)
        {
            print '<tr><td>';
            print $langs->trans('SupplierCode').'</td><td colspan="'.(2+($soc->logo?0:1)).'">';
            print $soc->code_fournisseur;
            if ($soc->check_codefournisseur() <> 0) print ' <font class="error">('.$langs->trans("WrongSupplierCode").')</font>';
            print '</td>';
            print $showlogo; $showlogo='';
            print '</tr>';
        }

        // Status
        print '<tr><td>'.$langs->trans("Status").'</td>';
        print '<td colspan="'.(2+($soc->logo?0:1)).'">';
        print $soc->getLibStatut(2);
        print '</td>';
        print $showlogo; $showlogo='';
        print '</tr>';

        // Barcode
        if ($conf->global->MAIN_MODULE_BARCODE)
        {
            print '<tr><td>'.$langs->trans('Gencod').'</td><td colspan="'.(2+($soc->logo?0:1)).'">'.$soc->gencod.'</td></tr>';
        }

        // Address
        print "<tr><td valign=\"top\">".$langs->trans('Address').'</td><td colspan="'.(2+($soc->logo?0:1)).'">';
        dol_print_address($soc->address,'gmap','thirdparty',$soc->id);
        print "</td></tr>";

        // Zip / Town
        print '<tr><td width="25%">'.$langs->trans('Zip').' / '.$langs->trans("Town").'</td><td colspan="'.(2+($soc->logo?0:1)).'">';
        print $soc->cp.($soc->cp && $soc->ville?" / ":"").$soc->ville;
        print "</td>";
        print '</tr>';

        // Country
        print '<tr><td>'.$langs->trans("Country").'</td><td colspan="'.(2+($soc->logo?0:1)).'" nowrap="nowrap">';
        $img=picto_from_langcode($soc->pays_code);
        if ($soc->isInEEC()) print $form->textwithpicto(($img?$img.' ':'').$soc->pays,$langs->trans("CountryIsInEEC"),1,0);
        else print ($img?$img.' ':'').$soc->pays;
        print '</td></tr>';

        // State
        if (empty($conf->global->SOCIETE_DISABLE_STATE)) print '<tr><td>'.$langs->trans('State').'</td><td colspan="'.(2+($soc->logo?0:1)).'">'.$soc->departement.'</td>';

        print '<tr><td>'.$langs->trans('Phone').'</td><td style="min-width: 25%;">'.dol_print_phone($soc->tel,$soc->pays_code,0,$soc->id,'AC_TEL').'</td>';
        print '<td>'.$langs->trans('Fax').'</td><td style="min-width: 25%;">'.dol_print_phone($soc->fax,$soc->pays_code,0,$soc->id,'AC_FAX').'</td></tr>';

        // EMail
        print '<tr><td>'.$langs->trans('EMail').'</td><td width="25%">';
        print dol_print_email($soc->email,0,$soc->id,'AC_EMAIL');
        print '</td>';

        // Web
        print '<td>'.$langs->trans('Web').'</td><td>';
        print dol_print_url($soc->url);
        print '</td></tr>';

        // ProfId1 (SIREN for France)
        $profid=$langs->transcountry('ProfId1',$soc->pays_code);
        if ($profid!='-')
        {
            print '<tr><td>'.$profid.'</td><td>';
            print $soc->siren;
            if ($soc->siren)
            {
                if ($soc->id_prof_check(1,$soc) > 0) print ' &nbsp; '.$soc->id_prof_url(1,$soc);
                else print ' <font class="error">('.$langs->trans("ErrorWrongValue").')</font>';
            }
            print '</td>';
        }
        else print '<tr><td>&nbsp;</td><td>&nbsp;</td>';
        // ProfId2 (SIRET for France)
        $profid=$langs->transcountry('ProfId2',$soc->pays_code);
        if ($profid!='-')
        {
            print '<td>'.$profid.'</td><td>';
            print $soc->siret;
            if ($soc->siret)
            {
                if ($soc->id_prof_check(2,$soc) > 0) print ' &nbsp; '.$soc->id_prof_url(2,$soc);
                else print ' <font class="error">('.$langs->trans("ErrorWrongValue").')</font>';
            }
            print '</td></tr>';
        }
        else print '<td>&nbsp;</td><td>&nbsp;</td></tr>';

        // ProfId3 (APE for France)
        $profid=$langs->transcountry('ProfId3',$soc->pays_code);
        if ($profid!='-')
        {
            print '<tr><td>'.$profid.'</td><td>';
            print $soc->ape;
            if ($soc->ape)
            {
                if ($soc->id_prof_check(3,$soc) > 0) print ' &nbsp; '.$soc->id_prof_url(3,$soc);
                else print ' <font class="error">('.$langs->trans("ErrorWrongValue").')</font>';
            }
            print '</td>';
        }
        else print '<tr><td>&nbsp;</td><td>&nbsp;</td>';
        // ProfId4 (NU for France)
        $profid=$langs->transcountry('ProfId4',$soc->pays_code);
        if ($profid!='-')
        {
            print '<td>'.$profid.'</td><td>';
            print $soc->idprof4;
            if ($soc->idprof4)
            {
                if ($soc->id_prof_check(4,$soc) > 0) print ' &nbsp; '.$soc->id_prof_url(4,$soc);
                else print ' <font class="error">('.$langs->trans("ErrorWrongValue").')</font>';
            }
            print '</td></tr>';
        }
        else print '<td>&nbsp;</td><td>&nbsp;</td></tr>';

        // VAT payers
        $html = new Form($db);
        print '<tr><td>';
        print $langs->trans('VATIsUsed');
        print '</td><td>';
        print yn($soc->tva_assuj);
        print '</td>';

        // VAT Code
        print '<td nowrap="nowrpa">'.$langs->trans('VATIntra').'</td><td>';
        if ($soc->tva_intra)
        {
            $s='';
            $s.=$soc->tva_intra;
            $s.='<input type="hidden" name="tva_intra" size="12" maxlength="20" value="'.$soc->tva_intra.'">';

            if (empty($conf->global->MAIN_DISABLEVATCHECK))
            {
                $s.=' &nbsp; ';

                if ($conf->use_javascript_ajax)
                {
                    print "\n";
                    print '<script language="JavaScript" type="text/javascript">';
                    print "function CheckVAT(a) {\n";
                    print "newpopup('".DOL_URL_ROOT."/societe/checkvat/checkVatPopup.php?vatNumber='+a,'".dol_escape_js($langs->trans("VATIntraCheckableOnEUSite"))."',500,285);\n";
                    print "}\n";
                    print '</script>';
                    print "\n";
                    $s.='<a href="#" onclick="javascript: CheckVAT(document.formsoc.tva_intra.value);">'.$langs->trans("VATIntraCheck").'</a>';
                    $s = $form->textwithpicto($s,$langs->trans("VATIntraCheckDesc",$langs->trans("VATIntraCheck")),1);
                }
                else
                {
                    $s.='<a href="'.$langs->transcountry("VATIntraCheckURL",$soc->id_pays).'" target="_blank">'.img_picto($langs->trans("VATIntraCheckableOnEUSite"),'help').'</a>';
                }
            }
            print $s;
        }
        else
        {
            print '&nbsp;';
        }
        print '</td>';
        print '</tr>';

        // Local Taxes
        // TODO add specific function by country
        if($mysoc->pays_code=='ES')
        {
            if($mysoc->localtax1_assuj=="1" && $mysoc->localtax2_assuj=="1")
            {
                print '<tr><td>'.$langs->trans("LocalTax1IsUsedES").'</td><td>';
                print yn($soc->localtax1_assuj);
                print '</td><td>'.$langs->trans("LocalTax2IsUsedES").'</td><td>';
                print yn($soc->localtax2_assuj);
                print '</td></tr>';

            }
            elseif($mysoc->localtax1_assuj=="1")
            {
                print '<tr><td>'.$langs->trans("LocalTax1IsUsedES").'</td><td colspan="3">';
                print yn($soc->localtax1_assuj);
                print '</td><tr>';
            }
            elseif($mysoc->localtax2_assuj=="1")
            {
                print '<tr><td>'.$langs->trans("LocalTax2IsUsedES").'</td><td colspan="3">';
                print yn($soc->localtax2_assuj);
                print '</td><tr>';
            }
        }

        // Type + Staff
        $arr = $formcompany->typent_array(1);
        $soc->typent= $arr[$soc->typent_code];
        print '<tr><td>'.$langs->trans("ThirdPartyType").'</td><td>'.$soc->typent.'</td><td>'.$langs->trans("Staff").'</td><td>'.$soc->effectif.'</td></tr>';

        // Legal
        print '<tr><td>'.$langs->trans('JuridicalStatus').'</td><td colspan="3">'.$soc->forme_juridique.'</td></tr>';

        // Capital
        print '<tr><td>'.$langs->trans('Capital').'</td><td colspan="3">';
        if ($soc->capital) print $soc->capital.' '.$langs->trans("Currency".$conf->monnaie);
        else print '&nbsp;';
        print '</td></tr>';

        // Default language
        if ($conf->global->MAIN_MULTILANGS)
        {
            require_once(DOL_DOCUMENT_ROOT."/lib/functions2.lib.php");
            print '<tr><td>'.$langs->trans("DefaultLang").'</td><td colspan="3">';
            //$s=picto_from_langcode($soc->default_lang);
            //print ($s?$s.' ':'');
            $langs->load("languages");
            $labellang = ($soc->default_lang?$langs->trans('Language_'.$soc->default_lang):'');
            print $labellang;
            print '</td></tr>';
        }

        // Ban
        if (empty($conf->global->SOCIETE_DISABLE_BANKACCOUNT))
        {
            print '<tr><td>';
            print '<table width="100%" class="nobordernopadding"><tr><td>';
            print $langs->trans('RIB');
            print '<td><td align="right">';
            if ($user->rights->societe->creer)
            print '<a href="'.DOL_URL_ROOT.'/societe/rib.php?socid='.$soc->id.'">'.img_edit().'</a>';
            else
            print '&nbsp;';
            print '</td></tr></table>';
            print '</td>';
            print '<td colspan="3">';
            print $soc->display_rib();
            print '</td></tr>';
        }

        // Parent company
        if (empty($conf->global->SOCIETE_DISABLE_PARENTCOMPANY))
        {
            print '<tr><td>';
            print '<table width="100%" class="nobordernopadding"><tr><td>';
            print $langs->trans('ParentCompany');
            print '<td><td align="right">';
            if ($user->rights->societe->creer)
            print '<a href="'.DOL_URL_ROOT.'/societe/lien.php?socid='.$soc->id.'">'.img_edit() .'</a>';
            else
            print '&nbsp;';
            print '</td></tr></table>';
            print '</td>';
            print '<td colspan="3">';
            if ($soc->parent)
            {
                $socm = new Societe($db);
                $socm->fetch($soc->parent);
                print $socm->getNomUrl(1).' '.($socm->code_client?"(".$socm->code_client.")":"");
                print $socm->ville?' - '.$socm->ville:'';
            }
            else {
                print $langs->trans("NoParentCompany");
            }
            print '</td></tr>';
        }

        // Commercial
        print '<tr><td>';
        print '<table width="100%" class="nobordernopadding"><tr><td>';
        print $langs->trans('SalesRepresentatives');
        print '<td><td align="right">';
        if ($user->rights->societe->creer)
        print '<a href="'.DOL_URL_ROOT.'/societe/commerciaux.php?socid='.$soc->id.'">'.img_edit().'</a>';
        else
        print '&nbsp;';
        print '</td></tr></table>';
        print '</td>';
        print '<td colspan="3">';

        $listsalesrepresentatives=$soc->getSalesRepresentatives($user);
        $nbofsalesrepresentative=sizeof($listsalesrepresentatives);
        if ($nbofsalesrepresentative > 3)   // We print only number
        {
            print '<a href="'.DOL_URL_ROOT.'/societe/commerciaux.php?socid='.$soc->id.'">';
            print $nbofsalesrepresentative;
            print '</a>';
        }
        else if ($nbofsalesrepresentative > 0)
        {
            $userstatic=new User($db);
            $i=0;
            foreach($listsalesrepresentatives as $val)
            {
                $userstatic->id=$val['id'];
                $userstatic->nom=$val['name'];
                $userstatic->prenom=$val['firstname'];
                print $userstatic->getNomUrl(1);
                $i++;
                if ($i < $nbofsalesrepresentative) print ', ';
            }
        }
        else print $langs->trans("NoSalesRepresentativeAffected");
        print '</td></tr>';

        // Module Adherent
        if ($conf->adherent->enabled)
        {
            $langs->load("members");
            print '<tr><td width="25%" valign="top">'.$langs->trans("LinkedToDolibarrMember").'</td>';
            print '<td colspan="3">';
            $adh=new Adherent($db);
            $result=$adh->fetch('','',$soc->id);
            if ($result > 0)
            {
                $adh->ref=$adh->getFullName($langs);
                print $adh->getNomUrl(1);
            }
            else
            {
                print $langs->trans("UserNotLinkedToMember");
            }
            print '</td>';
            print "</tr>\n";
        }

        print '</table>';

        dol_fiche_end();


        /*
         *  Actions
         */
        print '<div class="tabsAction">'."\n";

        if ($user->rights->societe->creer)
        {
            print '<a class="butAction" href="'.$_SERVER["PHP_SELF"].'?socid='.$soc->id.'&amp;action=edit">'.$langs->trans("Modify").'</a>'."\n";
        }

        /*if ($user->rights->societe->contact->creer)
        {
            print '<a class="butAction" href="'.DOL_URL_ROOT.'/contact/fiche.php?socid='.$soc->id.'&amp;action=create">'.$langs->trans("AddContact").'</a>'."\n";
        }
        */

        /*if ($conf->projet->enabled && $user->rights->projet->creer)
        {
            print '<a class="butAction" href="'.DOL_URL_ROOT.'/projet/fiche.php?socid='.$soc->id.'&action=create">'.$langs->trans("AddProject").'</a>'."\n";
        }*/

        if ($user->rights->societe->supprimer)
        {
            if ($conf->use_javascript_ajax)
            {
                print '<span id="action-delete" class="butActionDelete">'.$langs->trans('Delete').'</span>'."\n";
            }
            else
            {
                print '<a class="butActionDelete" href="'.$_SERVER["PHP_SELF"].'?socid='.$soc->id.'&amp;action=delete">'.$langs->trans('Delete').'</a>'."\n";
            }
        }

        print '</div>'."\n";
        print '<br>';

        print '<table width="100%"><tr><td valign="top" width="50%">';
        print '<a name="builddoc"></a>'; // ancre

        /*
         * Documents generes
         */
        $filedir=$conf->societe->dir_output.'/'.$soc->id;
        $urlsource=$_SERVER["PHP_SELF"]."?socid=".$soc->id;
        $genallowed=$user->rights->societe->creer;
        $delallowed=$user->rights->societe->supprimer;

        $var=true;

        $somethingshown=$formfile->show_documents('company',$soc->id,$filedir,$urlsource,$genallowed,$delallowed,'',0,0,0,28,0,'',0,'',$soc->default_lang);

        print '</td>';
        print '<td></td>';
        print '</tr>';
        print '</table>';

        print '<br>';

        // Subsidiaries list
        $result=show_subsidiaries($conf,$langs,$db,$soc);

        // Contacts list
        if (empty($conf->global->SOCIETE_DISABLE_CONTACTS))
        {
            $result=show_contacts($conf,$langs,$db,$soc,$_SERVER["PHP_SELF"].'?socid='.$soc->id);
        }

        // Projects list
        $result=show_projects($conf,$langs,$db,$soc,$_SERVER["PHP_SELF"].'?socid='.$soc->id);
    }

}


$db->close();

llxFooter('$Date$ - $Revision$');
?>