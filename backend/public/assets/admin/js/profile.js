/**
 * AD-19 Personal settings — progressive enhancement (the page also works without JavaScript).
 *
 * - Picture review & crop: after choosing or dropping a file, a dialog shows it in a round frame;
 *   drag to position, slider to zoom, live previews at the real display sizes. "Save" crops a
 *   512 × 512 image in the browser and uploads it (server validation stays authoritative).
 * - Profile form: show "unsaved changes" and warn before leaving the page with unsaved edits.
 *
 * @author PH.Tuan
 * @created 2026-10-18
 */
(function () {
  "use strict";

  /** Accepted picture types and size — keep in sync with App\Http\Requests\Admin\Profile\UpdateAvatarRequest. */
  const ACCEPTED_TYPES = ["image/jpeg", "image/png", "image/webp"];
  const MAX_FILE_SIZE_BYTES = 2048 * 1024;
  /** Size of the cropped picture sent to the server. */
  const OUTPUT_SIZE_PX = 512;

  /**
   * Encode a canvas as an image file, synchronously.
   *
   * - WebP keeps the upload small; browsers without a WebP encoder fall back to PNG.
   *
   * @param {HTMLCanvasElement} canvas Canvas holding the cropped picture.
   * @param {string} baseName File name without extension.
   * @returns {File}
   */
  function canvasToFile(canvas, baseName) {
    const dataUrl = canvas.toDataURL("image/webp", 0.9);
    const [header, base64Data] = dataUrl.split(",");
    const mimeType = header.slice(5, header.indexOf(";"));
    const binary = atob(base64Data);
    const bytes = new Uint8Array(binary.length);
    for (let index = 0; index < binary.length; index++) bytes[index] = binary.charCodeAt(index);

    return new File([bytes], `${baseName}.${mimeType === "image/webp" ? "webp" : "png"}`, { type: mimeType });
  }

  /**
   * Picture review & crop dialog bound to one upload form.
   */
  class AvatarEditor {
    /**
     * @param {HTMLFormElement} form Picture upload form ([data-avatar-form]).
     */
    constructor(form) {
      this.form = form;
      this.input = form.querySelector("[data-avatar-input]");
      this.dialog = form.querySelector("[data-avatar-dialog]");
      this.canvas = form.querySelector("[data-avatar-canvas]");
      this.zoomInput = form.querySelector("[data-avatar-zoom]");
      this.thumbnails = [...form.querySelectorAll("[data-avatar-thumb]")];
      this.dropzone = form.querySelector("[data-avatar-dropzone]");
      this.hint = document.querySelector("[data-avatar-hint]");
      this.defaultHint = this.hint ? this.hint.innerHTML : "";

      /** @type {HTMLImageElement|null} Image being edited. */
      this.image = null;
      /** Zoom factor (1 = image covers the frame) and offset of the image centre from the frame centre, in canvas px. */
      this.zoom = 1;
      this.offsetX = 0;
      this.offsetY = 0;

      this.bindEvents();
    }

    /**
     * Attach every listener of the form and the dialog.
     *
     * @returns {void}
     */
    bindEvents() {
      this.input.addEventListener("change", () => this.input.files[0] && this.open(this.input.files[0]));

      ["dragenter", "dragover"].forEach((type) =>
        this.dropzone.addEventListener(type, (event) => {
          event.preventDefault();
          this.dropzone.classList.add("is-dragging");
        })
      );
      ["dragleave", "drop"].forEach((type) =>
        this.dropzone.addEventListener(type, () => this.dropzone.classList.remove("is-dragging"))
      );
      this.dropzone.addEventListener("drop", (event) => {
        event.preventDefault();
        if (event.dataTransfer.files[0]) this.open(event.dataTransfer.files[0]);
      });

      this.zoomInput.addEventListener("input", () => {
        this.zoom = Number(this.zoomInput.value);
        this.render();
      });
      this.bindDragging();

      this.form.querySelectorAll("[data-avatar-cancel]").forEach((button) => button.addEventListener("click", () => this.close()));
      this.form.querySelector("[data-avatar-reset]").addEventListener("click", () => this.resetPosition());
      this.form.querySelector("[data-avatar-save]").addEventListener("click", () => this.save());
      this.dialog.addEventListener("cancel", () => this.close());
    }

    /**
     * Pan the image with mouse, pen or touch; arrow keys also move it.
     *
     * @returns {void}
     */
    bindDragging() {
      let lastPoint = null;
      const scale = () => this.canvas.width / this.canvas.getBoundingClientRect().width;

      this.canvas.addEventListener("pointerdown", (event) => {
        lastPoint = { x: event.clientX, y: event.clientY };
        this.canvas.setPointerCapture(event.pointerId);
      });
      this.canvas.addEventListener("pointermove", (event) => {
        if (!lastPoint) return;
        this.offsetX += (event.clientX - lastPoint.x) * scale();
        this.offsetY += (event.clientY - lastPoint.y) * scale();
        lastPoint = { x: event.clientX, y: event.clientY };
        this.render();
      });
      ["pointerup", "pointercancel"].forEach((type) => this.canvas.addEventListener(type, () => (lastPoint = null)));

      this.canvas.tabIndex = 0;
      this.canvas.addEventListener("keydown", (event) => {
        const step = { ArrowLeft: [-10, 0], ArrowRight: [10, 0], ArrowUp: [0, -10], ArrowDown: [0, 10] }[event.key];
        if (!step) return;
        event.preventDefault();
        this.offsetX += step[0];
        this.offsetY += step[1];
        this.render();
      });
    }

    /**
     * Validate the file, load it and open the dialog.
     *
     * @param {File} file Selected or dropped file.
     * @returns {void}
     */
    open(file) {
      if (!ACCEPTED_TYPES.includes(file.type) || file.size > MAX_FILE_SIZE_BYTES) {
        this.input.value = "";
        this.showHint(this.hint?.dataset.invalidText || this.defaultHint, true);
        return;
      }

      const objectUrl = URL.createObjectURL(file);
      const image = new Image();
      image.onload = () => {
        URL.revokeObjectURL(objectUrl);
        this.image = image;
        this.resetPosition();
        this.showHint(file.name, false);
        this.dialog.showModal();
      };
      image.src = objectUrl;
    }

    /**
     * Close the dialog and forget the chosen file.
     *
     * @returns {void}
     */
    close() {
      if (this.dialog.open) this.dialog.close();
      this.input.value = "";
      this.image = null;
      this.showHint(this.defaultHint, false, true);
    }

    /**
     * Center the image and reset the zoom.
     *
     * @returns {void}
     */
    resetPosition() {
      this.zoom = 1;
      this.zoomInput.value = "1";
      this.offsetX = 0;
      this.offsetY = 0;
      this.render();
    }

    /**
     * Get where the image is drawn on a square canvas, keeping the frame always fully covered.
     *
     * @param {number} canvasSize Width/height of the target canvas in px.
     * @returns {{x: number, y: number, width: number, height: number}}
     */
    placement(canvasSize) {
      const ratio = canvasSize / this.canvas.width;
      const coverScale = Math.max(canvasSize / this.image.naturalWidth, canvasSize / this.image.naturalHeight) * this.zoom;
      const width = this.image.naturalWidth * coverScale;
      const height = this.image.naturalHeight * coverScale;

      // Clamp the offset so no empty area appears inside the frame.
      const maxOffsetX = (width - canvasSize) / 2 / ratio;
      const maxOffsetY = (height - canvasSize) / 2 / ratio;
      this.offsetX = Math.min(maxOffsetX, Math.max(-maxOffsetX, this.offsetX));
      this.offsetY = Math.min(maxOffsetY, Math.max(-maxOffsetY, this.offsetY));

      return {
        x: (canvasSize - width) / 2 + this.offsetX * ratio,
        y: (canvasSize - height) / 2 + this.offsetY * ratio,
        width,
        height,
      };
    }

    /**
     * Draw the editor canvas and every live preview.
     *
     * @returns {void}
     */
    render() {
      if (!this.image) return;
      [this.canvas, ...this.thumbnails].forEach((target) => this.drawTo(target));
    }

    /**
     * Draw the current crop onto a canvas.
     *
     * @param {HTMLCanvasElement} target Canvas to draw on.
     * @returns {void}
     */
    drawTo(target) {
      const context = target.getContext("2d");
      const { x, y, width, height } = this.placement(target.width);
      context.clearRect(0, 0, target.width, target.height);
      context.imageSmoothingQuality = "high";
      context.drawImage(this.image, x, y, width, height);
    }

    /**
     * Crop to OUTPUT_SIZE_PX, put the result into the file input and submit the form.
     *
     * @returns {void}
     */
    save() {
      if (!this.image) return;

      const output = document.createElement("canvas");
      output.width = OUTPUT_SIZE_PX;
      output.height = OUTPUT_SIZE_PX;
      this.drawTo(output);

      const transfer = new DataTransfer();
      transfer.items.add(canvasToFile(output, "avatar"));
      this.input.files = transfer.files;
      this.form.querySelector("[data-avatar-save]").disabled = true;
      this.form.submit();
    }

    /**
     * Update the hint line under the profile header.
     *
     * @param {string} content Text (or the original HTML when restoring).
     * @param {boolean} isError Show the hint as an error.
     * @param {boolean} [asHtml=false] Treat content as HTML (only for the server-rendered default hint).
     * @returns {void}
     */
    showHint(content, isError, asHtml = false) {
      if (!this.hint) return;
      this.hint.classList.toggle("err", isError);
      if (asHtml) this.hint.innerHTML = content;
      else this.hint.textContent = content;
    }
  }

  /**
   * Track unsaved edits of a form and warn before navigating away.
   *
   * @param {HTMLFormElement} form Form with [data-dirty-guard].
   * @returns {void}
   */
  function initDirtyGuard(form) {
    const label = form.querySelector("[data-dirty-label]");
    const initialState = new URLSearchParams(new FormData(form)).toString();
    let isSubmitting = false;

    const isDirty = () => new URLSearchParams(new FormData(form)).toString() !== initialState;

    form.addEventListener("input", () => {
      if (label) label.hidden = !isDirty();
    });
    form.addEventListener("submit", () => {
      isSubmitting = true;
    });
    window.addEventListener("beforeunload", (event) => {
      if (!isSubmitting && isDirty()) event.preventDefault();
    });
  }

  document.querySelectorAll("[data-avatar-form]").forEach((form) => new AvatarEditor(form));
  document.querySelectorAll("[data-dirty-guard]").forEach(initDirtyGuard);
})();
