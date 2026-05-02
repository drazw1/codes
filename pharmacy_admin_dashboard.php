<?php
// pharmacy_admin_dashboard.php
// Single-file PHP + JS app. API endpoints at top, UI below.

// Path to data file
$data_file = __DIR__ . '/data.json';

// Initialize data.json if missing
if (!file_exists($data_file)) {
    $initial = [
        'medicines' => [
            ['id'=>1, 'name'=>'Paracetamol 500mg', 'category'=>'Pain Relief', 'categoryId'=>1, 'price'=>5.99,  'stock'=>150, 'prescription'=>'NO'],
            ['id'=>2, 'name'=>'Amoxicillin 250mg', 'category'=>'Antibiotics', 'categoryId'=>2, 'price'=>12.50, 'stock'=>45,  'prescription'=>'YES'],
            ['id'=>3, 'name'=>'Ibuprofen 400mg', 'category'=>'Pain Relief', 'categoryId'=>1, 'price'=>7.99,  'stock'=>200, 'prescription'=>'NO'],
            ['id'=>4, 'name'=>'Aspirin 75mg', 'category'=>'Cardiovascular', 'categoryId'=>3, 'price'=>4.50,   'stock'=>15,  'prescription'=>'NO'],
            ['id'=>5, 'name'=>'Metformin 500mg', 'category'=>'Diabetes', 'categoryId'=>4, 'price'=>15.99, 'stock'=>80,  'prescription'=>'YES'],
            ['id'=>6, 'name'=>'Lisinopril 10mg', 'category'=>'Cardiovascular', 'categoryId'=>3, 'price'=>18.75, 'stock'=>35,  'prescription'=>'YES'],
            ['id'=>7, 'name'=>'Cetirizine 10mg', 'category'=>'Allergy', 'categoryId'=>5, 'price'=>6.25,  'stock'=>120, 'prescription'=>'NO'],
            ['id'=>8, 'name'=>'Omeprazole 20mg', 'category'=>'Gastrointestinal', 'categoryId'=>6, 'price'=>11.50, 'stock'=>18,  'prescription'=>'YES']
        ],
        'categories' => [
            ['id'=>1, 'name'=>'Pain Relief', 'description'=>'Medications for pain management'],
            ['id'=>2, 'name'=>'Antibiotics', 'description'=>'Bacterial infection treatments'],
            ['id'=>3, 'name'=>'Cardiovascular', 'description'=>'Heart and blood pressure medications'],
            ['id'=>4, 'name'=>'Diabetes', 'description'=>'Blood sugar management medications'],
            ['id'=>5, 'name'=>'Allergy', 'description'=>'Allergy and antihistamine medications'],
            ['id'=>6, 'name'=>'Gastrointestinal', 'description'=>'Digestive system medications']
        ]
    ];
    file_put_contents($data_file, json_encode($initial, JSON_PRETTY_PRINT));
}

// Helper to read/write data file safely
function read_data() {
    global $data_file;
    $raw = @file_get_contents(filename: $data_file);
    if ($raw === false) return ['medicines'=>[], 'categories'=>[]];
    $data = json_decode($raw, true);
    return is_array($data) ? $data : ['medicines'=>[], 'categories'=>[]];
}
function write_data($data) {
    global $data_file;
    file_put_contents(filename: $data_file, data: json_encode($data, JSON_PRETTY_PRINT));
}

