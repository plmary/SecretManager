/**
* Ce script gère une partie des fonctions Ajax disponible pour le script "SM-users.php.
*
* @license http://www.gnu.org/copyleft/lesser.html  LGPL License 3
* @author Pierre-Luc MARY
* @date 2014-06-20
*/


// Active les fonctions ci-dessous quand le DOM de la page HTML est fini de charger.
$(document).ready(function(){
    // Masque les "modales", si l'utilisateur utilise les touches Echap".
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


// Cache la modale et remet à blanc les champs de saisie.
function hideModal() {
    // Remet à zéro les champs du calque
    $('#iGroupLabel').attr('data-id','');    
    $('#iGroupLabel').val('');
    $('#iGroupAlert').attr('checked',false);
    $('#iButtonAddGroup').val('')

    // Cache le calque.
    $('#afficherSecret').hide();
    $('#addGroup').hide();

    $('#history_title').remove();
    $('.history_row').remove();

    $('#menu-icon-page').off( 'click' );
}


// Traite l'affichage d'un secret.
function viewPassword( scr_id ){
    if ($('#inputlabel').val() != '') {
        var S_Status = 1;

        $.ajax({
            async: false,
            url: 'SM-secrets.php?action=CTRL_SRV_X',
            type: 'POST',
            dataType: 'json',
            success: function(reponse) {
                if ( reponse['Status'] != 'success' ) {
                    S_Status = 0;
                    showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
                }
            },
            error: function(reponse) {
                alert('Erreur sur serveur "Ajax_secrets.js" - "CTRL_SRV_X" : ' + reponse['responseText']);
            }
        });

        if ( S_Status == 0 ) return;

        $.ajax({
            url: '../../SM-secrets.php?action=SCR_V',
            type: 'POST',
            data: $.param({'scr_id': scr_id}),
            dataType: 'json',
            success: function(reponse) {
                var statut = reponse['Statut'];
                var password = reponse['password'];
                var Message = '';

                if ( password == null ) {
                    password = '('+reponse['l_invalid_mother_key']+')';
                    var couleur_fond = '';
                } else {
                    var couleur_fond = 'bg-orange ';                    
                }

                // Contrôle s'il faut gérer un lien.
                var regex_http = new RegExp('^http://', 'gi');
                var regex_https = new RegExp('^https://', 'gi');
                var regex_www = new RegExp('^www.', 'gi');

                if (statut == 'succes') {
                    if ( reponse['host'].match( regex_http )
                     || reponse['host'].match( regex_https )
                     || reponse['host'].match( regex_www ) ) {
                        reponse['host'] = '<a href="' + reponse['host'] + '" target="_blank">' + reponse['host'] + '</a>';
                    }

                    Message += '<p><span>'+reponse['l_host']+' : </span>'+
                        '<span class="td-aere">'+reponse['host']+'</span></p>'+
                        '<p><span>'+reponse['l_user']+' : </span>'+
                        '<span class="td-aere">'+reponse['user']+'</span></p>'+
                        '<p><span>'+reponse['l_password']+' : </span>'+
                        '<span class="'+couleur_fond+'td-aere" id="iPassword">'+password+'</span></p>';

                    $('#detailSecret').html(Message);


                    $('#menu-icon-page').off( 'click' );

                    $('#menu-icon-page').one( 'click', function() {
                        addSecretsHistory( scr_id );
                    });

                    if ( $('#history_title').length > 0 ) {
                        $('#history_title').remove();
                        $('.history_row').remove();
                    }
                } else if (statut == 'erreur') {
                    $('#detailSecret').html(reponse['Message']);
                }

                $('#afficherSecret').show();

                var OldSize = $('#afficherSecret').width();
                var MinSize = 400;
                if ( MinSize > OldSize ) {
                    OldSize = 400;
                }

                $('#afficherSecret').css({
                    'left': ((window.outerWidth - OldSize) / 2) + 'px',
                    'maxWidth': OldSize + 'px',
                    'minWidth': MinSize + 'px'
                });

            },
            error: function(reponse) {
                alert('Erreur serveur "Ajax_secrets.js" - "SCR_V" : ' + reponse['responseText']);
            }
        });
    }
}


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
            alert('Erreur serveur "Ajax_secrets.js" - "L_EDIT_FIELDS_X" : ' + reponse['responseText']);
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


// Annule une modification en cours.
function hideEditFields( Id ) {
    $('#MOD_'+Id).remove();
    $('#sgr_id-'+Id).show();
}


// Annule toutes les modifications en cours.
function hideAllEditFields() {
    $('tr[data-line-open="1"]').each( function(index) {
        var L_Id = $(this).attr("id");
        var T_Id = L_Id.split('_');
        hideEditFields( T_Id[1] );
    } );
}


// Sauvegarde les modificiations d'un Secret.
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
                alert('Erreur serveur "Ajax_secrets.js" - "MX" : ' + reponse['responseText']);
            }
        });
    }
}


