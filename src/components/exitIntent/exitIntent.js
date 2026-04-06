import "./exitIntent.scss";

document.addEventListener("DOMContentLoaded", () => {
  const root = document.querySelector(".exitIntent");
  if (!root) return;

  const STORAGE_KEY = "exitIntent_dismissed";
  if (sessionStorage.getItem(STORAGE_KEY)) {
    root.classList.add("is-dismissed");
    return;
  }

  let shown = false;
  const SCROLL_THRESHOLD = 0.65;
  const DELAY_MS = 5000;

  function show() {
    if (shown) return;
    shown = true;
    root.classList.add("is-visible");
  }

  function dismiss() {
    root.classList.remove("is-visible");
    sessionStorage.setItem(STORAGE_KEY, "1");
    setTimeout(() => root.classList.add("is-dismissed"), 400);
  }

  // Desktop: cursor leaves viewport (exit-intent)
  setTimeout(() => {
    document.addEventListener("mouseleave", (e) => {
      if (e.clientY <= 0) show();
    });
  }, DELAY_MS);

  // Mobile: scroll 65%+ of page
  setTimeout(() => {
    window.addEventListener("scroll", () => {
      const scrolled = window.scrollY / (document.body.scrollHeight - window.innerHeight);
      if (scrolled >= SCROLL_THRESHOLD) show();
    }, { passive: true });
  }, DELAY_MS);

  // Close button
  root.querySelector(".exitIntent__close").addEventListener("click", dismiss);

  // Form submit
  const form = root.querySelector("#exitIntentForm");
  form.addEventListener("submit", async (e) => {
    e.preventDefault();
    const email = form.querySelector('input[name="exit_email"]').value;

    const formData = new FormData();
    formData.append("name", "Exit Intent Lead");
    formData.append("email", email);
    formData.append("details", "Submitted via exit-intent banner");

    try {
      await fetch("https://apollorise.tech/contact.php", {
        method: "POST",
        mode: "cors",
        body: formData,
      });

      window.dataLayer = window.dataLayer || [];
      window.dataLayer.push({ event: "form_submit", form_name: "exit_intent" });

      root.classList.add("is-sent");
      setTimeout(dismiss, 3000);
    } catch {
      alert("Please try again");
    }
  });
});
