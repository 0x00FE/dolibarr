
alter table llx_societe add siret     varchar(14) after siren;
alter table llx_societe add ape       varchar(4) after siret;
alter table llx_societe add tva_intra varchar(20) after ape;
alter table llx_societe add capital real after tva_intra;
alter table llx_societe add rubrique varchar(255);

alter table llx_societe add fk_forme_juridique integer default 0 after fk_typent;

alter table llx_societe add fk_user_creat integer;
alter table llx_societe add fk_user_modif integer;

alter table llx_socpeople add civilite smallint;
alter table llx_socpeople add fk_user_modif integer;


alter table llx_paiement add tms timestamp after datec;
alter table llx_paiement add fk_user_creat integer;
alter table llx_paiement add fk_user_modif integer;

alter table llx_propal add fin_validite datetime ;

alter table llx_entrepot add statut tinyint default 1;

alter table llx_product add stock_propale integer default 0;
alter table llx_product add stock_commande integer default 0;
alter table llx_product add seuil_stock_alerte integer default 0;

alter table llx_groupart add description text after groupart ;

alter table llx_socpeople add phone_perso varchar(30) after phone ;
alter table llx_socpeople add phone_mobile varchar(30) after phone_perso ;
alter table llx_socpeople add jabberid varchar(255) after email ;
alter table llx_socpeople add birthday date after address ;
alter table llx_socpeople add tms timestamp after datec ;

alter table llx_facture_fourn drop index facnumber ;
alter table llx_facture_fourn add unique index (facnumber, fk_soc) ;

alter table llx_bank_account modify bank varchar(60);
alter table llx_bank_account modify domiciliation varchar(255);
alter table llx_bank_account add proprio varchar(60) after domiciliation ;
alter table llx_bank_account add adresse_proprio varchar(255) after proprio ;

alter table llx_paiement add fk_bank integer NOT NULL after note ;
alter table llx_paiementfourn add fk_bank integer NOT NULL after note ;


alter table c_paiement rename llx_c_paiement ;
alter table c_propalst rename llx_c_propalst ;

alter table c_actioncomm     rename llx_c_actioncomm ;
alter table c_chargesociales rename llx_c_chargesociales ;
alter table c_effectif       rename llx_c_effectif ;
alter table c_pays           rename llx_c_pays ;
alter table c_stcomm         rename llx_c_stcomm ;
alter table c_typent         rename llx_c_typent ;


create table llx_birthday_alert
(
  rowid        integer AUTO_INCREMENT PRIMARY KEY,
  fk_contact   integer,
  fk_user      integer
)type=innodb;

create table llx_co_fa
(
  rowid       integer AUTO_INCREMENT PRIMARY KEY,
  fk_commande integer NOT NULL,
  fk_facture  integer NOT NULL,

  key(fk_commande),
  key(fk_facture)
)type=innodb;

create table llx_paiement_facture
(
  rowid           integer AUTO_INCREMENT PRIMARY KEY,
  fk_paiement     integer,
  fk_facture      integer,
  amount          real default 0,
  
  key (fk_paiement),
  key( fk_facture)
)type=innodb;


insert into llx_const(name, value, type, note) values ('MAIN_UPLOAD_DOC','1','chaine','Authorise l\'upload de document');




create table llx_c_forme_juridique
(
  code       integer PRIMARY KEY,
  libelle    varchar(255),
  active     tinyint default 1

)type=innodb;