// Affiche une boîte de dialogue pour créer un nouveau Groupe de Secrets.
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
        },
        error: function(reponse) {
            alert('Erreur serveur "Ajax_secrets.js" - "L_ADD_GROUP_X" : ' + reponse['responseText']);
        }
    });

    $( '<div id="confirm_message" class="modal" role="dialog" tabindex="-1">' +
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

    $('#iGroupLabel, #iGroupAlert').keyup(function(e){
        if (e.which == 13) {
            if ( $('#iGroupLabel').val() != '' ) addGroup();
        }
    });
}


// Supprime la boîte de dialogue affichée.
function hideConfirmMessage() {
    $('#confirm_message').remove();
}


// Crée un nouveau Groupe de Secrets.
function addGroup(){
    // Gère le cas d'une création d'un Groupe de Secret.
    if ( $('#iGroupLabel').val() != '' ) {
        var Rights;
        $.ajax({
            async: false,
            url: '../../SM-users.php?action=L_RIGHTS_X',
            type: 'POST',
            //data: $.param({'sgr_id': Id, 'Alert': Alert, 'Label': Label}),
            dataType: 'json',
            success: function(reponse) {
                Rights = reponse['liste_rights'];
            },
            error: function(reponse) {
                alert('Erreur serveur "Ajax_secrets.js" - "L_RIGHTS_X" : ' + reponse['responseText']);
            }
        });

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

                    if ( $('#fAGSP').attr('id') ) {
                        $('tbody#listeSecrets').prepend(
                         '<tr>'+
                         '<td class="align-middle">'+Label+'</td>'+
                         '<td><select multiple size="4" name="r_'+Id+'[]">'+Rights+'</select></td>'+
                         '</tr>'
                        );
                    } else {
                        $('#listeSecrets').prepend(
                         '<tr id="sgr_id-'+Id+'" class="surline">'+
                         '<td id="label-'+Id+'" class="align-middle">'+Label+'</td>'+
                         '<td id="alert-'+Id+'" class="align-middle">'+Image+'</td>'+
                         '<td>'+
                         '<a id="modify_'+Id+'" class="simple" href="javascript:editFields(\''+Id+'\');">'+
                         '<img class="no-border" src="'+URL_PICTURES+'/b_edit.png" alt="'+L_Modify+'" title="'+L_Modify+'" /></a>\n'+
                         '<a class="simple" href="javascript:confirmDeleteGroup(\''+Id+'\');">'+
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
                    }
                    
                    var Total = $('#total').text();
                    Total = Number(Total) + 1;
                    $('#total').text( Total );
                    
                    hideConfirmMessage();
                }
                
                showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
            },
            error: function(reponse) {
                alert('Erreur sur serveur "Ajax_secrets.js" - "ADDX" : ' + reponse['responseText']);
            }
        });
    }
}


// Affiche une boîte de dialogue pour supprimer un Groupe de Secrets.
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
            alert('Erreur sur serveur "Ajax_secrets.js" - "L_DELETE_GROUP_X" : ' + reponse['responseText']);
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


// Supprime un Groupe de Secrets.
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
        },
        error: function(reponse) {
            alert('Erreur sur serveur "Ajax_secrets.js" - "DX" : ' + reponse['responseText']);
        }
    });
}


