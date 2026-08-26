    </main>

    <!-- Footer Footer Bar -->
    <footer class="bg-white border-t border-gray-200 mt-12 py-6 text-center text-xs text-gray-500">
        <div class="max-w-7xl mx-auto px-4">
            <p class="font-medium text-gray-700">© 2026 TRƯỜNG ĐẠI HỌC HNDA - CỔNG THÔNG TIN GIẢNG VIÊN</p>
            <p class="mt-1 text-gray-400">Thực hành PHP - Buổi 4: Quản lý điểm, Validate Server-side & Kết nối SQL Server Manager</p>
        </div>
    </footer>

    <!-- JavaScript For Interactivity -->
    <script>
        // Toggle Nhập điểm modal
        function openAddModal() {
            document.getElementById('addStudentModal').classList.remove('hidden');
        }

        function closeAddModal() {
            document.getElementById('addStudentModal').classList.add('hidden');
        }

        // Auto calculate total and grade on input change in modal
        function calculateModalGrade() {
            const cc = parseFloat(document.getElementById('modal_diem_cc').value) || 0;
            const gk = parseFloat(document.getElementById('modal_diem_gk').value) || 0;
            const ck = parseFloat(document.getElementById('modal_diem_ck').value) || 0;
            const total = (cc * 0.10) + (gk * 0.30) + (ck * 0.60);
            document.getElementById('modal_tong_ket').value = total.toFixed(1);
        }
    </script>
</body>
</html>
