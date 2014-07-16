-- Author  : Pierre-Luc MARY
-- Date    : 2014-06-19
-- Base    : secret_manager
-- Model   : 1.5-0

DROP DATABASE IF EXISTS secret_manager;
CREATE DATABASE secret_manager DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci;
USE secret_manager;


CREATE TABLE hac_history_actions_codes (
                hac_id BIGINT AUTO_INCREMENT NOT NULL,
                hac_name VARCHAR(30) NOT NULL,
                PRIMARY KEY (hac_id)
);

CREATE UNIQUE INDEX hac_idx_1
 ON hac_history_actions_codes
 ( hac_name );


CREATE TABLE app_applications (
                app_id BIGINT AUTO_INCREMENT NOT NULL,
                app_name VARCHAR(100) NOT NULL,
                PRIMARY KEY (app_id)
);


CREATE UNIQUE INDEX app_idx_1
 ON app_applications
 ( app_name );

CREATE TABLE spr_system_parameters (
                spr_id BIGINT AUTO_INCREMENT NOT NULL,
                spr_name VARCHAR(30) NOT NULL,
                spr_value VARCHAR(60) NOT NULL,
                PRIMARY KEY (spr_id)
);


CREATE UNIQUE INDEX spr_idx
 ON spr_system_parameters
 ( spr_name );

CREATE TABLE env_environments (
                env_id BIGINT AUTO_INCREMENT NOT NULL,
                env_name VARCHAR(30) NOT NULL,
                PRIMARY KEY (env_id)
);


CREATE UNIQUE INDEX env_idx
 ON env_environments
 ( env_name );

CREATE TABLE ent_entities (
                ent_id BIGINT AUTO_INCREMENT NOT NULL,
                ent_code VARCHAR(10) NOT NULL,
                ent_label VARCHAR(60) NOT NULL,
                ent_logical_delete BOOLEAN DEFAULT 0 NOT NULL,
                PRIMARY KEY (ent_id)
);


CREATE UNIQUE INDEX ent_idx
 ON ent_entities
 ( ent_code );

CREATE UNIQUE INDEX ent_idx1
 ON ent_entities
 ( ent_label );

CREATE TABLE cvl_civilities (
                cvl_id BIGINT AUTO_INCREMENT NOT NULL,
                cvl_last_name VARCHAR(35) NOT NULL,
                cvl_first_name VARCHAR(25) NOT NULL,
                cvl_sex BOOLEAN DEFAULT false NOT NULL,
                cvl_birth_date DATE,
                cvl_born_town VARCHAR(60),
                cvl_logical_delete BOOLEAN DEFAULT 0 NOT NULL,
                PRIMARY KEY (cvl_id)
);


CREATE UNIQUE INDEX cvl_idx
 ON cvl_civilities
 ( cvl_last_name, cvl_first_name );

CREATE TABLE rgh_rights (
                rgh_id BIGINT NOT NULL,
                rgh_name VARCHAR(30) NOT NULL,
                PRIMARY KEY (rgh_id)
);


CREATE UNIQUE INDEX rgh_idx
 ON rgh_rights
 ( rgh_name );

CREATE TABLE stp_secret_types (
                stp_id BIGINT AUTO_INCREMENT NOT NULL,
                stp_name VARCHAR(30) NOT NULL,
                PRIMARY KEY (stp_id)
);


CREATE UNIQUE INDEX stp_idx
 ON stp_secret_types
 ( stp_name );

CREATE TABLE prf_profiles (
                prf_id BIGINT AUTO_INCREMENT NOT NULL,
                prf_label VARCHAR(60),
                PRIMARY KEY (prf_id)
);


CREATE UNIQUE INDEX profils_idx
 ON prf_profiles
 ( prf_label );

CREATE TABLE idn_identities (
                idn_id BIGINT AUTO_INCREMENT NOT NULL,
                ent_id BIGINT NOT NULL,
                cvl_id BIGINT NOT NULL,
                idn_login VARCHAR(20) NOT NULL,
                idn_authenticator CHAR(64) NOT NULL,
                idn_salt VARCHAR(32) NOT NULL,
                idn_change_authenticator BOOLEAN DEFAULT true NOT NULL,
                idn_super_admin BOOLEAN DEFAULT false NOT NULL,
                idn_operator BOOLEAN DEFAULT false NOT NULL,
                idn_attempt SMALLINT DEFAULT 0 NOT NULL,
                idn_disable BOOLEAN DEFAULT false NOT NULL,
                idn_logical_delete BOOLEAN DEFAULT 0 NOT NULL,
                idn_last_connection DATETIME NOT NULL,
                idn_expiration_date DATETIME NOT NULL,
                idn_updated_authentication DATETIME NOT NULL,
                PRIMARY KEY (idn_id)
);


