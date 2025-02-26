import "./howWeWork.scss";
import Swiper from 'swiper';
import { Navigation, Pagination } from 'swiper/modules';
import 'swiper/css';

const ANIMATION_DELAY = 100;
let howWeWorkSwiperInitialized = false;

function initSwiper() {

  if (!howWeWorkSwiperInitialized && window.innerWidth <= 834) {
    howWeWorkSwiperInitialized = true;
  
    new Swiper(".howWeWork__cards_swiper", {
      modules: [ Navigation, Pagination ],
      slidesPerView: 1,
      spaceBetween: 8,
      touchEventsTarget: 'container',
      preventInteractionOnTransition: true,
      navigation: {
        nextEl: ".howWeWork-button-next",
        prevEl: ".howWeWork-button-prev",
      },
      pagination: {
        el: ".howWeWork-pagination",
        clickable: true,
      },
      breakpoints: {
        320: {
          slidesPerView: 1.01,
        },
        660: {
          slidesPerView: 1.125,
          spaceBetween: 16,
        }
      }
    });
  }
}


const StackCards = function(element) {
  this.element = element;
  this.items = this.element.getElementsByClassName('howWeWork__card');
  this.scrollingListener = false;
  this.scrolling = false;
  initStackCardsEffect(this);
};

function initStackCardsEffect(element) { // use Intersection Observer to trigger animation
  setStackCards(element);
  const observer = new IntersectionObserver(stackCardsCallback.bind(element));
  observer.observe(element.element);
};

function setStackCards(element) {
  // store wrapper properties
  element.marginY = 30;
  // getIntegerFromProperty(element); // convert element.marginY to integer (px value)
  element.elementHeight = element.element.offsetHeight;

  // store card properties
  var cardStyle = getComputedStyle(element.items[0]);
  element.cardTop = Math.floor(parseFloat(cardStyle.getPropertyValue('top')));
  element.cardHeight = Math.floor(parseFloat(cardStyle.getPropertyValue('height')));

  // store window property
  element.windowHeight = window.innerHeight;

  // reset margin + translate values
  if(isNaN(element.marginY)) {
    element.element.style.paddingBottom = '0px';
  } else {
    element.element.style.paddingBottom = (element.marginY*(element.items.length - 1))+'px';
  }

  for(var i = 0; i < element.items.length; i++) {
    if(isNaN(element.marginY)) {
      element.items[i].style.transform = 'none;';
      element.items[i].style.opacity = '1';
    } else {
      element.items[i].style.transform = 'translateY('+element.marginY*i+'px)';
      element.items[i].style.transform = 'none;';
    }
  }
};
 
function stackCardsCallback(entries) {
  if (entries[0].isIntersecting) {
    stackCardsInitEvent(this);
  } else {
    if (this.scrollingListener) {
      window.removeEventListener('scroll', this.scrollingListener);
      this.scrollingListener = false;
    }
  }
}
 
function stackCardsInitEvent(element) {
  element.scrollingListener = stackCardsScrolling.bind(element);
  window.addEventListener('scroll', element.scrollingListener);
};
 
function stackCardsScrolling() {
  if(this.scrolling) return;
  this.scrolling = true;
  
  window.requestAnimationFrame(animateStackCards.bind(this));
};
 
function animateStackCards() {
  const top = this.element.getBoundingClientRect().top;
  const title = document.getElementsByClassName('howWeWork__title')[0];
  
  for(let i = 0; i < this.items.length; i++) {
  // cardTop/cardHeight/marginY are the css values for the card top position/height/Y offset
    const scrolling = this.cardTop - top - i*(this.cardHeight+this.marginY);
    if(scrolling > 0) { // card is fixed - we can scale it down
      this.items[i].setAttribute('style', 'transform: translateY('+this.marginY*i+'px) scale('+(this.cardHeight - scrolling*0.05)/this.cardHeight+'); opacity: '+(this.cardHeight - scrolling*0.005)/this.cardHeight+';');
    }

    if(i === 5) {
      if(scrolling > 2000) {
        title.style.opacity = '0';
        setTimeout(() => {
          title.style.position = 'static';
        }, ANIMATION_DELAY)
      } else {
        title.style.position = 'sticky';
        title.style.opacity = '1';
      }
    }
  }
 
  this.scrolling = false;
};

document.addEventListener("DOMContentLoaded", function() {
  const stackCardsElements = document.getElementsByClassName('howWeWork');
  const intersectionObserverSupported = ('IntersectionObserver' in window && 'IntersectionObserverEntry' in window && 'intersectionRatio' in window.IntersectionObserverEntry.prototype);
  
  function initCards() {
    if(window.innerWidth > 834) {
      if(stackCardsElements.length > 0 && intersectionObserverSupported) { 
        for(let i = 0; i < stackCardsElements.length; i++) {
          new StackCards(stackCardsElements[i]);
        }
      }
    } else {
      initSwiper();
    }
  }

  initCards();

  // Add resize event listener to re-initialize cards on window resize
  window.addEventListener('resize', function() {
    initCards();
  });
});

