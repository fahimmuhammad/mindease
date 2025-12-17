document.addEventListener('DOMContentLoaded', () => {
    const slider = document.getElementById('stress-slider');
    const analyzeBtn = document.getElementById('analyze-btn');
    const statusBox = document.getElementById('status-box');
    const resultBox = document.getElementById('result-box');
    const consent = document.getElementById('consent');
    const note = document.getElementById('note');

    function showStatus(msg, err = false) {
        statusBox.textContent = msg;
        statusBox.style.color = err ? 'red' : '#333';
        statusBox.style.display = 'block';
    }

    async function analyzeQuick() {
        showStatus('Analyzing...');

        try {
            const res = await fetch('/api/analyze_quick.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({
                    score: Number(slider.value),
                    consent: consent.checked,
                    note: note.value || ''
                })
            });

            const json = await res.json();

            if (!json.success) {
                showStatus(json.error || 'Failed', true);
                return;
            }

            resultBox.innerHTML = `
                <p><b>Score:</b> ${json.data.score}</p>
                <p><b>Summary:</b> ${json.data.summary}</p>
            `;
            resultBox.style.display = 'block';
            showStatus('Done');

        } catch (e) {
            showStatus('Error connecting to server.', true);
        }
    }

    analyzeBtn.addEventListener('click', e => {
        e.preventDefault();
        analyzeQuick();
    });
});
