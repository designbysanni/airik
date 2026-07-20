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
  initClientsList();
  initTestimonials();
  initInquiryForms();
});

/* Shared header/footer, injected so nav/footer only need editing once ---- */
function loadPartials() {
  const header = document.querySelector("[data-include-header]");
  const footer = document.querySelector("[data-include-footer]");
  const jobs = [];
  // Deliberately still .html here, not the clean URL: this is a raw file
  // fetch (get the partial's actual markup), not page navigation, and it
  // needs to work identically against the local python -m http.server used
  // for quick local checks throughout this project (which doesn't process
  // .htaccess/vercel.json at all — the clean path 404s there). In
  // production this costs one extra 301/308 round-trip per page load
  // (fetch() follows it transparently) — acceptable; not worth losing easy
  // local testing to shave off.
  if (header) jobs.push(fetch("/partials/header.html").then((r) => r.text()).then((html) => (header.innerHTML = html)));
  if (footer) jobs.push(fetch("/partials/footer.html").then((r) => r.text()).then((html) => (footer.innerHTML = html)));
  return Promise.all(jobs).catch((err) => console.error("Failed to load partials", err));
}

function highlightActiveNav() {
  // Clean URLs now (see .htaccess), but strip .html/trailing slash anyway
  // so this still matches correctly for anyone hitting an old bookmarked
  // .html URL before its 301 redirect completes.
  const current = window.location.pathname.replace(/\.html$/, "").replace(/(.)\/$/, "$1");
  document.querySelectorAll(".main-nav a").forEach((link) => {
    const href = link.getAttribute("href").replace(/\.html$/, "");
    if (href === current) link.classList.add("active");
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
    toggle.setAttribute("aria-expanded", String(isOpen));
    toggle.textContent = isOpen ? "✕" : "☰";
    document.body.style.overflow = isOpen ? "hidden" : "";
  });
  nav.querySelectorAll("a").forEach((link) =>
    link.addEventListener("click", () => {
      nav.classList.remove("is-open");
      toggle.setAttribute("aria-expanded", "false");
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

/* Work page + Home "Featured Work": load /data/work.json, render cards,
   filter by category. Home only shows items flagged "featured": true
   (marked via [data-featured-only] on the grid element); Work page shows
   everything and wires up the category filter bar. ------------------------ */
function initWorkFilters() {
  const grid = document.querySelector("[data-work-grid]");
  if (!grid) return;
  const featuredOnly = grid.hasAttribute("data-featured-only");

  fetch("/data/work.json")
    .then((res) => res.json())
    .then((items) => {
      const initial = featuredOnly ? items.filter((i) => i.featured) : items;
      renderWorkGrid(grid, initial, featuredOnly);

      const filterBar = document.querySelector("[data-filter-bar]");
      if (!filterBar) return;

      // Supports /work?category=Music%20%26%20Culture deep links (used by the
      // Home page's Music & Culture featured card, which opens the full
      // collection instead of a single project) by pre-selecting that filter.
      const requestedCategory = new URLSearchParams(window.location.search).get("category");
      if (requestedCategory) {
        const matchingBtn = [...filterBar.querySelectorAll(".filter-btn")].find(
          (b) => b.dataset.category === requestedCategory
        );
        if (matchingBtn) {
          filterBar.querySelectorAll(".filter-btn").forEach((b) => b.classList.remove("active"));
          matchingBtn.classList.add("active");
          renderWorkGrid(grid, items.filter((i) => i.category === requestedCategory));
        }
      }

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
      grid.innerHTML = "<p>Portfolio data could not be loaded.</p>";
    });
}

function renderWorkGrid(grid, items, featuredOnly) {
  if (!items.length) {
    grid.innerHTML = "<p>No projects in this category yet.</p>";
    return;
  }
  grid.innerHTML = items
    .map((item) => {
      // On the Home page's featured grid, a "categoryHref" lets a card open the
      // full category collection on /work instead of a single project page.
      const href = (featuredOnly && item.categoryHref) || item.href || "/work/template";
      const linkLabel = featuredOnly && item.categoryHref ? "View Collection →" : "View Project →";
      return `
      <a class="media-card reveal is-visible" href="${href}">
        ${item.image ? `<img src="${item.image}" alt="${item.title}" loading="lazy" style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0;">` : `<div class="placeholder">Image pending<br>${item.title}</div>`}
        <div class="caption">
          <span class="eyebrow">${item.category}</span>
          <h4>${item.title}</h4>
          <span class="view-link">${linkLabel}</span>
        </div>
      </a>`;
    })
    .join("");
}

/* About page: load /data/team.json, render team grid + clickable modal
   with portrait, bio, role, expertise, and related work (per brief). ------ */
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
        <button class="team-member reveal is-visible" type="button" data-index="${i}">
          <div class="media-card">
            ${m.photo ? `<img src="${m.photo}" alt="${m.name}" loading="lazy" style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0;">` : `<div class="placeholder">Portrait pending</div>`}
          </div>
          <h4>${m.name}</h4>
          <span class="role">${m.role}</span>
        </button>`
        )
        .join("");

      const closeModal = () => {
        overlay.classList.remove("is-open");
        document.body.style.overflow = "";
      };

      const openModal = (m) => {
        const expertise = (m.expertise || []).map((e) => `<span class="tag">${e}</span>`).join("");
        const relatedWork = (m.relatedWork || []).length
          ? `<h4 style="margin-top:1.5rem;">Related Work</h4>
             <ul style="list-style:none;padding:0;margin:0;display:grid;gap:0.4rem;">
               ${m.relatedWork.map((w) => `<li><a href="${w.href}" style="color:var(--aaec-silver);">${w.title} →</a></li>`).join("")}
             </ul>`
          : "";
        overlay.querySelector("[data-modal-body]").innerHTML = `
          <div class="media-card" style="aspect-ratio:1/1;max-width:220px;margin-bottom:1.5rem;">
            ${m.photo ? `<img src="${m.photo}" alt="${m.name}" style="width:100%;height:100%;object-fit:cover;position:absolute;inset:0;">` : `<div class="placeholder">Portrait pending</div>`}
          </div>
          <h3>${m.name}</h3>
          <span class="role">${m.role}</span>
          <p style="margin-top:1rem;">${m.bio}</p>
          ${expertise ? `<div class="tag-list" style="margin-top:1rem;">${expertise}</div>` : ""}
          ${relatedWork}
        `;
        overlay.classList.add("is-open");
        document.body.style.overflow = "hidden";
      };

      grid.querySelectorAll(".team-member").forEach((btn) => {
        btn.addEventListener("click", () => openModal(members[btn.dataset.index]));
      });

      overlay.querySelector(".modal-close").addEventListener("click", closeModal);
      overlay.addEventListener("click", (e) => {
        if (e.target === overlay) closeModal();
      });
      document.addEventListener("keydown", (e) => {
        if (e.key === "Escape") closeModal();
      });
    })
    .catch(() => {
      grid.innerHTML = "<p>Team data could not be loaded.</p>";
    });
}

/* Home page "Selected Clients": load /data/clients.json ------------------- */
function initClientsList() {
  const el = document.querySelector("[data-clients-list]");
  if (!el) return;
  fetch("/data/clients.json")
    .then((res) => res.json())
    .then((clients) => {
      el.innerHTML = clients.map((c) => `<span class="tag">${c.name}</span>`).join("");
    })
    .catch(() => {
      el.innerHTML = "<p>Client list could not be loaded.</p>";
    });
}

/* Home page "Testimonials": load /data/testimonials.json ------------------ */
function initTestimonials() {
  const el = document.querySelector("[data-testimonials]");
  if (!el) return;
  fetch("/data/testimonials.json")
    .then((res) => res.json())
    .then((items) => {
      el.innerHTML = items
        .map(
          (t) => `
        <blockquote class="card">
          <p style="color:var(--aaec-white);font-size:1.1rem;">&ldquo;${t.quote}&rdquo;</p>
          <p style="color:var(--aaec-silver);margin:0;font-family:var(--font-structural);text-transform:uppercase;font-size:0.75rem;letter-spacing:0.08em;">${t.name}${t.org ? `, ${t.org}` : ""}</p>
        </blockquote>`
        )
        .join("");
    })
    .catch(() => {
      el.innerHTML = "<p>Testimonials could not be loaded.</p>";
    });
}

/* Contact + Careers forms: posts to our own /api/submit-lead.php, which
   holds the GHL credentials server-side and creates/tags the contact in
   GHL. See CLAUDE.md "GHL integration" for why it's a custom PHP endpoint
   instead of an embedded GHL form. Each <form data-inquiry-form> needs a
   hidden "source" field ("contact" or "careers") so the endpoint knows
   which tag/note shape to apply. --------------------------------------- */
function initInquiryForms() {
  document.querySelectorAll("[data-inquiry-form]").forEach((form) => {
    form.addEventListener("submit", (e) => {
      e.preventDefault();
      const status = form.querySelector(".form-status");
      const submitBtn = form.querySelector('button[type="submit"]');
      const originalBtnText = submitBtn ? submitBtn.textContent : "";
      const data = Object.fromEntries(new FormData(form).entries());

      if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.textContent = "Sending…";
      }
      if (status) {
        status.textContent = "Sending…";
        status.style.color = "var(--aaec-silver)";
      }

      fetch("/api/submit-lead.php", {
        method: "POST",
        headers: { "Content-Type": "application/json" },
        body: JSON.stringify(data),
      })
        .then((res) => res.json())
        .then((result) => {
          if (!result.success) throw new Error(result.error || "Submission failed");
          form.reset();
          if (status) {
            status.textContent = "Thanks — we've got it and will be in touch soon.";
            status.style.color = "var(--aaec-white)";
          }
        })
        .catch(() => {
          if (status) {
            status.textContent =
              "Something went wrong sending this. Please email info@airikart.com directly and we'll follow up.";
            status.style.color = "var(--aaec-red-text)";
          }
        })
        .finally(() => {
          if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.textContent = originalBtnText;
          }
        });
    });
  });
}
