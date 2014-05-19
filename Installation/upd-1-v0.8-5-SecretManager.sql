-- Création de la nouvelle table qui distingue les types objets ayant été accédés par des utilisateurs.
CREATE TABLE hac_history_actions_codes (
                hac_id BIGINT AUTO_INCREMENT NOT NULL,
                hac_name VARCHAR(30) NOT NULL,
                PRIMARY KEY (hac_id)
);

CREATE UNIQUE INDEX hac_idx_1
 ON hac_history_actions_codes
 ( hac_name );


-- Modifie la table de suivi des accès aux objets de SecretManager.
ALTER TABLE ach_access_history ADD rgh_id BIGINT NULL AFTER idn_id, ADD hot_id BIGINT NOT NULL AFTER rgh_id;
ALTER TABLE ach_access_history CHANGE ach_access ach_access VARCHAR(300) CHARACTER SET utf8 COLLATE utf8_general_ci NULL;
ALTER TABLE ach_access_history ADD hac_id BIGINT NULL AFTER rgh_id;
ALTER TABLE ach_access_history ADD ach_gravity_level INT NULL AFTER hac_id;

-- Créé les relations de cohérence entre les tables.
ALTER TABLE ach_access_history ADD CONSTRAINT rgh_ach_fk
FOREIGN KEY (rgh_id)
REFERENCES rgh_rights (rgh_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;

ALTER TABLE ach_access_history ADD CONSTRAINT hac_ach_fk
FOREIGN KEY (hac_id)
REFERENCES hac_history_actions_codes (hac_id)
ON DELETE NO ACTION
ON UPDATE NO ACTION;
