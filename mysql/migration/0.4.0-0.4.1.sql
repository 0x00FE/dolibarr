
--
-- Mise � jour de la version 0.4.0 � 0.4.1
--

alter table llx_user add fk_socpeople integer default 0;
alter table llx_socpeople add fk_user integer default 0;