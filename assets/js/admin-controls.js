/**
 * Forge Fields field-control behavior.
 *
 * This file contains JavaScript required by rendered Forge Fields
 * on WordPress Page/Post editing screens.
 *
 * Builder and Forge Fields admin-page behavior belongs in
 * admin-fields.js.
 */

/**
 * Initializes all password-field visibility controls.
 *
 * Each password wrapper contains:
 * - A toggle button.
 * - A password input.
 * - A Dashicons visibility icon.
 *
 * The control keeps the input type, icon, and accessible label synchronized.
 */
document.querySelectorAll(".ff-password-wrap").forEach(function (wrap) {
  const btn = wrap.querySelector(".ff-password-toggle");
  const input = wrap.querySelector(".ff-password-input");
  const icon = btn ? btn.querySelector(".dashicons") : null;

  if (!btn || !input || !icon) return;

  function sync() {
    const isVisible = input.type === "text";

    btn.setAttribute(
      "aria-label",
      isVisible ? "Hide password" : "Show password",
    );

    icon.classList.toggle("dashicons-visibility", isVisible);
    icon.classList.toggle("dashicons-hidden", !isVisible);
  }

  sync();

  btn.addEventListener("click", function () {
    input.type = input.type === "text" ? "password" : "text";
    sync();
  });
});

/**
 * Initializes delegated jQuery synchronization for Forge Fields
 * range sliders and their associated numeric inputs.
 *
 * data-target attributes are used to locate the paired control.
 */
jQuery(function ($) {
  /**
   * Stop immediately when the current screen contains
   * no Forge Fields Range controls.
   */
  if (!document.querySelector(".ff-range-wrap")) {
    return;
  }
  /**
   * Synchronize a range slider with its linked numeric input.
   *
   * Whenever the slider receives an input or change event, its current
   * value is copied into the element referenced by data-target.
   */
  $(document).on("input change", ".ff-range-slider", function () {
    var $num = $($(this).data("target"));

    if ($num.length) $num.val(this.value);
  });

  /**
   * Synchronize a numeric range input with its linked slider.
   *
   * The entered value is constrained to the slider's configured
   * minimum and maximum before both controls are updated.
   */
  $(document).on("input change", ".ff-range-number", function () {
    var $rng = $($(this).data("target"));

    if ($rng.length) {
      /**
       * Read the linked slider's minimum and maximum values.
       *
       * Defaults are used when those attributes cannot be parsed.
       */
      var min = parseFloat($rng.attr("min")) || 0;
      var max = parseFloat($rng.attr("max")) || 100;

      /**
       * Parse the numeric value entered by the user.
       */
      var val = parseFloat(this.value);

      /**
       * Invalid numeric input falls back to the slider's minimum value.
       */
      if (isNaN(val)) val = min;

      /**
       * Clamp the value so it cannot fall outside the slider's
       * configured minimum and maximum.
       */
      val = Math.min(max, Math.max(min, val));

      /**
       * Apply the normalized value to both controls.
       */
      this.value = val;
      $rng.val(val);
    }
  });
});

/**
 * Self-contained WordPress Media Library integration for Forge Fields
 * image and file controls.
 *
 * This section:
 * - Prevents duplicate event binding.
 * - Opens the WordPress media frame.
 * - Stores selected attachment IDs.
 * - Updates image previews.
 * - Updates selected file links.
 */
