/**
 * Initializes the Forge Fields field editor after the page DOM has finished loading.
 *
 * This section handles:
 * - Generating field-name slugs from labels.
 * - Referencing the main field table and Add Field button.
 * - Building field-type select markup.
 * - Renumbering field rows.
 * - Attaching row-specific events.
 * - Automatically generating field names.
 * - Adding new field rows.
 */
document.addEventListener("DOMContentLoaded", function () {
  /**
   * Converts a human-readable field label into a normalized field-name slug.
   *
   * Example:
   * "Customer Email Address" becomes "customer_email_address".
   *
   * @param {string} text The field label to convert.
   * @returns {string} The normalized field-name slug.
   */
  function labelToSlug(text) {
    return text
      .trim()
      .toLowerCase()
      .replace(/[^a-z0-9\s]/g, "")
      .replace(/\s+/g, "_");
  }

  const tbody = document.querySelector("#ff-fields-body");

  const addBtn = document.querySelector("#ff-add-field");

  const rowTemplate = document.getElementById("ff-field-row-template");

  function buildTypeSelectHTML(nameAttr) {
    return (
      '<div class="ff-select-wrap">' +
      '<select class="ff-field-type" name="' +
      nameAttr +
      '">' +
      '<optgroup label="Basic">' +
      '<option value="text">Text</option>' +
      '<option value="textarea">Textarea</option>' +
      '<option value="number">Number</option>' +
      '<option value="email">Email</option>' +
      '<option value="url">URL</option>' +
      '<option value="range">Range</option>' +
      '<option value="password">Password</option>' +
      "</optgroup>" +
      '<optgroup label="Content">' +
      '<option value="image">Image</option>' +
      '<option value="file">File</option>' +
      '<option value="wysiwyg">WYSIWYG Editor</option>' +
      "</optgroup>" +
      '<optgroup label="Choice">' +
      '<option value="select">Select</option>' +
      '<option value="checkbox">Checkbox</option>' +
      '<option value="radio">Radio</option>' +
      '<option value="button_group">Button Group</option>' +
      '<option value="true_false">True/False</option>' +
      "</optgroup>" +
      '<optgroup label="Layout">' +
      '<option value="tab">Tab</option>' +
      "</optgroup>" +
      "</select>" +
      "</div>"
    );
  }

  if (tbody) {
    /**
     * Reindex all editable field rows and their associated options rows.
     *
     * Submitted fields must retain sequential ff_fields[index] names
     * after rows are added, removed, or reordered.
     */
    function renumberRows() {
      const rows = Array.from(tbody.querySelectorAll(".ff-field-row"));
      const canDrag = rows.length > 1;

      rows.forEach(function (row, index) {
        row.dataset.index = index;

        const labelInput = row.querySelector(".ff-field-label");
        const nameInput = row.querySelector(".ff-field-name");
        const typeSelect = row.querySelector(".ff-field-type");
        const handle = row.querySelector(".ff-field-handle");

        if (labelInput) {
          labelInput.name = "ff_fields[" + index + "][label]";
          labelInput.dataset.index = index;
        }

        if (nameInput) {
          nameInput.name = "ff_fields[" + index + "][name]";
          nameInput.dataset.index = index;
        }

        if (typeSelect) {
          typeSelect.name = "ff_fields[" + index + "][type]";
        }

        if (handle) {
          handle.draggable = canDrag;

          if (canDrag) {
            handle.classList.remove("ff-handle-disabled");
          } else {
            handle.classList.add("ff-handle-disabled");
          }
        }

        /**
         * Keep the settings row attached to the same field index.
         */
        const settingsRow = row.nextElementSibling;

        if (settingsRow && settingsRow.matches("[data-ff-settings]")) {
          settingsRow.dataset.index = index;

          settingsRow
            .querySelectorAll("input, textarea, select")
            .forEach(function (control) {
              if (!control.name) return;

              control.name = control.name.replace(
                /ff_fields\[\d+\]/,
                "ff_fields[" + index + "]",
              );
            });

          settingsRow.querySelectorAll("[id]").forEach(function (element) {
            element.id = element.id.replace(/-\d+$/, "-" + index);
          });

          settingsRow.querySelectorAll("label[for]").forEach(function (label) {
            label.htmlFor = label.htmlFor.replace(/-\d+$/, "-" + index);
          });
        }
      });
    }

    function normalizeFieldName(value) {
      return value
        .trim()
        .toLowerCase()
        .replace(/[^a-z0-9_-]/g, "");
    }

    function getFieldNameMessageElement(nameInput) {
      let message = nameInput.parentElement.querySelector(
        ".ff-field-name-message",
      );

      if (!message) {
        message = document.createElement("p");
        message.className = "ff-field-name-message";
        nameInput.insertAdjacentElement("afterend", message);
      }

      return message;
    }

    function validateFieldNames() {
      const rows = Array.from(tbody.querySelectorAll(".ff-field-row"));

      const currentNames = {};

      rows.forEach(function (row) {
        const nameInput = row.querySelector(".ff-field-name");

        if (!nameInput || nameInput.readOnly) {
          return;
        }

        const name = normalizeFieldName(nameInput.value);

        if (!name) {
          return;
        }

        if (!currentNames[name]) {
          currentNames[name] = [];
        }

        currentNames[name].push(nameInput);
      });

      rows.forEach(function (row) {
        const nameInput = row.querySelector(".ff-field-name");

        if (!nameInput) {
          return;
        }

        const message = getFieldNameMessageElement(nameInput);
        const name = normalizeFieldName(nameInput.value);

        message.textContent = "";
        message.classList.remove("is-error", "is-warning");

        nameInput.classList.remove(
          "ff-field-name-error",
          "ff-field-name-warning",
        );

        if (!name || nameInput.readOnly) {
          message.hidden = true;
          return;
        }

        /*
         * Same Field Group.
         *
         * This is an error because two fields in the same group
         * would use the same group-scoped post-meta key.
         */
        if (currentNames[name] && currentNames[name].length > 1) {
          message.textContent =
            'Field name "' +
            name +
            '" is already used in this Field Group. Choose a unique name.';

          message.classList.add("is-error");
          nameInput.classList.add("ff-field-name-error");
          message.hidden = false;

          return;
        }

        /*
         * Another saved Field Group in the same retrieval scope.
         *
         * Global and Page/Post fields use separate retrieval namespaces,
         * so the same field name may safely exist once globally and once
         * within normal Page/Post Field Groups.
         */
        const registry =
          window.ffFieldNameRegistry &&
          typeof window.ffFieldNameRegistry === "object"
            ? window.ffFieldNameRegistry
            : {
                global: [],
                non_global: [],
              };

        const locationSelect = document.querySelector("#ff_location");

        const currentScope =
          locationSelect && locationSelect.value === "global"
            ? "global"
            : "non_global";

        const otherGroupNames = Array.isArray(registry[currentScope])
          ? registry[currentScope]
          : [];

        if (otherGroupNames.includes(name)) {
          message.textContent =
            'Field name "' +
            name +
            '" is already used in another ' +
            (currentScope === "global" ? "Global " : "") +
            "Field Group. To avoid ambiguous output, choose a unique name or specify this Field Group Key as the third argument to ff_get_field().";

          message.classList.add("is-warning");
          nameInput.classList.add("ff-field-name-warning");
          message.hidden = false;

          return;
        }

        message.hidden = true;
      });
    }

    function attachRowEvents(row) {
      const labelInput = row.querySelector(".ff-field-label");
      const nameInput = row.querySelector(".ff-field-name");
      const removeLink = row.querySelector(".ff-field-remove");
      const typeSelect = row.querySelector(".ff-field-type");
      const optionsToggle = row.querySelector(".ff-field-options-toggle");

      function syncOptionsToggleVisibility() {
        if (!typeSelect || !optionsToggle) {
          return;
        }

        const isTab = typeSelect.value === "tab";

        optionsToggle.style.display = isTab ? "none" : "";

        /**
         * If the row was switched to Tab while Field Options was open,
         * close the now-empty options row as well.
         */
        if (isTab) {
          const settingsRow = row.nextElementSibling;

          if (settingsRow && settingsRow.matches("[data-ff-settings]")) {
            settingsRow.classList.add("is-hidden");
            settingsRow.style.display = "none";

            row.classList.remove("ff-options-open");
            settingsRow.classList.remove("ff-options-open");

            optionsToggle.setAttribute("aria-expanded", "false");
          }
        }
      }

      syncOptionsToggleVisibility();

      if (typeSelect) {
        typeSelect.addEventListener("change", function () {
          syncTabRowState(row);
          syncFieldOptions(row);
          syncOptionsToggleVisibility();

          const choiceTypes = ["select", "checkbox", "radio", "button_group"];

          if (choiceTypes.includes(typeSelect.value)) {
            const settingsRow = row.nextElementSibling;

            if (settingsRow && settingsRow.matches("[data-ff-settings]")) {
              settingsRow.classList.remove("is-hidden");
              settingsRow.style.display = "";

              row.classList.add("ff-options-open");
              settingsRow.classList.add("ff-options-open");

              if (optionsToggle) {
                optionsToggle.setAttribute("aria-expanded", "true");
              }

              const choicesInput = settingsRow.querySelector(
                '[data-ff-option="choices"] textarea',
              );

              if (choicesInput) {
                setTimeout(function () {
                  choicesInput.focus();
                }, 50);
              }
            }
          }
        });
      }

      if (optionsToggle) {
        optionsToggle.addEventListener("click", function (e) {
          e.preventDefault();

          const settingsRow = row.nextElementSibling;

          if (!settingsRow || !settingsRow.matches("[data-ff-settings]")) {
            return;
          }

          const isOpen = !settingsRow.classList.contains("is-hidden");

          if (isOpen) {
            settingsRow.classList.add("is-hidden");
            settingsRow.style.display = "none";

            row.classList.remove("ff-options-open");
            settingsRow.classList.remove("ff-options-open");

            optionsToggle.setAttribute("aria-expanded", "false");
          } else {
            settingsRow.classList.remove("is-hidden");
            settingsRow.style.display = "";

            row.classList.add("ff-options-open");
            settingsRow.classList.add("ff-options-open");

            optionsToggle.setAttribute("aria-expanded", "true");
            syncFieldOptions(row);
          }
        });
      }

      const trueFalseToggle = row.nextElementSibling
        ? row.nextElementSibling.querySelector(".ff-true-false-default-toggle")
        : null;

      if (trueFalseToggle) {
        trueFalseToggle.addEventListener("change", function () {
          const settingsRow = row.nextElementSibling;

          if (!settingsRow) {
            return;
          }

          const defaultInput = settingsRow.querySelector(
            'input[name$="[default_value]"]',
          );

          const label = settingsRow.querySelector(
            ".ff-true-false-default-label",
          );

          if (defaultInput) {
            defaultInput.value = this.checked ? "1" : "0";
          }

          if (label) {
            label.textContent = this.checked ? "True" : "False";
          }
        });
      }

      /**
       * Automatically generate a field name after the user leaves the label input.
       *
       * A name is only generated when:
       * - A matching name input exists.
       * - The field is not a Tab field.
       * - The user has not already entered a field name manually.
       * - The label itself is not empty.
       */
      if (labelInput) {
        labelInput.addEventListener("blur", function () {
          if (!nameInput) return;

          const typeSelect = row.querySelector(".ff-field-type");
          const isTab = typeSelect && typeSelect.value === "tab";

          if (isTab) {
            nameInput.value = "";
            validateFieldNames();
            return;
          }

          if (nameInput.value.trim() !== "") return;

          if (this.value.trim() === "") return;

          nameInput.value = labelToSlug(this.value);

          validateFieldNames();
        });
      }

      if (nameInput) {
        nameInput.addEventListener("input", function () {
          validateFieldNames();
        });

        nameInput.addEventListener("blur", function () {
          validateFieldNames();
        });
      }
    }

    tbody.querySelectorAll(".ff-field-row").forEach(function (row) {
      attachRowEvents(row);
      syncTabRowState(row);
      syncFieldOptions(row);

      const settingsRow = row.nextElementSibling;

      if (
        settingsRow &&
        settingsRow.matches("[data-ff-settings]") &&
        !settingsRow.classList.contains("is-hidden")
      ) {
        row.classList.add("ff-options-open");
        settingsRow.classList.add("ff-options-open");
      }
    });

    renumberRows();
    validateFieldNames();

    const locationSelect = document.querySelector("#ff_location");

    if (locationSelect) {
      locationSelect.addEventListener("change", function () {
        validateFieldNames();
      });
    }

    /**
     * Adds a new editable field row.
     *
     * Uses the dedicated row template when available and falls back
     * to cloning the final existing row.
     */
    function addFieldRow() {
      const index = tbody.querySelectorAll(".ff-field-row").length;

      if (rowTemplate) {
        const html = rowTemplate.innerHTML.replace(/__INDEX__/g, index);
        const tmp = document.createElement("tbody");
        tmp.innerHTML = html;

        const rows = Array.from(tmp.querySelectorAll("tr"));

        rows.forEach(function (r) {
          tbody.appendChild(r);
          if (r.classList.contains("ff-field-row")) {
            attachRowEvents(r);
            syncTabRowState(r);
            syncFieldOptions(r);
          }
        });
      } else {
        const rows = tbody.querySelectorAll(".ff-field-row");
        const lastRow = rows[rows.length - 1] || null;
        if (!lastRow) return;

        const newRow = lastRow.cloneNode(true);

        newRow.querySelectorAll("input, textarea").forEach(function (el) {
          el.value = "";
        });

        const sel = newRow.querySelector("select.ff-field-type");
        if (sel) sel.selectedIndex = 0;

        tbody.appendChild(newRow);
        attachRowEvents(newRow);
      }

      renumberRows();
    }

    if (addBtn) {
      addBtn.addEventListener("click", function (e) {
        e.preventDefault();
        addFieldRow();
      });
    }

    /**
     * Tab fields are structural and do not use a normal field name.
     */
    function syncTabRowState(row) {
      const typeSelect = row.querySelector(".ff-field-type");
      const nameInput = row.querySelector(".ff-field-name");

      if (!typeSelect || !nameInput) return;

      const isTab = typeSelect.value === "tab";

      if (isTab) {
        nameInput.value = "";
        nameInput.placeholder = "";
        nameInput.readOnly = true;
        nameInput.classList.add("ff-name-disabled");
      } else {
        nameInput.readOnly = false;
        nameInput.placeholder = "";
        nameInput.classList.remove("ff-name-disabled");
      }
    }

    function syncFieldOptions(row) {
      const typeSelect = row.querySelector(".ff-field-type");

      if (!typeSelect) {
        return;
      }

      const settingsRow = row.nextElementSibling;

      if (!settingsRow || !settingsRow.matches("[data-ff-settings]")) {
        return;
      }

      const type = typeSelect.value;

      const choiceTypes = ["select", "checkbox", "radio", "button_group"];

      const characterLimitTypes = [
        "text",
        "textarea",
        "email",
        "url",
        "password",
      ];

      const prependAppendTypes = ["text", "number", "email", "password"];

      const choicesOption = settingsRow.querySelector(
        '[data-ff-option="choices"]',
      );

      const defaultOption = settingsRow.querySelector(
        '[data-ff-option="default"]',
      );

      const standardDefault = settingsRow.querySelector(".ff-default-standard");

      const trueFalseDefault = settingsRow.querySelector(
        ".ff-default-true-false",
      );

      const defaultInput = settingsRow.querySelector(
        'input[name$="[default_value]"]',
      );

      const trueFalseToggle = settingsRow.querySelector(
        ".ff-true-false-default-toggle",
      );

      const trueFalseLabel = settingsRow.querySelector(
        ".ff-true-false-default-label",
      );

      const characterLimitOption = settingsRow.querySelector(
        '[data-ff-option="character-limit"]',
      );

      const requiredOption = settingsRow.querySelector(
        '[data-ff-option="required"]',
      );

      const prependOption = settingsRow.querySelector(
        '[data-ff-option="prepend"]',
      );

      const appendOption = settingsRow.querySelector(
        '[data-ff-option="append"]',
      );

      if (choicesOption) {
        choicesOption.style.display = choiceTypes.includes(type) ? "" : "none";
      }

      if (defaultOption) {
        defaultOption.style.display = type === "tab" ? "none" : "";

        if (standardDefault) {
          standardDefault.style.display = type === "true_false" ? "none" : "";
        }

        if (trueFalseDefault) {
          trueFalseDefault.style.display = type === "true_false" ? "" : "none";
        }

        if (type === "true_false" && defaultInput && trueFalseToggle) {
          trueFalseToggle.checked = defaultInput.value === "1";

          if (trueFalseLabel) {
            trueFalseLabel.textContent = trueFalseToggle.checked
              ? "True"
              : "False";
          }
        }
      }

      if (characterLimitOption) {
        characterLimitOption.style.display = characterLimitTypes.includes(type)
          ? ""
          : "none";
      }

      if (requiredOption) {
        const requiredInput = requiredOption.querySelector(
          'input[type="checkbox"]',
        );

        const supportsRequired = type !== "tab" && type !== "true_false";

        requiredOption.style.display = supportsRequired ? "" : "none";

        /**
         * True/False and Tab fields cannot meaningfully be required.
         */
        if (!supportsRequired && requiredInput) {
          requiredInput.checked = false;
        }
      }

      if (prependOption) {
        prependOption.style.display = prependAppendTypes.includes(type)
          ? ""
          : "none";
      }

      if (appendOption) {
        appendOption.style.display = prependAppendTypes.includes(type)
          ? ""
          : "none";
      }
    }

    let draggingRow = null;
    let draggingSettingsRow = null;

    /**
     * Starts field-row drag-and-drop reordering.
     *
     * The field's settings row is tracked separately so it remains
     * attached to the same field while the pair is moved.
     */
    tbody.addEventListener("dragstart", function (e) {
      const handle = e.target.closest(".ff-field-handle");
      if (!handle || !handle.draggable) {
        return;
      }

      const row = handle.closest(".ff-field-row");
      if (!row) return;

      draggingRow = row;
      draggingSettingsRow = row.nextElementSibling;

      if (
        !draggingSettingsRow ||
        !draggingSettingsRow.matches("[data-ff-settings]")
      ) {
        draggingSettingsRow = null;
      }

      row.classList.add("ff-row-dragging");
      e.dataTransfer.effectAllowed = "move";

      /**
       * Required for HTML5 drag-and-drop in some browsers.
       */
      e.dataTransfer.setData("text/plain", "");
    });

    tbody.addEventListener("dragend", function () {
      if (draggingRow) {
        draggingRow.classList.remove("ff-row-dragging");
      }

      draggingRow = null;
      draggingSettingsRow = null;

      renumberRows();
    });

    tbody.addEventListener("dragover", function (e) {
      if (!draggingRow) return;

      e.preventDefault();

      const afterElement = getDragAfterElement(tbody, e.clientY);

      if (!afterElement) {
        tbody.appendChild(draggingRow);

        if (draggingSettingsRow) {
          tbody.appendChild(draggingSettingsRow);
        }
      } else if (afterElement !== draggingRow) {
        tbody.insertBefore(draggingRow, afterElement);

        if (draggingSettingsRow) {
          tbody.insertBefore(draggingSettingsRow, draggingRow.nextSibling);
        }
      }
    });

    /**
     * Finds the insertion point for a dragged field row based on
     * the pointer's vertical position.
     *
     * @param {HTMLElement} container The element containing the field rows.
     * @param {number} y The current vertical pointer coordinate.
     * @returns {HTMLElement|null} The row to insert before, or null.
     */
    function getDragAfterElement(container, y) {
      const rows = Array.from(
        container.querySelectorAll(".ff-field-row:not(.ff-row-dragging)"),
      );

      let closest = { offset: Number.NEGATIVE_INFINITY, element: null };

      rows.forEach(function (row) {
        const box = row.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;

        if (offset < 0 && offset > closest.offset) {
          closest = { offset: offset, element: row };
        }
      });

      return closest.element;
    }
  }

  /**
   * Forge Key copy controls.
   *
   * Uses the Clipboard API when available and retains a legacy
   * document.execCommand fallback.
   */
  document.querySelectorAll(".ff-copy-key").forEach(function (btn) {
    btn.addEventListener("click", async function (e) {
      e.preventDefault();

      const key = this.dataset.key;

      if (!key) {
        return;
      }

      const button = this;

      function showCopiedState() {
        button.classList.add("is-copied");

        const oldTitle = button.getAttribute("title") || "";

        button.setAttribute("title", "Copied!");

        setTimeout(function () {
          button.classList.remove("is-copied");
          button.setAttribute("title", oldTitle || "Copy to clipboard");
        }, 1000);
      }

      /**
       * Legacy clipboard fallback.
       *
       * @param {string} text Text to copy.
       * @returns {boolean} Whether the copy operation succeeded.
       */
      function fallbackCopy(text) {
        const textarea = document.createElement("textarea");

        textarea.value = text;
        textarea.setAttribute("readonly", "");
        textarea.style.position = "fixed";
        textarea.style.opacity = "0";
        textarea.style.pointerEvents = "none";

        document.body.appendChild(textarea);

        textarea.select();
        textarea.setSelectionRange(0, textarea.value.length);

        let copied = false;

        try {
          copied = document.execCommand("copy");
        } catch (error) {
          copied = false;
        }

        document.body.removeChild(textarea);

        return copied;
      }

      try {
        if (
          navigator.clipboard &&
          typeof navigator.clipboard.writeText === "function"
        ) {
          await navigator.clipboard.writeText(key);
          showCopiedState();
          return;
        }
      } catch (error) {
        // Fall through to the legacy copy method.
      }

      if (fallbackCopy(key)) {
        showCopiedState();
      }
    });
  });

  document.addEventListener("focusin", function (e) {
    const td = e.target.closest("td.column-primary");

    if (td) {
      const tr = td.closest("tr");

      if (tr) tr.classList.add("ff-row-focus");
    }
  });

  document.addEventListener("focusout", function (e) {
    const td = e.target.closest("td.column-primary");

    if (td) {
      const tr = td.closest("tr");

      if (tr) tr.classList.remove("ff-row-focus");
    }
  });
});

