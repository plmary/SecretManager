$(document).ready(function(){
    // Masque les "modales", si l'utilisateur utilise les touches "Return" ou "Escape".
    $(document).keyup(function(e){
        if(e.which == 27){
            hideModal();
            hideAllEditFields();
            hideConfirmMessage();
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

                var resultat = new Array();

                $.each(reponse, function(attribut, valeur) {
                    resultat[attribut]=valeur;
                });

                var statut = resultat['Statut'];
                var password = resultat['password'];
                var Message = '';

                if ( password == null ) {
                    password = '('+resultat['l_invalid_mother_key']+')';
                    var couleur_fond = '';
                    //Message += resultat['responseText'];
                } else {
                    var couleur_fond = 'bg-orange ';                    
                }

                if (statut == 'succes') {
                    Message += '<p><span>'+resultat['l_host']+' : </span>'+
                        '<span class="td-aere">'+resultat['host']+'</span></p>'+
                        '<p><span>'+resultat['l_user']+' : </span>'+
                        '<span class="td-aere">'+resultat['user']+'</span></p>'+
                        '<p><span>'+resultat['l_password']+' : </span>'+
                        '<span class="'+couleur_fond+'td-aere">'+password+'</span></p>';

                    $('#detailSecret').html(Message);
                }
                else if (statut == 'erreur') {
                    $('#detailSecret').text(resultat['Message']);
                }

            },
            error: function(reponse) {
                var resultat = new Array();

                $.each(reponse, function(attribut, valeur) {
                    resultat[attribut]=valeur;
                });

                alert('Erreur serveur : ' + resultat['responseText']);
            }
        });
    }
}


// ============================================
// Modification des Groupes de secrets en ligne.
function editFields(Id,CancelButton,ModifyButton) {
    hideAllEditFields();

    var GroupLabel = $('#label-'+Id).text();
    var GroupAlert = $('#image-'+Id).attr('alt');

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
                var resultat = new Array();

                $.each(reponse, function(attribut, valeur) {
                    resultat[attribut]=valeur;
                });

                var statut = resultat['Status'];

                if (statut == 'success') {
                    showInfoMessage( resultat['Status'], resultat['Message'] ); // SecretManager.js
                    $('#MOD_'+Id).remove();
                    
                    if ( Alert == 1 ) {
                        Alert = "<img class=\"no-border\" id=\"image-" + Id + "\" src=\"" + resultat['URL_PICTURES'] +
                            "/bouton_coche.gif\" alt=\"Yes\" />";
                    } else {
                        Alert = "<img class=\"no-border\" id=\"image-" + Id + "\" src=\"" + resultat['URL_PICTURES'] +
                            "/bouton_non_coche.gif\" alt=\"No\" />";
                    }
                    
                    $('#label-'+Id).text( Label );
                    $('#alert-'+Id).html( Alert );

                    $('#sgr_id-'+Id).show();                    
                }
                else if (statut == 'erreur') {
                    showInfoMessage( resultat['Status'], resultat['Message'] ); // SecretManager.js
                }

            },
            error: function(reponse) {
                var resultat = new Array();

                $.each(reponse, function(attribut, valeur) {
                    resultat[attribut]=valeur;
                });

                alert('Erreur serveur : ' + resultat['responseText']);
            }
        });
    }
}


// ============================================
// Gestion des créations de Profil à la volée et dans une "modale".
function putAddGroup(Title,Label,Alert,Cancel,ButtonName){
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
                var resultat = new Array();

                $.each(reponse, function(attribut, valeur) {
                    resultat[attribut]=valeur;
                });
                $('#addGroup').hide();

                $('#iGroupLabel').val('');

                var statut = resultat['Status'];

                if (statut == 'success') {
                    var Id = resultat['IdGroup'];
                    var Script = resultat['Script'];
                    var URL_PICTURES = resultat['URL_PICTURES'];
                    var L_Modify = resultat['L_Modify'];
                    var L_Delete = resultat['L_Delete'];
                    var L_Cancel = resultat['L_Cancel'];
                    var L_Groups_Associate = resultat['L_Groups_Associate'];
                    var L_Profiles_Associate = resultat['L_Profiles_Associate'];
                    var L_Secret_Management = resultat['L_Secret_Management'];
                    
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
                     '<a id="modify_'+Id+'" class="simple" href="javascript:editFields(\''+Id+'\', \''+Label+'\', \''+Secret_Alert+
                     '\', \''+L_Cancel+'\', \''+L_Modify+'\');">'+
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
                var resultat = new Array();

                $.each(reponse, function(attribut, valeur) {
                    resultat[attribut]=valeur;
                });

                alert('Erreur sur serveur : ' + resultat['responseText']);
            }
        });
    }
}


function confirmDeleteGroup( Id, Message, Warning, Cancel, Confirm) {
    var Label = $('#label-'+Id).text();
    
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
            var resultat = new Array();

            $.each(reponse, function(attribut, valeur) {
                resultat[attribut]=valeur;
            });

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