(function ($) {
  /**
   * Stop immediately when the current screen contains no
   * Forge Fields Image or File controls.
   */
  if (!document.querySelector(".ff-media-wrap")) {
    return;
  }

  /**
   * Stop if WordPress Media Library is not available.
   */
  if (typeof wp === "undefined" || !wp.media) {
    return;
  }

  /**
   * Prevent the media handlers from being bound more than once.
   *
   * The flag is stored globally so repeated script execution does not
   * create duplicate WordPress Media Library event handlers.
   */
  if (window.ffMediaBound) {
    return;
  }

  window.ffMediaBound = true;

  /**
   * Opens the WordPress Media Library for a Forge Fields media control.
   *
   * The field wrapper's data-type determines whether the frame is configured
   * for image selection or general file selection.
   *
   * Once an attachment is selected:
   * - Its attachment ID is stored in the field input.
   * - Image fields display the selected image preview.
   * - File fields display the selected filename and link.
   * - The media-clear control becomes visible.
   *
   * @param {jQuery} $wrap The Forge Fields media wrapper.
   * @param {jQuery} $input The input used to store the attachment ID.
   */
  function openFFFrame($wrap, $input) {
    /**
     * Determine whether this media control represents an image or file field.
     */
    var type = $wrap.data("type");

    /**
     * Create the WordPress media-selection frame.
     */
    var frame = wp.media({
      title: type === "image" ? "Select Image" : "Select File",
      button: {
        text: "Use this " + (type === "image" ? "image" : "file"),
      },
      multiple: false,
      library: type === "image" ? { type: "image" } : {},
    });

    /**
     * Handle the attachment selected from the WordPress Media Library.
     */
    frame.on("select", function () {
      /**
       * Retrieve the first selected attachment as a plain object.
       */
      var att = frame.state().get("selection").first().toJSON();

      /**
       * Store the WordPress attachment ID and trigger the input's
       * change event so any dependent behavior is notified.
       */
      $input.val(att.id).trigger("change");

      /**
       * Image fields display an image preview.
       */
      if (type === "image") {
        /**
         * Prefer the generated thumbnail size when available,
         * otherwise fall back to the attachment's original URL.
         */
        var url =
          att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;

        $wrap.find(".ff-media-preview").attr("src", url).show();
      } else {
        /**
         * File fields display a clickable link containing the selected filename.
         */
        $wrap
          .find(".ff-media-fileurl")
          .attr("href", att.url)
          .text(att.filename)
          .show();

        /**
         * Hide the empty-state message now that a file has been selected.
         */
        $wrap.find(".ff-media-nofile").hide();
      }

      /**
       * Show the control that allows the selected media item to be cleared.
       */
      $wrap.find(".ff-media-clear").show();
    });

    /**
     * Display the configured WordPress Media Library frame.
     */
    frame.open();
  }

  /**
   * Bind the media-selection control using a namespaced delegated
   * jQuery click handler.
   *
   * The existing ffMedia click handler is removed first to prevent
   * duplicate bindings, then the current handler is attached.
   */
  $(document)
    .off("click.ffMedia", ".ff-media-select")
    .on("click.ffMedia", ".ff-media-select", function (e) {
      e.preventDefault();

      /**
       * Read the target input ID stored on the clicked media-select control.
       */
      var id = $(this).data("target");

      /**
       * Locate the input that stores the selected attachment ID.
       */
      var $input = $("#" + id);

      if (!$input.length) return;

      /**
       * Find the surrounding Forge Fields media wrapper.
       */
      var $wrap = $input.closest(".ff-media-wrap");

      /**
       * Open the WordPress Media Library for this media field.
       */
      openFFFrame($wrap, $input);
    });

  /**
   * Bind the media-clear control using a namespaced delegated
   * jQuery click handler.
   *
   * The existing ffMedia handler is removed first so the clear action
   * cannot be bound multiple times.
   */
  $(document)
    .off("click.ffMedia", ".ff-media-clear")
    .on("click.ffMedia", ".ff-media-clear", function (e) {
      e.preventDefault();

      /**
       * Read the target input ID stored on the clicked clear control.
       */
      var id = $(this).data("target");

      /**
       * Locate the input currently storing the attachment ID.
       */
      var $input = $("#" + id);

      if (!$input.length) return;

      /**
       * Find the surrounding media wrapper so its preview state
       * can also be reset.
       */
      var $wrap = $input.closest(".ff-media-wrap");

      /**
       * Clear the stored attachment ID and trigger its change event.
       */
      $input.val("").trigger("change");

      /**
       * Reset the visual state differently depending on whether this
       * media field represents an image or a general file.
       */
      if ($wrap.data("type") === "image") {
        /**
         * Image fields clear and hide the image preview.
         */
        $wrap.find(".ff-media-preview").attr("src", "").hide();
      } else {
        /**
         * File fields clear and hide the selected file link,
         * then restore the no-file-selected message.
         */
        $wrap.find(".ff-media-fileurl").attr("href", "").text("").hide();
        $wrap.find(".ff-media-nofile").show();
      }

      /**
       * Hide the Clear control after the selected media has been removed.
       */
      $(this).hide();
    });
})(jQuery);

