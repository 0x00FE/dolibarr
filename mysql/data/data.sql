-- Copyright (C) 2001-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
-- Copyright (C) 2003      Jean-Louis Bergamo   <jlb@j1b.org>
-- Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
-- Copyright (C) 2004      Benoit Mortier       <benoit.mortier@opensides.be>
-- Copyright (C) 2004      Guillaume Delecourt  <guillaume.delecourt@opensides.be>
-- Copyright (C) 2005      Regis Houssin        <regis.houssin@cap-networks.com>
--
-- This program is free software; you can redistribute it and/or modify
-- it under the terms of the GNU General Public License as published by
-- the Free Software Foundation; either version 2 of the License, or
-- (at your option) any later version.
--
-- This program is distributed in the hope that it will be useful,
-- but WITHOUT ANY WARRANTY; without even the implied warranty of
-- MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
-- GNU General Public License for more details.
--
-- You should have received a copy of the GNU General Public License
-- along with this program; if not, write to the Free Software
-- Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
--
-- $Id$
-- $Source$
--

--
-- Ne pas place de commentaire en fin de ligne, ce fichier est pars� lors
-- de l'install et tous les sigles '--' sont supprim�s.
--


INSERT INTO llx_c_methode_commande_fournisseur (rowid, code, libelle, active) VALUES (1, 'OrderByMail',  'Courrier',  1);
INSERT INTO llx_c_methode_commande_fournisseur (rowid, code, libelle, active) VALUES (2, 'OrderByFax',   'Fax',       1);
INSERT INTO llx_c_methode_commande_fournisseur (rowid, code, libelle, active) VALUES (3, 'OrderByEMail', 'EMail',     1);
INSERT INTO llx_c_methode_commande_fournisseur (rowid, code, libelle, active) VALUES (4, 'OrderByPhone', 'T�l�phone', 1);
INSERT INTO llx_c_methode_commande_fournisseur (rowid, code, libelle, active) VALUES (5, 'OrderByWWW',   'En ligne',  1);


insert into llx_cond_reglement(rowid, code, sortorder, active, libelle, libelle_facture, fdm, nbjour) values (1,'RECEP',       1,1, 'A r�ception','R�ception de facture',0,0);
insert into llx_cond_reglement(rowid, code, sortorder, active, libelle, libelle_facture, fdm, nbjour) values (2,'30D',         2,1, '30 jours','R�glement � 30 jours',0,30);
insert into llx_cond_reglement(rowid, code, sortorder, active, libelle, libelle_facture, fdm, nbjour) values (3,'30DENDMONTH', 3,1, '30 jours fin de mois','R�glement � 30 jours fin de mois',1,30);
insert into llx_cond_reglement(rowid, code, sortorder, active, libelle, libelle_facture, fdm, nbjour) values (4,'60D',         4,1, '60 jours','R�glement � 60 jours',0,60);
insert into llx_cond_reglement(rowid, code, sortorder, active, libelle, libelle_facture, fdm, nbjour) values (5,'60DENDMONTH', 5,1, '60 jours fin de mois','R�glement � 60 jours fin de mois',1,60);
insert into llx_cond_reglement(rowid, code, sortorder, active, libelle, libelle_facture, fdm, nbjour) values (6,'PROFORMA',    6,1, 'Proforma','R�glement avant livraison',0,0);


insert into llx_sqltables (name, loaded) values ('llx_album',0);

--
-- D�finition des actions de workflow notifications
--
delete from llx_action_def;
insert into llx_action_def (rowid,code,titre,description,objet_type) values (1,'NOTIFY_VAL_FICHINTER','Validation fiche intervention','D�clench� lors de la validation d\'une fiche d\'intervention','ficheinter');
insert into llx_action_def (rowid,code,titre,description,objet_type) values (2,'NOTIFY_VAL_FAC','Validation facture','D�clench� lors de la validation d\'une facture','facture');

--
-- Constantes de configuration
--
insert into llx_const (name, value, type, note, visible) values ('MAIN_NOT_INSTALLED','1','chaine','Test d\'installation',1);
insert into llx_const (name, value, type, note, visible) values ('MAIN_UPLOAD_DOC','1','chaine','Autorise l\'upload de documents',0);

