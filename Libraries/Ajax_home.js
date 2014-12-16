/**
* Ce script gère une partie des fonctions Ajax disponible pour le script "SM-home.php.
*
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @date 2014-06-19
*/


// Active les fonctions ci-dessous quand le DOM de la page HTML est fini de charger.
$(document).ready( function() {
    // Surveille les touches du clavier utilisées dans tout le document HTML.
    $(document).keyup(function(e){
        if(e.which == 27) { // Gestion de la touche "Echap"
                cancel();
        }
    });


    // Surveille les touches du clavier utilisées dans le champ de Recherche.
    $('input[name="searchSecret"]').keyup(function(){
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
                alert('Erreur sur serveur "Ajax_home.js" - "R" : ' + reponse['responseText']);
            }
        }); 
    });

    // Force l'ajustement du tableau dans lesquels les Secrets sont affichés.
    resizeSecretsWindow();  // SecretManager.js

    // Force l'ajustement du tableau dans lesquels les Secrets sont affichés quand l'utilisateur change la taille d'affichage de son navigateur.
    $(window).resize( function() {
        resizeSecretsWindow();  // SecretManager.js
    });
});


// Affiche les moyens de modification d'un Secret "en ligne".
function setSecret( secret_id, action ) {
    var S_Status = 1;

    $.ajax({
        async: false,
        url: 'SM-secrets.php?action=CTRL_SRV_X', // Vérifie que le SecretServer est bien démarré.
        type: 'POST',
        dataType: 'json',
        success: function(reponse) {
            if ( reponse['Status'] != 'success' ) {
                S_Status = 0;
                showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
            }
        },
        error: function(reponse) {
            alert('Erreur sur serveur "Ajax_home.js" - "CTRL_SRV_X" : ' + reponse['responseText']);
            return;
        }
    });

    if ( S_Status == 0 ) return;

    var action = action || '';
    var ListeApplications;
    
    if ( action == 'D' ) {
        var Delete_Mode = ' disabled';
    } else {
        var Delete_Mode = '';
    }
    
    $.ajax({
        async: false,
        url: '../SM-secrets.php?action=AJAX_L_APP_X', // Récupère la liste des Applications disponibles
        type: 'POST',
        data: $.param({'scr_id': secret_id}),
        dataType: 'json', // le résultat est transmit dans un objet JSON
        success: function(reponse){
            ListeApplications = reponse[ 'applications' ];
        },
        error: function(reponse) {
            alert('Erreur sur serveur "Ajax_home.js" - "AJAX_L_APP_X" : ' + reponse['responseText']);
            return;
        }
    });
    
    cancel();

    $.ajax({
        url: '../SM-home.php?action=AJAX_LV', // Récupère les différents libellés.
        type: 'POST',
        data: $.param({'scr_id': secret_id}),
        dataType: 'json', // le résultat est transmit dans un objet JSON
        success: function(reponse){
            if ( $('#' + secret_id + ' td p').length == 0 ) {
                if ( reponse['statut'] == 'success' ) {
                    var group, group_id, type, type_id, environment, environment_id, application,
                        host, user, expiration, comment, right, dirname, L_Edit, L_Delete, L_View, L_Personal;

                    $('tr#' + secret_id + ' td').each( function( index ) {
                        if ( index == 0 ) {
                            // Uniformisme la réponse pour simplifier la gestion ultérieure.
                            if ( reponse['Owner'] == '' || reponse['Owner'] == 0 || reponse['Owner'] == null ) {
                                reponse['Owner'] = 0;
                            }

                            if ( reponse['Owner'] == 0 ) {
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
                            } else {
                                group_id = 0;
                                group = '<b id="i_personal">' + reponse['L_Personal'] + '</b>';
                            }
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

                            if ( group_id == 0 ) {
                                environment = environment + '<option value="-" selected>---</option>';                                
                            }

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

                    var newOcc = '<tr id="MOD_' + secret_id + '" class="' + currentClass +
                        '" style="cursor: pointer;">' +
                        '<td colspan="9" style="margin:0;padding:0;border:2px solid #568EB6;">' +
                        '<div id="modification-zone">' +
                        '<table>' +
                        '<tr>' +
                        '<td><label for="'+'group_'+secret_id+'">' + reponse['L_Group'] + '</label></td>' +
                        '<td colspan="3">';

                    if ( reponse['Owner'] == 0 ) {
                        newOcc = newOcc + '<select id="'+'group_'+secret_id+'" class="input-xlarge"' +
                            Delete_Mode + '>' + group + '</select>';
                    } else {
                        newOcc = newOcc + group;
                    }

                        
                    newOcc = newOcc + '</td><td><label for="'+'type_'+secret_id+'">' + reponse['L_Type'] + '</label></td>' +
                        '<td><select id="'+'type_'+secret_id+'" class="input-medium"' + Delete_Mode + '>' +
                        type +
                        '</select></td>' +
                        '<td><label for="'+'environment_'+secret_id+'">' + reponse['L_Environment'] + '</label></td>' +
                        '<td><select id="'+'environment_'+secret_id+'"' + ' class="input-medium"' + Delete_Mode + '>' +
                        environment +
                        '</select></td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td><label for="'+'application_'+secret_id+'">' + reponse['L_Application'] + '</label></td>' +
                        '<td><select id="'+'application_'+secret_id+'" class="input-medium"' + Delete_Mode + '>' + ListeApplications + '</select></td>' +
                        '<td><label for="'+'host_'+secret_id+'">' + reponse['L_Host'] + '</label></td>' +
                        '<td><input id="'+'host_'+secret_id+'" type="text" value="' + host + '" class="input-medium"' + 
                        Delete_Mode + '></td>' +
                        '<td><label for="'+'user_'+secret_id+'">' + reponse['L_User'] + '</label></td>' +
                        '<td><input id="'+'user_'+secret_id+'" type="text" value="' + user + '" class="input-medium"' + 
                        Delete_Mode + '></td>' +
                        '<td><label for="'+'secret_'+secret_id+'">' + reponse['L_Password'] + '</label></td>';

                    if ( action == 'D' ) {
                        password = '************';
                    }
                    
                    newOcc = newOcc + '<td><input id="secret_'+secret_id+'" type="text" value="' + password + 
                        '" class="input-medium"' + Delete_Mode + ' ' +
                        'onkeyup="checkPassword(\'secret_'+secret_id+'\', \'Result\', ' + reponse['Secrets_Complexity'] + ', ' + reponse['Secrets_Size'] + ');" ' +
                        'onfocus="checkPassword(\'secret_'+secret_id+'\', \'Result\', ' + reponse['Secrets_Complexity'] + ', ' + reponse['Secrets_Size'] + ');"/>';

                    if ( Delete_Mode == '' ) {
                        newOcc = newOcc + '<div class="btn-group">' +
                            '<button id="btn-done" class="btn">' + reponse['L_Generate'] + '</button>' +
                            '<button class="btn dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>' +
                            '<ul class="dropdown-menu pull-right">' +
                            '<li style="font-size:10px;"><a id="cpl_1" href="#" style="line-height: 14px;" data-toggle="selector_cpl"></a></li>' +
                            '<li style="font-size:10px;"><a id="cpl_2" href="#" style="line-height: 14px;" data-toggle="selector_cpl"></a></li>' +
                            '<li style="font-size:10px;"><a id="cpl_3" href="#" style="line-height: 14px;" data-toggle="selector_cpl"></a></li>' +
                            '<li style="font-size:10px;"><a id="cpl_4" href="#" style="line-height: 14px;" data-toggle="selector_cpl"></a></li>' +
                            '</ul></div> <!-- Fin : btn-group -->' +
                            '&nbsp;<img id="Result" class="no-border" width="16" height="16" alt="Ok" src="' + Parameters['URL_PICTURES'] + '/blank.gif" />';
                    }

                    newOcc = newOcc + '</td>' +
                        '</tr>' +
                        '<tr>' +
                        '<td><label for="'+'alert_'+secret_id+'">' + reponse['L_Alert'] + '</label></td>' +
                        '<td><input id="'+'alert_'+secret_id+'" type="checkbox" ' + alert + Delete_Mode + '></td>' +
                        '<td><label for="'+'expiration_'+secret_id+'">' + reponse['L_Expiration_Date'] + '</label></td>' +
                        '<td><input id="'+'expiration_'+secret_id+'" type="text" value="' + expiration + '" class="input-medium"' +
                        Delete_Mode + '></td>' +
                        '<td><label for="'+'comment_'+secret_id+'">' + reponse['L_Comment'] + '</label></td>' +
                        '<td colspan="3"><input id="'+'comment_'+secret_id+'" type="text" value="' + comment + '" class="input-xlarge"' + 
                        Delete_Mode + '></td>' +
                        '</tr>' +
                        '</table>';

                    if ( Delete_Mode == '' ) {
                        newOcc = newOcc + '<script>' +
                            'function reset_selector_cpl() {' +
                            ' var L_Complexity_1 = "'+reponse['L_Complexity_1']+'";' +
                            ' var L_Complexity_2 = "'+reponse['L_Complexity_2']+'";' +
                            ' var L_Complexity_3 = "'+reponse['L_Complexity_3']+'";' +
                            ' var L_Complexity_4 = "'+reponse['L_Complexity_4']+'";' +
                            ' $(\'a[data-toggle="selector_cpl"]\').each( function(index) {' +
                            '   var S_Id = $(this).attr("id");'+
                            '   var T_Id = S_Id.split("_");'+
                            '   $("#"+S_Id).attr("data-selection", 0);'+
                            '   $("#"+S_Id).html(eval("L_Complexity_"+T_Id[1]));'+
                            ' } );' +
                            '}' +
                            'function setEventPassword( id ) {' +
                            ' var OldOnKeyUp = $("#secret_'+secret_id+'").attr("onkeyup");' +
                            ' var T_OldOnKeyUp = OldOnKeyUp.split(", ");' +
                            ' $("#secret_'+secret_id+'").attr("onkeyup", T_OldOnKeyUp[0] + ", " + T_OldOnKeyUp[1] + ", " + id + ", " + T_OldOnKeyUp[3]);' +
                            ' $("#secret_'+secret_id+'").attr("onfocus", T_OldOnKeyUp[0] + ", " + T_OldOnKeyUp[1] + ", " + id + ", " + T_OldOnKeyUp[3]);' +
                            '}' +
                            'reset_selector_cpl();' +
                            '$("#cpl_1").on("click", function() {' +
                            ' reset_selector_cpl();' +
                            ' $("#cpl_1").attr("data-selection", 1);' +
                            ' $("#cpl_1").html(\'<i class="icon-ok"></i>&nbsp;'+reponse['L_Complexity_1']+'\');' +
                            ' setEventPassword( 1 );' +
                            '});' +
                            '$("#cpl_2").on("click", function() {' +
                            ' reset_selector_cpl();' +
                            ' $("#cpl_2").attr("data-selection", 1);' +
                            ' $("#cpl_2").html(\'<i class="icon-ok"></i>&nbsp;'+reponse['L_Complexity_2']+'\');' +
                            ' setEventPassword( 2 );' +
                            '});' +
                            '$("#cpl_3").on("click", function() {' +
                            ' reset_selector_cpl();' +
                            ' $("#cpl_3").attr("data-selection", 1);' +
                            ' $("#cpl_3").html(\'<i class="icon-ok"></i>&nbsp;'+reponse['L_Complexity_3']+'\');' +
                            ' setEventPassword( 3 );' +
                            '});' +
                            '$("#cpl_4").on("click", function() {' +
                            ' reset_selector_cpl();' +
                            ' $("#cpl_4").attr("data-selection", 1);' +
                            ' $("#cpl_4").html(\'<i class="icon-ok"></i>&nbsp;'+reponse['L_Complexity_4']+'\');' +
                            ' setEventPassword( 4 );' +
                            '});' +
                            '$("#btn-done").on("click", function() {' +
                            ' var MyID = $(\'a[data-selection="1"]\').attr("id");' +
                            ' MyID = MyID.split("_");' +
                            ' generatePassword( \'secret_'+secret_id+'\', MyID[1], ' + reponse['Secrets_Size'] + ' );' +
                            '});' +
                            '$("#cpl_' + reponse['Secrets_Complexity'] + '").trigger("click");' +
                            '</script>';
                    }

                    newOcc = newOcc + '</div>' +
                        '<p style="margin-top: 6px;margin-bottom: 6px;padding:0">' +
                        '<span class="div-left tbrl_padding_6"><a class="button" href="javascript:cancel();">' + L_Cancel + '</a></span>';
                    
                    if ( action == 'D' ) {
                        newOcc = newOcc + '<span class="div-right tbrl_padding_6"><a class="button" href="javascript:remove('+secret_id+')">' +
                            L_Delete + '</a></span>';
                    } else {
                        newOcc = newOcc + '<span class="div-right tbrl_padding_6"><a class="button" href="javascript:save('+secret_id+')">' +
                            L_Modify + '</a></span>';
                    }
                    
                    newOcc = newOcc + '</p>' +
                        '<p>&nbsp;</p>' +
                        '</td>' +
                        '</tr>';

                    $('tr#' + secret_id).hide();
                    $(newOcc).insertAfter('tr#' + secret_id);

                    $('#MOD_' + secret_id).keyup(function(e){
                        if(e.which == 13) {
                            save(secret_id);
                        }
                    });
                } else {
                    showInfoMessage( reponse['statut'], reponse['message'] );
                }
            }
        },
        error: function(reponse) {
            alert('Erreur sur serveur "Ajax_home.js" - "AJAX_LV" : ' + reponse['responseText']);
            return;
        }
    }); 
    
}


// Ferme toutes les occurrences ouvertes en modification.
function cancel() {
    $('tbody#listeSecrets tr td div').each( function() {
        var currentId = $(this).parent().parent().attr('id');
        $('#' + currentId).remove();

        if ( currentId ) {
            var Tmp = currentId.split('_');
            $('#'+Tmp[1]).show();
        }
    } );
}


// Sauvegarde les modifications apportées à un Secret.
function save( secret_id ) {
    var sgr_id = $('#group_'+secret_id).val();
    var sgr_name = $('#group_'+secret_id+' option:selected').text();
    var stp_id = $('#type_'+secret_id).val();
    var stp_name = $('#type_'+secret_id+' option:selected').text();
    var env_id = $('#environment_'+secret_id).val();
    var env_name = $('#environment_'+secret_id+' option:selected').text();
    var scr_host = $('#host_'+secret_id).val();
    var scr_user = $('#user_'+secret_id).val();
    var scr_password = $('#secret_'+secret_id).val();
    var scr_comment = $('#comment_'+secret_id).val();
    var scr_alert = $('#alert_'+secret_id).is(':checked');
    var scr_application = $('#application_'+secret_id).val();
    var scr_expiration_date = $('#expiration_'+secret_id).val();
    var Personal;

    if ( $('#i_personal').text() != '' ) {
        Personal = 1;
        sgr_id = 0;
    } else {
        Personal = 0;
    }


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
            'sgr_id': sgr_id,
            'sgr_name': sgr_name,
            'stp_id': stp_id,
            'stp_name': stp_name,
            'scr_host': scr_host,
            'scr_user': scr_user,
            'scr_password': scr_password,
	        'scr_comment': scr_comment,
	        'scr_alert': scr_alert,
	        'env_id': env_id,
            'env_name': env_name,
	        'scr_application': scr_application,
	        'scr_expiration_date': scr_expiration_date,
            'Personal': Personal
	        }),
        dataType: 'json', // le résultat est transmit dans un objet JSON
        success: function(reponse) {
            showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
            
            if ( reponse['Status'] == 'success' ) {
                $.ajax({
                    url: '../SM-home.php?action=AJAX_R',
                    type: 'POST',
                    success: function(reponse){
                        $('tbody#listeSecrets').html(reponse);
                    },
                    error: function(reponse) {
                        alert('Erreur sur serveur "Ajax_home.js" - "AJAX_R" : ' + reponse['responseText']);
                    }
                });
            }
        },
        error: function(reponse) {
            alert('Erreur sur serveur "Ajax_home.js" - "AJAX_S" : ' + reponse['responseText']);
        }
    }); 

}