/**
 * Prevent Page/Post updates when a required Forge Field is empty.
 */
document.addEventListener("DOMContentLoaded", function () {
  const postForm = document.getElementById("post");

  if (postForm) {
    postForm.addEventListener("submit", function (event) {
      const requiredRows = document.querySelectorAll(
        ".ff-mb .ff-required-field",
      );

      let firstInvalid = null;

      requiredRows.forEach(function (row) {
        const type = row.dataset.ffFieldType || "";
        let isValid = true;

        switch (type) {
          case "checkbox": {
            isValid =
              row.querySelectorAll('input[type="checkbox"]:checked').length > 0;
            break;
          }

          case "radio":
          case "button_group": {
            isValid = row.querySelector('input[type="radio"]:checked') !== null;
            break;
          }

          case "true_false": {
            isValid =
              row.querySelector('input[type="checkbox"]:checked') !== null;
            break;
          }

          case "image":
          case "file": {
            const hidden = row.querySelector(
              'input[type="hidden"][name^="_ff_"]',
            );

            isValid =
              hidden !== null &&
              hidden.value.trim() !== "" &&
              hidden.value !== "0";

            break;
          }

          case "wysiwyg": {
            const textarea = row.querySelector("textarea");

            if (!textarea) {
              isValid = false;
              break;
            }

            /**
             * When TinyMCE is active, read the content directly from
             * the editor because it may not yet be synchronized with
             * the underlying textarea.
             */
            if (
              window.tinymce &&
              textarea.id &&
              window.tinymce.get(textarea.id)
            ) {
              const editor = window.tinymce.get(textarea.id);

              isValid = editor.getContent({ format: "text" }).trim() !== "";
            } else {
              isValid = textarea.value.trim() !== "";
            }

            break;
          }

          default: {
            const control = row.querySelector(
              "input:not([type='hidden']), textarea, select",
            );

            isValid = control !== null && String(control.value).trim() !== "";

            break;
          }
        }

        row.classList.toggle("ff-required-error", !isValid);

        if (!isValid && !firstInvalid) {
          firstInvalid = row;
        }
      });

      if (firstInvalid) {
        event.preventDefault();

        firstInvalid.scrollIntoView({
          behavior: "smooth",
          block: "center",
        });

        const control = firstInvalid.querySelector(
          "input, textarea, select, button",
        );

        if (control) {
          control.focus();
        }

        window.alert(
          "Please complete all required Forge Fields before updating this page.",
        );
      }
    });
  }
});

/**
 * Handle Forge Fields tab navigation on rendered field groups.
 *
 * Each tab button activates the matching panel inside its own
 * data-ff-tabs container without affecting other Field Groups.
 */
document.addEventListener("click", function (event) {
  const button = event.target.closest(".ff-tab-button");

  if (!button) {
    return;
  }

  const tabsWrap = button.closest("[data-ff-tabs]");

  if (!tabsWrap) {
    return;
  }

  event.preventDefault();

  const target = button.getAttribute("data-ff-tab");

  if (target === null) {
    return;
  }

  const buttons = tabsWrap.querySelectorAll(".ff-tab-button");
  const panels = tabsWrap.querySelectorAll(".ff-tab-panel");

  /**
   * Clear the currently active tab and panel.
   */
  buttons.forEach(function (tabButton) {
    tabButton.classList.remove("is-active");
  });

  panels.forEach(function (panel) {
    panel.classList.remove("is-active");
  });

  /**
   * Activate the clicked tab.
   */
  button.classList.add("is-active");

  /**
   * Activate the panel belonging to the clicked tab.
   */
  const targetPanel = tabsWrap.querySelector(
    '[data-ff-tab-panel="' + target + '"]',
  );

  if (targetPanel) {
    targetPanel.classList.add("is-active");
  }
});

