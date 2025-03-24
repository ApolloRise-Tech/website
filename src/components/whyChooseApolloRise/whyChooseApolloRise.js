import "./whyChooseApolloRise.scss";
import Swiper from 'swiper';
import { Navigation, Pagination } from 'swiper/modules';
import 'swiper/css';

document.addEventListener("DOMContentLoaded", function () {
  new Swiper(".ourCoreValues__swiper", {
    modules: [Navigation, Pagination],
    slidesPerView: 1.1,
    spaceBetween: 8,
    touchEventsTarget: 'container',
    preventInteractionOnTransition: true,
    navigation: {
      nextEl: ".ourCoreValues-button-next",
      prevEl: ".ourCoreValues-button-prev",
    },
    pagination: {
      el: ".ourCoreValues-pagination",
      clickable: true,
    },
    breakpoints: {
      500: {
        slidesPerView: 1.55,
      },
      756: {
        slidesPerView: 3.05,
      },
      1280: {
        slidesPerView: 3.6,
      },
      1600: {
        slidesPerView: 4.125,
      }
    }
  });
});