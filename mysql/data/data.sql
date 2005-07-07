-- Copyright (C) 2001-2004 Rodolphe Quiedeville <rodolphe@quiedeville.org>
-- Copyright (C) 2003      Jean-Louis Bergamo   <jlb@j1b.org>
-- Copyright (C) 2004-2005 Laurent Destailleur  <eldy@users.sourceforge.net>
-- Copyright (C) 2004      Benoit Mortier       <benoit.mortier@opensides.be>
-- Copyright (C) 2004      Guillaume Delecourt  <guillaume.delecourt@opensides.be>
-- Copyright (C) 2005      Regis Houssin  			<regis.houssin@cap-networks.com>
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

insert into llx_cond_reglement(rowid, code, sortorder, actif, libelle, libelle_facture, fdm, nbjour) values (1,'RECEP',       1,1, 'A r�ception','R�ception de facture',0,0);
insert into llx_cond_reglement(rowid, code, sortorder, actif, libelle, libelle_facture, fdm, nbjour) values (2,'30D',         2,1, '30 jours','R�glement � 30 jours',0,30);
insert into llx_cond_reglement(rowid, code, sortorder, actif, libelle, libelle_facture, fdm, nbjour) values (3,'30DENDMONTH', 3,1, '30 jours fin de mois','R�glement � 30 jours fin de mois',1,30);
insert into llx_cond_reglement(rowid, code, sortorder, actif, libelle, libelle_facture, fdm, nbjour) values (4,'60D',         4,1, '60 jours','R�glement � 60 jours',0,60);
insert into llx_cond_reglement(rowid, code, sortorder, actif, libelle, libelle_facture, fdm, nbjour) values (5,'60DENDMONTH', 5,1, '60 jours fin de mois','R�glement � 60 jours fin de mois',1,60);


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
insert into llx_const (name, value, type, note, visible) values ('MAIN_MONNAIE','euros','chaine','Monnaie',0);

insert into llx_const (name, value, type, note, visible) values ('MAIN_UPLOAD_DOC','1','chaine','Autorise l\'upload de document',1);
insert into llx_const (name, value, type, note, visible) values ('MAIN_NOT_INSTALLED','1','chaine','Test d\'installation',1);
insert into llx_const (name, value, type, note, visible) values ('MAIN_MAIL_FROM','dolibarr-robot@domain.com','chaine','EMail emetteur pour les notifications automatiques Dolibarr',1);

insert into llx_const (name, value, type, note, visible) values ('MAIN_TITLE','Dolibarr','chaine','Titre des pages',0);

insert into llx_const (name, value, type, note, visible) values ('COMPTA_ONLINE_PAYMENT_BPLC','1','yesno','Syst�me de gestion de la banque populaire de Lorraine',0);

--
-- IHM
--
insert into llx_const (name, value, type, note, visible) values ('MAIN_THEME','eldy','chaine','Th�me par d�faut',0);
insert into llx_const (name, value, type, note, visible) values ('SIZE_LISTE_LIMIT','20','chaine','Taille des listes',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_MENU_BARRETOP','default.php','chaine','Module de gestion de la barre de menu du haut',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_LANG_DEFAULT','fr_FR','chaine','Langue par d�faut pour les �crans Dolibarr',0);

