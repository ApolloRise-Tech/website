import './caseRecommendationCard.scss';

document.addEventListener("DOMContentLoaded", function() {
  const caseRecommendationCard = document.querySelectorAll('.caseRecommendationCard');
  
  caseRecommendationCard.forEach(card => {
    card.querySelectorAll('a').forEach(link => 
      link.addEventListener('click', (e) => {
        if(link.getAttribute('href') === '') {
          e.preventDefault();
        }
      })
    );
  })
})