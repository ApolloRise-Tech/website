import "./footer.scss"

document.querySelector('.footer__button_toTop').addEventListener('click', () => {
  window.scrollTo({
    top: 0,
    behavior: 'smooth',
  }), false
});
