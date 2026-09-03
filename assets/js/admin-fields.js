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
   * The generated value:
   * - Removes leading and trailing whitespace.
   * - Converts all characters to lowercase.
   * - Removes non-alphanumeric characters except spaces.
   * - Converts one or more spaces into underscores.
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

  /**
   * Main table body containing all editable field rows.
   */
  const tbody = document.querySelector("#ff-fields-body");

  /**
   * Button used to add a new field row.
   */
  const addBtn = document.querySelector("#ff-add-field");

  /**
   * HTML template used when creating new field rows dynamically.
   */
  const rowTemplate = document.getElementById("ff-field-row-template");

  /**
   * Builds the complete HTML markup for a field-type select element.
   *
   * Field types are grouped into:
   * - Basic
   * - Content
   * - Choice
   * - Layout
   *
   * The provided name attribute allows the select to submit as part of
   * the correct ff_fields[index] array entry.
   *
   * @param {string} nameAttr The name attribute assigned to the select.
   * @returns {string} The complete field-type select HTML.
   */
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

  /**
   * Only initialize field-row editing behavior when the main fields
   * table exists on the current admin page.
   */
  if (tbody) {
    /**
     * Renumbers every field row based on its current position.
     *
     * This is important after rows are added, removed, or reordered.
     * WordPress/PHP expects the submitted fields to use sequential names such as:
     *
     * ff_fields[0][label]
     * ff_fields[1][label]
     * ff_fields[2][label]
     *
     * The function also:
     * - Updates each row's data-index value.
     * - Updates label input names.
     * - Updates field-name input names.
     * - Updates field-type select names.
     * - Enables dragging only when more than one field exists.
     */
    /**
     * Reindex all editable field rows and their associated options rows.
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

    /**
     * Attaches the required event listeners to a field row.
     *
     * This function is used for both:
     * - Rows already present when the page loads.
     * - Rows dynamically added later.
     *
     * It handles:
     * - Updating tab-specific field state when the field type changes.
     * - Automatically generating a field name from its label.
     *
     * @param {HTMLElement} row The field row receiving the event handlers.
     */

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

      /**
       * Whenever the field type changes, synchronize any special behavior
       * required by that field type.
       *
       * In particular, Tab fields do not use a normal field-name value.
       */
      if (typeSelect) {
        typeSelect.addEventListener("change", function () {
          syncTabRowState(row);
          syncFieldOptions(row);

          /**
           * Choice-based fields require configured choices.
           * Automatically open Field Options when one is selected.
           */
          const choiceTypes = ["select", "checkbox", "radio", "button_group"];

          if (choiceTypes.includes(typeSelect.value)) {
            const settingsRow = row.nextElementSibling;

            if (settingsRow && settingsRow.matches("[data-ff-settings]")) {
              settingsRow.classList.remove("is-hidden");
              settingsRow.style.display = "";

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

    /**
     * Initialize all field rows that were already rendered when the page loaded.
     *
     * Each row receives its event handlers and its current field-type state
     * is synchronized before the rows are renumbered.
     */
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

    /**
     * Ensure all initially rendered rows have correct sequential indexes,
     * then validate field names for duplicates.
     */
    renumberRows();
    validateFieldNames();

    const locationSelect = document.querySelector("#ff_location");

    if (locationSelect) {
      locationSelect.addEventListener("change", function () {
        validateFieldNames();
      });
    }

    /**
     * Adds a new editable field row to the field table.
     *
     * Two creation methods are supported:
     *
     * 1. Preferred method:
     *    Use the dedicated row template and replace __INDEX__ placeholders
     *    with the new field index.
     *
     * 2. Fallback method:
     *    Clone the final existing field row, clear its values, and reset
     *    its type selector.
     *
     * After insertion, row events are attached and the entire field list
     * is renumbered.
     */
    function addFieldRow() {
      const index = tbody.querySelectorAll(".ff-field-row").length;

      /**
       * Preferred method: create the new row from the stored HTML template.
       */
      if (rowTemplate) {
        const html = rowTemplate.innerHTML.replace(/__INDEX__/g, index);
        const tmp = document.createElement("tbody");
        tmp.innerHTML = html;

        /**
         * Extract every table row generated by the template.
         */
        const rows = Array.from(tmp.querySelectorAll("tr"));

        /**
         * Append the generated rows to the live fields table.
         *
         * Editable field rows receive the same event handlers as rows that
         * existed when the page initially loaded.
         */
        rows.forEach(function (r) {
          tbody.appendChild(r);
          if (r.classList.contains("ff-field-row")) {
            attachRowEvents(r);
            syncTabRowState(r);
            syncFieldOptions(r);
          }
        });
      } else {
        /**
         * Fallback method: clone the final existing field row when no
         * dedicated row template is available.
         */
        const rows = tbody.querySelectorAll(".ff-field-row");
        const lastRow = rows[rows.length - 1] || null;
        if (!lastRow) return;

        /**
         * Clone the complete row including its child elements.
         */
        const newRow = lastRow.cloneNode(true);

        /**
         * Clear text-based values inherited from the cloned row.
         */
        newRow.querySelectorAll("input, textarea").forEach(function (el) {
          el.value = "";
        });

        /**
         * Reset the field type to the first available option.
         */
        const sel = newRow.querySelector("select.ff-field-type");
        if (sel) sel.selectedIndex = 0;

        /**
         * Insert the cloned row and initialize its event handlers.
         */
        tbody.appendChild(newRow);
        attachRowEvents(newRow);
      }

      /**
       * Reindex all fields after the new row has been inserted.
       */
      renumberRows();
    }

    /**
     * Bind the Add Field button.
     *
     * Prevent its normal browser action and create a new field row instead.
     */
    if (addBtn) {
      addBtn.addEventListener("click", function (e) {
        e.preventDefault();
        addFieldRow();
      });
    }

    /**
     * Synchronizes the field-name input with the currently selected field type.
     *
     * Tab fields do not use a normal field name, so when the field type is "tab":
     * - The field name is cleared.
     * - The placeholder is cleared.
     * - The input is made read-only.
     * - A disabled-state CSS class is applied.
     *
     * For all other field types, the input is restored to an editable state.
     *
     * @param {HTMLElement} row The field row whose state should be synchronized.
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

    /**
     * Show or hide individual options based on the selected field type.
     */
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
      }

      if (characterLimitOption) {
        characterLimitOption.style.display = characterLimitTypes.includes(type)
          ? ""
          : "none";
      }

      if (requiredOption) {
        requiredOption.style.display = type === "tab" ? "none" : "";
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

    /**
     * Stores the field row currently being dragged.
     *
     * A null value means no row is actively being reordered.
     */
    let draggingRow = null;
    let draggingSettingsRow = null;

    /**
     * Starts field-row drag-and-drop reordering.
     *
     * Dragging is only allowed when the event originated from an enabled
     * field drag handle. The owning row is then marked as the active
     * dragging row and configured for a move operation.
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
       * Set basic drag data so the browser recognizes the operation
       * as a valid HTML5 drag-and-drop interaction.
       */
      e.dataTransfer.setData("text/plain", "");
    });

    /**
     * Finishes field-row drag-and-drop reordering.
     *
     * The temporary dragging class is removed, the active drag reference
     * is cleared, and all rows are renumbered to match their new order.
     */
    tbody.addEventListener("dragend", function () {
      if (draggingRow) {
        draggingRow.classList.remove("ff-row-dragging");
      }

      draggingRow = null;
      draggingSettingsRow = null;

      renumberRows();
    });

    /**
     * Repositions the currently dragged row while it moves through the table.
     *
     * The pointer's vertical position is used to determine which field row
     * the dragged row should be inserted before. If no later row is found,
     * the dragged row is appended to the end of the table.
     */
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
     * Determines which field row should come after the currently dragged row.
     *
     * Every non-dragging field row is measured against the pointer's Y
     * position. The nearest row whose midpoint is still below the pointer
     * is selected as the insertion point.
     *
     * @param {HTMLElement} container The element containing the field rows.
     * @param {number} y The current vertical pointer coordinate.
     * @returns {HTMLElement|null} The row to insert before, or null if the
     * dragged row should be appended to the end.
     */
    function getDragAfterElement(container, y) {
      const rows = Array.from(
        container.querySelectorAll(".ff-field-row:not(.ff-row-dragging)"),
      );

      /**
       * Track the closest valid row found while comparing row positions.
       */
      let closest = { offset: Number.NEGATIVE_INFINITY, element: null };

      rows.forEach(function (row) {
        const box = row.getBoundingClientRect();
        const offset = y - box.top - box.height / 2;

        /**
         * Only consider rows whose midpoint is still below the pointer.
         * Among those rows, keep the closest one.
         */
        if (offset < 0 && offset > closest.offset) {
          closest = { offset: offset, element: row };
        }
      });

      return closest.element;
    }
  }

  /**
   * Initializes field-key copy controls.
   *
   * Each .ff-copy-key element reads the value stored in its data-key
   * attribute and prepares clipboard-related behavior when clicked.
   */
  document.querySelectorAll(".ff-copy-key").forEach(function (btn) {
    btn.addEventListener("click", function (e) {
      e.preventDefault();

      /**
       * Retrieve the key associated with the clicked copy control.
       */
      const key = this.dataset.key;
      if (!key) return;

      const self = this;

      /**
       * Defines a temporary visual success state for this copy button.
       *
       * The button receives the is-copied class and its tooltip changes
       * to "Copied!" before being restored after 1.5 seconds.
       */
      const done = () => {
        self.classList.add("is-copied");
        const oldTitle = self.getAttribute("title") || "";
        self.setAttribute("title", "Copied!");
        setTimeout(() => {
          self.classList.remove("is-copied");
          self.setAttribute("title", oldTitle || "Copy to clipboard");
        }, 1500);
      };

      /**
       * Attach the clipboard-copy implementation to all field-key controls.
       *
       * This handler first attempts to use the modern Clipboard API.
       * If that is unavailable or fails, it falls back to the older
       * document.execCommand("copy") approach.
       */
      document.querySelectorAll(".ff-copy-key").forEach(function (btn) {
        btn.addEventListener("click", async function (e) {
          e.preventDefault();

          const key = this.dataset.key;
          if (!key) return;

          const self = this;

          /**
           * Displays temporary success feedback after a key is copied.
           *
           * The copied-state CSS class is applied and the title is changed
           * to "Copied!" before the previous tooltip is restored.
           */
          function showCopiedState() {
            self.classList.add("is-copied");

            const oldTitle = self.getAttribute("title") || "";
            self.setAttribute("title", "Copied!");

            setTimeout(function () {
              self.classList.remove("is-copied");
              self.setAttribute("title", oldTitle || "Copy to clipboard");
            }, 1000);
          }

          /**
           * Provides a legacy clipboard fallback for environments where
           * navigator.clipboard.writeText is unavailable or fails.
           *
           * A temporary invisible textarea is created, populated with the
           * requested text, selected, copied, and then removed from the DOM.
           *
           * @param {string} text The text to copy to the clipboard.
           * @returns {boolean} Whether document.execCommand reported success.
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

            /**
             * Attempt the legacy copy operation and safely handle browsers
             * that reject or do not support the command.
             */
            try {
              copied = document.execCommand("copy");
            } catch (error) {
              copied = false;
            }

            document.body.removeChild(textarea);

            return copied;
          }

          /**
           * Prefer the modern asynchronous Clipboard API when available.
           */
          try {
            if (
              navigator.clipboard &&
              typeof navigator.clipboard.writeText === "function"
            ) {
              await navigator.clipboard.writeText(key);
              showCopiedState();
              return;
            }
          } catch (error) {}

          /**
           * Fall back to the legacy textarea-based copy approach.
           */
          if (fallbackCopy(key)) {
            showCopiedState();
          }
        });
      });
    });
  });

  /**
   * Applies a row-level focus class whenever focus enters the primary
   * table cell of a WordPress list-table row.
   *
   * This allows the entire row to receive a focused visual treatment
   * while one of its primary-cell controls is active.
   */
  document.addEventListener("focusin", function (e) {
    const td = e.target.closest("td.column-primary");
    if (td) {
      const tr = td.closest("tr");
      if (tr) tr.classList.add("ff-row-focus");
    }
  });

  /**
   * Removes the row-level focus class when focus leaves the primary
   * table cell.
   */
  document.addEventListener("focusout", function (e) {
    const td = e.target.closest("td.column-primary");
    if (td) {
      const tr = td.closest("tr");
      if (tr) tr.classList.remove("ff-row-focus");
    }
  });
});

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

  /**
   * Skip incomplete password controls.
   */
  if (!btn || !input || !icon) return;

  /**
   * Synchronizes the toggle button with the password input's visibility.
   *
   * When the password is visible:
   * - The button announces "Hide password".
   * - The visibility icon is shown.
   *
   * When the password is hidden:
   * - The button announces "Show password".
   * - The hidden icon is shown.
   */
  function sync() {
    const isVisible = input.type === "text";
    btn.setAttribute(
      "aria-label",
      isVisible ? "Hide password" : "Show password",
    );
    icon.classList.toggle("dashicons-visibility", isVisible);
    icon.classList.toggle("dashicons-hidden", !isVisible);
  }

  /**
   * Apply the correct icon and accessibility state on initial load.
   */
  sync();

  /**
   * Toggle between visible text and obscured password input modes.
   */
  btn.addEventListener("click", function () {
    input.type = input.type === "text" ? "password" : "text";
    sync();
  });
});

