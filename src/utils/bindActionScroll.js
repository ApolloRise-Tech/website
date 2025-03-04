export function bindActionScroll() {
    const actionButtons = document.getElementsByClassName('action_button');

    // Loop through the HTMLCollection and add event listener to each button
    for (let i = 0; i < actionButtons.length; i++) {
        actionButtons[i].addEventListener('click', function (e) {
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
}