import './branch-map.js';
import './attendance.js';

document.addEventListener('DOMContentLoaded', () => {
    const sidebarToggle = document.getElementById('sidebar-toggle');
    const sidebar = document.getElementById('sidebar');
    const overlay = document.getElementById('sidebar-overlay');
    const sidebarClose = document.getElementById('sidebar-close');

    function openSidebar() {
        if (sidebar) sidebar.classList.remove('-translate-x-full');
        if (overlay) overlay.classList.remove('hidden');
    }

    function closeSidebar() {
        if (sidebar) sidebar.classList.add('-translate-x-full');
        if (overlay) overlay.classList.add('hidden');
    }

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', openSidebar);
    }

    if (sidebarClose) {
        sidebarClose.addEventListener('click', closeSidebar);
    }

    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }

    // Cascading Dropdown Cabang -> Departemen -> Jabatan
    const branchSelect = document.getElementById('employee-branch-select');
    const departmentSelect = document.getElementById('employee-department-select');
    const designationSelect = document.getElementById('employee-designation-select');

    if (branchSelect && departmentSelect) {
        branchSelect.addEventListener('change', async (e) => {
            const branchId = e.target.value;
            departmentSelect.innerHTML = '<option value="">Memuat departemen...</option>';
            if (designationSelect) designationSelect.innerHTML = '<option value="">Pilih Departemen terlebih dahulu</option>';

            if (!branchId) {
                departmentSelect.innerHTML = '<option value="">Pilih Cabang terlebih dahulu</option>';
                return;
            }

            try {
                const res = await fetch(`/ajax/branches/${branchId}/departments`);
                const data = await res.json();
                departmentSelect.innerHTML = '<option value="">Pilih Departemen</option>';
                data.forEach(item => {
                    departmentSelect.innerHTML += `<option value="${item.id}">${item.name} (${item.code})</option>`;
                });
            } catch (err) {
                departmentSelect.innerHTML = '<option value="">Gagal memuat departemen</option>';
            }
        });
    }

    if (departmentSelect && designationSelect) {
        departmentSelect.addEventListener('change', async (e) => {
            const deptId = e.target.value;
            designationSelect.innerHTML = '<option value="">Memuat jabatan...</option>';

            if (!deptId) {
                designationSelect.innerHTML = '<option value="">Pilih Departemen terlebih dahulu</option>';
                return;
            }

            try {
                const res = await fetch(`/ajax/departments/${deptId}/designations`);
                const data = await res.json();
                designationSelect.innerHTML = '<option value="">Pilih Jabatan</option>';
                data.forEach(item => {
                    designationSelect.innerHTML += `<option value="${item.id}">${item.title}</option>`;
                });
            } catch (err) {
                designationSelect.innerHTML = '<option value="">Gagal memuat jabatan</option>';
            }
        });
    }

});
