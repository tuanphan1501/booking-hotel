// Sinh bộ thiết kế từ HotelBooking_Mockup.html:
//   web-admin/  prototype HTML/CSS/JS, mỗi màn AD-xx một page, nút bấm chuyển trang (tools/proto-links.js)
//   mobile/     KHÔNG phải HTML: ảnh PNG từng màn + đặc tả Markdown + icon/minh họa SVG cho dev Flutter
//   docs/       review UI v1 → v2
// Chạy từ thư mục gốc dự án:  node design/tools/build.js        (thêm --no-png để bỏ qua xuất ảnh)
// Xuất PNG dùng Chrome/Edge headless (biến môi trường CHROME để chỉ định đường dẫn).
const fs = require("fs");
const path = require("path");
const os = require("os");
const { execFileSync } = require("child_process");

const ROOT = path.resolve(__dirname, "..", "..");
const OUT = path.resolve(__dirname, "..");
const SRC = path.join(ROOT, "HotelBooking_Mockup.html");
const specs = require("./mobile-specs.js");
const links = require("./proto-links.js");
const NO_PNG = process.argv.includes("--no-png");

const lines = fs.readFileSync(SRC, "utf8").split(/\r?\n/);
const L = (a, b) => lines.slice(a - 1, b).join("\n"); // dòng a..b của file gốc (1-based)
const write = (rel, s) => {
  const p = path.join(OUT, rel);
  fs.mkdirSync(path.dirname(p), { recursive: true });
  fs.writeFileSync(p, typeof s === "string" ? s.replace(/\n?$/, "\n") : s);
};
const esc = (s) => String(s).replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;");
const plain = (h) =>
  h
    .replace(/<[^>]+>/g, "")
    .replace(/&amp;/g, "&")
    .trim();
function balanced(html, start) {
  // đoạn <div ...>...</div> cân bằng bắt đầu tại start
  const re = /<(\/?)div\b[^>]*>/g;
  re.lastIndex = start;
  let d = 0,
    m;
  while ((m = re.exec(html))) {
    d += m[1] ? -1 : 1;
    if (d === 0) return html.slice(start, re.lastIndex);
  }
  throw new Error("unbalanced at " + start);
}
const banner = (t) => `/* ${t} — sinh từ HotelBooking_Mockup.html bởi design/tools/build.js, không sửa tay */\n`;
const sprite = L(276, 374);
const FONTS = L(4, 5);

