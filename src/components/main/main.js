import "./main.scss";

document.addEventListener("DOMContentLoaded", function() {
  const startYourProjectButtons = document.getElementsByClassName('action_button');

  // Loop through the HTMLCollection and add event listener to each button
  for (let i = 0; i < startYourProjectButtons.length; i++) {
    startYourProjectButtons[i].addEventListener('click', function (e) {
      e.preventDefault();

      const targetElement = document.getElementById('contactUs');

      if (targetElement) {
        window.scrollTo({
          top: targetElement.offsetTop,
          behavior: 'smooth'
        });
      }
    });
  }
});