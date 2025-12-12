document.addEventListener("DOMContentLoaded", function () {
    // -------------------------------
    // Helpers
    // -------------------------------
    function labelToSlug(text) {
        return text
            .trim()
            .toLowerCase()
            .replace(/[^a-z0-9\s]/g, "")
            .replace(/\s+/g, "_");
    }

    const tbody = document.querySelector("#ff-fields-body");
    const addBtn = document.querySelector("#ff-add-field");

    // Build select HTML with a non-selectable “Basic” group for brand-new rows
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
            '<option value="url">Url</option>' +
            '<option value="range">Range</option>' +
            '<option value="password">Password</option>' +
            "</optgroup>" +
            '<optgroup label="Content">' +
            '<option value="image">Image</option>' +
            '<option value="file">File</option>' +
            '<option value="wysiwyg">WYSIWYG Editor</option>' +
            "</optgroup>" +
            "</select>" +
            "</div>"
        );
    }

    // -------------------------------
    // FIELD BUILDER (only on edit screen)
    // -------------------------------
    if (tbody) {
        // -------------------------------
        // Re-index rows (names + data-index)
        // -------------------------------
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

                // Handle becomes the draggable element
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

        // -------------------------------
        // Per-row setup
        // -------------------------------
        function attachRowEvents(row) {
            const labelInput = row.querySelector(".ff-field-label");
            const nameInput = row.querySelector(".ff-field-name");
            const removeLink = row.querySelector(".ff-field-remove");

            // Auto label → slug
            if (labelInput) {
                labelInput.addEventListener("blur", function () {
                    if (!nameInput) return;

                    if (nameInput.value.trim() !== "") return;
                    if (this.value.trim() === "") return;

                    nameInput.value = labelToSlug(this.value);
                });
            }
        }

        // Attach to any existing rows
        tbody.querySelectorAll(".ff-field-row").forEach(function (row) {
            attachRowEvents(row);
        });
        renumberRows();

        // Create a brand-new, empty field row when there are none to clone
        function createEmptyRow() {
            const row = document.createElement("tr");
            row.className = "ff-field-row";

            row.innerHTML = `
        <td class="ff-field-handle">≡</td>
        <td>
            <input type="text"
                   class="regular-text ff-field-label"
                   placeholder="Field Label">
        </td>
        <td>
            <input type="text"
                   class="regular-text ff-field-name"
                   placeholder="field_name">
        </td>
        <td>
            <select class="ff-field-type">
                <optgroup label="Basic">
                    <option value="text">Text</option>
                    <option value="textarea">Textarea</option>
                    <option value="number">Number</option>
                    <option value="email">Email</option>
                    <option value="url">URL</option>
                    <option value="range">Range</option>
                    <option value="password">Password</option>
                </optgroup>
                <optgroup label="Content">
                    <option value="image">Image</option>
                    <option value="file">File</option>
                    <option value="wysiwyg">WYSIWYG Editor</option>
                </optgroup>
            </select>
        </td>
        <td>
            <a href="#" class="ff-field-remove">Remove</a>
        </td>
    `;

            return row;
        }

        // -------------------------------
        // Add new field
        // -------------------------------
        if (addBtn) {
            addBtn.addEventListener("click", function (e) {
                e.preventDefault();

                const lastRow = tbody.querySelector(".ff-field-row:last-child");
                let newRow;

                if (lastRow) {
                    // Clone last row
                    newRow = lastRow.cloneNode(true);

                    // Clear values in the clone
                    const inputs = newRow.querySelectorAll("input, select");
                    inputs.forEach(function (el) {
                        if (el.tagName === "INPUT") {
                            el.value = "";
                        } else if (el.tagName === "SELECT") {
                            el.selectedIndex = 0;
                        }
                    });
                } else {
                    // No rows exist yet – create a fresh empty one
                    newRow = createEmptyRow();
                }

                tbody.appendChild(newRow);
                attachRowEvents(newRow);
                renumberRows();
            });
        }

        // -------------------------------
        // Drag & drop ordering (HTML5)
        // -------------------------------
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
            // Required for Firefox
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
                container.querySelectorAll(
                    ".ff-field-row:not(.ff-row-dragging)"
                )
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
    } // end if (tbody)

    // -------------------------------
    // Copy group key button (runs on any screen)
    // -------------------------------
    // Copy group key button (runs on any screen)
    document.querySelectorAll(".ff-copy-key").forEach(function (btn) {
        btn.addEventListener("click", function (e) {
            e.preventDefault();
            const key = this.dataset.key;
            if (!key) return;

            const self = this;
            const done = () => {
                self.classList.add("is-copied"); // show check icon
                const oldTitle = self.getAttribute("title") || "";
                self.setAttribute("title", "Copied!");
                setTimeout(() => {
                    self.classList.remove("is-copied"); // revert icon
                    self.setAttribute("title", oldTitle || "Copy to clipboard");
                }, 1500);
            };

            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard
                    .writeText(key)
                    .then(done)
                    .catch(function () {
                        window.prompt("Copy field group key:", key);
                    });
            } else {
                window.prompt("Copy field group key:", key);
            }
        });
    });

    // -------------------------------
    // Keep row-actions visible when keyboard focuses title cell
    // -------------------------------
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

