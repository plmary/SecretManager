$(document).ready(function(){
    // Masque les "modales", si l'utilisateur utilise les touches "Return" ou "Escape".
    $(document).keyup(function(e){
        if(e.which == 27){
            hideModal();
            hideAllEditFields();
            hideConfirmMessage();
            hideInfoMessage();
        }
    });

    // Masque la modale quand on clique un objet de class "close"
     $(".close").on('click', function() {
        hideModal();
    });

    // Change couleur si vide ou plein des champs obligatoires
    $('input.obligatoire').focusout(function(){
        if ($(this).val() != '') {
            $(this).css("border", "1px solid #cdcdcd");
        } else {
            $(this).css("border", "2px solid #00608d");
        }
    });
});


function hideModal() {
    // Remet à zéro les champs du calque
    $('#iGroupLabel').attr('data-id','');    
    $('#iGroupLabel').val('');
    $('#iGroupAlert').attr('checked',false);
    $('#iButtonAddGroup').val('')

    // Cache le calque.
    $('#afficherSecret').hide();
    $('#addGroup').hide();
}


// Traite l'affichage d'un secret.
function viewPassword( scr_id ){
    if ($('#inputlabel').val() != '') {
        $.ajax({
            url: '../../SM-secrets.php?action=SCR_V',
            type: 'POST',
            data: $.param({'scr_id': scr_id}),
            dataType: 'json',
            success: function(reponse) {
                $('#afficherSecret').show();

                var statut = reponse['Statut'];
                var password = reponse['password'];
                var Message = '';

                if ( password == null ) {
                    password = '('+reponse['l_invalid_mother_key']+')';
                    var couleur_fond = '';
                    //Message += resultat['responseText'];
                } else {
                    var couleur_fond = 'bg-orange ';                    
                }

                if (statut == 'succes') {
                    Message += '<p><span>'+reponse['l_host']+' : </span>'+
                        '<span class="td-aere">'+reponse['host']+'</span></p>'+
                        '<p><span>'+reponse['l_user']+' : </span>'+
                        '<span class="td-aere">'+reponse['user']+'</span></p>'+
                        '<p><span>'+reponse['l_password']+' : </span>'+
                        '<span class="'+couleur_fond+'td-aere">'+password+'</span></p>';

                    $('#detailSecret').html(Message);
                }
                else if (statut == 'erreur') {
                    $('#detailSecret').text(reponse['Message']);
                }

            },
            error: function(reponse) {
                alert('Erreur serveur : ' + reponse['responseText']);
            }
        });
    }
}


// ============================================
// Modification des Groupes de secrets en ligne.
function editFields(Id) {
    hideAllEditFields();

    var GroupLabel = $('#label-'+Id).text();
    var GroupAlert = $('#image-'+Id).attr('alt');
    
    var CancelButton, ModifyButton;
    
    $.ajax({
        async: false,
        url: '../../SM-secrets.php?action=L_EDIT_FIELDS_X',
        type: 'POST',
        dataType: 'json',
        success: function(reponse) {
            CancelButton = reponse['Cancel'];
            ModifyButton = reponse['Modify'];
        },
        error: function(reponse) {
            alert('Erreur serveur : ' + reponse['responseText']);
        }
    });


    $('#sgr_id-'+Id).hide();
    
    if ( GroupAlert == 'Ok' || GroupAlert == 'Yes' ) GroupAlert = 'checked';
    else GroupAlert = '';

    $( "       <tr id=\"MOD_" + Id + "\" class=\"pair\" data-line-open=\"1\">\n" +
	    "        <td class=\"align-middle blue-border-line\"><input id=\"iGroupLabel\" class=\"input-xxlarge\" value=\"" + GroupLabel + "\" /></td>\n" +
		"        <td class=\"align-middle blue-border-line\"><input id=\"iGroupAlert\" type=\"checkbox\" " + GroupAlert + " /></td>\n" +
		"        <td class=\"align-middle blue-border-line\"><a class=\"button tbrl_margin_6\" href=\"javascript:hideEditFields('" + Id + "');\">" + CancelButton + "</a>" +
		"&nbsp;<a class=\"button tbrl_margin_6\" href=\"javascript:saveEditFields('" + Id + "');\">" + ModifyButton + "</a></td>\n" +
		"       </tr>\n"
	).insertAfter('#sgr_id-'+Id);
	
    // Met le focus sur le champ.
    document.getElementById('iGroupLabel').focus();

    // Place le curseur après la dernière lettre
    document.getElementById('iGroupLabel').selectionStart = GroupLabel.length;

    $('#iGroupLabel').keyup(function(e){
        if (e.which == 13) {
            if ( $('#iGroupLabel').val() != '' ) saveEditFields( Id );
        }
    });

    $('#iGroupAlert').keyup(function(e){
        if (e.which == 13) {
            if ( $('#iGroupLabel').val() != '' ) saveEditFields( Id );
        }
    });
}


