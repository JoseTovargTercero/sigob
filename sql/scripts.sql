
INSERT INTO `menu` (`id`, `oficina`, `categoria`, `nombre`, `dir`, `icono`) VALUES (NULL, 'pl_formulacion', NULL, 'Sectores', 'mod_pl_formulacion/form_sectores', 'bx-objects-horizontal-right');
ALTER TABLE `proyecto_inversion_partidas` ADD `sector_id` VARCHAR(255) NULL DEFAULT NULL AFTER `monto`;