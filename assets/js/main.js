/* AAEC shared site behavior. Vanilla JS, no build step, no dependencies.
   Requires the page to be served over http(s), not opened as a local file
   (fetch() for partials/data won't work with file://). See README. */

document.addEventListener("DOMContentLoaded", () => {
  loadPartials().then(() => {
    initHeader();
    initMobileNav();
    highlightActiveNav();
  });
  initReveal();
  initWorkFilters();
  initTeamModal();
  initContactForm();
});

/* Shared header/footer, injected so nav/footer only need editing once ---- */
function loadPartials() {
  const header = document.querySelector("[data-include-header]");
  const footer = document.querySelector("[data-include-footer]");
  const jobs = [];
  if (header) jobs.push(fetch("/partials/header.html").then((r) => r.text()).then((html) => (header.innerHTML = html)));
  if (footer) jobs.push(fetch("/partials/footer.html").then((r) => r.text()).then((html) => (footer.innerHTML = html)));
  return Promise.all(jobs).catch((err) => console.error("Failed to load partials", err));
}

function highlightActiveNav() {
  const current = window.location.pathname.replace(/\/index\.html$/, "/").replace(/\.html$/, "");
  document.querySelectorAll(".main-nav a").forEach((link) => {
    const href = link.getAttribute("href").replace(/\.html$/, "");
    if (href === current || (current === "/" && href === "/index")) link.classList.add("active");
  });
}

/* Header background swap on scroll -------------------------------------- */
function initHeader() {
  const header = document.querySelector(".site-header");
  if (!header) return;
  const onScroll = () => header.classList.toggle("is-scrolled", window.scrollY > 20);
  onScroll();
  window.addEventListener("scroll", onScroll, { passive: true });
}

/* Mobile nav toggle -------------------------------------------------------*/
function initMobileNav() {
  const toggle = document.querySelector(".nav-toggle");
  const nav = document.querySelector(".main-nav");
  if (!toggle || !nav) return;
  toggle.addEventListener("click", () => {
    const isOpen = nav.classList.toggle("is-open");
    toggle.textContent = isOpen ? "✕" : "☰";
    document.body.style.overflow = isOpen ? "hidden" : "";
  });
  nav.querySelectorAll("a").forEach((link) =>
    link.addEventListener("click", () => {
      nav.classList.remove("is-open");
      toggle.textContent = "☰";
      document.body.style.overflow = "";
    })
  );
}

/* Scroll reveal via IntersectionObserver --------------------------------- */
function initReveal() {
  const targets = document.querySelectorAll(".reveal");
  if (!targets.length) return;
  const io = new IntersectionObserver(
    (entries) => {
      entries.forEach((entry) => {
        if (entry.isIntersecting) {
          entry.target.classList.add("is-visible");
          io.unobserve(entry.target);
        }
      });
    },
    { threshold: 0.15 }
  );
  targets.forEach((t) => io.observe(t));
}

/* Work page: load /data/work.json, render cards, filter by category ------ */
function initWorkFilters() {
  const grid = document.querySelector("[data-work-grid]");
  if (!grid) return;

  fetch("/data/work.json")
    .then((res) => res.json())
    .then((items) => {
      renderWorkGrid(grid, items);
      const filterBar = document.querySelector("[data-filter-bar]");
      if (!filterBar) return;
      filterBar.addEventListener("click", (e) => {
        const btn = e.target.closest(".filter-btn");
        if (!btn) return;
        filterBar.querySelectorAll(".filter-btn").forEach((b) => b.classList.remove("active"));
        btn.classList.add("active");
        const category = btn.dataset.category;
        const filtered = category === "all" ? items : items.filter((i) => i.category === category);
        renderWorkGrid(grid, filtered);
      });
    })
    .catch(() => {
      grid.innerHTML = '<p>Portfolio data could not be loaded.</p>';
    });
}

function renderWorkGrid(grid, items) {
  if (!items.length) {
    grid.innerHTML = '<p>No projects in this category yet.</p>';
    return;
  }
  grid.innerHTML = items
    .map(
      (item) => `
      <a class="media-card reveal is-visible" href="${item.href || '/work/template.html'}">
        ${item.image ? `<img src="${item.image}" alt="${item.title}" loading="lazy" style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0;">` : `<div class="placeholder">Image pending<br>${item.title}</div>`}
        <div class="caption">
          <span class="eyebrow">${item.category}</span>
          <h4>${item.title}</h4>
        </div>
      </a>`
    )
    .join("");
}

/* About page: load /data/team.json, render team grid + modal ------------- */
function initTeamModal() {
  const grid = document.querySelector("[data-team-grid]");
  const overlay = document.querySelector("[data-team-modal]");
  if (!grid || !overlay) return;

  fetch("/data/team.json")
    .then((res) => res.json())
    .then((members) => {
      grid.innerHTML = members
        .map(
          (m, i) => `
        <button class="team-member reveal is-visible" data-index="${i}">
          <div class="media-card">
            ${m.photo ? `<img src="${m.photo}" alt="${m.name}" style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0;">` : `<div class="placeholder">Portrait pending</div>`}
          </div>
          <h4>${m.name}</h4>
          <span class="role">${m.role}</span>
        </button>`
        )
        .join("");

      grid.querySelectorAll(".team-member").forEach((btn) => {
        btn.addEventListener("click", () => {
          const m = members[btn.dataset.index];
          overlay.querySelector("[data-modal-body]").innerHTML = `
            <h3>${m.name}</h3>
            <span class="role">${m