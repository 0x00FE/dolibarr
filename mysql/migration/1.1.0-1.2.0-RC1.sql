--
--
-- Attention � l ordre des requetes
-- ce fichier doit �tre charg� sur une version 1.1.0 
-- sans AUCUNE erreur ni warning
-- 
alter table llx_contrat add fk_facturedet integer NOT NULL default 0 after fk_facture;
alter table llx_contrat change fk_user_cloture fk_user_cloture integer;
alter table llx_contrat change fk_user_mise_en_service fk_user_mise_en_service integer;

alter table llx_facturedet add date_start date;
alter table llx_facturedet add date_end   date;

alter table llx_user add egroupware_id integer;
alter table llx_societe add siret     varchar(14) after siren;
alter table llx_societe add ape       varchar(4) after siret;
alter table llx_societe add tva_intra varchar(20) after ape;
alter table llx_societe add capital real after tva_intra;
alter table llx_societe add rubrique varchar(255);
alter table llx_societe add remise_client real default 0;



alter table llx_societe add fk_forme_juridique integer default 0 after fk_typent;

alter table llx_societe add fk_departement integer default 0 after ville;

alter table llx_societe add fk_user_creat integer;
alter table llx_societe add fk_user_modif integer;

alter table llx_socpeople add civilite varchar(6);
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
alter table llx_bank_account add account_number varchar(8) after clos ;
update llx_bank_account set account_number = '51' where account_number is null;

alter table llx_paiement add fk_bank integer NOT NULL after note ;
alter table llx_paiementfourn add fk_bank integer NOT NULL after note ;


alter table c_actioncomm     rename llx_c_actioncomm ;
alter table c_chargesociales rename llx_c_chargesociales ;
alter table c_effectif       rename llx_c_effectif ;
alter table c_paiement       rename llx_c_paiement ;
alter table c_pays           rename llx_c_pays ;
alter table c_propalst       rename llx_c_propalst ;
alter table c_stcomm         rename llx_c_stcomm ;
alter table c_typent         rename llx_c_typent ;



alter table llx_c_actioncomm add type varchar(10) not null default 'system' after id;
alter table llx_c_actioncomm add active tinyint default 1 NOT NULL after libelle;

alter table llx_c_paiement add code varchar(6) after id;



create table llx_birthday_alert
(
  rowid        integer AUTO_INCREMENT PRIMARY KEY,
  fk_contact   integer,
  fk_user      integer
)type=innodb;


alter table llx_birthday_alert rename llx_user_alert ;
alter table llx_user_alert add type integer after rowid;
update llx_user_alert set type=1 where type is null;


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


insert into llx_const(name, value, type, note, visible) values ('MAIN_UPLOAD_DOC','1','chaine','Authorise l\'upload de document',1);
insert into llx_const(name, value, type, note, visible) values ('MAIN_SEARCHFORM_PRODUITSERVICE','1','yesno','Affichage formulaire de recherche des Produits et Services dans la barre de gauche',0);
delete from llx_const where name = 'COMPTA_BANK_FACTURES';

update llx_bank set fk_type = 'VAD' where fk_type = 'WWW';

alter table llx_socpeople change civilite civilite varchar(6);

update llx_paiement set author = null where author = '';

create table llx_paiementcharge
(
  rowid           integer AUTO_INCREMENT PRIMARY KEY,
  fk_charge       integer,
  datec           datetime,
  tms             timestamp,
  datep           datetime,
  amount          real default 0,
  fk_typepaiement integer NOT NULL,
  num_paiement    varchar(50),
  note            text,
  fk_bank         integer NOT NULL,
  fk_user_creat   integer,
  fk_user_modif   integer

)type=innodb;


update llx_const set visible=0 where name like 'ADHERENT%';
update llx_const set visible=0 where name like 'PROPALE_ADDON%';

create table llx_user_param
(
  fk_user       integer,
  page          varchar(255),
  param         varchar(255),
  value         varchar(255),
  UNIQUE (fk_user,page,param)
)type=innodb;

create table llx_cash_account
(
  rowid          integer AUTO_INCREMENT PRIMARY KEY,
  datec          datetime,
  tms            timestamp,
  label          varchar(30),
  courant        smallint default 0 not null,
  clos           smallint default 0 not null,
  account_number varchar(8)
)type=innodb;

create table llx_cash
(
  rowid           integer AUTO_INCREMENT PRIMARY KEY,
  datec           datetime,
  dateo           date NOT NULL,
  amount          real NOT NULL default 0,
  label           varchar(255),
  fk_account      integer,
  fk_user_author  integer,
  fk_type         varchar(4),
  note            text
)type=innodb;


update llx_bank set datev=dateo where datev is null;