insert into llx_const (name, value, type, note, visible) values ('MAIN_MONNAIE','EUR','chaine','Monnaie',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_EMAIL_FROM','dolibarr-robot@domain.com','chaine','EMail emetteur pour les envois automatiques Dolibarr (Notifications, ...)',1);

insert into llx_const (name, value, type, note, visible) values ('COMPTA_ONLINE_PAYMENT_BPLC','1','yesno','Syst�me de gestion de la banque populaire de Lorraine',0);

--
-- IHM
--
insert into llx_const (name, value, type, note, visible) values ('MAIN_LANG_DEFAULT','fr_FR','chaine','Langue par d�faut pour les �crans Dolibarr',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_SIZE_LISTE_LIMIT','25','chaine','Longueur maximum des listes',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_SHOW_WORKBOARD','1','yesno','Affichage tableau de bord de travail Dolibarr',0);

insert into llx_const (name, value, type, note, visible) values ('MAIN_MENU_BARRETOP','default.php','chaine','Module de gestion de la barre de menu du haut pour utilisateurs internes',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_MENUFRONT_BARRETOP','eldy_frontoffice.php','chaine','Module de gestion de la barre de menu du haut pour utilisateurs externes',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_MENU_BARRELEFT','default.php','chaine','Module de gestion de la barre de menu gauche pour utilisateurs internes',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_MENUFRONT_BARRELEFT','eldy_frontoffice.php','chaine','Module de gestion de la barre de menu gauche pour utilisateurs externes',0);

insert into llx_const (name, value, type, note, visible) values ('MAIN_THEME','eldy','chaine','Th�me par d�faut',0);

insert into llx_const (name, value, type, note, visible) values ('MAIN_SEARCHFORM_CONTACT','1','yesno','Affichage formulaire de recherche des Contacts dans la barre de gauche',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_SEARCHFORM_SOCIETE','1','yesno','Affichage formulaire de recherche des Soci�t�s dans la barre de gauche',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_SEARCHFORM_PRODUITSERVICE','1','yesno','Affichage formulaire de recherche des Produits et Services dans la barre de gauche',0);

--
-- Delai tolerance
--

insert into llx_const (name, value, type, note, visible) values ('MAIN_DELAY_ACTIONS_TODO','7','chaine','Tol�rance de retard avant alerte (en jours) sur actions planifi�es non r�alis�es',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_DELAY_ORDERS_TO_PROCESS','2','chaine','Tol�rance de retard avant alerte (en jours) sur commandes non trait�es',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_DELAY_PROPALS_TO_CLOSE','31','chaine','Tol�rance de retard avant alerte (en jours) sur propales � cloturer',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_DELAY_PROPALS_TO_BILL','7','chaine','Tol�rance de retard avant alerte (en jours) sur propales non factur�es',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_DELAY_SUPPLIER_BILLS_TO_PAY','2','chaine','Tol�rance de retard avant alerte (en jours) sur factures fournisseur impay�es',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_DELAY_CUSTOMER_BILLS_UNPAYED','31','chaine','Tol�rance de retard avant alerte (en jours) sur factures client impay�es',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_DELAY_NOT_ACTIVATED_SERVICES','0','chaine','Tol�rance de retard avant alerte (en jours) sur services � activer',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_DELAY_RUNNING_SERVICES','0','chaine','Tol�rance de retard avant alerte (en jours) sur services expir�s',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_DELAY_MEMBERS','31','chaine','Tol�rance de retard avant alerte (en jours) sur cotisations adh�rent en retard',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_DELAY_TRANSACTIONS_TO_CONCILIATE','62','chaine','Tol�rance de retard avant alerte (en jours) sur rapprochements bancaires � faire',0);

--
-- Mail Adherent
--
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_MAIL_REQUIRED','1','yesno','Le mail est obligatoire pour cr�er un adh�rent',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_MAIL_FROM','adherents@domain.com','chaine','From des mails adherents',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_MAIL_RESIL','Votre adhesion sur %SERVEUR% vient d\'etre resilie.\r\nNous esperons vous revoir tres bientot','texte','Mail de Resiliation',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_MAIL_VALID','MAIN\r\nVotre adhesion vient d\'etre validee. \r\nVoici le rappel de vos coordonnees (toute information erronee entrainera la non validation de votre inscription) :\r\n\r\n%INFO%\r\n\r\nVous pouvez a tout moment, grace a votre login et mot de passe, modifier vos coordonnees a l\'adresse suivante : \r\n%SERVEUR%public/adherents/','texte','Mail de validation',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_MAIL_EDIT','Voici le rappel des coordonnees que vous avez modifiees (toute information erronee entrainera la non validation de votre inscription) :\r\n\r\n%INFO%\r\n\r\nVous pouvez a tout moment, grace a votre login et mot de passe, modifier vos coordonnees a l\'adresse suivante :\r\n%SERVEUR%public/adherents/','texte','Mail d\'edition',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_MAIL_NEW','Merci de votre inscription. Votre adhesion devrait etre rapidement validee.\r\nVoici le rappel des coordonnees que vous avez rentrees (toute information erronee entrainera la non validation de votre inscription) :\r\n\r\n%INFO%\r\n\r\nVous pouvez a tout moment, grace a votre login et mot de passe, modifier vos coordonnees a l\'adresse suivante :\r\n%SERVEUR%public/adherents/','texte','Mail de nouvel inscription',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_MAIL_COTIS','Bonjour %PRENOM%,\r\nMerci de votre inscription.\r\nCet email confirme que votre cotisation a ete recue et enregistree.\r\n\r\nVous pouvez a tout moment, grace a votre login et mot de passe, modifier vos coordonnees a l\'adresse suivante :\r\n%SERVEUR%public/adherents/','texte','Mail de validation de cotisation',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_MAIL_VALID_SUBJECT','Votre adh�sion a ete valid�e sur %SERVEUR%','chaine','sujet du mail de validation',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_MAIL_RESIL_SUBJECT','Resiliation de votre adhesion sur %SERVEUR%','chaine','sujet du mail de resiliation',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_MAIL_COTIS_SUBJECT','Recu de votre cotisation','chaine','sujet du mail de validation de cotisation',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_MAIL_NEW_SUBJECT','Bienvenue sur %SERVEUR%','chaine','Sujet du mail de nouvelle adhesion',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_MAIL_EDIT_SUBJECT','Votre fiche a ete editee sur %SERVEUR%','chaine','Sujet du mail d\'edition',0);

--
-- Mail Mailing
--
insert into llx_const (name, value, type, note, visible) values ('MAILING_EMAIL_FROM','dolibarr@domain.com','chaine','EMail emmetteur pour les envois d\'emailings',0);

--
-- Mailman
--
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_USE_MAILMAN','0','yesno','Utilisation de Mailman',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_MAILMAN_UNSUB_URL','http://%SERVER%/cgi-bin/mailman/admin/%LISTE%/members?adminpw=%ADMINPW%&user=%EMAIL%','chaine','Url de desinscription aux listes mailman',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_MAILMAN_URL','http://%SERVER%/cgi-bin/mailman/admin/%LISTE%/members?adminpw=%ADMINPW%&send_welcome_msg_to_this_batch=1&subscribees=%EMAIL%','chaine','url pour les inscriptions mailman',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_MAILMAN_LISTS','test-test,test-test2','chaine','Listes auxquelles inscrire les nouveaux adherents',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_MAILMAN_ADMINPW','','chaine','Mot de passe Admin des liste mailman',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_MAILMAN_SERVER','lists.domain.com','chaine','Serveur hebergeant les interfaces d\'Admin des listes mailman',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_MAILMAN_LISTS_COTISANT','','chaine','Liste(s) auxquelles les nouveaux cotisants sont inscris automatiquement',0);
--
-- Glasnost
--
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_USE_GLASNOST','0','yesno','utilisation de glasnost ?',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_GLASNOST_SERVEUR','glasnost.j1b.org','chaine','serveur glasnost',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_GLASNOST_USER','user','chaine','Administrateur glasnost',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_GLASNOST_PASS','password','chaine','password de l\'administrateur',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_USE_GLASNOST_AUTO','0','yesno','inscription automatique a glasnost ?',0);
--
-- SPIP
--
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_USE_SPIP','0','yesno','Utilisation de SPIP ?',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_USE_SPIP_AUTO','0','yesno','Utilisation de SPIP automatiquement',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_SPIP_USER','user','chaine','user spip',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_SPIP_PASS','pass','chaine','Pass de connection',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_SPIP_SERVEUR','localhost','chaine','serveur spip',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_SPIP_DB','spip','chaine','db spip',0);
--
-- Cartes adherents
--
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_TEXT_NEW_ADH','','texte','Texte d\'entete du formaulaire d\'adhesion en ligne',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_CARD_HEADER_TEXT','%ANNEE%','chaine','Texte imprime sur le haut de la carte adherent',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_CARD_FOOTER_TEXT','Association FreeLUG http://www.freelug.org/','chaine','Texte imprime sur le bas de la carte adherent',0);
insert into llx_const (name, value, type, note, visible) values ('ADHERENT_CARD_TEXT','%TYPE% n� %ID%\r\n%PRENOM% %NOM%\r\n<%EMAIL%>\r\n%ADRESSE%\r\n%CP% %VILLE%\r\n%PAYS%','texte','Texte imprime sur la carte adherent',0);

--
-- OsCommerce
--
insert into llx_const (name, value, type) values ('OSC_DB_NAME','catalog','chaine');
insert into llx_const (name, value, type) values ('OSC_LANGUAGE_ID','1','chaine');
insert into llx_const (name, value, type) values ('OSC_CATALOG_URL','http://osc.lafrere.lan/','chaine');

--
--
--
insert into llx_const (name, value, type, visible) values ('DON_ADDON',           'html_cerfafr','chaine',0);
insert into llx_const (name, value, type, visible) values ('PROPALE_ADDON',       'mod_propale_marbre','chaine',0);
insert into llx_const (name, value, type, visible) values ('PROPALE_ADDON_PDF',   'azur','chaine',0);
insert into llx_const (name, value, type, visible) values ('COMMANDE_ADDON',      'mod_commande_ivoire','chaine',0);
insert into llx_const (name, value, type, visible) values ('COMMANDE_ADDON_PDF',  'einstein','chaine',0);
insert into llx_const (name, value, type, visible) values ('COMMANDE_SUPPLIER_ADDON',      'mod_commande_fournisseur_muguet','chaine',0);
insert into llx_const (name, value, type, visible) values ('COMMANDE_SUPPLIER_ADDON_PDF',  'muscadet','chaine',0);
insert into llx_const (name, value, type, visible) values ('EXPEDITION_ADDON',    'enlevement','chaine',0);
insert into llx_const (name, value, type, visible) values ('EXPEDITION_ADDON_PDF','rouget','chaine',0);
insert into llx_const (name, value, type, visible) values ('FICHEINTER_ADDON_PDF','soleil','chaine',0);
insert into llx_const (name, value, type, visible) values ('FACTURE_ADDON',       'terre','chaine',0);
insert into llx_const (name, value, type, visible) values ('FACTURE_ADDON_PDF',   'crabe','chaine',0);


--
-- Forcer les locales
--
insert into llx_const (name, value, type, visible, note) VALUES ('MAIN_FORCE_SETLOCALE_LC_ALL',      'MAIN_FORCE_SETLOCALE_LC_ALL', 'chaine', 1, 'Pour forcer LC_ALL si pb de locale');
insert into llx_const (name, value, type, visible, note) VALUES ('MAIN_FORCE_SETLOCALE_LC_TIME',     'MAIN_FORCE_SETLOCALE_LC_TIME', 'chaine', 1, 'Pour forcer LC_TIME si pb de locale');
insert into llx_const (name, value, type, visible, note) VALUES ('MAIN_FORCE_SETLOCALE_LC_MONETARY', 'MAIN_FORCE_SETLOCALE_LC_MONETARY', 'chaine', 1, 'Pour forcer LC_MONETARY si pb de locale');
insert into llx_const (name, value, type, visible, note) VALUES ('MAIN_FORCE_SETLOCALE_LC_NUMERIC',  'MAIN_FORCE_SETLOCALE_LC_NUMERIC', 'chaine', 1, 'Mettre la valeur C si probl�me de centimes');


