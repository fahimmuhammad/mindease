<?php require_once __DIR__ . '/../src/lib/auth_guard.php'; ?>
<?php include __DIR__ . '/../src/pages/_nav.php'; ?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8" />
    <title>Profile — MindEase</title>
    <link rel="stylesheet" href="css/style.css">
    <style>
        .setting-row { margin-bottom:15px; }
        .info-box {
            background:#fff;
            padding:15px;
            border-radius:10px;
            box-shadow:0 1px 6px rgba(0,0,0,0.05);
            margin-top:18px;
        }
    </style>
</head>
<body>

<div class="container">
    <h1>Your Preferences</h1>

    <div class="info-box">
        <h3>Data Saving Preference</h3>
        <p>Choose whether MindEase is allowed to store your stress check entries automatically.</p>

        <div class="setting-row">
            <label>
                <input type="checkbox" id="pref_consent">
                I allow MindEase to save my entries automatically.
            </label>
        </div>

        <button class="primary" onclick="savePref()">Save Preference</button>
        <p id="status" style="margin-top:10px;"></p>
    </div>

    <div class="info-box">
        <h3>System Info</h3>
        <p>You are using a local (offline) version of MindEase.</p>
        <p>No personal identifying data is collected.</p>
    </div>
</div>

<script>
async function loadPref() {
    try {
        const res = await fetch('/mindease/src/api/get_pref.php');
        const j = await res.json();

        if (j.success && j.data) {
            document.getElementById('pref_consent').checked = j.data.save_entries === true;
        }
    } catch (e) {
        console.error(e);
    }
}

async function savePref() {
    const pref = {
        save_entries: document.getElementById('pref_consent').checked
    };

    try {
        const res = await fetch('/mindease/src/api/set_pref.php', {
            method:'POST',
            headers:{ 'Content-Type':'application/json' },
            body: JSON.stringify(pref)
        });

        const j = await res.json();

        if (j.success) {
            document.getElementById('status').style.color = '#1a7f37';
            document.getElementById('status').innerText = 'Saved successfully.';
        } else {
            document.getElementById('status').style.color = 'crimson';
            document.getElementById('status').innerText = 'Failed: ' + j.error;
        }
    } catch (e) {
        document.getElementById('status').style.color = 'crimson';
        document.getElementById('status').innerText = 'Network error.';
    }
}

loadPref();
</script>

</body>
</html>