update llx_chargesociales set periode=date_ech where periode is null or periode = '0000-00-00';



create table llx_societe_remise
(
  rowid           integer AUTO_INCREMENT PRIMARY KEY,
  fk_soc          integer NOT NULL,
  tms             timestamp,
  datec	          datetime,                            
  fk_user_author  integer,                             
  remise_client   real           default 0,            
  note            text

)type=innodb;


create table llx_contact_facture
(
  idp          integer AUTO_INCREMENT PRIMARY KEY,
  fk_soc       integer NOT NULL,
  fk_contact   integer NOT NULL,   -- point sur llx_socpeople

  UNIQUE (fk_soc, fk_contact)
)type=innodb;


--
--
--
--

create table llx_so_gr
(
  rowid       integer AUTO_INCREMENT PRIMARY KEY,
  fk_soc      integer,
  fk_groupe   integer,

  UNIQUE(fk_soc, fk_groupe)
)type=innodb;

create table llx_groupesociete_remise
(
  rowid           integer AUTO_INCREMENT PRIMARY KEY,
  fk_groupe       integer NOT NULL,
  tms             timestamp,
  datec	          datetime,                            
  fk_user_author  integer,                             
  remise          real           default 0,            
  note            text

)type=innodb;

create table llx_groupesociete
(
  rowid           integer AUTO_INCREMENT PRIMARY KEY,
  parent          integer UNIQUE,
  tms             timestamp,
  datec	          datetime,                            
  nom             varchar(60),                         
  note            text,                                
  remise          real           default 0,
  fk_user_author  integer

)type=innodb;

--
--
--
--

create table llx_commande
(
  rowid            integer AUTO_INCREMENT PRIMARY KEY,
  tms              timestamp,
  fk_soc           integer,
  fk_soc_contact   integer,
  fk_projet        integer default 0,
  ref              varchar(30) NOT NULL,
  date_creation    datetime,            
  date_valid       datetime,            
  date_cloture     datetime,            
  date_commande    date,                
  fk_user_author   integer,             
  fk_user_valid    integer,             
  fk_user_cloture  integer,             
  source           smallint NOT NULL,
  fk_statut        smallint  default 0,
  amount_ht        real      default 0,
  remise_percent   real      default 0,
  remise           real      default 0,
  tva              real      default 0,
  total_ht         real      default 0,
  total_ttc        real      default 0,
  note             text,
  model_pdf        varchar(50),
  facture          tinyint default 0,   
  UNIQUE INDEX (ref)
)type=innodb;

create table llx_commandedet
(
  rowid          integer AUTO_INCREMENT PRIMARY KEY,
  fk_commande    integer,
  fk_product     integer,
  label          varchar(255),
  description    text,
  tva_tx         real default 19.6,
  qty		 real,             
  remise_percent real default 0,
  remise         real default 0,
  subprice       real,          
  price          real           
)type=innodb;




create table llx_societe_rib
(
  rowid          integer AUTO_INCREMENT PRIMARY KEY,
  fk_soc         integer NOT NULL,
  datec          datetime,
  tms            timestamp,
  label          varchar(30),
  bank           varchar(255),
  code_banque    varchar(7),
  code_guichet   varchar(6),
  number         varchar(255),
  cle_rib        varchar(5),
  bic            varchar(10),
  iban_prefix    varchar(5),
  domiciliation  varchar(255),
  proprio        varchar(60),
  adresse_proprio varchar(255)


)type=innodb;





drop table if exists llx_c_accountingsystem;

create table llx_c_accountingsystem
(
  rowid           integer AUTO_INCREMENT PRIMARY KEY,
  fk_pays         integer      NOT NULL,
  pcg_version     varchar(12)  NOT NULL,
  pcg_type        varchar(20)  NOT NULL,
  pcg_subtype     varchar(20)  NOT NULL,
  label           varchar(128) NOT NULL,
  account_number  varchar(20)  NOT NULL
)type=innodb;

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


drop table if exists llx_c_actioncomm;

create table llx_c_actioncomm
(
  id         integer     PRIMARY KEY,
  code       varchar(12)  UNIQUE NOT NULL,
  type       varchar(10) default 'system' not null,
  libelle    varchar(30) NOT NULL,
  active     tinyint default 1  NOT NULL,
  todo       tinyint
)type=innodb;