// Basic router for AJAX API calls
$action = $_GET['action'] ?? null;
if ($action) {
    header('Content-Type: application/json; charset=utf-8');

    $data = read_data();
    switch ($action) {
        case 'list':
            echo json_encode($data);
            exit;

        case 'stats':
            $totalMedicines = count($data['medicines']);
            $totalCategories = count($data['categories']);
            $lowStock = count(array_filter($data['medicines'], function($m){ return $m['stock'] < 20; }));
            $totalValue = array_reduce($data['medicines'], function($sum, $m){ return $sum + ($m['price'] * $m['stock']); }, 0);
            echo json_encode([
                'totalMedicines'=>$totalMedicines,
                'totalCategories'=>$totalCategories,
                'lowStock'=>$lowStock,
                'totalValue'=>$totalValue
            ]);
            exit;

        // Medicines CRUD
        case 'add_medicine':
        case 'update_medicine':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input)) { http_response_code(400); echo json_encode(['error'=>'Invalid input']); exit; }

            if ($action === 'add_medicine') {
                // find next id
                $ids = array_map(function($m){ return $m['id']; }, $data['medicines']);
                $next = $ids ? max($ids) + 1 : 1;
                $input['id'] = $next;
                // If categoryId present, find category name
                if (!empty($input['categoryId'])) {
                    $cat = array_values(array_filter($data['categories'], function($c) use ($input){ return $c['id'] == $input['categoryId']; }));
                    $input['category'] = $cat ? $cat[0]['name'] : ($input['category'] ?? 'Uncategorized');
                } else {
                    $input['categoryId'] = 0;
                    $input['category'] = $input['category'] ?? 'Uncategorized';
                }
                $data['medicines'][] = $input;
                write_data($data);
                echo json_encode(['success'=>true, 'medicine'=>$input]);
                exit;
            } else {
                // update
                $id = $input['id'] ?? null;
                if (!$id) { http_response_code(400); echo json_encode(['error'=>'missing id']); exit; }
                $found = false;
                foreach ($data['medicines'] as &$m) {
                    if ($m['id'] == $id) {
                        // update fields
                        foreach (['name','price','stock','prescription','categoryId','category'] as $k) {
                            if (isset($input[$k])) $m[$k] = $input[$k];
                        }
                        // ensure category name sync if categoryId changed
                        if (!empty($m['categoryId'])) {
                            $cat = array_values(array_filter($data['categories'], function($c) use ($m){ return $c['id'] == $m['categoryId']; }));
                            if ($cat) $m['category'] = $cat[0]['name'];
                        }
                        $found = true;
                        $updated = $m;
                        break;
                    }
                }
                if (!$found) { http_response_code(404); echo json_encode(['error'=>'medicine not found']); exit; }
                write_data($data);
                echo json_encode(['success'=>true, 'medicine'=>$updated]);
                exit;
            }

        case 'delete_medicine':
            $id = intval($_GET['id'] ?? 0);
            if (!$id) { http_response_code(400); echo json_encode(['error'=>'missing id']); exit; }
            $before = count($data['medicines']);
            $data['medicines'] = array_filter($data['medicines'], function($m) use ($id){ return $m['id'] != $id; });
            write_data($data);
            echo json_encode(['success'=>true, 'deleted'=> $before - count($data['medicines'])]);
            exit;

        // Categories CRUD
        case 'add_category':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input) || empty($input['name'])) { http_response_code(400); echo json_encode(['error'=>'Invalid input']); exit; }
            $ids = array_map(function($c){ return $c['id']; }, $data['categories']);
            $next = $ids ? max($ids) + 1 : 1;
            $cat = ['id'=>$next, 'name'=>$input['name'], 'description'=>$input['description'] ?? ''];
            $data['categories'][] = $cat;
            write_data($data);
            echo json_encode(['success'=>true, 'category'=>$cat]);
            exit;

        case 'update_category':
            $input = json_decode(file_get_contents('php://input'), true);
            if (!is_array($input) || empty($input['id'])) { http_response_code(400); echo json_encode(['error'=>'Invalid input']); exit; }
            $found = false;
            foreach ($data['categories'] as &$c) {
                if ($c['id'] == $input['id']) {
                    $c['name'] = $input['name'] ?? $c['name'];
                    $c['description'] = $input['description'] ?? $c['description'];
                    $found = true;
                    $updated = $c;
                    break;
                }
            }
            if (!$found) { http_response_code(404); echo json_encode(['error'=>'category not found']); exit; }
            // Also update any medicine category names if category name changed
            foreach ($data['medicines'] as &$m) {
                if ($m['categoryId'] == $updated['id']) $m['category'] = $updated['name'];
            }
            write_data($data);
            echo json_encode(['success'=>true, 'category'=>$updated]);
            exit;

        case 'delete_category':
            $id = intval($_GET['id'] ?? 0);
            if (!$id) { http_response_code(400); echo json_encode(['error'=>'missing id']); exit; }
            $data['categories'] = array_filter($data['categories'], function($c) use ($id){ return $c['id'] != $id; });
            // Set medicines with that categoryId to Uncategorized (categoryId = 0)
            foreach ($data['medicines'] as &$m) {
                if ($m['categoryId'] == $id) { $m['categoryId'] = 0; $m['category'] = 'Uncategorized'; }
            }
            write_data($data);
            echo json_encode(['success'=>true]);
            exit;

        default:
            http_response_code(400);
            echo json_encode(['error'=>'Unknown action']);
            exit;
    }
}