// Affiche une boîte de dialogue pour créer un nouveau Secret.
function getCreateSecret( sgr_id ){
    var S_Status = 1;

    $.ajax({
        async: false,
        url: 'SM-secrets.php?action=CTRL_SRV_X',
        type: 'POST',
        dataType: 'json',
        success: function(reponse) {
            if ( reponse['Status'] != 'success' ) {
                S_Status = 0;
                showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
            }
        },
        error: function(reponse) {
            alert('Erreur sur serveur "Ajax_secrets.js" - "CTRL_SRV_X" : ' + reponse['responseText']);
        }
    });

    if ( S_Status == 0 ) return;

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
        L_Expiration_Date,
        L_Mandatory_Field,
        L_Personal,
        L_Complexity_1,
        L_Complexity_2,
        L_Complexity_3,
        L_Complexity_4,
        Secrets_Complexity,
        Secrets_Size;

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
            L_Mandatory_Field = reponse['L_Mandatory_Field'];
            L_Personal = reponse['L_Personal'];
            L_Complexity_1 = reponse['L_Complexity_1'];
            L_Complexity_2 = reponse['L_Complexity_2'];
            L_Complexity_3 = reponse['L_Complexity_3'];
            L_Complexity_4 = reponse['L_Complexity_4'];
            Secrets_Complexity = reponse['Secrets_Complexity'];
            Secrets_Size = reponse['Secrets_Size'];
            Secrets_Lifetime = reponse['Secrets_Lifetime'];
        },
        error: function(reponse) {
            alert('Erreur sur serveur "Ajax_secrets.js" - "LABELS_X" : ' + reponse['responseText']);
        }
    });

    var List_Environments;
    $.ajax({
        async: false,
        url: 'SM-secrets.php?action=LIST_ENV_X',
        type: 'POST',
        success: function(reponse) {
            List_Environments = reponse;
        },
        error: function(reponse) {
            alert('Erreur sur serveur "Ajax_secrets.js" - "LIST_ENV_X" : ' + reponse['responseText']);
        }
    });

    var List_Applications;
    $.ajax({
        async: false,
        url: 'SM-secrets.php?action=AJAX_L_APP_X',
        type: 'POST',
        dataType: 'json',
        success: function(reponse) {
            List_Applications = reponse['applications'];
        },
        error: function(reponse) {
            alert('Erreur sur serveur "Ajax_secrets.js" - "AJAX_L_APP_X" : ' + reponse['responseText']);
        }
    });

    var List_Groups;
    $.ajax({
        async: false,
        url: 'SM-secrets.php?action=LIST_GRP_X',
        data: $.param({
            'sgr_id': sgr_id
            }),
        type: 'POST',
        success: function(reponse) {
            List_Groups = reponse;
        },
        error: function(reponse) {
            alert('Erreur sur serveur "Ajax_secrets.js" - "LIST_GRP_X" : ' + reponse['responseText']);
        }
    });

    var List_Types;
    $.ajax({
        async: false,
        url: 'SM-secrets.php?action=LIST_TYP_X',
        type: 'POST',
        success: function(reponse) {
            List_Types = reponse;
        },
        error: function(reponse) {
            alert('Erreur sur serveur "Ajax_secrets.js" - "LIST_TYP_X" : ' + reponse['responseText']);
        }
    });

    var CurrentDate = new Date();

    NewDay = parseInt( CurrentDate.getDate() );
    NewMonth = parseInt( CurrentDate.getMonth() ) + parseInt( Secrets_Lifetime ) + 1;
    NewYear = parseInt( CurrentDate.getFullYear() );

    var FutureDate = new Date(NewYear, NewMonth, NewDay);

    NewDay = FutureDate.getDate();
    if ( NewDay.toString().length == 1 ) NewDay = '0' + NewDay.toString();
    NewMonth = parseInt( FutureDate.getMonth() ) + 1;
    if ( NewMonth.toString().length == 1 ) NewMonth = '0' + NewMonth.toString();
    NewYear = FutureDate.getFullYear();

    FutureDate = NewYear + '/' + NewMonth + '/' + NewDay;


    $('<div id="confirm_message" class="modal" role="dialog" tabindex="-1" style="min-width:700px;">' +
     '<div class="modal-header">' +
     '<button class="close" aria-hidden="true" data-dismiss="modal" type="button" ' +
     'onClick="javascript:hideConfirmMessage();">×</button>' +
     '<h4 id="myModalLabel">'+L_Secret_Create+'</h4>' +
     '</div> <!-- Fin : modal-header -->' +
     '<div class="modal-body">' +
     '<div class="row-fluid" style="margin-top:8px;">' +
     '<table style="margin:10px auto;width:90%">' +
     '<tbody>' +
     '<tr>' +
     '<td class="align-right">' + L_Group + '</td>' +
     '<td>' +
     '<select id="i_sgr_id" class="input-xlarge">' +
     '  <option value="-">---</option>'+
     List_Groups +
     '</select>' +
     '</td>' +
     '<td class="align-right" id="i_l_personal">' + L_Personal + '</td>' +
     '<td class="align-left"><input type="checkbox" id="i_personal" /></td>' +
     '</tr>' +
     '<tr>' +
     '<td class="align-right">' + L_Type + '</td>' +
     '<td colspan="3">' +
     '<select id="i_stp_id">' +
     '  <option value="-">---</option>'+
     List_Types +
     '</select>' +
     '</td>' +
     '</tr>' +
     '<tr>' +
     '<td class="align-right">' + L_Environment + '</td>' +
     '<td colspan="3">' +
     '<select id="i_env_id">' +
     '  <option value="-">---</option>'+
     List_Environments +
     '</select>' +
     '</td>' +
     '</tr>' +
     '<tr>' +
     '<td class="align-right">' + L_Application + '</td>' +
     '<td colspan="3">' +
     '<select id="i_app_id">' +
     '  <option value="-">---</option>'+
     List_Applications +
     '</select>' +
     '</td>' +
     '</tr>' +
     '<tr>' +
     '<td class="align-right">' + L_Host + '</td>' +
     '<td colspan="3"><input id="i_Host" type="text" size="100" maxlength="255" /></td>' +
     '</tr>' +
     '<tr>' +
     '<td class="align-right">' + L_User + '</td>' +
     '<td colspan="3"><input id="i_User" type="text" size="100" maxlength="100\" /></td>' +
     '</tr>' +
     '<tr>' +
     '<td class="align-right">' + L_Password + '</td>' +
     '<td><input name="Password" id="i_Password" type="text" size="64" maxlength="64" ' +
     'onkeyup="checkPassword(\'i_Password\', \'Result\', ' + Secrets_Complexity + ', ' + Secrets_Size + ');" ' +
     'onfocus="checkPassword(\'i_Password\', \'Result\', ' + Secrets_Complexity + ', ' + Secrets_Size + ');"/></td>'+
     '<td colspan="2" class="align-right"><div class="btn-group">' +
     '<button id="btn-done" class="btn">' + L_Generate + '</button>' +
     '<button class="btn dropdown-toggle" data-toggle="dropdown"><span class="caret"></span></button>' +
     '<ul class="dropdown-menu pull-right">' +
     '<li style="font-size:10px;"><a id="cpl_1" href="#" style="line-height: 14px;" data-toggle="selector_cpl"></a></li>' +
     '<li style="font-size:10px;"><a id="cpl_2" href="#" style="line-height: 14px;" data-toggle="selector_cpl"></a></li>' +
     '<li style="font-size:10px;"><a id="cpl_3" href="#" style="line-height: 14px;" data-toggle="selector_cpl"></a></li>' +
     '<li style="font-size:10px;"><a id="cpl_4" href="#" style="line-height: 14px;" data-toggle="selector_cpl"></a></li>' +
     '</ul></div> <!-- Fin : btn-group -->' +
     '&nbsp;<img id="Result" class="no-border" width="16" height="16" alt="Ok" src="' + Parameters['URL_PICTURES'] + '/blank.gif" /></td>' +
     '</tr>' +
     '<tr>' +
     '<td class="align-right">' + L_Expiration_Date + '</td>' +
     '<td colspan="3"><input id="i_Expiration_Date" type="text" size="19" maxlength="19" class="input-small" value="' + FutureDate + '" /></td>' +
     '</tr>' +
     '<tr>' +
     '<td class="align-right">' + L_Comment + '</td>' +
     '<td colspan="2"><input id="i_Comment" type="text" class="input-xxlarge" size="100" maxlength="100" /></td>' +
     '</tr>' +
     '<tr>' +
     '<td class="align-right">' + L_Alert + '</td>' +
     '<td colspan="3"><input id="i_Alert" type="checkbox" /></td>' +
     '</tr>' +
     '</tbody>' +
     '</table>' +
     '<script>' +
     'function reset_selector_cpl() {' +
     ' var L_Complexity_1 = "'+L_Complexity_1+'";' +
     ' var L_Complexity_2 = "'+L_Complexity_2+'";' +
     ' var L_Complexity_3 = "'+L_Complexity_3+'";' +
     ' var L_Complexity_4 = "'+L_Complexity_4+'";' +
     ' $(\'a[data-toggle="selector_cpl"]\').each( function(index) {' +
     '   var S_Id = $(this).attr("id");'+
     '   var T_Id = S_Id.split("_");'+
     '   $("#"+S_Id).attr("data-selection", 0);'+
     '   $("#"+S_Id).html(eval("L_Complexity_"+T_Id[1]));'+
     '   } );' +
     '}' +
     'function setEventPassword( id ) {' +
     ' var OldOnKeyUp = $("#i_Password").attr("onkeyup");' +
     ' var T_OldOnKeyUp = OldOnKeyUp.split(", ");' +
     ' $("#i_Password").attr("onkeyup", T_OldOnKeyUp[0] + ", " + T_OldOnKeyUp[1] + ", " + id + ", " + T_OldOnKeyUp[3]);' +
     ' $("#i_Password").attr("onfocus", T_OldOnKeyUp[0] + ", " + T_OldOnKeyUp[1] + ", " + id + ", " + T_OldOnKeyUp[3]);' +
     '}' +
     'reset_selector_cpl();' +
     '$("#cpl_1").on("click", function() {' +
     ' reset_selector_cpl();' +
     ' $("#cpl_1").attr("data-selection", 1);' +
     ' $("#cpl_1").html(\'<i class="icon-ok"></i>&nbsp;'+L_Complexity_1+'\');' +
     ' setEventPassword( 1 );' +
     '});' +
     '$("#cpl_2").on("click", function() {' +
     ' reset_selector_cpl();' +
     ' $("#cpl_2").attr("data-selection", 1);' +
     ' $("#cpl_2").html(\'<i class="icon-ok"></i>&nbsp;'+L_Complexity_2+'\');' +
     ' setEventPassword( 2 );' +
     '});' +
     '$("#cpl_3").on("click", function() {' +
     ' reset_selector_cpl();' +
     ' $("#cpl_3").attr("data-selection", 1);' +
     ' $("#cpl_3").html(\'<i class="icon-ok"></i>&nbsp;'+L_Complexity_3+'\');' +
     ' setEventPassword( 3 );' +
     '});' +
     '$("#cpl_4").on("click", function() {' +
     ' reset_selector_cpl();' +
     ' $("#cpl_4").attr("data-selection", 1);' +
     ' $("#cpl_4").html(\'<i class="icon-ok"></i>&nbsp;'+L_Complexity_4+'\');' +
     ' setEventPassword( 4 );' +
     '});' +
     '$("#btn-done").on("click", function() {' +
     ' var MyID = $(\'a[data-selection="1"]\').attr("id");' +
     ' MyID = MyID.split("_");' +
     ' generatePassword( \'i_Password\', MyID[1], ' + Secrets_Size + ' );' +
     '});' +
     '$("#cpl_' + Secrets_Complexity + '").trigger("click");' +
     '</script>' +
     '</div> <!-- Fin : row-fluid -->' +
     '</div> <!-- Fin : modal-body -->' +
     '<div class="modal-footer">' +
     '<a class="button" id="iCancel" href="javascript:hideConfirmMessage();">' +
     L_Cancel + '</a>&nbsp;' +
     '<a class="button" href="javascript:CreateSecret(' + sgr_id + ');">' +
     L_Create+'</a>' +
     '</div> <!-- Fin : modal-footer -->' +
     '</div> <!-- Fin : confirm_message -->\n' ).prependTo( 'body' );

    // Met le focus sur le 1er champ du calque.
    $('#i_sgr_id').focus();

    // Masque la modale quand on clique un objet de class "close"
    $('#i_personal').on('click', function() {
        if ( $("#i_personal").is(':checked') == true ) {
            $('#i_sgr_id').attr('disabled', true);
        } else {
            $('#i_sgr_id').attr('disabled', false);
        }
    });


    $('#i_sgr_id, #i_stp_id, #i_env_id, #i_Application, #i_Host, #i_User, #i_Password, '+
        '#i_Expiration_Date, #i_Comment, #i_Alert').keyup(function(e){
        if (e.which == 13) {
            if ( $('#i_stp_id').val() != '-'
             && $('#i_User').val() != ''
             && $('#i_Password').val() != '' ) {
                CreateSecret();
            } else {
                if ( $('#i_stp_id').val() == '-' ) $('#i_stp_id').focus();
                if ( $('#i_User').val() == '' ) $('#i_User').focus();
                if ( $('#i_Password').val() == '' ) $('#i_Password').focus();

                showInfoMessage( 'error', L_Mandatory_Field ); // SecretManager.js
            }
       }
    });
}