// =====================================================================
// 1. WEB ADMIN
// =====================================================================
const WA = "web-admin/";
write(WA + "css/tokens.css", banner("Design tokens & reset") + L(7, 23) + "\n" + L(274, 274));
write(WA + "css/utilities.css", banner("Thang chữ & tiện ích bố cục") + L(79, 95));
write(WA + "css/components.css", banner("Component dùng chung") + L(97, 178));
write(
  WA + "css/admin.css",
  banner("Bố cục & component Web Admin") +
    L(180, 182) +
    "\n" +
    L(187, 259) +
    "\n" +
    L(272, 272).replace(".browser{overflow-x:auto}", "")
);
write(
  WA + "assets/icons.svg",
  sprite.replace(
    '<svg width="0" height="0" style="position:absolute" aria-hidden="true">',
    '<svg xmlns="http://www.w3.org/2000/svg">'
  )
);
write(
  WA + "js/icons.js",
  '// SVG sprite (icon nét 1,75px + minh họa). Chèn vào đầu <body> để dùng <use href="#i-...">. Bản file: assets/icons.svg\n' +
    banner("").trim() +
    "\n" +
    `document.body.insertAdjacentHTML("afterbegin", ${JSON.stringify(sprite)});`
);
write(
  WA + "js/i18n.js",
  `// Đa ngôn ngữ VI / JA: phần tử có data-ja đổi nội dung; data-m (tiền), data-mc (tiền rút gọn), data-n (số), data-d + data-f (ngày).
${banner("").trim()}
(function(){
${L(388, 395)}
  window.setLang=function(l){
${L(397, 406)}
  };
  document.addEventListener("click",e=>{const b=e.target.closest("[data-setlang]");if(b)setLang(b.dataset.setlang);});
  window.HB={fmtMoney,fmtCompact,fmtDate};
})();`
);
write(
  WA + "js/app.js",
  `// Khởi tạo trang: ngôn ngữ (lưu localStorage, ?lang=ja) và chú thích số (?ann=1).
(function(){
  const ann=document.getElementById("annToggle");
  if(ann)ann.addEventListener("change",()=>{document.body.classList.toggle("ann-on",ann.checked);try{localStorage.setItem("hb-ann",ann.checked?"1":"0")}catch(e){}});
  let l="vi",a=false;try{l=localStorage.getItem("hb-lang")||"vi";a=localStorage.getItem("hb-ann")==="1"}catch(e){}
  const q=new URLSearchParams(location.search);if(q.get("lang"))l=q.get("lang");if(q.get("ann"))a=q.get("ann")==="1";
  if(ann){ann.checked=a;document.body.classList.toggle("ann-on",a);}
  setLang(l);
})();`
);
write(
  WA + "js/links.js",
  "// Bảng liên kết giữa các màn (nguồn: design/tools/proto-links.js)\n" +
    `window.PROTO_LINKS=${JSON.stringify(links, null, 1)};`
);

const ad = lines[382];
const adScreens = [];
{
  const re = /<div class="ad-shot" id="([^"]+)">/g;
  let m;
  while ((m = re.exec(ad))) {
    const shot = balanced(ad, m.index);
    const cap = balanced(shot, shot.indexOf('<div class="cap"'));
    const adm = balanced(shot, shot.indexOf('<div class="adm'));
    const url = (shot.match(/<span class="url">([^<]+)<\/span>/) || [])[1] || "";
    const name = plain((cap.match(/<b>[^<]+<\/b><span>(<span[^>]*>[^<]*<\/span>)/) || [])[1] || "");
    const note = plain((cap.match(/<em>(.*?)<\/em>/) || [])[1] || "");
    adScreens.push({ id: m[1], adm, url, name, note });
  }
}
// Ngôn ngữ là cài đặt cá nhân (AD-19): bỏ nút VI/JA khỏi thanh trên, chỉ giữ ở màn đăng nhập AD-01
for (const s of adScreens) {
  const i = s.adm.indexOf('<div class="ab-lang">');
  if (s.id !== "AD-01" && i >= 0) s.adm = s.adm.replace(balanced(s.adm, i), "");
}
adScreens.push(buildPersonalSettings(adScreens.find((s) => s.id === "AD-02").adm));

