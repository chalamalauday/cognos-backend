/**
 * COGNOS 2K26 - Main UI & Interaction Scripts
 * Dynamic animated title, countdown, scroll reveal, counter animations, modal handling, and lightbox.
 */

document.addEventListener("DOMContentLoaded", () => {
    initDynamicTitle();
    initDynamicTagline();
    initCountdownTimer();
    initScrollAnimations();
    initCounters();
    initNavLinksClose();
    initScrollSpy();
});

/* ----------------------------------------------------------
   1. Dynamic Title Typing & Glow Animation on Reload
   ---------------------------------------------------------- */
function initDynamicTitle() {
    const titleEl = document.getElementById("dynamicHeroTitle");
    if (!titleEl) return;

    const fullText = "COGNOS 2K26";
    titleEl.textContent = "";
    titleEl.style.borderRight = "3px solid #00e5ff";
    
    let index = 0;
    const typingSpeed = 90; // ms per char

    function typeChar() {
        if (index < fullText.length) {
            titleEl.textContent += fullText.charAt(index);
            index++;
            setTimeout(typeChar, typingSpeed);
        } else {
            // Finished typing: remove cursor after 1.5s
            setTimeout(() => {
                titleEl.style.borderRight = "none";
            }, 1200);
        }
    }

    // Start typing after brief delay
    setTimeout(typeChar, 300);
}

function initDynamicTagline() {
    const taglineEl = document.querySelector(".hero-tagline");
    if (!taglineEl) return;

    const fullText = '"LET THE DATA SPEAK"';
    taglineEl.textContent = "";
    taglineEl.style.borderRight = "3px solid #e11d48";

    let index = 0;
    function typeChar() {
        if (index < fullText.length) {
            taglineEl.textContent += fullText.charAt(index);
            index++;
            setTimeout(typeChar, 65);
        } else {
            setTimeout(() => {
                taglineEl.style.borderRight = "none";
            }, 1200);
        }
    }

    setTimeout(typeChar, 700);
}

/* ----------------------------------------------------------
   2. Live Countdown Timer (Target: October 9, 2026, 09:00 AM)
   ---------------------------------------------------------- */
function initCountdownTimer() {
    const targetDate = new Date("2026-10-09T09:00:00").getTime();

    const daysEl = document.getElementById("timer-days");
    const hoursEl = document.getElementById("timer-hours");
    const minsEl = document.getElementById("timer-mins");
    const secsEl = document.getElementById("timer-secs");

    if (!daysEl) return;

    function updateTimer() {
        const now = new Date().getTime();
        const difference = targetDate - now;

        if (difference <= 0) {
            daysEl.innerText = "00";
            hoursEl.innerText = "00";
            minsEl.innerText = "00";
            secsEl.innerText = "00";
            return;
        }

        const days = Math.floor(difference / (1000 * 60 * 60 * 24));
        const hours = Math.floor((difference % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
        const mins = Math.floor((difference % (1000 * 60 * 60)) / (1000 * 60));
        const secs = Math.floor((difference % (1000 * 60)) / 1000);

        daysEl.innerText = String(days).padStart(2, "0");
        hoursEl.innerText = String(hours).padStart(2, "0");
        minsEl.innerText = String(mins).padStart(2, "0");
        secsEl.innerText = String(secs).padStart(2, "0");
    }

    updateTimer();
    setInterval(updateTimer, 1000);
}

/* ----------------------------------------------------------
   3. Scroll Reveal Animations (IntersectionObserver)
   ---------------------------------------------------------- */
function initScrollAnimations() {
    const elements = document.querySelectorAll(".animate-on-scroll");
    if (!elements || elements.length === 0) return;

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add("is-visible");
                obs.unobserve(entry.target);
            }
        });
    }, {
        threshold: 0.15,
        rootMargin: "0px 0px -40px 0px"
    });

    elements.forEach(el => observer.observe(el));
}

/* ----------------------------------------------------------
   4. Stat Counter Numbers Animation
   ---------------------------------------------------------- */
function initCounters() {
    const counterElements = document.querySelectorAll(".counter-target");
    if (!counterElements || counterElements.length === 0) return;

    const observer = new IntersectionObserver((entries, obs) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                const target = parseInt(entry.target.getAttribute("data-target"), 10);
                if (!isNaN(target)) {
                    animateValue(entry.target, 0, target, 1200);
                }
                obs.unobserve(entry.target);
            }
        });
    }, { threshold: 0.5 });

    counterElements.forEach(el => observer.observe(el));
}

