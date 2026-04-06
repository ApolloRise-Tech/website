import "./readingProgress.scss";

document.addEventListener("DOMContentLoaded", () => {
  const container = document.createElement("div");
  container.className = "readingProgress";
  container.innerHTML = '<div class="readingProgress__bar"></div>';
  document.body.prepend(container);

  const bar = container.querySelector(".readingProgress__bar");

  window.addEventListener("scroll", () => {
    const scrollTop = window.scrollY;
    const docHeight = document.body.scrollHeight - window.innerHeight;
    const progress = docHeight > 0 ? Math.min(scrollTop / docHeight, 1) : 0;
    bar.style.width = (progress * 100) + "%";
  }, { passive: true });
});
