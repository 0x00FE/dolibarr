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