function animateValue(element, start, end, duration) {
    let startTimestamp = null;
    const isPlus = element.innerText.includes("+");

    function step(timestamp) {
        if (!startTimestamp) startTimestamp = timestamp;
        const progress = Math.min((timestamp - startTimestamp) / duration, 1);
        const current = Math.floor(progress * (end - start) + start);
        element.innerText = current + (isPlus ? "+" : "");
        if (progress < 1) {
            window.requestAnimationFrame(step);
        } else {
            element.innerText = end + (isPlus ? "+" : "");
        }
    }

    window.requestAnimationFrame(step);
}

/* ----------------------------------------------------------
   5. Event Details Modal
   ---------------------------------------------------------- */
function openEventModal(eventId) {
    if (typeof COGNOS_EVENTS_DATA === "undefined") return;
    const data = COGNOS_EVENTS_DATA[eventId];
    if (!data) return;

    const modal = document.getElementById("eventModal");
    const modalContent = document.getElementById("eventModalContent");
    if (!modal || !modalContent) return;

    // Build Prize Pills with Color Tiers
    const prizesHtml = data.prizesBreakdown.map((p, idx) => {
        let tierClass = "bronze-tier";
        if (idx === 0) tierClass = "gold-tier";
        else if (idx === 1) tierClass = "silver-tier";

        return `
            <div class="prize-tier ${tierClass}" style="padding: 10px; border-radius: 8px;">
                <div class="tier-label" style="font-size: 11px;">${p.pos}</div>
                <div class="tier-amount" style="font-size: 15px; font-weight: 800;">${p.amt}</div>
            </div>
        `;
    }).join("");

    // Build Domains (if Razzle Review)
    let domainsHtml = "";
    if (data.domains && data.domains.length > 0) {
        const chips = data.domains.map(d => `<span class="domain-chip" style="display: inline-block; background: #eff6ff; color: #1d4ed8; padding: 4px 10px; border-radius: 4px; font-size: 12px; margin: 3px; font-weight: 600; border: 1px solid #bfdbfe;">${d}</span>`).join("");
        domainsHtml = `
            <div style="margin: 18px 0; background: #f8fafc; padding: 14px; border-radius: 8px; border: 1px solid #e2e8f0;">
                <h4 style="font-size: 13px; text-transform: uppercase; color: #1d4ed8; margin-bottom: 8px; font-weight: 700;">Approved Presentation Domains:</h4>
                <div>${chips}</div>
            </div>
        `;
    }

    // Build Rules List
    const rulesHtml = data.rules.map((rule, idx) => `
        <li class="rule-item" style="display: flex; gap: 10px; margin-bottom: 8px; font-size: 13.5px; line-height: 1.5; color: #334155;">
            <span class="rule-number" style="background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; min-width: 22px; height: 22px; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700;">${idx + 1}</span>
            <span>${rule}</span>
        </li>
    `).join("");

    // Build Coordinators List
    const facultyList = data.coordinators.faculty.map(f => `
        <p style="margin: 4px 0; font-size: 13px;">
            <strong>${f.name}</strong> &ndash; 
            <a href="tel:${f.phone}" style="color: #1d4ed8; font-weight: 700;">${f.phone}</a>
        </p>
    `).join("");

    const studentList = data.coordinators.students.map(s => `
        <p style="margin: 4px 0; font-size: 13px;">
            <strong>${s.name}</strong>
            ${s.phone ? ` &ndash; <a href="tel:${s.phone}" style="color: #1d4ed8; font-weight: 700;">${s.phone}</a>` : ""}
        </p>
    `).join("");

    modalContent.innerHTML = `
        <div class="modal-header">
            <div class="modal-title-group">
                <span class="modal-badge">${data.tagNumber}</span>
                <h2 class="modal-title">${data.name}</h2>
            </div>
            <button class="modal-close-btn" onclick="closeEventModal()">&times;</button>
        </div>

        <div class="modal-body">
            <div class="event-modal-about" style="font-size: 14px; color: #475569; margin-bottom: 20px; line-height: 1.6;">
                <p>${data.about}</p>
            </div>

            <div class="event-details-grid" style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 20px;">
                <div class="detail-block" style="background: #f8fafc; padding: 12px 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Date &amp; Timing</div>
                    <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-top: 2px;">${data.date} &bull; ${data.timingDisplay}</div>
                </div>
                <div class="detail-block" style="background: #f8fafc; padding: 12px 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Team Format</div>
                    <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-top: 2px;">${data.teamSize}</div>
                </div>
                <div class="detail-block" style="grid-column: span 2; background: #f8fafc; padding: 12px 16px; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <div style="font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase;">Venue &amp; Lab Allotment</div>
                    <div style="font-size: 14px; font-weight: 700; color: #0f172a; margin-top: 2px;">${data.venue}</div>
                </div>
            </div>

            <div class="prizes-container" style="background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px; padding: 16px; margin-bottom: 20px;">
                <div style="font-size: 14px; font-weight: 800; color: #b45309; margin-bottom: 12px; display: flex; align-items: center; gap: 6px;">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#d97706" stroke-width="2"><path d="M6 9H4.5a2.5 2.5 0 0 1 0-5H6"></path><path d="M18 9h1.5a2.5 2.5 0 0 0 0-5H18"></path><path d="M4 22h16"></path><path d="M10 14.66V17c0 .55-.45 1-1 1H8c-.55 0-1 .45-1 1v1h10v-1c0-.55-.45-1-1-1h-1c-.55 0-1-.45-1-1v-2.34"></path><path d="M6 4h12v7a6 6 0 0 1-12 0V4z"></path></svg>
                    Cash Prize Pool: ${data.prizePool}
                </div>
                <div style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 10px;">${prizesHtml}</div>
            </div>

            ${domainsHtml}

            <div class="rules-container" style="margin-bottom: 20px;">
                <h4 style="font-size: 14px; text-transform: uppercase; color: #0f172a; margin-bottom: 10px; font-weight: 700;">Official Rules &amp; Guidelines:</h4>
                <ul style="list-style: none;">${rulesHtml}</ul>
            </div>

            <div class="modal-coordinators" style="background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px; padding: 16px;">
                <div style="font-size: 12px; color: #1d4ed8; text-transform: uppercase; letter-spacing: 0.8px; margin-bottom: 10px; font-weight: 700;">Event Coordinators</div>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px;">
                    <div>
                        <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 4px;">Faculty Coordinators</span>
                        ${facultyList}
                    </div>
                    <div>
                        <span style="font-size: 11px; color: #64748b; text-transform: uppercase; font-weight: 700; display: block; margin-bottom: 4px;">Student Coordinators</span>
                        ${studentList}
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer-cta">
            <button class="btn-secondary-action" onclick="closeEventModal()">Close Window</button>
        </div>
    `;

    modal.classList.add("active");
    document.body.style.overflow = "hidden";
}