/**
 * Keeps the Forge Fields subbar positioned beneath the WordPress
 * admin bar and brand bar while accounting for the admin-menu width.
 */
(function () {
  const sub = document.querySelector(".ff-subbar");

  if (!sub) return;

  function placeSubbar() {
    const adminBar = document.getElementById("wpadminbar");
    const brand = document.querySelector(".ff-brandbar");

    const top =
      (adminBar ? adminBar.offsetHeight : 0) + (brand ? brand.offsetHeight : 0);

    const left = document.body.classList.contains("folded") ? 56 : 180;

    sub.style.position = "fixed";
    sub.style.top = top + "px";
    sub.style.left = left + "px";
    sub.style.right = 0;
    sub.style.width = "auto";
    sub.style.zIndex = 10;

    const wpcontent = document.getElementById("wpcontent");

    if (wpcontent && brand) {
      wpcontent.style.paddingTop = brand.offsetHeight + sub.offsetHeight + "px";
    }
  }

  window.addEventListener("load", placeSubbar);

  window.addEventListener("resize", placeSubbar);

  /**
   * WordPress changes the sidebar width after the Collapse menu
   * transition, so positioning is recalculated after a short delay.
   */
  document.body.addEventListener("click", (e) => {
    if (e.target.closest("#collapse-menu")) {
      setTimeout(placeSubbar, 200);
    }
  });

  placeSubbar();
})();

