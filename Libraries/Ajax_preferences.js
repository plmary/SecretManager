/**
* Ce script gère une partie des fonctions Ajax disponible pour le script "SM-preferences.php.
*
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @date 2014-06-19
*/


// Active les fonctions ci-dessous quand le DOM de la page HTML est fini de charger.
$(document).ready( function() {
    // Déclenche la recherche suite à l'utilisation du click gauche sur le bouton de recherche.
    $("#iSearchSecret").on('click', function(){
        var recherche = $(this).val();
        
        $('tbody#listeSecrets').html("");

        $.ajax({
            url: '../SM-home.php?action=R',
            type: 'POST',
            data: $.param({'Search_Secrets': recherche}),
            success: function(reponse){
                if(reponse){
                    $('tbody#listeSecrets').html(reponse);

                    var total = $('tbody#listeSecrets tr').attr('data-total');

                    $('#total').text( total );
                }
            },
            error: function(reponse) {
                alert('Erreur sur serveur "Ajax_preferences.js" - "R" : ' + reponse['responseText']);
            }
        }); 
    });
    

    // Déclenche la sauvegarde des paramètres sur le fonctionnement du SecretServer.
    $("#iSaveUseServer").click(function(){
        var UseSecretServer = $('#Use_SecretServer').val();
        var StopSecretServer = $('#Stop_SecretServer').val();

        $.ajax({
            url: '../SM-preferences.php?action=SUX',
            type: 'POST',
            data: $.param({'UseSecretServer': UseSecretServer, 'StopSecretServer': StopSecretServer}),
            dataType: 'json',
            success: function(reponse){
                if ( reponse['Status'] == 'success' || reponse['Status'] == 'error' ) {
                    showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
                } else {
                    alert('Erreur sur serveur : ' + reponse);
                }
            },
            error: function(reponse) {
                alert('Erreur sur serveur "Ajax_preferences.js" - "SUX" : ' + reponse['responseText']);
            }
        }); 
    });

    
    // Décleche la sauvegarde des Propiétés sur les Clés de Chiffrement
    $("#iSaveKeysProperties").click(function(){
        var Operator_Key_Size = $('#Operator_Key_Size').val();
        var Operator_Key_Complexity = $('#Operator_Key_Complexity').val();

        var Mother_Key_Size = $('#Mother_Key_Size').val();
        var Mother_Key_Complexity = $('#Mother_Key_Complexity').val();

        $.ajax({
            url: '../SM-preferences.php?action=SKX',
            type: 'POST',
            data: $.param({
                'Operator_Key_Size': Operator_Key_Size,
                'Operator_Key_Complexity' : Operator_Key_Complexity,
                'Mother_Key_Size' : Mother_Key_Size,
                'Mother_Key_Complexity' : Mother_Key_Complexity
                }),
            dataType: 'json',
            success: function(reponse){
                if ( reponse['Status'] == 'success' || reponse['Status'] == 'error' ) {
                    showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
                } else {
                    alert('Erreur sur serveur : ' + reponse);
                }
            },
            error: function(reponse) {
                alert('Erreur interne sur serveur "Ajax_preferences.js" - "SKX" : ' + reponse['responseText']);
            }
        }); 
    });
    
    // Désactive le formatage du Syslog, s'il n'y a pas de trace Syslog à générer.
    $('#i_alert_syslog').change( function() {
    	if ( $('#i_alert_syslog').val() == 0 ) {
    		$("#i_syslog_format").prop('disabled', 'disabled');
    	} else {
    		$("#i_syslog_format").removeAttr('disabled');
    	}
    });
});


