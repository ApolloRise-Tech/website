import "./faq.scss";

document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".faq__question").forEach((btn) => {
    btn.addEventListener("click", () => {
      const item = btn.closest(".faq__item");
      const wasOpen = item.classList.contains("is-open");

      item.closest(".faq__list").querySelectorAll(".faq__item").forEach((el) => {
        el.classList.remove("is-open");
      });

      if (!wasOpen) item.classList.add("is-open");
    });
  });
});