function hideEditFields( Id ) {
    $('#MOD_'+Id).remove();
    $('#sgr_id-'+Id).show();
}

function hideAllEditFields() {
    $('tr[data-line-open="1"]').each( function(index) {
        var L_Id = $(this).attr("id");
        var T_Id = L_Id.split('_');
        hideEditFields( T_Id[1] );
    } );
}


// Traite l'affichage d'un secret.
function saveEditFields( Id ){
    var Label = $('#iGroupLabel').val();
    var Alert = $('#iGroupAlert').is(':checked');
    
    if ( Alert == true ) Alert = 1;
    else Alert = 0;

    if ($('#iGroupLabel').val() != '') {
        $.ajax({
            url: '../../SM-secrets.php?action=MX',
            type: 'POST',
            data: $.param({'sgr_id': Id, 'Alert': Alert, 'Label': Label}),
            dataType: 'json',
            success: function(reponse) {
                var statut = reponse['Status'];

                if (statut == 'success') {
                    showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
                    $('#MOD_'+Id).remove();
                    
                    if ( Alert == 1 ) {
                        Alert = "<img class=\"no-border\" id=\"image-" + Id + "\" src=\"" + reponse['URL_PICTURES'] +
                            "/bouton_coche.gif\" alt=\"Yes\" />";
                    } else {
                        Alert = "<img class=\"no-border\" id=\"image-" + Id + "\" src=\"" + reponse['URL_PICTURES'] +
                            "/bouton_non_coche.gif\" alt=\"No\" />";
                    }
                    
                    $('#label-'+Id).text( Label );
                    $('#alert-'+Id).html( Alert );

                    $('#sgr_id-'+Id).show();                    
                }
                else if (statut == 'erreur') {
                    showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
                }

            },
            error: function(reponse) {
                alert('Erreur serveur : ' + reponse['responseText']);
            }
        });
    }
}


// ============================================
// Gestion des créations de Profil à la volée et dans une "modale".
function putAddGroup() {
    var Title, Label, Alert, Cancel, ButtonName;
    $.ajax({
        async: false,
        url: '../../SM-secrets.php?action=L_ADD_GROUP_X',
        type: 'POST',
        //data: $.param({'sgr_id': Id, 'Alert': Alert, 'Label': Label}),
        dataType: 'json',
        success: function(reponse) {
            Title = reponse['Title'];
            Label = reponse['Label'];
            Alert = reponse['Alert'];
            Cancel = reponse['Cancel'];
            ButtonName = reponse['ButtonName'];
        }
    });

    $('<div id="confirm_message" class="modal" role="dialog" tabindex="-1">' +
     '<div class="modal-header">' +
     '<button class="close" aria-hidden="true" data-dismiss="modal" type="button" ' +
     'onClick="javascript:hideConfirmMessage();">×</button>' +
     '<h4 id="myModalLabel">'+Title+'</h4>' +
     '</div>' +
     '<div class="modal-body">' +
     '<div class="row-fluid"style="width:82%; margin-top:8px;">' +
	 "       <p><span class=\"td-aere align-right\" style=\"width:150px;\">" + Label + "</span>"+
	 "<span  class=\"td-aere\">"+
	 "<input id=\"iGroupLabel\" type=\"text\" class=\"obligatoire input-xlarge\" name=\"Label\" " +
	 "size=\"60\" maxlength=\"60\" /></span></p>\n" +
	 "       <p><span class=\"td-aere align-right\" style=\"width:150px;\">" + Alert + "</span>" +
	 "<span  class=\"td-aere\"><input id=\"iGroupAlert\" type=\"checkbox\" class=\"obligatoire\" name=\"Alert\" /></span></p>\n" +
     '</div>' +
     '</div>' +
     '<div class="modal-footer">' +
     '<a class="button" id="iCancel" href="javascript:hideConfirmMessage();">'+Cancel+
     '</a>&nbsp;<a class="button" href="javascript:addGroup();">'+
     ButtonName+'</a>' +
     '</div>' +
     '</div>\n' ).prependTo( 'body' );

    // Met le focus sur le 1er champ du calque.
    $('#iGroupLabel').focus();

    $('#iGroupLabel').keyup(function(e){
        if (e.which == 13) {
            if ( $('#iGroupLabel').val() != '' ) addGroup();
        }
    });

    $('#iGroupAlert').keyup(function(e){
        if (e.which == 13) {
            if ( $('#iGroupLabel').val() != '' ) addGroup();
        }
    });
}