// Modifie ou supprime un Secret "en ligne".
function setSecret( secret_id, action ) {
    var action = action || '';
    
    if ( action == 'D' ) {
        var Delete_Mode = ' disabled';
    } else {
        var Delete_Mode = '';
    }
    
    cancel();

    $.ajax({
        url: '../SM-home.php?action=AJAX_LV',
        type: 'POST',
        data: $.param({'scr_id': secret_id}),
        dataType: 'json', // le résultat est transmit dans un objet JSON
        success: function(reponse){
            if ( $('#' + secret_id + ' td p').length == 0 ) {
                var group, group_id, type, type_id, environment, environment_id, application,
                    host, user, expiration, comment, right, dirname, L_Edit, L_Delete, L_View;
                $('tr#' + secret_id + ' td').each( function( index ) {
                    if ( index == 0 ) {                        
                        group_id = $(this).attr('data-id');

                        $.each(reponse['listGroups'], function(attribut, valeur) {
                            if ( group_id == valeur['sgr_id'] ) {
                                var Selected = ' selected';
                            } else {
                                var Selected = '';
                            }
                            
                            group = group + '<option value=' + valeur['sgr_id'] + Selected +
                                '>' + valeur['sgr_label'] + '</option>';
                        });
                
                        group_o = $(this).text();                
                    } else if ( index == 1 ) {
                        type_id = $(this).attr('data-id');
                            
                        $.each(reponse['listTypes'], function(attribut, valeur) {
                            if ( type_id == valeur['stp_id'] ) {
                                var Selected = ' selected';
                            } else {
                                var Selected = '';
                            }

                            type = type + '<option value=' + valeur['stp_id'] + Selected +
                                '>' + valeur['stp_name'] + '</option>';
                        });

                        type_o = $(this).text();
                    } else if ( index == 2 ) {
                        environment_id = $(this).attr('data-id');

                        $.each(reponse['listEnvironments'], function(attribut, valeur) {
                            if ( environment_id == valeur['env_id'] ) {
                                var Selected = ' selected';
                            } else {
                                var Selected = '';
                            }

                            environment = environment + '<option value=' + valeur['env_id'] +
                                Selected + '>' + valeur['env_name'] + '</option>';
                        });

                        environment_o = $(this).text();
                    } else if ( index == 3 ) {
                        application = $(this).text();
                    } else if ( index == 4 ) {
                        host = $(this).text();
                    } else if ( index == 5 ) {
                        user = $(this).text();
                    } else if ( index == 6 ) {
                        expiration = $(this).text();
                        expiration_color = $(this).attr('class');
                    } else if ( index == 7 ) {
                        comment = $(this).text();
                    }
                } );

                var currentClass = $('tr#' + secret_id).attr('class');
                currentClass = currentClass.replace("surline", "");
        
                var L_Cancel = $('tr#' + secret_id).attr('data-cancel');
                var L_Modify = $('tr#' + secret_id).attr('data-modify');
                var L_Delete = $('tr#' + secret_id).attr('data-delete');
                
                if ( reponse['Password'] == null ) {
                    var password = '*********';
                } else {
                    var password = reponse['Password'];
                }
                
                if ( reponse['alert'] == true ) {
                    var alert = 'checked';
                } else {
                    var alert = '';
                }

                var newOcc = '<tr id="MOD_' + secret_id + '" class="' + currentClass + '" style="cursor: pointer;">' +
                    '<td colspan="9" style="margin:0;padding:0;border:2px solid #568EB6;">' +
                    '<div id="modification-zone">' +
                    '<label for="'+'group_'+secret_id+'">' + reponse['L_Group'] + '</label>' +
                    '<select id="'+'group_'+secret_id+'" class="input-xlarge"' + Delete_Mode + '>' +
                    group +
                    '</select>' +
                    '<label for="'+'type_'+secret_id+'">' + reponse['L_Type'] + '</label>' +
                    '<select id="'+'type_'+secret_id+'" class="input-medium"' + Delete_Mode + '>' +
                    type +
                    '</select>' +
                    '<label for="'+'environment_'+secret_id+'">' + reponse['L_Environment'] + '</label>' +
                    '<select id="'+'environment_'+secret_id+'"' + ' class="input-medium"' + Delete_Mode + '>' +
                    environment +
                    '</select><br/>' +
                    '<label for="'+'application_'+secret_id+'">' + reponse['L_Application'] + '</label>' +
                    '<input id="'+'application_'+secret_id+'" type="text" value="' + application +
                    '" class="input-medium"' + Delete_Mode + '>' +
                    '<label for="'+'host_'+secret_id+'">' + reponse['L_Host'] + '</label>' +
                    '<input id="'+'host_'+secret_id+'" type="text" value="' + host + '" class="input-medium"' + 
                    Delete_Mode + '>' +
                    '<label for="'+'user_'+secret_id+'">' + reponse['L_User'] + '</label>' +
                    '<input id="'+'user_'+secret_id+'" type="text" value="' + user + '" class="input-medium"' + 
                    Delete_Mode + '>' +
                    '<label for="'+'secret_'+secret_id+'">' + reponse['L_Password'] + '</label>';

                if ( action == 'D' ) {
                    password = '************';
                }
                    
                newOcc = newOcc + '<input id="'+'secret_'+secret_id+'" type="text" value="' + password + 
                    '" class="input-medium"' + Delete_Mode + '><br/>' +
                    '<label for="'+'alert_'+secret_id+'">' + reponse['L_Alert'] + '</label>' +
                    '<input id="'+'alert_'+secret_id+'" type="checkbox" ' + alert + Delete_Mode + '>' +
                    '<label for="'+'expiration_'+secret_id+'">' + reponse['L_Expiration_Date'] + '</label>' +
                    '<input id="'+'expiration_'+secret_id+'" type="text" value="' + expiration + '" class="input-medium"' +
                    Delete_Mode + '>' +
                    '<label for="'+'comment_'+secret_id+'">' + reponse['L_Comment'] + '</label>' +
                    '<input id="'+'comment_'+secret_id+'" type="text" value="' + comment + '" class="input-xxlarge"' + 
                    Delete_Mode + '>' +
                    '</div>' +
                    '<p style="margin-top: 6px;margin-bottom: 6px;padding:0">' +
                    '<span class="div-left"><a class="button" href="javascript:cancel();">' + L_Cancel + '</a></span>';
                    
                if ( action == 'D' ) {
                    newOcc = newOcc + '<span class="div-right"><a class="button" href="javascript:remove('+secret_id+')">' +
                        L_Delete + '</a></span>';
                } else {
                    newOcc = newOcc + '<span class="div-right"><a class="button" href="javascript:save('+secret_id+')">' +
                        L_Modify + '</a></span>';
                }
                    
                newOcc = newOcc + '</p>' +
                    '<p>&nbsp;</p>' +
                    '</td>' +
                    '</tr>';

                $('tr#' + secret_id).hide();
                $(newOcc).insertAfter('tr#' + secret_id);
            }

        },
        error: function(reponse) {
            alert('Erreur sur serveur "Ajax_preferences.js" - "AJAX_LV" : ' + reponse['responseText']);
        }
    }); 
    
}


