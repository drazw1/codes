<?php
// ============================================================
//  json_manager.php
//  PURPOSE : Demonstrates three JSON operations in PHP:
//    1. PRODUCE  – query DB, build structured JSON, write to file
//    2. VALIDATE – validate the file against JSON Schema
//    3. CONSUME  – read/parse the JSON file and display data in PHP
//
//  Also acts as an AJAX endpoint when ?action= is present.
// ============================================================

require_once 'db_connect.php';
require_once 'JsonSchemaValidator.php';

// ── Constants ─────────────────────────────────────────────────
define('JSON_OUTPUT_FILE',  __DIR__ . '/json/medicines_export.json');
define('SCHEMA_FILE',       __DIR__ . '/json/medicines_export_schema.json');
define('MEDICINE_SCHEMA',   __DIR__ . '/json/medicine_schema.json');

// ── AJAX mode ─────────────────────────────────────────────────
if (!empty($_GET['action'])) {
    header('Content-Type: application/json');

    switch ($_GET['action']) {
        case 'export':  ajaxExport($conn);  break;
        case 'validate':ajaxValidate();     break;
        case 'consume': ajaxConsume();      break;
        default:
            echo json_encode(['success' => false, 'message' => 'Unknown action.']);
    }
    exit;
}

// ── HTML page ─────────────────────────────────────────────────
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>JSON Manager – Pharmacy</title>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <style>
        :root {
            --bg: #0f1117; --surface: #1a1d27; --border: #2e3349;
            --accent: #4ade80; --text: #e2e8f0; --muted: #64748b;
            --error: #f87171; --warn: #fbbf24;
        }
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { background: var(--bg); color: var(--text); font-family: 'Courier New', monospace; padding: 2rem; }
        h1  { color: var(--accent); margin-bottom: .25rem; font-size: 1.5rem; }
        .sub { color: var(--muted); font-size: .85rem; margin-bottom: 2rem; }
        .card {
            background: var(--surface); border: 1px solid var(--border);
            border-radius: 8px; padding: 1.5rem; margin-bottom: 1.5rem;
        }
        .card h2 { font-size: 1rem; margin-bottom: 1rem; color: var(--warn); }
        .btn {
            background: var(--accent); color: #000; border: none; padding: .6rem 1.4rem;
            border-radius: 5px; cursor: pointer; font-weight: 700; font-size: .85rem;
            margin-right: .5rem; margin-top: .5rem; transition: opacity .2s;
        }
        .btn:hover { opacity: .85; }
        .btn.secondary { background: var(--border); color: var(--text); }
        pre {
            background: #0a0c14; border: 1px solid var(--border); border-radius: 5px;
            padding: 1rem; font-size: .78rem; overflow-x: auto; white-space: pre-wrap;
            margin-top: 1rem; max-height: 350px; overflow-y: auto;
        }
        .badge {
            display: inline-block; padding: .2rem .6rem; border-radius: 20px;
            font-size: .75rem; font-weight: 700; margin-left: .5rem;
        }
        .badge.ok  { background: #14532d; color: var(--accent); }
        .badge.err { background: #450a0a; color: var(--error); }
        .steps { display: flex; gap: 1rem; flex-wrap: wrap; margin-bottom: 1rem; }
        .step  {
            background: #12141e; border: 1px solid var(--border); border-radius: 6px;
            padding: .75rem 1rem; font-size: .82rem; flex: 1; min-width: 200px;
        }
        .step .n { color: var(--accent); font-size: 1.4rem; font-weight: 900; }
        .step p  { color: var(--muted); margin-top: .25rem; line-height: 1.4; }
        #spinner { display: none; color: var(--muted); font-size: .85rem; margin-top: .5rem; }
    </style>
</head>
<body>

<h1>⚕ JSON Manager</h1>
<p class="sub">Pharmacy System · Demonstrating: JSON Production · Schema Validation · PHP Consumption</p>

<!-- Step explanation -->
<div class="steps">
    <div class="step"><span class="n">1</span><br><strong>Produce</strong><p>Query DB → build PHP array → encode as JSON → write to <code>medicines_export.json</code></p></div>
    <div class="step"><span class="n">2</span><br><strong>Validate</strong><p>Load file → decode → run against <code>medicines_export_schema.json</code> using our PHP validator</p></div>
    <div class="step"><span class="n">3</span><br><strong>Consume (PHP)</strong><p>Read file → decode → loop in PHP → render HTML table (server-side)</p></div>
    <div class="step"><span class="n">4</span><br><strong>Consume (AJAX)</strong><p>jQuery fetches the JSON file or API → parses response → renders table in browser</p></div>
</div>

<div class="card">
    <h2>Step 1 – Produce JSON File</h2>
    <p style="color:var(--muted);font-size:.85rem;">Queries all medicines from DB and writes a structured JSON file to disk.</p>
    <button class="btn" id="btnExport">⬇ Export to JSON File</button>
    <span id="spinner">⏳ Working...</span>
    <pre id="exportResult" style="display:none"></pre>
</div>

<div class="card">
    <h2>Step 2 – Validate JSON File Against Schema</h2>
    <p style="color:var(--muted);font-size:.85rem;">Loads <code>medicines_export.json</code> and validates every record against <code>medicines_export_schema.json</code>.</p>
    <button class="btn secondary" id="btnValidate">🔎 Validate Schema</button>
    <pre id="validateResult" style="display:none"></pre>
</div>

<div class="card">
    <h2>Step 3 – Consume JSON in PHP (server-side render)</h2>
    <p style="color:var(--muted);font-size:.85rem;">PHP reads and parses the JSON file, then renders a table. Result returned via AJAX.</p>
    <button class="btn secondary" id="btnConsume">📄 Render PHP Table</button>
    <div id="consumeResult" style="margin-top:1rem"></div>
</div>

<div class="card">
    <h2>Step 4 – Consume JSON in jQuery / AJAX</h2>
    <p style="color:var(--muted);font-size:.85rem;">jQuery fetches the raw JSON file directly (<code>$.getJSON</code>) and renders it client-side.</p>
    <button class="btn" id="btnJsConsume">⚡ Load with jQuery</button>
    <div id="jsConsumeResult" style="margin-top:1rem"></div>
</div>

<script>
// ── Step 1: Export ────────────────────────────────────────────
$('#btnExport').on('click', function () {
    $('#spinner').show();
    $('#exportResult').hide();

    $.ajax({
        url: 'json_manager.php?action=export',
        method: 'GET',
        dataType: 'json',
        success: function (resp) {
            $('#spinner').hide();
            const badge = resp.success
                ? '<span class="badge ok">✓ SUCCESS</span>'
                : '<span class="badge err">✗ FAILED</span>';
            $('#exportResult')
                .html(badge + '\n\n' + JSON.stringify(resp, null, 2))
                .show();
        },
        error: function () {
            $('#spinner').hide();
            $('#exportResult').text('AJAX request failed.').show();
        }
    });
});

// ── Step 2: Validate ──────────────────────────────────────────
$('#btnValidate').on('click', function () {
    $.getJSON('json_manager.php?action=validate', function (resp) {
        const badge = resp.schema_valid
            ? '<span class="badge ok">✓ VALID</span>'
            : '<span class="badge err">✗ INVALID</span>';
        $('#validateResult')
            .html(badge + '\n\n' + JSON.stringify(resp, null, 2))
            .show();
    });
});

// ── Step 3: PHP-side consume (returns HTML) ───────────────────
$('#btnConsume').on('click', function () {
    $.get('json_manager.php?action=consume', function (html) {
        $('#consumeResult').html(html);
    });
});

// ── Step 4: jQuery fetches the JSON file directly ─────────────
$('#btnJsConsume').on('click', function () {
    // $.getJSON is a shorthand for $.ajax({ dataType:'json' })
    $.getJSON('json/medicines_export.json', function (data) {
        if (!data.medicines || data.medicines.length === 0) {
            $('#jsConsumeResult').html('<p style="color:var(--error)">No data. Run Export first.</p>');
            return;
        }

        let html = `<p style="color:var(--muted);font-size:.82rem">
                      Loaded <strong style="color:var(--accent)">${data.total}</strong> medicines
                      from <em>medicines_export.json</em>
                      (exported ${data.exported_at})
                    </p>
                    <table style="width:100%;border-collapse:collapse;margin-top:.75rem;font-size:.8rem">
                      <thead>
                        <tr style="background:#12141e;color:var(--warn)">
                          <th style="padding:.4rem .6rem;text-align:left">ID</th>
                          <th style="padding:.4rem .6rem;text-align:left">Name</th>
                          <th style="padding:.4rem .6rem;text-align:left">Category</th>
                          <th style="padding:.4rem .6rem;text-align:right">Price</th>
                          <th style="padding:.4rem .6rem;text-align:right">Stock</th>
                          <th style="padding:.4rem .6rem;text-align:center">Rx</th>
                        </tr>
                      </thead><tbody>`;

        // Loop over the JSON array – pure JS/jQuery
        $.each(data.medicines, function (i, m) {
            const stockColor = m.stock < 10 ? 'var(--error)' : 'var(--accent)';
            html += `<tr style="border-top:1px solid var(--border)">
                       <td style="padding:.35rem .6rem">${m.medicine_id}</td>
                       <td style="padding:.35rem .6rem">${m.medicine_name}</td>
                       <td style="padding:.35rem .6rem;color:var(--muted)">${m.category_name || '—'}</td>
                       <td style="padding:.35rem .6rem;text-align:right">$${parseFloat(m.price).toFixed(2)}</td>
                       <td style="padding:.35rem .6rem;text-align:right;color:${stockColor}">${m.stock}</td>
                       <td style="padding:.35rem .6rem;text-align:center">${m.prescription_required}</td>
                     </tr>`;
        });

        html += '</tbody></table>';
        $('#jsConsumeResult').html(html);

    }).fail(function () {
        $('#jsConsumeResult').html(
            '<p style="color:var(--error)">Could not load medicines_export.json — run Export first.</p>'
        );
    });
});
</script>

</body>
</html>

<?php
// ============================================================
//  AJAX action handlers  (only reached when ?action= is set)
// ============================================================

// ── ACTION: export ────────────────────────────────────────────
function ajaxExport(mysqli $conn): void
{
    // 1. Query DB with a JOIN to get category names
    $result    = $conn->query(
        "SELECT m.medicine_id, m.medicine_name, m.category_id,
                c.category_name, m.supplier_id,
                m.price, m.stock, m.prescription_required
         FROM medicines m
         LEFT JOIN categories c ON m.category_id = c.category_id
         ORDER BY m.medicine_name"
    );
    $medicines = [];
    while ($row = $result->fetch_assoc()) {
        $medicines[] = [
            'medicine_id'           => (int)$row['medicine_id'],
            'medicine_name'         => $row['medicine_name'],
            'category_id'           => $row['category_id'] !== null ? (int)$row['category_id'] : null,
            'category_name'         => $row['category_name'],
            'supplier_id'           => $row['supplier_id']  !== null ? (int)$row['supplier_id']  : null,
            'price'                 => (float)$row['price'],
            'stock'                 => (int)$row['stock'],
            'prescription_required' => $row['prescription_required'],
        ];
    }

    // 2. Build the export envelope
    $export = [
        'exported_at'  => date('Y-m-d H:i:s'),
        'exported_by'  => 'PharmacySystem/json_manager.php',
        'total'        => count($medicines),
        'medicines'    => $medicines,
    ];

    // 3. Validate the envelope against the export schema BEFORE writing
    $validator = new JsonSchemaValidator(__DIR__ . '/json');
    $valid     = $validator->validateFile($export, SCHEMA_FILE);

    if (!$valid) {
        echo json_encode([
            'success'       => false,
            'message'       => 'Pre-write schema validation failed – file NOT written.',
            'schema_errors' => $validator->getErrors(),
        ]);
        return;
    }

    // 4. Write JSON to disk
    $written = file_put_contents(
        JSON_OUTPUT_FILE,
        json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

    echo json_encode([
        'success'      => $written !== false,
        'message'      => $written !== false
                         ? "Exported {$export['total']} medicines to medicines_export.json"
                         : 'File write failed (check directory permissions).',
        'schema_valid' => true,
        'file'         => 'json/medicines_export.json',
        'total'        => $export['total'],
        'exported_at'  => $export['exported_at'],
    ]);
}

// ── ACTION: validate ──────────────────────────────────────────
function ajaxValidate(): void
{
    if (!file_exists(JSON_OUTPUT_FILE)) {
        echo json_encode(['success' => false, 'message' => 'Export file not found. Run Export first.']);
        return;
    }

    // Decode the file
    $json = file_get_contents(JSON_OUTPUT_FILE);
    $data = json_decode($json, true);

    if ($data === null) {
        echo json_encode(['success' => false, 'message' => 'File is not valid JSON: ' . json_last_error_msg()]);
        return;
    }

    // Validate file envelope against export schema
    $validator   = new JsonSchemaValidator(__DIR__ . '/json');
    $envelopeOk  = $validator->validateFile($data, SCHEMA_FILE);
    $envErrors   = $validator->getErrors();

    // Also individually validate each medicine record
    $medSchema   = json_decode(file_get_contents(MEDICINE_SCHEMA), true);
    $medErrors   = [];
    foreach ($data['medicines'] ?? [] as $i => $med) {
        if (!$validator->validateSchema($med, $medSchema)) {
            $medErrors["medicine[$i] ({$med['medicine_name']})"] = $validator->getErrors();
        }
    }

    $allOk = $envelopeOk && empty($medErrors);

    echo json_encode([
        'success'            => true,
        'schema_valid'       => $allOk,
        'envelope_valid'     => $envelopeOk,
        'envelope_errors'    => $envErrors,
        'medicine_errors'    => $medErrors,
        'medicines_checked'  => count($data['medicines'] ?? []),
        'message'            => $allOk
                                ? 'All records pass schema validation ✓'
                                : 'Some records failed schema validation.',
    ]);
}

// ── ACTION: consume (PHP-side, returns HTML) ──────────────────
function ajaxConsume(): void
{
    // Switch to HTML content type since we return a rendered table
    header('Content-Type: text/html');

    if (!file_exists(JSON_OUTPUT_FILE)) {
        echo '<p style="color:#f87171">File not found. Please run Export first.</p>';
        return;
    }

    // PHP consuming the JSON file: decode → iterate → render
    $json = file_get_contents(JSON_OUTPUT_FILE);
    $data = json_decode($json, true);          // associative array

    if ($data === null) {
        echo '<p style="color:#f87171">JSON decode error: ' . json_last_error_msg() . '</p>';
        return;
    }

    // Validate at consumption time
    $validator = new JsonSchemaValidator(__DIR__ . '/json');
    $valid     = $validator->validateFile($data, SCHEMA_FILE);

    $badge = $valid
        ? '<span style="background:#14532d;color:#4ade80;padding:.2rem .5rem;border-radius:4px;font-size:.75rem">✓ Schema Valid</span>'
        : '<span style="background:#450a0a;color:#f87171;padding:.2rem .5rem;border-radius:4px;font-size:.75rem">✗ Schema Invalid</span>';

    echo "<p style='font-size:.82rem;color:#64748b;margin-bottom:.75rem'>
            PHP consumed <strong style='color:#e2e8f0'>{$data['total']}</strong> records
            from <code>medicines_export.json</code> · Exported {$data['exported_at']} $badge
          </p>";

    echo "<table style='width:100%;border-collapse:collapse;font-size:.8rem'>
          <thead>
            <tr style='background:#12141e;color:#fbbf24'>
              <th style='padding:.4rem .6rem;text-align:left'>ID</th>
              <th style='padding:.4rem .6rem;text-align:left'>Medicine</th>
              <th style='padding:.4rem .6rem;text-align:left'>Category</th>
              <th style='padding:.4rem .6rem;text-align:right'>Price</th>
              <th style='padding:.4rem .6rem;text-align:right'>Stock</th>
              <th style='padding:.4rem .6rem;text-align:center'>Rx</th>
            </tr>
          </thead><tbody>";

    // PHP loop over the decoded JSON array
    foreach ($data['medicines'] as $med) {
        $stockColor = (int)$med['stock'] < 10 ? '#f87171' : '#4ade80';
        $catName    = $med['category_name'] ?? '—';
        echo "<tr style='border-top:1px solid #2e3349'>
                <td style='padding:.35rem .6rem'>{$med['medicine_id']}</td>
                <td style='padding:.35rem .6rem'>{$med['medicine_name']}</td>
                <td style='padding:.35rem .6rem;color:#64748b'>{$catName}</td>
                <td style='padding:.35rem .6rem;text-align:right'>$" . number_format((float)$med['price'], 2) . "</td>
                <td style='padding:.35rem .6rem;text-align:right;color:{$stockColor}'>{$med['stock']}</td>
                <td style='padding:.35rem .6rem;text-align:center'>{$med['prescription_required']}</td>
              </tr>";
    }

    echo '</tbody></table>';
}
