$(document).keyup(function(e){
    if(e.which == 27) { // || e.which == 13){
            cancel();
    }
});


$(document).ready( function() {
    $("#iSearchSecret").keyup(function(){
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
                alert('Erreur sur serveur : ' + reponse['responseText']);
            }
        }); 
    });
});


function setSecret( secret_id, action ) {
    var action = action || '';
    var ListeApplications;
    
    if ( action == 'D' ) {
        var Delete_Mode = ' disabled';
    } else {
        var Delete_Mode = '';
    }
    
    $.ajax({
        async: false,
        url: '../SM-secrets.php?action=AJAX_L_APP_X',
        type: 'POST',
        data: $.param({'scr_id': secret_id}),
        dataType: 'json', // le résultat est transmit dans un objet JSON
        success: function(reponse){
            ListeApplications = reponse[ 'applications' ];
        },
        error: function(reponse) {
            alert('Erreur sur serveur : ' + reponse['responseText']);
        }
    });
    
    cancel();

    $.ajax({
        url: '../SM-home.php?action=AJAX_LV',
        type: 'POST',
        data: $.param({'scr_id': secret_id}),
        dataType: 'json', // le résultat est transmit dans un objet JSON
        success: function(reponse){
            if ( $('#' + secret_id + ' td p').length == 0 ) {
                if ( reponse['statut'] == 'success' ) {
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

                    var newOcc = '<tr id="MOD_' + secret_id + '" class="' + currentClass +
                        '" style="cursor: pointer;">' +
                        '<td colspan="9" style="margin:0;padding:0;border:2px solid #568EB6;">' +
                        '<div id="modification-zone">' +
                        '<table>' +
                        '<tr>' +
                        '<td><label for="'+'group_'+secret_id+'">' + reponse['L_Group'] + '</label></td>' +
                        '<td colspan="3"><select id="'+'group_'+secret_id+'" class="input-xlarge"' +
                        Delete_Mode + '>' + group + '</select></td>' +
                        '<td><label for="'+'type_'+secret_id+'">' + reponse['L_Type'] + '</label></td>' +
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
                    
                    newOcc = newOcc + '<td><input id="'+'secret_'+secret_id+'" type="text" value="' + password + 
                        '" class="input-medium"' + Delete_Mode + '></td>' +
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
                        '</table>' +
                        '</div>' +
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
                } else {
                    showInfoMessage( reponse['statut'], reponse['message'] );
                }
            }
        },
        error: function(reponse) {
            alert('Erreur sur serveur : ' + reponse['responseText']);
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
            alert('Erreur sur serveur : ' + reponse['responseText']);
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
            alert('Erreur sur serveur : ' + reponse['responseText']);
        }
    }); 

}