function closeEventModal() {
    const modal = document.getElementById("eventModal");
    if (modal) {
        modal.classList.remove("active");
        document.body.style.overflow = "";
    }
}

/* ----------------------------------------------------------
   6. Mobile Navigation
   ---------------------------------------------------------- */
function toggleNav() {
    const navLinks = document.getElementById("navLinks");
    if (navLinks) {
        navLinks.classList.toggle("show");
        navLinks.classList.toggle("active");
    }
}

function initNavLinksClose() {
    const navItems = document.querySelectorAll(".nav-link, .btn-nav-register");
    navItems.forEach(item => {
        item.addEventListener("click", () => {
            const nav = document.getElementById("navLinks");
            if (nav) {
                nav.classList.remove("show");
                nav.classList.remove("active");
            }
        });
    });

    // Close when clicking outside navbar on mobile
    document.addEventListener("click", (e) => {
        const navbar = document.getElementById("mainNavbar");
        const nav = document.getElementById("navLinks");
        if (nav && (nav.classList.contains("show") || nav.classList.contains("active"))) {
            if (navbar && !navbar.contains(e.target)) {
                nav.classList.remove("show");
                nav.classList.remove("active");
            }
        }
    });
}

function initScrollSpy() {
    const sections = Array.from(document.querySelectorAll("section[id]"));
    const links = Array.from(document.querySelectorAll('.nav-link[href^="#"]'));
    if (!sections.length || !links.length || !("IntersectionObserver" in window)) return;

    const linkById = new Map(links.map(link => [link.getAttribute("href").slice(1), link]));
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            links.forEach(link => link.classList.remove("active"));
            const activeLink = linkById.get(entry.target.id);
            if (activeLink) activeLink.classList.add("active");
        });
    }, {
        rootMargin: "-28% 0px -62% 0px",
        threshold: 0
    });

    sections.forEach(section => observer.observe(section));
}

/* ----------------------------------------------------------
   7. Lightbox Modal
   ---------------------------------------------------------- */
function openLightbox(src) {
    const modal = document.getElementById("lightboxModal");
    const img = document.getElementById("lightboxImg");
    if (modal && img) {
        img.src = src;
        modal.classList.add("active");
        document.body.style.overflow = "hidden";
    }
}

function closeLightbox() {
    const modal = document.getElementById("lightboxModal");
    if (modal) {
        modal.classList.remove("active");
        document.body.style.overflow = "";
    }
}

// Close modals on Escape key
document.addEventListener("keydown", (e) => {
    if (e.key === "Escape") {
        closeEventModal();
        closeLightbox();
        if (typeof closeRegistrationModal === "function") closeRegistrationModal();
        if (typeof closeSuccessModal === "function") closeSuccessModal();
    }
});
