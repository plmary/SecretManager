<?php

class IICA_Authentications extends PDO {
/**
* Cette classe gère l'authentification des utilisateurs.
*
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @version 1.0
* @date 2012-11-07
*
*/


	public function __construct( $_Host, $_Port, $_Driver, $_Base, $_User, $_Password ) {
	/**
	* Connexion à la base de données.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-07
	*
	* @param[in] $_Host Noeud sur lequel s'exécute la base de données
	* @param[in] $_Port Port IP sur lequel répond la base de données
	* @param[in] $_Driver Type de la base de données
	* @param[in] $_Base Nom de la base de données
	* @param[in] $_User Nom de l'utilisateur dans la base de données
	* @param[in] $_Password Mot de passe de l'utilisateur dans la base de données
	*
	* @return Renvoi un booléen sur le succès de la connexion à la base de données
	*/
		$DSN = $_Driver . ':host=' . $_Host . ';port=' . $_Port . ';dbname=' . $_Base ;
		
		parent::__construct( $DSN, $_User, $_Password );
		
		return true;
	}


	public function authentication( $Login, $Authenticator, $Type = 'database',
	 $Salt = '', $_Port = '' ) {
	/**
	* Contrôle les éléments d'authentification
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.1
	* @date 2013-04-07
	*
	* @param[in] $Login Nom de connexion de l'utilisateur
	* @param[in] $Authenticator Authentifiant de l'utilisageur.
	* @param[in] $Type Type d'authentification (par défaut 'database', l'autre valeur possible est radius)
	* @param[in] $Salt Grain de sel à utiliser pour calculer le hash du mot de passe
	*
	* @param[out] $_SESSION['idn_id'] Identifiant de l'utilisateur connecté
	* @param[out] $_SESSION['ent_id'] Identifiant de l'entité d'appartenance de l'utilisateur
	* @param[out] $_SESSION['cvl_id'] Identifiant de la civilité de l'utilisateur
	* @param[out] $_SESSION['idn_login'] Nom de connexion de l'utilisateur
	* @param[out] $_SESSION['idn_change_authenticator'] Flag sur la nécessité de changer de mot de passe
	* @param[out] $_SESSION['idn_attempt'] Nombre de tentative de connexion
	* @param[out] $_SESSION['idn_updated_authentication'] Date de mise à jour du mot de passe
	* @param[out] $_SESSION['idn_last_connection'] Date de dernière connexion
	* @param[out] $_SESSION['idn_super_admin'] Flag sur le droit Super Administrateur
	* @param[out] $_SESSION['idn_auditor'] Flag sur le droit Auditeur
	* @param[out] $_SESSION['cvl_last_name'] Nom usuel de l'utilisateur
	* @param[out] $_SESSION['cvl_first_name'] Prénom de l'utilisateur
	* @param[out] $_SESSION['cvl_sex'] Sexe de l'utilisateur
	* @param[out] $_SESSION['ent_code'] Code de l'entité d'appartenance de l'utilisateur
	* @param[out] $_SESSION['ent_label'] Libellé de l'entité d'appartenance de l'utilisateur
	* @param[out] $_SESSION['Expired'] Temps d'expiration
	* @exception Exception Exception standard. Le message retourné étant applicatif dans la majorité des cas
	*
	* @return Renvoi vrai en cas de succès ou génère une exception en cas d'erreur.
	*/
		include( DIR_LIBRARIES . '/Config_Hash.inc.php' );
		include( DIR_LIBRARIES . '/Config_Authentication.inc.php' );
		include( DIR_LABELS . '/' . $_SESSION[ 'Language' ] . '_SM-users.php' );

		
		$Security = new Security();


		// -----------------------------------
		// Récupère les données de l'identité.
		$Request = 'SELECT ' .
		 'T1.idn_id, ' .
		 'T1.ent_id, ' .
		 'T1.cvl_id, ' .
		 'T1.idn_login, ' .
		 'T1.idn_change_authenticator, ' .
		 'T1.idn_attempt, ' .
		 'T1.idn_updated_authentication, ' .
		 'T1.idn_last_connection, ' .
		 'T1.idn_super_admin, ' .
		 'T1.idn_auditor, ' .
		 'T1.idn_disable, ' .
		 'T1.idn_expiration_date, ' .
		 'T2.cvl_last_name, ' .
		 'T2.cvl_first_name,' .
		 'T2.cvl_sex, ' .
		 'T3.ent_code, ' .
		 'T3.ent_label ' .
		 'FROM idn_identities AS T1 ' .
		 'LEFT JOIN cvl_civilities AS T2 ON T1.cvl_id = T2.cvl_id ' .
		 'LEFT JOIN ent_entities AS T3 ON T1.ent_id = T3.ent_id ' .
		 'WHERE T1.idn_login = :Login ' ;
		
		if ( $Type == 'database') {
			$Request .= 'AND T1.idn_authenticator = :Authenticator ' ;
		}
		
		$Request .= 'AND T1.idn_logical_delete = false ' ;
		 
		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		
		if ( ! $Result->bindParam( ':Login', $Login, PDO::PARAM_STR, 20 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}


		switch( $Type ) {
		 default:
		 case 'database':
			if ( $Salt == '' ) {
				$Salt = $_salt_user;
			}

			$Authenticator = sha1( $Authenticator . $Salt );
	  
			if ( ! $Result->bindParam( ':Authenticator', $Authenticator,
			 PDO::PARAM_STR, 64 ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
			
			break;
		
		 case 'radius':
			include( DIR_RADIUS . '/radius.class.php' );
			include( DIR_LIBRARIES . '/Config_Radius.inc.php' );

			$Radius_Suffix = '';
			$UPD_Timeout = 5;

			if ( isset( $_Radius_Authentication_Port ) ) {
				$authentication_port = $_Radius_Authentication_Port;
			} else {			
				$authentication_port = 1812;
			}
			
			if ( isset( $_Radius_Accounting_Port ) ) {
				$accounting_port = $_Radius_Accounting_Port;
			} else {			
				$accounting_port = 1813;
			}

			$radius = new Radius( $_Radius_Server, $_Radius_Secret_Common,
			 $Radius_Suffix, $UPD_Timeout, $authentication_port, $accounting_port );

			if ( ! $radius->AccessRequest( $Login, $Authenticator ) ) {
				return false;
			}
			
			break;
		
		 case 'ldap':
			include( DIR_LIBRARIES . '/Config_LDAP.inc.php' );
			
			$LDAP_RDN = $_LDAP_RDN_Prefix . '=' . $Login . ',' . $_LDAP_Organization;

			// Connexion au serveur LDAP
			$ldap_c = ldap_connect( $_LDAP_Server, $_LDAP_Port );
			if ( $ldap_c === FALSE ) {
				print( ldap_error( $ldap_c ) . ' (' . ldap_errno( $ldap_c ) . ')' );
				return FALSE;
			}
	 
			if ( ldap_set_option( $ldap_c, LDAP_OPT_PROTOCOL_VERSION,
			 $_LDAP_Protocol_Version ) === FALSE ) {
				print( ldap_error( $ldap_c ) . ' (' . ldap_errno( $ldap_c ) . ')' );
				return FALSE;
			}

			if ( $ldap_c ) {
				// Authentification au serveur LDAP
				$ldap_b = ldap_bind( $ldap_c, $LDAP_RDN, $Authenticator );

				// Vérification de l'authentification
				if ( ! $ldap_b ) {
					print( ldap_error( $ldap_c ) . ' (' . ldap_errno( $ldap_c ) . ')' );
					return FALSE;
				}
			}

			break;
		}
		 
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Occurrence = $Result->fetchObject();


		/* ---------------------------------------------------------------------
		** Si pas d'occurrence, alors mot de passe ou nom d'utilisateur inconnu.
		*/
		if ( $Occurrence == '' ) {
			return false;
		}


		/* ---------------------------------------------
		** Vérifie si l'utilisateur n'est pas désactivé.
		*/		
		if ( $Occurrence->idn_disable == 1 ) {
			throw new Exception( $L_User_Disabled, -1 );
		}


		/* ----------------------------------------
		** Vérifie si l'utilisateur n'a pas expiré.
		** Date d'expiration dépassée.
		*/
		if ( $Occurrence->idn_expiration_date != '0000-00-00 00:00:00' ) {
			if ( $Occurrence->idn_expiration_date < date( 'Y-m-d' ) ) {
				throw new Exception( $L_User_Expired . '<br/>' .
				 $L_Expiration_Date_Exceeded, -1 );
			}
		}


		/* ----------------------------------------
		** Vérifie si l'utilisateur n'a pas expiré.
		** Date de dernière connexion supérieure au temps de vie d'un utilisateur.
		*/
		if ( $Occurrence->idn_last_connection != '0000-00-00 00:00:00' ) {
			$datetime1 = new DateTime( date( 'Y-m-d' ) );
			$datetime2 = new DateTime( $Occurrence->idn_last_connection );

			$interval = $datetime1->diff( $datetime2 );

			if ( $interval->format('%R') == '-' ) {
				if ( $interval->format('%m') >= $_Default_User_Lifetime ) {
					throw new Exception( $L_User_Expired . '<br/>' .
					 $L_Last_Connection_Old, -1 );
				}
			}
		}
		
		
		/* -----------------------------------------------------------------
		** Vérifie si le nombre de tentative de connexion n'est pas dépassé.
		*/
		if ( $Occurrence->idn_attempt > $_Max_Attempt ) {
			throw new Exception( $L_Attempt_Exceeded, -1 );
		}



		// -----------------------------------
		// Met à jour la date de connexion.
		$Request = 'UPDATE idn_identities SET ' .
		 'idn_attempt = 0, ' .
		 'idn_last_connection = "' . date( 'Y-m-d H:i:s' ) . '" ' .
		 'WHERE idn_id = ' . $Occurrence->idn_id ;
		 
		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		 
		 
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		

		$_SESSION[ 'idn_id' ] = $Occurrence->idn_id ;
		$_SESSION[ 'ent_id' ] = $Occurrence->ent_id ;
		$_SESSION[ 'cvl_id' ] = $Occurrence->cvl_id ;
		$_SESSION[ 'idn_login' ] = $Occurrence->idn_login ;
		$_SESSION[ 'idn_change_authenticator' ] = $Occurrence->idn_change_authenticator ;
		$_SESSION[ 'idn_attempt' ] = $Occurrence->idn_attempt ;
		$_SESSION[ 'idn_updated_authentication' ] =
		 $Occurrence->idn_updated_authentication ;
		$_SESSION[ 'idn_last_connection' ] = $Occurrence->idn_last_connection ;
		$_SESSION[ 'idn_super_admin' ] = $Occurrence->idn_super_admin ;
		$_SESSION[ 'idn_auditor' ] = $Occurrence->idn_auditor ;

		$_SESSION[ 'cvl_last_name' ] = $Security->XSS_Protection(
		 $Occurrence->cvl_last_name );
		$_SESSION[ 'cvl_first_name' ] = $Security->XSS_Protection( 
		 $Occurrence->cvl_first_name );
		$_SESSION[ 'cvl_sex' ] = $Occurrence->cvl_sex ;

		$_SESSION[ 'ent_code' ] = $Security->XSS_Protection( $Occurrence->ent_code );
		$_SESSION[ 'ent_label' ] = $Security->XSS_Protection( $Occurrence->ent_label );

		$this->saveTimeSession();

 		return true;
	}


	public function is_connect() {
	/** -----------------------------
	* Contrôle si l'utilisateur est déjà connecté.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-07
	*
	* @return Retourne vrai si l'utilisateur est connecté. Sinon, retourne faux.
	*/
		if ( isset( $_SESSION[ 'idn_id' ] ) ) {
			return true;
		}

		return false;
	}


	public function disconnect() {
	/** -----------------------------
	* Détruit les variables de la session.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-07
	*
	* @return Retourne toujours vrai
	*/
		unset( $_SESSION[ 'idn_id' ] );
		unset( $_SESSION[ 'ent_id' ] );
		unset( $_SESSION[ 'cvl_id' ] );
		unset( $_SESSION[ 'cvl_last_name' ] );
		unset( $_SESSION[ 'cvl_first_name' ] );
		unset( $_SESSION[ 'Expired' ] );
		unset( $_SESSION[ 'idn_login' ] );
		unset( $_SESSION[ 'searchSecret' ] );

		return true;
	}


	public function is_administrator() {
	/** -----------------------------
	* Contrôle si l'utilisateur connecté est un administrateur.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-07
	*
	* @return Retourne vrai si l'utilisateur est un administrateur, sinon retourne faux
	*/
		if ( $_SESSION[ 'idn_super_admin' ] == true ) {
			return true;
		}

		return false;
	}


	public function is_auditor() {
	/** -----------------------------
	* Contrôle si l'utilisateur connecté est un auditeur.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-07
	*
	* @return Retourne vrai si l'utilisateur est un auditeur, sinon retourne faux
	*/
		if ( $_SESSION[ 'idn_auditor' ] == true ) {
			return true;
		}

		return false;
	}


	public function resetPassword( $idn_id ) {
	/** -----------------------------
	* Ecrase le mot de passe de l'utilisateur par celui par défaut.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-07
	*
	* @param[in] $idn_id Identifiant de l'utilisateur
	*
	* @return Retourne vrai en cas de succès, sinon lève une exception en cas d'erreur
	*/
		include( DIR_LIBRARIES . '/Config_Hash.inc.php' );
		include( DIR_LIBRARIES . '/Config_Authentication.inc.php' );
		
		if ( ! class_exists( 'Security' ) ) {
			include( DIR_LIBRARIES . '/Class_Security.inc.php' );
		}
		
		$Security = new Security();
		
		// ===========================================================
		// Calcule un nouveau grain de sel spécifique à l'utilisateur.
		$size = 8;
		$complexity = 2; // Majuscules, Minuscules et Chiffres
		
		$Salt = $Security->passwordGeneration( $size, $complexity );


		// ===================================
		// Récupère les données de l'identité.
		$Request = 'UPDATE idn_identities SET ' .
		 'idn_change_authenticator = 1, ' .
		 'idn_authenticator = :Authenticator, ' .
		 'idn_salt = :Salt, ' .
		 'idn_updated_authentication = \'' . date( 'Y-m-d H:i:s' ) . '\' ' .
		 'WHERE idn_id = :idn_id ' ;
		 
		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		
		if ( ! $Result->bindParam( ':idn_id', $idn_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Authenticator = sha1( $_Default_Password . $Salt );
		
		if ( ! $Result->bindParam( ':Authenticator', $Authenticator,
		 PDO::PARAM_STR, 64 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		 
		if ( ! $Result->bindParam( ':Salt', $Salt, PDO::PARAM_STR, 32 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		 
		 
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

 		return true;
	}
	


	public function changePassword( $Idn_Id, $O_Password, $N_Password ) {
	/** -----------------------------
	* Change le mot de passe de l'utilisateur
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-07
	*
	* @param[in] $idn_id Identifiant de l'utilisateur
	* @param[in] $O_Password Ancien mot de passe
	* @param[in] $N_Password Nouveau mot de passe
	*
	* @return Retourne vrai en cas de succès, sinon lève une exception en cas d'erreur
	*/
		include( DIR_LIBRARIES . '/Config_Hash.inc.php' );
		include( DIR_LIBRARIES . '/Config_Authentication.inc.php' );

		if ( ! class_exists( 'Security' ) ) {
			include( DIR_LIBRARIES . '/Class_Security.inc.php' );
		}
		
		$Security = new Security();
		
		// ===========================================================
		// Calcule un nouveau grain de sel spécifique à l'utilisateur.
		$size = 8;
		$complexity = 2; // Majuscules, Minuscules et Chiffres
		
		$Salt = $Security->passwordGeneration( $size, $complexity );


		// ===================================
		// Récupère les données de l'identité.
		$Request = 'UPDATE idn_identities SET ' .
		 'idn_authenticator = :N_Password, ' .
		 'idn_salt = :Salt, ' .
		 'idn_updated_authentication = \'' . date( 'Y-m-d H:i:s' ) . '\', ' .
		 'idn_change_authenticator = 0 ' .
		 'WHERE idn_id = :Idn_Id AND idn_authenticator = :O_Password ' ;
		 
		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		
		if ( ! $Result->bindParam( ':Idn_Id', $Idn_Id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( ! $Result->bindParam( ':Salt', $Salt, PDO::PARAM_STR, 32 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		

		$N_Password = sha1( $N_Password . $Salt );
		
		if ( ! $Result->bindParam( ':N_Password', $N_Password,
		 PDO::PARAM_STR, 64 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		 

		$Old_Salt = $this->getSalt( '', $Idn_Id );

		$O_Password = sha1( $O_Password . $Old_Salt );
		
		if ( ! $Result->bindParam( ':O_Password', $O_Password,
		 PDO::PARAM_STR, 64 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		 
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		if ( $Result->rowCount() == 0 ) return false;

 		return true;
	}



	public function resetAttempt( $idn_id ) {
	/** -----------------------------
	* Remet à zéro le nombre de tentative de connexion.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-07
	*
	* @param[in] $idn_id Identifiant de l'utilisateur
	*
	* @return Retourne vrai en cas de succès, sinon lève une exception en cas d'erreur
	*/
		include( DIR_LIBRARIES . '/Config_Authentication.inc.php' );

		// ===================================
		// Récupère les données de l'identité.
		$Request = 'UPDATE idn_identities SET ' .
		 'idn_attempt = 0 ' .
		 'WHERE idn_id = :idn_id ' ;
		 
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

 		return true;
	}



	public function resetExpirationDate( $idn_id ) {
	/** -----------------------------
	* Réactualise la date d'expiration de l'utilisateur.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-07
	*
	* @param[in] $idn_id Identifiant de l'utilisateur
	*
	* @return Retourne vrai en cas de succès, sinon lève une exception en cas d'erreur
	*/
		include( DIR_LIBRARIES . '/Config_Authentication.inc.php' );

		// ===================================
		// Récupère les données de l'identité.
		$Request = 'UPDATE idn_identities SET ' .
		 'idn_expiration_date = :idn_expiration_date ' .
		 'WHERE idn_id = :idn_id ' ;
		 
		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		
		$NextDate  = strftime( "%Y-%m-%d",
		 mktime( 0, 0, 0, date("m") + $_Default_User_Lifetime, date("d"), date("Y") ) );

		if ( ! $Result->bindParam( ':idn_expiration_date', $NextDate,
		 PDO::PARAM_STR, 19 ) ) {
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

 		return true;
	}



	public function setDisable( $idn_id, $Status ) {
	/** -----------------------------
	* Active ou désactive un utilisateur
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-07
	*
	* @param[in] $idn_id Identifiant de l'utilisateur
	* @param[in] $Status Statut d'activation de l'utilisateur (0 = active, 1 = désactive)
	*
	* @return Retourne vrai en cas de succès, sinon lève une exception en cas d'erreur
	*/
		include( DIR_LIBRARIES . '/Config_Hash.inc.php' );
		include( DIR_LIBRARIES . '/Config_Authentication.inc.php' );

		// ===================================
		// Récupère les données de l'identité.
		$Request = 'UPDATE idn_identities SET ' .
		 'idn_disable = :Status ' .
		 'WHERE idn_id = :idn_id ' ;
		 
		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		
		if ( ! $Result->bindParam( ':idn_id', $idn_id, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		
		if ( ! $Result->bindParam( ':Status', $Status, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		 
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

 		return true;
	}



	public function getGroups( $idn_id ) {
	/** -----------------------------
	* Récupère les Groupes de Secrets rattachés à un Utilisateur.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-07
	*
	* @param[in] $idn_id Identifiant de l'utilisateur
	*
	* @return Retourne les Groupes de Secrets associés à l'utilisateur, sinon lève une exception en cas d'erreur
	*/

		// ===================================
		// Récupère les données de l'identité.
		$Request = 'SELECT DISTINCT sgr_id, rgh_id ' .
		 'FROM prsg_profiles_secrets_groups AS t1 ' .
		 'LEFT JOIN idpr_identities_profiles AS t2 ON t2.prf_id = t1.prf_id ' .
		 'WHERE idn_id = :idn_id ' .
		 'ORDER BY sgr_id ' ;
		 
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

		$Data = array();
		$Data[ 'W' ] = 0;
		
		while ( $Occurrence = $Result->fetchObject() ) {
			$Data[ $Occurrence->sgr_id ][] = $Occurrence->rgh_id ;

			// Sauvegarde de façon globale, qu'il a un droit d'écriture.
			if ( $Occurrence->rgh_id == 2 ) $Data[ 'W' ] = 1; 
		}
 
 		return $Data;
	}
   	

	public function validTimeSession() {
	/** -----------------------------
	* Contrôle si la session n'a pas expirée.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-07
	*
	* @return Retourne vrai si la session n'a pas expirée, sinon retourne faux.
	*/
		if ( $_SESSION[ 'Expired' ] < time() ) {
			return FALSE;
		} else {
			return TRUE;
		}
	}
   	

	public function saveTimeSession() {
	/** -----------------------------
	* Contrôle si la session n'a pas expirée.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-07
	*
	* @return Retourne vrai en cas de succès, sinon retourne faux
	*/
		include_once( DIR_LIBRARIES . '/Class_IICA_Parameters_PDO.inc.php' );
		include( DIR_LIBRARIES . '/Config_Access_DB.inc.php' );

		$Parameters = new IICA_Parameters( $_Host, $_Port, $_Driver, $_Base, $_User, $_Password );

		$_SESSION[ 'Expired' ] = time() + ( $Parameters->get( 'expiration_time' ) * 60 );
		
		return TRUE;
	}



	public function addAttempt( $Login ) {
	/** -----------------------------
	* Incrémente le compteur de tentative de connexion (suite à une erreur de connexion).
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-07
	*
	* @param[in] $Login Nom de l'utilisateur à traiter
	*
	* @return Retourne la nouvelle valeur du nombre de tentative ou lève une exception en cas d'erreur.
	*/
		// ===================================
		// Récupère la dernière valeur de tentative de connexion.
		$Request = 'SELECT idn_attempt FROM idn_identities ' .
		 'WHERE idn_login = :Login ' ;
		 
		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		
		if ( ! $Result->bindParam( ':Login', $Login, PDO::PARAM_STR, 20 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		 
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		$Occurrence = $Result->fetchObject();

		if ( $Occurrence == '' ) {
			return 0;
		}

		$Attempt = $Occurrence->idn_attempt + 1;

		$Request = 'UPDATE idn_identities SET ' .
		 'idn_attempt = :Attempt ' .
		 'WHERE idn_login = :Login ' ;
		 
		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		
		if ( ! $Result->bindParam( ':Login', $Login, PDO::PARAM_STR, 20 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		
		if ( ! $Result->bindParam( ':Attempt', $Attempt, PDO::PARAM_INT ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		 
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

 		return $Attempt;
	}


	public function getSalt( $idn_login, $idn_id = '' ) {
	/** -----------------------------
	** Récupère le grain de sel de l'utilisateur
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @version 1.0
	* @date 2012-11-07
	*
	* @param[in] $idn_login Nom de l'utilisateur à traiter
	*
	* @return Retourne le grain de sel ou lève une exception en cas d'erreur.
	*/

		// ===================================
		// Récupère les données de l'identité.
		$Request = 'SELECT idn_salt ' .
		 'FROM idn_identities ';
		 
		if ( $idn_id != '' ) {
			$Request .= 'WHERE idn_id = :idn_id ';
		} else {
			$Request .= 'WHERE idn_login = :idn_login ';
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
		} else {
			if ( ! $Result->bindParam( ':idn_login', $idn_login, PDO::PARAM_STR, 20 ) ) {
				$Error = $Result->errorInfo();
				throw new Exception( $Error[ 2 ], $Error[ 1 ] );
			}
		}
		
		 
		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Data = $Result->fetchObject();
		if ( $Data == '' ) {
			return false;
		}
	
		return $Data->idn_salt;
	}

}

?>