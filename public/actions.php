<?php include __DIR__ . '/../src/pages/_nav.php'; ?>
<?php
require_once __DIR__ . '/../src/config/config.php';
?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>All Activities — MindEase</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
      .action-list li { margin-bottom: 12px; }
      .action-meta { color:#666; font-size:0.95rem; }
      .actions-card { padding: 12px; border-radius: 10px; background:#fff; box-shadow:0 1px 6px rgba(12,20,30,0.05); }
    </style>
</head>
<body>

<div class="container">
    <h1>Available Coping Activities</h1>

    <div id="action-list" class="actions-card" style="margin-top:18px;">
        Loading actions...
    </div>
</div>

<script>
async function loadActions() {
    try {
        const res = await fetch('../src/api/actions.php');
        const data = await res.json();

        if (!data.success) {
            document.getElementById('action-list').innerHTML =
                "<p style='color:red;'>Failed to load actions.</p>";
            return;
        }

        const actions = data.data;
        if (!actions || actions.length === 0) {
            document.getElementById('action-list').innerHTML =
                "<p>No activities found.</p>";
            return;
        }

        let html = "<ul class='action-list' style='padding-left:18px; margin:0;'>";

        actions.forEach(a => {
            html += `
                <li>
                    <a href="action.php?id=${a.id}" style="text-decoration:none; color:#0b1220;">
                        <strong>${a.title}</strong>
                    </a>
                    <br>
                    <span class="action-meta">(${a.type}, ${a.duration_seconds} sec)</span>
                </li>
            `;
        });

        html += "</ul>";

        document.getElementById('action-list').innerHTML = html;

    } catch (e) {
        document.getElementById('action-list').innerHTML =
            "<p style='color:red;'>Error loading actions.</p>";
    }
}

loadActions();
</script>

</body>
</html>
