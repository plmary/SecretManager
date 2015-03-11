ALTER TABLE `idn_identities` ADD `idn_api` TINYINT NOT NULL DEFAULT '0' AFTER `idn_operator`;
INSERT INTO `cvl_civilities` (`cvl_last_name`, `cvl_first_name`, `cvl_sex`, `cvl_birth_date`, `cvl_born_town`) VALUES
('API', 'Utilisateur', 0, '0000-00-00', '');
