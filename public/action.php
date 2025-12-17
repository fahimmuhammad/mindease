<?php require_once __DIR__ . '/../src/lib/auth_guard.php'; ?>

<?php
// public/action.php — Modern UI activity page with step player
// Paste this file into your project's public folder, replacing existing action.php

// load config
$configPath1 = __DIR__ . '/../src/config/config.php';
$configPath2 = __DIR__ . '/../src/config.php';
$config = null;
if (file_exists($configPath1)) {
    $config = include $configPath1;
} elseif (file_exists($configPath2)) {
    $config = include $configPath2;
} else {
    die('Configuration file not found.');
}

// connect db
$conn = @mysqli_connect(
    $config['db']['host'] ?? 'localhost',
    $config['db']['user'] ?? 'root',
    $config['db']['pass'] ?? '',
    $config['db']['name'] ?? 'mindease'
);
if (!$conn) {
    die('Database connection failed: ' . mysqli_connect_error());
}
mysqli_set_charset($conn, 'utf8mb4');

// read id
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    die('Invalid activity id.');
}

// fetch activity
$sql = "SELECT id, slug, title, type, duration_seconds, content_json FROM actions WHERE id = ? LIMIT 1";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, 'i', $id);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);
$activity = mysqli_fetch_assoc($res);
mysqli_stmt_close($stmt);

if (!$activity) {
    http_response_code(404);
    die('Activity not found.');
}

// decode content
$content_json = $activity['content_json'] ?? '';
$content = json_decode($content_json, true);
if ($content === null) $content = [];

// normalize steps into $steps = array of ['text'=>..., 'step'=>n (optional)]
$steps = [];
if (is_array($content) && isset($content[0]) && (isset($content[0]['text']) || is_string($content[0]))) {
    // array of objects OR strings
    foreach ($content as $i => $item) {
        if (is_array($item)) {
            $steps[] = ['text' => $item['text'] ?? json_encode($item), 'meta' => $item];
        } else {
            $steps[] = ['text' => (string)$item, 'meta' => null];
        }
    }
} elseif (isset($content['steps']) && is_array($content['steps'])) {
    foreach ($content['steps'] as $i => $item) {
        $steps[] = ['text' => $item['text'] ?? (string)$item, 'meta' => $item];
    }
} elseif (isset($content['content']) && is_array($content['content'])) {
    foreach ($content['content'] as $i => $item) {
        $steps[] = ['text' => $item['text'] ?? (string)$item, 'meta' => $item];
    }
} elseif (isset($content['instructions']) && is_array($content['instructions'])) {
    foreach ($content['instructions'] as $i => $item) {
        $steps[] = ['text' => $item['text'] ?? (string)$item, 'meta' => $item];
    }
} else {
    // fallback: if content not structured, try to display as single block
    if (!empty($content_json)) {
        $steps[] = ['text' => trim(strip_tags((string)$content_json)), 'meta' => null];
    }
}

// page variables
$title = $activity['title'] ?: 'Activity';
$type = $activity['type'] ?: '';
$duration_seconds = (int)$activity['duration_seconds'];

?><!doctype html>
<html lang="en">
<head>
<meta charset="utf-8" />
<meta name="viewport" content="width=device-width,initial-scale=1" />
<title><?php echo htmlspecialchars($title); ?> — MindEase</title>

