-- $Revision$
--
-- Attention � l ordre des requetes
-- ce fichier doit �tre charg� sur une version 2.0.0 
-- sans AUCUNE erreur ni warning
-- 


create table llx_commande_model_pdf
(
  nom         varchar(50) PRIMARY KEY,
  libelle     varchar(255),
  description text
)type=innodb;


alter table llx_commande add column note_public text after note;

alter table llx_contrat add column note text;
alter table llx_contrat add column note_public text after note;

alter table llx_facture add column note_public text after note;

alter table llx_propal add column note_public text after note;

ALTER TABLE llx_societe ADD mode_reglement INT( 11 ) DEFAULT NULL ;
ALTER TABLE llx_societe ADD cond_reglement INT( 11 ) DEFAULT '1' NOT NULL ;

alter table llx_product add gencode varchar(255) DEFAULT NULL;

insert into llx_cond_reglement(rowid, code, sortorder, active, libelle, libelle_facture, fdm, nbjour) values (6,'PROFORMA',    6,1, 'Proforma','R�glement avant livraison',0,0);

alter table llx_commande add fk_cond_reglement int(11) DEFAULT NULL;
alter table llx_commande add fk_mode_reglement int(11) DEFAULT NULL;