// AD-19 Cài đặt cá nhân — dựng từ khung AD-02 (sidebar + thanh trên), thay phần nội dung
function buildPersonalSettings(base) {
  const ic = (n) => `<svg class="i" aria-hidden="true"><use href="#i-${n}"/></svg>`;
  const vj = (vi, ja) => `<span data-ja="${ja}">${vi}</span>`;
  const sw = (on) => `<span class="sw${on ? " on" : ""}"></span>`;
  const row = (icon, label, right) =>
    `<div class="list-row"><span class="ic">${ic(icon)}</span><span class="grow">${label}</span>${right}</div>`;
  const page = `<div class="apage">
<div class="ph-row"><h1>${vj("Cài đặt cá nhân", "個人設定")}</h1></div>
<div class="g2e">
<div class="panel"><h3>${ic("globe")}<b class="ann">1</b>${vj("Ngôn ngữ", "言語")}</h3>
<p class="t-s sub" style="margin-bottom:8px">${vj("Áp dụng cho giao diện quản trị và email thông báo gửi cho bạn.", "管理画面とあなた宛の通知メールに適用されます。")}</p>
<div class="list-row" data-setlang="vi" style="cursor:pointer"><span class="ic"><b>VI</b></span><span class="grow">Tiếng Việt<div class="t-xs sub">Vietnamese</div></span><span class="rd on" data-langopt="vi"></span></div>
<div class="list-row" data-setlang="ja" style="cursor:pointer"><span class="ic"><b>JA</b></span><span class="grow">日本語<div class="t-xs sub">Japanese</div></span><span class="rd" data-langopt="ja"></span></div>
<div class="divider" style="margin:10px 0"></div>
<div class="col g4"><b class="ann">2</b><div class="t-s w6">${vj("Xem trước định dạng", "表示プレビュー")}</div>
<div class="kv"><span class="k">${vj("Ngày", "日付")}</span><span class="v" data-d="2026-11-20" data-f="mdw"></span></div>
<div class="kv"><span class="k">${vj("Số tiền", "金額")}</span><span class="v" data-m="3415400"></span></div></div></div>
<div class="panel"><h3>${ic("user")}${vj("Hồ sơ", "プロフィール")}</h3>
<div class="col g12">
<div class="kv"><span class="k">${vj("Họ tên", "氏名")}</span><span class="v">Lê Hạnh</span></div>
<div class="kv"><span class="k">Email</span><span class="v">le.hanh@lumiere-hotels.vn</span></div>
<div class="kv"><span class="k">${vj("Vai trò", "ロール")}</span><span class="v">${vj("Quản lý chi nhánh", "支店マネージャー")}</span></div>
<div class="kv"><span class="k">${vj("Chi nhánh", "支店")}</span><span class="v">${vj("Lumière Đà Nẵng", "ルミエール ダナン")}</span></div></div></div>
<div class="panel"><h3>${ic("shield")}<b class="ann">3</b>${vj("Bảo mật", "セキュリティ")}</h3>
${row("lock", vj("Mật khẩu", "パスワード"), `<button class="btn sm sec">${vj("Đổi mật khẩu", "パスワード変更")}</button>`)}
${row("phone", vj("Xác thực 2 bước (TOTP)", "2段階認証（TOTP）"), `<span class="chip brand">${vj("Đang bật", "有効")}</span>`)}
${row("clock", vj("Phiên đăng nhập khác", "他のセッション"), `<button class="btn sm sec">${vj("Đăng xuất tất cả", "すべてログアウト")}</button>`)}</div>
<div class="panel"><h3>${ic("bell")}<b class="ann">4</b>${vj("Thông báo cho tôi", "自分宛の通知")}</h3>
${row("ticket", vj("Đặt phòng mới", "新規予約"), sw(true))}
${row("close", vj("Đặt phòng bị hủy", "予約キャンセル"), sw(true))}
${row("money", vj("Hoàn tiền chờ duyệt", "承認待ちの返金"), sw(false))}</div>
</div>
<div class="row"><button class="btn sm danger">${ic("logout")}<b class="ann">5</b>${vj("Đăng xuất", "ログアウト")}</button></div>
</div>`;
  const side = base.replace(/<a class="on">/, '<a class="">');
  const crumbStart = side.indexOf('<div class="crumb">');
  const withCrumb = side.replace(
    balanced(side, crumbStart),
    `<div class="crumb">${vj("Tài khoản", "アカウント")} / <b>${vj("Cài đặt cá nhân", "個人設定")}</b></div>`
  );
  const pageStart = withCrumb.indexOf('<div class="apage">');
  return {
    id: "AD-19",
    adm: withCrumb.replace(balanced(withCrumb, pageStart), page),
    url: "admin.lumiere-hotels.vn/me/settings",
    name: "Cài đặt cá nhân",
    note: "Mở từ avatar. Ngôn ngữ đổi ngay, lưu vào admin_users.language; email thông báo dùng cùng ngôn ngữ.",
  };
}
// nhóm theo sidebar
const adGroups = [
  ["Bắt đầu", ["AD-01", "AD-02"]],
  ["Vận hành", ["AD-03", "AD-04", "AD-05", "AD-08"]],
  ["Sản phẩm", ["AD-06", "AD-07", "AD-09", "AD-10"]],
  ["Khách hàng", ["AD-11", "AD-12", "AD-13"]],
  ["Tài chính", ["AD-14", "AD-15"]],
  ["Hệ thống", ["AD-16", "AD-17", "AD-18"]],
  ["Tài khoản", ["AD-19"]],
];
const adById = Object.fromEntries(adScreens.map((s) => [s.id, s]));
const head = (title, rel, desc) => `<!doctype html>
<html lang="vi"><head><meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
<title>${esc(title)}</title>
<meta name="description" content="${esc(desc)}">
${FONTS}
<link rel="stylesheet" href="${rel}css/tokens.css">
<link rel="stylesheet" href="${rel}css/utilities.css">
<link rel="stylesheet" href="${rel}css/components.css">
<link rel="stylesheet" href="${rel}css/admin.css">
<link rel="stylesheet" href="${rel}css/prototype.css">
</head>`;
const jumpOptions = (cur) =>
  adGroups
    .map(
      ([g, ids]) =>
        `<optgroup label="${g}">${ids.map((i) => `<option value="${i}"${i === cur ? " selected" : ""}>${i} · ${esc(adById[i].name)}</option>`).join("")}</optgroup>`
    )
    .join("");

