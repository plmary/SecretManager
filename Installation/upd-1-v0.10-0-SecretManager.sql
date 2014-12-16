REVOKE ALL PRIVILEGES ON `secret\_manager`.* FROM 'iica_user'@'localhost';
GRANT SELECT, INSERT, UPDATE, DELETE, ALTER, DROP ON `secret\_manager`.* TO 'iica_user'@'localhost';

ALTER TABLE `ach_access_history` CHANGE `ach_access` `ach_access` VARCHAR(300) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL;

CREATE TABLE shs_secrets_history (
                shs_id BIGINT AUTO_INCREMENT NOT NULL,
                scr_id BIGINT NOT NULL,
                shs_password LONGBLOB NOT NULL,
                shs_last_date_use DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL,
                PRIMARY KEY (shs_id)
);
