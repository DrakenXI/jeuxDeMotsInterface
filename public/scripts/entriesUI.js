/**
 *
 */
function displayEntries(id){
    console.log(id);
    let entry = document.getElementById(id);
    let buttonEntry = document.getElementById("buttonDisplay_"+id);
    if(entry.style.display == "block") {
        entry.style.display = "none";
        buttonEntry.classList.remove("green-button");
    }else{
        entry.style.display = "block";
        buttonEntry.classList.add("green-button");
    }
}
