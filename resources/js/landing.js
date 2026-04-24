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

// Typewriter — types data-text after data-start-delay ms on first reveal.
document.querySelectorAll('[data-typewriter]').forEach((el) => {
    const text = el.dataset.text ?? '';
    const startDelay = Number(el.dataset.startDelay ?? 400);
    const speed = 28;

    el.textContent = '';
    const cursor = document.createElement('span');
    cursor.className = 'codex-cursor';
    el.appendChild(cursor);

    let i = 0;
    const tick = () => {
        if (i >= text.length) return;
        cursor.insertAdjacentText('beforebegin', text[i]);
        i += 1;
        setTimeout(tick, speed + Math.random() * 30);
    };
    setTimeout(tick, startDelay);
});

// Mist parallax — writes --mx/--my CSS vars on mousemove inside the hero region.
const mist = document.querySelector('[data-mist]');
if (mist && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
    const host = mist.parentElement ?? document.body;
    let raf = 0;
    host.addEventListener('mousemove', (event) => {
        cancelAnimationFrame(raf);
        raf = requestAnimationFrame(() => {
            const rect = host.getBoundingClientRect();
            const x = (event.clientX - rect.left) / rect.width - 0.5;
            const y = (event.clientY - rect.top) / rect.height - 0.5;
            mist.style.setProperty('--mx', x.toFixed(3));
            mist.style.setProperty('--my', y.toFixed(3));
        });
    });
}