insert into llx_const (name, value, type, note, visible) values ('MAIN_SEARCHFORM_CONTACT','1','yesno','Affichage formulaire de recherche des Contacts dans la barre de gauche',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_SEARCHFORM_SOCIETE','1','yesno','Affichage formulaire de recherche des Soci�t�s dans la barre de gauche',0);
insert into llx_const (name, value, type, note, visible) values ('MAIN_SEARCHFORM_PRODUITSERVICE','1','yesno','Affichage formulaire de recherche des Produits et Services dans la barre de gauche',0);

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
insert into llx_const (name, value, type, note) values ('MAILING_EMAIL_FROM','mailing@societe.com','chaine','Champ From du mail pour mailing clients/prospects');

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
insert into llx_const (name, value, type) values ('DB_NAME_OSC','catalog','chaine');
insert into llx_const (name, value, type) values ('OSC_LANGUAGE_ID','1','chaine');
insert into llx_const (name, value, type) values ('OSC_CATALOG_URL','http://osc.lafrere.lan/','chaine');

--
--
--
insert into llx_const (name, value, type, visible) values ('FACTURE_ADDON',       'jupiter','chaine',0);
insert into llx_const (name, value, type, visible) values ('FACTURE_ADDON_PDF',   'crabe','chaine',0);
insert into llx_const (name, value, type, visible) values ('COMMANDE_ADDON',      'mod_commande_ivoire','chaine',0);
insert into llx_const (name, value, type, visible) values ('EXPEDITION_ADDON_PDF','rouget','chaine',0);
insert into llx_const (name, value, type, visible) values ('PROPALE_ADDON',       'mod_propale_ivoire','chaine',0);
insert into llx_const (name, value, type, visible) values ('FACTURE_ADDON_PDF',    'azur','chaine',0);

--
-- Forcer les locales
--
insert into llx_const (name, value, type, visible, note) VALUES ('MAIN_FORCE_SETLOCALE_LC_ALL',      '', 'chaine', 1, 'Pour forcer LC_ALL si pb de locale');
insert into llx_const (name, value, type, visible, note) VALUES ('MAIN_FORCE_SETLOCALE_LC_TIME',     '', 'chaine', 1, 'Pour forcer LC_TIME si pb de locale');
insert into llx_const (name, value, type, visible, note) VALUES ('MAIN_FORCE_SETLOCALE_LC_MONETARY', '', 'chaine', 1, 'Pour forcer LC_MONETARY si pb de locale');
insert into llx_const (name, value, type, visible, note) VALUES ('MAIN_FORCE_SETLOCALE_LC_NUMERIC',  '', 'chaine', 1, 'Mettre la valeur C si probl�me de centimes');

-- Dictionnaires llx_c

--
-- Descriptif du plan comptable FR PCG99-ABREGE
--

delete from llx_c_accountingsystem;
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES (  1,1,'PCG99-ABREGE','CAPIT', 'CAPITAL',  'Capital','101');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES (  2,1,'PCG99-ABREGE','CAPIT', 'XXXXXX',   'Ecarts de r��valuation','105');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES (  3,1,'PCG99-ABREGE','CAPIT', 'XXXXXX',   'R�serve l�gale','1061');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES (  4,1,'PCG99-ABREGE','CAPIT', 'XXXXXX',   'R�serves statutaires ou contractuelles','1063');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES (  5,1,'PCG99-ABREGE','CAPIT', 'XXXXXX',   'R�serves r�glement�es','1064');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES (  6,1,'PCG99-ABREGE','CAPIT', 'XXXXXX',   'Autres r�serves','1068');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES (  7,1,'PCG99-ABREGE','CAPIT', 'XXXXXX',   'Compte de l''exploitant','108');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES (  8,1,'PCG99-ABREGE','CAPIT', 'XXXXXX',   'r�sultat de l''exercice','12');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES (  9,1,'PCG99-ABREGE','CAPIT', 'XXXXXX',   'Amortissements d�rogatoires','145');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 10,1,'PCG99-ABREGE','CAPIT', 'XXXXXX',   'Provision sp�ciale de r��valuation','146');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 11,1,'PCG99-ABREGE','CAPIT', 'XXXXXX',   'Plus-values r�investies','147');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 12,1,'PCG99-ABREGE','CAPIT', 'XXXXXX',   'Autres provisions r�glement�es','148');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 13,1,'PCG99-ABREGE','CAPIT', 'XXXXXX',   'Provisions pour risques et charges','15');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 14,1,'PCG99-ABREGE','CAPIT', 'XXXXXX',   'emprunts et dettes assimilees','16');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 15,1,'PCG99-ABREGE','IMMO',  'XXXXXX',   'immobilisations incorporelles','20');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 16,1,'PCG99-ABREGE','IMMO',  'XXXXXX',   'Frais d''�tablissement','201');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 17,1,'PCG99-ABREGE','IMMO',  'XXXXXX',   'Droit au bail','206');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 18,1,'PCG99-ABREGE','IMMO',  'XXXXXX',   'Fonds commercial','207');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 19,1,'PCG99-ABREGE','IMMO',  'XXXXXX',   'Autres immobilisations incorporelles','208');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 20,1,'PCG99-ABREGE','IMMO',  'XXXXXX',   'immobilisations corporelles','21');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 21,1,'PCG99-ABREGE','IMMO',  'XXXXXX',   'immobilisations en cours','23');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 22,1,'PCG99-ABREGE','IMMO',  'XXXXXX',   'autres immobilisations financieres','27');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 23,1,'PCG99-ABREGE','IMMO',  'XXXXXX',   'Amortissements des immobilisations incorporelles','280');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 24,1,'PCG99-ABREGE','IMMO',  'XXXXXX',   'Amortissements des immobilisations corporelles','281');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 25,1,'PCG99-ABREGE','IMMO',  'XXXXXX',   'Provisions pour d�pr�ciation des immobilisations incorporelles','290');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 26,1,'PCG99-ABREGE','IMMO',  'XXXXXX',   'Provisions pour d�pr�ciation des immobilisations corporelles','291');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 27,1,'PCG99-ABREGE','IMMO',  'XXXXXX',   'Provisions pour d�pr�ciation des autres immobilisations financi�res','297');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 28,1,'PCG99-ABREGE','STOCK', 'XXXXXX',   'matieres premi�res','31');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 29,1,'PCG99-ABREGE','STOCK', 'XXXXXX',   'autres approvisionnements','32');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 30,1,'PCG99-ABREGE','STOCK', 'XXXXXX',   'en-cours de production de biens','33');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 31,1,'PCG99-ABREGE','STOCK', 'XXXXXX',   'en-cours de production de services','34');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 32,1,'PCG99-ABREGE','STOCK', 'XXXXXX',   'stocks de produits','35');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 33,1,'PCG99-ABREGE','STOCK', 'XXXXXX',   'stocks de marchandises','37');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 34,1,'PCG99-ABREGE','STOCK', 'XXXXXX',   'Provisions pour d�pr�ciation des mati�res premi�res','391');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 35,1,'PCG99-ABREGE','STOCK', 'XXXXXX',   'Provisions pour d�pr�ciation des autres approvisionnements','392');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 36,1,'PCG99-ABREGE','STOCK', 'XXXXXX',   'Provisions pour d�pr�ciation des en-cours de production de biens','393');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 37,1,'PCG99-ABREGE','STOCK', 'XXXXXX',   'Provisions pour d�pr�ciation des en-cours de production de services','394');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 38,1,'PCG99-ABREGE','STOCK', 'XXXXXX',   'Provisions pour d�pr�ciation des stocks de produits','395');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 39,1,'PCG99-ABREGE','STOCK', 'XXXXXX',   'Provisions pour d�pr�ciation des stocks de marchandises','397');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 40,1,'PCG99-ABREGE','TIERS', 'SUPPLIER', 'Fournisseurs et Comptes rattach�s','400');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 41,1,'PCG99-ABREGE','TIERS', 'XXXXXX',   'Fournisseurs d�biteurs','409');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 42,1,'PCG99-ABREGE','TIERS', 'CUSTOMER', 'Clients et Comptes rattach�s','410');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 43,1,'PCG99-ABREGE','TIERS', 'XXXXXX',   'Clients cr�diteurs','419');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 44,1,'PCG99-ABREGE','TIERS', 'XXXXXX',   'Personnel','421');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 45,1,'PCG99-ABREGE','TIERS', 'XXXXXX',   'Personnel','428');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 46,1,'PCG99-ABREGE','TIERS', 'XXXXXX',   'S�curit� sociale et autres organismes sociaux','43');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 47,1,'PCG99-ABREGE','TIERS', 'XXXXXX',   'Etat - imp�ts sur b�n�fice','444');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 48,1,'PCG99-ABREGE','TIERS', 'XXXXXX',   'Etat - Taxes sur chiffre affaire','445');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 49,1,'PCG99-ABREGE','TIERS', 'XXXXXX',   'Autres imp�ts, taxes et versements assimil�s','447');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 50,1,'PCG99-ABREGE','TIERS', 'XXXXXX',   'Groupe et associes','45');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 51,1,'PCG99-ABREGE','TIERS', 'XXXXXX',   'Associ�s','455');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 52,1,'PCG99-ABREGE','TIERS', 'XXXXXX',   'D�biteurs divers et cr�diteurs divers','46');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 53,1,'PCG99-ABREGE','TIERS', 'XXXXXX',   'comptes transitoires ou d''attente','47');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 54,1,'PCG99-ABREGE','TIERS', 'XXXXXX',   'Charges � r�partir sur plusieurs exercices','481');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 55,1,'PCG99-ABREGE','TIERS', 'XXXXXX',   'Charges constat�es d''avance','486');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 56,1,'PCG99-ABREGE','TIERS', 'XXXXXX',   'Produits constat�s d''avance','487');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 57,1,'PCG99-ABREGE','TIERS', 'XXXXXX',   'Provisions pour d�pr�ciation des comptes de clients','491');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 58,1,'PCG99-ABREGE','TIERS', 'XXXXXX',   'Provisions pour d�pr�ciation des comptes de d�biteurs divers','496');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 59,1,'PCG99-ABREGE','FINAN', 'XXXXXX',   'valeurs mobili�res de placement','50');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 60,1,'PCG99-ABREGE','FINAN', 'BANK',     'banques, �tablissements financiers et assimil�s','51');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 61,1,'PCG99-ABREGE','FINAN', 'CASH',     'Caisse','53');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 62,1,'PCG99-ABREGE','FINAN', 'XXXXXX',   'r�gies d''avance et accr�ditifs','54');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 63,1,'PCG99-ABREGE','FINAN', 'XXXXXX',   'virements internes','58');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 64,1,'PCG99-ABREGE','FINAN', 'XXXXXX',   'Provisions pour d�pr�ciation des valeurs mobili�res de placement','590');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 65,1,'PCG99-ABREGE','CHARGE','XXXXXX',   'Achats','60');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 66,1,'PCG99-ABREGE','CHARGE','XXXXXX',   'variations des stocks','603');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 67,1,'PCG99-ABREGE','CHARGE','XXXXXX',   'Services ext�rieurs','61');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 68,1,'PCG99-ABREGE','CHARGE','XXXXXX',   'Autres services ext�rieurs','62');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 69,1,'PCG99-ABREGE','CHARGE','XXXXXX',   'Imp�ts, taxes et versements assimiles','63');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 70,1,'PCG99-ABREGE','CHARGE','XXXXXX',   'R�mun�rations du personnel','641');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 71,1,'PCG99-ABREGE','CHARGE','XXXXXX',   'R�mun�ration du travail de l''exploitant','644');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 72,1,'PCG99-ABREGE','CHARGE','SOCIAL',   'Charges de s�curit� sociale et de pr�voyance','645');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 73,1,'PCG99-ABREGE','CHARGE','XXXXXX',   'Cotisations sociales personnelles de l''exploitant','646');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 74,1,'PCG99-ABREGE','CHARGE','XXXXXX',   'Autres charges de gestion courante','65');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 75,1,'PCG99-ABREGE','CHARGE','XXXXXX',   'Charges financi�res','66');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 76,1,'PCG99-ABREGE','CHARGE','XXXXXX',   'Charges exceptionnelles','67');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 77,1,'PCG99-ABREGE','CHARGE','XXXXXX',   'Dotations aux amortissements et aux provisions','681');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 78,1,'PCG99-ABREGE','CHARGE','XXXXXX',   'Dotations aux amortissements et aux provisions','686');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 79,1,'PCG99-ABREGE','CHARGE','XXXXXX',   'Dotations aux amortissements et aux provisions','687');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 80,1,'PCG99-ABREGE','CHARGE','XXXXXX',   'Participation des salari�s aux r�sultats','691');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 81,1,'PCG99-ABREGE','CHARGE','XXXXXX',   'Imp�ts sur les b�n�fices','695');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 82,1,'PCG99-ABREGE','CHARGE','XXXXXX',   'Imposition forfaitaire annuelle des soci�t�s','697');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 83,1,'PCG99-ABREGE','CHARGE','XXXXXX',   'Produits','699');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 84,1,'PCG99-ABREGE','PROD',  'PRODUCT',  'Ventes de produits finis','701');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 85,1,'PCG99-ABREGE','PROD',  'SERVICE',  'Prestations de services','706');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 86,1,'PCG99-ABREGE','PROD',  'PRODUCT',  'Ventes de marchandises','707');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 87,1,'PCG99-ABREGE','PROD',  'PRODUCT',  'Produits des activit�s annexes','708');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 88,1,'PCG99-ABREGE','PROD',  'XXXXXX',   'Rabais, remises et ristournes accord�s par l''entreprise','709');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 89,1,'PCG99-ABREGE','PROD',  'XXXXXX',   'Variation des stocks','713');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 90,1,'PCG99-ABREGE','PROD',  'XXXXXX',   'Production immobilis�e','72');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 91,1,'PCG99-ABREGE','PROD',  'XXXXXX',   'Produits nets partiels sur op�rations � long terme','73');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 92,1,'PCG99-ABREGE','PROD',  'XXXXXX',   'Subventions d''exploitation','74');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 93,1,'PCG99-ABREGE','PROD',  'XXXXXX',   'Autres produits de gestion courante','75');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 94,1,'PCG99-ABREGE','PROD',  'XXXXXX',   'Jetons de pr�sence et r�mun�rations d''administrateurs, g�rants,...','753');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 95,1,'PCG99-ABREGE','PROD',  'XXXXXX',   'Ristournes per�ues des coop�ratives','754');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 96,1,'PCG99-ABREGE','PROD',  'XXXXXX',   'Quotes-parts de r�sultat sur op�rations faites en commun','755');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 97,1,'PCG99-ABREGE','PROD',  'XXXXXX',   'Produits financiers','76');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 98,1,'PCG99-ABREGE','PROD',  'XXXXXX',   'Produits exceptionnels','77');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES ( 99,1,'PCG99-ABREGE','PROD',  'XXXXXX',   'Reprises sur amortissements et provisions','781');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES (100,1,'PCG99-ABREGE','PROD',  'XXXXXX',   'Reprises sur provisions pour risques','786');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES (101,1,'PCG99-ABREGE','PROD',  'XXXXXX',   'Reprises sur provisions','787');
insert into llx_c_accountingsystem (rowid, fk_pays, pcg_version, pcg_type, pcg_subtype, label, account_number) VALUES (102,1,'PCG99-ABREGE','PROD',  'XXXXXX',   'Transferts de charges','79');

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

