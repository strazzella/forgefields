/**
 * Forge Fields field-control behavior.
 *
 * Rendered Page/Post field behavior belongs here.
 * Builder/admin-page behavior belongs in admin-fields.js.
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
 * Synchronizes Range sliders with their paired numeric controls.
 *
 * data-target identifies the paired control.
 */
jQuery(function ($) {
  if (!document.querySelector(".ff-range-wrap")) {
    return;
  }

  $(document).on("input change", ".ff-range-slider", function () {
    var $num = $($(this).data("target"));

    if ($num.length) $num.val(this.value);
  });

  /**
   * Numeric values are clamped to the slider's configured range.
   */
  $(document).on("input change", ".ff-range-number", function () {
    var $rng = $($(this).data("target"));

    if ($rng.length) {
      var min = parseFloat($rng.attr("min")) || 0;
      var max = parseFloat($rng.attr("max")) || 100;

      var val = parseFloat(this.value);

      if (isNaN(val)) val = min;

      val = Math.min(max, Math.max(min, val));

      this.value = val;
      $rng.val(val);
    }
  });
});

/**
 * WordPress Media Library integration for Image and File fields.
 *
 * Attachment IDs are stored rather than URLs, and global binding
 * protection prevents duplicate delegated media handlers.
 */
(function ($) {
  if (!document.querySelector(".ff-media-wrap")) {
    return;
  }

  if (typeof wp === "undefined" || !wp.media) {
    return;
  }

  /**
   * Prevent duplicate handlers if this script is executed more than once.
   */
  if (window.ffMediaBound) {
    return;
  }

  window.ffMediaBound = true;

  /**
   * Opens the WordPress Media Library for an Image or File field.
   *
   * @param {jQuery} $wrap The Forge Fields media wrapper.
   * @param {jQuery} $input The input used to store the attachment ID.
   */
  function openFFFrame($wrap, $input) {
    var type = $wrap.data("type");

    var frame = wp.media({
      title: type === "image" ? "Select Image" : "Select File",
      button: {
        text: "Use this " + (type === "image" ? "image" : "file"),
      },
      multiple: false,
      library: type === "image" ? { type: "image" } : {},
    });

    frame.on("select", function () {
      var att = frame.state().get("selection").first().toJSON();

      $input.val(att.id).trigger("change");

      if (type === "image") {
        /**
         * Prefer WordPress's generated thumbnail and fall back
         * to the original attachment URL.
         */
        var url =
          att.sizes && att.sizes.thumbnail ? att.sizes.thumbnail.url : att.url;

        $wrap.find(".ff-media-preview").attr("src", url).show();
      } else {
        $wrap
          .find(".ff-media-fileurl")
          .attr("href", att.url)
          .text(att.filename)
          .show();

        $wrap.find(".ff-media-nofile").hide();
      }

      $wrap.find(".ff-media-clear").show();
    });

    frame.open();
  }

  /**
   * Namespaced delegated handlers allow dynamically rendered media
   * controls while preventing duplicate bindings.
   */
  $(document)
    .off("click.ffMedia", ".ff-media-select")
    .on("click.ffMedia", ".ff-media-select", function (e) {
      e.preventDefault();

      var id = $(this).data("target");

      var $input = $("#" + id);

      if (!$input.length) return;

      var $wrap = $input.closest(".ff-media-wrap");

      openFFFrame($wrap, $input);
    });

  $(document)
    .off("click.ffMedia", ".ff-media-clear")
    .on("click.ffMedia", ".ff-media-clear", function (e) {
      e.preventDefault();

      var id = $(this).data("target");

      var $input = $("#" + id);

      if (!$input.length) return;

      var $wrap = $input.closest(".ff-media-wrap");

      $input.val("").trigger("change");

      if ($wrap.data("type") === "image") {
        $wrap.find(".ff-media-preview").attr("src", "").hide();
      } else {
        $wrap.find(".ff-media-fileurl").attr("href", "").text("").hide();
        $wrap.find(".ff-media-nofile").show();
      }

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
             * TinyMCE content may not yet be synchronized with
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
 * Handles tab navigation within each rendered Field Group independently.
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

  buttons.forEach(function (tabButton) {
    tabButton.classList.remove("is-active");
  });

  panels.forEach(function (panel) {
    panel.classList.remove("is-active");
  });

  button.classList.add("is-active");

  const targetPanel = tabsWrap.querySelector(
    '[data-ff-tab-panel="' + target + '"]',
  );

  if (targetPanel) {
    targetPanel.classList.add("is-active");
  }
});

/**
 * Keeps rendered True/False labels synchronized with their toggle state.
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
 * Applies the Forge Fields focus state to WordPress WYSIWYG editors.
 *
 * TinyMCE Visual mode runs inside an iframe, so its own focus and blur
 * events must update the outer WordPress editor wrapper.
 */
(function () {
  const focusClass = "ff-wysiwyg-focused";

  /**
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
   * Bind focus behavior once per TinyMCE instance.
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

  function bindExistingEditors() {
    if (!window.tinymce || !Array.isArray(window.tinymce.editors)) {
      return;
    }

    window.tinymce.editors.forEach(bindEditorFocus);
  }

  /**
   * Handles both existing TinyMCE editors and editors added later by WordPress.
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
   * Code/Text mode uses normal DOM focus events.
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