delete from llx_c_actioncomm;
insert into llx_c_actioncomm (id, code, type, libelle) values ( 1, 'AC_TEL',  'system', 'Appel T�l�phonique');
insert into llx_c_actioncomm (id, code, type, libelle) values ( 2, 'AC_FAX',  'system', 'Envoi Fax');
insert into llx_c_actioncomm (id, code, type, libelle) values ( 3, 'AC_PROP', 'system', 'Envoi Proposition');
insert into llx_c_actioncomm (id, code, type, libelle) values ( 4, 'AC_EMAIL','system', 'Envoi Email');
insert into llx_c_actioncomm (id, code, type, libelle) values ( 5, 'AC_RDV',  'system', 'Prendre rendez-vous');
insert into llx_c_actioncomm (id, code, type, libelle) values ( 9, 'AC_FAC',  'system', 'Envoi Facture');
insert into llx_c_actioncomm (id, code, type, libelle) values (10, 'AC_REL',  'system', 'Relance effectu�e');
insert into llx_c_actioncomm (id, code, type, libelle) values (11, 'AC_CLO',  'system', 'Cl�ture');


drop table if exists llx_c_ape;

create table llx_c_ape
(
  rowid       integer      AUTO_INCREMENT UNIQUE,
  code_ape    varchar(5)   PRIMARY KEY,
  libelle     varchar(255),
  active      tinyint default 1  NOT NULL
)type=innodb;


delete from llx_c_ape;


drop table if exists llx_c_chargesociales;

create table llx_c_chargesociales
(
  id          integer PRIMARY KEY,
  libelle     varchar(80),
  deductible  smallint NOT NULL default 0,
  active      tinyint default 1  NOT NULL
)type=innodb;

delete from llx_c_chargesociales;
insert into llx_c_chargesociales (id,libelle,deductible) values ( 1, 'Allocations familiales',1);
insert into llx_c_chargesociales (id,libelle,deductible) values ( 2, 'GSG Deductible',1);
insert into llx_c_chargesociales (id,libelle,deductible) values ( 3, 'GSG/CRDS NON Deductible',0);


drop table if exists llx_c_civilite;

create table llx_c_civilite
(
  rowid       integer    PRIMARY KEY,
  code        varchar(6) UNIQUE NOT NULL,
  civilite	  varchar(50),
  active      tinyint default 1  NOT NULL
)type=innodb;

delete from llx_c_civilite;
insert into llx_c_civilite (rowid, code, civilite, active) values (1 , 'MME',  'Madame', 1);
insert into llx_c_civilite (rowid, code, civilite, active) values (3 , 'MR',   'Monsieur', 1);
insert into llx_c_civilite (rowid, code, civilite, active) values (5 , 'MLE',  'Mademoiselle', 1);
insert into llx_c_civilite (rowid, code, civilite, active) values (7 , 'MTRE', 'Ma�tre', 1);


drop table if exists llx_c_departements;

create table llx_c_departements
(
  rowid       integer         AUTO_INCREMENT PRIMARY KEY,
  code_departement varchar(6) NOT NULL,
  fk_region   integer,
  cheflieu    varchar(7),
  tncc        integer,
  ncc         varchar(50),
  nom         varchar(50),
  active      tinyint default 1  NOT NULL,

  key (fk_region)
)type=innodb;

delete from llx_c_departements;
insert into llx_c_departements (rowid, fk_region, code_departement,cheflieu,tncc,ncc,nom) values (0,0,'0','0',0,'-','-');

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


drop table if exists llx_c_effectif;

create table llx_c_effectif
(
  id      integer     PRIMARY KEY,
  code    varchar(12)  UNIQUE NOT NULL,
  libelle varchar(30),
  active  tinyint default 1  NOT NULL
)type=innodb;

delete from llx_c_effectif;
insert into llx_c_effectif (id,code,libelle) values (0, 'EF0',       '-');
insert into llx_c_effectif (id,code,libelle) values (1, 'EF1-5',     '1 - 5');
insert into llx_c_effectif (id,code,libelle) values (2, 'EF6-10',    '6 - 10');
insert into llx_c_effectif (id,code,libelle) values (3, 'EF11-50',   '11 - 50');
insert into llx_c_effectif (id,code,libelle) values (4, 'EF51-100',  '51 - 100');
insert into llx_c_effectif (id,code,libelle) values (5, 'EF100-500', '100 - 500');
insert into llx_c_effectif (id,code,libelle) values (6, 'EF500-',    '> 500');


drop table if exists llx_c_forme_juridique;

create table llx_c_forme_juridique
(
  rowid      integer       AUTO_INCREMENT PRIMARY KEY,
  code       varchar(12)   UNIQUE NOT NULL,
  fk_pays    integer       NOT NULL,
  libelle    varchar(255),
  active     tinyint default 1  NOT NULL

)type=innodb;

delete from llx_c_forme_juridique;

insert into llx_c_forme_juridique (fk_pays, code, libelle) values (0, '0','-');

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

drop table if exists llx_c_paiement;

create table llx_c_paiement
(
  id         integer     PRIMARY KEY,
  code       varchar(6)  UNIQUE NOT NULL,
  libelle    varchar(30),
  type       smallint,	
  active     tinyint default 1  NOT NULL
)type=innodb;