// Supprime un Secret à la volée.
function remove( secret_id ) {
    var stp_name = $('#type_'+secret_id+' option:selected').text();
    var env_name = $('#environment_'+secret_id+' option:selected').text();
    var scr_host = $('#host_'+secret_id).val();
    var scr_user = $('#user_'+secret_id).val();
    var scr_password = $('#secret_'+secret_id).val();
    var scr_comment = $('#comment_'+secret_id).val();
    var scr_alert = $('#alert_'+secret_id).is(':checked');
    var scr_application = $('#application_'+secret_id).val();
    var scr_expiration_date = $('#expiration_'+secret_id).val();

    $.ajax({
        url: '../SM-home.php?action=AJAX_D',
        type: 'POST',
        data: $.param({
            'scr_id': secret_id, 
            'stp_name' : stp_name,
            'scr_host' : scr_host,
            'scr_user' : scr_user,
            'scr_password' : scr_password,
            'scr_comment' : scr_comment,
            'scr_alert' : scr_alert,
            'env_name' : env_name,
            'scr_application' : scr_application,
            'scr_expiration_date' : scr_expiration_date
	        }),
        dataType: 'json', // le résultat est transmit dans un objet JSON
        success: function(reponse) {
            showInfoMessage( reponse['status'], reponse['message'] ); // SecretManager.js
            
            if ( reponse['status'] == 'success' ) {
                $('#' + secret_id ).remove();
                $('#MOD_' + secret_id ).remove();

                var Total = $('#total').text();
                Total = Number(Total) - 1;
                $('#total').text( Total );
            }
        },
        error: function(reponse) {
            alert('Erreur sur serveur "Ajax_home.js" - "AJAX_D" : ' + reponse['responseText']);
        }
    }); 

}