// If no action, render the main HTML page (below)
?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width,initial-scale=1" />
    <title>Pharmacy Admin Dashboard (PHP)</title>
    <style>
        /* (same CSS as the original) */
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);min-height:100vh;padding:20px}
        .container{max-width:1400px;margin:0 auto;background:white;border-radius:20px;box-shadow:0 20px 60px rgba(0,0,0,.3);overflow:hidden}
        .header{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;padding:30px;display:flex;justify-content:space-between;align-items:center;flex-wrap:wrap;gap:20px}
        .header h1{font-size:28px;font-weight:600}
        .header-info{display:flex;gap:20px;align-items:center}
        .stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:20px;padding:30px;background:#f8f9fa}
        .stat-card{background:white;padding:25px;border-radius:15px;box-shadow:0 4px 15px rgba(0,0,0,.1);transition:transform .3s ease}
        .stat-card:hover{transform:translateY(-5px)}
        .stat-card h3{color:#6c757d;font-size:14px;margin-bottom:10px;text-transform:uppercase}
        .stat-card .value{font-size:32px;font-weight:bold;color:#667eea}
        .tabs{display:flex;gap:0;padding:0 30px;background:white;border-bottom:2px solid #f0f0f0}
        .tab{padding:20px 30px;cursor:pointer;border:none;background:none;font-size:16px;font-weight:600;color:#6c757d;border-bottom:3px solid transparent;transition:all .3s ease}
        .tab:hover{color:#667eea;background:#f8f9fa}
        .tab.active{color:#667eea;border-bottom-color:#667eea}
        .tab-content{display:none}
        .tab-content.active{display:block}
        .controls{padding:30px;background:white;display:flex;gap:20px;flex-wrap:wrap;align-items:center;border-bottom:2px solid #f0f0f0}
        .search-box{flex:1;min-width:300px;position:relative}
        .search-box input{width:100%;padding:15px 45px 15px 20px;border:2px solid #e0e0e0;border-radius:10px;font-size:16px;transition:all .3s ease}
        .search-box input:focus{outline:none;border-color:#667eea;box-shadow:0 0 0 3px rgba(102,126,234,.1)}
        .search-icon{position:absolute;right:15px;top:50%;transform:translateY(-50%);color:#6c757d;pointer-events:none}
        .filter-group{display:flex;gap:15px;flex-wrap:wrap}
        select,.btn{padding:12px 20px;border:2px solid #e0e0e0;border-radius:10px;font-size:14px;cursor:pointer;transition:all .3s ease}
        select{background:white}
        select:focus{outline:none;border-color:#667eea}
        .btn{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:white;border:none;font-weight:600}
        .btn:hover{transform:translateY(-2px);box-shadow:0 5px 15px rgba(102,126,234,.4)}
        .btn-success{background:linear-gradient(135deg,#11998e 0%,#38ef7d 100%)}
        .btn-danger{background:linear-gradient(135deg,#eb3349 0%,#f45c43 100%)}
        .table-container{padding:30px;overflow-x:auto}
        table{width:100%;border-collapse:collapse;background:white}
        th{background:#f8f9fa;padding:15px;text-align:left;font-weight:600;color:#495057;border-bottom:2px solid #dee2e6;cursor:pointer;user-select:none;position:relative}
        th:hover{background:#e9ecef}
        th.sortable::after{content:'⇅';margin-left:5px;opacity:.5}
        th.sort-asc::after{content:'↑';opacity:1;color:#667eea}
        th.sort-desc::after{content:'↓';opacity:1;color:#667eea}
        td{padding:15px;border-bottom:1px solid #f0f0f0}
        tr:hover{background:#f8f9fa}
        .badge{padding:5px 12px;border-radius:20px;font-size:12px;font-weight:600;display:inline-block}
        .badge-success{background:#d4edda;color:#155724}
        .badge-warning{background:#fff3cd;color:#856404}
        .badge-danger{background:#f8d7da;color:#721c24}
        .action-btns{display:flex;gap:8px}
        .action-btn{padding:8px 15px;border-radius:8px;font-size:13px;border:none;cursor:pointer;transition:all .2s ease;font-weight:600}
        .action-btn:hover{transform:scale(1.05)}
        .modal{display:none;position:fixed;top:0;left:0;width:100%;height:100%;background:rgba(0,0,0,.5);z-index:1000;align-items:center;justify-content:center}
        .modal.active{display:flex}
        .modal-content{background:white;padding:40px;border-radius:20px;max-width:600px;width:90%;max-height:90vh;overflow-y:auto;animation:slideIn .3s ease}
        @keyframes slideIn{from{transform:translateY(-50px);opacity:0}to{transform:translateY(0);opacity:1}}
        .modal h2{margin-bottom:25px;color:#333}
        .form-group{margin-bottom:20px}
        .form-group label{display:block;margin-bottom:8px;font-weight:600;color:#495057}
        .form-group input,.form-group select,.form-group textarea{width:100%;padding:12px;border:2px solid #e0e0e0;border-radius:8px;font-size:14px}
        .form-group input:focus,.form-group select:focus,.form-group textarea:focus{outline:none;border-color:#667eea}
        .form-buttons{display:flex;gap:15px;margin-top:30px}
        .no-results{text-align:center;padding:50px;color:#6c757d;font-size:18px}
        .loading{text-align:center;padding:50px;color:#667eea}
        .notification{position:fixed;top:20px;right:20px;padding:20px 30px;border-radius:10px;color:white;font-weight:600;z-index:2000;animation:slideInRight .3s ease;box-shadow:0 5px 20px rgba(0,0,0,.3)}
        @keyframes slideInRight{from{transform:translateX(400px);opacity:0}to{transform:translateX(0);opacity:1}}
        .notification.success{background:linear-gradient(135deg,#11998e 0%,#38ef7d 100%)}
        .notification.error{background:linear-gradient(135deg,#eb3349 0%,#f45c43 100%)}
        @media (max-width:768px){.controls{flex-direction:column}.search-box{width:100%}.stats{grid-template-columns:1fr}.tabs{overflow-x:auto}}
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🏥 Pharmacy Admin Dashboard</h1>
            <div class="header-info">
                <span id="currentDate"></span>
            </div>
        </div>

        <div class="stats">
            <div class="stat-card">
                <h3>Total Medicines</h3>
                <div class="value" id="totalMedicines">0</div>
            </div>
            <div class="stat-card">
                <h3>Total Categories</h3>
                <div class="value" id="totalCategories">0</div>
            </div>
            <div class="stat-card">
                <h3>Low Stock Items</h3>
                <div class="value" id="lowStock">0</div>
            </div>
            <div class="stat-card">
                <h3>Total Value</h3>
                <div class="value" id="totalValue">$0</div>
            </div>
        </div>

        <div class="tabs">
            <button class="tab active" id="tab-medicines" onclick="switchTab('medicines')">💊 Medicines</button>
            <button class="tab" id="tab-categories" onclick="switchTab('categories')">📁 Categories</button>
        </div>

        <!-- Medicines Tab -->
        <div id="medicines-tab" class="tab-content active">
            <div class="controls">
                <div class="search-box">
                    <input type="text" id="medicineSearch" placeholder="🔍 Search medicines..." onkeyup="searchMedicines()">
                    <span class="search-icon">🔍</span>
                </div>
                <div class="filter-group">
                    <select id="medicineStockFilter" onchange="filterMedicines()">
                        <option value="all">All Stock Levels</option>
                        <option value="low">Low Stock (&lt;20)</option>
                        <option value="medium">Medium Stock (20-50)</option>
                        <option value="high">High Stock (&gt;50)</option>
                    </select>
                    <select id="medicineRxFilter" onchange="filterMedicines()">
                        <option value="all">All Prescriptions</option>
                        <option value="YES">Prescription Required</option>
                        <option value="NO">No Prescription</option>
                    </select>
                    <button class="btn btn-success" onclick="openMedicineModal()">➕ Add Medicine</button>
                </div>
            </div>

            <div class="table-container">
                <table id="medicineTable">
                    <thead>
                        <tr>
                            <th class="sortable" onclick="sortTable('medicines', 0)">ID</th>
                            <th class="sortable" onclick="sortTable('medicines', 1)">Medicine Name</th>
                            <th class="sortable" onclick="sortTable('medicines', 2)">Category</th>
                            <th class="sortable" onclick="sortTable('medicines', 3)">Price</th>
                            <th class="sortable" onclick="sortTable('medicines', 4)">Stock</th>
                            <th>Prescription</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="medicineTableBody">
                        <tr><td colspan="7" class="loading">Loading medicines...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Categories Tab -->
        <div id="categories-tab" class="tab-content">
            <div class="controls">
                <div class="search-box">
                    <input type="text" id="categorySearch" placeholder="🔍 Search categories..." onkeyup="searchCategories()">
                    <span class="search-icon">🔍</span>
                </div>
                <button class="btn btn-success" onclick="openCategoryModal()">➕ Add Category</button>
            </div>

            <div class="table-container">
                <table id="categoryTable">
                    <thead>
                        <tr>
                            <th class="sortable" onclick="sortTable('categories', 0)">ID</th>
                            <th class="sortable" onclick="sortTable('categories', 1)">Category Name</th>
                            <th class="sortable" onclick="sortTable('categories', 2)">Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="categoryTableBody">
                        <tr><td colspan="4" class="loading">Loading categories...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Medicine Modal -->
    <div id="medicineModal" class="modal">
        <div class="modal-content">
            <h2 id="medicineModalTitle">Add Medicine</h2>
            <form id="medicineForm">
                <input type="hidden" id="medicineId">
                <div class="form-group">
                    <label>Medicine Name *</label>
                    <input type="text" id="medicineName" required>
                </div>
                <div class="form-group">
                    <label>Category</label>
                    <select id="medicineCategory">
                        <option value="">Select Category</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Price *</label>
                    <input type="number" id="medicinePrice" step="0.01" required>
                </div>
                <div class="form-group">
                    <label>Stock *</label>
                    <input type="number" id="medicineStock" required>
                </div>
                <div class="form-group">
                    <label>Prescription Required</label>
                    <select id="medicinePrescription">
                        <option value="NO">No</option>
                        <option value="YES">Yes</option>
                    </select>
                </div>
                <div class="form-buttons">
                    <button type="submit" class="btn btn-success"> Save</button>
                    <button type="button" class="btn" onclick="closeMedicineModal()"> Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Category Modal -->
    <div id="categoryModal" class="modal">
        <div class="modal-content">
            <h2 id="categoryModalTitle">Add Category</h2>
            <form id="categoryForm">
                <input type="hidden" id="categoryId">
                <div class="form-group">
                    <label>Category Name *</label>
                    <input type="text" id="categoryName" required>
                </div>
                <div class="form-group">
                    <label>Description</label>
                    <textarea id="categoryDescription" rows="4"></textarea>
                </div>
                <div class="form-buttons">
                    <button type="submit" class="btn btn-success">💾 Save</button>
                    <button type="button" class="btn" onclick="closeCategoryModal()">❌ Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <script>
    // Front-end: talks to the PHP endpoints defined above using fetch
    let medicines = [];
    let categories = [];
    let sortOrder = {medicines: {}, categories: {}};
    let currentTab = 'medicines';

    document.addEventListener('DOMContentLoaded', function() {
        updateDate();
        loadData();
        document.getElementById('medicineForm').addEventListener('submit', medicineFormSubmit);
        document.getElementById('categoryForm').addEventListener('submit', categoryFormSubmit);
    });

    function api(action, opts = {}) {
        // Generic helper to call API endpoints
        const url = new URL(window.location.href);
        url.searchParams.set('action', action);
        if (opts.query) {
            Object.keys(opts.query).forEach(k => url.searchParams.set(k, opts.query[k]));
        }
        if (opts.method && opts.method.toUpperCase() === 'POST') {
            return fetch(url.toString(), {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(opts.body || {})
            }).then(r => r.json());
        } else {
            return fetch(url.toString()).then(r => r.json());
        }
    }

    async function loadData() {
        const all = await api('list');
        medicines = all.medicines || [];
        categories = all.categories || [];
        renderMedicines();
        renderCategories();
        populateCategoryDropdown();
        updateStats();
    }

    async function updateStats() {
        const s = await api('stats');
        document.getElementById('totalMedicines').textContent = s.totalMedicines ?? 0;
        document.getElementById('totalCategories').textContent = s.totalCategories ?? 0;
        document.getElementById('lowStock').textContent = s.lowStock ?? 0;
        document.getElementById('totalValue').textContent = '$' + (s.totalValue ?? 0).toFixed(2);
    }

    function updateDate() {
        const now = new Date();
        document.getElementById('currentDate').textContent = now.toLocaleDateString('en-US', {
            weekday: 'long', year:'numeric', month:'long', day:'numeric'
        });
    }

    function switchTab(tab) {
        currentTab = tab;
        document.querySelectorAll('.tab').forEach(t => t.classList.remove('active'));
        document.querySelectorAll('.tab-content').forEach(t => t.classList.remove('active'));
        document.getElementById('tab-'+tab).classList.add('active');
        document.getElementById(tab + '-tab').classList.add('active');
    }

    // Render functions
    function renderMedicines(data = medicines) {
        const tbody = document.getElementById('medicineTableBody');
        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="7" class="no-results">No medicines found</td></tr>';
            return;
        }
        tbody.innerHTML = data.map(med => `
            <tr>
                <td>${med.id}</td>
                <td><strong>${escapeHtml(med.name)}</strong></td>
                <td>${escapeHtml(med.category || 'Uncategorized')}</td>
                <td>$${Number(med.price).toFixed(2)}</td>
                <td><span class="badge ${getStockBadge(med.stock)}">${med.stock} units</span></td>
                <td><span class="badge ${med.prescription === 'YES' ? 'badge-warning' : 'badge-success'}">${med.prescription === 'YES' ? '📋 Required' : '✓ Not Required'}</span></td>
                <td>
                    <div class="action-btns">
                        <button class="action-btn btn-success" onclick="editMedicine(${med.id})">✏️ Edit</button>
                        <button class="action-btn btn-danger" onclick="deleteMedicine(${med.id})">🗑️ Delete</button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function renderCategories(data = categories) {
        const tbody = document.getElementById('categoryTableBody');
        if (!data || data.length === 0) {
            tbody.innerHTML = '<tr><td colspan="4" class="no-results">No categories found</td></tr>';
            return;
        }
        tbody.innerHTML = data.map(cat => `
            <tr>
                <td>${cat.id}</td>
                <td><strong>${escapeHtml(cat.name)}</strong></td>
                <td>${escapeHtml(cat.description)}</td>
                <td>
                    <div class="action-btns">
                        <button class="action-btn btn-success" onclick="editCategory(${cat.id})">✏️ Edit</button>
                        <button class="action-btn btn-danger" onclick="deleteCategory(${cat.id})">🗑️ Delete</button>
                    </div>
                </td>
            </tr>
        `).join('');
    }

    function getStockBadge(stock) {
        if (stock < 20) return 'badge-danger';
        if (stock < 50) return 'badge-warning';
        return 'badge-success';
    }

    // Search & filter
    function searchMedicines() {
        const q = document.getElementById('medicineSearch').value.toLowerCase();
        const filtered = medicines.filter(m => (m.name || '').toLowerCase().includes(q) || (m.category || '').toLowerCase().includes(q));
        renderMedicines(filtered);
    }

    function filterMedicines() {
        const stockFilter = document.getElementById('medicineStockFilter').value;
        const rxFilter = document.getElementById('medicineRxFilter').value;
        let filtered = medicines.slice();
        if (stockFilter !== 'all') {
            filtered = filtered.filter(m => {
                if (stockFilter === 'low') return m.stock < 20;
                if (stockFilter === 'medium') return m.stock >= 20 && m.stock <= 50;
                if (stockFilter === 'high') return m.stock > 50;
            });
        }
        if (rxFilter !== 'all') {
            filtered = filtered.filter(m => m.prescription === rxFilter);
        }
        renderMedicines(filtered);
    }

    // Sorting
    function sortTable(type, column) {
        const data = type === 'medicines' ? medicines : categories;
        const key = sortOrder[type][column] || 'asc';
        const newOrder = key === 'asc' ? 'desc' : 'asc';
        sortOrder[type][column] = newOrder;

        data.sort((a,b) => {
            let valA, valB;
            if (type === 'medicines') {
                const keys = ['id','name','category','price','stock'];
                valA = a[keys[column]] ?? '';
                valB = b[keys[column]] ?? '';
            } else {
                const keys = ['id','name','description'];
                valA = a[keys[column]] ?? '';
                valB = b[keys[column]] ?? '';
            }
            if (typeof valA === 'string') {
                return newOrder === 'asc' ? valA.localeCompare(valB) : valB.localeCompare(valA);
            }
            return newOrder === 'asc' ? valA - valB : valB - valA;
        });

        updateSortIcons(type, column, newOrder);
        type === 'medicines' ? renderMedicines() : renderCategories();
    }

    function updateSortIcons(type, column, order) {
        const table = type === 'medicines' ? 'medicineTable' : 'categoryTable';
        document.querySelectorAll(`#${table} th`).forEach((th, i) => {
            th.classList.remove('sort-asc','sort-desc');
            if (i === column) th.classList.add(`sort-${order}`);
        });
    }

    // Modals & forms
    function openMedicineModal(id = null) {
        document.getElementById('medicineModal').classList.add('active');
        document.getElementById('medicineForm').reset();
        if (id) {
            const med = medicines.find(m => m.id === id);
            document.getElementById('medicineModalTitle').textContent = 'Edit Medicine';
            document.getElementById('medicineId').value = med.id;
            document.getElementById('medicineName').value = med.name;
            document.getElementById('medicineCategory').value = med.categoryId ?? '';
            document.getElementById('medicinePrice').value = med.price;
            document.getElementById('medicineStock').value = med.stock;
            document.getElementById('medicinePrescription').value = med.prescription;
        } else {
            document.getElementById('medicineModalTitle').textContent = 'Add Medicine';
            document.getElementById('medicineId').value = '';
        }
    }

    function closeMedicineModal() { document.getElementById('medicineModal').classList.remove('active'); }

    function editMedicine(id) { openMedicineModal(id); }

    async function deleteMedicine(id) {
        if (!confirm('Are you sure you want to delete this medicine?')) return;
        await api('delete_medicine', { query: { id } });
        await loadData();
        showNotification('Medicine deleted successfully', 'success');
    }

    async function medicineFormSubmit(e) {
        e.preventDefault();
        const id = document.getElementById('medicineId').value;
        const payload = {
            name: document.getElementById('medicineName').value,
            categoryId: Number(document.getElementById('medicineCategory').value) || 0,
            price: parseFloat(document.getElementById('medicinePrice').value) || 0,
            stock: parseInt(document.getElementById('medicineStock').value) || 0,
            prescription: document.getElementById('medicinePrescription').value
        };
        if (id) {
            payload.id = Number(id);
            await api('update_medicine', { method: 'POST', body: payload });
            showNotification('Medicine updated successfully', 'success');
        } else {
            await api('add_medicine', { method: 'POST', body: payload });
            showNotification('Medicine added successfully', 'success');
        }
        await loadData();
        closeMedicineModal();
    }

    // Category functions
    function openCategoryModal(id = null) {
        document.getElementById('categoryModal').classList.add('active');
        document.getElementById('categoryForm').reset();
        if (id) {
            const cat = categories.find(c => c.id === id);
            document.getElementById('categoryModalTitle').textContent = 'Edit Category';
            document.getElementById('categoryId').value = cat.id;
            document.getElementById('categoryName').value = cat.name;
            document.getElementById('categoryDescription').value = cat.description;
        } else {
            document.getElementById('categoryModalTitle').textContent = 'Add Category';
            document.getElementById('categoryId').value = '';
        }
    }
    function closeCategoryModal() { document.getElementById('categoryModal').classList.remove('active'); }
    function editCategory(id) { openCategoryModal(id); }

    async function deleteCategory(id) {
        if (!confirm('Are you sure you want to delete this category?')) return;
        await api('delete_category', { query: { id } });
        await loadData();
        showNotification('Category deleted successfully', 'success');
    }

    async function categoryFormSubmit(e) {
        e.preventDefault();
        const id = document.getElementById('categoryId').value;
        const payload = {
            name: document.getElementById('categoryName').value,
            description: document.getElementById('categoryDescription').value
        };
        if (id) {
            payload.id = Number(id);
            await api('update_category', { method: 'POST', body: payload });
            showNotification('Category updated successfully', 'success');
        } else {
            await api('add_category', { method: 'POST', body: payload });
            showNotification('Category added successfully', 'success');
        }
        await loadData();
        populateCategoryDropdown();
        closeCategoryModal();
    }

    function populateCategoryDropdown() {
        const select = document.getElementById('medicineCategory');
        select.innerHTML = '<option value="">Select Category</option>' + (categories.map(cat => `<option value="${cat.id}">${escapeHtml(cat.name)}</option>`).join(''));
    }

    function showNotification(message, type='success') {
        const notification = document.createElement('div');
        notification.className = `notification ${type}`;
        notification.textContent = message;
        document.body.appendChild(notification);
        setTimeout(() => notification.remove(), 3000);
    }

    // Close modals on outside click
    window.onclick = function(event) {
        if (event.target.classList.contains('modal')) event.target.classList.remove('active');
    }

    // small utilities
    function escapeHtml(str){ if(!str) return ''; return String(str).replace(/[&<>"'`=\/]/g, function(s){ return {'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;','/':'&#x2F;','`':'&#x60;','=':'&#x3D;'}[s]; }); }
    </script>
</body>
</html>