function hideConfirmMessage() {
    $('#confirm_message').remove();
}


function addGroup(){
    // Gère le cas d'une création d'un Groupe de Secret.
    if ( $('#iGroupLabel').val() != '' ) {
        var Secret_Alert = $('#iGroupAlert').is(':checked');
        var Label = $('#iGroupLabel').val()

        if ( Secret_Alert == true ) Secret_Alert = 1;
        else Secret_Alert = 0;

        $.ajax({
            url: 'SM-secrets.php?action=ADDX',
            type: 'POST',
            data: $.param({'Label': Label, 'Alert': Secret_Alert}),
            dataType: 'json',
            success: function(reponse) {
                // Récupère le statut de l'appel Ajax
                $('#addGroup').hide();

                $('#iGroupLabel').val('');

                var statut = reponse['Status'];

                if (statut == 'success') {
                    var Id = reponse['IdGroup'];
                    var Script = reponse['Script'];
                    var URL_PICTURES = reponse['URL_PICTURES'];
                    var L_Modify = reponse['L_Modify'];
                    var L_Delete = reponse['L_Delete'];
                    var L_Cancel = reponse['L_Cancel'];
                    var L_Groups_Associate = reponse['L_Groups_Associate'];
                    var L_Profiles_Associate = reponse['L_Profiles_Associate'];
                    var L_Secret_Management = reponse['L_Secret_Management'];
                    
                    if ( Secret_Alert == 1 ) {
                        var Image = '<img class="no-border" alt="Ok" src="' +
                            URL_PICTURES + '/bouton_coche.gif" />';
                    } else {
                        var Image = '<img class="no-border" alt="Ko" src="' +
                            URL_PICTURES + '/bouton_non_coche.gif" />';
                    }

                    $('#listeSecrets').prepend(
                     '<tr id="sgr_id-'+Id+'" class="surline">'+
                     '<td class="align-middle">'+Label+'</td>'+
                     '<td class="align-middle">'+Image+'</td>'+
                     '<td>'+
                     '<a id="modify_'+Id+'" class="simple" href="javascript:editFields(\''+Id+'\');">'+
                     '<img class="no-border" src="'+URL_PICTURES+'/b_edit.png" alt="'+L_Modify+'" title="'+L_Modify+'" /></a>\n'+
                     '<a class="simple" href="'+Script+'?action=D&sgr_id='+Id+'">'+
                     '<img class="no-border" src="'+URL_PICTURES+'/b_drop.png" alt="'+L_Delete+'" title="'+L_Delete+'" /></a>\n'+
                     '<a class="simple" href="'+Script+'?action=PRF&sgr_id='+Id+'">'+
                     '<img class="no-border" src="'+URL_PICTURES+'/b_usrscr_2.png" alt="'+L_Profiles_Associate+
                     '" title="'+L_Groups_Associate+'" /></a>\n'+
                     '<a class="simple" href="'+Script+'?action=SCR&sgr_id='+Id+'&store">'+
                     '<img class="no-border" src="'+URL_PICTURES+'/b_scredit_1.png" alt="'+L_Secret_Management+
                     '" title="'+L_Groups_Associate+'" /></a>'+
                     '</td>'+
                     '</tr>'
                    );
                    
                    var Total = $('#total').text();
                    Total = Number(Total) + 1;
                    $('#total').text( Total );
                    
                    hideConfirmMessage();
                }
                
                showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
            },
            error: function(reponse) {
                alert('Erreur sur serveur : ' + reponse['responseText']);
            }
        });
    }
}