/**
 * Confirmation modal for single and bulk field removal.
 *
 * Includes keyboard focus trapping and restores focus when closed.
 */
(function () {
  const modal = document.getElementById("ff-confirm");

  if (!modal) return;

  const dlg = modal.querySelector(".ff-confirm__dialog");

  const overlay = modal.querySelector(".ff-confirm__overlay");

  const btnYes = modal.querySelector("#ff-confirm-yes");

  const nameEl = modal.querySelector(".ff-confirm__field-name");

  let pendingRow = null;

  let pendingRows = [];

  let lastFocus = null;

  /**
   * Opens confirmation for a single field row.
   *
   * @param {HTMLElement} row The field row pending removal.
   */
  function openConfirm(row) {
    pendingRow = row;

    lastFocus = document.activeElement;

    const labelInput = row.querySelector('input[name*="[label]"]');

    const label = (labelInput && labelInput.value.trim()) || "this field";

    nameEl.textContent = label;

    modal.classList.remove("is-hidden");

    dlg.focus();

    document.addEventListener("keydown", onKeydown, true);
  }

  /**
   * Opens confirmation for multiple selected field rows.
   *
   * @param {Iterable<HTMLElement>} rows The field rows pending bulk removal.
   */
  function openBulkConfirm(rows) {
    pendingRow = null;

    pendingRows = Array.from(rows);

    lastFocus = document.activeElement;

    const titleEl = modal.querySelector("#ff-confirm-title");

    const descEl = modal.querySelector("#ff-confirm-desc");

    titleEl.textContent = "Remove fields?";

    descEl.innerHTML =
      "This will remove <strong>" +
      pendingRows.length +
      "</strong> selected field" +
      (pendingRows.length === 1 ? "" : "s") +
      " from the group.";

    modal.classList.remove("is-hidden");

    dlg.focus();

    document.addEventListener("keydown", onKeydown, true);
  }

  /**
   * Closes the modal, clears pending state, and restores focus.
   */
  function closeConfirm() {
    modal.classList.add("is-hidden");

    document.removeEventListener("keydown", onKeydown, true);

    if (lastFocus && typeof lastFocus.focus === "function") {
      lastFocus.focus();
    }

    pendingRow = null;

    pendingRows = [];

    const titleEl = modal.querySelector("#ff-confirm-title");

    const descEl = modal.querySelector("#ff-confirm-desc");

    titleEl.textContent = "Remove field?";

    descEl.innerHTML =
      'This will remove <strong class="ff-confirm__field-name">this field</strong> from the group.';
  }

  /**
   * Handles Escape and traps Tab focus inside the modal.
   *
   * @param {KeyboardEvent} e The captured keyboard event.
   */
  function onKeydown(e) {
    if (e.key === "Escape") {
      e.preventDefault();

      closeConfirm();
    }

    if (e.key === "Tab") {
      const focusable = dlg.querySelectorAll(
        'button,[href],input,select,textarea,[tabindex]:not([tabindex="-1"])',
      );

      const list = Array.prototype.slice
        .call(focusable)
        .filter((el) => !el.disabled && el.offsetParent !== null);

      if (!list.length) return;

      const first = list[0];

      const last = list[list.length - 1];

      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault();

        last.focus();
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();

        first.focus();
      }
    }
  }

  /**
   * Reindexes field rows after removals so submitted
   * ff_fields[index] names remain contiguous.
   */
  function reindexFieldRows() {
    const rows = document.querySelectorAll("#ff-fields-body tr.ff-field-row");

    rows.forEach((row, index) => {
      row.dataset.index = index;

      const inputs = row.querySelectorAll(
        'input[name^="ff_fields["], select[name^="ff_fields["], textarea[name^="ff_fields["]',
      );

      inputs.forEach((input) => {
        input.name = input.name.replace(
          /ff_fields\[\d+]/,
          "ff_fields[" + index + "]",
        );
      });
    });
  }

  /**
   * Ensures the save flag exists before submitting the field-group form.
   */
  function submitFieldGroupForm() {
    const form = document.getElementById("ff-edit-form");

    if (!form) return;

    let saveInput = form.querySelector('input[name="ff_save_field_group"]');

    if (!saveInput) {
      saveInput = document.createElement("input");
      saveInput.type = "hidden";
      saveInput.name = "ff_save_field_group";
      saveInput.value = "1";

      form.appendChild(saveInput);
    }

    form.submit();
  }

  /**
   * Handles confirmed single or bulk field removal.
   *
   * Remaining rows are reindexed and the field group is saved immediately.
   */
  btnYes.addEventListener("click", function () {
    const tbody = document.querySelector("#ff-fields-body");

    if (!tbody) {
      closeConfirm();
      return;
    }

    if (pendingRows.length) {
      pendingRows.forEach(function (row) {
        const index = row.dataset.index;

        const settingsRow = tbody.querySelector(
          '.ff-field-settings[data-index="' + index + '"]',
        );

        if (settingsRow) {
          settingsRow.remove();
        }

        row.remove();
      });

      /**
       * Forge Fields keeps at least one editable row after bulk removal.
       */
      if (!tbody.querySelector(".ff-field-row")) {
        const addBtn = document.getElementById("ff-add-field");

        if (addBtn) {
          addBtn.click();
        }
      }

      reindexFieldRows();

      submitFieldGroupForm();

      closeConfirm();

      return;
    }

    if (!pendingRow) {
      closeConfirm();
      return;
    }

    const rows = tbody.querySelectorAll(".ff-field-row");

    const index = pendingRow.dataset.index;

    const settingsRow = tbody.querySelector(
      '.ff-field-settings[data-index="' + index + '"]',
    );

    /**
     * If the final editable row is removed, reset it instead of
     * removing it so the editor always retains one row.
     */
    if (rows.length === 1) {
      pendingRow.querySelectorAll("input").forEach(function (input) {
        if (input.type !== "checkbox") {
          input.value = "";
        }
      });

      const typeSelect = pendingRow.querySelector(".ff-field-type");

      if (typeSelect) {
        typeSelect.selectedIndex = 0;
      }

      if (settingsRow) {
        settingsRow.style.display = "none";
        settingsRow.classList.add("is-hidden");
      }
    } else {
      if (settingsRow) {
        settingsRow.remove();
      }

      pendingRow.remove();
    }

    reindexFieldRows();

    submitFieldGroupForm();

    closeConfirm();
  });

  modal.addEventListener("click", function (e) {
    if (e.target.hasAttribute("data-ff-close")) {
      e.preventDefault();
      closeConfirm();
    }
  });

  /**
   * Uses delegated removal handling so dynamically added field rows
   * work without separate remove-button listeners.
   */
  document.addEventListener(
    "click",
    function (e) {
      const removeBtn = e.target.closest(".ff-field-remove, .ff-remove-field");

      if (!removeBtn) return;

      const row = removeBtn.closest("tr.ff-field-row");

      if (!row) return;

      e.preventDefault();
      e.stopPropagation();

      if (e.stopImmediatePropagation) e.stopImmediatePropagation();

      openConfirm(row);
    },
    true,
  );

  const selectAllFields = document.querySelectorAll(".ff-select-all-fields");

  /**
   * Keeps Select All controls synchronized with individual field selections,
   * including the indeterminate state when only some fields are selected.
   */
  function syncSelectAllFields() {
    const fieldCheckboxes = Array.from(
      document.querySelectorAll("#ff-fields-body .ff-field-select"),
    );

    const checkedCount = fieldCheckboxes.filter(function (checkbox) {
      return checkbox.checked;
    }).length;

    selectAllFields.forEach(function (selectAll) {
      selectAll.checked =
        fieldCheckboxes.length > 0 && checkedCount === fieldCheckboxes.length;

      selectAll.indeterminate =
        checkedCount > 0 && checkedCount < fieldCheckboxes.length;
    });
  }

  selectAllFields.forEach(function (selectAll) {
    selectAll.addEventListener("change", function () {
      const checked = selectAll.checked;

      document
        .querySelectorAll("#ff-fields-body .ff-field-select")
        .forEach(function (checkbox) {
          checkbox.checked = checked;
        });

      /**
       * Multiple Select All controls must remain synchronized.
       */
      selectAllFields.forEach(function (otherSelectAll) {
        otherSelectAll.checked = checked;
        otherSelectAll.indeterminate = false;
      });
    });
  });

  document.addEventListener("change", function (e) {
    if (!e.target.classList.contains("ff-field-select")) {
      return;
    }

    syncSelectAllFields();
  });

  const bulkAction = document.getElementById("ff-field-bulk-action");

  const bulkApply = document.getElementById("ff-apply-field-bulk-action");

  /**
   * Applies the currently supported bulk field action.
   */
  if (bulkApply) {
    bulkApply.addEventListener("click", function () {
      if (!bulkAction || bulkAction.value !== "remove") {
        return;
      }

      const selectedRows = Array.from(
        document.querySelectorAll("#ff-fields-body .ff-field-select:checked"),
      )
        .map(function (checkbox) {
          return checkbox.closest(".ff-field-row");
        })
        .filter(Boolean);

      if (!selectedRows.length) {
        return;
      }

      openBulkConfirm(selectedRows);
    });
  }
})();