--
-- Descriptif des plans comptables FR PCG99-ABREGE
--

delete from llx_accountingsystem_det;
delete from llx_accountingsystem;

insert into llx_accountingsystem (pcg_version, fk_pays, label, datec, fk_author, active) VALUES ('PCG99-ABREGE', 1, 'Plan de compte standard fran�ais abr�g�', sysdate(), null, 0);

insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES (  1,'PCG99-ABREGE','CAPIT', 'CAPITAL', '101', '1', 'Capital');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES (  2,'PCG99-ABREGE','CAPIT', 'XXXXXX',  '105', '1', 'Ecarts de r��valuation');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES (  3,'PCG99-ABREGE','CAPIT', 'XXXXXX', '1061', '1', 'R�serve l�gale');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES (  4,'PCG99-ABREGE','CAPIT', 'XXXXXX', '1063', '1', 'R�serves statutaires ou contractuelles');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES (  5,'PCG99-ABREGE','CAPIT', 'XXXXXX', '1064', '1', 'R�serves r�glement�es');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES (  6,'PCG99-ABREGE','CAPIT', 'XXXXXX', '1068', '1', 'Autres r�serves');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES (  7,'PCG99-ABREGE','CAPIT', 'XXXXXX',  '108', '1', 'Compte de l''exploitant');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES (  8,'PCG99-ABREGE','CAPIT', 'XXXXXX',   '12', '1', 'R�sultat de l''exercice');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES (  9,'PCG99-ABREGE','CAPIT', 'XXXXXX',  '145', '1', 'Amortissements d�rogatoires');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 10,'PCG99-ABREGE','CAPIT', 'XXXXXX',  '146', '1', 'Provision sp�ciale de r��valuation');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 11,'PCG99-ABREGE','CAPIT', 'XXXXXX',  '147', '1', 'Plus-values r�investies');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 12,'PCG99-ABREGE','CAPIT', 'XXXXXX',  '148', '1', 'Autres provisions r�glement�es');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 13,'PCG99-ABREGE','CAPIT', 'XXXXXX',   '15', '1', 'Provisions pour risques et charges');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 14,'PCG99-ABREGE','CAPIT', 'XXXXXX',   '16', '1', 'Emprunts et dettes assimilees');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 15,'PCG99-ABREGE','IMMO',  'XXXXXX',   '20', '2', 'Immobilisations incorporelles');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 16,'PCG99-ABREGE','IMMO',  'XXXXXX',  '201','20', 'Frais d''�tablissement');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 17,'PCG99-ABREGE','IMMO',  'XXXXXX',  '206','20', 'Droit au bail');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 18,'PCG99-ABREGE','IMMO',  'XXXXXX',  '207','20', 'Fonds commercial');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 19,'PCG99-ABREGE','IMMO',  'XXXXXX',  '208','20', 'Autres immobilisations incorporelles');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 20,'PCG99-ABREGE','IMMO',  'XXXXXX',   '21', '2', 'Immobilisations corporelles');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 21,'PCG99-ABREGE','IMMO',  'XXXXXX',   '23', '2', 'Immobilisations en cours');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 22,'PCG99-ABREGE','IMMO',  'XXXXXX',   '27', '2', 'Autres immobilisations financieres');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 23,'PCG99-ABREGE','IMMO',  'XXXXXX',  '280', '2', 'Amortissements des immobilisations incorporelles');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 24,'PCG99-ABREGE','IMMO',  'XXXXXX',  '281', '2', 'Amortissements des immobilisations corporelles');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 25,'PCG99-ABREGE','IMMO',  'XXXXXX',  '290', '2', 'Provisions pour d�pr�ciation des immobilisations incorporelles');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 26,'PCG99-ABREGE','IMMO',  'XXXXXX',  '291', '2', 'Provisions pour d�pr�ciation des immobilisations corporelles');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 27,'PCG99-ABREGE','IMMO',  'XXXXXX',  '297', '2', 'Provisions pour d�pr�ciation des autres immobilisations financi�res');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 28,'PCG99-ABREGE','STOCK', 'XXXXXX',   '31', '3', 'Matieres premi�res');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 29,'PCG99-ABREGE','STOCK', 'XXXXXX',   '32', '3', 'Autres approvisionnements');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 30,'PCG99-ABREGE','STOCK', 'XXXXXX',   '33', '3', 'En-cours de production de biens');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 31,'PCG99-ABREGE','STOCK', 'XXXXXX',   '34', '3', 'En-cours de production de services');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 32,'PCG99-ABREGE','STOCK', 'XXXXXX',   '35', '3', 'Stocks de produits');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 33,'PCG99-ABREGE','STOCK', 'XXXXXX',   '37', '3', 'Stocks de marchandises');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 34,'PCG99-ABREGE','STOCK', 'XXXXXX',  '391', '3', 'Provisions pour d�pr�ciation des mati�res premi�res');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 35,'PCG99-ABREGE','STOCK', 'XXXXXX',  '392', '3', 'Provisions pour d�pr�ciation des autres approvisionnements');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 36,'PCG99-ABREGE','STOCK', 'XXXXXX',  '393', '3', 'Provisions pour d�pr�ciation des en-cours de production de biens');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 37,'PCG99-ABREGE','STOCK', 'XXXXXX',  '394', '3', 'Provisions pour d�pr�ciation des en-cours de production de services');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 38,'PCG99-ABREGE','STOCK', 'XXXXXX',  '395', '3', 'Provisions pour d�pr�ciation des stocks de produits');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 39,'PCG99-ABREGE','STOCK', 'XXXXXX',  '397', '3', 'Provisions pour d�pr�ciation des stocks de marchandises');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 40,'PCG99-ABREGE','TIERS', 'SUPPLIER','400', '4', 'Fournisseurs et Comptes rattach�s');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 41,'PCG99-ABREGE','TIERS', 'XXXXXX',  '409', '4', 'Fournisseurs d�biteurs');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 42,'PCG99-ABREGE','TIERS', 'CUSTOMER','410', '4', 'Clients et Comptes rattach�s');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 43,'PCG99-ABREGE','TIERS', 'XXXXXX',  '419', '4', 'Clients cr�diteurs');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 44,'PCG99-ABREGE','TIERS', 'XXXXXX',  '421', '4', 'Personnel');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 45,'PCG99-ABREGE','TIERS', 'XXXXXX',  '428', '4', 'Personnel');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 46,'PCG99-ABREGE','TIERS', 'XXXXXX',   '43', '4', 'S�curit� sociale et autres organismes sociaux');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 47,'PCG99-ABREGE','TIERS', 'XXXXXX',  '444', '4', 'Etat - imp�ts sur b�n�fice');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 48,'PCG99-ABREGE','TIERS', 'XXXXXX',  '445', '4', 'Etat - Taxes sur chiffre affaire');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 49,'PCG99-ABREGE','TIERS', 'XXXXXX',  '447', '4', 'Autres imp�ts, taxes et versements assimil�s');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 50,'PCG99-ABREGE','TIERS', 'XXXXXX',   '45', '4', 'Groupe et associes');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 51,'PCG99-ABREGE','TIERS', 'XXXXXX',  '455','45', 'Associ�s');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 52,'PCG99-ABREGE','TIERS', 'XXXXXX',   '46', '4', 'D�biteurs divers et cr�diteurs divers');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 53,'PCG99-ABREGE','TIERS', 'XXXXXX',   '47', '4', 'Comptes transitoires ou d''attente');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 54,'PCG99-ABREGE','TIERS', 'XXXXXX',  '481', '4', 'Charges � r�partir sur plusieurs exercices');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 55,'PCG99-ABREGE','TIERS', 'XXXXXX',  '486', '4', 'Charges constat�es d''avance');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 56,'PCG99-ABREGE','TIERS', 'XXXXXX',  '487', '4', 'Produits constat�s d''avance');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 57,'PCG99-ABREGE','TIERS', 'XXXXXX',  '491', '4', 'Provisions pour d�pr�ciation des comptes de clients');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 58,'PCG99-ABREGE','TIERS', 'XXXXXX',  '496', '4', 'Provisions pour d�pr�ciation des comptes de d�biteurs divers');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 59,'PCG99-ABREGE','FINAN', 'XXXXXX',   '50', '5', 'Valeurs mobili�res de placement');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 60,'PCG99-ABREGE','FINAN', 'BANK',     '51', '5', 'Banques, �tablissements financiers et assimil�s');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 61,'PCG99-ABREGE','FINAN', 'CASH',     '53', '5', 'Caisse');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 62,'PCG99-ABREGE','FINAN', 'XXXXXX',   '54', '5', 'R�gies d''avance et accr�ditifs');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 63,'PCG99-ABREGE','FINAN', 'XXXXXX',   '58', '5', 'Virements internes');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 64,'PCG99-ABREGE','FINAN', 'XXXXXX',  '590', '5', 'Provisions pour d�pr�ciation des valeurs mobili�res de placement');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 65,'PCG99-ABREGE','CHARGE','PRODUCT',  '60', '6', 'Achats');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 66,'PCG99-ABREGE','CHARGE','XXXXXX',  '603','60', 'Variations des stocks');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 67,'PCG99-ABREGE','CHARGE','SERVICE',  '61', '6', 'Services ext�rieurs');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 68,'PCG99-ABREGE','CHARGE','XXXXXX',   '62', '6', 'Autres services ext�rieurs');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 69,'PCG99-ABREGE','CHARGE','XXXXXX',   '63', '6', 'Imp�ts, taxes et versements assimiles');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 70,'PCG99-ABREGE','CHARGE','XXXXXX',  '641', '6', 'R�mun�rations du personnel');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 71,'PCG99-ABREGE','CHARGE','XXXXXX',  '644', '6', 'R�mun�ration du travail de l''exploitant');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 72,'PCG99-ABREGE','CHARGE','SOCIAL',  '645', '6', 'Charges de s�curit� sociale et de pr�voyance');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 73,'PCG99-ABREGE','CHARGE','XXXXXX',  '646', '6', 'Cotisations sociales personnelles de l''exploitant');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 74,'PCG99-ABREGE','CHARGE','XXXXXX',   '65', '6', 'Autres charges de gestion courante');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 75,'PCG99-ABREGE','CHARGE','XXXXXX',   '66', '6', 'Charges financi�res');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 76,'PCG99-ABREGE','CHARGE','XXXXXX',   '67', '6', 'Charges exceptionnelles');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 77,'PCG99-ABREGE','CHARGE','XXXXXX',  '681', '6', 'Dotations aux amortissements et aux provisions');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 78,'PCG99-ABREGE','CHARGE','XXXXXX',  '686', '6', 'Dotations aux amortissements et aux provisions');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 79,'PCG99-ABREGE','CHARGE','XXXXXX',  '687', '6', 'Dotations aux amortissements et aux provisions');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 80,'PCG99-ABREGE','CHARGE','XXXXXX',  '691', '6', 'Participation des salari�s aux r�sultats');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 81,'PCG99-ABREGE','CHARGE','XXXXXX',  '695', '6', 'Imp�ts sur les b�n�fices');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 82,'PCG99-ABREGE','CHARGE','XXXXXX',  '697', '6', 'Imposition forfaitaire annuelle des soci�t�s');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 83,'PCG99-ABREGE','CHARGE','XXXXXX',  '699', '6', 'Produits');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 84,'PCG99-ABREGE','PROD',  'PRODUCT', '701', '7', 'Ventes de produits finis');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 85,'PCG99-ABREGE','PROD',  'SERVICE', '706', '7', 'Prestations de services');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 86,'PCG99-ABREGE','PROD',  'PRODUCT', '707', '7', 'Ventes de marchandises');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 87,'PCG99-ABREGE','PROD',  'PRODUCT', '708', '7', 'Produits des activit�s annexes');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 88,'PCG99-ABREGE','PROD',  'XXXXXX',  '709', '7', 'Rabais, remises et ristournes accord�s par l''entreprise');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 89,'PCG99-ABREGE','PROD',  'XXXXXX',  '713', '7', 'Variation des stocks');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 90,'PCG99-ABREGE','PROD',  'XXXXXX',   '72', '7', 'Production immobilis�e');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 91,'PCG99-ABREGE','PROD',  'XXXXXX',   '73', '7', 'Produits nets partiels sur op�rations � long terme');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 92,'PCG99-ABREGE','PROD',  'XXXXXX',   '74', '7', 'Subventions d''exploitation');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 93,'PCG99-ABREGE','PROD',  'XXXXXX',   '75', '7', 'Autres produits de gestion courante');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 94,'PCG99-ABREGE','PROD',  'XXXXXX',  '753','75', 'Jetons de pr�sence et r�mun�rations d''administrateurs, g�rants,...');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 95,'PCG99-ABREGE','PROD',  'XXXXXX',  '754','75', 'Ristournes per�ues des coop�ratives');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 96,'PCG99-ABREGE','PROD',  'XXXXXX',  '755','75', 'Quotes-parts de r�sultat sur op�rations faites en commun');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 97,'PCG99-ABREGE','PROD',  'XXXXXX',   '76', '7', 'Produits financiers');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 98,'PCG99-ABREGE','PROD',  'XXXXXX',   '77', '7', 'Produits exceptionnels');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES ( 99,'PCG99-ABREGE','PROD',  'XXXXXX',  '781', '7', 'Reprises sur amortissements et provisions');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES (100,'PCG99-ABREGE','PROD',  'XXXXXX',  '786', '7', 'Reprises sur provisions pour risques');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES (101,'PCG99-ABREGE','PROD',  'XXXXXX',  '787', '7', 'Reprises sur provisions');
insert into llx_accountingsystem_det (rowid, fk_pcg_version, pcg_type, pcg_subtype, account_number, account_parent, label) VALUES (102,'PCG99-ABREGE','PROD',  'XXXXXX',   '79', '7', 'Transferts de charges');



