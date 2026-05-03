const btn = document.getElementById("themeButton");
const body = document.body;

btn.addEventListener("click", function() {
    body.classList.toggle("dark-mode");

    if (body.classList.contains("dark-mode")) {
        btn.innerHTML = "Ubah ke Light Mode";
    } else {
        btn.innerHTML = "Ubah ke Dark Mode";
    }
});