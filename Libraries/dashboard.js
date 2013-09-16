$(function(){
    $("div.tableau_synthese p.titre#users").on('mouseenter', function() {
        $('div.corps#c_users').css('display','block');
    });

    $("div.tableau_synthese p.titre#groups").on('mouseenter', function() {
        $('div.corps#c_groups').css('display','block');
    });

    $("div.tableau_synthese p.titre#profiles").on('mouseenter', function() {
        $('div.corps#c_profiles').css('display','block');
    });

    $("div.tableau_synthese p.titre#entities").on('mouseenter', function() {
        $('div.corps#c_entities').css('display','block');
    });

    $("div.tableau_synthese p.titre#civilities").on('mouseenter', function() {
        $('div.corps#c_civilities').css('display','block');
    });

    $("div.tableau_synthese p.titre").on('mouseleave', function() {
        $('div.corps').css('display','');
    });
});
