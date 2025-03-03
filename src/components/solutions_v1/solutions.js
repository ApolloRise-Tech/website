import "./solutions.scss";

document.addEventListener("DOMContentLoaded", () => {
  const solutionsContainer = document.querySelector(".solutions");
  const solutionsContent = document.querySelector(".solutions_content");
  const cases = document.querySelectorAll(".case_textContainer");
  const messages = document.querySelectorAll(".message");

  // Проверяем, существуют ли элементы
  if (!solutionsContainer || !solutionsContent || cases.length === 0 || messages.length === 0) {
    console.warn("Не удалось найти необходимые элементы DOM для solutions.");
    return;
  }

  // Делаем первое изображение активным по умолчанию
  if (messages.length > 0) {
    messages[0].classList.add("active");
  }

  let currentActiveIndex = 0;
  let isContentFullyVisible = false;

  // Функция для проверки полной видимости элемента
  const isElementFullyVisible = (element) => {
    const rect = element.getBoundingClientRect();
    const windowHeight = window.innerHeight || document.documentElement.clientHeight;
    return rect.top >= 0 && rect.bottom <= windowHeight;
  };

  // Обработчик скроллинга для активации/деактивации скролла
  const handleScroll = () => {
    if (isElementFullyVisible(solutionsContent)) {
      if (!isContentFullyVisible) {
        solutionsContent.classList.add("scroll-enabled");
        isContentFullyVisible = true;
      }
    } else {
      if (isContentFullyVisible) {
        solutionsContent.classList.remove("scroll-enabled");
        isContentFullyVisible = false;
      }
    }
  };

  // Добавляем слушатель события скроллинга
  window.addEventListener("scroll", handleScroll);

  // Наблюдатель для переключения изображений
  const caseObserver = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (!entry.target || !entry.target.getAttribute) {
            console.warn("Элемент не найден или не имеет атрибута getAttribute.");
            return;
          }

          const id = entry.target.getAttribute("id");
          if (!id) {
            console.warn("Элемент не имеет атрибута id.");
            return;
          }

          const index = parseInt(id.replace(/[^\d]/g, ""), 10) - 1;
          const messageItem = messages[index];

          if (!messageItem) {
            console.warn(`Изображение с индексом ${index} не найдено.`);
            return;
          }

          if (entry.isIntersecting && entry.intersectionRatio >= 0.5) {
            if (currentActiveIndex !== index) {
              if (messages[currentActiveIndex]) {
                messages[currentActiveIndex].classList.remove("active");
              }
              messageItem.classList.add("active");
              currentActiveIndex = index;
            }
          }
        });
      },
      {
        root: solutionsContainer, // Наблюдаем внутри .solutions
        threshold: 0.5, // Срабатывает, когда 50% текстового блока видно
      }
  );

  // Начать наблюдение за текстовыми блоками
  cases.forEach((caseItem) => {
    if (caseItem) {
      caseObserver.observe(caseItem);
    } else {
      console.warn("Элемент case_textContainer не найден.");
    }
  });

  // Инициализируем проверку видимости при загрузке страницы
  handleScroll();
});

