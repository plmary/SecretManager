<?php

// =============================
class IICA_Groups extends IICA_DB_Connector {
/**
* Cette classe gère les groupes de secrets.
*
* PHP version 5
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @date 2014-06-23
*/
    public $LastInsertId;

	public function __construct() {
	/**
	* Connexion à la base de données.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
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
	* Liste tous les Groupes (par défaut) ou tous les Groupes rattachés à une Identité.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-07
	*
	* @param[in] $idn_id (int) Identifiant de l'identité pour laquelle on recherche les Groupes d'appartenance (si précisée, sinon recherche tous les Groupes)
	* @param[in] $orderBy (string) Code de la colonne sur lequel se fera le tri à l'affichage
	* @param[in] $rgh_id (int) Contrôle si l'utilisateur à aux moins les droits minimum sur les Groupes de Secrets (quand il n'est pas Administrateur)
	*
	* @return Renvoi vrai sur le succès de la mise à jour du Groupe, sinon lève une Exception
	*/
		$Request = 'SELECT DISTINCT ' .
		 'T1.sgr_id, sgr_label, sgr_alert, max(T2.rgh_id) AS "rgh_id" ' .
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
		
		$Request .= 'GROUP BY T1.sgr_id, sgr_label, sgr_alert ';
		
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
			$Occurrence->sgr_label = stripslashes( $Occurrence->sgr_label );
			$Data[ $Occurrence->sgr_id ] = $Occurrence;
		}
 
 		return $Data;
	}


	public function get( $sgr_id ) {
	/**
	* Récupère les informations d'un Groupe.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-07
	*
	* @param[in] $sgr_id (int) Identifiant du Groupe de Secret pour lequel on recherche des informations
	*
	* @return Renvoi vrai si les informations sur le Groupe de Secret ont été trouvées, sinon lève une Exception
	*/
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


	public function delete( $sgr_id ) {
	/**
	* Supprime un Groupe.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-07
	*
	* @param[in] $sgr_id (int) Identifiant du Groupe de Secret que l'on souhaite supprimer
	*
	* @return Renvoi vrai sur le Groupe de Secrets à été supprimé, sinon lève une Exception
	*/
		// Démarre la transaction.
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
		$this->commitTransaction();
 
		return true;
	}

	
	/* -----------------------------
	*/
	public function addProfile( $sgr_id, $prf_id, $rgh_id = 1 ) {
	/**
	* Ajoute une association entre un Groupe de Secrets et un Profil.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-07
	*
	* @param[in] $sgr_id (int) Identifiant du Groupe de Secret que l'on souhaite associer
	* @param[in] $prf_id (int) Identifiant du Profil que l'on souhaite associer
	* @param[in] $rgh_id (int) Identifiant du Droit d'Accès de Secret que l'on souhaite associer
	*
	* @return Renvoi vrai si l'association a été sauvegardée, sinon lève une Exception
	*/
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

	
	public function deleteProfile( $grp_id, $prf_id ) {
	/**
	* Détruit l'association entre un Groupe de Secrets et un Profil.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-07
	*
	* @param[in] $sgr_id (int) Identifiant du Groupe de Secret que l'on souhaite dissocier
	* @param[in] $prf_id (int) Identifiant du Profil que l'on souhaite disssocier
	*
	* @return Renvoi vrai si l'association a été supprimée, sinon lève une Exception
	*/
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

	
	public function deleteProfiles( $sgr_id, $prf_id = '' ) {
	/**
	* Détruit toutes les associations entre un Groupe de Secrets ou un Profil.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-07
	*
	* @param[in] $sgr_id (int) Identifiant du Groupe de Secret que l'on souhaite dissocier
	* @param[in] $prf_id (int) Identifiant du Profil que l'on souhaite disssocier
	*
	* @return Renvoi vrai si les associations ont été supprimées, sinon lève une Exception
	*/
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

	
	public function listProfiles( $sgr_id, $Keys = 0 ) {
	/**
	* Liste les Profils associés à un Groupe de Secrets.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-07
	*
	* @param[in] $sgr_id (int) Identifiant du Groupe de Secret sur lequel on souhaite réaliser une recherche
	* @param[in] $Keys (boolean) Drapeau pour définir si l'on souhaite classer le résultat dans un tableau associatif ou séquentiel.
	*
	* @return Renvoi un tableau de Profils (classé par Groupe de Secrets ou non), sinon lève une Exception
	*/
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


	public function isAssociated( $sgr_id ) {
	/**
	* Vérifie si le Groupe de Secrets est associé.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-07
	*
	* @param[in] $sgr_id (int) Identifiant du Groupe de Secret que l'on souhaite contrôler
	*
	* @return Renvoi vrai si le Groupe de Secrets est associé, faux si pas associé ou sinon lève une Exception en cas d'erreur
	*/
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



	public function total( $idn_id = '' ) {
	/**
	* Récupère le nombre total de Groupe de Secrets ou de rattaché à un utilisateur (non administrateur).
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2012-11-07
	*
	* @param[in] $idn_id (int) Identifiant de l'Identité pour lequel on cherche le nombre de Groupe de Secret associé
	*
	* @return Renvoi le nombre total de Groupe de Secrets dans la Base ou associé à un Utilisateur, sinon lève une Exception en cas d'erreur
	*/
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


	public function getGroupForHistory( $sgr_id, $oGroup = '' ) {
	/**
	* Formate une chaine descriptive du Groupe accédé pour le tracer dans l'historique.
	*
	* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
	* @author Pierre-Luc MARY
	* @date 2014-06-23
	*
	* @param[in] $sgr_id (int) Identifiant du Groupe de Secret qui a été accédé
	* @param[in] $oGroup (object) Objet décrivant le Groupe de Secret qui vient d'être créé
	*
	* @return Renvoi la chaîne formattée
	*/
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


	public function searchIdByLabel( $sgr_label ) {
		/**
		 * Recherche "l'ID" d'un Groupe de Secrets par son "libellé".
		 *
		 * @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
		 * @author Pierre-Luc MARY
		 * @date 2015-03-06
		 *
		 * @param[in] $sgr_label (str) Libellé par lequel on va rechercher le Groupe de Secret
		 *
		 * @return Renvoi l'ID du Groupe de Secret, sinon lève une exception.
		 */
		if ( $sgr_label == '' ) throw new Exception( 'Parameter "sgr_label" mandatory' );

		$Request = 'SELECT ' .
		 'sgr_id ' .
		 'FROM sgr_secrets_groups ' .
		 'WHERE UPPER( sgr_label ) like :sgr_label ';

		if ( ! $Result = $this->prepare( $Request ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		$sgr_label = '%' . strtoupper( $sgr_label ) . '%';
		
		if ( ! $Result->bindParam( ':sgr_label', $sgr_label, PDO::PARAM_STR, 60 ) ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}

		if ( ! $Result->execute() ) {
			$Error = $Result->errorInfo();
			throw new Exception( $Error[ 2 ], $Error[ 1 ] );
		}
		
		$Occurrence = $Result->fetchObject() ;
		
		return $Occurrence->sgr_id;
	}
	
} // Fin class IICA_Groups

?>