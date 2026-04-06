function sendToDataLayer(metric) {
  window.dataLayer = window.dataLayer || [];
  window.dataLayer.push({
    event: "web_vitals",
    vitals_name: metric.name,
    vitals_value: Math.round(metric.name === "CLS" ? metric.value * 1000 : metric.value),
    vitals_id: metric.id,
    vitals_rating: metric.rating,
  });
}

if ("PerformanceObserver" in window) {
  // LCP
  try {
    const lcpObserver = new PerformanceObserver((list) => {
      const entries = list.getEntries();
      const last = entries[entries.length - 1];
      sendToDataLayer({ name: "LCP", value: last.startTime, id: "lcp", rating: last.startTime <= 2500 ? "good" : last.startTime <= 4000 ? "needs-improvement" : "poor" });
    });
    lcpObserver.observe({ type: "largest-contentful-paint", buffered: true });
  } catch (e) { /* unsupported */ }

  // FID
  try {
    const fidObserver = new PerformanceObserver((list) => {
      const entry = list.getEntries()[0];
      const value = entry.processingStart - entry.startTime;
      sendToDataLayer({ name: "FID", value, id: "fid", rating: value <= 100 ? "good" : value <= 300 ? "needs-improvement" : "poor" });
    });
    fidObserver.observe({ type: "first-input", buffered: true });
  } catch (e) { /* unsupported */ }

  // CLS
  try {
    let clsValue = 0;
    const clsObserver = new PerformanceObserver((list) => {
      for (const entry of list.getEntries()) {
        if (!entry.hadRecentInput) clsValue += entry.value;
      }
    });
    clsObserver.observe({ type: "layout-shift", buffered: true });

    addEventListener("visibilitychange", () => {
      if (document.visibilityState === "hidden") {
        sendToDataLayer({ name: "CLS", value: clsValue, id: "cls", rating: clsValue <= 0.1 ? "good" : clsValue <= 0.25 ? "needs-improvement" : "poor" });
      }
    }, { once: true });
  } catch (e) { /* unsupported */ }
}
