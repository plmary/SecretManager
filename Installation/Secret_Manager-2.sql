SET SQL_MODE="NO_AUTO_VALUE_ON_ZERO";
SET AUTOCOMMIT=0;
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8 */;

--
-- Base de données: `Secret_Manager`
--


--
-- Contenu de la table `cvl_civilities`
--

INSERT INTO `cvl_civilities` (`cvl_id`, `cvl_last_name`, `cvl_first_name`, `cvl_sex`, `cvl_birth_date`, `cvl_born_town`, `cvl_logical_delete`) VALUES(1, 'de l\\''Outil', 'Administrateur', 0, '0000-00-00', '', 0);

--
-- Contenu de la table `ent_entities`
--

INSERT INTO `ent_entities` (`ent_id`, `ent_code`, `ent_label`, `ent_logical_delete`) VALUES(1, 'ORA', 'Orasys', 0);

--
-- Contenu de la table `env_environments`
--

INSERT INTO `env_environments` (`env_id`, `env_name`) VALUES(1, 'L_Environment_1');
INSERT INTO `env_environments` (`env_id`, `env_name`) VALUES(2, 'L_Environment_2');
INSERT INTO `env_environments` (`env_id`, `env_name`) VALUES(3, 'L_Environment_3');
INSERT INTO `env_environments` (`env_id`, `env_name`) VALUES(4, 'L_Environment_4');

--
-- Contenu de la table `idn_identities`
--

INSERT INTO `idn_identities` (`idn_id`, `ent_id`, `cvl_id`, `idn_login`, `idn_authenticator`, `idn_salt`, `idn_change_authenticator`, `idn_super_admin`, `idn_auditor`, `idn_attempt`, `idn_disable`, `idn_logical_delete`, `idn_last_connection`, `idn_expiration_date`, `idn_updated_authentication`) VALUES(1, 1, 1, 'root', '658be8288e1156eccbcf7c62a8d731d55fe81e7d', '0.X-n:A/9', 0, 1, 0, 0, 0, 0, '2012-11-13 22:44:46', '2013-04-29 00:00:00', '2012-10-29 21:10:48');


--
-- Contenu de la table `rgh_rights`
--

INSERT INTO `rgh_rights` (`rgh_id`, `rgh_name`) VALUES(1, 'L_Right_1');
INSERT INTO `rgh_rights` (`rgh_id`, `rgh_name`) VALUES(2, 'L_Right_2');
INSERT INTO `rgh_rights` (`rgh_id`, `rgh_name`) VALUES(3, 'L_Right_3');
INSERT INTO `rgh_rights` (`rgh_id`, `rgh_name`) VALUES(4, 'L_Right_4');


--
-- Contenu de la table `spr_system_parameters`
--

INSERT INTO `spr_system_parameters` (`spr_name`, `spr_value`) VALUES('alert_mail', '0');
INSERT INTO `spr_system_parameters` (`spr_name`, `spr_value`) VALUES('alert_syslog', '1');
INSERT INTO `spr_system_parameters` (`spr_name`, `spr_value`) VALUES('authentication_type', 'D');
INSERT INTO `spr_system_parameters` (`spr_name`, `spr_value`) VALUES('expiration_time', '60');
INSERT INTO `spr_system_parameters` (`spr_name`, `spr_value`) VALUES('mail_from', 'alert_SecretManager@societe.com');
INSERT INTO `spr_system_parameters` (`spr_name`, `spr_value`) VALUES('mail_to', 'admin@societe.com');
INSERT INTO `spr_system_parameters` (`spr_name`, `spr_value`) VALUES('verbosity_alert', '1');
INSERT INTO `spr_system_parameters` (`spr_name`, `spr_value`) VALUES('use_SecretServer', '1');

--
-- Contenu de la table `stp_secret_types`
--

INSERT INTO `stp_secret_types` (`stp_id`, `stp_name`) VALUES(1, 'L_Secret_Type_1');
INSERT INTO `stp_secret_types` (`stp_id`, `stp_name`) VALUES(2, 'L_Secret_Type_2');

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
