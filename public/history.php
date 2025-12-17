<?php include __DIR__ . '/../src/pages/_nav.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>History — MindEase</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
      .entry { margin-bottom: 14px; padding:12px; border-radius:8px; background:#fff; box-shadow:0 1px 4px rgba(0,0,0,0.04); }
      .entry-meta { color:#555; font-size:0.95rem; margin-bottom:8px; }
      .feedback-btn { margin-right:8px; }
      .activity-link { margin-top:6px; display:block; }
    </style>
</head>
<body>

<div class="container">
    <h1>Your Recent Checks</h1>

    <div id="history-area" class="card" style="margin-top:18px; padding:12px;">
        Loading history...
    </div>
</div>

<script>
async function loadHistory(limit = 20) {
    try {
        const res = await fetch('../src/api/history.php?limit=' + limit);
        const j = await res.json();

        if (!j.success) {
            document.getElementById('history-area').innerHTML =
                "<p style='color:red;'>Failed to load history.</p>";
            return;
        }

        const rows = j.data;
        if (!rows || rows.length === 0) {
            document.getElementById('history-area').innerHTML =
                "<p>No history yet. Try a Quick Check or Text Check.</p>";
            return;
        }

        let html = '';

        rows.forEach(r => {
            const tags = (r.tone_tags && r.tone_tags.length) ?
                r.tone_tags.join(', ') : '—';

            html += `
                <div class="entry">
                    <div class="entry-meta">
                        <strong>Score:</strong> ${r.score}/10
                        &nbsp;•&nbsp;
                        <strong>Method:</strong> ${r.method}
                        &nbsp;•&nbsp;
                        <strong>When:</strong> ${r.created_at}
                    </div>

                    <div><strong>Tags:</strong> ${tags}</div>
                    
                    ${r.suggestion_id ? `
                        <a class="activity-link" href="action.php?id=${r.suggestion_id}">
                            View Suggested Activity →
                        </a>
                    ` : ''}

                    <div style="margin-top:10px;">
                        <button class="primary feedback-btn" onclick="sendFeedback(${r.id}, 'helpful', this)">Helpful</button>
                        <button class="feedback-btn" onclick="sendFeedback(${r.id}, 'not_helpful', this)">Not helpful</button>
                    </div>
                </div>
            `;
        });

        document.getElementById('history-area').innerHTML = html;

    } catch (err) {
        console.error(err);
        document.getElementById('history-area').innerHTML =
            "<p style='color:red;'>Error loading history.</p>";
    }
}

async function sendFeedback(entryId, feedback, btn) {
    try {
        btn.disabled = true;

        const res = await fetch('../src/api/feedback.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ entry_id: entryId, feedback: feedback })
        });

        const j = await res.json();

        if (j.success) {
            btn.innerText = 'Recorded';
            btn.style.opacity = '0.8';
            setTimeout(() => loadHistory(), 600);
        } else {
            alert('Failed: ' + (j.error || 'unknown'));
            btn.disabled = false;
        }

    } catch (err) {
        alert('Network error');
        console.error(err);
        btn.disabled = false;
    }
}

// Load history on page load
loadHistory();
</script>

</body>
</html>
