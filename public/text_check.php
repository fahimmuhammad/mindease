<?php require_once __DIR__ . '/../src/lib/auth_guard.php'; ?>
<?php
// public/text_check.php
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Text Check — MindEase</title>

  <link rel="stylesheet" href="css/style.css">
  <style>
    body { background:#f8fafc; color:#0f172a; font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, Arial; margin:0; }
    .container { max-width: 980px; margin: 28px auto; padding: 0 20px; }
    .card { background:#fff; border-radius:12px; box-shadow: 0 6px 18px rgba(2,6,23,0.04); padding:18px; border:1px solid rgba(15,23,42,0.03); }
    h1 { margin:0 0 16px 0; font-size:28px; }
    .small { font-size:13px; color:#64748b; }
    .spacer { height:14px; }
    .btn { padding:10px 14px; border-radius:10px; border:0; cursor:pointer; font-weight:600; }
    .btn.primary { background:#111827; color:#fff; }
    textarea { width:100%; min-height:120px; border-radius:8px; padding:12px; border:1px solid #e6eef6; }
    .note-error { color:#b91c1c; margin-top:10px; }
    .note-success { color:#166534; margin-top:10px; }
    .activity { margin-top:16px; border-left:4px solid #6366f1; padding-left:12px; }
  </style>
</head>
<body>

<?php @include __DIR__ . '/../src/pages/_nav.php'; ?>

<main class="container">
  <h1>Text Check</h1>

  <section class="card">
    <div class="small" style="margin-bottom:8px;font-weight:600">
      Describe how you're feeling:
    </div>

    <textarea id="textInput" placeholder="I am stressed because..."></textarea>

    <div class="spacer"></div>

    <label><input type="checkbox" id="useAI" checked> Use AI</label>
    <label style="margin-left:14px"><input type="checkbox" id="saveHistory" checked> Save to history</label>

    <div class="spacer"></div>

    <button id="analyzeBtn" class="btn primary">Analyze</button>
  </section>

  <div id="resultArea"></div>
</main>

<script>
const analyzeBtn = document.getElementById('analyzeBtn');
const resultArea = document.getElementById('resultArea');

async function fetchActions() {
  if (window._actions) return window._actions;
  const r = await fetch('/api/actions.php');
  const j = await r.json();
  window._actions = Array.isArray(j) ? j : (j.data || []);
  return window._actions;
}

function renderActivity(action) {
  return `
    <div class="card activity">
      <h4>${action.title}</h4>
      <p class="small">Type: ${action.type}</p>
      <ol>
        ${(action.content_json || []).map(s => `<li>${s.text}</li>`).join('')}
      </ol>
      <a href="/action.php?id=${action.id}" target="_blank" class="small">Open activity →</a>
    </div>
  `;
}

analyzeBtn.addEventListener('click', async () => {
  resultArea.innerHTML = '';

  const text = document.getElementById('textInput').value.trim();
  if (!text) {
    resultArea.innerHTML = '<div class="card note-error">Enter text</div>';
    return;
  }

  const loading = document.createElement('div');
  loading.className = 'card';
  loading.innerHTML = '<em>Analyzing…</em>';
  resultArea.appendChild(loading);

  try {
    const res = await fetch('/api/analyze_text.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({
        text: text,
        useAI: document.getElementById('useAI').checked
      })
    });

    const json = await res.json();
    loading.remove();

    if (!json.success) {
      resultArea.innerHTML = '<div class="card note-error">Analysis failed</div>';
      return;
    }

    const d = json.data;

    resultArea.innerHTML = `
      <div class="card">
        <h3>Score: ${d.score}/10</h3>
        <p>${d.summary}</p>
        <p class="small">Tags: ${(d.tone_tags||[]).join(', ')}</p>
        <p class="small">Provider: ${d.provider}</p>
      </div>
    `;

    // 🔹 ACTIVITY SUGGESTION
    const actions = await fetchActions();
    let action = null;

    if (d.suggestion_id) {
      action = actions.find(a => a.id == d.suggestion_id);
    }

    if (!action && d.score <= 2) {
      action = actions.find(a => a.type === 'grounding');
    }
    if (!action && d.score >= 7) {
      action = actions.find(a => a.type === 'breathing');
    }
    if (!action) action = actions[0];

    if (action) {
      resultArea.innerHTML += renderActivity(action);
    }

    // SAVE
    if (document.getElementById('saveHistory').checked) {
      await fetch('/api/save_entry.php', {
        method:'POST',
        headers:{'Content-Type':'application/json'},
        body: JSON.stringify({
          score: d.score,
          text: text,
          tone_tags: d.tone_tags,
          method: 'text',
          consent: true
        })
      });
    }

  } catch (e) {
    resultArea.innerHTML = '<div class="card note-error">Server error</div>';
  }
});
</script>

</body>
</html>
