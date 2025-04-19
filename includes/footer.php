<?php if (isset($_SESSION['user_id']) && basename($_SERVER['PHP_SELF']) !== 'login.php' && basename($_SERVER['PHP_SELF']) !== 'register.php'): ?>
            </div>
            <!-- End of Page Content -->
        </div>
        <!-- End of Main Content -->
    </div>
    <!-- End of Wrapper -->
<?php endif; ?>

<!-- Custom JS -->
<script src="../assets/js/main.js"></script>
<script src="../assets/js/validations.js"></script>

</body>
</html>
