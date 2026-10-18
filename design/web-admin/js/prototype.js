// Prototype click-through: gắn liên kết cho phần tử trong màn theo window.PROTO_LINKS (js/links.js).
// Phải nạp TRƯỚC app.js (lúc này chữ trong mockup vẫn là tiếng Việt để so khớp).
(function () {
  const id = document.body.dataset.screen;
  const rules = (window.PROTO_LINKS || {})[id] || [];
  const root = document.querySelector(".adm") || document.body;
  const CLICK = "button,a,.btn,.list-row,.card,.chip,.ib,.inp,.toast,td.link,.avatar,.logo";
  const txt = (el) => el.textContent.replace(/\s+/g, " ");

  function find(r) {
    let c;
    if (r.i) c = [...root.querySelectorAll(`use[href="#${r.i}"]`)].map((u) => u.closest("svg"));
    else if (r.s) {
      c = [...root.querySelectorAll(r.s)];
      if (r.t) c = c.filter((e) => txt(e).includes(r.t));
    } else
      c = [...root.querySelectorAll("*")].filter(
        (e) => txt(e).includes(r.t) && ![...e.children].some((ch) => txt(ch).includes(r.t))
      );
    if (!r.all) {
      const n = (r.n || 1) - 1;
      c = c.slice(n, n + 1);
    }
    return c.map((e) => e.closest(CLICK) || e);
  }
  rules.forEach((r) => {
    const els = find(r);
    if (!els.length) console.warn("[proto] không tìm thấy", id, JSON.stringify(r));
    els.forEach((el) => {
      if (el.dataset.go || el.dataset.toast) return;
      if (r.go) el.dataset.go = r.go;
      else el.dataset.toast = r.toast;
      if (r.title) el.title = r.title;
      el.setAttribute("role", "link");
      el.tabIndex = 0;
    });
  });

  function href(go) {
    const [p, q] = go.split("?");
    return p + ".html" + (q ? "?" + q.replace(/^toast=(.*)$/, (m, v) => "toast=" + encodeURIComponent(v)) : "");
  }
  let tm;
  function toast(msg) {
    let t = document.querySelector(".proto-toast");
    if (!t) {
      t = document.createElement("div");
      t.className = "proto-toast";
      t.setAttribute("role", "status");
      document.body.appendChild(t);
    }
    t.textContent = msg;
    t.classList.add("on");
    clearTimeout(tm);
    tm = setTimeout(() => t.classList.remove("on"), 2600);
  }
  function hint() {
    document.body.classList.remove("proto-hint");
    void document.body.offsetWidth;
    document.body.classList.add("proto-hint");
    setTimeout(() => document.body.classList.remove("proto-hint"), 1100);
  }

  function act(el) {
    if (el.dataset.toast) toast(el.dataset.toast);
    else location.href = href(el.dataset.go);
  }
  document.addEventListener("click", (e) => {
    if (e.target.closest("[data-setlang],.proto-bar")) return;
    const el = e.target.closest("[data-go],[data-toast]");
    if (el) {
      e.preventDefault();
      act(el);
      return;
    }
    if (e.target.closest("button,.btn,a,.inp,.sel")) toast("Prototype: thao tác này chưa gắn màn hình");
    else hint();
  });
  document.addEventListener("keydown", (e) => {
    if (e.key === "Enter" && e.target.dataset && (e.target.dataset.go || e.target.dataset.toast)) {
      act(e.target);
      return;
    }
    if (/INPUT|SELECT|TEXTAREA/.test(e.target.tagName)) return;
    const k = { ArrowLeft: "prev", ArrowRight: "next" }[e.key];
    const a = k && document.querySelector(`[data-nav="${k}"]`);
    if (a && a.href) location.href = a.href;
  });
  const jump = document.querySelector(".proto-jump");
  if (jump)
    jump.addEventListener("change", () => {
      location.href = jump.value + ".html";
    });
  const q = new URLSearchParams(location.search).get("toast");
  if (q) setTimeout(() => toast(q), 250);
})();