--
-- Formes juridiques
-- Extrait de http://www.insee.fr/fr/nom_def_met/nomenclatures/cj/cjniveau2.htm
insert into llx_c_forme_juridique (code, libelle) values (0,'Non renseign�e');
insert into llx_c_forme_juridique (code, libelle) values (11,'Artisan Commer�ant');
insert into llx_c_forme_juridique (code, libelle) values (12,'Commer�ant');
insert into llx_c_forme_juridique (code, libelle) values (13,'Artisan');
insert into llx_c_forme_juridique (code, libelle) values (14,'Officier public ou minist�riel');
insert into llx_c_forme_juridique (code, libelle) values (15,'Profession lib�rale');
insert into llx_c_forme_juridique (code, libelle) values (16,'Exploitant agricole');
insert into llx_c_forme_juridique (code, libelle) values (17,'Agent commercial');
insert into llx_c_forme_juridique (code, libelle) values (18,'Associ� G�rant de soci�t�');
insert into llx_c_forme_juridique (code, libelle) values (19,'(Autre) personne physique');
insert into llx_c_forme_juridique (code, libelle) values (21,'Indivision');
insert into llx_c_forme_juridique (code, libelle) values (22,'Soci�t� cr��e de fait');
insert into llx_c_forme_juridique (code, libelle) values (23,'Soci�t� en participation');
insert into llx_c_forme_juridique (code, libelle) values (27,'Paroisse hors zone concordataire');
insert into llx_c_forme_juridique (code, libelle) values (29,'Autre groupement de droit priv� non dot� de la personnalit� morale');
insert into llx_c_forme_juridique (code, libelle) values (31,'Personne morale de droit �tranger, immatricul�e au RCS (registre du commerce et des soci�t�s)');
insert into llx_c_forme_juridique (code, libelle) values (32,'Personne morale de droit �tranger, non immatricul�e au RCS');

insert into llx_c_forme_juridique (code, libelle) values (41,'�tablissement public ou r�gie � caract�re industriel ou commercial');

insert into llx_c_forme_juridique (code, libelle) values (51,'Soci�t� coop�rative commerciale particuli�re');
insert into llx_c_forme_juridique (code, libelle) values (52,'Soci�t� en nom collectif');
insert into llx_c_forme_juridique (code, libelle) values (53,'Soci�t� en commandite');
insert into llx_c_forme_juridique (code, libelle) values (54,'Soci�t� � responsabilit� limit� (SARL)');
insert into llx_c_forme_juridique (code, libelle) values (55,'Soci�t� anonyme � conseil d\'administration');
insert into llx_c_forme_juridique (code, libelle) values (56,'Soci�t� anonyme � directoire');
insert into llx_c_forme_juridique (code, libelle) values (57,'Soci�t� par actions simplifi�e');

insert into llx_c_forme_juridique (code, libelle) values (61,'Caisse d\'�pargne et de pr�voyance');
insert into llx_c_forme_juridique (code, libelle) values (62,'Groupement d\'int�r�t �conomique');
insert into llx_c_forme_juridique (code, libelle) values (63,'Soci�t� coop�rative agricole');
insert into llx_c_forme_juridique (code, libelle) values (64,'Soci�t� non commerciale d\'assurances');
insert into llx_c_forme_juridique (code, libelle) values (65,'Soci�t� civile');
insert into llx_c_forme_juridique (code, libelle) values (69,'Autres personnes de droit priv� inscrites au registre du commerce et des soci�t�s');

insert into llx_c_forme_juridique (code, libelle) values (71,'Administration de l\'�tat');
insert into llx_c_forme_juridique (code, libelle) values (72,'Collectivit� territoriale');
insert into llx_c_forme_juridique (code, libelle) values (73,'�tablissement public administratif');
insert into llx_c_forme_juridique (code, libelle) values (74,'Autre personne morale de droit public administratif');

insert into llx_c_forme_juridique (code, libelle) values (81,'Organisme g�rant un r�gime de protection social � adh�sion obligatoire');
insert into llx_c_forme_juridique (code, libelle) values (82,'Organisme mutualiste');
insert into llx_c_forme_juridique (code, libelle) values (83,'Comit� d\'entreprise');
insert into llx_c_forme_juridique (code, libelle) values (84,'Organisme professionnel');
insert into llx_c_forme_juridique (code, libelle) values (85,'Organisme de retraite � adh�sion non obligatoire');

insert into llx_c_forme_juridique (code, libelle) values (91,'Syndicat de propri�taires');
insert into llx_c_forme_juridique (code, libelle) values (92,'Association loi 1901 ou assimil�');
insert into llx_c_forme_juridique (code, libelle) values (93,'Fondation');
insert into llx_c_forme_juridique (code, libelle) values (99,'Autre personne morale de droit priv�');



