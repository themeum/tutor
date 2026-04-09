/* Tutor Core Docs — Shared Script */

(function () {
  // Theme toggle
  const toggle = document.getElementById('themeToggle');
  const root = document.documentElement;
  const stored = localStorage.getItem('tutor-docs-theme');
  if (stored) root.setAttribute('data-theme', stored);

  if (toggle) {
    toggle.addEventListener('click', () => {
      const next = root.getAttribute('data-theme') === 'dark' ? 'light' : 'dark';
      root.setAttribute('data-theme', next);
      localStorage.setItem('tutor-docs-theme', next);
      toggle.querySelector('.sun')?.classList.toggle('hidden', next === 'light');
      toggle.querySelector('.moon')?.classList.toggle('hidden', next === 'dark');
    });
    // Init icons
    const isDark = root.getAttribute('data-theme') === 'dark';
    toggle.querySelector('.sun')?.classList.toggle('hidden', !isDark);
    toggle.querySelector('.moon')?.classList.toggle('hidden', isDark);
  }

  // Active sidebar link highlight on scroll
  const sections = document.querySelectorAll('.doc-main section[id]');
  const sideLinks = document.querySelectorAll('.doc-sidebar__link[href^="#"]');

  if (sections.length && sideLinks.length) {
    const observer = new IntersectionObserver(
      (entries) => {
        entries.forEach((entry) => {
          if (entry.isIntersecting) {
            sideLinks.forEach((l) => l.classList.remove('active'));
            const match = document.querySelector(`.doc-sidebar__link[href="#${entry.target.id}"]`);
            if (match) match.classList.add('active');
          }
        });
      },
      { rootMargin: '-80px 0px -60% 0px', threshold: 0 }
    );
    sections.forEach((s) => observer.observe(s));
  }

  // Mark current page link in header
  const pageName = location.pathname.split('/').pop() || 'index.html';
  document.querySelectorAll('.doc-header__link').forEach((link) => {
    if (link.getAttribute('href') === pageName) link.classList.add('active');
  });
})();