--
-- $Id$
--
-- Attention � l ordre des requetes.
-- Ce fichier doit �tre charg� sur une version 2.4.0 
--

alter table llx_product add column   price_min          double(24,8) DEFAULT 0;
alter table llx_product add column   price_min_ttc      double(24,8) DEFAULT 0;

alter table llx_product_price   add column price_min              double(24,8) default NULL;
alter table llx_product_price   add column price_min_ttc          double(24,8) default NULL;

alter table llx_societe add column gencod			 varchar(255);

delete from llx_user_param where page <> '';

alter table llx_expedition add tracking_number varchar(50) after fk_expedition_methode;
