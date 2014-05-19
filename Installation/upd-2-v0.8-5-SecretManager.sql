-- Gestion des Connexions et Déconnexions des Utilisateurs
INSERT INTO secret_manager.hac_history_actions_codes (hac_id, hac_name) VALUES
	(NULL, 'L_ALERT_CNX'),
	(NULL, 'L_ALERT_DCNX');


-- Types d'objet dans le SecretManager
INSERT INTO secret_manager.hac_history_actions_codes (hac_id, hac_name) VALUES 
	(NULL, 'L_ALERT_APP'), -- Alerte sur les Applications
	(NULL, 'L_ALERT_CVL'), -- Alerte sur les Civilités
	(NULL, 'L_ALERT_ENT'), -- Alerte sur les Entités
	(NULL, 'L_ALERT_IDN'), -- Alerte sur les Identités
	(NULL, 'L_ALERT_IDPR'), -- Alerte sur les Relations entre les Identités et les Profils
	(NULL, 'L_ALERT_PRF'), -- Alerte sur les Profils
	(NULL, 'L_ALERT_PRSG'), -- Alerte sur les Relations entre les Profils et les Groupes de Secrets
	(NULL, 'L_ALERT_SCR'), -- Alerte sur les Secrets
	(NULL, 'L_ALERT_SGR'), -- Alerte sur les Groupes de Secrets
	(NULL, 'L_ALERT_SPR'), -- Alerte sur les Paramètres Systèmes
	(NULL, 'L_ALERT_HST'), -- Alerte sur l'Historique
	(NULL, 'L_ALERT_MK'), -- Alerte sur Clé Mère
	(NULL, 'L_ALERT_SS'), -- Alerte sur SecretServer
	(NULL, 'L_ALERT_BCK'), -- Alerte sur Sauvegarde
	(NULL, 'L_ALERT_RSTR'); -- Alerte sur Restauration
	