/**
 * Keep True/False field labels synchronized with their toggle state.
 *
 * Applies to Forge Fields controls rendered on:
 * - Page/Post edit screens
 * - Global Fields
 */
document.addEventListener("change", function (event) {
  if (!event.target.matches(".ff-true-false-input")) {
    return;
  }

  const control = event.target.closest(".ff-true-false-control");

  if (!control) {
    return;
  }

  const label = control.querySelector(".ff-true-false-label");

  if (!label) {
    return;
  }

  label.textContent = event.target.checked ? "True" : "False";
});

/**
 * Apply the standard Forge Fields focus state to WordPress WYSIWYG editors.
 *
 * Code/Text mode can use normal browser focus detection, but TinyMCE Visual
 * mode runs inside an iframe. TinyMCE focus and blur events therefore toggle
 * a class on the outer WordPress editor wrapper.
 */
(function () {
  const focusClass = "ff-wysiwyg-focused";

  /**
   * Return the WordPress editor wrapper associated with a TinyMCE editor.
   *
   * @param {Object} editor TinyMCE editor instance.
   * @returns {HTMLElement|null}
   */
  function getEditorWrap(editor) {
    if (!editor || !editor.id) {
      return null;
    }

    const textarea = document.getElementById(editor.id);

    if (!textarea) {
      return null;
    }

    return textarea.closest(".wp-editor-wrap");
  }

  /**
   * Attach Forge Fields focus behavior to one TinyMCE instance.
   *
   * @param {Object} editor TinyMCE editor instance.
   */
  function bindEditorFocus(editor) {
    if (!editor || editor.__ffFocusBound) {
      return;
    }

    editor.__ffFocusBound = true;

    editor.on("focus", function () {
      const wrap = getEditorWrap(editor);

      if (wrap) {
        wrap.classList.add(focusClass);
      }
    });

    editor.on("blur", function () {
      const wrap = getEditorWrap(editor);

      if (wrap) {
        wrap.classList.remove(focusClass);
      }
    });

    editor.on("remove", function () {
      const wrap = getEditorWrap(editor);

      if (wrap) {
        wrap.classList.remove(focusClass);
      }
    });
  }

  /**
   * Bind all TinyMCE editors that already exist.
   */
  function bindExistingEditors() {
    if (!window.tinymce || !Array.isArray(window.tinymce.editors)) {
      return;
    }

    window.tinymce.editors.forEach(bindEditorFocus);
  }

  /**
   * Initialize TinyMCE focus tracking.
   *
   * Existing editors are handled immediately and editors created later
   * are handled through TinyMCE's AddEditor event.
   */
  function initTinyMceFocus() {
    if (!window.tinymce) {
      return;
    }

    bindExistingEditors();

    if (typeof window.tinymce.on === "function") {
      window.tinymce.on("AddEditor", function (event) {
        if (event && event.editor) {
          bindEditorFocus(event.editor);
        }
      });
    }
  }

  /**
   * Code/Text mode uses a normal textarea, so standard DOM focus events
   * can toggle the same wrapper class.
   */
  document.addEventListener("focusin", function (event) {
    if (
      !event.target.matches(
        ".ff-mb .wp-editor-area, .ff-editor-card .wp-editor-area",
      )
    ) {
      return;
    }

    const wrap = event.target.closest(".wp-editor-wrap");

    if (wrap) {
      wrap.classList.add(focusClass);
    }
  });

  document.addEventListener("focusout", function (event) {
    if (
      !event.target.matches(
        ".ff-mb .wp-editor-area, .ff-editor-card .wp-editor-area",
      )
    ) {
      return;
    }

    const wrap = event.target.closest(".wp-editor-wrap");

    if (wrap) {
      wrap.classList.remove(focusClass);
    }
  });

  /**
   * WordPress may initialize TinyMCE before or after this script executes.
   */
  if (document.readyState === "loading") {
    document.addEventListener("DOMContentLoaded", initTinyMceFocus);
  } else {
    initTinyMceFocus();
  }

  window.addEventListener("load", bindExistingEditors);
})();