-- Dictionnaires llx_c

--
-- Types action comm
--

delete from llx_c_actioncomm;
insert into llx_c_actioncomm (id, code, type, libelle) values ( 1, 'AC_TEL',  'system', 'Appel T�l�phonique');
insert into llx_c_actioncomm (id, code, type, libelle) values ( 2, 'AC_FAX',  'system', 'Envoi Fax');
insert into llx_c_actioncomm (id, code, type, libelle) values ( 3, 'AC_PROP', 'system', 'Envoi Proposition');
insert into llx_c_actioncomm (id, code, type, libelle) values ( 4, 'AC_EMAIL','system', 'Envoi Email');
insert into llx_c_actioncomm (id, code, type, libelle) values ( 5, 'AC_RDV',  'system', 'Prendre rendez-vous');
insert into llx_c_actioncomm (id, code, type, libelle) values ( 9, 'AC_FAC',  'system', 'Envoi Facture');
insert into llx_c_actioncomm (id, code, type, libelle) values (10, 'AC_REL',  'system', 'Relance effectu�e');
insert into llx_c_actioncomm (id, code, type, libelle) values (11, 'AC_CLO',  'system', 'Cl�ture');

--
-- Ape
--
delete from llx_c_ape;


--
-- Types de charges
--

insert into llx_c_chargesociales (id,libelle,deductible) values ( 1, 'Allocations familiales',1);
insert into llx_c_chargesociales (id,libelle,deductible) values ( 2, 'GSG Deductible',1);
insert into llx_c_chargesociales (id,libelle,deductible) values ( 3, 'GSG/CRDS NON Deductible',0);

--
-- Civilites
--

delete from llx_c_civilite;
insert into llx_c_civilite (rowid, code, civilite, active) values (1 , 'MME',  'Madame', 1);
insert into llx_c_civilite (rowid, code, civilite, active) values (3 , 'MR',   'Monsieur', 1);
insert into llx_c_civilite (rowid, code, civilite, active) values (5 , 'MLE',  'Mademoiselle', 1);
insert into llx_c_civilite (rowid, code, civilite, active) values (7 , 'MTRE', 'Ma�tre', 1);


