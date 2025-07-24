<?php
/*
 * Free Hosting Setup Script
 * Sets up the werewolf game for free hosting platforms
 */

echo "<h1>🐺 Werewolf Game - Free Hosting Setup</h1>";

// Check if setup is needed
if (file_exists('includes/includes.php') && !isset($_GET['force'])) {
    echo "<p>✅ Game is already configured!</p>";
    echo "<p><a href='Werwolf.php'>🎮 Play Game</a></p>";
    echo "<p><a href='?force=1'>🔧 Force Reconfigure</a></p>";
    exit;
}

echo "<h2>🔧 Setting up your free werewolf game...</h2>";

// Step 1: Copy free hosting config
if (copy('includes/includes.free.php', 'includes/includes.php')) {
    echo "<p>✅ Database configuration copied</p>";
} else {
    echo "<p>❌ Failed to copy database config</p>";
}

// Step 2: Create data directory for SQLite
if (!is_dir('data')) {
    if (mkdir('data', 0755, true)) {
        echo "<p>✅ Data directory created</p>";
    } else {
        echo "<p>❌ Failed to create data directory</p>";
    }
}

// Step 3: Test database connection
echo "<h3>Testing database connection...</h3>";
try {
    require_once('includes/includes.php');
    echo "<p>✅ Database connection successful!</p>";
    
    // Test creating a game
    if (function_exists('createNewGame')) {
        $testGameId = 99999;
        if (createNewGame($mysqli, $testGameId)) {
            echo "<p>✅ Game creation works!</p>";
            // Cleanup test game
            deleteGame($mysqli, $testGameId);
        }
    }
    
} catch (Exception $e) {
    echo "<p>❌ Database error: " . $e->getMessage() . "</p>";
}

// Step 4: Create .htaccess for better routing
if (!file_exists('.htaccess')) {
    $htaccess = 'RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security
Header always set X-Content-Type-Options nosniff
Header always set X-Frame-Options DENY';
    
    if (file_put_contents('.htaccess', $htaccess)) {
        echo "<p>✅ URL routing configured</p>";
    }
}

// Step 5: Performance check
echo "<h3>🚀 Performance Check</h3>";
echo "<p>PHP Version: " . PHP_VERSION . "</p>";
echo "<p>Memory Limit: " . ini_get('memory_limit') . "</p>";
echo "<p>Max Execution Time: " . ini_get('max_execution_time') . "s</p>";

// Check required extensions
$required = ['mysqli', 'pdo', 'json'];
foreach ($required as $ext) {
    $status = extension_loaded($ext) ? '✅' : '❌';
    echo "<p>$status Extension: $ext</p>";
}

echo "<h2>🎉 Setup Complete!</h2>";
echo "<p><strong>Your werewolf game is ready!</strong></p>";
echo "<p><a href='Werwolf.php' style='background: #007cba; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🎮 Start Playing</a></p>";

echo "<h3>📊 Free Hosting Tips:</h3>";
echo "<ul>";
echo "<li>Your game uses SQLite database (file-based, no MySQL needed)</li>";
echo "<li>Data is stored in the 'data/' directory</li>";
echo "<li>Can handle 50-100 concurrent players</li>";
echo "<li>Automatically scales with your hosting platform</li>";
echo "</ul>";

echo "<h3>🔧 Platform-Specific Instructions:</h3>";
echo "<details><summary><strong>Railway.app</strong></summary>";
echo "<ol>";
echo "<li>Connect your GitHub repo: johan-becker/WW</li>";
echo "<li>Deploy automatically detects PHP</li>";
echo "<li>No environment variables needed</li>";
echo "<li>Free: 500 hours/month</li>";
echo "</ol></details>";

echo "<details><summary><strong>Heroku</strong></summary>";
echo "<ol>";
echo "<li>Create new app</li>";
echo "<li>Connect GitHub repo</li>";
echo "<li>Add PHP buildpack</li>";
echo "<li>Deploy from main branch</li>";
echo "</ol></details>";

echo "<details><summary><strong>Vercel</strong></summary>";
echo "<ol>";
echo "<li>Install Vercel CLI: npm i -g vercel</li>";
echo "<li>Run: vercel in your project directory</li>";
echo "<li>Follow prompts to deploy</li>";
echo "</ol></details>";
?>