<?php
?>

</div>
</div>
<!-- End of main content container -->

<!-- Footer -->
<footer class="bg-gray-800 text-white mt-auto">
    <div class="container mx-auto px-4 py-6">
        <div class="flex justify-end items-center">
            <div class="flex items-center space-x-4">
                <a href="dashboard.php" class="text-gray-300 hover:text-white text-sm">Dashboard</a>
                <a href="rooms.php" class="text-gray-300 hover:text-white text-sm">Rooms</a>
                <a href="bookings.php" class="text-gray-300 hover:text-white text-sm">Bookings</a>
                <a href="users.php" class="text-gray-300 hover:text-white text-sm">Users</a>
                <a href="../index.php" class="text-gray-300 hover:text-white text-sm">View Website</a>
            </div>
        </div>
        
        <div class="mt-4 border-t border-gray-700 pt-4 text-center text-xs text-gray-400 flex flex-col gap-4">
            <p>For admin use only. Unauthorized access is prohibited.</p>
            <p class="text-sm">&copy; <?php echo date('Y'); ?> Hotel Management System - Admin Panel</p>
        </div>
    </div>
</footer>

<!-- JavaScript -->
<script>
    // Toggle mobile menu
    document.addEventListener('DOMContentLoaded', function() {
        const mobileMenuButton = document.getElementById('mobile-menu-button');
        const mobileMenu = document.getElementById('mobile-menu');

        if (mobileMenuButton && mobileMenu) {
            mobileMenuButton.addEventListener('click', function() {
                mobileMenu.classList.toggle('hidden');
            });
        }

        // Add current page highlighting
        const currentPath = window.location.pathname;
        const filename = currentPath.substring(currentPath.lastIndexOf('/') + 1);

        document.querySelectorAll('nav a').forEach(link => {
            const linkHref = link.getAttribute('href');
            if (linkHref === filename) {
                link.classList.add('bg-gray-900');
                link.classList.add('text-white');
            }
        });
    });

    // Confirmation for delete actions
    function confirmDelete(itemType, itemId) {
        return confirm(`Are you sure you want to delete this ${itemType}? This action cannot be undone.`);
    }
</script>

<!-- Additional Scripts -->
<?php if (isset($page_scripts)): ?>
    <?php foreach ($page_scripts as $script): ?>
        <script src="<?php echo htmlspecialchars($script); ?>"></script>
    <?php endforeach; ?>
<?php endif; ?>

</body>

</html>