function cancel() {
    $('tbody#listeSecrets tr td div').each( function() {
        var currentId = $(this).parent().parent().attr('id');
        $('#' + currentId).remove();
        
        var Tmp = currentId.split('_');
        $('#'+Tmp[1]).show();
    } );
}


function save( secret_id ) {
    var sgr_id = $('#group_'+secret_id).val();
    var stp_id = $('#type_'+secret_id).val();
    var env_id = $('#environment_'+secret_id).val();
    var scr_host = $('#host_'+secret_id).val();
    var scr_user = $('#user_'+secret_id).val();
    var scr_password = $('#secret_'+secret_id).val();
    var scr_comment = $('#comment_'+secret_id).val();
    var scr_alert = $('#alert_'+secret_id).is(':checked');
    var scr_application = $('#application_'+secret_id).val();
    var scr_expiration_date = $('#expiration_'+secret_id).val();

    if ( scr_alert == true ) {
        scr_alert = 1;
    } else {
        scr_alert = 0;
    }

    $.ajax({
        url: '../SM-home.php?action=AJAX_S',
        type: 'POST',
        data: $.param({
            'scr_id': secret_id, 
            'sgr_id' : sgr_id,
            'stp_id' : stp_id,
            'scr_host' : scr_host,
            'scr_user' : scr_user,
            'scr_password' : scr_password,
	        'scr_comment' : scr_comment,
	        'scr_alert' : scr_alert,
	        'env_id' : env_id,
	        'scr_application' : scr_application,
	        'scr_expiration_date' : scr_expiration_date
	        }),
        dataType: 'json', // le résultat est transmit dans un objet JSON
        success: function(reponse) {
            showInfoMessage( reponse['status'], reponse['message'] ); // SecretManager.js
            
            if ( reponse['status'] == 'success' ) {
                $.ajax({
                    url: '../SM-home.php?action=AJAX_R',
                    type: 'POST',
                    success: function(reponse){
                        $('tbody#listeSecrets').html(reponse);
                    },
                    error: function(reponse) {
                        alert('Erreur sur serveur : ' + reponse['responseText']);
                    }
                });
            }
        },
        error: function(reponse) {
            alert('Erreur sur serveur "Ajax_preferences.js" - "AJAX_S" : ' + reponse['responseText']);
        }
    }); 

}



