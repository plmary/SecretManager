$(document).ready(function(){
    // Masque la modale quand on clique un objet de class "close"
     $(".close").on('click', function() {
        $('#addCivility').hide();
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


// Création d'une Civilité à la volée.
function putAddCivility(Title,L_First_Name,L_Last_Name,L_Sex,L_Man,L_Woman,L_Cancel,L_Create){
    $('<div id="confirm_message" class="modal" role="dialog" tabindex="-1">' +
     '<div class="modal-header">' +
     '<button class="close" aria-hidden="true" data-dismiss="modal" type="button" ' +
     'onClick="javascript:hideConfirmMessage();">×</button>' +
     '<h4 id="myModalLabel">'+Title+'</h4>' +
     '</div>' +
     '<div class="modal-body">' +
     '<div class="row-fluid"style="width:82%; margin-top:8px;">' +
	 "       <p><span class=\"td-aere align-right\" style=\"width:150px;\">" + L_First_Name + "</span>"+
	 "<span  class=\"td-aere\">"+
	 "<input id=\"iFirstName\" type=\"text\" class=\"obligatoire input-large\" " +
	 "maxlength=\"25\" /></span></p>\n" +
	 "       <p><span class=\"td-aere align-right\" style=\"width:150px;\">" + L_Last_Name + "</span>" +
	 "<span  class=\"td-aere\"><input id=\"iLastName\" type=\"text\" class=\"obligatoire input-xlarge\" " +
	 "maxlength=\"35\"/></span></p>\n" +
	 "       <p><span class=\"td-aere align-right\" style=\"width:150px;\">" + L_Sex + "</span>" +
	 "<span  class=\"td-aere\"><select id=\"iSex\" class=\"obligatoire input-xlarge\">" +
	 "<option value=\"0\" selected>"+L_Man+"</option><option value=\"1\">"+L_Woman+"</option>" +
	 "</select>" +
	 "</span></p>\n" +
     '</div>' +
     '</div>' +
     '<div class="modal-footer">' +
     '<a class="button" id="iCancel" href="javascript:hideConfirmMessage();">'+L_Cancel+
     '</a>&nbsp;<a class="button" href="javascript:addCivility();">'+
     L_Create+'</a>' +
     '</div>' +
     '</div>\n' ).prependTo( 'body' );

    // Met le focus sur le 1er champ du calque.
    $('#iFirstName').focus();

    $('#iFirstName').keyup(function(e){
        if (e.which == 13) {
            if ( $('#iFirstName').val() != '' || $('#iLastName').val() != '' ) addCivility();
        }
    });

    $('#iLastName').keyup(function(e){
        if (e.which == 13) {
            if ( $('#iFirstName').val() != '' || $('#iLastName').val() != '' ) addCivility();
        }
    });

    $('#iSex').keyup(function(e){
        if (e.which == 13) {
            if ( $('#iFirstName').val() != '' || $('#iLastName').val() != '' ) addCivility();
        }
    });
}


function addCivility(){
    var LastName = $('#iLastName').val();
    var FirstName = $('#iFirstName').val();
    var Sex = $('#iSex').val();

    if ( LastName != '' && FirstName != '') {        
        $.ajax({
            url: 'SM-users.php?action=CVL_CX',
            type: 'POST',
            data: $.param({'Last_Name': LastName, 'First_Name': FirstName, 'Sex': Sex}),
            dataType: 'json',
            success: function(reponse) {
                showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js

                if (reponse['Status'] == 'success') {
                    var Id = reponse['IdCivility'];
                    var Script = reponse['Script'];
                    var URL_PICTURES = reponse['URL_PICTURES'];
                    var L_Modify = reponse['L_Modify'];
                    var L_Delete = reponse['L_Delete'];
                    var L_Warning = reponse['L_Warning'];
                    var L_Cancel = reponse['L_Cancel'];
                    var L_Confirm_Delete_Civility = reponse['L_Confirm_Delete_Civility'];
                    var L_Confirm = reponse['L_Confirm'];
                    var L_Man = reponse['L_Man'];
                    var L_Woman = reponse['L_Woman'];

                    if ( Sex == 0 ) Sex = L_Man;
                    else Sex = L_Woman;

                    hideConfirmMessage();

                    if ( $('#dashboard').length == 0 ) {
                        $('#iSelectCivility option').attr('selected','off')

                        $('#iSelectCivility').prepend('<option value="'+Id+'" selected>'+FirstName+' '+LastName+'</option>');
                    } else {
                        $('#listeSecrets').prepend(
                         '<tr id="civility-' + Id + '" class="surline">'+
                         '<td id="first_name-' + Id + '">'+FirstName+'</td>'+
                         '<td id="last_name-' + Id + '">'+LastName+'</td>'+
                         '<td id="sex-' + Id + '">'+Sex+'</td>'+
                         '<td>'+
                         '<a class="simple" href="javascript:modifyCivility(\''+Id+'\',\''+L_Man+
                         '\',\''+L_Woman+'\',\''+L_Cancel+'\',\''+L_Modify+'\');">'+
                         '<img class="no-border" src="'+URL_PICTURES+'/b_edit.png" alt="'+L_Modify+'" title="'+L_Modify+'" />'+
                         '</a> '+
                         '<a class="simple" href="javascript:confirmDeleteCivility(\'' +
                         Id + "','" + L_Warning + "','" + L_Confirm_Delete_Civility + "','" + L_Cancel +
                         "','" + L_Confirm + '\');">'+
                         '<img class="no-border" src="'+URL_PICTURES+'/b_drop.png" alt="'+L_Delete+'" title="'+L_Delete+'" />'+
                         '</a>'+
                         '</td>'+
                         '</tr>'
                        );
                    }
                    var Total = $('#total').text();
                    Total = Number(Total) + 1;
                    $('#total').text( Total );
                }

            },
            error: function(reponse) {
                alert('Erreur serveur : ' + reponse['responseText']);
            }
        });
    }
}


// Création d'une Entité à la volée.
function confirmDeleteCivility(Id,Title,Message,L_Cancel,L_Confirm){
    $('<div id="confirm_message" class="modal" role="dialog" tabindex="-1">' +
     '<div class="modal-header">' +
     '<button class="close" aria-hidden="true" data-dismiss="modal" type="button" ' +
     'onClick="javascript:hideConfirmMessage();">×</button>' +
     '<h4 id="myModalLabel">'+Title+'</h4>' +
     '</div>' +
     '<div class="modal-body">' +
     '<div class="row-fluid"style="width:82%; margin-top:8px;">' +
	 "<p>" + Message + "<b>" + $('#first_name-'+Id).text() + " - " + $('#last_name-'+Id).text() +  "</b></p>\n" +
     '</div>' +
     '</div>' +
     '<div class="modal-footer">' +
     '<a class="button" id="iCancel" href="javascript:hideConfirmMessage();">'+L_Cancel+
     '</a>&nbsp;<a class="button" href="javascript:deleteCivility('+Id+');">'+
     L_Confirm+'</a>' +
     '</div>' +
     '</div>\n' ).prependTo( 'body' );

    // Met le focus sur le 1er champ du calque.
    $('#iCancel').focus();
}


// Gestion des suppressions d'Entité à la volée.
function deleteCivility( Id ){
    $.ajax({
        url: 'SM-users.php?action=CVL_DX',
        type: 'POST',
        data: $.param({'cvl_id': Id}),
        dataType: 'json',
        success: function(reponse) {
            showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
            
            if (reponse['Status'] == 'success') {
                $('#civility-'+Id).remove();
                hideConfirmMessage();

                var Total = $('#total').text();
                Total = Number(Total) - 1;
                $('#total').text( Total );
            }
        },
        error: function(reponse) {
            alert('Erreur sur serveur : ' + reponse['responseText']);
        }
    });
}


// Gestion des modifications "en place".
function modifyCivility( Id, L_Man, L_Woman, CancelButton, ModifyButton ) {
    endAllModifyCivility();

    $('#civility-'+Id).hide();
    
    var FirstName = $('#first_name-'+Id).text();
    var LastName = $('#last_name-'+Id).text();
    var Sex = $('#sex-'+Id).text();
    
    if ( Sex == L_Man ) {
        var Man_Selected = ' selected';
        var Woman_Selected = '';
    } else {
        var Man_Selected = '';
        var Woman_Selected = ' selected';
    }

	$( "       <tr id=\"MODI_" + Id + "\" class=\"pair\">\n" +
		"        <td class=\"align-middle blue-border-line\"><input id=\"iFirstName\" value=\"" +
		FirstName + "\" class=\"input-large\" /></td>\n" +
		"        <td class=\"align-middle blue-border-line\"><input id=\"iLastName\" value=\"" +
		LastName + "\" class=\"input-xlarge\" /></td>\n" +
		"        <td class=\"align-middle blue-border-line\"><select id=\"iSex\">" +
		"<option value=\"0\""+Man_Selected+">"+L_Man+"</option>" +
		"<option value=\"1\""+Woman_Selected+">"+L_Woman+"</option>" +
		"</select></td>\n" +
		"        <td class=\"align-middle blue-border-line\"><a class=\"button\" href=\"javascript:endModifyCivility('" +
		Id + "');\">" + CancelButton + "</a>&nbsp;<a class=\"button\" href=\"javascript:saveModifyCivility('" +
		Id + "','" + L_Man + "','" + L_Woman + "');\">" + ModifyButton + "</a></td>\n" +
		"       </tr>\n" ).insertAfter( '#civility-'+Id );
    
    document.getElementById('iFirstName').focus();
    document.getElementById('iFirstName').selectionStart = FirstName.length;

    $('#iFirstName').keyup(function(e){
        if (e.which == 13) {
            if ( $('#iFirstName').val() != '' || $('#iLastName').val() != '' ) saveModifyCivility( Id, L_Man, L_Woman );
        }

        if (e.which == 27) {
            endModifyCivility( Id );
        }
    });

    $('#iLastName').keyup(function(e){
        if (e.which == 13) {
            if ( $('#iFirstName').val() != '' || $('#iLastName').val() != '' ) saveModifyCivility( Id, L_Man, L_Woman );
        }

        if (e.which == 27) {
            endModifyCivility( Id );
        }
    });

    $('#iSex').keyup(function(e){
        if (e.which == 13) {
            if ( $('#iFirstName').val() != '' || $('#iLastName').val() != '' ) saveModifyCivility( Id, L_Man, L_Woman );
        }

        if (e.which == 27) {
            endModifyCivility( Id );
        }
    });
}

function saveModifyCivility( Id, L_Man, L_Woman ) {
    var First_Name = $('#iFirstName').val();
    var Last_Name = $('#iLastName').val();
    var Sex = $('#iSex').val();

    $.ajax({
        url: 'SM-users.php?action=CVL_MX',
        type: 'POST',
        data: $.param({'cvl_id': Id, 'First_Name': First_Name, 'Last_Name': Last_Name, 'Sex': Sex}),
        dataType: 'json',
        success: function(reponse) {
            if ( Sex == '0' ) {
                var L_Sex = L_Man;
            } else {
                var L_Sex = L_Woman;
            }

            showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js

            if (reponse['Status'] == 'success') {
                $('#last_name-'+Id).text(Last_Name);
                $('#first_name-'+Id).text(First_Name);
                $('#sex-'+Id).text(L_Sex);
                
                $('#MODI_'+Id).remove();
                $('#civility-'+Id).show();
            }
        },
        error: function(reponse) {
            alert('Erreur sur serveur : ' + reponse['responseText']);
        }
    });
}


// Annule la modification en cours.
function endModifyCivility(Id) {
    $('#MODI_'+Id).remove();
    $('#civility-'+Id).show();
}


// Annule toutes les modifications en cours.
function endAllModifyCivility() {
    $('tr[id^="MODI_"]').each( function(index) {
        var T_ID = $(this).attr( 'id' );
        T_ID = T_ID.split('_');
        endModifyCivility(T_ID[1]);
    } );
}


function hideConfirmMessage() {
    $('#confirm_message').remove();
}