--
-- Provinces de Belgique - en Francais
--
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
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'54','Soci�t� � responsabilit� limit� (SARL)');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'55','Soci�t� anonyme � conseil d\'administration');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'56','Soci�t� anonyme � directoire');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'57','Soci�t� par actions simplifi�e');
                                                                     
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'61','Caisse d\'�pargne et de pr�voyance');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'62','Groupement d\'int�r�t �conomique');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'63','Soci�t� coop�rative agricole');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'64','Soci�t� non commerciale d\'assurances');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'65','Soci�t� civile');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (1,'69','Autres personnes de droit priv� inscrites au registre du commerce et des soci�t�s');
                                                                     
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

insert into llx_c_forme_juridique (fk_pays, code, libelle) values (2,'100','Ind�pendant');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (2,'101','SPRL - Soci�t� � responsabilit� limit�e');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (2,'102','SA   - Soci�t� Anonyme');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (2,'103','SCRL - Soci�t� coop�rative � responsabilit� limit�e');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (2,'104','ASBL - Association sans but Lucratif');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (2,'105','SCRI - Soci�t� coop�rative � responsabilit� illimit�e');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (2,'106','SCS  - Soci�t� en comanndite simple');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (2,'107','SCA  - Soci�t� en commandite par action');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (2,'108','SNC  - Soci�t� en nom collectif');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (2,'109','GIE  - Groupement d\'int�r�t �conomique');
insert into llx_c_forme_juridique (fk_pays, code, libelle) values (2,'110','GEIE - Groupement europ�en d\'int�r�t �conomique');

