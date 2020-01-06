/**
 *
 */
function displayEntries(id){
    console.log(id);
    var entry = document.getElementById(id);
    if(entry.style.display == "block")
        entry.style.display = "none";
    else
        entry.style.display = "block"
}