adScreens.forEach((s, i) => {
  const prev = adScreens[i - 1],
    next = adScreens[i + 1];
  write(
    `${WA}pages/${s.id}.html`,
    `${head(`${s.id} ${s.name} — Lumière Admin`, "../", s.note)}
<body class="proto" data-screen="${s.id}">
<script src="../js/icons.js"></script>
${s.adm}
<div class="proto-bar" aria-label="Prototype">
<a href="../index.html" title="Danh sách màn hình">☰</a>
<a data-nav="prev" ${prev ? `href="${prev.id}.html"` : 'aria-disabled="true"'} title="Màn trước (←)">‹</a>
<select class="proto-jump" aria-label="Chuyển màn">${jumpOptions(s.id)}</select>
<a data-nav="next" ${next ? `href="${next.id}.html"` : 'aria-disabled="true"'} title="Màn sau (→)">›</a>
<label title="Hiện số chú thích"><input type="checkbox" id="annToggle">Chú thích</label>
</div>
<script src="../js/i18n.js"></script>
<script src="../js/links.js"></script>
<script src="../js/prototype.js"></script>
<script src="../js/app.js"></script>
</body></html>`
  );
});

write(
  WA + "index.html",
  `${head("Lumière Admin — Prototype", "", "Danh sách màn hình prototype Web Admin")}
<body>
<script src="js/icons.js"></script>
<main class="dh">
<div class="dh-head"><span class="brand-mark"><svg class="i" aria-hidden="true"><use href="#i-door"/></svg></span><div><h1>Lumière Admin — Prototype</h1></div></div>
<p class="lead">Prototype bấm được của Web Admin, ${adScreens.length} màn hình. Bấm nút / menu trong màn để chuyển trang; bấm vùng trống để hiện các vùng bấm được; phím <kbd>←</kbd> <kbd>→</kbd> chuyển màn kế tiếp. Đổi ngôn ngữ VI / JA ở thanh trên của mỗi màn.</p>
<a class="btn dh-start" href="pages/AD-01.html">Bắt đầu demo từ màn đăng nhập ›</a>
<div class="dh-path"><h2>Kịch bản demo gợi ý</h2>
<div><b>Lễ tân nhận khách</b><a href="pages/AD-01.html">Đăng nhập</a>→<a href="pages/AD-02.html">Tổng quan</a>→<a href="pages/AD-03.html">Danh sách đặt phòng</a>→<a href="pages/AD-04.html">Chi tiết · Nhận phòng</a>→<a href="pages/AD-08.html">Tình trạng buồng</a></div>
<div><b>Đặt phòng tại quầy</b><a href="pages/AD-03.html">Danh sách</a>→<a href="pages/AD-05.html">Đặt tại quầy</a>→<a href="pages/AD-04.html">Chi tiết đặt phòng</a></div>
<div><b>Quản lý giá & sản phẩm</b><a href="pages/AD-06.html">Giá & tồn kho</a>→<a href="pages/AD-07.html">Loại phòng</a>→<a href="pages/AD-09.html">Gói giá & hủy</a>→<a href="pages/AD-10.html">Dịch vụ thêm</a></div>
<div><b>Marketing & CSKH</b><a href="pages/AD-11.html">Khuyến mãi</a>→<a href="pages/AD-12.html">Đánh giá</a>→<a href="pages/AD-13.html">Khách & điểm</a></div>
<div><b>Kế toán</b><a href="pages/AD-14.html">Thanh toán & hoàn tiền</a>→<a href="pages/AD-15.html">Báo cáo</a></div>
</div>
<div class="dh-grid">${adGroups.map(([g, ids]) => `<section class="dh-grp"><h3>${g}</h3>${ids.map((i) => `<a href="pages/${i}.html"><b>${i}</b>${esc(adById[i].name)}<em>${esc(adById[i].url.replace("admin.lumiere-hotels.vn", ""))}</em></a>`).join("")}</section>`).join("")}</div>
</main>
</body></html>`
);