/**
 * Initializes paired range-slider and number-input controls.
 *
 * Changes to either control are immediately mirrored to the other
 * so both interfaces always display the same value.
 */
document.querySelectorAll(".ff-range-wrap").forEach(function (wrap) {
  const slider = wrap.querySelector(".ff-range-slider");
  const number = wrap.querySelector(".ff-range-number");

  /**
   * Skip wrappers that do not contain both required controls.
   */
  if (!slider || !number) return;

  /**
   * Copy range-slider changes into the number input.
   */
  slider.addEventListener("input", function () {
    number.value = slider.value;
  });

  /**
   * Copy number-input changes back into the range slider.
   */
  number.addEventListener("input", function () {
    slider.value = number.value;
  });
});

/**
 * Controls positioning of the Forge Fields admin subbar.
 *
 * The subbar is kept fixed beneath the WordPress admin bar and Forge Fields
 * brand bar. Its horizontal position also adjusts depending on whether the
 * WordPress admin menu is expanded or collapsed.
 */
(function () {
  const sub = document.querySelector(".ff-subbar");

  /**
   * Stop immediately on pages that do not include the Forge Fields subbar.
   */
  if (!sub) return;

  /**
   * Calculates and applies the subbar's current fixed position.
   *
   * The vertical offset is based on the combined heights of:
   * - The WordPress admin bar.
   * - The Forge Fields brand bar.
   *
   * The horizontal offset accounts for the WordPress admin menu width.
   *
   * The main wpcontent area also receives top padding so page content
   * does not become hidden underneath the fixed navigation elements.
   */
  function placeSubbar() {
    const adminBar = document.getElementById("wpadminbar");
    const brand = document.querySelector(".ff-brandbar");

    /**
     * Position the subbar directly below the admin and brand bars.
     */
    const top =
      (adminBar ? adminBar.offsetHeight : 0) + (brand ? brand.offsetHeight : 0);

    /**
     * WordPress uses a narrower sidebar when the admin menu is folded.
     */
    const left = document.body.classList.contains("folded") ? 56 : 180;

    /**
     * Apply the calculated fixed-position layout.
     */
    sub.style.position = "fixed";
    sub.style.top = top + "px";
    sub.style.left = left + "px";
    sub.style.right = 0;
    sub.style.width = "auto";
    sub.style.zIndex = 10;

    /**
     * Add enough top padding to wpcontent to account for the fixed
     * Forge Fields brand and subbar elements.
     */
    const wpcontent = document.getElementById("wpcontent");
    if (wpcontent && brand) {
      wpcontent.style.paddingTop = brand.offsetHeight + sub.offsetHeight + "px";
    }
  }

  /**
   * Recalculate the layout after all page resources have loaded.
   */
  window.addEventListener("load", placeSubbar);

  /**
   * Recalculate positioning whenever the browser window changes size.
   */
  window.addEventListener("resize", placeSubbar);

  /**
   * WordPress changes the sidebar width when its Collapse menu control
   * is clicked. Wait briefly for that layout transition, then reposition
   * the Forge Fields subbar.
   */
  document.body.addEventListener("click", (e) => {
    if (e.target.closest("#collapse-menu")) {
      setTimeout(placeSubbar, 200);
    }
  });

  /**
   * Apply the correct subbar position immediately when this script runs.
   */
  placeSubbar();
})();

