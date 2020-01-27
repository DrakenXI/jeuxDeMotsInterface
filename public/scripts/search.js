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

var resultDefZone = $("#result_raff");
var sectionRaff = $("#section_raff");
var titleBalise = $("#titre_resultat");

function searchStart(){
    rechercheEnCours = true;
    submitButton.attr("disabled", true);
}

function searchDone(){
    rechercheEnCours = false;
    submitButton.attr("disabled", false);
}

function searchOnJDM(term = null, mode = null){
    if(rechercheEnCours){
        return null;
    }
    if(term === null){
        term = termBarre.val();
    }
    if(mode === null){
        mode = searchMode.val();
    }
    if(term != "" && term != null){
        if(mode != "" && mode != null){
            resetCharger();
            searchStart();
            resultDefZone.html(""); //clear du contenue
            termBarre.val(term);
            zoneResult.html("<h1>Recherche en cours !</h1><img src='/assets/loading.gif' alt='recherche en cours'/>");
            titleBalise.html("Résultat pour : " + term);
            switch (mode) {
                case'exacte':
                    searchExact(term);
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
            searchRaffinementList();
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

/* IMPORTANT : ne pas toucher le split, la route se base sur l'ID de la relation uniquement */
function searchRelation(){
    var relationSplitted = relationSelect.val().split("_");
    $.ajax({
        url: 'search-relations/'+relationSplitted[0]+'/'+termBarre.val(),
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

function searchEntriesForTermByRelation(relation,term, iddiv){
    var zoneResultEntries = document.getElementById(iddiv);

    if(rechercheEnCours){
        zoneResultEntries.innerHTML = "<p>D'autres recherches sont déjà en cours</p>";
        searchDone();
        return null;
    }

    searchStart();
    zoneResultEntries.innerHTML = "<p>Recherche en cours !</p> <img src='/assets/loading.gif' alt='recherche en cours'/>";

    relationClicked[relation] = relation;
    var buttonRelationClicked =  $("#buttonDisplay_".relation)
    buttonRelationClicked.attr("disabled", false);
    $.ajax({
        url: '/search-entries-for-term-by-relation/'+relation+'/'+term,
        type: 'GET',
        dataType : 'html',
        success : function(code_html, statut){
            rechercheEnCours = true;
            zoneResultEntries.innerHTML = code_html;
        },
        error : function(resultat, statut, erreur){
            zoneResultEntries.innerHTML = phraseErreur;
        },
        complete : function(resultat, statut){
            searchDone();
            buttonRelationClicked.attr("disabled", true);
        }
    });
}

function getFirstDef(term ,callback){
    $.ajax({
        url: 'search-first-definition/'+term,
        type: 'GET',
        dataType : 'json',
        success : function(result, statut){
            callback(JSON.parse(result));
        },
        error : function(resultat, statut, erreur){
        },
        complete : function(resultat, statut){
        }
    });
    callback(null);
}

function searchRaffinementList(){
    $.ajax({
        url: 'search-raffinement-list/'+termBarre.val(),
        type: 'GET',
        dataType : 'json',
        success : function(result, statut){

            result = JSON.parse(result);
            if(result !== null){
                rechercheEnCours = true;
                sectionRaff.removeClass("default-hiden");
                var nbResult = 0;
                resultDefZone.innerHTML = ""; //on clear

                result.entries.forEach(function(e){
                    getFirstDef(e.nodeOut, function(def){
                        if(def !== null){

                            let htmlResult = "<tr><td>"+nbResult+"</td><td>"+def[0].def;
                            def[0].examples.forEach(function(ex){
                                htmlResult = htmlResult+"<br/><small><i>"+ex+"</i></small>";
                            });
                            htmlResult = htmlResult + "</td></tr>";

                            resultDefZone.append(htmlResult);
                            nbResult++;
                        }
                    });
                });
            }else{
                resultDefZone.append('<td colspan="2">Aucune définition par raffinement trouvé.</td>');
            }
        },
        error : function(resultat, statut, erreur){
            resultDefZone.append(phraseErreur);
        },
        complete : function(resultat, statut){
            searchDone();
        }
    });
}

function getAutoCompletLetter(term ,callback){
    $.ajax({
        url: 'search-auto-complet-letter/'+term,
        type: 'GET',
        dataType : 'json',
        success : function(result, statut){
            callback(JSON.parse(result));
        },
        error : function(resultat, statut, erreur){
        },
        complete : function(resultat, statut){
        }
    });
    callback(null);
}

var lettreTest = [];

$(function(){
    getAutoCompletLetter("a", function (l) {
        lettreTest = l;
        console.log(l)
    });
});

termBarre.autocomplete({
    source:lettreTest
});

termBarre.bind("enterKey",function(e){
    searchOnJDM();
});
termBarre.keyup(function(e){
    if(e.keyCode == 13)
    {
        $(this).trigger("enterKey");
    }
});