// =====================================================================
// 2. MOBILE — ảnh + đặc tả, không có HTML
// =====================================================================
const MO = "mobile/";
const mb = lines[381];
const flows = [];
mb.replace(/<section class="flow"><div class="flow-h"><h2>(.*?)<\/h2><p>(.*?)<\/p><\/div>/g, (m, h, p, idx) => {
  flows.push({ h: plain(h), p: plain(p), idx, ids: [] });
  return m;
});
const screens = [];
{
  const re = /<div class="shot" id="([^"]+)">/g;
  let m;
  while ((m = re.exec(mb))) {
    const shot = balanced(mb, m.index);
    const phone = balanced(shot, shot.indexOf('<div class="phone">'));
    const cap = balanced(shot, shot.indexOf('<div class="cap">'));
    const flow = flows.filter((f) => f.idx < m.index).pop();
    flow.ids.push(m[1]);
    screens.push({ id: m[1], phone, note: plain((cap.match(/<em>(.*?)<\/em>/) || [])[1] || ""), flow });
  }
}
const missing = screens.filter((s) => !specs[s.id]).map((s) => s.id);
if (missing.length) throw new Error("Thiếu spec cho: " + missing.join(", "));

// 2a. icon + minh họa SVG rời (flutter_svg)
for (const m of sprite.matchAll(/<symbol id="i-([a-z]+)" viewBox="([^"]+)"([^>]*)>([\s\S]*?)<\/symbol>/g))
  write(
    `${MO}assets/icons/${m[1]}.svg`,
    `<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="${m[2]}"${m[3]}>${m[4]}</svg>`
  );
const grads = (sprite.match(/<linearGradient[\s\S]*?<\/linearGradient>/g) || []).join("");
for (const m of sprite.matchAll(/<symbol id="sc-([a-z]+)" viewBox="([^"]+)">([\s\S]*?)<\/symbol>/g))
  write(
    `${MO}assets/illustrations/${m[1]}.svg`,
    `<svg xmlns="http://www.w3.org/2000/svg" viewBox="${m[2]}"><defs>${grads}</defs>${m[3]}</svg>`
  );