--
-- Types effectifs
--

delete from llx_c_effectif;
insert into llx_c_effectif (id,code,libelle) values (0, 'EF0',       '-');
insert into llx_c_effectif (id,code,libelle) values (1, 'EF1-5',     '1 - 5');
insert into llx_c_effectif (id,code,libelle) values (2, 'EF6-10',    '6 - 10');
insert into llx_c_effectif (id,code,libelle) values (3, 'EF11-50',   '11 - 50');
insert into llx_c_effectif (id,code,libelle) values (4, 'EF51-100',  '51 - 100');
insert into llx_c_effectif (id,code,libelle) values (5, 'EF100-500', '100 - 500');
insert into llx_c_effectif (id,code,libelle) values (6, 'EF500-',    '> 500');

--
-- Formes juridiques
--

delete from llx_c_forme_juridique;

insert into llx_c_forme_juridique (fk_pays, code, libelle) values (0, '0','-');

-- Pour la France: Extrait de http://www.insee.fr/fr/nom_def_met/nomenclatures/cj/cjniveau2.htm
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'11','Artisan Commer�ant');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'12','Commer�ant');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'13','Artisan');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'14','Officier public ou minist�riel');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'15','Profession lib�rale');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'16','Exploitant agricole');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'17','Agent commercial');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'18','Associ� G�rant de soci�t�');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'19','(Autre) personne physique');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'21','Indivision');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'22','Soci�t� cr��e de fait');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'23','Soci�t� en participation');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'27','Paroisse hors zone concordataire');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'29','Autre groupement de droit priv� non dot� de la personnalit� morale');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'31','Personne morale de droit �tranger, immatricul�e au RCS');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'32','Personne morale de droit �tranger, non immatricul�e au RCS');
                                                                     
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'41','�tablissement public ou r�gie � caract�re industriel ou commercial');
                                                                     
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'51','Soci�t� coop�rative commerciale particuli�re');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'52','Soci�t� en nom collectif');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'53','Soci�t� en commandite');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'54','Soci�t� � responsabilit� limit�e (SARL)');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'55','Soci�t� anonyme � conseil d\'administration');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'56','Soci�t� anonyme � directoire');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'57','Soci�t� par actions simplifi�e');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'58','Entreprise Unipersonnelle � Responsabilit� Limit�e (EURL)');
                                                                     
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'61','Caisse d\'�pargne et de pr�voyance');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'62','Groupement d\'int�r�t �conomique');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'63','Soci�t� coop�rative agricole');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'64','Soci�t� non commerciale d\'assurances');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'65','Soci�t� civile');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'69','Autres personnes de droit priv� inscrites au registre du commerce et soci�t�s');
                                                                     
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'71','Administration de l\'�tat');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'72','Collectivit� territoriale');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'73','�tablissement public administratif');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'74','Autre personne morale de droit public administratif');
                                                                     
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'81','Organisme g�rant un r�gime de protection social � adh�sion obligatoire');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'82','Organisme mutualiste');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'83','Comit� d\'entreprise');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'84','Organisme professionnel');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'85','Organisme de retraite � adh�sion non obligatoire');
                                                                     
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'91','Syndicat de propri�taires');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'92','Association loi 1901 ou assimil�');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'93','Fondation');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'99','Autre personne morale de droit priv�');

--
-- Pour la Belgique
--

insert into llx_c_forme_juridique (fk_pays, code, libelle) values (2, '200', 'Ind�pendant');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (2, '201', 'SPRL - Soci�t� � responsabilit� limit�e');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (2, '202', 'SA   - Soci�t� Anonyme');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (2, '203', 'SCRL - Soci�t� coop�rative � responsabilit� limit�e');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (2, '204', 'ASBL - Association sans but Lucratif');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (2, '205', 'SCRI - Soci�t� coop�rative � responsabilit� illimit�e');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (2, '206', 'SCS  - Soci�t� en commandite simple');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (2, '207', 'SCA  - Soci�t� en commandite par action');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (2, '208', 'SNC  - Soci�t� en nom collectif');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (2, '209', 'GIE  - Groupement d\'int�r�t �conomique');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (2, '210', 'GEIE - Groupement europ�en d\'int�r�t �conomique');

--
-- Pour la Suisse
--

insert into llx_c_forme_juridique (fk_pays, code, libelle) values (6, '600', 'Raison Individuelle');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (6, '601', 'Soci�t� Simple');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (6, '602', 'Soci�t� en nom collectif');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (6, '603', 'Soci�t� en commandite');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (6, '604', 'Soci�t� anonyme (SA)');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (6, '605', 'Soci�t� en commandite par actions');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (6, '606', 'Soci�t� � responsabilit� limit�e (SARL)');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (6, '607', 'Soci�t� coop�rative');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (6, '608', 'Association');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (6, '609', 'Fondation');

--
-- Pour le Royaume Uni
--

insert into llx_c_forme_juridique (fk_pays, code, libelle) values (7, '700', 'Sole Trader');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (7, '701', 'Partnership');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (7, '702', 'Private Limited Company by shares - (LTD)');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (7, '703', 'Public Limited Company');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (7, '704', 'Workers Cooperative');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (7, '705', 'Limited Liability Partnership');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (7, '706', 'Franchise');

--
-- Types paiement
--

delete from llx_c_paiement;
insert into llx_c_paiement (id,code,libelle,type,active) values (0, '',    '-',                 3,1);
insert into llx_c_paiement (id,code,libelle,type,active) values (1, 'TIP', 'TIP',               2,1);
insert into llx_c_paiement (id,code,libelle,type,active) values (2, 'VIR', 'Virement',          2,1);
insert into llx_c_paiement (id,code,libelle,type,active) values (3, 'PRE', 'Pr�l�vement',       2,1);
insert into llx_c_paiement (id,code,libelle,type,active) values (4, 'LIQ', 'Esp�ces',           2,1);
insert into llx_c_paiement (id,code,libelle,type,active) values (5, 'VAD', 'Paiement en ligne', 2,0);
insert into llx_c_paiement (id,code,libelle,type,active) values (6, 'CB',  'Carte Bancaire',    2,1);
insert into llx_c_paiement (id,code,libelle,type,active) values (7, 'CHQ', 'Ch�que',            2,1);
insert into llx_c_paiement (id,code,libelle,type,active) values (8, 'TRA', 'Traite',            2,1);
insert into llx_c_paiement (id,code,libelle,type,active) values (9, 'LCR', 'LCR',               2,1);
insert into llx_c_paiement (id,code,libelle,type,active) values (10,'FAC', 'Factor',            2,1);
insert into llx_c_paiement (id,code,libelle,type,active) values (11,'PRO', 'Proforma',          2,1);

--
-- Pays
--

