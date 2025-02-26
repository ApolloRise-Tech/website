import "./successForm.scss";

document.addEventListener("DOMContentLoaded", function() {
  const goToMain = document.getElementById("go_main");

  goToMain.addEventListener("click", () => {
    window.location.replace('/');
  })
})