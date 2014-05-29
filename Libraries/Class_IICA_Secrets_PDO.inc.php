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
* @version 1.1
* @date 2012-11-19
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
	
	/* -----------------------------
	** Crée ou modifie un Secret.
	*/
	public function set( $scr_id, $sgr_id, $stp_id, $scr_host, $scr_user, $scr_password,
	 $scr_comment, $scr_alert, $env_id, $app_id, $scr_expiration_date = NULL, $idn_id = NULL ) {
		include_once( DIR_LIBRARIES . '/Class_Security.inc.php' );

		include_once( DIR_LIBRARIES . '/Class_IICA_Parameters_PDO.inc.php' );

		include_once( DIR_LIBRARIES . '/Class_Secrets_Server.inc.php' );
		
		include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-secrets-server.php' );

		$Security = new Security();

		$Parameters = new IICA_Parameters();

		$Secret_Server = new Secret_Server();

		
		if ( $scr_id == '' ) {
			$Request = 'INSERT INTO scr_secrets ' .
				'( sgr_id, stp_id, scr_host, scr_user, scr_password, scr_comment, ' .
				'scr_alert, scr_creation_date, env_id, app_id, scr_expiration_date, idn_id ) ' .
				'VALUES ( :sgr_id, :stp_id, :scr_host, :scr_user, :scr_password, ' .
				':scr_comment, :scr_alert, "' . date( 'Y-m-d H:n:s' ) . '", :env_id, :app_id, ' .
				':scr_expiration_date, :idn_id ) ';

			if ( ! $Result = $this->prepare( $Request ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		} else {
			$Request = 'UPDATE scr_secrets SET ' .
				'scr_id = :scr_id, sgr_id = :sgr_id, stp_id = :stp_id, scr_host = :scr_host, ' .
				'scr_user = :scr_user, scr_password = :scr_password, scr_comment = :scr_comment, ' .
				'scr_alert = :scr_alert, scr_modification_date = "' . date( 'Y-m-d H:n:s' ) . '", ' .
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
		
		return true;
	}

	
	/* -----------------------------
	** Déchiffre le Secret avec l'ancienne clé mère et rechiffre le Secret avec la nouvelle clé mère.
	*/
	public function transcrypt( $old_Mother_Key, $new_Mother_Key ) {
		include_once( DIR_LIBRARIES . '/Class_Security.inc.php' );
		//include_once( DIR_LIBRARIES . '/Class_IICA_Parameters_PDO.inc.php' );

		include_once( DIR_LIBRARIES . '/Class_IICA_DB_Connector_PDO.inc.php' );

		if ( ! isset( $_SESSION[ 'Language' ] ) ) $_SESSION[ 'Language' ] = 'en';

		include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-secrets-server.php' );

		//include( DIR_LIBRARIES . '/Config_Access_DB.inc.php' );
		

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


	/* -------------------
	** Lister les Secrets.
	*/
	public function listSecrets( $sgr_id = '', $idn_id = '', $stp_id = '', $env_id = '',
	 $app_id = '', $scr_host = '', $scr_user = '', $scr_comment = '',
	 $Administrator = false, $orderBy = '' ) {
		$Data = false;
		
		$Where = '';

		if ( $sgr_id != '' ) {
			$Where = 'WHERE T1.sgr_id = :sgr_id ';
		}

		if ( $idn_id != '' and $Administrator == false ) {
			if ( $Where == '' ) $Where = 'WHERE T6.idn_id = :idn_id ';
			else $Where .= 'AND T6.idn_id = :idn_id ';
				
		}
		
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

		if ( $Where == '' ) $Where = 'WHERE T1.idn_id IS NULL OR T1.idn_id = ' . $_SESSION['idn_id'] . ' ';
		else $Where .= 'AND ( T1.idn_id IS NULL OR T1.idn_id = ' . $_SESSION['idn_id'] . ' ) ';

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
		$Data = false;


		if ( $Administrator === false ) $Where = 'WHERE T6.idn_id = :idn_id ';
		else $Where = 'WHERE ( T1.idn_id IS NULL OR T1.idn_id = :idn_id ) ';

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

		//print( $Request ); print('<hr/>');
		
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


	/* -------------------
	** Récupère les informations d'un Secret.
	*/
	public function get( $scr_id ) {
		include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-secrets-server.php' );
		include_once( DIR_LIBRARIES . '/Class_Secrets_Server.inc.php' );

		$Parameters = new IICA_Parameters();

		$Secret_Server = new Secret_Server();

		
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
		 'AND ( T1.idn_id IS NULL OR T1.idn_id = ' . $_SESSION['idn_id'] . ' ) ';

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
				if ( isset( ${$Error} ) ) $Error = ${$Error};
				
				throw new Exception( $Error, 0 );
			}
		} else {
			$Occurrence->scr_password = $Secret_Server->mc_decrypt( $Occurrence->scr_password );
		}

		return $Occurrence;
	}


	/* ----------------------
	** Supprime un Secret.
	*/
	public function delete( $scr_id ) {
		if ( ! $Result = $this->prepare( 'DELETE ' .
		 'FROM scr_secrets ' .
		 'WHERE scr_id = :scr_id ' .
		 'AND ( T1.idn_id IS NULL OR T1.idn_id = ' . $_SESSION['idn_id'] . ' ) ' ) ) {
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



	/* -------------------
	** Récupère le nombre total de Secrets.
	*/
	public function total( $idn_id = '' ) {
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


	/* -------------------
	** Lister les événements de l'historique.
	*/
	public function listHistoryEvents( $scr_id = '', $idn_id = '', $since_date = '', $before_date = '',
	 $ach_access = '', $ach_ip = '', $hac_id = '', $rgh_id = '', $ach_gravity_level = '', $start = 0, $number = 10 ) {
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


	/* -------------------
	** Total des événements dans l'historique.
	*/
	public function totalHistoryEvents( $scr_id = '', $idn_id = '', $since_date = '', $before_date = '',
	 $ach_access = '', $ach_ip = '', $hac_id = '', $rgh_id = '', $ach_gravity_level = '' ) {
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


	/* -------------------
	** Purge les événements dans l'historique.
	*/
	public function purgeHistoryEvents( $ach_date ) {
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


	/* -------------------
	** Construit le message détaillé à remonter dans l'Historique.
	*/
	public function getMessageForHistory( $scr_id, $Secret = '' ) {
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

} // Fin class IICA_Secrets

?>