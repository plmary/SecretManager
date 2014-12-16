<?php

include_once( 'Constants.inc.php' );

include_once( IICA_LIBRARIES . '/Class_IICA_DB_Connector_PDO.inc.php' );
include_once( IICA_LIBRARIES . '/Class_IICA_Groups_PDO.inc.php' );
include_once( IICA_LIBRARIES . '/Class_IICA_Referentials_PDO.inc.php' );

include( DIR_LIBRARIES . '/Config_Access_Tables.inc.php' );


class IICA_Secrets extends IICA_DB_Connector {
/**
* Cette classe gère les secrets.
*
* PHP version 5
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @date 2014-06-24
*/
    public $LastInsertId;

	/* ===============================
	** Connexion à la base de données.
	*/
	public function __construct() {
		parent::__construct();
		
		return true;
	}


	/* ===============================================================================
	** Gestion des Secrets
	*/

	public function set( $scr_id, $sgr_id, $stp_id, $scr_host, $scr_user, $scr_password,
	 $scr_comment, $scr_alert, $env_id, $app_id, $scr_expiration_date = NULL, $idn_id = NULL ) {
	/**
	* Crée ou modifie un Secret.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-02-18
	*
	* @param[in] $scr_id Identifiant du Secret à modifier (quand précisé)
	* @param[in] $sgr_id Identifiant du Groupe de Secrets auquel le Secret peut être associé
	* @param[in] $stp_id Identifiant du Type de Secrets constituant le Secret
	* @param[in] $scr_host Serveur constituant le Secret
	* @param[in] $scr_user Utilisateur constituant le Secret
	* @param[in] $scr_password Mot de passe constituant le Secret
	* @param[in] $scr_comment Commentaire donnant des informations sur le Secret
	* @param[in] $scr_alert Drapeau indiquant si ce Secret doit explicitement être supervisé en traçabilité
	* @param[in] $env_id Environnement auquel peut être rattaché le Secret
	* @param[in] $app_id Application à laquelle peut être rattaché le Secret
	* @param[in] $scr_expiration_date Date d'expiration calculée par défaut pour le Secret (Date du jour  + Nombre de mois définit dans les préférences)
	* @param[in] $idn_id Identifiant de l'identité auquel est rattaché le Secret (si précisé et dans ce cas nous parlons de Secret Personnel)
	*
	* @return Renvoi vrai si le Secret a été mis à jour ou créé, sinon lève une exception
	*/
		include_once( DIR_LIBRARIES . '/Class_Security.inc.php' );

		include_once( DIR_LIBRARIES . '/Class_IICA_Parameters_PDO.inc.php' );

		include_once( DIR_LIBRARIES . '/Class_Secrets_Server.inc.php' );
		
		include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-secrets-server.php' );
		include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_labels_generic.php' );

		$Security = new Security();

		$Parameters = new IICA_Parameters();

		$Secret_Server = new Secret_Server();

		
		$Integrity_Status = $Security->checkFilesIntegrity();
		if ( $Integrity_Status[0] == FALSE ) {
			if ( $Security->getParameter('stop_secret_server_on_alert') == 1 ) {
				$Secret_Server->SS_Shutdown();
			}

			$Files = '';

			foreach( $Integrity_Status[ 1 ] as $File ) {
				if ( $Files != '' ) $Files .= '<br/>';

				$Files .= $File;
			}

			$Files = '<br/>(' . $Files . ')';

			throw new Exception( '<p class="alert">'. $L_Files_Integrity_Alert . '</p><p>' . $Files . '</p>', 1 );
		}

		$DateCourante = date( 'Y-m-d H:i:s' );
		
		if ( $scr_id == '' ) {
			$Request = 'INSERT INTO scr_secrets ' .
				'( sgr_id, stp_id, scr_host, scr_user, scr_password, scr_comment, ' .
				'scr_alert, scr_creation_date, env_id, app_id, scr_expiration_date, idn_id ) ' .
				'VALUES ( :sgr_id, :stp_id, :scr_host, :scr_user, :scr_password, ' .
				':scr_comment, :scr_alert, "' . $DateCourante . '", :env_id, :app_id, ' .
				':scr_expiration_date, :idn_id ) ';

			if ( ! $Result = $this->prepare( $Request ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		} else {
			$Request = 'UPDATE scr_secrets SET ' .
				'scr_id = :scr_id, sgr_id = :sgr_id, stp_id = :stp_id, scr_host = :scr_host, ' .
				'scr_user = :scr_user, scr_password = :scr_password, scr_comment = :scr_comment, ' .
				'scr_alert = :scr_alert, scr_modification_date = "' . $DateCourante . '", ' .
				'env_id = :env_id, app_id = :app_id, scr_expiration_date = :scr_expiration_date, ' .
				'idn_id = :idn_id ' .
				'WHERE scr_id = :scr_id';

			if ( ! $Result = $this->prepare( $Request ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
			
			if ( ! $Result->bindParam( ':scr_id', $scr_id, PDO::PARAM_INT ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}

		if ( ! $Result->bindParam( ':sgr_id', $sgr_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->bindParam( ':stp_id', $stp_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
				
		if ( ! $Result->bindParam( ':scr_host', $scr_host, PDO::PARAM_STR, 255 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
				
		if ( ! $Result->bindParam( ':scr_user', $scr_user, PDO::PARAM_STR, 100 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->bindParam( ':env_id', $env_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->bindParam( ':app_id', $app_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		

		// =================================
		// Chiffrement du secret.
		if ( $Parameters->getParameter( 'use_SecretServer' ) == '1' ) {
			try {
				$Encrypted = $Secret_Server->SS_encryptValue( $scr_password );
			} catch( Exception $e ) {
				$Error = $e->getMessage();
				if ( isset( ${$Error} ) ) $Error = ${$Error};
				
				throw new Exception( $Error );
			}
		} else {
			$Encrypted = $Security->mc_encrypt( $scr_password );
		}


		if ( ! $Result->bindParam( ':scr_password', $Encrypted,
		 PDO::PARAM_LOB ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
				
		if ( ! $Result->bindParam( ':scr_comment', $scr_comment, PDO::PARAM_STR, 100 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
				
		if ( ! $Result->bindParam( ':scr_alert', $scr_alert, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->bindParam( ':scr_expiration_date', $scr_expiration_date, PDO::PARAM_STR, 19 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
				
		if ( ! $Result->bindParam( ':idn_id', $idn_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( $scr_id == '' ) {
			switch( $this->getAttribute(PDO::ATTR_DRIVER_NAME) ) {
			 default;
				$this->LastInsertId = $this->lastInsertId();
				break;

			 case 'pgsql';
				$this->LastInsertId = $this->lastInsertId( 'scr_secrets_scr_id_seq' );
				break;
			}
		}


		// =========================================
		// Mise à jour de l'historique des Secrets.
		$Request = 'INSERT INTO shs_secrets_history ' .
			'( scr_id, shs_password, shs_last_date_use ) ' .
			'VALUES ( :scr_id, :scr_password, "' . $DateCourante . '" ) ';

		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( $scr_id == '' ) {
			if ( ! $Result->bindParam( ':scr_id', $this->LastInsertId, PDO::PARAM_INT ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		} else {
			if ( ! $Result->bindParam( ':scr_id', $scr_id, PDO::PARAM_INT ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}


		if ( ! $Result->bindParam( ':scr_password', $Encrypted,
		 PDO::PARAM_LOB ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}


		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		
		return true;
	}

	
	public function transcrypt( $old_Mother_Key, $new_Mother_Key ) {
	/**
	* Déchiffre le Secret avec l'ancienne clé mère et rechiffre le Secret avec la nouvelle clé mère (opération de transchiffrement).
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-02-18
	*
	* @param[in] $old_Mother_Key Ancienne clé Mère
	* @param[in] $new_Mother_Key Nouvelle clé Mère
	*
	* @return Renvoi vrai si le Profil a été créé ou mis à jour, sinon lève une exception
	*/
		include_once( DIR_LIBRARIES . '/Class_Security.inc.php' );

		include_once( DIR_LIBRARIES . '/Class_IICA_DB_Connector_PDO.inc.php' );

		if ( ! isset( $_SESSION[ 'Language' ] ) ) $_SESSION[ 'Language' ] = 'en';

		include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-secrets-server.php' );
		

		$Security = new Security();

		$DB_Connect = new IICA_DB_Connector();

		
		if ( ! $Result = $DB_Connect->prepare( 'SELECT scr_id, scr_password FROM scr_secrets ' ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		/*
		** Démarre la transaction.
		*/
		$DB_Connect->beginTransaction();
		
		while ( $Occurrence = $Result->fetchObject() ) {
			$Decrypted = $Security->mc_decrypt( $Occurrence->scr_password, $old_Mother_Key );
			$Encrypted = $Security->mc_encrypt( $Decrypted, $new_Mother_Key );

			if ( ! $Updater = $this->prepare( 'UPDATE scr_secrets SET ' .
				'scr_password = :scr_password ' .
				'WHERE scr_id = :scr_id ' ) ) {
				$Error = $Updater->errorInfo();

				$DB_Connect->rollBack();

				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
			
			if ( ! $Updater->bindParam( ':scr_id', $Occurrence->scr_id, PDO::PARAM_INT ) ) {
				$Error = $Updater->errorInfo();

				$DB_Connect->rollBack();

				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
			
			if ( ! $Updater->bindParam( ':scr_password', $Encrypted, PDO::PARAM_LOB ) ) {
				$Error = $Updater->errorInfo();

				$DB_Connect->rollBack();

				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}

			if ( ! $Updater->execute() ) {
				$Error = $Updater->errorInfo();

				$DB_Connect->rollBack();

				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}

        $DB_Connect->commit();
		
		return true;
	}


	public function listSecrets( $sgr_id = '', $idn_id = '', $stp_id = '', $env_id = '',
	 $app_id = '', $scr_host = '', $scr_user = '', $scr_comment = '',
	 $Administrator = false, $orderBy = '' ) {
	/**
	* Lister les Secrets (ne fournit que les informations générales des Secrets).
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2014-12-08
	*
	* @param[in] $sgr_id Id. du Groupe de Secret précisé comme critère de recherche pour les Secrets
	* @param[in] $idn_id Id. de l'Identité précisé comme critère de recherche pour les Secrets
	* @param[in] $stp_id Id. du Type de Secret précisé comme critère de recherche pour les Secrets
	* @param[in] $env_id Id. de l'Environnement de Secret précisé comme critère de recherche pour les Secrets
	* @param[in] $app_id Id. de l'Application précisé comme critère de recherche pour les Secrets
	* @param[in] $scr_host Nom du Serveur précisé comme critère de recherche pour les Secrets
	* @param[in] $scr_user Nom de l'Utilisateur précisé comme critère de recherche pour les Secrets
	* @param[in] $scr_comment Commentaire précisé comme critère de recherche pour les Secrets
	* @param[in] $Administrator Drapeau pour préciser si l'utilisateur est Administrateur
	* @param[in] $orderBy Précise la préférence de tri en restitution de l'information
	*
	* @return Renvoi un tableau d'objet de Secrets, sinon lève une exception
	*/
		$Data = false;
		
		$Where = '';

		if ( $sgr_id != '' ) {
			$Where = 'WHERE T1.sgr_id = :sgr_id ';
		}

		if ( $idn_id != '' and $Administrator == false ) {
			if ( $Where == '' ) $Where = 'WHERE T6.idn_id = :idn_id ';
			else $Where .= 'AND T6.idn_id = :idn_id ';
				
		} //else $Where = 'WHERE ( T1.idn_id IS NULL OR T1.idn_id = 0 OR T1.idn_id = :idn_id ) ';

		
		if ( $stp_id != '' ) {
			if ( $Where == '' ) $Where = 'WHERE T1.stp_id = :stp_id ';
			else $Where .= 'AND T1.stp_id = :stp_id ';
			
		}

		if ( $env_id != '' ) {
			if ( $Where == '' ) $Where = 'WHERE T1.env_id = :env_id ';
			else $Where .= 'AND T1.env_id = :env_id ';
			
		}

		if ( $app_id != '' ) {
			if ( $Where == '' )
				$Where = 'WHERE T1.app_id like :app_id ';
			else
				$Where .= 'AND T1.app_id like :app_id ';
			
		}

		if ( $scr_host != '' ) {
			if ( $Where == '' ) $Where = 'WHERE T1.scr_host like :scr_host ';
			else $Where .= 'AND T1.scr_host like :scr_host ';
			
		}

		if ( $scr_user != '' ) {
			if ( $Where == '' ) $Where = 'WHERE T1.scr_user like :scr_user ';
			else $Where .= 'AND T1.scr_user like :scr_user ';
			
		}

		if ( $scr_comment != '' ) {
			if ( $Where == '' ) $Where = 'WHERE T1.scr_comment like :scr_comment ';
			else $Where .= 'AND T1.scr_comment like :scr_comment ';
			
		}

		if ( $Where == '' ) $Where = 'WHERE T1.idn_id IS NULL OR T1.idn_id = 0 OR T1.idn_id = ' . $_SESSION['idn_id'] . ' ';
		else $Where .= 'AND ( T1.idn_id IS NULL OR T1.idn_id = 0 OR T1.idn_id = ' . $_SESSION['idn_id'] . ' ) ';

		$Request = 'SELECT DISTINCT ' .
		 'scr_id, T1.app_id, app_name, scr_host, scr_user, scr_comment, scr_alert, ' .
		 'T1.sgr_id, sgr_label, sgr_alert, scr_expiration_date, ' .
		 'T1.stp_id, stp_name, ' .
		 'T1.env_id, env_name, ' .
		 'T1.idn_id ' .
		 'FROM scr_secrets AS T1 ' .
		 'LEFT JOIN sgr_secrets_groups AS T2 ON T1.sgr_id = T2.sgr_id ' .
		 'LEFT JOIN stp_secret_types AS T3 ON T1.stp_id = T3.stp_id ' .
		 'LEFT JOIN env_environments AS T4 ON T1.env_id = T4.env_id ' .
		 'LEFT JOIN app_applications AS T7 ON T1.app_id = T7.app_id ';
		
		if ( $Administrator == false ) {
			$Request .=
			 'LEFT JOIN prsg_profiles_secrets_groups AS T5 ON T2.sgr_id = T5.sgr_id ' .
			 'LEFT JOIN idpr_identities_profiles AS T6 ON T5.prf_id = T6.prf_id ';
		}
		 
		$Request .= $Where ;
		
		switch( $orderBy ) {
		 default:
		 case 'group':
			$Request .= 'ORDER BY sgr_label ';
			break;

		 case 'group-desc':
			$Request .= 'ORDER BY sgr_label DESC ';
			break;

		 case 'type':
			$Request .= 'ORDER BY stp_name ';
			break;

		 case 'type-desc':
			$Request .= 'ORDER BY stp_name DESC ';
			break;

		 case 'environment':
			$Request .= 'ORDER BY env_name ';
			break;

		 case 'environment-desc':
			$Request .= 'ORDER BY env_name DESC ';
			break;

		 case 'application':
			$Request .= 'ORDER BY app_name ';
			break;

		 case 'application-desc':
			$Request .= 'ORDER BY app_name DESC ';
			break;

		 case 'host':
			$Request .= 'ORDER BY scr_host ';
			break;

		 case 'host-desc':
			$Request .= 'ORDER BY scr_host DESC ';
			break;

		 case 'user':
			$Request .= 'ORDER BY scr_user ';
			break;

		 case 'user-desc':
			$Request .= 'ORDER BY scr_user DESC ';
			break;

		 case 'alert':
			$Request .= 'ORDER BY scr_alert ';
			break;

		 case 'alert-desc':
			$Request .= 'ORDER BY scr_alert DESC ';
			break;

		 case 'comment':
			$Request .= 'ORDER BY scr_comment ';
			break;

		 case 'comment-desc':
			$Request .= 'ORDER BY scr_comment DESC ';
			break;
		}
		
		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}


		if ( $sgr_id != '' ) {
			if ( ! $Result->bindParam( ':sgr_id', $sgr_id, PDO::PARAM_INT ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}

		if ( $idn_id != '' and $Administrator == false ) {
			if ( ! $Result->bindParam( ':idn_id', $idn_id, PDO::PARAM_INT ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}
		
		if ( $stp_id != '' ) {
			if ( ! $Result->bindParam( ':stp_id', $stp_id, PDO::PARAM_INT ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}

		if ( $env_id != '' ) {
			if ( ! $Result->bindParam( ':env_id', $env_id, PDO::PARAM_INT ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}

		if ( $app_id != '' ) {
			if ( ! $Result->bindParam( ':app_id', $app_id, PDO::PARAM_INT ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}

		if ( $scr_host != '' ) {
			$scr_host = '%' . $scr_host . '%';
			if ( ! $Result->bindParam( ':scr_host', $scr_host, PDO::PARAM_STR, 255 ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}

		if ( $scr_user != '' ) {
			$scr_user = '%' . $scr_user . '%';
			if ( ! $Result->bindParam( ':scr_user', $scr_user, PDO::PARAM_STR, 25 ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}

		if ( $scr_comment != '' ) {
			$scr_comment = '%' . $scr_comment . '%';
			if ( ! $Result->bindParam( ':scr_comment', $scr_comment, PDO::PARAM_STR,
			 100 ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}


		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Data = array();
		
		while ( $Occurrence = $Result->fetchObject() ) {
			$Data[] = $Occurrence;
		}
 
 		return $Data;
	}


	public function listSecrets2( $searchSecret = '', $idn_id = '', $Administrator = false, $orderBy = '' ) {
	/**
	* Lister les Secrets (avec tous les détails associés).
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-02-18
	*
	* @param[in] $searchSecret Critère de recherche pour les Secrets
	* @param[in] $idn_id Id. de l'Identité précisé comme critère de recherche pour les Secrets
	* @param[in] $Administrator Drapeau pour préciser si l'utilisateur est Administrateur
	* @param[in] $orderBy Précise la préférence de tri en restitution de l'information
	*
	* @return Renvoi un tableau d'objet de Secrets, sinon lève une exception
	*/
		$Data = false;


		if ( $Administrator === false ) $Where = 'WHERE T6.idn_id = :idn_id ';
		else $Where = 'WHERE ( T1.idn_id IS NULL OR T1.idn_id = 0 OR T1.idn_id = :idn_id ) ';

		$Where1 = '';
		
		if ( $searchSecret != '' ) {
			$Where1 = 'AND ( T2.sgr_label like :secret ' .
			'OR T3.stp_name like :secret ' .
			'OR T4.env_name like :secret ' .
			'OR T7.app_name like :secret ' .
			'OR T1.scr_host like :secret ' .
			'OR T1.scr_user like :secret ' .
			'OR T1.scr_comment like :secret ' .
			'OR T1.scr_expiration_date like :secret ) ';

			$Where .= $Where1;
		}


		$Request = 'SELECT DISTINCT ' .
		 'scr_id, T1.app_id, app_name, scr_host, scr_user, scr_comment, scr_alert, ' .
		 'T1.sgr_id, sgr_label, sgr_alert, scr_expiration_date, ' .
		 'T1.stp_id, stp_name, ' .
		 'T1.env_id, env_name, ' .
		 'T1.idn_id ' .
		 'FROM scr_secrets AS T1 ' .
		 'LEFT JOIN sgr_secrets_groups AS T2 ON T1.sgr_id = T2.sgr_id ' .
		 'LEFT JOIN stp_secret_types AS T3 ON T1.stp_id = T3.stp_id ' .
		 'LEFT JOIN env_environments AS T4 ON T1.env_id = T4.env_id ' .
		 'LEFT JOIN app_applications AS T7 ON T1.app_id = T7.app_id ';
		
		if ( $Administrator === false ) {
			$Request .=
			 'LEFT JOIN prsg_profiles_secrets_groups AS T5 ON T2.sgr_id = T5.sgr_id ' .
			 'LEFT JOIN idpr_identities_profiles AS T6 ON T5.prf_id = T6.prf_id ';
		}
		 
		$Request .= $Where ;

		if ( $Administrator === false ) {
			$Request .= 'UNION ALL
			SELECT DISTINCT scr_id, T1.app_id, app_name, scr_host, scr_user, scr_comment, scr_alert, T1.sgr_id, sgr_label, sgr_alert,
			 scr_expiration_date, T1.stp_id, stp_name, T1.env_id, env_name, T1.idn_id
			FROM scr_secrets AS T1
			LEFT JOIN sgr_secrets_groups AS T2 ON T1.sgr_id = T2.sgr_id
			LEFT JOIN stp_secret_types AS T3 ON T1.stp_id = T3.stp_id
			LEFT JOIN env_environments AS T4 ON T1.env_id = T4.env_id
			LEFT JOIN app_applications AS T7 ON T1.app_id = T7.app_id
			WHERE T1.idn_id = :idn_id ' . $Where1 ;
		}


		switch( $orderBy ) {
		 default:
		 case 'group':
			$Request .= 'ORDER BY sgr_label ';
			break;

		 case 'group-desc':
			$Request .= 'ORDER BY sgr_label DESC ';
			break;

		 case 'type':
			$Request .= 'ORDER BY stp_name ';
			break;

		 case 'type-desc':
			$Request .= 'ORDER BY stp_name DESC ';
			break;

		 case 'environment':
			$Request .= 'ORDER BY env_name ';
			break;

		 case 'environment-desc':
			$Request .= 'ORDER BY env_name DESC ';
			break;

		 case 'application':
			$Request .= 'ORDER BY app_name ';
			break;

		 case 'application-desc':
			$Request .= 'ORDER BY app_name DESC ';
			break;

		 case 'host':
			$Request .= 'ORDER BY scr_host ';
			break;

		 case 'host-desc':
			$Request .= 'ORDER BY scr_host DESC ';
			break;

		 case 'user':
			$Request .= 'ORDER BY scr_user ';
			break;

		 case 'user-desc':
			$Request .= 'ORDER BY scr_user DESC ';
			break;

		 case 'alert':
			$Request .= 'ORDER BY scr_alert ';
			break;

		 case 'alert-desc':
			$Request .= 'ORDER BY scr_alert DESC ';
			break;

		 case 'comment':
			$Request .= 'ORDER BY scr_comment ';
			break;

		 case 'comment-desc':
			$Request .= 'ORDER BY scr_comment DESC ';
			break;

		 case 'expiration_date':
			$Request .= 'ORDER BY scr_expiration_date ';
			break;

		 case 'expiration_date-desc':
			$Request .= 'ORDER BY scr_expiration_date DESC ';
			break;
		}

		
		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}


		if ( $searchSecret != '' ) {
			$searchSecret = '%' . $searchSecret . '%';
			if ( ! $Result->bindParam( ':secret', $searchSecret, PDO::PARAM_STR, 30 ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}

		if ( $idn_id != '' ) {
			if ( ! $Result->bindParam( ':idn_id', $idn_id, PDO::PARAM_INT ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}


		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Data = array();
		
		while ( $Occurrence = $Result->fetchObject() ) {
			$Data[] = $Occurrence;
		}
 
 		return $Data;
	}


	public function get( $scr_id ) {
	/**
	* Récupère les informations générales d'un Secret.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-02-18
	*
	* @param[in] $scr_id Id. du Secret à rechercher
	*
	* @return Renvoi un objet de type Secret, sinon lève une exception
	*/
		include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-secrets-server.php' );
		include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_labels_generic.php' );
		include_once( DIR_LIBRARIES . '/Class_Secrets_Server.inc.php' );

		$Parameters = new IICA_Parameters();

		$Secret_Server = new Secret_Server();


		$Security = new Security();

		$Integrity_Status = $Security->checkFilesIntegrity();
		if ( $Integrity_Status[ 0 ] === FALSE ) {
			if ( $Security->getParameter('stop_secret_server_on_alert') == 1 ) {
				$Secret_Server->SS_Shutdown();
			}

			$Files = '';

			foreach( $Integrity_Status[ 1 ] as $File ) {
				if ( $Files != '' ) $Files .= '<br/>';

				$Files .= $File;
			}

			$Files = '<br/>(' . $Files . ')';

			throw new Exception( '<p class="alert">'. $L_Files_Integrity_Alert . '</p><p>' . $Files . '</p>', 1 );
		}

		
		$Request = 'SELECT ' .
		 'scr_id, scr_host, scr_user, scr_password, scr_comment, scr_alert, ' .
		 'scr_creation_date, scr_modification_date, T1.app_id, app_name, scr_expiration_date, ' .
		 'T1.sgr_id, sgr_label, sgr_alert, ' .
		 'T1.stp_id, stp_name, ' .
		 'T1.env_id, env_name, ' .
		 'T1.idn_id ' .
		 'FROM scr_secrets AS T1 ' .
		 'LEFT JOIN sgr_secrets_groups AS T2 ON T1.sgr_id = T2.sgr_id ' .
		 'LEFT JOIN stp_secret_types AS T3 ON T1.stp_id = T3.stp_id ' .
		 'LEFT JOIN env_environments AS T4 ON T1.env_id = T4.env_id ' .
		 'LEFT JOIN app_applications AS T5 ON T1.app_id = T5.app_id ' .
		 'WHERE scr_id = :scr_id ' .
		 'AND ( T1.idn_id IS NULL OR T1.idn_id = 0 OR T1.idn_id = ' . $_SESSION['idn_id'] . ' ) ';

		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->bindParam( ':scr_id', $scr_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Occurrence = $Result->fetchObject();


		// =================================
		// Déchiffrement du secret.
		if ( $Parameters->getParameter( 'use_SecretServer' ) == '1' ) {
			try {
				$Occurrence->scr_password = $Secret_Server->SS_decryptValue(
				 $Occurrence->scr_password );
			} catch( Exception $e ) {
				$Error = $e->getMessage();
				if ( isset( ${$Error} ) ) {
					$Error = ${$Error};
				}
				
				throw new Exception( $Error, 0 );
			}
		} else {
			$Occurrence->scr_password = $Secret_Server->mc_decrypt( $Occurrence->scr_password );
		}

		return $Occurrence;
	}


	public function delete( $scr_id ) {
	/**
	* Supprime un Secret.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-02-18
	*
	* @param[in] $scr_id Id. du Secret à supprimer
	*
	* @return Renvoi vrai le Secret a été supprimé, sinon lève une exception
	*/
		if ( ! $Result = $this->prepare( 'DELETE ' .
		 'FROM scr_secrets ' .
		 'WHERE scr_id = :scr_id ' .
		 'AND ( idn_id IS NULL OR idn_id = ' . $_SESSION['idn_id'] . ' ) ' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->bindParam( ':scr_id', $scr_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
 
		return true;
	}



	public function total( $idn_id = '' ) {
	/**
	* Récupère le nombre total de Secrets.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-02-18
	*
	* @param[in] $idn_id Id. de l'Identité quand on cherche le nombre total de Secrets rattaché à un utilisateur
	*
	* @return Renvoi le nombre total de Secrets, sinon lève une exception
	*/
		if ( $idn_id == '' ) {
			$Request = 'SELECT ' .
			 'count(*) AS total ' .
			 'FROM scr_secrets ' ;
		} else {
			$Request = 'SELECT ' .
			 'T3.count(*) AS total ' .
			 'FROM idpr_identities_profiles AS T1 ' .
			 'LEFT JOIN prsg_profiles_secrets_groups AS T2 ON T1.prf_id = T2.prf_id ' .
			 'LEFT JOIN scr_secrets AS T3 ON T2.sgr_id_id = T3.sgr_id ' .
			 'WHERE idn_id = :idn_id ' .
			 'AND ( T1.idn_id IS NULL OR T1.idn_id = ' . $_SESSION['idn_id'] . ' ) ';
		}
		

		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->bindParam( ':idn_id', $idn_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Occurrence = $Result->fetchObject() ;
		
		return $Occurrence->total;
	}


	public function listHistoryEvents( $scr_id = '', $idn_id = '', $since_date = '', $before_date = '',
	 $ach_access = '', $ach_ip = '', $hac_id = '', $rgh_id = '', $ach_gravity_level = '', $start = 0, $number = 10 ) {
	/**
	* Lister les événements de l'historique.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-02-18
	*
	* @param[in] $scr_id Id. du Secret pour lequel on cherche l'historique associé
	* @param[in] $idn_id Id. de l'Identité pour laquelle on cherche l'historique associé
	* @param[in] $since_date Date depuis laquelle on recherche de l'historique
	* @param[in] $before_date Date avant laquelle on recherche de l'historique
	* @param[in] $ach_access Libellé de l'accès pour lequel on cherche l'hsitorique associé
	* @param[in] $ach_ip Adresse IP pour laquelle on cherche l'historique associé
	* @param[in] $hac_id Id. du type d'accès pour lequel on cherche l'hsitorique associé
	* @param[in] $rgh_id Id. du droits utilisé pour lequel on cherche l'hsitorique associé
	* @param[in] $ach_gravity_level Niveau de gravité pour lequel on cherche l'hsitorique associé
	* @param[in] $start Rang dans le résultat à partir duquel on démarre l'affichage
	* @param[in] $number Nombre d'occurrence à afficher à partir du rang d'affichage
	*
	* @return Renvoi un tableau d'objets de type Historique, sinon lève une exception
	*/
		$Request = 'SELECT ' .
		 'scr_id, idn_login, ach_date, ach_access, ach_ip, hac_name, rgh_name, ach_gravity_level ' .
		 'FROM ach_access_history as T1 ' .
		 'LEFT JOIN idn_identities as T2 ON T1.idn_id = T2.idn_id ' .
		 'LEFT JOIN hac_history_actions_codes as T3 ON T1.hac_id = T3.hac_id ' .
		 'LEFT JOIN rgh_rights as T4 ON T1.rgh_id = T4.rgh_id ' ;
		
		if ( $scr_id != '' ) {
			$Where = 'WHERE scr_id = ' . $scr_id . ' ';
		} else {
			$Where = '';
		}
		
		if ( $idn_id != '' ) {
			if ( $Where == '' ) $Where = 'WHERE ';
			else $Where .= 'AND ';
			
			$Where .= 'T1.idn_id = ' . $idn_id . ' ';
		}
		
		if ( $since_date != '' ) {
			if ( $Where == '' ) $Where = 'WHERE ';
			else $Where .= 'AND ';
			
			$Where .= 'ach_date >= "' . $since_date . '%" ';
		}

		
		if ( $before_date != '' ) {
			if ( $Where == '' ) $Where = 'WHERE ';
			else $Where .= 'AND ';
			
			$Where .= 'ach_date <= "' . $before_date . '%" ';
		}
		
		if ( $ach_access != '' ) {
			if ( $Where == '' ) $Where = 'WHERE ';
			else $Where .= 'AND ';
			
			$Where .= 'ach_access like "%' . $ach_access . '%" ';
		}
		
		if ( $ach_ip != '' ) {
			if ( $Where == '' ) $Where = 'WHERE ';
			else $Where .= 'AND ';
			
			$Where .= 'ach_ip like "%' . $ach_ip . '%" ';
		}
		
		if ( $hac_id != '' ) {
			if ( $Where == '' ) $Where = 'WHERE ';
			else $Where .= 'AND ';
			
			$Where .= 'T1.hac_id = ' . $hac_id . ' ';
		}
		
		if ( $rgh_id != '' ) {
			if ( $Where == '' ) $Where = 'WHERE ';
			else $Where .= 'AND ';
			
			$Where .= 'T1.rgh_id = ' . $rgh_id . ' ';
		}

		if ( $ach_gravity_level != '' ) {
			if ( $Where == '' ) $Where = 'WHERE ';
			else $Where .= 'AND ';
			
			$Where .= 'T1.ach_gravity_level = ' . $ach_gravity_level . ' ';
		}

		
		$Request .= $Where .
		 'ORDER BY ach_date desc ' .
		 'LIMIT ' . $start . ', ' . $number . ' ' ;


		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Data = array();
		
		while ( $Occurrence = $Result->fetchObject() ) {
			$Data[] = $Occurrence;
		}
 
 		return $Data;
	}


	public function totalHistoryEvents( $scr_id = '', $idn_id = '', $since_date = '', $before_date = '',
	 $ach_access = '', $ach_ip = '', $hac_id = '', $rgh_id = '', $ach_gravity_level = '' ) {
	/**
	* Total des événements dans l'historique.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-02-18
	*
	* @param[in] $scr_id Id. du Secret pour lequel on cherche l'historique associé
	* @param[in] $idn_id Id. de l'Identité pour laquelle on cherche l'historique associé
	* @param[in] $since_date Date depuis laquelle on recherche de l'historique
	* @param[in] $before_date Date avant laquelle on recherche de l'historique
	* @param[in] $ach_access Libellé de l'accès pour lequel on cherche l'hsitorique associé
	* @param[in] $ach_ip Adresse IP pour laquelle on cherche l'historique associé
	* @param[in] $hac_id Id. du type d'accès pour lequel on cherche l'hsitorique associé
	* @param[in] $rgh_id Id. du droits utilisé pour lequel on cherche l'hsitorique associé
	* @param[in] $ach_gravity_level Niveau de gravité pour lequel on cherche l'hsitorique associé
	*
	* @return Renvoi le nombre total d'événements dans l'Historique répondant aux critères, sinon lève une exception
	*/
		$Request = 'SELECT ' .
		 'min(ach_date) as first_date, count(*) as total ' .
		 'FROM ach_access_history as T1 ' /*.
		 'LEFT JOIN idn_identities as T2 ON T1.idn_id = T2.idn_id '*/ ;
		
		if ( $scr_id != '' ) {
			$Where = 'WHERE scr_id = ' . $scr_id . ' ';
		} else {
			$Where = '';
		}
		
		if ( $idn_id != '' ) {
			if ( $Where == '' ) $Where = 'WHERE ';
			else $Where .= 'AND ';
			
			$Where .= 'T1.idn_id = ' . $idn_id . ' ';
		}
		
		if ( $since_date != '' ) {
			if ( $Where == '' ) $Where = 'WHERE ';
			else $Where .= 'AND ';
			
			$Where .= 'ach_date >= "' . $since_date . '%" ';
		}

		
		if ( $before_date != '' ) {
			if ( $Where == '' ) $Where = 'WHERE ';
			else $Where .= 'AND ';
			
			$Where .= 'ach_date <= "' . $before_date . '%" ';
		}
		
		if ( $ach_access != '' ) {
			if ( $Where == '' ) $Where = 'WHERE ';
			else $Where .= 'AND ';
			
			$Where .= 'ach_access like "%' . $ach_access . '%" ';
		}
		
		if ( $ach_ip != '' ) {
			if ( $Where == '' ) $Where = 'WHERE ';
			else $Where .= 'AND ';
			
			$Where .= 'ach_ip like "%' . $ach_ip . '%" ';
		}
		
		if ( $hac_id != '' ) {
			if ( $Where == '' ) $Where = 'WHERE ';
			else $Where .= 'AND ';
			
			$Where .= 'hac_id = ' . $hac_id . ' ';
		}
		
		if ( $rgh_id != '' ) {
			if ( $Where == '' ) $Where = 'WHERE ';
			else $Where .= 'AND ';
			
			$Where .= 'rgh_id = ' . $rgh_id . ' ';
		}

		if ( $ach_gravity_level != '' ) {
			if ( $Where == '' ) $Where = 'WHERE ';
			else $Where .= 'AND ';
			
			$Where .= 'ach_gravity_level = ' . $ach_gravity_level . ' ';
		}
		
		$Request .= $Where;
		

		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Data = $Result->fetchObject();

 		return $Data;
	}


	public function purgeHistoryEvents( $ach_date ) {
	/**
	* Purge les événements dans l'historique.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-02-18
	*
	* @param[in] $ach_date Date à partir de laquelle on supprime des occurrences dans l'historique (de cette date vers la plus ancienne)
	*
	* @return Renvoi vrai après avoir supprimé les occurrences de l'historique, sinon lève une exception
	*/
		$Request = 'DELETE ' .
		 'FROM ach_access_history ' .
		 'WHERE ach_date <= "' . $ach_date . '" ';

		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
 		return true;
	}


	public function getMessageForHistory( $scr_id, $Secret = '' ) {
	/**
	* Construit le message détaillé à remonter dans l'Historique.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2013-02-18
	*
	* @param[in] $scr_id Id. du Secret pour lequel on cherche à formatter un message pour l'historque
	* @param[in] $Secret Objet de type Secret qui vient d'être créé et pour lequel on cherche à formatter un message pour l'historque
	*
	* @return Renvoi la chaîne formatée pour l'historique, sinon une chaîne vide
	*/
		if ( $scr_id == '' ) return '';

		include_once( DIR_LIBRARIES . '/Class_HTML.inc.php');

		$pHTML = new HTML();

    	// Récupère les dernières informations du Secret qui vient d'être modifié.
    	if ( $Secret == '' ) $Secret = $this->get( $scr_id );

    	// Récupère les libellés pour le message
    	$Labels = $pHTML->getTextCode( array( 'L_Group', 'L_Type', 'L_Environment', 'L_Application', 'L_Host', 'L_User', 'L_Comment',
    		$Secret->stp_name, $Secret->env_name ), $pHTML->getParameter( 'language_alert' ) );

    	return ' (' . $Labels['L_Group'] . ':"' . $Secret->sgr_label . '", ' .
    		$Labels['L_Type'] . ':"' . $Labels[ $Secret->stp_name ] . '", ' .
    		$Labels['L_Environment'] . ':"' . $Labels[ $Secret->env_name ] . '", ' .
    		$Labels['L_Application'] . ':"' . $Secret->app_name . '", ' .
    		$Labels['L_Host'] . ':"' . $Secret->scr_host . '", ' .
    		$Labels['L_User'] . ':"' . $Secret->scr_user . '", ' .
    		$Labels['L_Comment'] . ':"' . $Secret->scr_comment . '")';
    }


    public function listSecretsHistory( $scr_id ) {
		$Request = 'SELECT ' .
		 'shs_password, shs_last_date_use ' .
		 'FROM shs_secrets_history AS shs ' .
		 'WHERE scr_id = :scr_id ' .
		 'ORDER BY shs_last_date_use DESC ';
		

		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->bindParam( ':scr_id', $scr_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		return $Result->fetchAll( PDO::FETCH_CLASS );
    }
} // Fin class IICA_Secrets

?>