delete from llx_c_paiement;
insert into llx_c_paiement (id,code,libelle,type) values (0, '',    '-', 3);
insert into llx_c_paiement (id,code,libelle,type) values (1, 'TIP', 'TIP', 1);
insert into llx_c_paiement (id,code,libelle,type) values (2, 'VIR', 'Virement', 2);
insert into llx_c_paiement (id,code,libelle,type) values (3, 'PRE', 'Pr�l�vement', 1);
insert into llx_c_paiement (id,code,libelle,type) values (4, 'LIQ', 'Liquide', 0);
insert into llx_c_paiement (id,code,libelle,type) values (5, 'VAD', 'Paiement en ligne', 0);
insert into llx_c_paiement (id,code,libelle,type) values (6, 'CB',  'Carte Bancaire', 1);
insert into llx_c_paiement (id,code,libelle,type) values (7, 'CHQ', 'Ch�que', 2);

drop table if exists llx_c_pays;

create table llx_c_pays
(
  rowid    integer     PRIMARY KEY,
  code     varchar(6)  UNIQUE NOT NULL,
  libelle  varchar(25)        NOT NULL,
  active   tinyint default 1  NOT NULL
)type=innodb;

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

drop table if exists llx_c_propalst;

create table llx_c_propalst
(
  id              smallint    PRIMARY KEY,
  code            varchar(12)  UNIQUE NOT NULL,
  label           varchar(30),
  active          tinyint default 1  NOT NULL
)type=innodb;

delete from llx_c_propalst;
insert into llx_c_propalst (id,code,label) values (0, 'PR_DRAFT',     'Brouillon');
insert into llx_c_propalst (id,code,label) values (1, 'PR_OPEN',      'Ouverte');
insert into llx_c_propalst (id,code,label) values (2, 'PR_SIGNED',    'Sign�e');
insert into llx_c_propalst (id,code,label) values (3, 'PR_NOTSIGNED', 'Non Sign�e');
insert into llx_c_propalst (id,code,label) values (4, 'PR_FAC',       'Factur�e');

drop table if exists llx_c_stcomm;

create table llx_c_stcomm
(
  id       integer     PRIMARY KEY,
  code     varchar(12)  UNIQUE NOT NULL,
  libelle  varchar(30),
  active   tinyint default 1  NOT NULL
)type=innodb;

delete from llx_c_stcomm;
insert into llx_c_stcomm (id,code,libelle) values (-1, 'ST_NO',    'Ne pas contacter');
insert into llx_c_stcomm (id,code,libelle) values ( 0, 'ST_NEVER', 'Jamais contact�');
insert into llx_c_stcomm (id,code,libelle) values ( 1, 'ST_TODO',  'A contacter');
insert into llx_c_stcomm (id,code,libelle) values ( 2, 'ST_PEND',  'Contact en cours');
insert into llx_c_stcomm (id,code,libelle) values ( 3, 'ST_DONE',  'Contact�e');

drop table if exists llx_c_typent;

create table llx_c_typent
(
  id        integer     PRIMARY KEY,
  code      varchar(12)  UNIQUE NOT NULL,
  libelle   varchar(30),
  active    tinyint default 1  NOT NULL
)type=innodb;

delete from llx_c_typent;
insert into llx_c_typent (id,code,libelle) values (  0, 'TE_UNKNOWN', 'Indiff�rent');
insert into llx_c_typent (id,code,libelle) values (  1, 'TE_STARTUP', 'Start-up');
insert into llx_c_typent (id,code,libelle) values (  2, 'TE_GROUP',   'Grand groupe');
insert into llx_c_typent (id,code,libelle) values (  3, 'TE_MEDIUM',  'PME/PMI');
insert into llx_c_typent (id,code,libelle) values (  4, 'TE_ADMIN',   'Administration');
insert into llx_c_typent (id,code,libelle) values (100, 'TE_OTHER',   'Autres');

drop table if exists llx_c_regions;

create table llx_c_regions
(
  rowid       integer AUTO_INCREMENT PRIMARY KEY,
  code_region integer UNIQUE NOT NULL,
  fk_pays     integer NOT NULL,
  cheflieu    varchar(7),
  tncc        integer,
  nom         varchar(50),
  active      tinyint default 1 NOT NULL
)type=innodb;

delete from llx_c_regions;
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (0,0,0,'0',0,'-');
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

insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (201,2,201,'',1,'Flandre');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (202,2,202,'',2,'Wallonie');
insert into llx_c_regions (rowid,fk_pays,code_region,cheflieu,tncc,nom) values (203,2,203,'',3,'Bruxelles-Capitale');