/**
 * Self-contained confirmation modal controller for removing fields.
 *
 * This section manages:
 * - Single-field removal confirmation.
 * - Bulk-field removal confirmation.
 * - Modal open/close state.
 * - Keyboard accessibility and focus trapping.
 * - Restoring focus after the modal closes.
 * - Reindexing field rows after removals.
 */
(function () {
  /**
   * Main confirmation modal element.
   */
  const modal = document.getElementById("ff-confirm");

  /**
   * Stop immediately if the confirmation modal does not exist
   * on the current admin page.
   */
  if (!modal) return;

  /**
   * Confirmation dialog container.
   */
  const dlg = modal.querySelector(".ff-confirm__dialog");

  /**
   * Modal overlay element.
   */
  const overlay = modal.querySelector(".ff-confirm__overlay");

  /**
   * Confirmation button used to approve the pending removal.
   */
  const btnYes = modal.querySelector("#ff-confirm-yes");

  /**
   * Element used to display the name of the field being removed.
   */
  const nameEl = modal.querySelector(".ff-confirm__field-name");

  /**
   * Stores the single field row currently pending removal.
   *
   * A null value means no individual row is awaiting confirmation.
   */
  let pendingRow = null;

  /**
   * Stores multiple field rows awaiting bulk removal confirmation.
   */
  let pendingRows = [];

  /**
   * Stores the element that had focus before the confirmation modal opened.
   *
   * This allows focus to be restored when the modal closes.
   */
  let lastFocus = null;

  /**
   * Opens the confirmation modal for a single field row.
   *
   * The function:
   * - Stores the row awaiting removal.
   * - Remembers the currently focused element.
   * - Reads the field's label for display in the confirmation message.
   * - Makes the modal visible.
   * - Moves keyboard focus into the dialog.
   * - Enables modal keyboard handling.
   *
   * @param {HTMLElement} row The field row pending removal.
   */
  function openConfirm(row) {
    pendingRow = row;
    lastFocus = document.activeElement;

    /**
     * Find the label input belonging to this field so the confirmation
     * dialog can identify the field by name.
     */
    const labelInput = row.querySelector('input[name*="[label]"]');

    /**
     * Use the entered field label when available, otherwise fall back
     * to a generic description.
     */
    const label = (labelInput && labelInput.value.trim()) || "this field";

    /**
     * Insert the field label into the confirmation message.
     */
    nameEl.textContent = label;

    /**
     * Display the confirmation modal and move focus into the dialog.
     */
    modal.classList.remove("is-hidden");
    dlg.focus();

    /**
     * Enable keyboard handling while the modal is open.
     */
    document.addEventListener("keydown", onKeydown, true);
  }

  /**
   * Opens the confirmation modal for multiple selected field rows.
   *
   * The function:
   * - Clears any single-row pending state.
   * - Stores all selected rows.
   * - Remembers the currently focused element.
   * - Updates the modal title and description for bulk removal.
   * - Displays the number of selected fields.
   * - Opens the dialog and enables keyboard handling.
   *
   * @param {Iterable<HTMLElement>} rows The field rows pending bulk removal.
   */
  function openBulkConfirm(rows) {
    pendingRow = null;
    pendingRows = Array.from(rows);
    lastFocus = document.activeElement;

    /**
     * Retrieve the modal elements whose text changes for bulk removal.
     */
    const titleEl = modal.querySelector("#ff-confirm-title");
    const descEl = modal.querySelector("#ff-confirm-desc");

    /**
     * Change the title from the single-field wording to bulk wording.
     */
    titleEl.textContent = "Remove fields?";

    /**
     * Build the bulk-removal description dynamically.
     *
     * Singular/plural wording is adjusted based on the number
     * of selected rows.
     */
    descEl.innerHTML =
      "This will remove <strong>" +
      pendingRows.length +
      "</strong> selected field" +
      (pendingRows.length === 1 ? "" : "s") +
      " from the group.";

    /**
     * Display the confirmation modal and move focus into the dialog.
     */
    modal.classList.remove("is-hidden");
    dlg.focus();

    /**
     * Enable keyboard handling while the modal is open.
     */
    document.addEventListener("keydown", onKeydown, true);
  }

  /**
   * Closes and resets the confirmation modal.
   *
   * The function:
   * - Hides the modal.
   * - Removes its temporary keyboard listener.
   * - Restores focus to the element that was active before opening.
   * - Clears pending single and bulk row references.
   * - Restores the modal's default single-field wording.
   */
  function closeConfirm() {
    modal.classList.add("is-hidden");
    document.removeEventListener("keydown", onKeydown, true);

    /**
     * Return keyboard focus to the control the user was interacting with
     * before the confirmation dialog opened.
     */
    if (lastFocus && typeof lastFocus.focus === "function") {
      lastFocus.focus();
    }

    /**
     * Clear all pending removal state.
     */
    pendingRow = null;
    pendingRows = [];

    /**
     * Retrieve the modal title and description so their default
     * single-field wording can be restored.
     */
    const titleEl = modal.querySelector("#ff-confirm-title");
    const descEl = modal.querySelector("#ff-confirm-desc");

    /**
     * Restore the default single-field title.
     */
    titleEl.textContent = "Remove field?";

    /**
     * Restore the default single-field description.
     */
    descEl.innerHTML =
      'This will remove <strong class="ff-confirm__field-name">this field</strong> from the group.';
  }

  /**
   * Handles keyboard interaction while the confirmation modal is open.
   *
   * Supported behavior:
   * - Escape closes the modal.
   * - Tab cycles forward through focusable controls.
   * - Shift+Tab cycles backward through focusable controls.
   *
   * Focus is trapped inside the dialog so keyboard users cannot
   * accidentally move focus into the page behind the modal.
   *
   * @param {KeyboardEvent} e The captured keyboard event.
   */
  function onKeydown(e) {
    /**
     * Close the confirmation modal when Escape is pressed.
     */
    if (e.key === "Escape") {
      e.preventDefault();
      closeConfirm();
    }

    /**
     * Trap Tab navigation inside the modal dialog.
     */
    if (e.key === "Tab") {
      /**
       * Find all potentially focusable controls inside the dialog.
       */
      const focusable = dlg.querySelectorAll(
        'button,[href],input,select,textarea,[tabindex]:not([tabindex="-1"])',
      );

      /**
       * Convert the NodeList to an array and remove controls that are
       * disabled or currently not visible.
       */
      const list = Array.prototype.slice
        .call(focusable)
        .filter((el) => !el.disabled && el.offsetParent !== null);

      /**
       * There is nothing to trap if the dialog contains no focusable elements.
       */
      if (!list.length) return;

      /**
       * Identify the first and last focusable controls in the dialog.
       */
      const first = list[0];
      const last = list[list.length - 1];

      /**
       * When Shift+Tab is pressed on the first control, wrap focus
       * around to the final control.
       */
      if (e.shiftKey && document.activeElement === first) {
        e.preventDefault();
        last.focus();

        /**
         * When Tab is pressed on the final control, wrap focus
         * back to the first control.
         */
      } else if (!e.shiftKey && document.activeElement === last) {
        e.preventDefault();
        first.focus();
      }
    }
  }

  /**
   * Reindexes all editable field rows after rows have been removed.
   *
   * Each remaining field row receives a new sequential data-index,
   * and every submitted field control is updated so its name uses
   * the same new ff_fields[index] position.
   *
   * Example:
   *
   * ff_fields[0][label]
   * ff_fields[1][label]
   * ff_fields[2][label]
   *
   * If the middle field is removed, the remaining rows are rewritten
   * so the indexes remain contiguous.
   */
  function reindexFieldRows() {
    /**
     * Retrieve every remaining editable field row.
     */
    const rows = document.querySelectorAll("#ff-fields-body tr.ff-field-row");

    /**
     * Assign each row its new zero-based position.
     */
    rows.forEach((row, index) => {
      row.dataset.index = index;

      /**
       * Find all submitted inputs, selects, and textareas belonging
       * to this field row.
       */
      const inputs = row.querySelectorAll(
        'input[name^="ff_fields["], select[name^="ff_fields["], textarea[name^="ff_fields["]',
      );

      /**
       * Rewrite the ff_fields[...] portion of each control name so
       * it matches the row's new position.
       */
      inputs.forEach((input) => {
        input.name = input.name.replace(
          /ff_fields\[\d+]/,
          "ff_fields[" + index + "]",
        );
      });
    });
  }

  /**
   * Submits the field-group edit form.
   *
   * Before submission, this function ensures that the hidden
   * ff_save_field_group flag exists so the server-side save handler
   * recognizes the request as a field-group save operation.
   */
  function submitFieldGroupForm() {
    const form = document.getElementById("ff-edit-form");

    /**
     * Stop if the field-group edit form is not present.
     */
    if (!form) return;

    /**
     * Look for the hidden save flag that may already exist in the form.
     */
    let saveInput = form.querySelector('input[name="ff_save_field_group"]');

    /**
     * Create the save flag when it does not already exist.
     */
    if (!saveInput) {
      saveInput = document.createElement("input");
      saveInput.type = "hidden";
      saveInput.name = "ff_save_field_group";
      saveInput.value = "1";

      form.appendChild(saveInput);
    }

    /**
     * Submit the field-group form using the browser's native form submission.
     */
    form.submit();
  }

  /**
   * Handles confirmation of either:
   * - A bulk field removal.
   * - A normal single-field removal.
   *
   * After the requested removal is processed, remaining field rows are
   * reindexed and the field-group form is submitted automatically.
   */
  btnYes.addEventListener("click", function () {
    const tbody = document.querySelector("#ff-fields-body");

    /**
     * If the fields table does not exist, close the modal and stop.
     */
    if (!tbody) {
      closeConfirm();
      return;
    }

    /**
     * Bulk removal
     *
     * When pendingRows contains one or more selected rows, remove each
     * selected field and its associated settings row.
     */
    if (pendingRows.length) {
      pendingRows.forEach(function (row) {
        /**
         * Also remove the settings row belonging to this field,
         * if one exists.
         */
        const index = row.dataset.index;

        const settingsRow = tbody.querySelector(
          '.ff-field-settings[data-index="' + index + '"]',
        );

        if (settingsRow) {
          settingsRow.remove();
        }

        /**
         * Remove the editable field row itself.
         */
        row.remove();
      });

      /**
       * Forge Fields always keeps at least one editable row.
       *
       * If bulk removal deleted every field row, trigger the existing
       * Add Field control so a fresh editable row is created.
       */
      if (!tbody.querySelector(".ff-field-row")) {
        const addBtn = document.getElementById("ff-add-field");

        if (addBtn) {
          addBtn.click();
        }
      }

      /**
       * Reindex all remaining fields so their submitted array indexes
       * match their current order.
       */
      reindexFieldRows();

      /**
       * Save the modified field group immediately.
       */
      submitFieldGroupForm();

      /**
       * Close and reset the confirmation modal.
       */
      closeConfirm();

      return;
    }

    /**
     * Normal single-field removal
     */
    if (!pendingRow) {
      closeConfirm();
      return;
    }

    /**
     * Retrieve the complete current field-row collection.
     */
    const rows = tbody.querySelectorAll(".ff-field-row");

    /**
     * Read the pending field's current index so its associated settings
     * row can be located.
     */
    const index = pendingRow.dataset.index;

    const settingsRow = tbody.querySelector(
      '.ff-field-settings[data-index="' + index + '"]',
    );

    /**
     * If only one editable field row remains, do not remove the row itself.
     *
     * Instead, clear its values and reset it so Forge Fields continues
     * to provide at least one editable row.
     */
    if (rows.length === 1) {
      pendingRow.querySelectorAll("input").forEach(function (input) {
        /**
         * Clear standard input values while leaving checkbox handling unchanged.
         */
        if (input.type !== "checkbox") {
          input.value = "";
        }
      });

      /**
       * Reset the field type selector to its first option.
       */
      const typeSelect = pendingRow.querySelector(".ff-field-type");

      if (typeSelect) {
        typeSelect.selectedIndex = 0;
      }

      /**
       * Hide the associated settings row instead of removing it when
       * the final editable field row is being reset.
       */
      if (settingsRow) {
        settingsRow.style.display = "none";
        settingsRow.classList.add("is-hidden");
      }
    } else {
      /**
       * When multiple field rows exist, remove the associated settings
       * row completely.
       */
      if (settingsRow) {
        settingsRow.remove();
      }

      /**
       * Remove the selected field row.
       */
      pendingRow.remove();
    }

    /**
     * Reindex the remaining rows after the single-field removal/reset.
     */
    reindexFieldRows();

    /**
     * Save the updated field group.
     */
    submitFieldGroupForm();

    /**
     * Close and reset the confirmation modal.
     */
    closeConfirm();
  });

  /**
   * Allows modal controls marked with data-ff-close to close the
   * confirmation dialog.
   */
  modal.addEventListener("click", function (e) {
    if (e.target.hasAttribute("data-ff-close")) {
      e.preventDefault();
      closeConfirm();
    }
  });

  /**
   * Captures clicks on field removal controls anywhere in the document.
   *
   * Event delegation is used so dynamically created field rows work
   * without needing separate remove-button listeners.
   */
  document.addEventListener(
    "click",
    function (e) {
      /**
       * Detect either supported field removal control class.
       */
      const removeBtn = e.target.closest(".ff-field-remove, .ff-remove-field");
      if (!removeBtn) return;

      /**
       * Find the editable field row containing the clicked remove control.
       */
      const row = removeBtn.closest("tr.ff-field-row");
      if (!row) return;

      /**
       * Prevent the control's normal action and stop other click handlers
       * from processing this removal request.
       */
      e.preventDefault();
      e.stopPropagation();
      if (e.stopImmediatePropagation) e.stopImmediatePropagation();

      /**
       * Open the single-field confirmation modal for this row.
       */
      openConfirm(row);
    },
    true,
  );

  /**
   * Retrieve all Select All controls used for field selection.
   *
   * More than one may exist, such as controls at both the top and
   * bottom of the field table.
   */
  const selectAllFields = document.querySelectorAll(".ff-select-all-fields");

  /**
   * Synchronizes all Select All checkboxes with the current state
   * of the individual field-selection checkboxes.
   *
   * States:
   * - All fields selected: Select All is checked.
   * - Some fields selected: Select All is indeterminate.
   * - No fields selected: Select All is unchecked.
   */
  function syncSelectAllFields() {
    /**
     * Retrieve every individual field-selection checkbox.
     */
    const fieldCheckboxes = Array.from(
      document.querySelectorAll("#ff-fields-body .ff-field-select"),
    );

    /**
     * Count how many individual field checkboxes are currently selected.
     */
    const checkedCount = fieldCheckboxes.filter(function (checkbox) {
      return checkbox.checked;
    }).length;

    /**
     * Apply the calculated aggregate state to every Select All control.
     */
    selectAllFields.forEach(function (selectAll) {
      /**
       * Check Select All only when at least one field exists and every
       * field checkbox is selected.
       */
      selectAll.checked =
        fieldCheckboxes.length > 0 && checkedCount === fieldCheckboxes.length;

      /**
       * Show the indeterminate state when some, but not all, fields
       * are currently selected.
       */
      selectAll.indeterminate =
        checkedCount > 0 && checkedCount < fieldCheckboxes.length;
    });
  }

  /**
   * Top or bottom Select All checkbox.
   *
   * Each Select All control applies its checked state to every individual
   * field-selection checkbox in the table.
   */
  selectAllFields.forEach(function (selectAll) {
    selectAll.addEventListener("change", function () {
      /**
       * Store the current Select All state so it can be applied
       * consistently to all individual field checkboxes.
       */
      const checked = selectAll.checked;

      /**
       * Apply the Select All state to every individual field checkbox.
       */
      document
        .querySelectorAll("#ff-fields-body .ff-field-select")
        .forEach(function (checkbox) {
          checkbox.checked = checked;
        });

      /**
       * Keep both Select All checkboxes synchronized.
       *
       * Any additional Select All control receives the same checked state,
       * and its indeterminate state is cleared because the selection is
       * now explicitly all-or-none.
       */
      selectAllFields.forEach(function (otherSelectAll) {
        otherSelectAll.checked = checked;
        otherSelectAll.indeterminate = false;
      });
    });
  });

  /**
   * Individual field checkbox changed.
   *
   * When one field-selection checkbox changes, recalculate the aggregate
   * state of the Select All controls.
   */
  document.addEventListener("change", function (e) {
    if (!e.target.classList.contains("ff-field-select")) {
      return;
    }

    syncSelectAllFields();
  });

  /**
   * Bulk-action selector containing the operation to perform
   * on the currently selected field rows.
   */
  const bulkAction = document.getElementById("ff-field-bulk-action");

  /**
   * Button used to apply the currently selected bulk action.
   */
  const bulkApply = document.getElementById("ff-apply-field-bulk-action");

  /**
   * Apply bulk action.
   *
   * This handler currently processes the "remove" bulk action.
   * It collects all selected field rows and passes them to the
   * bulk-removal confirmation modal.
   */
  if (bulkApply) {
    bulkApply.addEventListener("click", function () {
      /**
       * Stop unless a valid bulk-action selector exists and the
       * selected action is field removal.
       */
      if (!bulkAction || bulkAction.value !== "remove") {
        return;
      }

      /**
       * Find every checked field-selection checkbox, convert each one
       * into its owning field row, and discard any missing row references.
       */
      const selectedRows = Array.from(
        document.querySelectorAll("#ff-fields-body .ff-field-select:checked"),
      )
        .map(function (checkbox) {
          return checkbox.closest(".ff-field-row");
        })
        .filter(Boolean);

      /**
       * Do nothing when no field rows are selected.
       */
      if (!selectedRows.length) {
        return;
      }

      /**
       * Open the bulk-removal confirmation modal using the selected rows.
       */
      openBulkConfirm(selectedRows);
    });
  }
})();