CREATE UNIQUE INDEX idn_idx
 ON idn_identities
 ( idn_login );

CREATE TABLE idpr_identities_profiles (
                idn_id BIGINT NOT NULL,
                prf_id BIGINT NOT NULL,
                idpr_logical_delete BOOLEAN DEFAULT 0 NOT NULL,
                PRIMARY KEY (idn_id, prf_id)
);


CREATE TABLE sgr_secrets_groups (
                sgr_id BIGINT AUTO_INCREMENT NOT NULL,
                sgr_label VARCHAR(60),
                sgr_alert INT DEFAULT 0 NOT NULL,
                PRIMARY KEY (sgr_id)
);


CREATE UNIQUE INDEX secrets_groups_idx
 ON sgr_secrets_groups
 ( sgr_label );

CREATE TABLE prsg_profiles_secrets_groups (
                prf_id BIGINT NOT NULL,
                sgr_id BIGINT NOT NULL,
                rgh_id BIGINT NOT NULL,
                PRIMARY KEY (prf_id, sgr_id, rgh_id)
);


CREATE TABLE scr_secrets (
                scr_id BIGINT AUTO_INCREMENT NOT NULL,
                sgr_id BIGINT NOT NULL,
                stp_id BIGINT NOT NULL,
                env_id BIGINT NOT NULL,
                app_id BIGINT,
                idn_id BIGINT,
                scr_host VARCHAR(255) NOT NULL,
                scr_user VARCHAR(100) NOT NULL,
                scr_password LONGBLOB NOT NULL,
                scr_comment VARCHAR(100),
                scr_alert INT DEFAULT 0 NOT NULL,
                scr_creation_date DATETIME NOT NULL,
                scr_modification_date DATETIME NOT NULL,
                scr_expiration_date DATETIME,
                PRIMARY KEY (scr_id)
);


CREATE UNIQUE INDEX scr_idx
 ON scr_secrets
 ( scr_host, scr_user );

CREATE TABLE ach_access_history (
                ach_id BIGINT AUTO_INCREMENT NOT NULL,
                scr_id BIGINT,
                idn_id BIGINT,
                rgh_id BIGINT,
                hac_id BIGINT,
                ach_gravity_level INT,
                ach_date DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
                ach_ip VARCHAR(40),
                ach_access VARCHAR(300) NOT NULL,
                PRIMARY KEY (ach_id)
);


ALTER TABLE ach_access_history ADD CONSTRAINT hac_ach_fk
FOREIGN KEY (hac_id)
REFERENCES hac_history_actions_codes (hac_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE scr_secrets ADD CONSTRAINT app_scr_fk
FOREIGN KEY (app_id)
REFERENCES app_applications (app_id)
ON DELETE SET NULL
ON UPDATE NO ACTION;

ALTER TABLE scr_secrets ADD CONSTRAINT env_scr_fk
FOREIGN KEY (env_id)
REFERENCES env_environments (env_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE idn_identities ADD CONSTRAINT ent_idn_fk
FOREIGN KEY (ent_id)
REFERENCES ent_entities (ent_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE idn_identities ADD CONSTRAINT cvl_idn_fk
FOREIGN KEY (cvl_id)
REFERENCES cvl_civilities (cvl_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE prsg_profiles_secrets_groups ADD CONSTRAINT rgh_prpg_fk
FOREIGN KEY (rgh_id)
REFERENCES rgh_rights (rgh_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE scr_secrets ADD CONSTRAINT stp_scr_fk
FOREIGN KEY (stp_id)
REFERENCES stp_secret_types (stp_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE idpr_identities_profiles ADD CONSTRAINT idpr_prf_fk
FOREIGN KEY (prf_id)
REFERENCES prf_profiles (prf_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE prsg_profiles_secrets_groups ADD CONSTRAINT prf_prpg_fk
FOREIGN KEY (prf_id)
REFERENCES prf_profiles (prf_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE idpr_identities_profiles ADD CONSTRAINT idn_idpr_fk
FOREIGN KEY (idn_id)
REFERENCES idn_identities (idn_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE scr_secrets ADD CONSTRAINT sgr_scr_fk
FOREIGN KEY (sgr_id)
REFERENCES sgr_secrets_groups (sgr_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE prsg_profiles_secrets_groups ADD CONSTRAINT sgr_prpg_fk
FOREIGN KEY (sgr_id)
REFERENCES sgr_secrets_groups (sgr_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;
