<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    $dbHost = $_POST['db_host'] ?? '';
    $dbName = $_POST['db_name'] ?? '';
    $dbUser = $_POST['db_user'] ?? '';
    $dbPass = $_POST['db_pass'] ?? '';
    $smtpHost = $_POST['smtp_host'] ?? '';
    $smtpUser = $_POST['smtp_user'] ?? '';
    $smtpPass = $_POST['smtp_pass'] ?? '';
    $smtpPort = $_POST['smtp_port'] ?? '';
    $smtpFrom = $_POST['smtp_from'] ?? '';
    $adminUser = $_POST['admin_user'] ?? '';
    $adminPass = $_POST['admin_pass'] ?? '';
    $adminEmail = $_POST['admin_email'] ?? '';

    
    if (empty($dbHost) || empty($dbName) || empty($dbUser) || empty($dbPass) || empty($smtpHost) || empty($smtpUser) || empty($smtpPass) || empty($smtpPort) || empty($smtpFrom) || empty($adminUser) || empty($adminPass) || empty($adminEmail)) {
        echo "Error: All fields are required.";
        exit;
    }

    
    $requiredExtensions = [
        'pdo_mysql', 'mysqli', 'mbstring', 'json', 'xml', 'curl', 'fileinfo', 'zip', 'gd', 'soap', 'intl', 'bcmath', 'openssl', 'iconv'
    ];
    $missingExtensions = array_filter($requiredExtensions, function($ext) {
        return !extension_loaded($ext);
    });

    if (!empty($missingExtensions)) {
        echo "<p class='error'>The following PHP extensions are missing: " . implode(', ', $missingExtensions) . "</p>";
        echo "<button onclick='recheck()' class='btn-recheck'>Recheck</button>";
        exit;
    }

    
    try {
        $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch (PDOException $e) {
        echo "Database connection failed: " . $e->getMessage();
        exit;
    }

    
    if (!file_exists('database.sql')) {
        echo "<p class='error'>SQL file 'database.sql' not found.</p>";
        echo "<button onclick='recheck()' class='btn-recheck'>Recheck</button>";
        exit;
    }

    
    $sql = file_get_contents('database.sql');
    $pdo->exec($sql);

    // Save to config.php
    $configContent = "<?php\n";
    $configContent .= "define('DB_HOST', '$dbHost');\n";
    $configContent .= "define('DB_NAME', '$dbName');\n";
    $configContent .= "define('DB_USER', '$dbUser');\n";
    $configContent .= "define('DB_PASS', '$dbPass');\n";
    $configContent .= "define('SMTP_HOST', '$smtpHost');\n";
    $configContent .= "define('SMTP_USER', '$smtpUser');\n";
    $configContent .= "define('SMTP_PASS', '$smtpPass');\n";
    $configContent .= "define('SMTP_PORT', $smtpPort);\n";
    $configContent .= "define('SMTP_FROM', '$smtpFrom');\n";
    $configContent .= "define('ADMIN_USER', '$adminUser');\n";
    $configContent .= "define('ADMIN_PASS', '$adminPass');\n";
    $configContent .= "define('ADMIN_EMAIL', '$adminEmail');\n";
    $configContent .= "?>";

    file_put_contents('config.php', $configContent);

    // Delete install.php
    if (file_exists(__FILE__)) {
        unlink(__FILE__);
    }

    echo "Configuration saved successfully. The installer has been removed.";
    exit;
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>It's me Dev_[ Moin ] installer</title>
    <link rel="stylesheet" href="assets/styles.css">
    <script>
        function recheck() {
            location.reload();
        }

        function showForm() {
            document.getElementById('requirements').style.display = 'none';
            document.getElementById('form').style.display = 'block';
        }
    </script>
</head>
<body>
    <div class="container">
        <div id="requirements">
            <h2>System Requirements</h2>
            <div class="extension-status">
                <?php
                foreach ($requiredExtensions as $ext) {
                    $isOk = extension_loaded($ext);
                    $statusClass = $isOk ? 'ok' : 'not-ok';
                    $statusImage = $isOk ? 'checkmark.png' : 'cross.png';
                    echo "<div class='status $statusClass'>
                            <img src='assets/$statusImage' alt='$ext'>
                            <span>$ext: " . ($isOk ? 'Installed' : 'Not Installed') . "</span>
                        </div>";
                }
                ?>
            </div>
            <div class="extension-status">
                <?php
                if (file_exists('database.sql')) {
                    echo "<div class='status ok'>
                            <img src='assets/checkmark.png' alt='SQL File'>
                            <span>SQL File: Found</span>
                        </div>";
                } else {
                    echo "<div class='status not-ok'>
                            <img src='assets/cross.png' alt='SQL File'>
                            <span>SQL File: Not Found</span>
                        </div>";
                }
                ?>
            </div>
            <button onclick="showForm()" class="btn-continue">Continue</button>
        </div>

        <div id="form" style="display:none;">
            <form method="post" action="">
                <h2>Database Settings</h2>
                <label for="db_host">Database Host:</label>
                <input type="text" id="db_host" name="db_host" required><br>
                <label for="db_name">Database Name:</label>
                <input type="text" id="db_name" name="db_name" required><br>
                <label for="db_user">Database User:</label>
                <input type="text" id="db_user" name="db_user" required><br>
                <label for="db_pass">Database Password:</label>
                <input type="password" id="db_pass" name="db_pass" required><br>

                <h2>SMTP Settings</h2>
                <label for="smtp_host">SMTP Host:</label>
                <input type="text" id="smtp_host" name="smtp_host" required><br>
                <label for="smtp_user">SMTP Username:</label>
                <input type="text" id="smtp_user" name="smtp_user" required><br>
                <label for="smtp_pass">SMTP Password:</label>
                <input type="password" id="smtp_pass" name="smtp_pass" required><br>
                <label for="smtp_port">SMTP Port:</label>
                <input type="text" id="smtp_port" name="smtp_port" required><br>
                <label for="smtp_from">SMTP From:</label>
                <input type="email" id="smtp_from" name="smtp_from" required><br>

                <h2>Admin Settings</h2>
                <label for="admin_user">Admin Username:</label>
                <input type="text" id="admin_user" name="admin_user" required><br>
                <label for="admin_pass">Admin Password:</label>
                <input type="password" id="admin_pass" name="admin_pass" required><br>
                <label for="admin_email">Admin Email:</label>
                <input type="email" id="admin_email" name="admin_email" required><br>

                <input type="submit" value="Save Configuration">
            </form>
        </div>
    </div>
</body>
</html>