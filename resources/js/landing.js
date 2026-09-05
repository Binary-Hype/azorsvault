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

// Typewriter — reveals the server-rendered text one character at a time.
// Every character is wrapped and left in the flow from the start, hidden
// rather than absent, so revealing them cannot reflow the card. Typing by
// appending text nodes reflowed on every keystroke and shifted the whole
// grid when the line wrapped.
document.querySelectorAll('[data-typewriter]').forEach((el) => {
    const text = el.textContent.trim();
    const startDelay = Number(el.dataset.startDelay ?? 400);
    const speed = 28;

    const letters = [...text].map((character) => {
        const span = document.createElement('span');
        span.textContent = character;
        return span;
    });

    /** Zero-width resting place for the caret once typing finishes. */
    const tail = document.createElement('span');

    el.replaceChildren(...letters, tail);
    el.setAttribute('aria-label', text);

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
        letters.forEach((letter) => letter.classList.add('is-typed'));
        return;
    }

    let i = 0;
    letters[0]?.classList.add('codex-caret');

    const tick = () => {
        if (i >= letters.length) return;

        letters[i].classList.remove('codex-caret');
        letters[i].classList.add('is-typed');
        i += 1;

        (letters[i] ?? tail).classList.add('codex-caret');

        if (i < letters.length) {
            setTimeout(tick, speed + Math.random() * 30);
        }
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
