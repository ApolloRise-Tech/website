import intlTelInput from "intl-tel-input";

import "intl-tel-input/build/css/intlTelInput.css";
import "./contactUs.scss";

const url = "https://apollorise.tech/contact.php";

document.addEventListener("DOMContentLoaded", function () {
  const contactUsForm = document.getElementById("contactUsForm");
  const policyInput = document.getElementsByName("policy")[0];
  const buttonSubmit = document.getElementById("contactUsButtonSubmit");

  const select = document.querySelector(".custom-select");
  const selected = select.querySelector(".select-selected");
  const items = select.querySelector(".select-items");
  const hiddenInput = document.getElementsByName("why")?.[0];

  let iti = undefined;

  const phone = document.querySelector("#phone");

  if (phone) {
    iti = intlTelInput(phone, {
      initialCountry: "us",
      preferredCountries: ["us", "gb"],
      separateDialCode: true,
      placeholderNumberType: "MOBILE",
      countrySearch: false,
      useFullscreenPopup: false,
      utilsScript:
        "https://cdn.jsdelivr.net/npm/intl-tel-input@25.3.0/build/js/utils.js",
    });
  }

  // Показать / скрыть список при клике
  selected.addEventListener("click", function (e) {
    e.stopPropagation();
    items.classList.toggle("select-hide");
    selected.classList.toggle("select-arrow-active");
  });

  // Клик на элемент списка
  items.querySelectorAll("div").forEach((item) => {
    item.addEventListener("click", function (e) {
      const value = this.textContent;
      const paragraf = selected.getElementsByTagName("p")[0];

      if (paragraf) {
        paragraf.textContent = value;
      }

      hiddenInput.value = value;

      items.querySelectorAll("div").forEach((el) => {
        el.classList.remove("active");
      });

      this.classList.add("active");

      closeSelect();
    });
  });

  // Закрытие селекта при клике вне его
  document.addEventListener("click", closeSelect);

  function closeSelect() {
    if (selected.classList.contains("select-arrow-active")) {
      selected.classList.remove("select-arrow-active");
      items.classList.add("select-hide");
    }
  }

  const EMAIL_REGEXP =
    /^(([^<>()\[\]\.,;:\s@\"]+(\.[^<>()\[\]\.,;:\s@\"]+)*)|(\".+\"))@(([^<>()[\]\.,;:\s@\"]+\.)+[^<>()[\]\.,;:\s@\"]{2,})$/gm;
  const validatePhoneNumber = (phoneNumber) => {
    // This regex covers international phone numbers with optional + and country code
    const regex =
      /^\+?[1-9]\d{0,2}[\s-]?\(?\d{1,3}\)?[\s-]?\d{3}[\s-]?\d{4}$|^\d{10}$/;
    return regex.test(phoneNumber);
  };

  const checkValidForm = () => {
    const fieldsToCheck = ["name", "email", "company", "title"];
    let isCanSubmit = true;

    fieldsToCheck.forEach((field) => {
      const value = document.getElementsByName(field)[0].value;
      const label = document.querySelector(`.contactUs__label[for="${field}"]`);

      if (value === "") {
        isCanSubmit = false;
        label.classList.add("error");
      } else {
        label.classList.remove("error");
      }
    });

    const label = document.querySelector(`.contactUs__label[for="phone"]`);
    if (
      Boolean(phone.value) &&
      !validatePhoneNumber(
        `+${iti.selectedCountryData.dialCode}${phone?.value}`
      )
    ) {
      isCanSubmit = false;
      label.classList.add("error");
    } else {
      label.classList.remove("error");
    }

    return isCanSubmit;
  };

  const submitForm = async () => {
    const isCanSubmit = checkValidForm();

    if (isCanSubmit) {
      const formData = new FormData(contactUsForm);
      const headers = new Headers();
      if (phone) {
        formData.delete("phone");
        const phoneValue = `+${iti.selectedCountryData.dialCode}${
          phone?.value || ""
        }`;
        formData.append("phone", phoneValue);
      }
      headers.append("Accept", "application/json");

      const requestOptions = {
        method: "POST",
        mode: "cors",
        headers,
        body: formData,
        redirect: "follow",
      };
      buttonSubmit.disabled = true;
      try {
        await fetch(url, requestOptions);
        window.location.href = "/successContact.html";
      } catch (e) {
        buttonSubmit.disabled = false;
        alert("Please try again");
      }
    }
  };

  contactUsForm.addEventListener("submit", async (event) => {
    event.preventDefault();
    await submitForm();
  });

  policyInput.addEventListener("change", (event) => {
    if (event.currentTarget.checked) {
      buttonSubmit.disabled = false;
    } else {
      buttonSubmit.disabled = true;
    }
  });
});
