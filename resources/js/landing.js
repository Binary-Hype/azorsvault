// Copy buttons — flip to "Copied" for 1.6s after click.
document.addEventListener('click', (event) => {
    const button = event.target.closest('[data-copy]');
    if (!button) return;

    const value = button.dataset.copy;
    const flip = button.querySelector('.codex-flip');
    const status = button.querySelector('[data-copy-status]');

    // Returns whether the legacy copy actually went through.
    const fallback = () => {
        const ta = document.createElement('textarea');
        ta.value = value;
        ta.setAttribute('readonly', '');
        ta.style.position = 'absolute';
        ta.style.left = '-9999px';
        document.body.appendChild(ta);
        ta.select();
        let copied = false;
        try {
            copied = document.execCommand('copy');
        } catch (_) {
            // clipboard may be unavailable (insecure context, permissions)
        }
        document.body.removeChild(ta);
        return copied;
    };

    const done = () => {
        if (status) status.textContent = 'Copied';
        if (!flip) return;
        flip.classList.add('copied');
        setTimeout(() => {
            flip.classList.remove('copied');
            if (status) status.textContent = '';
        }, 1600);
    };

    const copyWithFallback = () => {
        if (fallback()) done();
    };

    if (navigator.clipboard?.writeText) {
        navigator.clipboard.writeText(value).then(done).catch(copyWithFallback);
    } else {
        copyWithFallback();
    }
});