<!-- Minimal modern CSS (self-contained) -->
<style>
:root{
  --bg:#f5f7fb; --card:#ffffff; --muted:#64748b; --accent:#0f172a; --accent-2:#7c3aed;
  --radius:14px; --shadow:0 10px 30px rgba(15,23,42,0.06);
  --glass: rgba(255,255,255,0.85);
}
*{box-sizing:border-box}
body{font-family:Inter,ui-sans-serif,system-ui,-apple-system,"Segoe UI",Roboto,"Helvetica Neue",Arial; background:var(--bg); margin:0; color:#0f172a; -webkit-font-smoothing:antialiased}
.container{max-width:1100px;margin:36px auto;padding:0 20px}
.header{display:flex;align-items:center;justify-content:space-between;margin-bottom:18px}
.breadcrumb{color:var(--muted);font-size:14px}
.card{background:var(--card);border-radius:var(--radius);box-shadow:var(--shadow);padding:28px}
.grid{display:grid;grid-template-columns: 1fr 360px;gap:28px;align-items:start}
@media(max-width:920px){.grid{grid-template-columns:1fr}}
.title{font-size:28px;margin:0 0 6px;font-weight:700}
.meta{color:var(--muted);font-size:14px;margin-bottom:18px}
.instructions{line-height:1.7;color:#0f172a}
.step-block{background:#f8fafc;border-radius:10px;padding:14px;margin-bottom:10px;display:flex;gap:12px;align-items:flex-start}
.step-number{width:36px;height:36px;border-radius:9px;background:linear-gradient(180deg,#0f172a,#374151);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:600}
.step-text{flex:1}
.playbar{display:flex;align-items:center;gap:12px;margin-top:18px}
.btn{
  display:inline-block;padding:10px 16px;border-radius:10px;background:var(--accent);color:#fff;text-decoration:none;font-weight:600;
  box-shadow:0 6px 16px rgba(12,18,24,0.12);
}
.btn-ghost{background:transparent;border:1px solid rgba(15,23,42,0.06);color:var(--accent)}
.side-card{position:sticky;top:28px}
.info-card{background:linear-gradient(180deg, rgba(255,255,255,0.85), rgba(255,255,255,0.95)); padding:20px;border-radius:12px;border:1px solid rgba(15,23,42,0.03)}
.small{font-size:13px;color:var(--muted)}
.timer{font-weight:700;font-size:18px}
.progress{height:8px;background:#e6edf6;border-radius:999px;overflow:hidden;margin-top:12px}
.progress > i{display:block;height:100%;background:linear-gradient(90deg,var(--accent-2),#0f172a);width:0%}
.modal {
  position:fixed; inset:0; display:none; align-items:center; justify-content:center; z-index:60;
  background:rgba(8,12,20,0.45);
}
.modal.open { display:flex; }
.modal-card { width:960px; max-width:96%; background:var(--card); padding:20px;border-radius:12px; box-shadow:var(--shadow); }
.modal-header{display:flex;align-items:center;justify-content:space-between;padding-bottom:8px;border-bottom:1px solid rgba(15,23,42,0.03)}
.modal-body{padding-top:16px}
.controls{display:flex;gap:10px;align-items:center;margin-top:12px}
.icon-btn{background:#0f172a;color:#fff;padding:10px;border-radius:10px;border:none;cursor:pointer}
.step-indicator{display:flex;gap:8px;flex-wrap:wrap;margin-top:8px}
.step-dot{width:8px;height:8px;background:#e6edf6;border-radius:999px}
.step-dot.active{background:linear-gradient(90deg,var(--accent-2),#0f172a)}
.footer-note{font-size:13px;color:var(--muted);margin-top:16px}
.empty-state{text-align:center;padding:60px 12px;color:var(--muted)}
a.back { color:var(--muted); text-decoration:none; font-weight:600 }
</style>
</head>
<body>
<div class="container">
  <div class="header">
    <div class="breadcrumb"><a href="/index.php" class="back">← Back to Home</a></div>
    <div class="small">MindEase • Activity</div>
  </div>

  <div class="grid">
    <div>
      <div class="card">
        <h1 class="title"><?php echo htmlspecialchars($title); ?></h1>
        <div class="meta"><?php echo htmlspecialchars($type ?: 'activity'); ?> • Duration: <?php echo ($duration_seconds > 0) ? ceil($duration_seconds / 60) . ' min' : '—'; ?></div>

        <?php if (count($steps) === 0): ?>
          <div class="empty-state card" style="background:transparent;box-shadow:none;padding:0">
            <p class="small">No steps or instructions are available for this activity.</p>
            <p class="small">Raw content (for debugging):</p>
            <pre style="background:#0f172a;color:#fff;padding:12px;border-radius:8px;overflow:auto"><?php echo htmlspecialchars($content_json); ?></pre>
          </div>
        <?php else: ?>
          <div id="stepsList" class="instructions">
            <?php foreach ($steps as $i => $s): ?>
              <div class="step-block" data-step-index="<?php echo $i; ?>">
                <div class="step-number"><?php echo $i + 1; ?></div>
                <div class="step-text"><?php echo nl2br(htmlspecialchars($s['text'])); ?></div>
              </div>
            <?php endforeach; ?>
          </div>

          <div class="playbar">
            <a href="#" class="btn" id="openPlayerBtn">Start Activity</a>
            <div style="flex:1"></div>
            <div class="small">Steps: <strong><?php echo count($steps); ?></strong></div>
          </div>
        <?php endif; ?>
      </div>

      <!-- Optional expanded instructions area -->
      <div style="height:18px"></div>
      <div class="card">
        <div class="small">About this activity</div>
        <div style="margin-top:8px">
          <p class="small">These activities are simple, self-guided mental health exercises. If you have severe symptoms, consider seeking professional help.</p>
        </div>
      </div>
    </div>

    <aside class="side-card">
      <div class="info-card">
        <div class="small">Quick info</div>
        <div style="margin-top:12px">
          <div class="timer"><?php echo ($duration_seconds > 0) ? gmdate('i\m\ i\s', $duration_seconds) : '—'; ?></div>
          <div class="small" style="margin-top:8px">Type: <?php echo htmlspecialchars($type ?: '—'); ?></div>

          <div class="progress" aria-hidden="true">
            <i id="sidebarProgress" style="width:0%"></i>
          </div>

          <div style="margin-top:12px">
            <a href="#" class="btn btn-ghost" id="openPlayerBtnSide">Preview Activity</a>
          </div>
        </div>
      </div>
    </aside>
  </div>
</div>

<!-- Modal player -->
<div id="playerModal" class="modal" role="dialog" aria-hidden="true">
  <div class="modal-card" role="document">
    <div class="modal-header">
      <div>
        <strong id="modalTitle"><?php echo htmlspecialchars($title); ?></strong>
        <div class="small" id="modalMeta"><?php echo htmlspecialchars($type ?: ''); ?> • <?php echo ($duration_seconds > 0) ? ceil($duration_seconds / 60) . ' min' : '—'; ?></div>
      </div>
      <div>
        <button class="icon-btn" id="closeModalBtn" aria-label="Close">✕</button>
      </div>
    </div>

    <div class="modal-body">
      <div id="playerContent">
        <div id="playerStepText" style="font-size:18px;line-height:1.6"></div>

        <div class="controls">
          <button class="btn" id="prevBtn">Prev</button>
          <button class="btn" id="playPauseBtn">Play</button>
          <button class="btn" id="nextBtn">Next</button>

          <div style="flex:1"></div>

          <div class="small">Step <span id="currentStepNum">1</span> / <span id="totalSteps"><?php echo count($steps); ?></span></div>
        </div>

        <div class="progress" aria-hidden="true" style="margin-top:12px">
          <i id="playerProgress" style="width:0%"></i>
        </div>

        <div class="step-indicator" id="stepDots" aria-hidden="true" style="margin-top:10px">
          <?php for ($i = 0; $i < count($steps); $i++): ?>
            <div class="step-dot" data-dot-index="<?php echo $i; ?>"></div>
          <?php endfor; ?>
        </div>

        <div class="footer-note small">Tip: use the Next/Prev controls to move between steps. Play will auto-advance where step meta includes a duration.</div>
      </div>
    </div>
  </div>
</div>

<!-- JS -->
<script>
(function(){
  const steps = <?php echo json_encode(array_map(function($s){ return ['text'=>$s['text'], 'meta'=>$s['meta']]; }, $steps)); ?>;
  const durationSeconds = <?php echo (int)$duration_seconds; ?>;
  const modal = document.getElementById('playerModal');
  const openBtn = document.getElementById('openPlayerBtn');
  const openBtnSide = document.getElementById('openPlayerBtnSide');
  const closeBtn = document.getElementById('closeModalBtn');
  const playBtn = document.getElementById('playPauseBtn');
  const prevBtn = document.getElementById('prevBtn');
  const nextBtn = document.getElementById('nextBtn');
  const playerText = document.getElementById('playerStepText');
  const curNum = document.getElementById('currentStepNum');
  const totalNum = document.getElementById('totalSteps');
  const playerProgress = document.getElementById('playerProgress');
  const sidebarProgress = document.getElementById('sidebarProgress');
  const stepDots = Array.from(document.querySelectorAll('.step-dot'));
  let idx = 0;
  let playing = false;
  let autoplayTimer = null;
  let stepDur = 0;

  function renderStep(i){
    idx = Math.max(0, Math.min(i, steps.length-1));
    const s = steps[idx] || {text:'', meta:null};
    playerText.innerHTML = s.text.replace(/\n/g,'<br/>');
    curNum.textContent = idx+1;
    const percent = Math.round(((idx+1)/steps.length) * 100);
    playerProgress.style.width = percent+'%';
    sidebarProgress.style.width = percent+'%';
    stepDots.forEach((d,j)=> d.classList.toggle('active', j===idx));
    // step duration: if meta.duration_seconds exists, use it; else use proportional duration if overall duration provided
    stepDur = (s.meta && s.meta.duration_seconds) ? Number(s.meta.duration_seconds) : (durationSeconds ? Math.max(3, Math.round(durationSeconds / Math.max(1, steps.length))) : 6);
    updatePlayBtn();
  }

  function updatePlayBtn(){
    playBtn.textContent = playing ? 'Pause' : 'Play';
  }

  function playStep(){
    if (playing) return;
    playing = true;
    updatePlayBtn();
    startTimerForStep();
  }

  function pauseStep(){
    playing = false;
    updatePlayBtn();
    if (autoplayTimer) { clearTimeout(autoplayTimer); autoplayTimer = null; }
  }

  function startTimerForStep(){
    if (autoplayTimer) { clearTimeout(autoplayTimer); autoplayTimer = null; }
    // auto-advance only if playing
    if (!playing) return;
    // use stepDur (seconds)
    autoplayTimer = setTimeout(()=> {
      if (idx < steps.length - 1) {
        goToStep(idx+1);
        // keep playing
        startTimerForStep();
      } else {
        // end - stop playing
        playing = false;
        updatePlayBtn();
      }
    }, stepDur * 1000);
  }

  function goToStep(i){
    pauseStep();
    renderStep(i);
  }

  // wire controls
  openBtn && openBtn.addEventListener('click', function(e){ e.preventDefault(); openModal(); });
  openBtnSide && openBtnSide.addEventListener('click', function(e){ e.preventDefault(); openModal(); });
  closeBtn && closeBtn.addEventListener('click', function(){ closeModal(); });
  prevBtn && prevBtn.addEventListener('click', function(){ goToStep(idx-1); });
  nextBtn && nextBtn.addEventListener('click', function(){ goToStep(idx+1); });
  playBtn && playBtn.addEventListener('click', function(){ if (playing) pauseStep(); else { playStep(); }});

  function openModal(){
    modal.classList.add('open');
    modal.setAttribute('aria-hidden','false');
    renderStep(0);
  }
  function closeModal(){
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden','true');
    pauseStep();
  }

  // clicking dots
  stepDots.forEach(d=>{
    d.addEventListener('click', function(){ const i = Number(this.getAttribute('data-dot-index')); goToStep(i); });
  });

  // keyboard navigation
  document.addEventListener('keydown', function(e){
    if (!modal.classList.contains('open')) return;
    if (e.key === 'ArrowRight') { goToStep(idx+1); }
    if (e.key === 'ArrowLeft') { goToStep(idx-1); }
    if (e.key === ' ' || e.key === 'Spacebar') { e.preventDefault(); if (playing) pauseStep(); else playStep(); }
    if (e.key === 'Escape') closeModal();
  });

  // initial render for sidebar progress
  (function init(){
    // If page shows steps inline, highlight first
    const firstStep = document.querySelector('.step-block[data-step-index="0"]');
    if (firstStep) firstStep.style.outline = 'none';
    // set initial player content for accessibility if user opens modal
    totalNum.textContent = steps.length;
  })();

})();
</script>
</body>
</html>
