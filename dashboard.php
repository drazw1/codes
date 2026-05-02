<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Pharmacy Dashboard – jQuery & AJAX Showcase</title>

<!--
    LIBRARIES:
    - jQuery 3.7.1  (CDN)   → event handling, DOM manipulation, AJAX
    - Google Fonts            → Syne display + JetBrains Mono body
-->
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Syne:wght@700;800&family=JetBrains+Mono:wght@400;600&display=swap" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<style>
/* ── CSS Variables ──────────────────────────────────────────── */
:root {
    --bg:       #080b14;
    --surface:  #0f1420;
    --card:     #141928;
    --border:   #1e2740;
    --accent:   #38bdf8;
    --green:    #4ade80;
    --amber:    #fbbf24;
    --red:      #f87171;
    --text:     #e2e8f0;
    --muted:    #64748b;
    --radius:   10px;
    --font-display: 'Syne', sans-serif;
    --font-mono:    'JetBrains Mono', monospace;
}

* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    background: var(--bg);
    color: var(--text);
    font-family: var(--font-mono);
    font-size: 14px;
    min-height: 100vh;
}

/* ── Layout ─────────────────────────────────────────────────── */
.topbar {
    background: linear-gradient(135deg, #060912 0%, #0d1526 100%);
    border-bottom: 1px solid var(--border);
    padding: 1rem 2rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky; top: 0; z-index: 100;
}
.logo { font-family: var(--font-display); font-size: 1.4rem; color: var(--accent); letter-spacing: -0.5px; }
.logo span { color: var(--green); }
.nav-links a {
    color: var(--muted); text-decoration: none; margin-left: 1.5rem;
    font-size: .8rem; transition: color .2s;
}
.nav-links a:hover { color: var(--text); }

.wrapper { max-width: 1280px; margin: 0 auto; padding: 2rem; }

/* ── Tabs ───────────────────────────────────────────────────── */
.tabs { display: flex; gap: .5rem; margin-bottom: 1.5rem; flex-wrap: wrap; }
.tab-btn {
    background: var(--card); border: 1px solid var(--border);
    color: var(--muted); padding: .5rem 1.2rem; border-radius: 6px;
    cursor: pointer; font-family: var(--font-mono); font-size: .8rem;
    transition: all .2s;
}
.tab-btn.active, .tab-btn:hover {
    border-color: var(--accent); color: var(--accent); background: rgba(56,189,248,.07);
}
.tab-panel { display: none; }
.tab-panel.active { display: block; }

/* ── Cards ──────────────────────────────────────────────────── */
.card {
    background: var(--card); border: 1px solid var(--border);
    border-radius: var(--radius); padding: 1.5rem; margin-bottom: 1.5rem;
}
.card-title {
    font-family: var(--font-display); font-size: 1rem; font-weight: 700;
    color: var(--accent); margin-bottom: .25rem;
}
.card-sub { color: var(--muted); font-size: .78rem; margin-bottom: 1.25rem; line-height: 1.5; }

/* ── Buttons ────────────────────────────────────────────────── */
.btn {
    display: inline-flex; align-items: center; gap: .4rem;
    padding: .55rem 1.3rem; border-radius: 6px; border: none;
    font-family: var(--font-mono); font-size: .82rem; font-weight: 600;
    cursor: pointer; transition: all .2s; margin-right: .5rem; margin-top: .5rem;
}
.btn-primary  { background: var(--accent); color: #000; }
.btn-success  { background: var(--green);  color: #000; }
.btn-warning  { background: var(--amber);  color: #000; }
.btn-ghost    { background: transparent; border: 1px solid var(--border); color: var(--text); }
.btn:hover    { filter: brightness(1.1); transform: translateY(-1px); }
.btn:active   { transform: translateY(0); }
.btn:disabled { opacity: .45; cursor: not-allowed; transform: none; }

/* ── Forms ──────────────────────────────────────────────────── */
.form-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr)); gap: 1rem; }
.form-group label { display: block; font-size: .75rem; color: var(--muted); margin-bottom: .35rem; }
.form-group input, .form-group select {
    width: 100%; background: var(--surface); border: 1px solid var(--border);
    color: var(--text); padding: .5rem .75rem; border-radius: 6px;
    font-family: var(--font-mono); font-size: .82rem;
    transition: border-color .2s;
}
.form-group input:focus, .form-group select:focus {
    outline: none; border-color: var(--accent);
}
.form-group input::placeholder { color: var(--muted); }

/* ── Table ──────────────────────────────────────────────────── */
.data-table { width: 100%; border-collapse: collapse; font-size: .8rem; margin-top: 1rem; }
.data-table th {
    background: var(--surface); color: var(--amber); padding: .5rem .75rem;
    text-align: left; font-weight: 600; border-bottom: 1px solid var(--border);
}
.data-table td { padding: .45rem .75rem; border-bottom: 1px solid #1a1e30; }
.data-table tr:hover td { background: rgba(255,255,255,.02); }

/* ── Badges / Tags ──────────────────────────────────────────── */
.badge {
    display: inline-block; padding: .15rem .5rem; border-radius: 4px;
    font-size: .7rem; font-weight: 600;
}
.badge-yes  { background: rgba(248,113,113,.15); color: var(--red); }
.badge-no   { background: rgba(74,222,128,.12);  color: var(--green); }
.badge-ok   { background: rgba(74,222,128,.15);  color: var(--green); }
.badge-warn { background: rgba(251,191,36,.15);  color: var(--amber); }
.badge-err  { background: rgba(248,113,113,.15); color: var(--red); }

/* ── Status / log output ────────────────────────────────────── */
.log-box {
    background: #060912; border: 1px solid var(--border); border-radius: 6px;
    padding: 1rem; font-size: .78rem; min-height: 60px;
    white-space: pre-wrap; overflow-x: auto; margin-top: 1rem;
    max-height: 280px; overflow-y: auto; color: var(--green);
}
.log-box.error { color: var(--red); }

/* ── Stock indicator ────────────────────────────────────────── */
.stock-bar-wrap { height: 6px; background: var(--border); border-radius: 3px; margin-top: 4px; }
.stock-bar { height: 100%; border-radius: 3px; transition: width .4s; }

/* ── Search bar ─────────────────────────────────────────────── */
.search-row { display: flex; gap: .75rem; align-items: flex-end; flex-wrap: wrap; }
.search-row .form-group { flex: 1; min-width: 160px; margin: 0; }

/* ── Info banner ────────────────────────────────────────────── */
.concept-tag {
    display: inline-block; background: rgba(56,189,248,.1);
    border: 1px solid rgba(56,189,248,.25); color: var(--accent);
    padding: .15rem .6rem; border-radius: 4px; font-size: .7rem;
    margin-right: .35rem; margin-bottom: .5rem;
}

/* ── Spinner ────────────────────────────────────────────────── */
@keyframes spin { to { transform: rotate(360deg); } }
.spinner {
    display: inline-block; width: 14px; height: 14px;
    border: 2px solid var(--border); border-top-color: var(--accent);
    border-radius: 50%; animation: spin .6s linear infinite;
    vertical-align: middle; margin-right: .4rem;
}

/* ── Toasts ─────────────────────────────────────────────────── */
#toast-container { position: fixed; bottom: 1.5rem; right: 1.5rem; z-index: 999; display: flex; flex-direction: column; gap: .5rem; }
.toast {
    background: var(--card); border: 1px solid var(--border);
    color: var(--text); padding: .75rem 1.2rem; border-radius: 8px;
    font-size: .82rem; max-width: 320px; animation: slideIn .3s ease;
    display: flex; gap: .75rem; align-items: center;
}
@keyframes slideIn { from { transform: translateX(100%); opacity:0; } to { transform: translateX(0); opacity:1; } }
.toast.success { border-left: 3px solid var(--green); }
.toast.error   { border-left: 3px solid var(--red); }
.toast.info    { border-left: 3px solid var(--accent); }
</style>
</head>
<body>

<!-- ── Top Bar ────────────────────────────────────────────────── -->
<div class="topbar">
    <div class="logo">⚕ Pharma<span>Hub</span></div>
    <nav class="nav-links">
        <a href="json_manager.php">JSON Manager</a>
        <a href="medicines_api.php?format=pretty" target="_blank">Medicines API</a>
        <a href="categories_api.php" target="_blank">Categories API</a>
    </nav>
</div>

<!-- ── Main Content ──────────────────────────────────────────── -->
<div class="wrapper">

    <!-- Concept tags -->
    <div style="margin-bottom:1.5rem">
        <span class="concept-tag">jQuery 3.7.1</span>
        <span class="concept-tag">$.ajax()</span>
        <span class="concept-tag">$.getJSON()</span>
        <span class="concept-tag">AJAX Events</span>
        <span class="concept-tag">JSON API</span>
        <span class="concept-tag">JSON Schema</span>
        <span class="concept-tag">PHP Backend</span>
    </div>

    <!-- ── Tabs ─────────────────────────────────────────────── -->
    <div class="tabs">
        <button class="tab-btn active" data-tab="search">🔍 Live Search</button>
        <button class="tab-btn" data-tab="add">➕ Add Medicine</button>
        <button class="tab-btn" data-tab="stock">📦 Stock Checker</button>
        <button class="tab-btn" data-tab="rawjson">🧾 Raw JSON</button>
    </div>

    <!-- ══════════════════════════════════════════════════════
         TAB 1 – LIVE SEARCH  (AJAX + JSON)
    ══════════════════════════════════════════════════════ -->
    <div class="tab-panel active" id="tab-search">

        <div class="card">
            <div class="card-title">Live Medicine Search</div>
            <div class="card-sub">
                Demonstrates <strong>$.ajax()</strong> with keyup event registration.
                Results are fetched from <code>medicines_api.php</code> which returns JSON
                and validates each record against the JSON Schema before serving it.
            </div>

            <div class="search-row">
                <div class="form-group">
                    <label>Search by name</label>
                    <input type="text" id="searchInput" placeholder="e.g. Amoxicillin…">
                </div>
                <div class="form-group">
                    <label>Filter by Category</label>
                    <select id="categoryFilter">
                        <option value="">All Categories</option>
                        <!-- Populated by AJAX on page load -->
                    </select>
                </div>
                <div class="form-group">
                    <label>Low stock only</label>
                    <select id="lowStockFilter">
                        <option value="">All</option>
                        <option value="1">Low stock (&lt; 10)</option>
                    </select>
                </div>
                <div>
                    <button class="btn btn-primary" id="btnSearch">Search</button>
                    <button class="btn btn-ghost" id="btnReset">Reset</button>
                </div>
            </div>

            <!-- Counts & schema status -->
            <div style="margin-top:1rem;display:flex;gap:1rem;align-items:center;flex-wrap:wrap">
                <span id="resultCount" style="color:var(--muted);font-size:.8rem">Ready</span>
                <span id="schemaStatus"></span>
                <span id="searchSpinner" style="display:none"><span class="spinner"></span> Loading…</span>
            </div>

            <!-- Results table -->
            <div id="searchResults">
                <p style="color:var(--muted);margin-top:1rem;font-size:.82rem">
                    Type above or click Search to load medicines via AJAX.
                </p>
            </div>
        </div>

    </div><!-- /tab-search -->

    <!-- ══════════════════════════════════════════════════════
         TAB 2 – ADD MEDICINE  (AJAX POST + JSON Schema)
    ══════════════════════════════════════════════════════ -->
    <div class="tab-panel" id="tab-add">

        <div class="card">
            <div class="card-title">Add New Medicine</div>
            <div class="card-sub">
                Demonstrates <strong>$.ajax POST</strong>. The form data is serialized
                to a JSON string via <code>JSON.stringify()</code>, posted to
                <code>ajax/add_medicine_ajax.php</code>, which validates against the
                JSON Schema <em>before inserting</em> into the database.
                The JSON response is then captured and rendered back into the DOM.
            </div>

            <div class="form-grid" id="addForm">
                <div class="form-group">
                    <label>Medicine Name *</label>
                    <input type="text" id="newName" placeholder="e.g. Paracetamol 500mg">
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select id="newCategory">
                        <option value="">— Select —</option>
                        <!-- Populated by AJAX -->
                    </select>
                </div>
                <div class="form-group">
                    <label>Supplier ID</label>
                    <input type="number" id="newSupplier" placeholder="e.g. 1" min="1">
                </div>
                <div class="form-group">
                    <label>Price ($) *</label>
                    <input type="number" id="newPrice" step="0.01" placeholder="e.g. 12.99" min="0">
                </div>
                <div class="form-group">
                    <label>Stock Qty *</label>
                    <input type="number" id="newStock" placeholder="e.g. 100" min="0">
                </div>
                <div class="form-group">
                    <label>Prescription Required *</label>
                    <select id="newRx">
                        <option value="NO">No</option>
                        <option value="YES">Yes</option>
                    </select>
                </div>
            </div>

            <button class="btn btn-success" id="btnAdd" style="margin-top:1rem">
                ➕ Save Medicine via AJAX
            </button>
            <button class="btn btn-ghost" id="btnClearForm" style="margin-top:1rem">Clear</button>

            <!-- Payload preview -->
            <div style="margin-top:1rem">
                <div style="font-size:.75rem;color:var(--muted);margin-bottom:.35rem">
                    JSON payload that will be sent (updates live):
                </div>
                <div class="log-box" id="payloadPreview" style="color:var(--accent)"></div>
            </div>

            <!-- Response -->
            <div id="addResponse" style="margin-top:1rem"></div>
        </div>

    </div><!-- /tab-add -->

    <!-- ══════════════════════════════════════════════════════
         TAB 3 – STOCK CHECKER  (minimal single-field AJAX)
    ══════════════════════════════════════════════════════ -->
    <div class="tab-panel" id="tab-stock">

        <div class="card">
            <div class="card-title">Live Stock Checker</div>
            <div class="card-sub">
                Type a medicine ID and press Enter or click Check.
                Uses <strong>$.getJSON()</strong> — a jQuery shorthand for
                <code>$.ajax({ dataType:'json' })</code>.
                The returned JSON object's fields are captured individually
                and bound to DOM elements.
            </div>

            <div style="display:flex;gap:.75rem;align-items:flex-end;max-width:380px">
                <div class="form-group" style="flex:1;margin:0">
                    <label>Medicine ID</label>
                    <input type="number" id="stockId" placeholder="e.g. 3" min="1">
                </div>
                <button class="btn btn-warning" id="btnStock">Check Stock</button>
            </div>

            <!-- Stock result card -->
            <div id="stockResult" style="display:none;margin-top:1.5rem">
                <div class="card" style="background:var(--surface);max-width:420px">
                    <div style="font-size:.75rem;color:var(--muted);margin-bottom:.5rem">RESULT · medicines_api → get_stock_ajax.php</div>
                    <div style="font-size:1.1rem;color:var(--text);font-weight:600" id="srName"></div>
                    <div style="font-size:.78rem;color:var(--muted);margin:.25rem 0 .75rem" id="srId"></div>
                    <div style="display:flex;align-items:baseline;gap:.5rem">
                        <span style="font-size:2rem;font-weight:700;font-family:var(--font-display)" id="srQty"></span>
                        <span style="color:var(--muted)">units in stock</span>
                    </div>
                    <div class="stock-bar-wrap"><div class="stock-bar" id="srBar"></div></div>
                    <div style="margin-top:.75rem" id="srStatus"></div>
                    <!-- Raw JSON response for educational display -->
                    <div style="font-size:.72rem;color:var(--muted);margin-top:1rem">Raw JSON response:</div>
                    <div class="log-box" id="srRaw" style="font-size:.72rem;max-height:120px"></div>
                </div>
            </div>
        </div>

    </div><!-- /tab-stock -->

    <!-- ══════════════════════════════════════════════════════
         TAB 4 – RAW JSON  (consume medicines_api.php output)
    ══════════════════════════════════════════════════════ -->
    <div class="tab-panel" id="tab-rawjson">

        <div class="card">
            <div class="card-title">Raw JSON API Response</div>
            <div class="card-sub">
                Fetches the full JSON from <code>medicines_api.php</code> and displays
                the raw response. Demonstrates capturing the complete data object returned
                by jQuery AJAX and rendering it in multiple ways.
            </div>

            <button class="btn btn-primary" id="btnLoadRaw">⬇ Fetch JSON</button>
            <button class="btn btn-ghost"   id="btnCopyJson">📋 Copy</button>

            <div style="margin-top:1rem;font-size:.78rem;color:var(--muted)" id="apiMeta"></div>
            <div class="log-box" id="rawJsonBox" style="color:#a5f3fc">
                Click "Fetch JSON" to load data from medicines_api.php
            </div>
        </div>

    </div><!-- /tab-rawjson -->

</div><!-- /wrapper -->

<!-- ── Toast Container ────────────────────────────────────────── -->
<div id="toast-container"></div>

<!-- ================================================================
     JQUERY  +  ALL AJAX / EVENT LOGIC
================================================================ -->
<script>
$(function () {

    // ── Utility: show toast notification ──────────────────────
    /**
     * showToast(message, type)
     * Creates a dismissing toast. Demonstrates DOM manipulation.
     */
    function showToast(msg, type = 'info') {
        const icons = { success: '✓', error: '✗', info: 'ℹ' };
        const $t = $('<div>')
            .addClass(`toast ${type}`)
            .html(`<span>${icons[type]}</span><span>${msg}</span>`);
        $('#toast-container').append($t);
        setTimeout(() => $t.fadeOut(400, () => $t.remove()), 3500);
    }

    // ── Utility: render medicines table ───────────────────────
    /**
     * renderMedicinesTable(medicines)
     * Captures the JSON array returned by AJAX and builds HTML table.
     */
    function renderMedicinesTable(medicines) {
        if (!medicines.length) {
            return '<p style="color:var(--muted);padding:.75rem 0">No medicines match your criteria.</p>';
        }

        let html = `<table class="data-table">
            <thead><tr>
                <th>ID</th><th>Name</th><th>Category</th>
                <th>Price</th><th>Stock</th><th>Rx</th>
            </tr></thead><tbody>`;

        // Iterate over the JSON array (data captured from AJAX response)
        $.each(medicines, function (i, med) {
            const stockColor = med.stock < 10 ? 'var(--red)' : med.stock < 50 ? 'var(--amber)' : 'var(--green)';
            const rxBadge    = med.prescription_required === 'YES'
                ? '<span class="badge badge-yes">Rx Required</span>'
                : '<span class="badge badge-no">OTC</span>';

            html += `<tr>
                <td style="color:var(--muted)">#${med.medicine_id}</td>
                <td><strong>${med.medicine_name}</strong></td>
                <td style="color:var(--muted)">${med.category_name || '—'}</td>
                <td>$${parseFloat(med.price).toFixed(2)}</td>
                <td style="color:${stockColor};font-weight:600">${med.stock}</td>
                <td>${rxBadge}</td>
            </tr>`;
        });

        html += '</tbody></table>';
        return html;
    }

    // ── STEP A: Load categories via AJAX on page load ─────────
    /**
     * Demonstrates AJAX call on document ready.
     * $.ajax() with success callback that populates multiple dropdowns.
     */
    $.ajax({
        url: 'categories_api.php',
        method: 'GET',
        dataType: 'json',           // jQuery parses JSON automatically

        // success callback: data = parsed JSON object
        success: function (data) {
            // data.categories is the array from the JSON response
            if (data.success && data.categories.length) {
                let opts = '<option value="">All Categories</option>';
                $.each(data.categories, function (i, cat) {
                    opts += `<option value="${cat.category_id}">${cat.category_name}</option>`;
                });
                // Populate both dropdowns simultaneously
                $('#categoryFilter, #newCategory').append(
                    $(opts).clone()
                );
                // Re-populate separately since clone needs reassignment
                $('#newCategory').html(
                    '<option value="">— Select —</option>' +
                    data.categories.map(c =>
                        `<option value="${c.category_id}">${c.category_name}</option>`
                    ).join('')
                );
            }
        },
        error: function (xhr, status, err) {
            showToast('Could not load categories: ' + err, 'error');
        }
    });

    // ── STEP B: Register keyup event on search input ──────────
    /**
     * Demonstrates $.fn.on() event registration.
     * Uses a debounce (setTimeout) pattern to avoid hitting the
     * server on every single keystroke.
     */
    let searchTimer;
    $('#searchInput').on('keyup', function () {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(doSearch, 400);   // debounce 400 ms
    });

    // Dropdown changes trigger immediate search
    $('#categoryFilter, #lowStockFilter').on('change', doSearch);

    // Button click also triggers search
    $('#btnSearch').on('click', doSearch);

    // Reset button
    $('#btnReset').on('click', function () {
        $('#searchInput').val('');
        $('#categoryFilter').val('');
        $('#lowStockFilter').val('');
        $('#searchResults').html('<p style="color:var(--muted);margin-top:1rem;font-size:.82rem">Filters cleared.</p>');
        $('#resultCount').text('Ready');
        $('#schemaStatus').html('');
    });

    /**
     * doSearch()
     * Core AJAX search function. Reads form values, builds query string,
     * fires $.ajax(), then displays the returned JSON.
     */
    function doSearch() {
        const params = {
            search:      $('#searchInput').val().trim(),
            category_id: $('#categoryFilter').val(),
            low_stock:   $('#lowStockFilter').val(),
        };

        $('#searchSpinner').show();
        $('#btnSearch').prop('disabled', true);

        // ── $.ajax() with all key options demonstrated ─────────
        $.ajax({
            url:      'medicines_api.php',
            method:   'GET',
            data:     params,           // serialised to ?search=…&category_id=…
            dataType: 'json',           // auto-parse JSON response

            // beforeSend: fires BEFORE the request is made
            beforeSend: function () {
                $('#searchResults').css('opacity', '.5');
            },

            // success: fires when response is 2xx and JSON is valid
            success: function (response) {
                // Capture returned data fields
                const meds  = response.medicines;   // array from JSON
                const total = response.total;

                // Display result count
                $('#resultCount').text(`${total} medicine(s) found`);

                // Display schema validation status (from API)
                const schemaHtml = response.schema_valid
                    ? '<span class="badge badge-ok">✓ Schema Valid</span>'
                    : '<span class="badge badge-err">✗ Schema Errors</span>';
                $('#schemaStatus').html(schemaHtml);

                // Render the table from the JSON array
                $('#searchResults').html(renderMedicinesTable(meds));
            },

            // error: fires on network error or non-2xx response
            error: function (xhr, status, err) {
                $('#searchResults').html(
                    `<p style="color:var(--red)">AJAX error: ${status} – ${err}</p>`
                );
                showToast('Search failed.', 'error');
            },

            // complete: always fires after success OR error
            complete: function () {
                $('#searchSpinner').hide();
                $('#searchResults').css('opacity', '1');
                $('#btnSearch').prop('disabled', false);
            }
        });
    }

    // ── STEP C: Live payload preview for Add form ─────────────
    /**
     * Demonstrates capturing form field values with jQuery
     * and building a JSON object in real time.
     */
    function buildPayload() {
        return {
            medicine_name:          $('#newName').val().trim(),
            category_id:            $('#newCategory').val() || null,
            supplier_id:            $('#newSupplier').val() || null,
            price:                  parseFloat($('#newPrice').val()) || 0,
            stock:                  parseInt($('#newStock').val()) || 0,
            prescription_required:  $('#newRx').val(),
        };
    }

    // Register input events on all add-form fields
    $('#addForm input, #addForm select').on('input change', function () {
        $('#payloadPreview').text(JSON.stringify(buildPayload(), null, 2));
    });

    // Initial render
    $('#payloadPreview').text(JSON.stringify(buildPayload(), null, 2));

    // Clear form
    $('#btnClearForm').on('click', function () {
        $('#addForm input').val('');
        $('#addForm select').prop('selectedIndex', 0);
        $('#payloadPreview').text(JSON.stringify(buildPayload(), null, 2));
        $('#addResponse').html('');
    });

    // ── STEP D: AJAX POST to add medicine ─────────────────────
    /**
     * Demonstrates $.ajax POST with JSON.stringify() payload.
     * Server validates against JSON Schema before inserting.
     * Response JSON is captured and displayed.
     */
    $('#btnAdd').on('click', function () {
        const payload = buildPayload();

        // Client-side pre-validation (basic required fields)
        if (!payload.medicine_name) {
            showToast('Medicine name is required.', 'error');
            return;
        }
        if (!payload.price || payload.price <= 0) {
            showToast('Enter a valid price.', 'error');
            return;
        }

        const $btn = $(this).prop('disabled', true).text('⏳ Saving…');

        $.ajax({
            url:         'ajax/add_medicine_ajax.php',
            method:      'POST',
            contentType: 'application/json',   // tell server we're sending JSON
            data:        JSON.stringify(payload), // serialise JS object → JSON string
            dataType:    'json',                // expect JSON back

            success: function (resp) {
                if (resp.success) {
                    // Capture the returned medicine object (with new DB id)
                    const m = resp.medicine;
                    showToast(`"${m.medicine_name}" added (ID #${m.medicine_id})`, 'success');

                    $('#addResponse').html(`
                        <div class="card" style="background:rgba(74,222,128,.05);border-color:rgba(74,222,128,.25)">
                            <div style="color:var(--green);font-weight:700;margin-bottom:.5rem">✓ ${resp.message}</div>
                            <div style="font-size:.75rem;color:var(--muted);margin-bottom:.35rem">
                                Schema validated: <strong style="color:var(--green)">${resp.schema_valid ? 'YES ✓' : 'NO ✗'}</strong>
                                &nbsp;|&nbsp; New ID: <strong style="color:var(--accent)">#${m.medicine_id}</strong>
                            </div>
                            <pre style="background:#060912;padding:.75rem;border-radius:5px;font-size:.75rem;color:#a5f3fc">${JSON.stringify(resp, null, 2)}</pre>
                        </div>`);

                    // Clear form on success
                    $('#addForm input').val('');
                    $('#addForm select').prop('selectedIndex', 0);

                } else {
                    // Display schema errors returned from PHP
                    const errList = resp.schema_errors
                        ? resp.schema_errors.map(e => `  • ${e}`).join('\n')
                        : '';
                    $('#addResponse').html(`
                        <div class="card" style="background:rgba(248,113,113,.05);border-color:rgba(248,113,113,.25)">
                            <div style="color:var(--red);font-weight:700;margin-bottom:.5rem">✗ ${resp.message}</div>
                            ${errList ? `<pre style="color:var(--red);font-size:.78rem">${errList}</pre>` : ''}
                        </div>`);
                    showToast(resp.message, 'error');
                }
            },

            error: function (xhr) {
                try {
                    const err = JSON.parse(xhr.responseText);
                    showToast(err.message || 'Server error.', 'error');
                } catch {
                    showToast('Network error.', 'error');
                }
            },

            complete: function () {
                $btn.prop('disabled', false).text('➕ Save Medicine via AJAX');
            }
        });
    });

    // ── STEP E: Stock Checker – $.getJSON ─────────────────────
    /**
     * $.getJSON is shorthand for $.ajax({ dataType: 'json', method: 'GET' }).
     * Demonstrates capturing individual fields from the JSON response
     * and binding them to specific DOM elements.
     */
    function checkStock() {
        const id = parseInt($('#stockId').val());
        if (!id || id <= 0) { showToast('Enter a valid medicine ID.', 'error'); return; }

        $.getJSON('ajax/get_stock_ajax.php', { id: id }, function (data) {
            // data = parsed JSON object, capture individual fields:
            if (!data.success) {
                showToast(data.message, 'error');
                return;
            }

            // Bind each field to its DOM element
            $('#srName').text(data.medicine_name);
            $('#srId').text('Medicine ID: #' + data.medicine_id);
            $('#srQty').text(data.stock);

            // Stock bar visual
            const pct = Math.min(data.stock, 200) / 200 * 100;
            const barColor = {
                out_of_stock: 'var(--red)',
                low_stock:    'var(--red)',
                moderate:     'var(--amber)',
                well_stocked: 'var(--green)',
            }[data.status] || 'var(--green)';
            $('#srBar').css({ width: pct + '%', background: barColor });

            const statusLabels = {
                out_of_stock: '<span class="badge badge-err">OUT OF STOCK</span>',
                low_stock:    '<span class="badge badge-warn">LOW STOCK – Reorder needed</span>',
                moderate:     '<span class="badge badge-warn">MODERATE</span>',
                well_stocked: '<span class="badge badge-ok">WELL STOCKED</span>',
            };
            $('#srStatus').html(statusLabels[data.status] || '');

            // Display raw JSON for educational purposes
            $('#srRaw').text(JSON.stringify(data, null, 2));
            $('#stockResult').fadeIn(300);

        }).fail(function (xhr, status, err) {
            showToast('AJAX error: ' + err, 'error');
        });
    }

    $('#btnStock').on('click', checkStock);

    // Also trigger on Enter key in the input
    $('#stockId').on('keypress', function (e) {
        if (e.which === 13) checkStock();   // e.which = key code (13 = Enter)
    });

    // ── STEP F: Fetch and display raw JSON ────────────────────
    /**
     * Demonstrates capturing the complete raw JSON string
     * as well as the parsed object.
     */
    $('#btnLoadRaw').on('click', function () {
        const $btn = $(this).prop('disabled', true).text('⏳ Fetching…');

        $.ajax({
            url:      'medicines_api.php?format=pretty',
            method:   'GET',
            dataType: 'text',   // Get raw text, not parsed JSON

            success: function (rawText) {
                // Parse manually to get both raw string AND object
                const parsed = JSON.parse(rawText);

                $('#apiMeta').html(
                    `<span class="badge badge-ok">✓ ${parsed.total} medicines</span> &nbsp;` +
                    `Schema: ${parsed.schema_valid
                        ? '<span class="badge badge-ok">Valid ✓</span>'
                        : '<span class="badge badge-err">Invalid ✗</span>'}`
                );

                // Display raw JSON string (for educational purposes)
                $('#rawJsonBox').text(rawText);
                showToast(`Loaded ${parsed.total} medicines from API`, 'success');
            },

            error: function () { showToast('Failed to load API.', 'error'); },
            complete: function () { $btn.prop('disabled', false).text('⬇ Fetch JSON'); }
        });
    });

    $('#btnCopyJson').on('click', function () {
        const text = $('#rawJsonBox').text();
        if (!text || text.includes('Click')) { showToast('Nothing to copy.', 'error'); return; }
        navigator.clipboard.writeText(text).then(() => showToast('JSON copied!', 'success'));
    });

    // ── Tab switching ─────────────────────────────────────────
    $('.tab-btn').on('click', function () {
        const tab = $(this).data('tab');
        $('.tab-btn').removeClass('active');
        $(this).addClass('active');
        $('.tab-panel').removeClass('active');
        $(`#tab-${tab}`).addClass('active');
    });

    // ── Global AJAX event hooks (jQuery ajaxSetup) ────────────
    /**
     * $.ajaxSetup sets defaults for ALL subsequent $.ajax() calls.
     * ajaxError and ajaxComplete are global event handlers.
     */
    $(document).ajaxError(function (event, jqXHR, settings, thrownError) {
        console.warn('[AJAX Error]', settings.url, thrownError, jqXHR.responseText);
    });

    // Load initial search results on page load
    doSearch();

}); // end document.ready
</script>

</body>
</html>
