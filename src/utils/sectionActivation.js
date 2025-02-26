export function sectionActivation(sections) {
  const OFFSET_HEADER = 40;
  
  sections.forEach((section) => {
    const rect = section.getBoundingClientRect();
    const isTopVisible = rect.top <= window.innerHeight - OFFSET_HEADER;
    const isBottomVisible = rect.bottom >= OFFSET_HEADER;
    section.classList.toggle('activeSection', isTopVisible && isBottomVisible);
  });
}