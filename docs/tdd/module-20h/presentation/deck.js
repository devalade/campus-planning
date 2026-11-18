(() => {
  'use strict';

  const slides = window.SLIDES || [];
  const stage = document.getElementById('stage');
  const deck = document.getElementById('deck');
  const progress = document.getElementById('progressFill');
  const counter = document.getElementById('counter');
  const help = document.getElementById('help');

  if (!slides.length || !stage || !deck) return;

  const pad = (value) => String(value).padStart(2, '0');

  const renderSlide = (slide, index) => {
    const total = slides.length;
    const hasChrome = slide.chrome !== false;
    const isCustom = ['cover', 'divider', 'closing'].includes(slide.layout);

    let titleHtml = slide.html;
    let contentHtml = '';

    if (!isCustom && slide.html.includes('<!--slide-content-->')) {
      const parts = slide.html.split('<!--slide-content-->');
      titleHtml = parts.shift() || '';
      contentHtml = parts.join('<!--slide-content-->');
    }

    return `<section
      class="slide layout-${slide.layout || 'default'}"
      data-index="${index}"
      role="group"
      aria-label="Diapositive ${index + 1} sur ${total}"
      aria-hidden="true">
      ${hasChrome ? `<header class="slide-top">
        <span class="wordmark"><span class="wordmark-mark"></span>TDD / PEST</span>
        <span class="slide-section">${slide.section || ''}</span>
      </header>` : ''}
      <div class="slide-body">
        ${titleHtml}
        ${contentHtml ? `<div class="slide-content">${contentHtml}</div>` : ''}
      </div>
      ${hasChrome ? `<footer class="slide-foot">
        <span>TDD · 20 heures</span>
        <span class="slide-count">${pad(index + 1)} <span class="slash">/</span> ${pad(total)}</span>
      </footer>` : ''}
    </section>`;
  };

  deck.innerHTML = slides.map(renderSlide).join('');
  const nodes = Array.from(deck.querySelectorAll('.slide'));

  let index = 0;
  let touchStartX = null;
  let lastFocused = null;
  let idleTimer;

  const clamp = (value) => Math.max(0, Math.min(slides.length - 1, value));

  const goTo = (target, options = {}) => {
    const next = clamp(target);
    const { updateHash = true } = options;

    if (next === index && nodes[index].classList.contains('is-active')) return;

    nodes.forEach((node, nodeIndex) => {
      node.classList.toggle('is-active', nodeIndex === next);
      node.classList.toggle('is-before', nodeIndex < next);
      node.setAttribute('aria-hidden', nodeIndex === next ? 'false' : 'true');
    });

    index = next;

    if (progress) {
      progress.style.width = `${((index + 1) / slides.length) * 100}%`;
    }

    if (counter) {
      counter.innerHTML = `<b>${pad(index + 1)}</b><span class="slash"> / </span>${pad(slides.length)}`;
    }

    document.title = `${slides[index].title || 'TDD · 20 heures'} · TDD · 20 heures`;

    if (updateHash) {
      try {
        history.replaceState(null, '', `#${index + 1}`);
      } catch {
        // File URLs can reject history updates in some browsers.
      }
    }

    resetIdle();
  };

  const next = () => goTo(index + 1);
  const prev = () => goTo(index - 1);

  const initialHash = Number.parseInt(window.location.hash.replace('#', ''), 10);
  goTo(Number.isFinite(initialHash) ? initialHash - 1 : 0, { updateHash: false });

  const isControl = (target) => target instanceof Element && Boolean(target.closest('button, a, input, textarea, select'));

  const isFullscreen = () => document.fullscreenElement || document.webkitFullscreenElement;

  const toggleFullscreen = () => {
    const root = document.documentElement;

    if (!isFullscreen()) {
      if (root.requestFullscreen) {
        root.requestFullscreen().catch(() => {});
      } else if (root.webkitRequestFullscreen) {
        root.webkitRequestFullscreen();
      }
    } else if (document.exitFullscreen) {
      document.exitFullscreen().catch(() => {});
    } else if (document.webkitExitFullscreen) {
      document.webkitExitFullscreen();
    }
  };

  const openHelp = () => {
    if (help.hidden) {
      lastFocused = document.activeElement;
      help.hidden = false;
      help.querySelector('[data-close]')?.focus();
    }
  };

  const closeHelp = () => {
    if (!help.hidden) {
      help.hidden = true;
      if (lastFocused instanceof HTMLElement) lastFocused.focus();
    }
  };

  const toggleHelp = () => {
    if (help.hidden) openHelp();
    else closeHelp();
  };

  document.addEventListener('keydown', (event) => {
    if (event.metaKey || event.ctrlKey || event.altKey) return;

    if (!help.hidden) {
      if (event.key === 'Escape' || event.key === '?') {
        event.preventDefault();
        closeHelp();
      }
      return;
    }

    if (isControl(event.target) && (event.key === ' ' || event.key === 'Enter')) return;

    switch (event.key) {
      case 'ArrowRight':
      case 'PageDown':
      case ' ':
      case 'Enter':
        event.preventDefault();
        next();
        break;
      case 'ArrowLeft':
      case 'PageUp':
        event.preventDefault();
        prev();
        break;
      case 'Home':
        event.preventDefault();
        goTo(0);
        break;
      case 'End':
        event.preventDefault();
        goTo(slides.length - 1);
        break;
      case 'f':
      case 'F':
        event.preventDefault();
        toggleFullscreen();
        break;
      case 'p':
      case 'P':
        event.preventDefault();
        window.print();
        break;
      case '?':
        event.preventDefault();
        openHelp();
        break;
      case 'Escape':
        closeHelp();
        break;
    }
  });

  document.querySelectorAll('[data-nav]').forEach((button) => {
    button.addEventListener('click', () => {
      if (button.dataset.nav === 'next') next();
      else prev();
    });
  });

  document.querySelectorAll('[data-action]').forEach((button) => {
    button.addEventListener('click', () => {
      if (button.dataset.action === 'fullscreen') toggleFullscreen();
      if (button.dataset.action === 'help') toggleHelp();
    });
  });

  help?.addEventListener('click', (event) => {
    if (event.target === help) closeHelp();
  });

  help?.querySelector('[data-close]')?.addEventListener('click', closeHelp);

  window.addEventListener('hashchange', () => {
    const target = Number.parseInt(window.location.hash.replace('#', ''), 10);
    if (Number.isFinite(target)) goTo(target - 1, { updateHash: false });
  });

  stage.addEventListener('pointerdown', (event) => {
    touchStartX = event.clientX;
  });

  stage.addEventListener('pointerup', (event) => {
    if (touchStartX === null) return;

    const delta = event.clientX - touchStartX;
    touchStartX = null;

    if (Math.abs(delta) > 50) {
      if (delta < 0) next();
      else prev();
    }
  });

  const resize = () => {
    const scale = Math.min(window.innerWidth / 1600, window.innerHeight / 900);
    stage.style.transform = `translate(-50%, -50%) scale(${scale})`;
  };

  window.addEventListener('resize', resize);
  resize();

  function resetIdle() {
    document.body.classList.remove('is-idle');
    window.clearTimeout(idleTimer);
    idleTimer = window.setTimeout(() => document.body.classList.add('is-idle'), 3500);
  }

  ['mousemove', 'pointerdown', 'keydown'].forEach((eventName) => {
    document.addEventListener(eventName, resetIdle, { passive: true });
  });

  document.addEventListener('fullscreenchange', resetIdle);
  resetIdle();
})();
