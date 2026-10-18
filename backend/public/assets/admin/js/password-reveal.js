/**
 * Show / hide password buttons rendered by <x-admin.field revealable>.
 *
 * - The button toggles the input between "password" and "text" and updates aria-pressed / aria-label.
 *
 * @author PH.Tuan
 * @created 2026-10-19
 */
(function () {
  "use strict";

  /**
   * Toggle the password visibility of the input controlled by a button.
   *
   * @param {HTMLButtonElement} button Button with aria-controls pointing at the input.
   * @returns {void}
   */
  function togglePassword(button) {
    const input = document.getElementById(button.getAttribute("aria-controls"));
    if (!input) return;

    const isVisible = input.type === "password";
    input.type = isVisible ? "text" : "password";
    button.setAttribute("aria-pressed", String(isVisible));
    button.setAttribute("aria-label", isVisible ? button.dataset.labelHide : button.dataset.labelShow);
  }

  document.querySelectorAll("[data-password-toggle]").forEach((button) => {
    button.addEventListener("click", () => togglePassword(button));
  });
})();
