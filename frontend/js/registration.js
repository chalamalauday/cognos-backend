/**
 * COGNOS 2K26 - Multi-Event Registration Handler
 * Manages modal states, teammate toggles, file upload, form validation, and AJAX submission.
 */

document.addEventListener("DOMContentLoaded", () => {
    initRegistrationForm();
});

function initRegistrationForm() {
    const regForm = document.getElementById("cognosRegForm");
    const teammateToggle = document.getElementById("teammateToggle");
    const teammateBox = document.getElementById("teammateBox");
    const idCardInput = document.getElementById("idCardInput");
    const uploadZone = document.getElementById("fileUploadZone");
    const fileBadge = document.getElementById("fileBadge");
    const distanceInput = document.getElementById("distance_from_college_km");
    const accommodationSelect = document.getElementById("accommodation_required");
    const accommodationHelp = document.getElementById("accommodationHelp");
    const vishleshanaBox = document.getElementById("vishleshanaParticipantBox");
    const vishleshanaParticipation = document.getElementById("vishleshanaParticipation");

    function syncAccommodationEligibility() {
        if (!distanceInput || !accommodationSelect) return;

        const distance = Number(distanceInput.value);
        const isEligible = Number.isFinite(distance) && distance > 100;
        const yesOption = accommodationSelect.querySelector('option[value="1"]');
        if (yesOption) yesOption.disabled = !isEligible;

        if (!isEligible && accommodationSelect.value === "1") {
            accommodationSelect.value = "0";
        }

        if (accommodationHelp) {
            accommodationHelp.textContent = isEligible
                ? "You are eligible to request accommodation because your distance is more than 100 km."
                : "Accommodation requests are accepted only for participants travelling more than 100 km.";
            accommodationHelp.style.color = isEligible ? "#047857" : "#64748b";
        }
    }

    if (distanceInput) {
        distanceInput.addEventListener("input", syncAccommodationEligibility);
        syncAccommodationEligibility();
    }

    // 1. Teammate Section Toggle
    if (teammateToggle && teammateBox) {
        teammateToggle.addEventListener("change", (e) => {
            if (e.target.checked) {
                teammateBox.classList.add("active");
                setTeammateRequired(true);
            } else {
                teammateBox.classList.remove("active");
                setTeammateRequired(false);
            }
            syncVishleshanaParticipants();
        });
    }

    function setTeammateRequired(isRequired) {
        const fields = ["teammate_name", "teammate_roll_no", "teammate_email", "teammate_branch", "teammate_college"];
        fields.forEach(f => {
            const input = document.getElementById(f);
            if (input) input.required = isRequired;
        });
    }

    function syncVishleshanaParticipants() {
        if (!vishleshanaBox || !vishleshanaParticipation) return;

        const checkedEvents = Array.from(document.querySelectorAll("input[name=\"events[]\"]:checked")).map(cb => cb.value);
        const vishleshanaSelected = checkedEvents.includes("Vishleshana");
        const isOnlyVishleshana = vishleshanaSelected && checkedEvents.length === 1;
        const hasTeammate = Boolean(teammateToggle && teammateToggle.checked);

        const showChoice = vishleshanaSelected && hasTeammate && !isOnlyVishleshana;
        vishleshanaBox.hidden = !showChoice;
        vishleshanaParticipation.required = showChoice;
        vishleshanaParticipation.disabled = !showChoice;
        if (!showChoice) {
            vishleshanaParticipation.value = vishleshanaSelected ? "primary_only" : "";
        }
    }

    // Dynamic Teammate Availability Check (Vishleshana is Strictly Individual)
    function syncTeammateAvailability() {
        const checkedEvents = Array.from(document.querySelectorAll("input[name=\"events[]\"]:checked")).map(cb => cb.value);
        const hasTeamEvent = checkedEvents.some(ev => ev === "Razzle Review" || ev === "Data Dazzle");
        const isOnlyVishleshana = checkedEvents.length === 1 && checkedEvents[0] === "Vishleshana";

        const teammateToggleWrap = document.querySelector(".teammate-toggle-wrap");
        const helpText = teammateToggleWrap ? teammateToggleWrap.querySelector(".toggle-info p") : null;

        if (isOnlyVishleshana) {
            // Only Vishleshana selected: disallow teammates
            if (teammateToggle) {
                teammateToggle.checked = false;
                teammateToggle.disabled = true;
            }
            if (teammateBox) {
                teammateBox.classList.remove("active");
                setTeammateRequired(false);
            }
            if (helpText) {
                helpText.innerHTML = '<span style="color: #e11d48; font-weight: 700;">Vishleshana is strictly an individual event (1 member).</span> Teammates can be added if you also select Razzle Review or Data Dazzle.';
            }
            if (teammateToggleWrap) {
                teammateToggleWrap.style.opacity = "0.75";
            }
        } else {
            // Team events present or no event selected yet: allow toggle
            if (teammateToggle) {
                teammateToggle.disabled = false;
            }
            if (helpText) {
                helpText.innerHTML = 'Applicable for Razzle Review (Max 2) and Data Dazzle (2 members). Vishleshana is individual only.';
            }
            if (teammateToggleWrap) {
                teammateToggleWrap.style.opacity = "1";
            }
        }
    }

    // 2. Drag & Drop / File Selection for College ID Card
    if (uploadZone && idCardInput) {
        uploadZone.addEventListener("click", () => idCardInput.click());

        uploadZone.addEventListener("dragover", (e) => {
            e.preventDefault();
            uploadZone.classList.add("dragover");
        });

        uploadZone.addEventListener("dragleave", () => {
            uploadZone.classList.remove("dragover");
        });

        uploadZone.addEventListener("drop", (e) => {
            e.preventDefault();
            uploadZone.classList.remove("dragover");
            if (e.dataTransfer.files.length > 0) {
                idCardInput.files = e.dataTransfer.files;
                handleFileSelect(idCardInput.files[0]);
            }
        });

        idCardInput.addEventListener("change", (e) => {
            if (e.target.files.length > 0) {
                handleFileSelect(e.target.files[0]);
            }
        });
    }

    function handleFileSelect(file) {
        const allowedExts = ["jpg", "jpeg", "png", "pdf", "webp"];
        const ext = file.name.split(".").pop().toLowerCase();

        if (!allowedExts.includes(ext)) {
            alert("Invalid file format. Please upload a JPG, PNG, or PDF file.");
            idCardInput.value = "";
            fileBadge.style.display = "none";
            return;
        }

        if (file.size > 5 * 1024 * 1024) {
            alert("File size exceeds 5MB limit. Please upload a smaller file.");
            idCardInput.value = "";
            fileBadge.style.display = "none";
            return;
        }

        fileBadge.innerHTML = `<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: -2px; margin-right: 4px;"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"></path><polyline points="14 2 14 8 20 8"></polyline></svg> ${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)`;
        fileBadge.style.display = "inline-block";
    }

    // 3. Event Checkbox Card Styling & Teammate Availability Sync
    const eventCards = document.querySelectorAll(".event-checkbox-card");
    eventCards.forEach(card => {
        const cb = card.querySelector("input[type=\"checkbox\"]");
        if (cb) {
            cb.addEventListener("change", () => {
                if (cb.checked) {
                    card.classList.add("selected");
                } else {
                    card.classList.remove("selected");
                }
                syncTeammateAvailability();
                syncVishleshanaParticipants();
            });
        }
    });

    // Initial check
    syncTeammateAvailability();
    syncVishleshanaParticipants();

    // Helper: Client-side ID card image compression to speed up upload by 10x
    async function compressImageIfImage(file, maxDimension = 1200, quality = 0.82) {
        if (!file || !file.type || !file.type.startsWith('image/') || file.type === 'image/svg+xml') {
            return file;
        }
        if (file.size < 300 * 1024) {
            return file; // If already under 300KB, skip compression
        }
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = (event) => {
                const img = new Image();
                img.onload = () => {
                    let { width, height } = img;
                    if (width > maxDimension || height > maxDimension) {
                        if (width > height) {
                            height = Math.round((height * maxDimension) / width);
                            width = maxDimension;
                        } else {
                            width = Math.round((width * maxDimension) / height);
                            height = maxDimension;
                        }
                    }
                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);
                    canvas.toBlob((blob) => {
                        if (blob && blob.size < file.size) {
                            const optimized = new File([blob], file.name.replace(/\.[^.]+$/, '.jpg'), {
                                type: 'image/jpeg',
                                lastModified: Date.now()
                            });
                            resolve(optimized);
                        } else {
                            resolve(file);
                        }
                    }, 'image/jpeg', quality);
                };
                img.onerror = () => resolve(file);
                img.src = event.target.result;
            };
            reader.onerror = () => resolve(file);
            reader.readAsDataURL(file);
        });
    }

    // 4. Form Submission via AJAX
    if (regForm) {
        regForm.addEventListener("submit", async (e) => {
            e.preventDefault();

            // Validate at least one event is checked
            const checkedEvents = Array.from(document.querySelectorAll("input[name=\"events[]\"]:checked"));
            if (checkedEvents.length === 0) {
                alert("Please select at least one event to participate in (Vishleshana, Razzle Review, or Data Dazzle).");
                return;
            }

            // Validate College ID card is uploaded
            if (!idCardInput.files || idCardInput.files.length === 0) {
                alert("Please upload your College ID Card.");
                return;
            }

            // Submit Button Loading State
            const submitBtn = document.getElementById("submitRegBtn");
            const originalBtnText = submitBtn.innerHTML;
            submitBtn.disabled = true;
            submitBtn.innerHTML = `<svg class="spin-icon" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" style="vertical-align: middle; margin-right: 6px;"><circle cx="12" cy="12" r="10" stroke-opacity="0.25"></circle><path d="M12 2a10 10 0 0 1 10 10" stroke-width="3"></path></svg> Processing Registration...`;

            const formData = new FormData(regForm);

            // Compress ID Card image on client side if > 300KB to make upload 10x faster
            if (idCardInput.files && idCardInput.files[0]) {
                try {
                    const optimizedFile = await compressImageIfImage(idCardInput.files[0]);
                    formData.set("id_card", optimizedFile);
                } catch (compressErr) {
                    console.warn("Client image compression fallback:", compressErr);
                }
            }

            // Adaptive API path resolver
            const isInsideFrontend = window.location.pathname.includes('/frontend');
            const apiUrl = window.COGNOS_API_URL || (window.COGNOS_API_BASE_URL ? `${window.COGNOS_API_BASE_URL}/register.php` : (isInsideFrontend ? '../backend/register.php' : 'backend/register.php'));

            try {
                const response = await fetch(apiUrl, {
                    method: "POST",
                    body: formData
                });

                const rawText = await response.text();
                let data;
                try {
                    const trimmed = rawText.trim().replace(/^\uFEFF/, '');
                    // Detect HTML error responses (404, 500, or InfinityFree bot challenge)
                    if (trimmed.startsWith('<') || trimmed.includes('<html') || trimmed.includes('<!DOCTYPE')) {
                        if (trimmed.includes('aes.js') || trimmed.includes('__test')) {
                            throw new Error("InfinityFree Free Tier Bot Challenge Blocked Request:\nInfinityFree prevents external API fetch calls using a mandatory bot security challenge (aes.js). Please upload the 'backend' folder directly to your college server alongside 'frontend' to run both from the same origin.");
                        } else if (response.status === 404 || trimmed.includes('404 Not Found')) {
                            throw new Error(`Registration API endpoint not found (404):\n${apiUrl}\n\nPlease ensure the 'backend' folder is uploaded to the server alongside 'frontend'.`);
                        } else {
                            throw new Error(`Server returned HTML instead of JSON (Status ${response.status}):\n${trimmed.replace(/<[^>]*>?/gm, ' ').replace(/\s+/g, ' ').substring(0, 150)}`);
                        }
                    }
                    // Clean BOM and parse JSON
                    data = JSON.parse(trimmed);
                } catch (jsonErr) {
                    console.error("Non-JSON Server Output:", rawText);
                    throw jsonErr;
                }

                if (data.success) {
                    // Registration Successful
                    closeRegistrationModal();
                    openSuccessModal(data);
                    regForm.reset();
                    fileBadge.style.display = "none";
                    teammateBox.classList.remove("active");
                    eventCards.forEach(c => c.classList.remove("selected"));
                    syncAccommodationEligibility();
                    syncTeammateAvailability();
                    syncVishleshanaParticipants();
                } else {
                    // Show validation or duplicate message
                    alert(data.message || "An error occurred during registration. Please try again.");
                }
            } catch (err) {
                console.error("Submission error:", err);
                alert(err.message || "Server connection failed. If running locally with XAMPP, ensure Apache & MySQL are running in XAMPP Control Panel.");
            } finally {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });
    }
}

