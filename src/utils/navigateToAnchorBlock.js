export function navigateToAnchorBlock (anchorLinks) {
  const menuMobile = document.getElementsByClassName('header__mobile_container')[0];

  anchorLinks.forEach(anchor => {
    const link = anchor.querySelector('a[href^="#"]');
    if(link !== null ) {

      link.addEventListener('click', function (e) {
        e.preventDefault();

        const targetId = this.getAttribute('href').substring(1);
        const targetElement = document.getElementById(targetId);

        if(menuMobile.classList.contains('_open')) {
          menuMobile.classList.remove('_open');
          document.body.style.overflow = 'unset';
        }

        if (targetElement) {
          window.scrollTo({
            top: targetElement.offsetTop,
            behavior: 'smooth'
          });
        }
      });
    }
  });
}