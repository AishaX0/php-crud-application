<nav class="navbar navbar-expand-lg navbar-light bg-success shadow-sm">
    <div class="container">
        <a class="navbar-brand text-white font-weight-bold" href="index.php">
            <i class="fas fa-sticky-note mr-2"></i> My Notes
        </a>

        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="d-flex align-items-center">
            <?php if ($is_logged_in): ?>
                <a href="profile.php" class="btn btn-light mr-2">
                    <i class="fas fa-user-circle"></i> Profile
                </a>

                <form method="POST" style="display: inline;">
                    <button type="submit" name="logout" class="btn btn-danger">
                        <i class="fas fa-sign-out-alt"></i> Logout
                    </button>
                </form>
            <?php else: ?>
                <a href="login.html" class="btn btn-light">
                    <i class="fas fa-sign-in-alt"></i> Login
                </a>
            <?php endif; ?>
        </div>
    </div>
</nav>
