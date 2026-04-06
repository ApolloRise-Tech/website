import "./quickContact.scss";

const API_URL = "https://apollorise.tech/contact.php";

document.addEventListener("DOMContentLoaded", () => {
  const root = document.querySelector(".quickContact");
  if (!root) return;

  const trigger = root.querySelector(".quickContact__trigger");
  const form = root.querySelector("#quickContactForm");
  const checkbox = root.querySelector(".quickContact__checkbox");
  const submit = root.querySelector(".quickContact__submit");

  const LABEL_KEY = "qc_label_shown";
  if (!sessionStorage.getItem(LABEL_KEY)) {
    setTimeout(() => {
      root.classList.add("show-label");
      setTimeout(() => {
        root.classList.remove("show-label");
        sessionStorage.setItem(LABEL_KEY, "1");
      }, 4000);
    }, 3000);
  }

  trigger.addEventListener("click", () => {
    root.classList.remove("show-label");
    const willOpen = !root.classList.contains("is-open");
    root.classList.toggle("is-open");
    if (willOpen) {
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({ event: "quick_contact_open" });
    }
  });

  document.addEventListener("click", (e) => {
    if (root.classList.contains("is-open") && !root.contains(e.target)) {
      root.classList.remove("is-open");
    }
  });

  document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") root.classList.remove("is-open");
  });

  checkbox.addEventListener("change", () => {
    submit.disabled = !checkbox.checked;
  });

  form.addEventListener("submit", async (e) => {
    e.preventDefault();

    const fields = [
      { name: "qc_name", required: true },
      { name: "qc_email", required: true },
      { name: "qc_details", required: true },
    ];

    let valid = true;
    fields.forEach((f) => {
      const input = form.querySelector(`[name="${f.name}"]`);
      const field = input.closest(".quickContact__field");
      if (f.required && !input.value.trim()) {
        field.classList.add("has-error");
        valid = false;
      } else {
        field.classList.remove("has-error");
      }
    });

    if (!valid) return;

    submit.disabled = true;

    const formData = new FormData();
    formData.append("name", form.querySelector('[name="qc_name"]').value);
    formData.append("email", form.querySelector('[name="qc_email"]').value);
    formData.append("company", form.querySelector('[name="qc_position"]').value || "—");
    formData.append("url", form.querySelector('[name="qc_phone"]').value || "—");
    formData.append("details", form.querySelector('[name="qc_details"]').value);

    try {
      await fetch(API_URL, {
        method: "POST",
        mode: "cors",
        headers: { Accept: "application/json" },
        body: formData,
      });
      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({ event: "form_submit", form_name: "quick_contact" });
      root.classList.add("is-sent");
      setTimeout(() => {
        root.classList.remove("is-open");
        setTimeout(() => {
          root.classList.remove("is-sent");
          form.reset();
          submit.disabled = true;
        }, 400);
      }, 2500);
    } catch {
      submit.disabled = false;
      alert("Something went wrong. Please try again.");
    }
  });

  form.querySelectorAll(".quickContact__input").forEach((input) => {
    input.addEventListener("input", () => {
      input.closest(".quickContact__field").classList.remove("has-error");
    });
  });
});