/**
 * Adjusts the WordPress HTML/Text editor textarea height inside
 * Forge Fields option cards.
 *
 * When the user switches a WordPress editor into HTML mode, the
 * corresponding textarea is forced to a consistent 260px height.
 */
document.addEventListener("click", function (e) {
  /**
   * Detect the WordPress HTML/Text editor switch inside a Forge Fields
   * options card.
   */
  const btn = e.target.closest(
    ".ff-options-card .wp-switch-editor.switch-html",
  );

  if (!btn) return;

  /**
   * Find the WordPress editor wrapper belonging to the clicked switch.
   */
  const wrap = btn.closest(".wp-editor-wrap");

  if (!wrap) return;

  /**
   * Locate the underlying WordPress editor textarea.
   */
  const ta = wrap.querySelector("textarea.wp-editor-area");

  /**
   * Apply a consistent minimum and explicit height when the textarea exists.
   */
  if (ta) {
    ta.style.minHeight = "260px";
    ta.style.height = "260px";
  }
});

/**
 * Initializes delegated jQuery synchronization for Forge Fields
 * range sliders and their associated numeric inputs.
 *
 * data-target attributes are used to locate the paired control.
 */
jQuery(function ($) {
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
 * Initializes conditional field-group location controls after the DOM loads.
 *
 * The target selectors shown to the user depend on the selected location:
 * - "page" shows the page target control.
 * - "post" shows the post target control.
 * - Any other value hides both target controls.
 */
document.addEventListener("DOMContentLoaded", function () {
  /**
   * Main location selector.
   */
  const locationSelect = document.getElementById("ff_location");

  /**
   * Wrapper containing the page-target selector.
   */
  const pageWrap = document.getElementById("ff_location_target_page_wrap");

  /**
   * Wrapper containing the post-target selector.
   */
  const postWrap = document.getElementById("ff_location_target_post_wrap");

  /**
   * Stop if the required location controls are not present
   * on the current admin screen.
   */
  if (!locationSelect || !pageWrap || !postWrap) return;

  /**
   * Synchronizes the visible target controls with the current
   * field-group location selection.
   */
  function syncLocationTargets() {
    const val = locationSelect.value;

    /**
     * Page location:
     * Show the page target selector and hide the post target selector.
     */
    if (val === "page") {
      pageWrap.style.display = "";
      postWrap.style.display = "none";

      /**
       * Post location:
       * Hide the page target selector and show the post target selector.
       */
    } else if (val === "post") {
      pageWrap.style.display = "none";
      postWrap.style.display = "";

      /**
       * Any other location:
       * Hide both target-specific selectors.
       */
    } else {
      pageWrap.style.display = "none";
      postWrap.style.display = "none";
    }
  }

  /**
   * Refresh target visibility whenever the location selection changes.
   */
  locationSelect.addEventListener("change", syncLocationTargets);

  /**
   * Apply the correct target visibility immediately on page load.
   */
  syncLocationTargets();
});

/**
 * Initializes reusable Forge Fields tab interfaces after the DOM loads.
 *
 * Each element marked with data-ff-tabs operates independently and
 * contains its own tab buttons and matching tab panels.
 */
document.addEventListener("DOMContentLoaded", function () {
  /**
   * Initialize every Forge Fields tab container on the page.
   */
  document.querySelectorAll("[data-ff-tabs]").forEach(function (tabsWrap) {
    /**
     * Retrieve all tab buttons belonging to this tab group.
     */
    const buttons = tabsWrap.querySelectorAll(".ff-tab-button");

    /**
     * Retrieve all tab panels belonging to this tab group.
     */
    const panels = tabsWrap.querySelectorAll(".ff-tab-panel");

    /**
     * Skip incomplete tab groups that do not contain both
     * buttons and panels.
     */
    if (!buttons.length || !panels.length) return;

    /**
     * Bind activation behavior to every tab button.
     */
    buttons.forEach(function (btn) {
      btn.addEventListener("click", function () {
        /**
         * Read the identifier of the panel this button controls.
         */
        const target = btn.getAttribute("data-ff-tab");

        if (target === null) return;

        /**
         * Remove the active state from every button in this tab group.
         */
        buttons.forEach(function (b) {
          b.classList.remove("is-active");
        });

        /**
         * Remove the active state from every panel in this tab group.
         */
        panels.forEach(function (panel) {
          panel.classList.remove("is-active");
        });

        /**
         * Mark the clicked button as active.
         */
        btn.classList.add("is-active");

        /**
         * Locate the panel whose data-ff-tab-panel value matches
         * the clicked button's data-ff-tab value.
         */
        const panel = tabsWrap.querySelector(
          '[data-ff-tab-panel="' + target + '"]',
        );

        /**
         * Activate the matching panel when one exists.
         */
        if (panel) {
          panel.classList.add("is-active");
        }
      });
    });
  });
  /**
   * Prevent Page/Post updates when a required Forge Field is empty.
   */
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