function confirmDeleteGroup( Id ) {
    var Label = $('#label-'+Id).text();
    
    var Message, Warning, Cancel, Confirm;

    $.ajax({
        async: false,
        url: 'SM-secrets.php?action=L_DELETE_GROUP_X',
        type: 'POST',
        dataType: 'json',
        success: function(reponse) {
            Message = reponse['Message'];
            Warning = reponse['Warning'];
            Cancel = reponse['Cancel'];
            Confirm = reponse['Confirm'];
        },
        error: function(reponse) {
            alert('Erreur sur serveur : ' + reponse['responseText']);
        }
    });
    
    $('<div id="confirm_message" class="modal" role="dialog" tabindex="-1">' +
     '<div class="modal-header">' +
     '<button class="close" aria-hidden="true" data-dismiss="modal" type="button" onClick="javascript:hideConfirmMessage();">×</button>' +
     '<h3 id="myModalLabel">'+Warning+'</h3>' +
     '</div>' +
     '<div class="modal-body">' +
     '<div class="row-fluid"style="width:82%; margin-top:8px;">' +
     '<p>' + Message + '<b>' + Label + '</b></p>' +
     '</div>' +
     '</div>' +
     '<div class="modal-footer">' +
     '<a class="button" id="iCancel" href="javascript:hideConfirmMessage();">'+Cancel+
     '</a>&nbsp;<a class="button" href="javascript:deleteGroup('+Id+');">'+
     Confirm+'</a>' +
     '</div>' +
     '</div>\n' ).prependTo( 'body' );
    
    document.getElementById('iCancel').focus();

}


function deleteGroup( Id ) {
    $.ajax({
        url: 'SM-secrets.php?action=DX',
        type: 'POST',
        data: $.param({'sgr_id': Id}),
        dataType: 'json',
        success: function(reponse) {
            // Récupère le statut de l'appel Ajax
            showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js

            if (reponse['Status'] == 'success') {
                $('#sgr_id-'+Id).remove();
                
                hideConfirmMessage();
            }
                    
            var Total = $('#total').text();
            Total = Number(Total) - 1;
            $('#total').text( Total );
        }
    });
}


