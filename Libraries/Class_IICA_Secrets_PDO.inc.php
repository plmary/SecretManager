<?php

include_once( 'Constants.inc.php' );

include_once( IICA_LIBRARIES . '/Class_IICA_DB_Connector_PDO.inc.php' );

// =============================
class IICA_Groups extends IICA_DB_Connector {
/**
* Cette classe gère les groupes de secrets.
*
* PHP version 5
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @version 1.1
* @date 2012-11-19
*/
    public $LastInsertId;

	public function __construct() {
	/**
	* Connexion à la base de données.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-07
	*
	* @return Renvoi un booléen sur le succès de la connexion à la base de données
	*/
		parent::__construct();
		
		return true;
	}


	public function set( $sgr_id, $Label, $Alert = 0 ) {
	/**
	* Crée ou modifie un Groupe.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-07
	*
	* @param[in] $sgr_id (int) Identifiant du Groupe de Secrets à modifier (s'il est précisé, sinon créé le Groupe)
	* @param[in] $Label (string) Libellé du Groupe de Secrets
	* @param[in] $Alert (boolean) Précise si les accès au Groupe de Secrets génère des alertes
	*
	* @return Renvoi vrai sur le succès de la mise à jour du Groupe, sinon lève une Exception
	*/
		if ( $sgr_id == '' ) {
			if ( ! $Result = $this->prepare( 'INSERT INTO sgr_secrets_groups ' .
				'( sgr_label, sgr_alert ) ' .
				'VALUES ( :Label, :Alert )' ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		} else {
			if ( ! $Result = $this->prepare( 'UPDATE sgr_secrets_groups SET ' .
				'sgr_label = :Label, sgr_alert = :Alert ' .
				'WHERE sgr_id = :sgr_id' ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
			
			if ( ! $Result->bindParam( ':sgr_id', $sgr_id, PDO::PARAM_INT ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}
				
		if ( ! $Result->bindParam( ':Label', $Label, PDO::PARAM_STR, 60 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
				
		if ( ! $Result->bindParam( ':Alert', $Alert, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( $sgr_id == '' ) {
			switch( $this->getAttribute(PDO::ATTR_DRIVER_NAME) ) {
			 default;
				$this->LastInsertId = $this->lastInsertId();
				break;

			 case 'pgsql';
				$this->LastInsertId = $this->lastInsertId( 'sgr_secrets_groups_sgr_id_seq' );
				break;
			}
		}
		
		return true;
	}


	public function listGroups( $idn_id = '', $orderBy = '', $rgh_id = '' ) {
	/**
	* Liste les Groupes.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-07
	*
	* @param[in] $idn_id (int) Identifiant de l'identité pour laquelle on recherche les Groupes d'appartenance (si précisée, sinon recherche tous les Groupes)
	* @param[in] $orderBy (string) Code de la colonne sur lequel se fera le tri à l'affichage
	*
	* @return Renvoi vrai sur le succès de la mise à jour du Groupe, sinon lève une Exception
	*/
		$Request = 'SELECT DISTINCT ' .
		 'T1.sgr_id, sgr_label, sgr_alert, T2.rgh_id ' .
		 'FROM sgr_secrets_groups AS T1 ' .
		 'LEFT JOIN prsg_profiles_secrets_groups AS T2 ON T1.sgr_id = T2.sgr_id ' ;
		
		if ( $idn_id != '' ) {
			$Request .= 
			 'LEFT JOIN idpr_identities_profiles AS T3 ON T2.prf_id = T3.prf_id ' .
			 'WHERE T3.idn_id = :idn_id ';
		}
		
		if ( $rgh_id != '' ) {
			if ( strpos( $Request, 'WHERE' ) === false ) {
				$Request .= 'WHERE T2.rgh_id >= :rgh_id ';
			} else {
				$Request .= 'AND T2.rgh_id >= :rgh_id ';
			}
		}
		
		switch( $orderBy ) {
		 default:
		 case 'label':
			$Request .= 'ORDER BY sgr_label ';
			
			break;

		 case 'label-desc':
			$Request .= 'ORDER BY sgr_label DESC ';
			
			break;

		 case 'alert':
			$Request .= 'ORDER BY sgr_alert ';
			
			break;

		 case 'alert-desc':
			$Request .= 'ORDER BY sgr_alert DESC ';
			
			break;
		}
		
		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( $idn_id != '' ) {
			if ( ! $Result->bindParam( ':idn_id', $idn_id, PDO::PARAM_INT ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}

		if ( $rgh_id != '' ) {
			if ( ! $Result->bindParam( ':rgh_id', $rgh_id, PDO::PARAM_INT ) ) {
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
			$Data[ $Occurrence->sgr_id ] = $Occurrence;
		}
 
 		return $Data;
	}


	/* -------------------
	** Récupère les informations d'un Groupe.
	*/
	public function get( $sgr_id ) {
		$Data = false;
		
		$Request = 'SELECT ' .
		 'sgr_label, sgr_alert ' .
		 'FROM sgr_secrets_groups ' .
		 'WHERE sgr_id = :sgr_id ' ;

		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->bindParam( ':sgr_id', $sgr_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		return $Result->fetchObject() ;
	}


	/* ----------------------
	** Supprime un Groupe.
	*/
	public function delete( $sgr_id ) {
      include( DIR_LIBRARIES . '/Config_Access_Tables.inc.php' );
      
		/*
		** Démarre la transaction.
		*/
		$this->beginTransaction();
		
		
		if ( ! $Result = $this->prepare( 'DELETE ' .
		 'FROM sgr_secrets_groups ' .
		 'WHERE sgr_id = :sgr_id' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Result->bindParam( ':sgr_id', $sgr_id, PDO::PARAM_INT ) ;
		
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}


		/*
		** Détruit les associations entre ce Groupe et
		** les Profils.
		*/
		if ( ! $Result = $this->prepare( 'DELETE ' .
		 'FROM prsg_profiles_secrets_groups ' .
		 'WHERE sgr_id = :sgr_id' ) ) {
			$this->rollBack();
			
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		$Result->bindParam( ':sgr_id', $sgr_id, PDO::PARAM_INT ) ;

		if ( ! $Result->execute() ) {
			$this->rollBack();

			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}


		// Sauvegarde l'ensemble des modifications.
		$this->commit();
 
		return true;
	}

	
	/* -----------------------------
	** Ajoute au Groupe un Profil.
	*/
	public function addProfile( $sgr_id, $prf_id, $rgh_id = 1 ) {
		if ( ! $Result = $this->prepare( 'INSERT INTO prsg_profiles_secrets_groups ' .
			'( prf_id, sgr_id, rgh_id ) ' .
			'VALUES ( :prf_id, :sgr_id, :rgh_id )' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
				
		if ( ! $Result->bindParam( ':prf_id', $prf_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
				
		if ( ! $Result->bindParam( ':sgr_id', $sgr_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->bindParam( ':rgh_id', $rgh_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		return true;
	}

	
	/* -----------------------------
	** Supprime au Groupe un Profil.
	*/
	public function deleteProfile( $grp_id, $prf_id ) {
		if ( ! $Result = $this->prepare( 'DELETE FROM prsg_profiles_secrets_groups ' .
			'WHERE prf_id = :prf_id AND sgr_id = :sgr_id ' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
				
		if ( ! $Result->bindParam( ':prf_id', $prf_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
				
		if ( ! $Result->bindParam( ':sgr_id', $sgr_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		return true;
	}

	
	/* -----------------------------
	** Supprime au Groupe les Profils.
	*/
	public function deleteProfiles( $sgr_id, $prf_id = '' ) {
		$Request = 'DELETE FROM prsg_profiles_secrets_groups ' ;
		
		if ( $sgr_id != '' ) $Request .= 'WHERE sgr_id = :sgr_id ';

		if ( $prf_id != '' ) $Request .= 'WHERE prf_id = :prf_id ';
			
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

		if ( $prf_id != '' ) {
			if ( ! $Result->bindParam( ':prf_id', $prf_id, PDO::PARAM_INT ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		return true;
	}

	
	/* -----------------------------
	** Liste les Profils d'un Groupe.
	*/
	public function listProfiles( $sgr_id, $Keys = 0 ) {
		if ( ! $Result = $this->prepare( 'SELECT sgr_id, prf_id, rgh_id ' .
		 'FROM prsg_profiles_secrets_groups ' .
		 'WHERE sgr_id = :sgr_id ' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
				
		if ( ! $Result->bindParam( ':sgr_id', $sgr_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		$Data = array();
		
		while ( $Occurrence = $Result->fetchObject() ) {
			if ( $Keys == 1 ) {
				$Key = $Occurrence->sgr_id . '-' . $Occurrence->prf_id .
				 '-' . $Occurrence->rgh_id ;
			} else {
				$Key = '';
			}
		
			$Data[ $Key ] = $Occurrence;
		}
 
 		return $Data;
	}


	/* -------------------
	** Vérifie si le Groupe est associé.
	*/
	public function isAssociated( $sgr_id ) {
		$Request = 'SELECT ' .
		 'count(*) ' .
		 'FROM scr_secrets ' .
		 'WHERE sgr_id = :sgr_id ' ;

		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->bindParam( ':sgr_id', $sgr_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Valeur = $Result->fetch() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Status = false;
		
		if ( $Valeur[ 0 ] != 0 ) $Status = true;

		$Request = 'SELECT ' .
		 'count(*) ' .
		 'FROM prsg_profiles_secrets_groups ' .
		 'WHERE sgr_id = :sgr_id ' ;

		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->bindParam( ':sgr_id', $sgr_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Valeur = $Result->fetch() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( $Valeur[ 0 ] != 0 ) $Status = true;
		
		return $Status ;
	}



	/* -------------------
	** Récupère le nombre total de Groupes.
	*/
	public function total( $idn_id = '' ) {
		if ( $idn_id == '' ) {
			$Request = 'SELECT ' .
			 'count(*) AS total ' .
			 'FROM sgr_secrets_groups ' ;
		} else {
			$Request = 'SELECT ' .
			 'count(*) AS total ' .
			 'FROM idpr_identities_profiles AS T1 ' .
			 'LEFT JOIN prsg_profiles_secrets_groups AS T2 ON T1.prf_id = T2.prf_id ' .
			 'WHERE idn_id = :idn_id ';
//			 sgr_secrets_groups ' ;
		}

		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
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
		
		$Occurrence = $Result->fetchObject() ;
		
		return $Occurrence->total;
	}

} // Fin class IICA_Groups


// ===========================================================================
// ===========================================================================
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
	 $scr_comment, $scr_alert, $env_id, $scr_application, $scr_expiration_date = NULL ) {
		include_once( DIR_LIBRARIES . '/Class_Security.inc.php' );

		include_once( DIR_LIBRARIES . '/Class_IICA_Parameters_PDO.inc.php' );

		include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-secrets-server.php' );
		include( DIR_LIBRARIES . '/Class_Secrets_Server.inc.php' );

		include( DIR_LIBRARIES . '/Config_Access_DB.inc.php' );
		
		$Security = new Security();

		$Parameters = new IICA_Parameters( 
		 $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );

		$Secret_Server = new Secret_Server();

		
		if ( $scr_id == '' ) {
			$Request = 'INSERT INTO scr_secrets ' .
				'( sgr_id, stp_id, scr_host, scr_user, scr_password, scr_comment, ' .
				'scr_alert, scr_creation_date, env_id, scr_application, scr_expiration_date ) ' .
				'VALUES ( :sgr_id, :stp_id, :scr_host, :scr_user, :scr_password, ' .
				':scr_comment, :scr_alert, "' . date( 'Y-m-d H:n:s' ) . '", :env_id, :scr_application, ' .
				':scr_expiration_date ) ';

			if ( ! $Result = $this->prepare( $Request ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		} else {
			$Request = 'UPDATE scr_secrets SET ' .
				'scr_id = :scr_id, sgr_id = :sgr_id, stp_id = :stp_id, scr_host = :scr_host, ' .
				'scr_user = :scr_user, scr_password = :scr_password, scr_comment = :scr_comment, ' .
				'scr_alert = :scr_alert, scr_modification_date = "' . date( 'Y-m-d H:n:s' ) . '", ' .
				'env_id = :env_id, scr_application = :scr_application, scr_expiration_date = :scr_expiration_date ' .
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

		if ( ! $Result->bindParam( ':scr_application', $scr_application, PDO::PARAM_STR, 60 ) ) {
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

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		return true;
	}

	
	/* -----------------------------
	** Déchiffre le Secret avec l'ancienne clé mère et rechiffre le Secret avec la nouvelle clé mère.
	*/
	public function transcrypt( $old_Mother_Key, $new_Mother_Key ) {
		include_once( DIR_LIBRARIES . '/Class_Security.inc.php' );
		include_once( DIR_LIBRARIES . '/Class_IICA_Parameters_PDO.inc.php' );

		if ( ! isset( $_SESSION[ 'Language' ] ) ) $_SESSION[ 'Language' ] = 'en';

		include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-secrets-server.php' );

		include( DIR_LIBRARIES . '/Config_Access_DB.inc.php' );
		

		$Security = new Security();

		
		if ( ! $Result = $this->prepare( 'SELECT scr_id, scr_password FROM scr_secrets ' ) ) {
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
		$this->beginTransaction();
		
		while ( $Occurrence = $Result->fetchObject() ) {
			$Decrypted = $Security->mc_decrypt( $Occurrence->scr_password, $old_Mother_Key );
			$Encrypted = $Security->mc_encrypt( $Decrypted, $new_Mother_Key );

			if ( ! $Updater = $this->prepare( 'UPDATE scr_secrets SET ' .
				'scr_password = :scr_password ' .
				'WHERE scr_id = :scr_id ' ) ) {
				$Error = $Updater->errorInfo();

				$this->rollBack();

				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
			
			if ( ! $Updater->bindParam( ':scr_id', $Occurrence->scr_id, PDO::PARAM_INT ) ) {
				$Error = $Updater->errorInfo();

				$this->rollBack();

				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
			
			if ( ! $Updater->bindParam( ':scr_password', $Encrypted, PDO::PARAM_LOB ) ) {
				$Error = $Updater->errorInfo();

				$this->rollBack();

				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}

			if ( ! $Updater->execute() ) {
				$Error = $Updater->errorInfo();

				$this->rollBack();

				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}

		
		return true;
	}


	/* -------------------
	** Lister les Secrets.
	*/
	public function listSecrets( $sgr_id = '', $idn_id = '', $stp_id = '', $env_id = '',
	 $scr_application = '', $scr_host = '', $scr_user = '', $scr_comment = '',
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

		if ( $scr_application != '' ) {
			if ( $Where == '' )
				$Where = 'WHERE T1.scr_application like :scr_application ';
			else
				$Where .= 'AND T1.scr_application like :scr_application ';
			
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

		$Request = 'SELECT DISTINCT ' .
		 'scr_id, scr_application, scr_host, scr_user, scr_comment, scr_alert, ' .
		 'T1.sgr_id, sgr_label, sgr_alert, scr_expiration_date, ' .
		 'T1.stp_id, stp_name, ' .
		 'T1.env_id, env_name ' .
		 'FROM scr_secrets AS T1 ' .
		 'LEFT JOIN sgr_secrets_groups AS T2 ON T1.sgr_id = T2.sgr_id ' .
		 'LEFT JOIN stp_secret_types AS T3 ON T1.stp_id = T3.stp_id ' .
		 'LEFT JOIN env_environments AS T4 ON T1.env_id = T4.env_id ';
		
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
			$Request .= 'ORDER BY scr_application ';
			break;

		 case 'application-desc':
			$Request .= 'ORDER BY scr_application DESC ';
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

		if ( $scr_application != '' ) {
			$scr_application = '%' . $scr_application . '%';
			if ( ! $Result->bindParam( ':scr_application', $scr_application,
			 PDO::PARAM_STR, 60 ) ) {
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

		$Where = '';
		

		if ( $Administrator === false ) {
			$Where = 'WHERE T6.idn_id = :idn_id AND (';
		}
		
		if ( $Where == '' ) $Where .= 'WHERE ';

		$Where .= 'T2.sgr_label like :secret ' .
			'OR T3.stp_name like :secret ' .
			'OR T4.env_name like :secret ' .
			'OR T1.scr_application like :secret ' .
			'OR T1.scr_host like :secret ' .
			'OR T1.scr_user like :secret ' .
			'OR T1.scr_comment like :secret ' .
			'OR T1.scr_expiration_date like :secret ';

		if ( $Administrator === false ) {
			$Where .= ') ' ;
		}


		$Request = 'SELECT DISTINCT ' .
		 'scr_id, scr_application, scr_host, scr_user, scr_comment, scr_alert, ' .
		 'T1.sgr_id, sgr_label, sgr_alert, scr_expiration_date, ' .
		 'T1.stp_id, stp_name, ' .
		 'T1.env_id, env_name ' .
		 'FROM scr_secrets AS T1 ' .
		 'LEFT JOIN sgr_secrets_groups AS T2 ON T1.sgr_id = T2.sgr_id ' .
		 'LEFT JOIN stp_secret_types AS T3 ON T1.stp_id = T3.stp_id ' .
		 'LEFT JOIN env_environments AS T4 ON T1.env_id = T4.env_id ';
		
		if ( $Administrator === false ) {
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
			$Request .= 'ORDER BY scr_application ';
			break;

		 case 'application-desc':
			$Request .= 'ORDER BY scr_application DESC ';
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


		$searchSecret = '%' . $searchSecret . '%';
		if ( ! $Result->bindParam( ':secret', $searchSecret, PDO::PARAM_STR, 30 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( $idn_id != '' ) {
			if ( ! $Result->bindParam( ':idn_id', $idn_id, PDO::PARAM_STR, 30 ) ) {
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
		include_once( DIR_LIBRARIES . '/Class_Security.inc.php' );

		include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-secrets-server.php' );
		include( DIR_LIBRARIES . '/Class_Secrets_Server.inc.php' );

		
		$Security = new Security();

		$Parameters = new IICA_Parameters();

		$Secret_Server = new Secret_Server();

		
		$Request = 'SELECT ' .
		 'scr_id, scr_host, scr_user, scr_password, scr_comment, scr_alert, ' .
		 'scr_creation_date, scr_modification_date, scr_application, scr_expiration_date, ' .
		 'T1.sgr_id, sgr_label, sgr_alert, ' .
		 'T1.stp_id, stp_name, ' .
		 'T1.env_id, env_name ' .
		 'FROM scr_secrets AS T1 ' .
		 'LEFT JOIN sgr_secrets_groups AS T2 ON T1.sgr_id = T2.sgr_id ' .
		 'LEFT JOIN stp_secret_types AS T3 ON T1.stp_id = T3.stp_id ' .
		 'LEFT JOIN env_environments AS T4 ON T1.env_id = T4.env_id ' .
		 'WHERE scr_id = :scr_id ' ;

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
				
				throw new Exception( $Error );
			}
		} else {
			$Occurrence->scr_password = $Security->mc_decrypt( $Occurrence->scr_password );
		}
		
		return $Occurrence;
	}


	/* ----------------------
	** Supprime un Secret.
	*/
	public function delete( $scr_id ) {
		if ( ! $Result = $this->prepare( 'DELETE ' .
		 'FROM scr_secrets ' .
		 'WHERE scr_id = :scr_id' ) ) {
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
			 'WHERE idn_id = :idn_id ';
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

	
	/* -----------------------------
	** Formate le message à remonter dans l'historique.
	*/
	public function formatHistoryMessage( $action, $id_object = '', $type = '',
	 $environment = '', $application = '', $host = '', $user = '' ) {
		$Separator = '|';
		if ( array_key_exists( 'idn_login', $_SESSION ) ) {
			$idn_login = $_SESSION[ 'idn_login' ];
		} else {
			$idn_login = '';
		}
		
		return $idn_login . $Separator . $_SERVER[ 'REMOTE_ADDR' ] . $Separator . $action .
		 $Separator . $id_object . $Separator . $type . $Separator . $environment .
		 $Separator . $application . $Separator . $host . $Separator . $user;
	}	

	
	/* -----------------------------
	** Met à jour l'historique des actions sur les secrets.
	*/
	public function updateHistory( $scr_id, $idn_id, $ach_access = '',
	 $ip_address = '' ) {
		if ( ! $Result = $this->prepare( 'INSERT INTO ach_access_history ' .
			'( scr_id, idn_id, ach_date, ach_access, ach_ip ) ' .
			'VALUES ( :scr_id, :idn_id, :ach_date, :ach_access, :ip_address )' ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
				
		if ( ! $Result->bindParam( ':scr_id', $scr_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
				
		if ( ! $Result->bindParam( ':idn_id', $idn_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		
		$ach_date = date( "Y-m-d H:i:s" );
		if ( ! $Result->bindParam( ':ach_date', $ach_date, PDO::PARAM_STR, 19 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->bindParam( ':ach_access', $ach_access, PDO::PARAM_STR, 300 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->bindParam( ':ip_address', $ip_address, PDO::PARAM_STR, 40 ) ) {
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
	** Lister les événements de l'historique.
	*/
	public function listHistoryEvents( $scr_id = '', $idn_id = '', $ach_date = '',
	 $ach_access = '', $ach_ip = '', $start = 0, $number = 10 ) {
		$Request = 'SELECT ' .
		 'scr_id, idn_login, ach_date, ach_access, ach_ip ' .
		 'FROM ach_access_history as T1 ' .
		 'LEFT JOIN idn_identities as T2 ON T1.idn_id = T2.idn_id ' ;
		
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
		
		if ( $ach_date != '' ) {
			if ( $Where == '' ) $Where = 'WHERE ';
			else $Where .= 'AND ';
			
			$Where .= 'ach_date like "' . $ach_date . '%" ';
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
	public function totalHistoryEvents( $scr_id = '', $idn_id = '', $ach_date = '',
	 $ach_access = '', $ach_ip = '' ) {
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
		
		if ( $ach_date != '' ) {
			if ( $Where == '' ) $Where = 'WHERE ';
			else $Where .= 'AND ';
			
			$Where .= 'ach_date like "' . $ach_date . '%" ';
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

} // Fin class IICA_Secrets



// ===================================
class IICA_Referentials extends IICA_DB_Connector {
/**
* Cette classe gère les référentiels internes.
*
* PHP version 5
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @version 1.1
* @date 2012-11-19
*/

	/* ===============================
	** Connexion à la base de données.
	*/
	public function __construct() {
		parent::__construct();
		
		return true;
	}


	/* -------------------
	** Lister les Droits.
	*/
	public function listRights() {
		$Data = false;
		
		$Request = 'SELECT ' .
		 'rgh_id, rgh_name ' .
		 'FROM rgh_rights ' ;

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
	** Lister les Types de Secret.
	*/
	public function listSecretTypes() {
		$Data = false;
		
		$Request = 'SELECT ' .
		 'stp_id, stp_name ' .
		 'FROM stp_secret_types ' ;

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
	** Lister les Environnements.
	*/
	public function listEnvironments() {
		$Data = false;
		
		$Request = 'SELECT ' .
		 'env_id, env_name ' .
		 'FROM env_environments ' ;

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

} // Fin class IICA_Referentials

?>