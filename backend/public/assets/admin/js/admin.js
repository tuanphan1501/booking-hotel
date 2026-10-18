/**
 * Web Admin shell behaviour shared by every signed-in page.
 *
 * - Below 1100 px the sidebar becomes a drawer opened by the ☰ button in the top bar.
 * - The drawer closes with Escape, a click on the backdrop, or when the window becomes wide again;
 *   focus returns to the ☰ button for keyboard users.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
(function () {
  "use strict";

  /** Width at which the sidebar is always visible — keep in sync with the 1100 px media query in admin.css. */
  const DESKTOP_QUERY = window.matchMedia("(min-width: 1101px)");

  const toggleButton = document.querySelector("[data-nav-toggle]");
  const scrim = document.querySelector("[data-nav-close]");
  const sidebar = document.getElementById("admin-sidebar");

  if (!toggleButton || !sidebar) return;

  /**
   * Open or close the navigation drawer.
   *
   * @param {boolean} isOpen True to open the drawer.
   * @returns {void}
   */
  function setDrawerOpen(isOpen) {
    document.body.classList.toggle("nav-open", isOpen);
    toggleButton.setAttribute("aria-expanded", String(isOpen));
    toggleButton.setAttribute("aria-label", isOpen ? toggleButton.dataset.labelClose : toggleButton.dataset.labelOpen);
    scrim.hidden = !isOpen;

    if (isOpen) {
      sidebar.querySelector("a[href]")?.focus();
    }
  }

  toggleButton.addEventListener("click", () => setDrawerOpen(!document.body.classList.contains("nav-open")));
  scrim.addEventListener("click", () => setDrawerOpen(false));

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape" && document.body.classList.contains("nav-open")) {
      setDrawerOpen(false);
      toggleButton.focus();
    }
  });

  DESKTOP_QUERY.addEventListener("change", (query) => query.matches && setDrawerOpen(false));
})();
