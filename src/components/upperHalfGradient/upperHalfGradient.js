import "./upperHalfGradient.scss";

document.addEventListener("DOMContentLoaded", function() {
  const startYourProjectButton = document.getElementById('main__button');
  

  startYourProjectButton.addEventListener('click', function (e) {
    e.preventDefault();

    
    const targetElement = document.getElementById('contactUs');

    if (targetElement) {
      window.scrollTo({
        top: targetElement.offsetTop,
        behavior: 'smooth'
      });
    }
  });
})