delete from llx_c_pays;
insert into llx_c_pays (rowid,code,libelle) values (0,  ''  , '-'              );
insert into llx_c_pays (rowid,code,libelle) values (1,  'FR', 'France'         );
insert into llx_c_pays (rowid,code,libelle) values (2,  'BE', 'Belgique'       );
insert into llx_c_pays (rowid,code,libelle) values (3,  'IT', 'Italie'         );
insert into llx_c_pays (rowid,code,libelle) values (4,  'ES', 'Espagne'        );
insert into llx_c_pays (rowid,code,libelle) values (5,  'DE', 'Allemagne'      );
insert into llx_c_pays (rowid,code,libelle) values (6,  'CH', 'Suisse'         );
insert into llx_c_pays (rowid,code,libelle) values (7,  'GB', 'Royaume uni'    );
insert into llx_c_pays (rowid,code,libelle) values (8,  'IE', 'Irlande'        );
insert into llx_c_pays (rowid,code,libelle) values (9,  'CN', 'Chine'          );
insert into llx_c_pays (rowid,code,libelle) values (10, 'TN', 'Tunisie'        );
insert into llx_c_pays (rowid,code,libelle) values (11, 'US', 'Etats Unis'     );
insert into llx_c_pays (rowid,code,libelle) values (12, 'MA', 'Maroc'          );
insert into llx_c_pays (rowid,code,libelle) values (13, 'DZ', 'Alg�rie'        );
insert into llx_c_pays (rowid,code,libelle) values (14, 'CA', 'Canada'         );
insert into llx_c_pays (rowid,code,libelle) values (15, 'TG', 'Togo'           );
insert into llx_c_pays (rowid,code,libelle) values (16, 'GA', 'Gabon'          );
insert into llx_c_pays (rowid,code,libelle) values (17, 'NL', 'Pays Bas'       );
insert into llx_c_pays (rowid,code,libelle) values (18, 'HU', 'Hongrie'        );
insert into llx_c_pays (rowid,code,libelle) values (19, 'RU', 'Russie'         );
insert into llx_c_pays (rowid,code,libelle) values (20, 'SE', 'Su�de'          );
insert into llx_c_pays (rowid,code,libelle) values (21, 'CI', 'C�te d\'Ivoire' );
insert into llx_c_pays (rowid,code,libelle) values (23, 'SN', 'S�n�gal'        );
insert into llx_c_pays (rowid,code,libelle) values (24, 'AR', 'Argentine'      );
insert into llx_c_pays (rowid,code,libelle) values (25, 'CM', 'Cameroun'       );
insert into llx_c_pays (rowid,code,libelle) values (26, 'PT', 'Portugal'       );
insert into llx_c_pays (rowid,code,libelle) values (27, 'SA', 'Arabie Saoudite');
insert into llx_c_pays (rowid,code,libelle) values (28, 'MC', 'Monaco'         );
insert into llx_c_pays (rowid,code,libelle) values (29, 'AU', 'Australie'      );
insert into llx_c_pays (rowid,code,libelle) values (30, 'SG', 'Singapoure'     );


--
-- Regions
--

insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (0,0,0,'0',0,'-');
-- Regions de France (id pays=1)
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 101, 1,   1,'97105',3,'Guadeloupe');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 102, 1,   2,'97209',3,'Martinique');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 103, 1,   3,'97302',3,'Guyane');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 104, 1,   4,'97411',3,'R�union');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 105, 1,  11,'75056',1,'�le-de-France');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 106, 1,  21,'51108',0,'Champagne-Ardenne');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 107, 1,  22,'80021',0,'Picardie');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 108, 1,  23,'76540',0,'Haute-Normandie');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 109, 1,  24,'45234',2,'Centre');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 110, 1,  25,'14118',0,'Basse-Normandie');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 111, 1,  26,'21231',0,'Bourgogne');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 112, 1,  31,'59350',2,'Nord-Pas-de-Calais');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 113, 1,  41,'57463',0,'Lorraine');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 114, 1,  42,'67482',1,'Alsace');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 115, 1,  43,'25056',0,'Franche-Comt�');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 116, 1,  52,'44109',4,'Pays de la Loire');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 117, 1,  53,'35238',0,'Bretagne');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 118, 1,  54,'86194',2,'Poitou-Charentes');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 119, 1,  72,'33063',1,'Aquitaine');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 120, 1,  73,'31555',0,'Midi-Pyr�n�es');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 121, 1,  74,'87085',2,'Limousin');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 122, 1,  82,'69123',2,'Rh�ne-Alpes');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 123, 1,  83,'63113',1,'Auvergne');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 124, 1,  91,'34172',2,'Languedoc-Roussillon');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 125, 1,  93,'13055',0,'Provence-Alpes-C�te d\'Azur');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 126, 1,  94,'2A004',0,'Corse');

-- Regions de Belgique (id pays=2)
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 201, 2, 201,     '',1,'Flandre');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 202, 2, 202,     '',2,'Wallonie');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values ( 203, 2, 203,     '',3,'Bruxelles-Capitale');

-- Regions d'Australie (id pays=29)
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (2901,29,2901,     '',0,'Australia');


--
-- Departements/Cantons/Provinces
--

insert into llx_c_departements (rowid, fk_region, code_departement,cheflieu,tncc,ncc,nom) values (0,0,'0','0',0,'-','-');
-- Departements de France
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (82,'01','01053',5,'AIN','Ain');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (22,'02','02408',5,'AISNE','Aisne');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (83,'03','03190',5,'ALLIER','Allier');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (93,'04','04070',4,'ALPES-DE-HAUTE-PROVENCE','Alpes-de-Haute-Provence');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (93,'05','05061',4,'HAUTES-ALPES','Hautes-Alpes');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (93,'06','06088',4,'ALPES-MARITIMES','Alpes-Maritimes');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (82,'07','07186',5,'ARDECHE','Ard�che');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (21,'08','08105',4,'ARDENNES','Ardennes');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (73,'09','09122',5,'ARIEGE','Ari�ge');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (21,'10','10387',5,'AUBE','Aube');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (91,'11','11069',5,'AUDE','Aude');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (73,'12','12202',5,'AVEYRON','Aveyron');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (93,'13','13055',4,'BOUCHES-DU-RHONE','Bouches-du-Rh�ne');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (25,'14','14118',2,'CALVADOS','Calvados');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (83,'15','15014',2,'CANTAL','Cantal');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (54,'16','16015',3,'CHARENTE','Charente');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (54,'17','17300',3,'CHARENTE-MARITIME','Charente-Maritime');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (24,'18','18033',2,'CHER','Cher');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (74,'19','19272',3,'CORREZE','Corr�ze');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (94,'2A','2A004',3,'CORSE-DU-SUD','Corse-du-Sud');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (94,'2B','2B033',3,'HAUTE-CORSE','Haute-Corse');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (26,'21','21231',3,'COTE-D\'OR','C�te-d\'Or');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (53,'22','22278',4,'COTES-D\'ARMOR','C�tes-d\'Armor');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (74,'23','23096',3,'CREUSE','Creuse');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (72,'24','24322',3,'DORDOGNE','Dordogne');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (43,'25','25056',2,'DOUBS','Doubs');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (82,'26','26362',3,'DROME','Dr�me');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (23,'27','27229',5,'EURE','Eure');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (24,'28','28085',1,'EURE-ET-LOIR','Eure-et-Loir');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (53,'29','29232',2,'FINISTERE','Finist�re');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (91,'30','30189',2,'GARD','Gard');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (73,'31','31555',3,'HAUTE-GARONNE','Haute-Garonne');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (73,'32','32013',2,'GERS','Gers');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (72,'33','33063',3,'GIRONDE','Gironde');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (91,'34','34172',5,'HERAULT','H�rault');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (53,'35','35238',1,'ILLE-ET-VILAINE','Ille-et-Vilaine');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (24,'36','36044',5,'INDRE','Indre');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (24,'37','37261',1,'INDRE-ET-LOIRE','Indre-et-Loire');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (82,'38','38185',5,'ISERE','Is�re');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (43,'39','39300',2,'JURA','Jura');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (72,'40','40192',4,'LANDES','Landes');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (24,'41','41018',0,'LOIR-ET-CHER','Loir-et-Cher');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (82,'42','42218',3,'LOIRE','Loire');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (83,'43','43157',3,'HAUTE-LOIRE','Haute-Loire');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (52,'44','44109',3,'LOIRE-ATLANTIQUE','Loire-Atlantique');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (24,'45','45234',2,'LOIRET','Loiret');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (73,'46','46042',2,'LOT','Lot');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (72,'47','47001',0,'LOT-ET-GARONNE','Lot-et-Garonne');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (91,'48','48095',3,'LOZERE','Loz�re');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (52,'49','49007',0,'MAINE-ET-LOIRE','Maine-et-Loire');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (25,'50','50502',3,'MANCHE','Manche');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (21,'51','51108',3,'MARNE','Marne');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (21,'52','52121',3,'HAUTE-MARNE','Haute-Marne');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (52,'53','53130',3,'MAYENNE','Mayenne');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (41,'54','54395',0,'MEURTHE-ET-MOSELLE','Meurthe-et-Moselle');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (41,'55','55029',3,'MEUSE','Meuse');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (53,'56','56260',2,'MORBIHAN','Morbihan');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (41,'57','57463',3,'MOSELLE','Moselle');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (26,'58','58194',3,'NIEVRE','Ni�vre');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (31,'59','59350',2,'NORD','Nord');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (22,'60','60057',5,'OISE','Oise');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (25,'61','61001',5,'ORNE','Orne');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (31,'62','62041',2,'PAS-DE-CALAIS','Pas-de-Calais');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (83,'63','63113',2,'PUY-DE-DOME','Puy-de-D�me');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (72,'64','64445',4,'PYRENEES-ATLANTIQUES','Pyr�n�es-Atlantiques');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (73,'65','65440',4,'HAUTES-PYRENEES','Hautes-Pyr�n�es');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (91,'66','66136',4,'PYRENEES-ORIENTALES','Pyr�n�es-Orientales');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (42,'67','67482',2,'BAS-RHIN','Bas-Rhin');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (42,'68','68066',2,'HAUT-RHIN','Haut-Rhin');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (82,'69','69123',2,'RHONE','Rh�ne');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (43,'70','70550',3,'HAUTE-SAONE','Haute-Sa�ne');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (26,'71','71270',0,'SAONE-ET-LOIRE','Sa�ne-et-Loire');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (52,'72','72181',3,'SARTHE','Sarthe');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (82,'73','73065',3,'SAVOIE','Savoie');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (82,'74','74010',3,'HAUTE-SAVOIE','Haute-Savoie');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (11,'75','75056',0,'PARIS','Paris');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (23,'76','76540',3,'SEINE-MARITIME','Seine-Maritime');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (11,'77','77288',0,'SEINE-ET-MARNE','Seine-et-Marne');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (11,'78','78646',4,'YVELINES','Yvelines');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (54,'79','79191',4,'DEUX-SEVRES','Deux-S�vres');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (22,'80','80021',3,'SOMME','Somme');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (73,'81','81004',2,'TARN','Tarn');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (73,'82','82121',0,'TARN-ET-GARONNE','Tarn-et-Garonne');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (93,'83','83137',2,'VAR','Var');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (93,'84','84007',0,'VAUCLUSE','Vaucluse');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (52,'85','85191',3,'VENDEE','Vend�e');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (54,'86','86194',3,'VIENNE','Vienne');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (74,'87','87085',3,'HAUTE-VIENNE','Haute-Vienne');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (41,'88','88160',4,'VOSGES','Vosges');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (26,'89','89024',5,'YONNE','Yonne');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (43,'90','90010',0,'TERRITOIRE DE BELFORT','Territoire de Belfort');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (11,'91','91228',5,'ESSONNE','Essonne');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (11,'92','92050',4,'HAUTS-DE-SEINE','Hauts-de-Seine');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (11,'93','93008',3,'SEINE-SAINT-DENIS','Seine-Saint-Denis');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (11,'94','94028',2,'VAL-DE-MARNE','Val-de-Marne');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (11,'95','95500',2,'VAL-D\'OISE','Val-d\'Oise');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values ( 1,'971','97105',3,'GUADELOUPE','Guadeloupe');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values ( 2,'972','97209',3,'MARTINIQUE','Martinique');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values ( 3,'973','97302',3,'GUYANE','Guyane');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values ( 4,'974','97411',3,'REUNION','R�union');

