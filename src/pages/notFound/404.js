import "../../style/index.scss";
import "./404.scss";

import '../../components/header/header';
import '../../components/footer/footer';

import { animateItems } from '../../utils/animateItems';

document.addEventListener("DOMContentLoaded", function() {

  const DELAY = 300;
  const HEADER_HEIGHT = 55;

  const animItems = document.querySelectorAll('._anim-items');
  const header = document.querySelector('.header');
  const goToMain = document.getElementById("go_main");

  if(animItems.length > 0 && header) {
    window.addEventListener('scroll', animOnScroll);
    function animOnScroll() {

      if(pageYOffset > HEADER_HEIGHT) {
        header.classList.add('header-scroll');
      } else {
        header.classList.remove('header-scroll');
      }

      animateItems(animItems);
    }

    setTimeout(() => {
      animOnScroll()
    }, DELAY)
  }

  goToMain.addEventListener("click", () => {
    window.location.replace('/');
  })
})