/* ----------------------------------------------------------
   Modal State Management
   ---------------------------------------------------------- */
function openRegistrationModal(preselectEventName) {
    const modal = document.getElementById("registrationModal");
    if (!modal) return;

    // Reset event checkboxes
    document.querySelectorAll("input[name=\"events[]\"]").forEach(cb => {
        const card = cb.closest(".event-checkbox-card");
        if (preselectEventName && cb.value.toLowerCase() === preselectEventName.toLowerCase()) {
            cb.checked = true;
            if (card) card.classList.add("selected");
        }
    });

    modal.classList.add("active");
    document.body.style.overflow = "hidden";
}

function closeRegistrationModal() {
    const modal = document.getElementById("registrationModal");
    if (modal) {
        modal.classList.remove("active");
        document.body.style.overflow = "";
    }
}

function openSuccessModal(resData) {
    const modal = document.getElementById("successModal");
    if (!modal) return;

    document.getElementById("successRegCode").innerText = resData.reg_code;
    document.getElementById("successStudentName").innerText = resData.student_name;
    document.getElementById("successEmail").innerText = resData.email;
    
    // WhatsApp Community link
    const waBtn = document.getElementById("whatsappJoinBtn");
    if (waBtn && resData.whatsapp_link) {
        waBtn.href = resData.whatsapp_link;
    }

    modal.classList.add("active");
    document.body.style.overflow = "hidden";
}

function closeSuccessModal() {
    const modal = document.getElementById("successModal");
    if (modal) {
        modal.classList.remove("active");
        document.body.style.overflow = "";
    }
}
