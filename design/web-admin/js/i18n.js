// Đa ngôn ngữ VI / JA: phần tử có data-ja đổi nội dung; data-m (tiền), data-mc (tiền rút gọn), data-n (số), data-d + data-f (ngày).
/*  — sinh từ HotelBooking_Mockup.html bởi design/tools/build.js, không sửa tay */
(function () {
  const WD = { vi: ["CN", "T2", "T3", "T4", "T5", "T6", "T7"], ja: ["日", "月", "火", "水", "木", "金", "土"] };
  const LOC = { vi: "vi-VN", ja: "ja-JP" };
  function fmtMoney(v, l) {
    const n = Math.abs(v);
    const s = new Intl.NumberFormat(LOC[l]).format(n);
    return (v < 0 ? "−" : "") + (l === "ja" ? s + "₫" : s + " ₫");
  }
  function fmtCompact(v, l) {
    return (
      new Intl.NumberFormat(LOC[l], { notation: "compact", maximumFractionDigits: 1 }).format(v) +
      (l === "ja" ? "₫" : " ₫")
    );
  }
  function fmtDate(s, f, l) {
    const [y, m, d] = s.split("-").map(Number);
    const dt = new Date(Date.UTC(y, m - 1, d));
    const w = WD[l][dt.getUTCDay()];
    const p = (n) => String(n).padStart(2, "0");
    if (l === "ja") {
      if (f === "mdw") return `${m}/${d}(${w})`;
      if (f === "dm") return `${m}/${d}`;
      if (f === "dmy") return `${y}/${p(m)}/${p(d)}`;
      if (f === "my") return `${y}年${m}月`;
      if (f === "long") return `${y}年${m}月${d}日(${w})`;
    }
    if (f === "mdw") return `${w}, ${p(d)}/${p(m)}`;
    if (f === "dm") return `${p(d)}/${p(m)}`;
    if (f === "dmy") return `${p(d)}/${p(m)}/${y}`;
    if (f === "my") return `Tháng ${m}/${y}`;
    if (f === "long") return `${w === "CN" ? "Chủ nhật" : "Thứ " + (dt.getUTCDay() + 1)}, ${p(d)}/${p(m)}/${y}`;
    return s;
  }
  window.setLang = function (l) {
    document.documentElement.lang = l;
    document.querySelectorAll("[data-ja]").forEach((el) => {
      if (el.dataset.vi === undefined) el.dataset.vi = el.innerHTML;
      if (l === "ja") el.textContent = el.dataset.ja;
      else el.innerHTML = el.dataset.vi;
    });
    document.querySelectorAll("[data-m]").forEach((el) => (el.textContent = fmtMoney(+el.dataset.m, l)));
    document.querySelectorAll("[data-mc]").forEach((el) => (el.textContent = fmtCompact(+el.dataset.mc, l)));
    document
      .querySelectorAll("[data-n]")
      .forEach((el) => (el.textContent = new Intl.NumberFormat(LOC[l]).format(+el.dataset.n)));
    document
      .querySelectorAll("[data-d]")
      .forEach((el) => (el.textContent = fmtDate(el.dataset.d, el.dataset.f || "mdw", l)));
    document
      .querySelectorAll("[data-setlang]")
      .forEach((b) => b.setAttribute("aria-pressed", String(b.dataset.setlang === l)));
    document.querySelectorAll("[data-langopt]").forEach((r) => r.classList.toggle("on", r.dataset.langopt === l));
    try {
      localStorage.setItem("hb-lang", l);
    } catch (e) {}
  };
  document.addEventListener("click", (e) => {
    const b = e.target.closest("[data-setlang]");
    if (b) setLang(b.dataset.setlang);
  });
  window.HB = { fmtMoney, fmtCompact, fmtDate };
})();
