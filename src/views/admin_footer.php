        </div>
    </div>

    <!-- ============================================================= -->
    <!-- ADMIN SCRIPTS -->
    <!-- ============================================================= -->
    <script>
        // ============================================================
        // Sidebar toggle (mobile)
        // ============================================================
        const sidebarToggle = document.getElementById('sidebar-toggle');
        const sidebar = document.getElementById('admin-sidebar');

        if (sidebarToggle && sidebar) {
            sidebarToggle.addEventListener('click', () => {
                sidebar.classList.toggle('-translate-x-full');
            });

            // Close sidebar when clicking outside on mobile
            document.addEventListener('click', (e) => {
                if (window.innerWidth < 1024 
                    && !sidebar.contains(e.target) 
                    && !sidebarToggle.contains(e.target)
                    && !sidebar.classList.contains('-translate-x-full')) {
                    sidebar.classList.add('-translate-x-full');
                }
            });
        }

        // ============================================================
        // User dropdown menu
        // ============================================================
        const userMenuBtn = document.getElementById('user-menu-btn');
        const userDropdown = document.getElementById('user-dropdown');

        if (userMenuBtn && userDropdown) {
            userMenuBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                userDropdown.classList.toggle('hidden');
            });

            document.addEventListener('click', () => {
                userDropdown.classList.add('hidden');
            });
        }
    </script>
</body>
</html>