/**
 * Keeps the WordPress HTML/Text editor textarea at a consistent
 * height inside Forge Fields option cards.
 */
document.addEventListener("click", function (e) {
  const btn = e.target.closest(
    ".ff-options-card .wp-switch-editor.switch-html",
  );

  if (!btn) return;

  const wrap = btn.closest(".wp-editor-wrap");

  if (!wrap) return;

  const ta = wrap.querySelector("textarea.wp-editor-area");

  if (ta) {
    ta.style.minHeight = "260px";
    ta.style.height = "260px";
  }
});

/**
 * Shows the correct target selector for the selected Field Group location.
 */
document.addEventListener("DOMContentLoaded", function () {
  const locationSelect = document.getElementById("ff_location");

  const pageWrap = document.getElementById("ff_location_target_page_wrap");

  const postWrap = document.getElementById("ff_location_target_post_wrap");

  if (!locationSelect || !pageWrap || !postWrap) return;

  function syncLocationTargets() {
    const val = locationSelect.value;

    if (val === "page") {
      pageWrap.style.display = "";
      postWrap.style.display = "none";
    } else if (val === "post") {
      pageWrap.style.display = "none";
      postWrap.style.display = "";
    } else {
      pageWrap.style.display = "none";
      postWrap.style.display = "none";
    }
  }

  locationSelect.addEventListener("change", syncLocationTargets);

  syncLocationTargets();
});

/**
 * Initializes independent Forge Fields tab interfaces.
 */
document.addEventListener("DOMContentLoaded", function () {
  document.querySelectorAll("[data-ff-tabs]").forEach(function (tabsWrap) {
    const buttons = tabsWrap.querySelectorAll(".ff-tab-button");

    const panels = tabsWrap.querySelectorAll(".ff-tab-panel");

    if (!buttons.length || !panels.length) return;

    buttons.forEach(function (btn) {
      btn.addEventListener("click", function () {
        const target = btn.getAttribute("data-ff-tab");

        if (target === null) return;

        buttons.forEach(function (b) {
          b.classList.remove("is-active");
        });

        panels.forEach(function (panel) {
          panel.classList.remove("is-active");
        });

        btn.classList.add("is-active");

        const panel = tabsWrap.querySelector(
          '[data-ff-tab-panel="' + target + '"]',
        );

        if (panel) {
          panel.classList.add("is-active");
        }
      });
    });
  });
});