// 2b. ảnh PNG từng màn (sạch + có số chú thích), render bằng Chrome headless
function findChrome() {
  const c = [
    process.env.CHROME,
    "C:/Program Files/Google/Chrome/Application/chrome.exe",
    "C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe",
    "/Applications/Google Chrome.app/Contents/MacOS/Google Chrome",
    "/usr/bin/google-chrome",
    "/usr/bin/chromium",
  ].filter(Boolean);
  return c.find((p) => fs.existsSync(p));
}
if (!NO_PNG) {
  const chrome = findChrome();
  if (!chrome) console.warn("Không tìm thấy Chrome/Edge — bỏ qua xuất PNG (đặt biến CHROME)");
  else {
    const tmp = fs.mkdtempSync(path.join(os.tmpdir(), "hb-render-"));
    const css = L(7, 274);
    for (const s of screens)
      for (const ann of [false, true]) {
        const f = path.join(tmp, `${s.id}${ann ? "-ann" : ""}.html`);
        fs.writeFileSync(
          f,
          `<!doctype html><html lang="vi"><head><meta charset="utf-8">${FONTS}<style>${css}
html,body{background:#fff}body{padding:12px}.phone{box-shadow:none;transform:none!important;margin:0!important}</style></head><body class="${ann ? "ann-on" : ""}">${sprite}${s.phone}
<script>(function(){${L(388, 395)}
window.setLang=function(l){${L(397, 406)}};setLang("vi");})();</script></body></html>`
        );
        const out = path.join(OUT, MO, "screens", `${s.id}${ann ? "_annotated" : ""}.png`);
        fs.mkdirSync(path.dirname(out), { recursive: true });
        execFileSync(
          chrome,
          [
            "--headless=new",
            "--disable-gpu",
            "--hide-scrollbars",
            "--force-device-scale-factor=2",
            "--default-background-color=00000000",
            "--virtual-time-budget=4000",
            "--window-size=396,816",
            `--screenshot=${out}`,
            "file:///" + f.replace(/\\/g, "/"),
          ],
          { stdio: "ignore" }
        );
      }
    fs.rmSync(tmp, { recursive: true, force: true });
    console.log(`PNG: ${screens.length * 2} ảnh → ${MO}screens/`);
  }
}

// 2c. đặc tả Markdown từng màn
const mdRow = (r) => "| " + r.map((c) => String(c).replace(/\|/g, "\\|").replace(/\n/g, " ")).join(" | ") + " |";
const mdTable = (h, rows) =>
  rows && rows.length ? [mdRow(h), mdRow(h.map(() => "---")), ...rows.map(mdRow)].join("\n") : "_(không có)_";
const linkIds = (s, rel) => String(s).replace(/\b(MB-\d+[a-z]?)\b/g, (m) => (specs[m] ? `[${m}](${rel}${m}.md)` : m));
screens.forEach((s, i) => {
  const sp = specs[s.id],
    prev = screens[i - 1],
    next = screens[i + 1];
  const nav = `[‹ ${prev ? prev.id : "—"}](${prev ? prev.id + ".md" : "../README.md"}) · [Danh sách](../README.md) · [${next ? next.id : "—"} ›](${next ? next.id + ".md" : "../README.md"})`;
  write(
    `${MO}specs/${s.id}.md`,
    `# ${s.id} · ${sp.name}

${nav}

**Luồng:** ${s.flow.h} · **Route:** \`${sp.route}\` · **Quyền:** ${sp.auth}

${sp.purpose}

> Ghi chú thiết kế: ${s.note}

| Thiết kế | Có số chú thích |
| :---: | :---: |
| <img src="../screens/${s.id}.png" width="300" alt="${s.id}"> | <img src="../screens/${s.id}_annotated.png" width="300" alt="${s.id} annotated"> |

## 1. Bố cục

${sp.layout.map((x) => "- " + linkIds(x, "")).join("\n")}

## 2. Hạng mục màn hình (画面項目定義)

Cột **No** trùng với số hồng trên ảnh có chú thích.

${mdTable(
  ["No", "Hạng mục", "Loại UI", "Dữ liệu / nguồn", "Ràng buộc / validate", "Hành động"],
  sp.items.map((r) => r.map((c) => linkIds(c, "")))
)}

## 3. API

${mdTable(
  ["Method", "Endpoint", "Ghi chú"],
  sp.api.map(([a, b, c]) => [a, "`" + b + "`", c])
)}

## 4. Trạng thái màn hình

${mdTable(
  ["Trạng thái", "Hiển thị / xử lý"],
  sp.states.map((r) => r.map((c) => linkIds(c, "")))
)}

## 5. Quy tắc nghiệp vụ

${sp.rules.map((x) => "- " + linkIds(x, "")).join("\n")}

## 6. Điều hướng

${mdTable(
  ["Thao tác", "Màn đích"],
  sp.nav.map(([a, b]) => [a, linkIds(b, "")])
)}

## 7. i18n key gợi ý

${sp.i18n.map((k) => "`" + k + "`").join(" · ")}

## 8. Gợi ý triển khai Flutter

${sp.flutter.map((x) => "- " + x).join("\n")}

---

${nav}
`
  );
});

