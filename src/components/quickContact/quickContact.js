import "./quickContact.scss";

const API_URL = "https://apollorise.tech/contact.php";

document.addEventListener("DOMContentLoaded", () => {
  const root = document.querySelector(".quickContact");
  if (!root) return;

  const trigger = root.querySelector(".quickContact__trigger");
  const form = root.querySelector("#quickContactForm");
  const checkbox = root.querySelector(".quickContact__checkbox");
  const submit = root.querySelector(".quickContact__submit");

  trigger.addEventListener("click", () => {
    root.classList.toggle("is-open");
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
