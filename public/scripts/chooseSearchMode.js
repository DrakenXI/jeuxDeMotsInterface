/**
 * Change address to request depending on mode search chosen.
 */
function setAction(){
    var form = document.getElementById('form');
    var mode = document.getElementById('search-mode');
    var term = document.getElementById('term').value;
    var relations = document.getElementById('relations').options;
    //console.log("toto"+relations);
    var formAction;

    // depending on the search mode, action will differ.
    if("exacte" == mode.value) {                // exact mode
        formAction = "search/"+term;

    } else if ("approximative" == mode.value) { // approximatif mode
        formAction = "search-approx/"+term;

    } else if ("relation" == mode.value) {      // search by relations
        // gathers wanted relation
        var relationsSelected = "";
        for (var i=0, iLen=relations.length; i<iLen; i++) {
            opt = relations[i];
            //console.log(opt);
            if (opt.selected) {
                var id = opt.value.split("_");
                relationSelected=id[0];
            }
        }
        formAction = "search-relations/"+relationSelected+"/"+term;
    } else {                                    // shall not happen
        formAction = "";
    }

    // puts chosen action
    form.action = formAction;
}

/**
 * Change explanation text depending on search mode chosen.
 */
function setExplanationText() {
    var form = document.getElementById('form');
    var mode = document.getElementById('search-mode');
    var text;
    var relationList = document.getElementById('relations');
    relationList.style.visibility = "hidden";
    if("exacte" == mode.value) {
        text = "Recherche le terme tel quel parmi les entrées de Jeux de Mots.";
    } else if ("approximative" == mode.value) {
        text = "Recherche le terme approximativement parmi les entrées de Jeux de Mots.";
    } else if ("relation" == mode.value) {
        text = "Recherche le terme associé à la relation choisie dans Jeux de Mots.";
        relationList.style.visibility = "visible";
    } else {
        text = "Choisissez le mode de recherche.";
    }
    document.getElementById("mode-explanation").innerHTML= text;
}