// -------------------------------
// Password show/hide toggle (meta boxes)
// -------------------------------
document.querySelectorAll(".ff-password-wrap").forEach(function (wrap) {
    const btn = wrap.querySelector(".ff-password-toggle");
    const input = wrap.querySelector(".ff-password-input");
    const icon = btn ? btn.querySelector(".dashicons") : null;
    if (!btn || !input || !icon) return;

    // Mirror the current input type (password by default) to the icon/label
    function sync() {
        const isVisible = input.type === "text";
        btn.setAttribute(
            "aria-label",
            isVisible ? "Hide password" : "Show password"
        );
        icon.classList.toggle("dashicons-visibility", isVisible); // open eye when visible
        icon.classList.toggle("dashicons-hidden", !isVisible); // slashed eye when masked
    }

    sync(); // defaults to slashed eye because inputs start as type="password"

    btn.addEventListener("click", function () {
        input.type = input.type === "text" ? "password" : "text";
        sync();
    });
});

// -------------------------------
// Range slider <-> number sync
// -------------------------------
document.querySelectorAll(".ff-range-wrap").forEach(function (wrap) {
    const slider = wrap.querySelector(".ff-range-slider");
    const number = wrap.querySelector(".ff-range-number");

    if (!slider || !number) return;

    // slider -> number
    slider.addEventListener("input", function () {
        number.value = slider.value;
    });

    // number -> slider
    number.addEventListener("input", function () {
        slider.value = number.value;
    });
});

// Forge Fields: only adjust the SUBBAR position.
// Brandbar is untouched.
(function () {
    const sub = document.querySelector(".ff-subbar");
    if (!sub) return;

    function placeSubbar() {
        // keep it fixed and just calculate offsets
        const adminBar = document.getElementById("wpadminbar");
        const brand = document.querySelector(".ff-brandbar");

        const top =
            (adminBar ? adminBar.offsetHeight : 0) +
            (brand ? brand.offsetHeight : 0);

        // match WP left gutter; user wanted 180px when menu is expanded
        // use the "folded" class to handle collapsed admin menu automatically
        const left = document.body.classList.contains("folded") ? 56 : 180;

        sub.style.position = "fixed";
        sub.style.top = top + "px";
        sub.style.left = left + "px";
        sub.style.right = 0;
        sub.style.width = "auto";
        sub.style.zIndex = 10;

        // keep content from sliding under the bars (optional – only touches top padding)
        const wpcontent = document.getElementById("wpcontent");
        if (wpcontent && brand) {
            wpcontent.style.paddingTop =
                brand.offsetHeight + sub.offsetHeight + "px";
        }
    }

    window.addEventListener("load", placeSubbar);
    window.addEventListener("resize", placeSubbar);
    // recalc when the admin menu is collapsed/expanded
    document.body.addEventListener("click", (e) => {
        if (e.target.closest("#collapse-menu")) {
            setTimeout(placeSubbar, 200);
        }
    });

    placeSubbar();
})();

