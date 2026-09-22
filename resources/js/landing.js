// Copy buttons — flip to "Copied" for 1.6s after click.
document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-copy]');
    if (!button) return;

    const value = button.dataset.copy;
    const flip = button.querySelector('.codex-flip');

    const fallback = () => {
        const ta = document.createElement('textarea');
        ta.value = value;
        ta.setAttribute('readonly', '');
        ta.style.position = 'absolute';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.select();
        try {
            document.execCommand('copy');
        } catch (_) {
            // swallow — clipboard may be unavailable (insecure context, permissions)
        }
        document.body.removeChild(ta);
    };

    const done = () => {
        if (!flip) return;
        flip.classList.add('copied');
        setTimeout(() => flip.classList.remove('copied'), 1600);
    };

    if (navigator.clipboard?.writeText) {
        navigator.clipboard.writeText(value).then(done).catch(() => {
            fallback();
            done();
        });
    } else {
        fallback();
        done();
    }
});