--
-- Types paiement
--

delete from llx_c_paiement;
insert into llx_c_paiement (id,code,libelle,type) values (0, '',    '-', 3);
insert into llx_c_paiement (id,code,libelle,type) values (1, 'TIP', 'TIP', 1);
insert into llx_c_paiement (id,code,libelle,type) values (2, 'VIR', 'Virement', 2);
insert into llx_c_paiement (id,code,libelle,type) values (3, 'PRE', 'Pr�l�vement', 1);
insert into llx_c_paiement (id,code,libelle,type) values (4, 'LIQ', 'Liquide', 0);
insert into llx_c_paiement (id,code,libelle,type) values (5, 'VAD', 'Paiement en ligne', 0);
insert into llx_c_paiement (id,code,libelle,type) values (6, 'CB',  'Carte Bancaire', 1);
insert into llx_c_paiement (id,code,libelle,type) values (7, 'CHQ', 'Ch�que', 2);

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
insert into llx_c_typent (id,code,libelle) values (  4, 'TE_ADMIN',   'Administration');
insert into llx_c_typent (id,code,libelle) values (100, 'TE_OTHER',   'Autres');

--
-- Regions
--

insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (0,0,0,'0',0,'-');
-- Regions de France
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (101,1,  1,'97105',3,'Guadeloupe');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (102,1,  2,'97209',3,'Martinique');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (103,1,  3,'97302',3,'Guyane');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (104,1,  4,'97411',3,'R�union');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (105,1, 11,'75056',1,'�le-de-France');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (106,1, 21,'51108',0,'Champagne-Ardenne');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (107,1, 22,'80021',0,'Picardie');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (108,1, 23,'76540',0,'Haute-Normandie');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (109,1, 24,'45234',2,'Centre');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (110,1, 25,'14118',0,'Basse-Normandie');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (111,1, 26,'21231',0,'Bourgogne');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (112,1, 31,'59350',2,'Nord-Pas-de-Calais');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (113,1, 41,'57463',0,'Lorraine');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (114,1, 42,'67482',1,'Alsace');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (115,1, 43,'25056',0,'Franche-Comt�');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (116,1, 52,'44109',4,'Pays de la Loire');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (117,1, 53,'35238',0,'Bretagne');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (118,1, 54,'86194',2,'Poitou-Charentes');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (119,1, 72,'33063',1,'Aquitaine');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (120,1, 73,'31555',0,'Midi-Pyr�n�es');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (121,1, 74,'87085',2,'Limousin');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (122,1, 82,'69123',2,'Rh�ne-Alpes');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (123,1, 83,'63113',1,'Auvergne');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (124,1, 91,'34172',2,'Languedoc-Roussillon');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (125,1, 93,'13055',0,'Provence-Alpes-C�te d\'Azur');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (126,1, 94,'2A004',0,'Corse');

--
-- Regions de Belgique
--

insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (201,2,201,'',1,'Flandre');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (202,2,202,'',2,'Wallonie');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (203,2,203,'',3,'Bruxelles-Capitale');

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
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'ES', 'PTE', 1, 'Escudos'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'FB', 'BEF', 1, 'Francs belges'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'FF', 'FRF', 1, 'Francs francais'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'FL', 'LUF', 1, 'Francs luxembourgeois'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'FO', 'NLG', 1, 'Florins'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'FS', 'CHF', 1, 'Francs suisses'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'LI', 'IEP', 1, 'Livres irlandaises'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'LR', 'ITL', 1, 'Lires'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'LS', 'GBP', 1, 'Livres sterling'); 
insert into llx_c_currencies ( code, code_iso, active, label ) values ( 'MA', 'DEM', 1, 'Deutsch mark'); 
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
