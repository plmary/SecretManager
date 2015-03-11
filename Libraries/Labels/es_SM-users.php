<?php
/**
* Libellé spécifique à la gestion des utilisateurs.
*
* @warning Ce fichier doit impérativement être au format UTF-8 pour une gestion cohérente des caractères accentués.
*
* @copyright LGPL License 3.0 http://www.gnu.org/copyleft/lesser.html
* @author Pierre-Luc MARY
* @date 2013-03-25
* @version 1.2
*/

	$L_Title = 'Gestión de Usuarios';
	$L_List_Users = 'Lista de Usuarios';
	$L_List_Entities = 'Lista de Entidades';
	$L_List_Civilities = 'Lista de Cortesías';
	$L_List_Profiles = 'Lista de Perfiles';
	$L_First_Name = 'Nombre de pila' ;
	$L_Last_Name = 'Nombre' ;
	$L_Sex = 'Sexo';
	$L_Man = 'Hombre';
	$L_Woman = 'Mujer';
	
	$L_User_Create = 'Creación de un Usuario';
	$L_User_Delete = 'Supresión de un Usuario';
	$L_User_Modify = 'Cambio de un Usuario';
	$L_User_View = 'Visualización de un Usuario';
	
	$L_Entity_Create = 'Creación de una Entidad';
	$L_Entity_Modify = 'Cambio de una Entidad';
	$L_Entity_Delete = 'Supresión de una Entidad';
	
	$L_Civility_Create = 'Creación de una Civilidad';
	$L_Civility_Modify = 'Cambio de una Civilidad';
	$L_Civility_Delete = 'Supresión de una Civilidad';
	
	$L_Profile_Create = 'Creación de un Perfil';
	$L_Profile_Modify = 'Cambio de un Perfil';
	$L_Profile_Delete = 'Supresión de un Perfil';
	
	$L_Entity = 'Entidad';
	$L_Civility = 'Civilidad';
	$L_Auditor = 'Auditor';
	$L_Administrator = 'Administrador';

	$L_User_Created = 'Usuario creado' ;
	$L_User_Modified = 'Usuario modificado' ;
	$L_User_Deleted = 'Usuario borrado' ;

	$L_Entity_Created = 'Entidad creado' ;
	$L_Entity_Modified = 'Entidad modificado' ;
	$L_Entity_Deleted = 'Entidad borrado' ;

	$L_Civility_Created = 'Civilidad creado' ;
	$L_Civility_Modified = 'Civilidad modificado' ;
	$L_Civility_Deleted = 'Civilidad borrado' ;
	
	$L_Profile_Created = 'Perfil creado' ;
	$L_Profile_Modified = 'Perfil modificado' ;
	$L_Profile_Deleted = 'Perfil borrado' ;
	
	$L_Change_Authenticator_Flag = 'Cambie el autenticador';
	$L_Attempt = 'Intento';
	$L_Disabled = 'Desactivar';
	$L_Enabled = 'Activar';
	$L_Last_Connection = 'Última conexión';
	$L_Expiration_Date = 'Fecha de expiración';
	$L_Updated_Authentication = 'Cambio de fecha autenticador';
	$L_Users_List_Return = 'Volver a la lista de usuarios';
	$L_Never_Connected = 'Nunca conectados';
	$L_To_Activate_User = 'Activar usuario';
	$L_To_Deactivate_User = 'Desactivar usuario';
	
	$L_Authenticator_Reset = 'Restablecer contraseña';
	$L_Password_Reseted = 'Restablecimiento de contraseña';
	$L_Attempt_Reset = 'Cambiar el número de intentos';
	$L_Attempt_Reseted = 'Número de intentos de rearme';
	$L_Expiration_Date_Reset = 'Cambiar la fecha de caducidad';
	$L_Expiration_Date_Reseted = 'Restablecimiento Fecha de vencimiento';
	
	$L_ERR_CREA_Entity = 'Error durante la creación de la Entidad';
	$L_ERR_MODI_Entity = 'Error durante la cambio de la Entidad';
	$L_ERR_DELE_Entity = 'Error durante la supresión de la Entidad';
	$L_ERR_DUPL_Entity = '"Código" o "Etiqueta" ya utilizadas';
	
	$L_ERR_CREA_Civility = 'Error durante la creación de la Civilidad';
	$L_ERR_MODI_Civility = 'Error durante la cambio de la Civilidad';
	$L_ERR_DELE_Civility = 'Error durante la supresión de la Civilidad';
	$L_ERR_DUPL_Civility = '"Nombre de pila" y "nombre" ya se usa ';
	
	$L_ERR_CREA_Identity = 'Error durante la creación de la Identidad';
	$L_ERR_MODI_Identity = 'Error durante la cambio de la Identidad';
	$L_ERR_DELE_Identity = 'Error durante la supresión de la Identidad';
	$L_ERR_DUPL_Identity = '"Nombre de usuario" ya está en uso';
	
	$L_ERR_RST_Password = 'Error al restablecer la contraseña';
	$L_ERR_RST_Attempt = 'Error al restablecer el número de intentos';
	$L_ERR_RST_Expiration = 'Error al cambio de la fecha de caducidad';
	$L_ERR_RST_Disable = 'Error durante la activación o la desactivación del usuario';

	$L_ERR_CREA_Profile = 'Error durante la creación de un Perfil';
	$L_ERR_MODI_Profile = 'Error durante la cambio de un Perfil';
	$L_ERR_DELE_Profile = 'Error durante la supresión de un Perfil';
	$L_ERR_DUPL_Profile = '"Etiqueta" ya utilizado';
	
	$L_Attempt_Exceeded = 'Número de intentos de conexión supera';
	$L_User_Disabled = 'Usuario deshabilitado';
	$L_User_Enabled = 'Usuario habilitado';
	$L_User_Expired = 'Usuario expiró';
	$L_Expiration_Date_Exceeded = 'Fecha de vencimiento alcanzado';
	$L_Last_Connection_Old = 'Fecha de la última conexión demasiado viejo';
	$L_Association_Terminated = 'Asociación completado';

	$L_Users_Profiles = 'Asociación de los Perfiles a la Identidad';
	$L_Profiles_Management = "Gestión de Perfiles";
	$L_Associated_Profiles = "Perfiles asociados";
	$L_Users_Associate = 'Usuarios asociados';
	
	$L_Confirm_Delete_Entity = 'Usted confirma la supresión de esta Entidad : ';
    $L_Confirm_Delete_Civility = 'Usted confirma la supresión de esta Civilidad : ';

    $L_No_User_Profile_Associated = 'Ningún perfil de usuario asociado';

    $L_API = 'API';
    $L_Force_Default_Password = 'Forzado contraseña predeterminada';
    $L_Empty_Default_Password = 'Si está vacío, la contraseña por defecto es inicializado';
    $L_Empty_No_Change_Password = 'Si está vacío, contraseña permanecer sin cambios';
?>