-- Provinces de Belgique
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (201,'01','',1,'ANVERS','Anvers');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (203,'02','',3,'BRUXELLES-CAPITALE','Bruxelles-Capitale');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (202,'03','',2,'BRABANT-WALLON','Brabant-Wallon');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (201,'04','',1,'BRABANT-FLAMAND','Brabant-Flamand');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (201,'05','',1,'FLANDRE-OCCIDENTALE','Flandre-Occidentale');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (201,'06','',1,'FLANDRE-ORIENTALE','Flandre-Orientale');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (202,'07','',2,'HAINAUT','Hainaut');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (201,'08','',2,'LIEGE','Li�ge');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (202,'09','',1,'LIMBOURG','Limbourg');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (202,'10','',2,'LUXEMBOURG','Luxembourg');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (201,'11','',2,'NAMUR','Namur');

-- Provinces Australie
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (2901,'NSW','',1,'','New South Wales');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (2901,'VIC','',1,'','Victoria');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (2901,'QLD','',1,'','Queensland');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (2901, 'SA','',1,'','South Australia');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (2901,'ACT','',1,'','Australia Capital Territory');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (2901,'TAS','',1,'','Tasmania');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (2901, 'WA','',1,'','Western Australia');
insert into llx_c_departements (fk_region, code_departement,cheflieu,tncc,ncc,nom) values (2901, 'NT','',1,'','Northern Territory');


--
-- Types etat propales
--

delete from llx_c_propalst;
insert into llx_c_propalst (id,code,label) values (0, 'PR_DRAFT',     'Brouillon');
insert into llx_c_propalst (id,code,label) values (1, 'PR_OPEN',      'Ouverte');
insert into llx_c_propalst (id,code,label) values (2, 'PR_SIGNED',    'Sign�e');
insert into llx_c_propalst (id,code,label) values (3, 'PR_NOTSIGNED', 'Non Sign�e');
insert into llx_c_propalst (id,code,label) values (4, 'PR_FAC',       'Factur�e');

--
-- Types action st
--

delete from llx_c_stcomm;
insert into llx_c_stcomm (id,code,libelle) values (-1, 'ST_NO',    'Ne pas contacter');
insert into llx_c_stcomm (id,code,libelle) values ( 0, 'ST_NEVER', 'Jamais contact�');
insert into llx_c_stcomm (id,code,libelle) values ( 1, 'ST_TODO',  'A contacter');
insert into llx_c_stcomm (id,code,libelle) values ( 2, 'ST_PEND',  'Contact en cours');
insert into llx_c_stcomm (id,code,libelle) values ( 3, 'ST_DONE',  'Contact�e');

--
-- Types entreprises
--

delete from llx_c_typent;
insert into llx_c_typent (id,code,libelle) values (  0, 'TE_UNKNOWN', '-');
insert into llx_c_typent (id,code,libelle) values (  1, 'TE_STARTUP', 'Start-up');
insert into llx_c_typent (id,code,libelle) values (  2, 'TE_GROUP',   'Grand groupe');
insert into llx_c_typent (id,code,libelle) values (  3, 'TE_MEDIUM',  'PME/PMI');
insert into llx_c_typent (id,code,libelle) values (  4, 'TE_SMALL',   'TPE');
insert into llx_c_typent (id,code,libelle) values (  5, 'TE_ADMIN',   'Administration');
insert into llx_c_typent (id,code,libelle) values (  6, 'TE_WHOLE',   'Grossiste');
insert into llx_c_typent (id,code,libelle) values (  7, 'TE_RETAIL',  'Revendeur');
insert into llx_c_typent (id,code,libelle) values (  8, 'TE_PRIVATE', 'Particulier');
insert into llx_c_typent (id,code,libelle) values (100, 'TE_OTHER',   'Autres');


--
-- Devises (code secondaire - code ISO4217 - libelle fr)
--

insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'BT', 'THB', 1, 'Bath thailandais'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'CD', 'DKK', 1, 'Couronnes dannoises'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'CN', 'NOK', 1, 'Couronnes norvegiennes'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'CS', 'SEK', 1, 'Couronnes suedoises'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'CZ', 'CZK', 1, 'Couronnes tcheques'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'DA', 'AUD', 1, 'Dollars australiens'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'DC', 'CAD', 1, 'Dollars canadiens'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'DH', 'HKD', 1, 'Dollars hong kong'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'DS', 'SGD', 1, 'Dollars singapour'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'DU', 'USD', 1, 'Dollars us'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'EC', 'XEU', 1, 'Ecus'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'ES', 'PTE', 0, 'Escudos'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'FB', 'BEF', 0, 'Francs belges'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'FF', 'FRF', 0, 'Francs francais'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'FL', 'LUF', 0, 'Francs luxembourgeois'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'FO', 'NLG', 1, 'Florins'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'FS', 'CHF', 1, 'Francs suisses'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'LI', 'IEP', 1, 'Livres irlandaises'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'LR', 'ITL', 0, 'Lires'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'LS', 'GBP', 1, 'Livres sterling'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'MA', 'DEM', 0, 'Deutsch mark'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'MF', 'FIM', 1, 'Mark finlandais'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'PA', 'ARP', 1, 'Pesos argentins'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'PC', 'CLP', 1, 'Pesos chilien'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'PE', 'ESP', 1, 'Pesete'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'PL', 'PLN', 1, 'Zlotys polonais'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'SA', 'ATS', 1, 'Shiliing autrichiens'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'TW', 'TWD', 1, 'Dollar taiwanais'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'YE', 'JPY', 1, 'Yens'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'ZA', 'ZAR', 1, 'Rand africa'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'DR', 'GRD', 1, 'Drachme (grece)'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'EU', 'EUR', 1, 'Euros'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'RB', 'BRL', 1, 'Real bresilien'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'SK', 'SKK', 1, 'Couronnes slovaques'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'YC', 'CNY', 1, 'Yuang chinois'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'AE', 'AED', 1, 'Arabes emirats dirham'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'CF', 'XAF', 1, 'Francs cfa beac'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'EG', 'EGP', 1, 'Livre egyptienne'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'KR', 'KRW', 1, 'Won coree du sud'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'NZ', 'NZD', 1, 'Dollar neo-zelandais'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'TR', 'TRL', 1, 'Livre turque'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'ID', 'IDR', 1, 'Rupiahs d''indonesie'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'IN', 'INR', 1, 'Roupie indienne'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'LT', 'LTL', 1, 'Litas'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'RU', 'SUR', 1, 'Rouble'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'FH', 'HUF', 1, 'Forint hongrois'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'LK', 'LKR', 1, 'Roupie sri lanka'); 

--
-- Taux TVA
-- Source des taux: http://fr.wikipedia.org/wiki/Taxe_sur_la_valeur_ajout%C3%A9e
--

delete from llx_c_tva;

-- ALLEMAGNE (id 5)
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values ( 51, 5,  '16','0','VAT Rate 16',1);
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values ( 52, 5,   '7','0','VAT Rate 7',1);
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values ( 53, 5,   '0','0','VAT Rate 0',1);

-- AUSTRALIE (id 29)
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values (291,29,  '10','0','VAT Rate 10',1);
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values (292,29,   '0','0','VAT Rate 0',1);

-- BELGIQUE (id 2)
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values ( 21, 2,  '21','0','VAT Rate 21',1);
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values ( 22, 2,   '6','0','VAT Rate 6',1);
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values ( 23, 2,   '0','0','VAT Rate 0 ou non applicable',1);

-- CANADA (id 14)
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values (141,14,   '7','0','VAT Rate 7',1);
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values (142,14,   '0','0','VAT Rate 0',1);

-- ESPAGNE (id 4)
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values ( 41, 4,  '16','0','VAT Rate 16',1);
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values ( 42, 4,   '7','0','VAT Rate 7',1);
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values ( 43, 4,   '4','0','VAT Rate 4',1);
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values ( 44, 4,   '0','0','VAT Rate 0',1);

-- ITALY (id 3)
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values ( 31, 3,  '20','0','VAT Rate 20',1);
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values ( 32, 3,  '10','0','VAT Rate 10',1);
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values ( 33, 3,   '4','0','VAT Rate 4',1);
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values ( 34, 3,   '0','0','VAT Rate 0',1);

-- FRANCE (id 1)
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values ( 11, 1,'19.6','0','VAT Rate 19.6 (France hors DOM-TOM)',1);
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values ( 12, 1, '8.5','0','VAT Rate 8.5 (DOM sauf Guyane et Saint-Martin)',0);
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values ( 13, 1, '8.5','1','VAT Rate 8.5 (DOM sauf Guyane et Saint-Martin), non per�u par le vendeur mais r�cup�rable par l\'acheteur',0);
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values ( 14, 1, '5.5','0','VAT Rate 5.5 (France hors DOM-TOM)',1);
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values ( 15, 1,   '0','0','VAT Rate 0 ou non applicable (France, TOM)',1);

-- PAYS-BAS (id 17)
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values (171,17,  '19','0','VAT Rate 19',1);
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values (172,17,   '6','0','VAT Rate 6',1);
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values (173,17,   '0','0','VAT Rate 0',1);

-- PORTUGAL (id 26)
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values (261,26,  '17','0','VAT Rate 17',1);
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values (262,26,  '12','0','VAT Rate 12',1);
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values (263,26,   '0','0','VAT Rate 0',1);

-- ROYAUME UNI (id 7)
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values ( 71, 7,'17.5','0','VAT Rate 17.5',1);
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values ( 72, 7,   '5','0','VAT Rate 5',1);
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values ( 73, 7,   '0','0','VAT Rate 0',1);

-- SUISSE (id 6)
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values ( 61, 6, '7.6','0','VAT Rate 7.6',1);
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values ( 62, 6, '3.6','0','VAT Rate 3.6',1);
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values ( 63, 6, '2.4','0','VAT Rate 2.4',1);
insert into llx_c_tva(rowid,fk_pays,taux,recuperableonly,note,active) values ( 64, 6,   '0','0','VAT Rate 0',1);



--
-- Les types de contact d'un element
--
insert into llx_c_type_contact(rowid, element, source, code, libelle, active ) values (10, 'contrat', 'internal', 'SALESREPSIGN',  'Commercial signataire du contrat', 1);
insert into llx_c_type_contact(rowid, element, source, code, libelle, active ) values (11, 'contrat', 'internal', 'SALESREPFOLL',  'Commercial suivi du contrat', 1);
insert into llx_c_type_contact(rowid, element, source, code, libelle, active ) values (20, 'contrat', 'external', 'BILLING',       'Contact client facturation contrat', 1);
insert into llx_c_type_contact(rowid, element, source, code, libelle, active ) values (21, 'contrat', 'external', 'CUSTOMER',      'Contact client suivi contrat', 1);
insert into llx_c_type_contact(rowid, element, source, code, libelle, active ) values (22, 'contrat', 'external', 'SALESREPSIGN',  'Contact client signataire contrat', 1);
                                                                                                    
insert into llx_c_type_contact(rowid, element, source, code, libelle, active ) values (31, 'propal',  'internal', 'SALESREPFOLL',  'Commercial � l\'origine de la propale', 1);
insert into llx_c_type_contact(rowid, element, source, code, libelle, active ) values (40, 'propal',  'external', 'BILLING',       'Contact client facturation propale', 1);
insert into llx_c_type_contact(rowid, element, source, code, libelle, active ) values (41, 'propal',  'external', 'CUSTOMER',      'Contact client suivi propale', 1);
                                                                                                    
insert into llx_c_type_contact(rowid, element, source, code, libelle, active ) values (80, 'projet',  'internal', 'PROJECTLEADER', 'Chef de Projet', 1);
insert into llx_c_type_contact(rowid, element, source, code, libelle, active ) values (81, 'projet',  'external', 'PROJECTLEADER', 'Chef de Projet', 1);

insert into llx_c_type_contact(rowid, element, source, code, libelle, active ) values (50, 'facture', 'internal', 'SALESREPFOLL',  'Responsable suivi du paiement', 1);
insert into llx_c_type_contact(rowid, element, source, code, libelle, active ) values (60, 'facture', 'external', 'BILLING',       'Contact client facturation', 1);
insert into llx_c_type_contact(rowid, element, source, code, libelle, active ) values (61, 'facture', 'external', 'SHIPPING',      'Contact client livraison', 1);
insert into llx_c_type_contact(rowid, element, source, code, libelle, active ) values (62, 'facture', 'external', 'SERVICE',       'Contact client prestation', 1);

insert into llx_c_type_contact(rowid, element, source, code, libelle, active ) values (91, 'commande','internal', 'SALESREPFOLL',  'Responsable suivi de la commande', 1);
insert into llx_c_type_contact(rowid, element, source, code, libelle, active ) values (100,'commande','external', 'BILLING',       'Contact client facturation commande', 1);
insert into llx_c_type_contact(rowid, element, source, code, libelle, active ) values (101,'commande','external', 'CUSTOMER',      'Contact client suivi commande', 1);

