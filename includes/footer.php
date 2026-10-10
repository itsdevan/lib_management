</main>

<footer>
    <div class="footer-brand">THE ARCANE ARCHIVE</div>
    <div>A beginner-friendly PHP + MySQL library management project</div>
    <div class="footer-credit">Hogwarts background: HarshLight, CC BY 2.0 via Wikimedia Commons.</div>
</footer>

<div id="toast-container" class="magic-toast-container"></div>

<script>
function showMagicToast(type, title, message) {
    const container = document.getElementById('toast-container');
    if (!container) return;

    const toast = document.createElement('div');
    toast.className = 'magic-toast ' + (type || 'info');

    const mark = type === 'success' ? 'SUCCESS' :
                 type === 'error' ? 'FAILED' :
                 type === 'warning' ? 'NOTICE' : 'ARCHIVE';

    toast.innerHTML = `
        <div class="toast-mark">${mark}</div>
        <div class="toast-copy">
            <div class="magic-toast-title">${escapeHtml(title)}</div>
            <div class="magic-toast-message">${escapeHtml(message)}</div>
        </div>
        <button class="magic-toast-close" aria-label="Close">×</button>
    `;

    toast.querySelector('.magic-toast-close').onclick = () => dismissToast(toast);
    container.appendChild(toast);

    setTimeout(() => dismissToast(toast), 4800);
}

function dismissToast(toast) {
    if (!toast || toast.classList.contains('hide')) return;
    toast.classList.add('hide');
    setTimeout(() => toast.remove(), 350);
}

function escapeHtml(value) {
    const div = document.createElement('div');
    div.textContent = value ?? '';
    return div.innerHTML;
}

</script>

<div id="spell-modal" class="spell-modal" aria-hidden="true">
    <div class="spell-panel">
        <div class="eyebrow">KEEPER'S CONFIRMATION</div>
        <h2 id="spell-title">Remove this volume?</h2>
        <p id="spell-message">This action cannot be undone.</p>
        <div class="hero-actions">
            <button class="btn btn-danger" id="spell-confirm">Remove from Collection</button>
            <button class="btn btn-secondary" id="spell-cancel">Cancel</button>
        </div>
    </div>
</div>

<script>
let pendingForm = null;
function confirmRemoval(form) {
    pendingForm = form;
    const modal = document.getElementById('spell-modal');
    modal.setAttribute('aria-hidden', 'false');
    modal.classList.add('open');
    return false;
}
document.getElementById('spell-confirm')?.addEventListener('click', function () {
    if (pendingForm) pendingForm.submit();
});
document.getElementById('spell-cancel')?.addEventListener('click', function () {
    pendingForm = null;
    const modal = document.getElementById('spell-modal');
    modal.classList.remove('open');
    modal.setAttribute('aria-hidden', 'true');
});
document.getElementById('spell-modal')?.addEventListener('click', function (e) {
    if (e.target === this) document.getElementById('spell-cancel').click();
});
</script>
</body>
</html>
