/*
    Gestion des recherche par ajax
    Affichage des résultats
 */

var zoneResult = $("#zone_result");
var termBarre = $("#term");
var searchMode = $("#search-mode");
var relationSelect = $('#relations');

var rechercheEnCours = false;

var submitButton = $("#search-submit-button");

var phraseErreur = "<p>Erreur : Aucun résultat ou erreur serveur !</p>";

var relationClicked = [];

function searchOnJDM(button){
    if(rechercheEnCours){
        return null;
    }
    searchStart();
    zoneResult.html("<h1>Recherche en cours !</h1><img src='/assets/rechercheEnCours.gif' alt='recherche en cours'/>");
    if(termBarre.val() != "" && termBarre.val() != null){
        if(searchMode.val() != "" && searchMode.val() != null){
            switch (searchMode.val()) {
                case'exacte':
                    searchExact();
                    break;
                case "approximative":
                    searchApproximative();
                    break;
                case "relation":
                    searchRelation();
                    break;
                default:
                    zoneResult.html(phraseErreur);
                    break;
            }
        }
    }
}

function searchExact(termInLink){
    if( typeof(termInLink) == 'undefined' ){
        termInLink = termBarre.val();
    }
    else{
        termInLink.replace(" ", "+");
    }
    $.ajax({
        url: 'search/'+termInLink,
        type: 'GET',
        dataType : 'html',
        success : function(code_html, statut){
            rechercheEnCours = true;
            zoneResult.html(code_html);
        },
        error : function(resultat, statut, erreur){
            zoneResult.html(phraseErreur);
        },
        complete : function(resultat, statut){
            searchDone();
        }
    });
}


function searchApproximative(){
    $.ajax({
        url: 'search-approx/'+termBarre.val(),
        type: 'GET',
        dataType : 'html',
        success : function(code_html, statut){
            rechercheEnCours = true;
            zoneResult.html(code_html);
        },
        error : function(resultat, statut, erreur){
            zoneResult.html(phraseErreur);
        },
        complete : function(resultat, statut){
            searchDone();
        }
    });
}

function searchRelation(){
    $.ajax({
        url: 'search-relations/'+relationSelect.val()+'/'+termBarre.val(),
        type: 'GET',
        dataType : 'html',
        success : function(code_html, statut){
            rechercheEnCours = true;
            zoneResult.html(code_html);
        },
        error : function(resultat, statut, erreur){
            zoneResult.html(phraseErreur);
        },
        complete : function(resultat, statut){
            searchDone();
        }
    });
}

function searchEntriesForTermByRelation(relation,term){
    var zoneResultEntries = $("#"+relation);

    if(rechercheEnCours){
        zoneResultEntries.html("<p>D'autres recherches sont déjà en cours</p>");
        searchDone();
        return null;
    }

    searchStart();
    zoneResultEntries.html("<p>Recherche en cours !</p>")

    relationClicked[relation] = relation;
    var buttonRelationClicked =  $("#buttonDisplay_".relation)
    buttonRelationClicked.attr("disabled", false);
    $.ajax({
        url: '/search-entries-for-term-by-relation/'+relation+'/'+term,
        type: 'GET',
        dataType : 'html',
        success : function(code_html, statut){
            rechercheEnCours = true;
            zoneResultEntries.html(code_html);
        },
        error : function(resultat, statut, erreur){
            zoneResultEntries.html(phraseErreur);
        },
        complete : function(resultat, statut){
            searchDone();
            buttonRelationClicked.attr("disabled", true);
        }
    });
}

function searchStart(){
    rechercheEnCours = true;
    submitButton.attr("disabled", true);
}

function searchDone(){
    rechercheEnCours = false;
    submitButton.attr("disabled", false);
}