// Crée le nouveau Secret.
function CreateSecret( sgr_id ){
    var Personal = $('#i_personal').is(':checked');

    if ( Personal == false ) {
        var ID_Group = $('#i_sgr_id').val();
        var L_Group = $('#i_sgr_id option:selected').text();
    } else {
        var ID_Group = 0;
        var L_Group = '<b>' + $('#i_l_personal').text() + '</b>';
    }
    
    if ( ID_Group == '-' && Personal == false ) {
        $('#i_sgr_id').focus();
        $('#i_sgr_id').addClass( 'mandatory' );
        return;
    }
    
    var ID_Type = $('#i_stp_id').val();
    var L_Type = $('#i_stp_id option:selected').text();
    
    if ( ID_Type == '-' ) {
        $('#i_stp_id').focus();
        $('#i_stp_id').addClass( 'mandatory' );
        return;
    }
    
    var ID_Environment = $('#i_env_id').val();
    var L_Environment = $('#i_env_id option:selected').text();
    
    if ( ID_Environment == '-' && Personal == false ) {
        $('#i_env_id').focus();
        $('#i_env_id').addClass( 'mandatory' );
        return;
    }
    
    var ID_Application = $('#i_app_id').val();
    var L_Application = $('#i_app_id option:selected').text();

    var Host = $('#i_Host').val();
    var User = $('#i_User').val();
    var U_Password = $('#i_Password').val();
    var Expiration_Date = $('#i_Expiration_Date').val();
    var Comment = $('#i_Comment').val();
    var Alert = $('#i_Alert').is(':checked');
    
    if ( User == '' ) {
        $('#i_User').focus();
        $('#i_User').addClass( 'mandatory' );
        return;
    }
    
    if ( U_Password == '' ) {
        $('#i_Password').focus();
        $('#i_Password').addClass( 'mandatory' );
        return;
    }
     
    if ( Alert == true ) {
        Alert = 1;
        Img_Alert = '<img class="no-border" alt="Oui" title="Oui" src="' +
        Parameters["URL_PICTURES"] + '/bouton_coche.gif">';
    } else {
        Alert = 0;
        Img_Alert = '<img class="no-border" alt="Non" title="Non" src="' + 
        Parameters["URL_PICTURES"] + '/bouton_non_coche.gif">';
    }

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
			'Password': U_Password,
			'Expiration_Date': Expiration_Date,
			'Comment': Comment,
			'Alert': Alert,
			'Application': ID_Application,
            'Personal': Personal
        }),
        dataType: 'json',
        success: function(reponse) {
            // Récupère le statut de l'appel Ajax
            showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js

            if (reponse['Status'] == 'success') {
                var Total = $('#total').text();
                Total = Number(Total) + 1;

                if ( sgr_id == 0 ) {
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
                         '<td class="align-middle" data-id="'+ID_Application+'" onclick="viewPassword('+reponse['scr_id']+
                         ');" style="max-width:100px; width:100px;">'+L_Application+'</td>\n' +
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
                } else {
                    $('#listeSecrets').prepend(
                        '<tr class="surline">\n' +
                         '<td class="align-middle" style="max-width:110px; width:110px;">'+L_Type+'</td>\n' +
                         '<td class="align-middle" style="max-width:110px; width:110px;">'+L_Environment+'</td>\n' +
                         '<td class="align-middle" style="max-width:100px; width:100px;">'+L_Application+'</td>\n' +
                         '<td class="align-middle" style="max-width:90px; width:90px;">'+Host+'</td>\n' +
                         '<td class="align-middle" style="max-width:90px; width:90px;">'+User+'</td>\n' +
                         '<td class="align-middle" style="max-width:50px; width:50px;">'+Img_Alert+'</td>\n'+
                         '<td class="align-middle" style="max-width:80px; width:80px;">'+Expiration_Date+'</td>\n'+
                         '<td class="align-middle" style="max-width:230px; width:230px;">'+Comment+'</td>\n' +
                         '<td data-right="'+reponse['Rights']+'">\n' +
                          //'<a class="simple" href="javascript:setSecret('+reponse['scr_id']+')">\n' +
                          '<a class="simple" href="SM-secrets.php?action=SCR_M&scr_id='+reponse['scr_id']+'">\n' +
                          '<img class="no-border" title="'+reponse['L_Modify']+'" alt="'+reponse['L_Modify']+
                          '" src="'+Parameters['URL_PICTURES']+'/b_edit.png"></a>' +
                          //'<a class="simple" href="javascript:setSecret('+reponse['scr_id']+',\'D\')">\n' +
                          '<a class="simple" href="SM-secrets.php?action=SCR_D&scr_id='+reponse['scr_id']+'">\n' +
                          '<img class="no-border" title="'+reponse['L_Delete']+'" alt="'+reponse['L_Delete']+
                          '" src="'+Parameters['URL_PICTURES']+'/b_drop.png"></a>\n' +
                         '</td>'
                    );
                }
                
                hideConfirmMessage();
            }
                    
            $('#total').text( Total );
        },
        error: function(reponse) {
            alert('Erreur sur serveur "Ajax_secrets.js" - "SCR_AX" : ' + reponse['responseText']);
        }
    });
}


function addSecretsHistory( scr_id ) {
    $.ajax({
        url: 'SM-secrets.php?action=SCR_LH_X',
        type: 'POST',
        data: $.param({
            'scr_id': scr_id,
        }),
        dataType: 'json',
        success: function(reponse) {
            if (reponse['Status'] == 'success') {
                if ( $('#history_title').length == 0 ) {
                    $('#listHistorique').before(
                        '<div id="history_title">' +
                        '<span class="bold">' + reponse['Password'] + '</span>' +
                        '<span class="bold">' + reponse['Date'] + '</span>' +
                        '</div>'
                    );

                    $('#listHistorique').html( reponse['Message'] );

                    $('#menu-icon-page').one( 'click', function() {
                        addSecretsHistory( scr_id );
                    });
                } else {
                    $('#history_title').remove();
                    $('.history_row').remove();

                    $('#menu-icon-page').one( 'click', function() {
                        addSecretsHistory( scr_id );
                    });
                }
            } else {
                // Récupère le statut de l'appel Ajax
                showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
            }
        },
        error: function(reponse) {
            alert('Erreur sur serveur "Ajax_secrets.js" - "SCR_LH_X" : ' + reponse['responseText']);
        }

    });

}