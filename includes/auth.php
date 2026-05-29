    <?php

    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }


    function require_login($base = '') {
        if (empty($_SESSION['user_id'])) {
            header('Location: ' . $base . 'login.php');
            exit;
        }
    }

    function require_admin($base = '') {
        require_login($base);
        if ($_SESSION['user_type'] !== 'Administrator') {
            header('Location: ' . $base . 'index.php');
            exit;
        }
    }

    /**
     * Check if the current user has a given role permission.
     * Roles ordered:  Administrator > Creator > Updater > Remover > Viewer
     *
     * @param string $needed  – minimum role required
     * @return bool
     */
    function can($needed) {
        $roles = ['Viewer'=>1, 'Remover'=>2, 'Updater'=>3, 'Creator'=>4, 'Administrator'=>5];
        $current = $_SESSION['user_role'] ?? 'Viewer';
        return ($roles[$current] ?? 0) >= ($roles[$needed] ?? 99);
    }
    ?>