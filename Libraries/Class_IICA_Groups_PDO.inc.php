<?php

include( DIR_LIBRARIES . '/Config_Access_Tables.inc.php' );

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



	/* -------------------
	** Formate une chaine descriptive du Groupe accédé pour le tracer dans l'historique.
	*/
	public function getGroupForHistory( $sgr_id, $oGroup = '' ) {
		if ( $sgr_id == '' ) return '';

		include_once( DIR_LIBRARIES . '/Class_HTML.inc.php');

		$pHTML = new HTML();

    	// Récupère les dernières informations du Secret qui vient d'être modifié.
    	if ( $oGroup == '' ) $oGroup = $this->get( $sgr_id );

    	// Récupère les libellés pour le message
    	$Labels = $pHTML->getTextCode( array( 'L_Group', 'L_Alert' ) );

    	return ' (' . $Labels['L_Group'] . ':"' . $oGroup->sgr_label . '", ' .
    		$Labels['L_Alert'] . ':"' . $oGroup->sgr_alert . '")';
	}

} // Fin class IICA_Groups

?>