function remove( secret_id ) {
    $.ajax({
        url: '../SM-home.php?action=AJAX_D',
        type: 'POST',
        data: $.param({
            'scr_id': secret_id
	        }),
        dataType: 'json', // le résultat est transmit dans un objet JSON
        success: function(reponse) {
            showInfoMessage( reponse['status'], reponse['message'] ); // SecretManager.js
            
            if ( reponse['status'] == 'success' ) {
                $('#' + secret_id ).remove();
                $('#MOD_' + secret_id ).remove();
            }
        },
        error: function(reponse) {
            alert('Erreur sur serveur "Ajax_preferences.js" - "AJAX_D" : ' + reponse['responseText']);
        }
    }); 

}


function prepareTestConnection( Title, L_Subtitle, L_User, L_Password, L_Connection, L_Cancel, ConnectionType ) {
    $( '<div id="confirm_message" class="modal" role="dialog" tabindex="-1">' +
     '<div class="modal-header">' +
     '<button class="close" aria-hidden="true" data-dismiss="modal" type="button" ' +
     'onClick="javascript:hideTestConnection();">×</button>' +
     '<h4 id="myModalLabel">'+Title+'</h4>' +
     '</div>' +
     '<div class="modal-body">' +
     '<div class="row-fluid" id="bodyDiv" style="width:100%;">' +
     '<h5 class="text-center">'+L_Subtitle+'</h5>'+
     "<p style=\"width:100%;\"><span class=\"td-aere align-right\" style=\"width:40%;float:left;\">"+L_User+"</span>"+
     "<span  class=\"td-aere\" style=\"width:50%;float:left;\">"+
     "<input id=\"iUser\" type=\"text\" class=\"input-large\" name=\"Label\" " +
     "size=\"60\" maxlength=\"60\" required></span></p>\n" +
     "<p style=\"width:100%;\"><span class=\"td-aere align-right\" style=\"width:40%;float:left;\">"+L_Password+"</span>" +
     "<span  class=\"td-aere\" style=\"width:50%;float:left;\"><input id=\"iPassword\" type=\"password\" class=\"input-large\" name=\"Alert\" required></span></p>\n" +
     '</div>' +
     '</div>' +
     '<div class="modal-footer">' +
     '<a class="button" id="iCancel" href="javascript:hideTestConnection();">'+L_Cancel+
     '</a>&nbsp;<a class="button" href="javascript:testConnection(\''+ConnectionType+'\');">'+
     L_Connection+'</a>' +
     '</div>' +
     '</div>\n' ).prependTo( 'body' );

    $('#iUser').focus();

    $('#confirm_message').keyup(function(e){
        if(e.which == 27) { // Gestion de la touche "Echap"
            hideTestConnection();
        } else if (e.which == 13){
            testConnection( ConnectionType );
        }
    });
}


function hideTestConnection() {
    $('#confirm_message').remove();
}


function testConnection( ConnectionType ) {
    if ($('#iUser').val() == '') {
        $('#iUser').focus();
        return;
    }
    var Login = $('#iUser').val();

    if ($('#iPassword').val() == '') {
        $('#iPassword').focus();
        return;
    }
    var Authenticator = $('#iPassword').val();


    $.ajax({
        url: '../SM-preferences.php?action=AJAX_CTRL_AUTH_X',
        type: 'POST',
        data: $.param({
            'Login': Login,
            'Authenticator': Authenticator,
            'ConnectionType': ConnectionType
            }),
        dataType: 'json', // le résultat est transmit dans un objet JSON
        success: function(reponse) {
            $( '#connectionStatus' ).remove();
            $( '<div style="clear:both;"></div><p id="connectionStatus" class="'+reponse['Status']+'Zone">'+reponse['Message']+'</p>' ).appendTo( '#bodyDiv' );
        },
        error: function(reponse) {
            alert('Erreur sur serveur "Ajax_preferences.js" - "AJAX_CTRL_AUTH_X" : ' + reponse['responseText']);
        }
    }); 

}