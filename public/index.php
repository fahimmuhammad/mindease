<?php require_once __DIR__ . '/../src/lib/auth_guard.php'; ?>
<?php include __DIR__ . '/../src/pages/_nav.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <title>MindEase</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

<div class="container">

    <h1>MindEase</h1>
    <p>A simple AI-assisted tool to check your stress and get quick coping suggestions.</p>

    <div class="card" style="margin-top:30px;">

        <h2>Quick Actions</h2>

        <a href="quick_check.php">
            <button class="primary">Quick Check</button>
        </a>

        <a href="text_check.php">
            <button class="primary" style="margin-left:10px;">Text Check</button>
        </a>

        <a href="actions.php">
            <button class="primary" style="margin-left:10px;">All Activities</button>
        </a>

        <a href="history.php">
            <button class="primary" style="margin-left:10px;">History</button>
        </a>

    </div>

</div>

</body>
</html>
