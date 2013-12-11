$(document).keyup(function(e){
    if(e.which == 27) { // || e.which == 13){
        $('#addCivility').hide();
    }
});

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

    $('#iEntityLabel').keyup(function(e){
        if (e.which == 13) {
            addEntity();
        }
    });

    $('#iEntityCode').keyup(function(e){
        if (e.which == 13) {
            addEntity();
        }
    });

    $('#iButtonCreateEntity').on('click', function(){
        addEntity();
    });

    // ===================

    $('#iCivilityLastName').keyup(function(e){
        if (e.which == 13) {
            addCivility();
        }
    });

    $('#iCivilityFirstName').keyup(function(e){
        if (e.which == 13) {
            addCivility();
        }
    });

    $('#iCivilitySex').keyup(function(e){
        if (e.which == 13) {
            addCivility();
        }
    });

    $('#iButtonCreateCivility').on('click', function(){
        addCivility();
    });

});


function resetPassword( Id ) {
    $.ajax({
        url: 'SM-users.php?action=RST_PWDX',
        type: 'POST',
        data: $.param({'idn_id': Id}),
        dataType: 'json',
        success: function(reponse) {
            showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
        },
        error: function(reponse) {
            alert('Erreur serveur : ' + reponse['responseText']);
        }
    });
}


function resetAttempt( Id ) {
    $.ajax({
        url: 'SM-users.php?action=RST_ATTX',
        type: 'POST',
        data: $.param({'idn_id': Id}),
        dataType: 'json',
        success: function(reponse) {
            showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
            
            var CurrentClass = $("#total-attempt").attr("class");
            $("#total-attempt").removeClass(CurrentClass).addClass("bg-green").html('&nbsp;0&nbsp;');
        },
        error: function(reponse) {
            alert('Erreur serveur : ' + reponse['responseText']);
        }
    });
}


function resetExpirationDate( Id ) {
    $.ajax({
        url: 'SM-users.php?action=RST_EXPX',
        type: 'POST',
        data: $.param({'idn_id': Id}),
        dataType: 'json',
        success: function(reponse) {
            showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
            
            var CurrentClass = $("#expiration-date").attr("class");
            $("#expiration-date").removeClass(CurrentClass).addClass("bg-green").html('&nbsp;'+
                reponse['Expiration_Date']+'&nbsp;00:00:00&nbsp;');
        },
        error: function(reponse) {
            alert('Erreur serveur : ' + reponse['responseText']);
        }
    });
}


function enableDisableUser( Id, Status ) {
    $.ajax({
        url: 'SM-users.php?action=RST_DISX',
        type: 'POST',
        data: $.param({'idn_id': Id, 'Status': Status}),
        dataType: 'json',
        success: function(reponse) {
            showInfoMessage( reponse['Status'], reponse['Message'] ); // SecretManager.js
            
/*            'Activated' => $Activated,
            'Disable_Color' => $Disable_Color,
            'Disable_Msg' => $Disable_Msg,
            'Disable_Action' => $Disable_Action,
            'Disable_Status' => $Disable_Status */
            
            var CurrentClass = $("#disabled-user").attr("class");
            $("#disabled-user").removeClass(CurrentClass).addClass(reponse['Disable_Color']).html('&nbsp;'+
                reponse['Disable_Msg']+'&nbsp;');
            
            $('#action-button').attr('href',"javascript:enableDisableUser('" + Id + "','" +
                reponse['Disable_Status'] + "');").text( reponse['Disable_Action'] );
        },
        error: function(reponse) {
            alert('Erreur serveur : ' + reponse['responseText']);
        }
    });
}