// ============================================
// Gestion des créations de Profil à la volée et dans une "modale".
function getCreateSecret(){
    var L_Secret_Create,
        L_Group,
        L_Type,
        L_Environment,
        L_Application,
        L_Host,
        L_User,
        L_Password,
        L_Generate,
        L_Cancel,
        L_Create,
        L_Alert,
        L_Comment,
        L_Expiration_Date;

    $.ajax({
        async: false,
        url: 'SM-secrets.php?action=LABELS_X',
        type: 'POST',
        dataType: 'json',
        success: function(reponse) {
            L_Secret_Create = reponse['L_Secret_Create'];
            L_Group = reponse['L_Group'];
            L_Type = reponse['L_Type'];
            L_Environment = reponse['L_Environment'];
            L_Application = reponse['L_Application'];
            L_Host = reponse['L_Host'];
            L_User = reponse['L_User'];
            L_Password = reponse['L_Password'];
            L_Generate = reponse['L_Generate'];
            L_Cancel = reponse['L_Cancel'];
            L_Create = reponse['L_Create'];
            L_Alert = reponse['L_Alert'];
            L_Comment = reponse['L_Comment'];
            L_Expiration_Date = reponse['L_Expiration_Date'];
        }
    });

    var List_Environments;
    $.ajax({
        async: false,
        url: 'SM-secrets.php?action=LIST_ENV_X',
        type: 'POST',
        success: function(reponse) {
            List_Environments = reponse;
        }
    });

    var List_Groups;
    $.ajax({
        async: false,
        url: 'SM-secrets.php?action=LIST_GRP_X',
        type: 'POST',
        success: function(reponse) {
            List_Groups = reponse;
        }
    });

    var List_Types;
    $.ajax({
        async: false,
        url: 'SM-secrets.php?action=LIST_TYP_X',
        type: 'POST',
        success: function(reponse) {
            List_Types = reponse;
        }
    });

    $('<div id="confirm_message" class="modal" role="dialog" tabindex="-1">' +
     '<div class="modal-header">' +
     '<button class="close" aria-hidden="true" data-dismiss="modal" type="button" ' +
     'onClick="javascript:hideConfirmMessage();">×</button>' +
     '<h4 id="myModalLabel">'+L_Secret_Create+'</h4>' +
     '</div>' +
     '<div class="modal-body">' +
     '<div class="row-fluid"style="margin-top:8px;">' +
     '<table style="margin:10px auto;width:80%">' +
     '<tbody>' +
     '<tr>' +
     '<td class="align-right">' + L_Group + '</td>' +
     '<td>' +
     '<select id="i_sgr_id" class="input-xlarge">' +
     '  <option value="-">---</option>'+
     List_Groups +
     '</select>' +
     '</td>' +
     '</tr>' +
     '<tr>' +
     '<td class="align-right">' + L_Type + '</td>' +
     '<td>' +
     '<select id="i_stp_id">' +
     '  <option value="-">---</option>'+
     List_Types +
     '</select>' +
     '</td>' +
     '</tr>' +
     '<tr>' +
     '<td class="align-right">' + L_Environment + '</td>' +
     '<td>' +
     '<select id="i_env_id">' +
     '  <option value="-">---</option>'+
     List_Environments +
     '</select>' +
     '</td>' +
     '</tr>' +
     '<tr>' +
     '<td class="align-right">' + L_Application + '</td>' +
     '<td><input id="i_Application" type="text" size="60" maxlength="60" /></td>' +
     '</tr>' +
     '<tr>' +
     '<td class="align-right">' + L_Host + '</td>' +
     '<td><input id="i_Host" type="text" size="100" maxlength="255" /></td>' +
     '</tr>' +
     '<tr>' +
     '<td class="align-right">' + L_User + '</td>' +
     '<td><input id="i_User" type="text" size="100" maxlength="100\" /></td>' +
     '</tr>' +
     '<tr>' +
     '<td class="align-right">' + L_Password + '</td>' +
     '<td><input name="Password" id="i_Password" type="text" size="64" maxlength="64" onkeyup="checkPassword(\'i_Password\', \'Result\', 3, 8);" onfocus="checkPassword(\'i_Password\', \'Result\', 3, 8);"/><a class="button" onclick="generatePassword( \'i_Password\', 3, 8 )">' + L_Generate + '</a><img id="Result" class="no-border" width="16" height="16" alt="Ok" src="' + Parameters['URL_PICTURES'] + '/blank.gif" /></td>' +
     "</tr>" +
     "<tr>" +
     "<td class=\"align-right\">" + L_Expiration_Date + "</td>" +
     "<td><input id=\"i_Expiration_Date\" type=\"text\" size=\"19\" maxlength=\"19\" /></td>" +
     "</tr>" +
     "<tr>" +
     "<td class=\"align-right\">" + L_Comment + "</td>" +
     "<td><input id=\"i_Comment\" type=\"text\" size=\"100\" maxlength=\"100\" /></td>" +
     "</tr>" +
     "<tr>" +
     "<td class=\"align-right\">" + L_Alert + "</td>" +
     "<td><input id=\"i_Alert\" type=\"checkbox\" /></td>" +
     "</tr>" +
     "</tbody>" +
     "</table>" +
     '</div>' +
     '</div>' +
     '<div class="modal-footer">' +
     '<a class="button" id="iCancel" href="javascript:hideConfirmMessage();">' +
     L_Cancel + '</a>&nbsp;' +
     '<a class="button" href="javascript:CreateSecret();">' +
     L_Create+'</a>' +
     '</div>' +
     '</div>\n' ).prependTo( 'body' );

    // Met le focus sur le 1er champ du calque.
    $('#i_sgr_id').focus();


    $('#i_sgr_id, #i_stp_id, #i_env_id, #i_Application, #i_Host, #i_User, #i_Password, '+
        '#i_Expiration_Date, #i_Comment, #i_Alert').keyup(function(e){
        if (e.which == 13) {
            if ( $('#i_sgr_id').val() != '-'
             && $('#i_stp_id').val() != '-'
             && $('#i_env_id').val() != '-'
             && $('#i_Application').val() != ''
             && $('#i_Host').val() != ''
             && $('#i_User').val() != ''
             && $('#i_Password').val() != '' ) {
                CreateSecret();
            } else {
                showInfoMessage( 'error', 'pouet' ); // SecretManager.js
            }
       }
    });
}


