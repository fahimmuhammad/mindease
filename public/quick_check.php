<?php require_once __DIR__ . '/../src/lib/auth_guard.php'; ?>
<?php
// public/quick_check.php
?>
<!doctype html>
<html lang="en">
<head>
  <meta charset="utf-8">
  <title>Quick Check — MindEase</title>
  <meta name="viewport" content="width=device-width,initial-scale=1">

  <link rel="stylesheet" href="/css/style.css">
  <style>
    body { background:#f8fafc; font-family:system-ui,-apple-system,Segoe UI,Roboto; margin:0; }
    .container { max-width:900px; margin:30px auto; padding:0 16px; }
    .card { background:#fff; padding:18px; border-radius:12px; box-shadow:0 6px 18px rgba(0,0,0,.06); }
    .muted { color:#64748b; font-size:14px; }
    .spacer { height:16px; }
  </style>
</head>
<body>

<?php @include __DIR__ . '/../src/pages/_nav.php'; ?>

<main class="container">
  <h1>Quick Check</h1>

  <section class="card">
    <label class="muted">Select your stress level (0 = calm, 10 = stressed)</label>
    <input id="slider" type="range" min="0" max="10" value="5" style="width:100%">
    <div class="muted">Current: <span id="sliderValue">5</span>/10</div>

    <div class="spacer"></div>

    <label><input type="checkbox" id="useAI" checked> Use AI</label><br>
    <label><input type="checkbox" id="consent" checked> Save to history</label>

    <div class="spacer"></div>
    <button id="analyzeBtn" class="btn primary">Analyze</button>
  </section>

  <div class="spacer"></div>
  <div id="result"></div>
  <div id="suggestion"></div>
</main>

<script>
const slider = document.getElementById('slider');
const sliderValue = document.getElementById('sliderValue');
slider.oninput = () => sliderValue.textContent = slider.value;

document.getElementById('analyzeBtn').onclick = analyzeQuick;

async function analyzeQuick() {
  const score = Number(slider.value);
  const consent = document.getElementById('consent').checked;

  document.getElementById('result').innerHTML =
    '<div class="card"><em>Analyzing…</em></div>';

  try {
    const res = await fetch('/api/analyze_quick.php', {
      method: 'POST',
      headers: {'Content-Type':'application/json'},
      body: JSON.stringify({ score, consent })
    });

    const json = await res.json();

    if (!json.success) throw new Error('API failed');

    document.getElementById('result').innerHTML = `
      <div class="card">
        <h3>Score: ${json.data.score}/10</h3>
        <p>${json.data.summary}</p>
      </div>
    `;

    if (json.data.suggestion_id) loadSuggestion(json.data.suggestion_id);

  } catch (e) {
    document.getElementById('result').innerHTML =
      '<div class="card" style="color:red">Error connecting to server</div>';
  }
}

async function loadSuggestion(id) {
  const res = await fetch('/api/actions.php');
  const actions = await res.json();
  const act = actions.find(a => Number(a.id) === Number(id));
  if (!act) return;

  document.getElementById('suggestion').innerHTML = `
    <div class="card">
      <h4>${act.title}</h4>
      <p>${act.type}</p>
      <button onclick="window.open('/action.php?id=${act.id}','_blank')">
        Open activity
      </button>
    </div>
  `;
}
</script>

</body>
</html>
