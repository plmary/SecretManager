
CREATE TABLE app_applications (
                app_id BIGINT AUTO_INCREMENT NOT NULL,
                app_name VARCHAR(100) NOT NULL,
                PRIMARY KEY (app_id)
);

CREATE UNIQUE INDEX app_idx_1
 ON app_applications
 ( app_name );


ALTER TABLE scr_secrets ADD COLUMN app_id BIGINT;

ALTER TABLE scr_secrets ADD CONSTRAINT app_applications_scr_secrets_fk
FOREIGN KEY (app_id)
REFERENCES app_applications (app_id)
ON DELETE SET NULL
ON UPDATE NO ACTION;
