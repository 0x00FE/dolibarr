
--
-- Mise � jour de la version 0.3.0 � 0.4.0
--

alter table llx_product add fk_product_type integer default 0 ;
alter table llx_product add duration varchar(6) ;