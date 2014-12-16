-- Author  : Pierre-Luc MARY
-- Date    : 2014-12-10
-- Base    : secret_manager
-- Model   : 1.6-1

USE secret_manager;


ALTER TABLE scr_secrets ADD CONSTRAINT env_scr_fk
FOREIGN KEY (env_id)
REFERENCES env_environments (env_id)
ON DELETE RESTRICT
ON UPDATE NO ACTION;

ALTER TABLE idn_identities ADD CONSTRAINT ent_idn_fk
FOREIGN KEY (ent_id)
REFERENCES ent_entities (ent_id)
ON DELETE CASCADE
ON UPDATE NO ACTION;

ALTER TABLE idn_identities ADD CONSTRAINT cvl_idn_fk
FOREIGN KEY (cvl_id)
REFERENCES cvl_civilities (cvl_id)
ON DELETE CASCADE
ON UPDATE NO ACTION;

ALTER TABLE prsg_profiles_secrets_groups ADD CONSTRAINT rgh_prsg_fk
FOREIGN KEY (rgh_id)
REFERENCES rgh_rights (rgh_id)
ON DELETE CASCADE
ON UPDATE NO ACTION;

ALTER TABLE scr_secrets ADD CONSTRAINT stp_scr_fk
FOREIGN KEY (stp_id)
REFERENCES stp_secret_types (stp_id)
ON DELETE RESTRICT
ON UPDATE NO ACTION;

ALTER TABLE idpr_identities_profiles ADD CONSTRAINT idpr_prf_fk
FOREIGN KEY (prf_id)
REFERENCES prf_profiles (prf_id)
ON DELETE CASCADE
ON UPDATE NO ACTION;

ALTER TABLE prsg_profiles_secrets_groups ADD CONSTRAINT prf_prsg_fk
FOREIGN KEY (prf_id)
REFERENCES prf_profiles (prf_id)
ON DELETE CASCADE
ON UPDATE NO ACTION;

ALTER TABLE idpr_identities_profiles ADD CONSTRAINT idn_idpr_fk
FOREIGN KEY (idn_id)
REFERENCES idn_identities (idn_id)
ON DELETE CASCADE
ON UPDATE NO ACTION;

ALTER TABLE prsg_profiles_secrets_groups ADD CONSTRAINT sgr_prsg_fk
FOREIGN KEY (sgr_id)
REFERENCES sgr_secrets_groups (sgr_id)
ON DELETE CASCADE
ON UPDATE NO ACTION;
