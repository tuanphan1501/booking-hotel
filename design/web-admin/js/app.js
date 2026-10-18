// Khởi tạo trang: ngôn ngữ (lưu localStorage, ?lang=ja) và chú thích số (?ann=1).
(function () {
  const ann = document.getElementById("annToggle");
  if (ann)
    ann.addEventListener("change", () => {
      document.body.classList.toggle("ann-on", ann.checked);
      try {
        localStorage.setItem("hb-ann", ann.checked ? "1" : "0");
      } catch (e) {}
    });
  let l = "vi",
    a = false;
  try {
    l = localStorage.getItem("hb-lang") || "vi";
    a = localStorage.getItem("hb-ann") === "1";
  } catch (e) {}
  const q = new URLSearchParams(location.search);
  if (q.get("lang")) l = q.get("lang");
  if (q.get("ann")) a = q.get("ann") === "1";
  if (ann) {
    ann.checked = a;
    document.body.classList.toggle("ann-on", a);
  }
  setLang(l);
})();