// ============================================
// Gestion des créations de Profil à la volée et dans une "modale".
function CreateSecret(){
    var ID_Group = $('#i_sgr_id').val();
    var L_Group = $('#i_sgr_id option:selected').text();
    
    var ID_Type = $('#i_stp_id').val();
    var L_Type = $('#i_stp_id option:selected').text();
    
    var ID_Environment = $('#i_env_id').val();
    var L_Environment = $('#i_env_id option:selected').text();
    
    var Application = $('#i_Application').val();
    var Host = $('#i_Host').val();
    var User = $('#i_User').val();
    var Password = $('#i_Password').val();
    var Expiration_Date = $('#i_Expiration_Date').val();
    var Comment = $('#i_Comment').val();
    var Alert = $('#i_Alert').is(':checked');
     
    if ( Alert == true ) Alert = 1;
    else Alert = 0;

    $.ajax({
        url: 'SM-secrets.php?action=SCR_AX',
        type: 'POST',
        data: $.param({
            'sgr_id': ID_Group,
            'stp_id': ID_Type,
            'env_id': ID_Environment,
            'Alert': Alert,
			'Host': Host,
			'User': User,
			'Password': Password,
			'Expiration_Date': Expiration_Date,
			'Comment': Comment,
			'Alert': Alert,
			'Application': Application
        }),
        dataType: 'json',
        success: function(reponse) {
            // Récupère le statut de l'appel Ajax
            showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js

            if (reponse['Status'] == 'success') {
                var Total = $('#total').text();
                Total = Number(Total) + 1;

                $('#listeSecrets').prepend(
                    '<tr id="'+reponse['scr_id']+'" class="surline" data-delete="'+reponse['L_Delete']+
                     '" data-modify="'+reponse['L_Modify']+'" data-cancel="'+reponse['L_Cancel']+
                     '" data-total="'+Total+'" style="cursor: pointer;">\n' +
                     '<td class="align-middle" data-id="'+ID_Group+'" onclick="viewPassword('+
                     reponse['scr_id']+');" style="max-width:210px; width:210px;">'+L_Group+'</td>\n' +
                     '<td class="align-middle" data-id="'+ID_Type+'" onclick="viewPassword('+
                     reponse['scr_id']+');" style="max-width:90px; width:90px;">'+L_Type+'</td>\n' +
                     '<td class="align-middle" data-id="'+ID_Environment+'" onclick="viewPassword('+
                     reponse['scr_id']+');" style="max-width:100px; width:100px;">'+L_Environment+'</td>\n' +
                     '<td class="align-middle" onclick="viewPassword('+reponse['scr_id']+
                     ');" style="max-width:100px; width:100px;">'+Application+'</td>\n' +
                     '<td class="align-middle" onclick="viewPassword('+reponse['scr_id']+
                     ');" style="max-width:70px; width:70px;">'+Host+'</td>\n' +
                     '<td class="align-middle" onclick="viewPassword('+reponse['scr_id']+
                     ');" style="max-width:70px; width:70px;">'+User+'</td>\n' +
                     '<td class="align-middle" onclick="viewPassword('+reponse['scr_id']+
                     ');" style="max-width:80px; width:80px;">'+Expiration_Date+'</td>\n'+
                     '<td class="align-middle" onclick="viewPassword('+reponse['scr_id']+
                     ');" style="max-width:110px; width:110px;">'+Comment+'</td>\n' +
                     '<td data-right="'+reponse['Rights']+'" style="max-width:80px; width:80px;">\n' +
                     '<a class="simple" href="javascript:setSecret('+reponse['scr_id']+')">\n' +
                     '<img class="no-border" title="'+reponse['L_Modify']+'" alt="'+reponse['L_Modify']+
                     '" src="'+Parameters['URL_PICTURES']+'/b_edit.png"></a>' +
                     '<a class="simple" href="javascript:setSecret('+reponse['scr_id']+',\'D\')">\n' +
                     '<img class="no-border" title="'+reponse['L_Delete']+'" alt="'+reponse['L_Delete']+
                     '" src="'+Parameters['URL_PICTURES']+'/b_drop.png"></a>\n' +
                     '<a class="simple" href="javascript:viewPassword( '+reponse['scr_id']+' );">' +
                     '<img class="no-border" title="'+reponse['L_Password_View']+'" alt="'+
                     reponse['L_Password_View']+'" src="'+Parameters['URL_PICTURES']+'/b_eye.png"></a>\n' +
                     '</td>'
                );
                
                hideConfirmMessage();
            }
                    
            $('#total').text( Total );
        }
    });
}