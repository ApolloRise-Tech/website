import "../../style/index.scss";

import '../../components/header/header';
import '../../components/main_old/main';
import '../../components/about/about';
import '../../components/whatWeDo/whatWeDo';
import '../../components/industries/industries';
import '../../components/caseRecommendationCard/caseRecommendationCard';
import '../../components/howWeWork/howWeWork';
import '../../components/ourCoreValues/ourCoreValues';
import '../../components/contactUs/contactUs';
import '../../components/footer/footer';

import './index.scss';

import { animateItems } from '../../utils/animateItems';
import { sectionActivation } from '../../utils/sectionActivation';
import { navigateToAnchorBlock } from '../../utils/navigateToAnchorBlock';

document.addEventListener("DOMContentLoaded", function() {

  const DELAY = 300;
  const HEADER_HEIGHT = 55;

  const metaTheme = document.querySelector('meta[name="theme-color"]');
  const animItems = document.querySelectorAll('._anim-items');
  const header = document.querySelector('.header');
  const headerConnectButton = document.querySelector('.header__connect');
  const sections = document.querySelectorAll('.colorSection');
  const anchorHeaderLinks = document.querySelectorAll(".header__link");
  const anchorHeaderMobileLinks = document.querySelectorAll(".header__mobile_link");
  const anchorFooterLinks = document.querySelectorAll(".footer__link");

  if(animItems.length > 0 && header) {
    window.addEventListener('scroll', animOnScroll);
    function animOnScroll() {

      if(pageYOffset > HEADER_HEIGHT) {
        header.classList.add('header-scroll');
      } else {
        header.classList.remove('header-scroll');
      }

      const activeSection = document.querySelector('.colorSection.activeSection');
      if (activeSection && activeSection.getAttribute('data-color') === 'dark') {
        header.classList.add('dark');
        metaTheme.setAttribute('content',  '#23221F');
      } else {
        header.classList.remove('dark');
        metaTheme.setAttribute('content',  '#FCFBF8');
      }

      if (activeSection && !activeSection.classList.contains("main")) {
        headerConnectButton.classList.add('header__connect-dark');
      } else {
        headerConnectButton.classList.remove('header__connect-dark');
      }

      sectionActivation(sections);
      animateItems(animItems);
    }

    setTimeout(() => {
      animOnScroll()
    }, DELAY)
  }

  navigateToAnchorBlock(anchorHeaderLinks);
  navigateToAnchorBlock(anchorHeaderMobileLinks);
  navigateToAnchorBlock(anchorFooterLinks);
})