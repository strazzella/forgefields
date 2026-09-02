document.addEventListener("DOMContentLoaded", function () {
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
      });
    }

    function attachRowEvents(row) {
      const labelInput = row.querySelector(".ff-field-label");
      const nameInput = row.querySelector(".ff-field-name");
      const removeLink = row.querySelector(".ff-field-remove");
      const typeSelect = row.querySelector(".ff-field-type");

      if (typeSelect) {
        typeSelect.addEventListener("change", function () {
          syncTabRowState(row);
        });
      }

      if (labelInput) {
        labelInput.addEventListener("blur", function () {
          if (!nameInput) return;

          const typeSelect = row.querySelector(".ff-field-type");
          const isTab = typeSelect && typeSelect.value === "tab";

          if (isTab) {
            nameInput.value = "";
            return;
          }

          if (nameInput.value.trim() !== "") return;
          if (this.value.trim() === "") return;

          nameInput.value = labelToSlug(this.value);
        });
      }
    }

    tbody.querySelectorAll(".ff-field-row").forEach(function (row) {
      attachRowEvents(row);
      syncTabRowState(row);
    });
    renumberRows();

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

    let draggingRow = null;

    tbody.addEventListener("dragstart", function (e) {
      const handle = e.target.closest(".ff-field-handle");
      if (!handle || !handle.draggable) {
        return;
      }

      const row = handle.closest(".ff-field-row");
      if (!row) return;

      draggingRow = row;
      row.classList.add("ff-row-dragging");
      e.dataTransfer.effectAllowed = "move";

      e.dataTransfer.setData("text/plain", "");
    });

    tbody.addEventListener("dragend", function () {
      if (draggingRow) {
        draggingRow.classList.remove("ff-row-dragging");
        draggingRow = null;
        renumberRows();
      }
    });

    tbody.addEventListener("dragover", function (e) {
      if (!draggingRow) return;

      e.preventDefault();

      const afterElement = getDragAfterElement(tbody, e.clientY);
      if (!afterElement) {
        tbody.appendChild(draggingRow);
      } else if (afterElement !== draggingRow) {
        tbody.insertBefore(draggingRow, afterElement);
      }
    });

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

  document.querySelectorAll(".ff-copy-key").forEach(function (btn) {
    btn.addEventListener("click", function (e) {
      e.preventDefault();
      const key = this.dataset.key;
      if (!key) return;

      const self = this;
      const done = () => {
        self.classList.add("is-copied");
        const oldTitle = self.getAttribute("title") || "";
        self.setAttribute("title", "Copied!");
        setTimeout(() => {
          self.classList.remove("is-copied");
          self.setAttribute("title", oldTitle || "Copy to clipboard");
        }, 1500);
      };

      document.querySelectorAll(".ff-copy-key").forEach(function (btn) {
        btn.addEventListener("click", async function (e) {
          e.preventDefault();

          const key = this.dataset.key;
          if (!key) return;

          const self = this;

          function showCopiedState() {
            self.classList.add("is-copied");

            const oldTitle = self.getAttribute("title") || "";
            self.setAttribute("title", "Copied!");

            setTimeout(function () {
              self.classList.remove("is-copied");
              self.setAttribute("title", oldTitle || "Copy to clipboard");
            }, 1000);
          }

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
          } catch (error) {}

          if (fallbackCopy(key)) {
            showCopiedState();
          }
        });
      });
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

document.querySelectorAll(".ff-range-wrap").forEach(function (wrap) {
  const slider = wrap.querySelector(".ff-range-slider");
  const number = wrap.querySelector(".ff-range-number");

  if (!slider || !number) return;

  slider.addEventListener("input", function () {
    number.value = slider.value;
  });

  number.addEventListener("input", function () {
    slider.value = number.value;
  });
});

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

  document.body.addEventListener("click", (e) => {
    if (e.target.closest("#collapse-menu")) {
      setTimeout(placeSubbar, 200);
    }
  });

  placeSubbar();
})();

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

  btnYes.addEventListener("click", function () {
    const tbody = document.querySelector("#ff-fields-body");

    if (!tbody) {
      closeConfirm();
      return;
    }

    /*
     * Bulk removal
     */
    if (pendingRows.length) {
      pendingRows.forEach(function (row) {
        /*
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

        row.remove();
      });

      /*
       * Forge Fields always keeps at least one editable row.
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

    /*
     * Normal single-field removal
     */
    if (!pendingRow) {
      closeConfirm();
      return;
    }

    const rows = tbody.querySelectorAll(".ff-field-row");

    const index = pendingRow.dataset.index;

    const settingsRow = tbody.querySelector(
      '.ff-field-settings[data-index="' + index + '"]',
    );

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

  const selectAllFields = document.getElementById("ff-select-all-fields");

  const bulkAction = document.getElementById("ff-field-bulk-action");

  const bulkApply = document.getElementById("ff-apply-field-bulk-action");

  /*
   * Select/deselect every field.
   */
  if (selectAllFields) {
    selectAllFields.addEventListener("change", function () {
      document
        .querySelectorAll("#ff-fields-body .ff-field-select")
        .forEach(function (checkbox) {
          checkbox.checked = selectAllFields.checked;
        });
    });
  }

  /*
   * Keep Select All state accurate.
   */
  document.addEventListener("change", function (e) {
    if (!e.target.classList.contains("ff-field-select")) {
      return;
    }

    const checkboxes = Array.from(
      document.querySelectorAll("#ff-fields-body .ff-field-select"),
    );

    if (!selectAllFields || !checkboxes.length) {
      return;
    }

    const checked = checkboxes.filter(function (checkbox) {
      return checkbox.checked;
    });

    selectAllFields.checked = checked.length === checkboxes.length;

    selectAllFields.indeterminate =
      checked.length > 0 && checked.length < checkboxes.length;
  });

  /*
   * Apply bulk action.
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

jQuery(function ($) {
  $(document).on("input change", ".ff-range-slider", function () {
    var $num = $($(this).data("target"));
    if ($num.length) $num.val(this.value);
  });

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

(function ($) {
  if (window.ffMediaBound) {
    return;
  }
  window.ffMediaBound = true;

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