// 2d. README mobile (danh mục + nền tảng chung)
const tpl = fs.readFileSync(path.join(__dirname, "mobile-readme.md"), "utf8");
const index = flows
  .map(
    (f) =>
      `### ${f.h}\n\n_${f.p}_\n\n| Màn | Tên | Route | Ảnh |\n| --- | --- | --- | :---: |\n` +
      f.ids
        .map(
          (id) =>
            `| [${id}](specs/${id}.md) | ${specs[id].name} | \`${specs[id].route}\` | <img src="screens/${id}.png" width="90" alt="${id}"> |`
        )
        .join("\n")
  )
  .join("\n\n");
write(MO + "README.md", tpl.replace("{{SCREENS}}", index).replace("{{COUNT}}", String(screens.length)));

// =====================================================================
// 3. DOCS — review UI v1 → v2
// =====================================================================
{
  const rv = lines[383];
  const rows = [...rv.matchAll(/<tr>(<td>[\s\S]*?)<\/tr>/g)].map((m) =>
    [...m[1].matchAll(/<td[^>]*>([\s\S]*?)<\/td>/g)].map((c) =>
      plain(c[1].replace(/<span data-ja="[^"]*">/g, "<span>"))
    )
  );
  const issues = rows.filter((r) => r.length === 5);
  const toks = [...rv.matchAll(/<div class="tok">[\s\S]*?<b>([^<]+)<\/b>\s*([^<]*)<[\s\S]*?<\/div><\/div>/g)];
  let doc = `# Review UI v1 → v2 & design system\n\n> Trích từ tab "Review & design system" của HotelBooking_Mockup.html. Áp dụng chung cho Mobile và Web Admin.\n\n## ${issues.length} vấn đề đã sửa\n\n`;
  doc += mdTable(["#", "Mức", "Vị trí", "Vấn đề ở v1", "Cách sửa ở v2"], issues);
  const proseStart = rv.indexOf('<div class="prose">');
  if (proseStart > 0) {
    const prose = balanced(rv, proseStart).replace(/<span data-ja="[^"]*">/g, "<span>");
    doc +=
      "\n\n## Quy ước\n\n" +
      [...prose.matchAll(/<h3>([\s\S]*?)<\/h3>\s*<p>([\s\S]*?)<\/p>/g)]
        .map((m) => `### ${plain(m[1])}\n\n${plain(m[2])}`)
        .join("\n\n");
  }
  write("docs/ui-review.md", doc + "\n\nMàu chi tiết: xem `mobile/tokens/design-tokens.json`.\n");
}

console.log(`OK: web-admin ${adScreens.length} page · mobile ${screens.length} màn (${flows.length} luồng)`);
