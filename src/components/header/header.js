import './header.scss'

document.addEventListener("DOMContentLoaded", function() {
  const burger = document.getElementsByClassName("header__burger")[0];
  const close = document.getElementsByClassName("header__close")[0];
  const menuMobile = document.getElementsByClassName('header__mobile_container')[0];

  menuMobile.style.height = `${window.innerHeight}px`;

  const toggleMenu = () => {
    if(!menuMobile.classList.contains('_open')) {
      menuMobile.classList.add('_open');
      document.body.style.overflow = 'hidden';
    } else {
      menuMobile.classList.remove('_open');
      document.body.style.overflow = 'unset';
    }
  }

  window.addEventListener('resize', () => {
    menuMobile.style.height = `${window.innerHeight}px`;
  });

  burger.addEventListener("click", toggleMenu);
  close.addEventListener("click", toggleMenu);
})