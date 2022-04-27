const menuButton = document.getElementById("menuButton");
menuButton.closed = false; openNav();
menuButton.onclick = function() { 
    if (menuButton.closed) {
        openNav();
    } else {
        closeNav();
    }
    menuButton.closed = !menuButton.closed;
    menuButton.style.width = "30px";
};
function openNav() {
    document.getElementById("mapmenu").style.width = "300px";
}
function closeNav() {
    document.getElementById("mapmenu").style.width = "0";
}