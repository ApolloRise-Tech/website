import "./contactUs.scss";

const url = 'https://apollorise.tech/contact.php';

document.addEventListener("DOMContentLoaded", function() {
  const contactUsForm = document.getElementById('contactUsForm');
  const policyInput = document.getElementsByName('policy')[0];
  const buttonSubmit = document.getElementById('contactUsButtonSubmit')

  const checkValidForm = () => {
    const fieldsToCheck = ['name', 'email', 'company', 'title'];
    let isCanSubmit = true;

    fieldsToCheck.forEach(field => {
      const value = document.getElementsByName(field)[0].value;
      const label = document.querySelector(`.contactUs__label[for="${field}"]`);

      if (value === "") {
        isCanSubmit = false;
        label.classList.add('error');
      } else {
        label.classList.remove('error');
      }
    });
    return isCanSubmit;
  }

  const submitForm = async () => {
    const isCanSubmit = checkValidForm();
  
    if (isCanSubmit) {
      const formData = new FormData(contactUsForm);
      const headers = new Headers();
      headers.append("Accept", "application/json");
  
      const requestOptions = {
        method: 'POST',
        mode: "cors",
        headers,
        body: formData,
        redirect: 'follow'
      };
      buttonSubmit.disabled = true;
      try {
        await fetch(url, requestOptions);
        window.location.href = '/successContact.html';
      } catch (e) {
        buttonSubmit.disabled = false;
        alert("Please try again");
      }
    }
  }

  contactUsForm.addEventListener('submit', async (event) => {
    event.preventDefault();
    await submitForm();
  });
  

  policyInput.addEventListener('change', (event) => {
    if (event.currentTarget.checked) {
      buttonSubmit.disabled = false;
    } else {
      buttonSubmit.disabled = true;
    }
  })

})
