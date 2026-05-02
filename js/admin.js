// Minimal admin JS: stubs for functions used in HTML
document.addEventListener('DOMContentLoaded', function(){
  // show current date
  const d = new Date();
  const el = document.getElementById('currentDate');
  if(el) el.textContent = d.toLocaleDateString();
});

function switchTab(tab){
  document.querySelectorAll('.tab').forEach(b=>b.classList.remove('active'));
  document.querySelectorAll('.tab-content').forEach(c=>c.classList.remove('active'));
  const btn = Array.from(document.querySelectorAll('.tab')).find(b=>b.textContent.toLowerCase().includes(tab));
  if(btn) btn.classList.add('active');
  const pane = document.getElementById(tab + '-tab');
  if(pane) pane.classList.add('active');
}

function openMedicineModal(){document.getElementById('medicineModal').style.display='flex'}
function closeMedicineModal(){document.getElementById('medicineModal').style.display='none'}
function openCategoryModal(){document.getElementById('categoryModal').style.display='flex'}
function closeCategoryModal(){document.getElementById('categoryModal').style.display='none'}

function searchMedicines(){/* implement filtering by name */}
function filterMedicines(){/* implement filtering by stock/prescription */}
function sortTable(section, col){/* implement sorting if needed */}
function searchCategories(){/* implement category search */}

// Form submissions (basic prevention of default behaviour)
const medForm = document.getElementById('medicineForm');
if(medForm){
  medForm.addEventListener('submit', function(e){
    e.preventDefault();
    // TODO: implement AJAX POST to API
    closeMedicineModal();
  });
}

const catForm = document.getElementById('categoryForm');
if(catForm){
  catForm.addEventListener('submit', function(e){
    e.preventDefault();
    // TODO: implement AJAX POST to API
    closeCategoryModal();
  });
}