// MODAL
(function () {
    const modal = document.getElementById("ff-confirm");
    if (!modal) return;

    const dlg = modal.querySelector(".ff-confirm__dialog");
    const overlay = modal.querySelector(".ff-confirm__overlay");
    const btnYes = modal.querySelector("#ff-confirm-yes");
    const nameEl = modal.querySelector(".ff-confirm__field-name");

    let pendingRow = null;
    let lastFocus = null;

    // Open modal for given row
    function openConfirm(row) {
        pendingRow = row;
        lastFocus = document.activeElement;
        // Try to show label/name in the message
        const labelInput = row.querySelector('input[name*="[label]"]');
        const label = (labelInput && labelInput.value.trim()) || "this field";
        nameEl.textContent = label;

        modal.classList.remove("is-hidden");
        // focus trap starts on dialog
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
    }

    function onKeydown(e) {
        if (e.key === "Escape") {
            e.preventDefault();
            closeConfirm();
        }
        // Basic focus trap
        if (e.key === "Tab") {
            const focusable = dlg.querySelectorAll(
                'button,[href],input,select,textarea,[tabindex]:not([tabindex="-1"])'
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

    // Reindex names after a delete so PHP receives a clean array
    function reindexFieldRows() {
        const rows = document.querySelectorAll(
            "#ff-fields-body tr.ff-field-row"
        );
        rows.forEach((row, index) => {
            row.dataset.index = index;
            // rename inputs: ff_fields[<n>][label|name|type]
            const inputs = row.querySelectorAll(
                'input[name^="ff_fields["], select[name^="ff_fields["], textarea[name^="ff_fields["]'
            );
            inputs.forEach((input) => {
                input.name = input.name.replace(
                    /ff_fields\[\d+]/,
                    "ff_fields[" + index + "]"
                );
            });
        });
    }

    // Confirm delete
    btnYes.addEventListener("click", function () {
        if (!pendingRow) {
            closeConfirm();
            return;
        }

        const tbody = document.querySelector("#ff-fields-body");
        const rows = tbody ? tbody.querySelectorAll(".ff-field-row") : [];

        if (rows.length === 1) {
            // last row: clear inputs + reset select
            pendingRow.querySelectorAll("input").forEach((input) => {
                input.value = "";
            });
            const typeSelect = pendingRow.querySelector(".ff-field-type");
            if (typeSelect) typeSelect.selectedIndex = 0;
        } else {
            // remove the row
            pendingRow.parentNode.removeChild(pendingRow);
        }

        // reindex names
        reindexFieldRows();

        // auto-save (mirrors your old behavior)
        const form = document.getElementById("ff-edit-form");
        if (form) {
            let saveInput = form.querySelector(
                'input[name="ff_save_field_group"]'
            );
            if (!saveInput) {
                saveInput = document.createElement("input");
                saveInput.type = "hidden";
                saveInput.name = "ff_save_field_group";
                saveInput.value = "1";
                form.appendChild(saveInput);
            }
            form.submit();
        }

        closeConfirm();
    });

    // Close handlers
    modal.addEventListener("click", function (e) {
        if (e.target.hasAttribute("data-ff-close")) {
            e.preventDefault();
            closeConfirm();
        }
    });

    // Delegate click from any Remove link in the table
    document.addEventListener(
        "click",
        function (e) {
            const removeBtn = e.target.closest(
                ".ff-field-remove, .ff-remove-field"
            );
            if (!removeBtn) return;

            const row = removeBtn.closest("tr.ff-field-row");
            if (!row) return;

            // prevent any legacy listeners from firing
            e.preventDefault();
            e.stopPropagation();
            if (e.stopImmediatePropagation) e.stopImmediatePropagation();

            openConfirm(row);
        },
        true
    ); // <= capture phase
})();

// Keep Quicktags (Code) textarea from shrinking on toggle
document.addEventListener("click", function (e) {
    const btn = e.target.closest(
        ".ff-options-card .wp-switch-editor.switch-html"
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
    // range -> number
    $(document).on("input change", ".ff-range-slider", function () {
        var $num = $($(this).data("target"));
        if ($num.length) $num.val(this.value);
    });

    // number -> range (and clamp within min/max)
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
    // Guard: ensure we bind only once across all admin screens
    if (window.ffMediaBound) {
        return;
    }
    window.ffMediaBound = true;

    function openFFFrame($wrap, $input) {
        var type = $wrap.data("type"); // "image" or "file"
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

            // Save the ID in the hidden input
            $input.val(att.id).trigger("change");

            if (type === "image") {
                var url =
                    att.sizes && att.sizes.thumbnail
                        ? att.sizes.thumbnail.url
                        : att.url;
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

    // Namespaced delegated bindings (works on both edit + global)
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
                $wrap
                    .find(".ff-media-fileurl")
                    .attr("href", "")
                    .text("")
                    .hide();
                $wrap.find(".ff-media-nofile").show();
            }
            $(this).hide();
        });
})(jQuery);
