<x-app-layout>
<div id="autosaveOverlay2" class="autosave-overlay hidden">Saving…</div>
<form id="pds-form2" method="POST" action="{{ route('pds.saveStep', [2], false) }}" enctype="multipart/form-data">
@csrf

    <style>
        table { border-collapse: collapse; width: 100%; }
        td, th { padding: 4px; vertical-align: top; }

        .border { border: 1px solid #000 !important; }
        .border-2 { border: 2px solid #000 !important; }
        .signature-box {
            position: relative;
            background: transparent;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            overflow: hidden;
        }
        .signature-box.signature-has-image {
            background: transparent;
            border-color: transparent;
        }
        .signature-box.signature-has-image img {
            inset: 0 !important;
            width: 100% !important;
            height: 100% !important;
        }
        @media print {
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        /* Offset native scroll positioning to account for sticky navbar */
        html, body { scroll-padding-top: 240px; }

        textarea { border: none; outline: none; padding: 8px; width: 100%; font: inherit; resize: none; background: transparent; line-height: 1.3; display: block; box-sizing: border-box; overflow: hidden; white-space: pre-wrap; word-break: break-word; min-height: 38px; height: auto; }

        textarea:focus, input:focus { outline: none; box-shadow: none; }
        /* Prevent focused fields from hiding under sticky header when scrolled into view */
        input, textarea, select { scroll-margin-top: 240px; }
        input[type="checkbox"] { width: 12px; height: 12px; }
        .autosave-overlay { position: fixed; inset: 0; background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; z-index: 9999; font-size: 20px; font-weight: 700; color: #111; }
        .autosave-overlay.hidden { display: none; }
    </style>
    <script>
        function checkWorkFields() {
            // Get all work fields for sequential row logic
            const positionFields = document.querySelectorAll('textarea[name="work_position_title[]"]');
            const workFromFields = document.querySelectorAll('input[name="work_from[]"]');
            const workToFields = document.querySelectorAll('input[name="work_to[]"]');
            const departmentFields = document.querySelectorAll('textarea[name="work_department[]"]');
            const statusFields = document.querySelectorAll('textarea[name="work_status[]"]');
            const govtServiceFields = document.querySelectorAll('textarea[name="work_govt_service[]"]');

            const isNA = (val) => {
                const v = (val || '').trim().toUpperCase();
                return v === 'NA' || v === 'N/A' || v === 'NONE';
            };

            const isRowComplete = (index) => {
                const positionVal = positionFields[index]?.value?.trim() || '';
                if (positionVal === '') return false; // Empty first column = incomplete
                if (isNA(positionVal)) return true; // NA in first column = complete (skip row)

                // Check if all required fields in the row are filled
                const fromVal = workFromFields[index]?.value?.trim() || '';
                const toVal = workToFields[index]?.value?.trim() || '';
                const deptVal = departmentFields[index]?.value?.trim() || '';
                const statusVal = statusFields[index]?.value?.trim() || '';
                const govtVal = govtServiceFields[index]?.value?.trim() || '';

                // All fields are required if position is filled (not NA)
                return (fromVal !== '' || isNA(fromVal)) &&
                       (toVal !== '' || isNA(toVal)) &&
                       (deptVal !== '' || isNA(deptVal)) &&
                       (statusVal !== '' || isNA(statusVal)) &&
                       (govtVal !== '' || isNA(govtVal));
            };

            const setRowEnabled = (index, enabled) => {
                const fields = [
                    positionFields[index],
                    workFromFields[index],
                    workToFields[index],
                    departmentFields[index],
                    statusFields[index],
                    govtServiceFields[index]
                ];

                fields.forEach(field => {
                    if (!field) return;
                    field.disabled = !enabled;
                    field.classList.toggle('bg-gray-200', !enabled);
                    field.classList.toggle('text-gray-500', !enabled);
                    field.classList.toggle('cursor-not-allowed', !enabled);
                    if (!enabled && (field.tagName === 'TEXTAREA' || field.tagName === 'INPUT')) {
                        field.value = '';
                        if (field.type === 'date') {
                            field.classList.remove('visible');
                        }
                    }
                });
            };

            // Process each row sequentially
            let allowNextRow = true;
            positionFields.forEach((positionField, index) => {
                const workFromField = workFromFields[index];
                const workToField = workToFields[index];
                const hasPosition = positionField.value.trim() !== '';
                const isNAValue = isNA(positionField.value);

                // First row is always enabled
                if (index === 0) {
                    setRowEnabled(index, true);
                } else {
                    // Enable row only if previous rows are complete
                    setRowEnabled(index, allowNextRow);
                }

                // Show/hide date fields based on position value and row enabled state
                if (workFromField && !workFromField.disabled) {
                    if (hasPosition && !isNAValue) {
                        workFromField.classList.add('visible');
                        workFromField.style.backgroundColor = 'transparent';
                    } else {
                        workFromField.classList.remove('visible');
                    }
                }

                if (workToField && !workToField.disabled) {
                    if (hasPosition && !isNAValue) {
                        workToField.classList.add('visible');
                        workToField.style.backgroundColor = 'transparent';
                    } else {
                        workToField.classList.remove('visible');
                    }
                }

                // Update allowNextRow for the next iteration
                if (allowNextRow) {
                    allowNextRow = isRowComplete(index);
                }
            });
        }

        function checkEligibilityFields() {
            // Get all eligibility fields for sequential row logic
            const eligibilityFields = document.querySelectorAll('textarea[name="eligibility[]"]');
            const ratingFields = document.querySelectorAll('textarea[name="rating[]"]');
            const dateFields = document.querySelectorAll('input[name="date[]"]');
            const placeFields = document.querySelectorAll('textarea[name="place[]"]');
            const licenseFields = document.querySelectorAll('textarea[name="license_no[]"]');
            const validityFields = document.querySelectorAll('textarea[name="validity[]"]');

            const isNA = (val) => {
                const v = (val || '').trim().toUpperCase();
                return v === 'NA' || v === 'N/A' || v === 'NONE';
            };

            const isRowComplete = (index) => {
                const eligibilityVal = eligibilityFields[index]?.value?.trim() || '';
                if (eligibilityVal === '') return false; // Empty first column = incomplete
                if (isNA(eligibilityVal)) return true; // NA in first column = complete (skip row)

                // Check if all required fields in the row are filled
                const ratingVal = ratingFields[index]?.value?.trim() || '';
                const dateVal = dateFields[index]?.value?.trim() || '';
                const placeVal = placeFields[index]?.value?.trim() || '';
                const licenseVal = licenseFields[index]?.value?.trim() || '';
                const validityVal = validityFields[index]?.value?.trim() || '';

                // All fields are required if eligibility is filled (not NA)
                return (ratingVal !== '' || isNA(ratingVal)) &&
                       (dateVal !== '' || isNA(dateVal)) &&
                       (placeVal !== '' || isNA(placeVal)) &&
                       (licenseVal !== '' || isNA(licenseVal)) &&
                       (validityVal !== '' || isNA(validityVal));
            };

            const setRowEnabled = (index, enabled) => {
                const fields = [
                    eligibilityFields[index],
                    ratingFields[index],
                    dateFields[index],
                    placeFields[index],
                    licenseFields[index],
                    validityFields[index]
                ];

                fields.forEach(field => {
                    if (!field) return;
                    field.disabled = !enabled;
                    field.classList.toggle('bg-gray-200', !enabled);
                    field.classList.toggle('text-gray-500', !enabled);
                    field.classList.toggle('cursor-not-allowed', !enabled);
                    if (!enabled && (field.tagName === 'TEXTAREA' || field.tagName === 'INPUT')) {
                        field.value = '';
                        if (field.type === 'date') {
                            field.classList.remove('visible');
                        }
                    }
                });
            };

            // Process each row sequentially
            let allowNextRow = true;
            eligibilityFields.forEach((eligibilityField, index) => {
                const dateField = dateFields[index];
                const hasEligibility = eligibilityField.value.trim() !== '';
                const isNAValue = isNA(eligibilityField.value);

                // First row is always enabled
                if (index === 0) {
                    setRowEnabled(index, true);
                } else {
                    // Enable row only if previous rows are complete
                    setRowEnabled(index, allowNextRow);
                }

                // Show/hide date fields based on eligibility value and row enabled state
                if (dateField && !dateField.disabled) {
                    if (hasEligibility && !isNAValue) {
                        dateField.classList.add('visible');
                        dateField.style.backgroundColor = 'transparent';
                    } else {
                        dateField.classList.remove('visible');
                    }
                }

                // Update allowNextRow for the next iteration
                if (allowNextRow) {
                    allowNextRow = isRowComplete(index);
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const autoSize = (el) => {
                el.style.height = 'auto';
                el.style.height = `${el.scrollHeight}px`;
            };

            document.querySelectorAll('textarea').forEach(el => {
                // uppercase enforcement
                el.addEventListener('input', () => {
                    const start = el.selectionStart;
                    const end = el.selectionEnd;
                    const upper = el.value.toUpperCase();
                    if (el.value !== upper) {
                        el.value = upper;
                        el.setSelectionRange(start, end);
                    }
                    autoSize(el);
                });

                // initial sizing
                requestAnimationFrame(() => autoSize(el));
            });

            // NA helper
            const isNA = (val) => {
                const v = (val || '').trim().toUpperCase();
                return v === 'NA' || v === 'N/A' || v === 'NONE';
            };

            // First-row logic for eligibility and work tables
            const rowGroup = (names) => names.map(n => Array.from(document.querySelectorAll(`[name="${n}"]`))).filter(arr => arr.length).map(arr => arr[0]);
            const eligibilityFirstRow = rowGroup(['eligibility[]']);
            const workFirstRow = rowGroup(['work_position_title[]']);

            const disableFollowingRows = (names, disable) => {
                names.forEach(n => {
                    const fields = Array.from(document.querySelectorAll(`[name="${n}"]`));
                    fields.forEach((f, idx) => {
                        if (idx === 0) return;
                        f.disabled = disable;
                        f.classList.toggle('bg-gray-200', disable);
                        f.classList.toggle('text-gray-500', disable);
                        f.classList.toggle('cursor-not-allowed', disable);
                        if (disable && f.tagName === 'TEXTAREA') {
                            f.value = '';
                        }
                    });
                });
            };

            const fillRowWithNA = (names, rowIndex = 0) => {
                names.forEach(n => {
                    const fields = Array.from(document.querySelectorAll(`[name="${n}"]`));
                    const target = fields[rowIndex];
                    if (target) target.value = 'NA';
                });
            };

            const clearRowNA = (names, rowIndex = 0) => {
                names.forEach(n => {
                    const fields = Array.from(document.querySelectorAll(`[name="${n}"]`));
                    const target = fields[rowIndex];
                    if (target && isNA(target.value)) target.value = '';
                });
            };

            const scrollToField = (el) => {
                if (!el) return;
                const nav = document.querySelector('nav');
                const navHeight = nav?.getBoundingClientRect().height || 80;
                const offset = navHeight + 540;
                const anchor = el.closest('td, th') || el;

                const applyOffset = () => {
                    const targetY = Math.max(anchor.getBoundingClientRect().top + window.pageYOffset - offset, 0);
                    window.scrollTo({ top: targetY, behavior: 'auto' });
                };

                applyOffset();
                requestAnimationFrame(applyOffset);
                setTimeout(applyOffset, 140);
                setTimeout(applyOffset, 300);
            };

            const firstRowState = (fields) => {
                const active = fields.filter(f => f && !f.disabled && !f.readOnly);
                const optionalNames = new Set();

                const allBlank = active.every(f => (f.value || '').trim() === '');
                const allNA = active.length && active.every(f => isNA(f.value));

                let requiredMissing = false;
                if (!(allBlank || allNA)) {
                    requiredMissing = active.some(f => {
                        if (optionalNames.has(f.name)) return false;
                        return !isFilled(f);
                    });
                }

                const incomplete = !(allBlank || allNA) && requiredMissing;

                return { allBlank, allNA, incomplete };
            };

            let prevDisableEligibilityRows = null;
            let prevDisableWorkRows = null;

            const refreshRows = () => {
                const eligibilityFirst = eligibilityFirstRow[0];
                const workFirst = workFirstRow[0];

                const disableEligibilityRows = eligibilityFirst ? isNA(eligibilityFirst.value) : false;
                const disableWorkRows = workFirst ? isNA(workFirst.value) : false;

                disableFollowingRows(['eligibility[]','rating[]','date[]','place[]','license_no[]','validity[]'], disableEligibilityRows);
                disableFollowingRows(['work_from[]','work_to[]','work_position_title[]','work_department[]','work_status[]','work_govt_service[]'], disableWorkRows);

                if (disableEligibilityRows) {
                    fillRowWithNA(['eligibility[]','rating[]','date[]','place[]','license_no[]','validity[]'], 0);
                } else if (prevDisableEligibilityRows === true && disableEligibilityRows === false) {
                    // Clear auto-filled NA once when toggling off, but allow user to enter NA afterward
                    clearRowNA(['rating[]','date[]','place[]','license_no[]','validity[]'], 0);
                }

                if (disableWorkRows) {
                    fillRowWithNA(['work_from[]','work_to[]','work_position_title[]','work_department[]','work_status[]','work_govt_service[]'], 0);
                } else if (prevDisableWorkRows === true && disableWorkRows === false) {
                    clearRowNA(['work_to[]','work_position_title[]','work_department[]','work_status[]','work_govt_service[]'], 0);
                }

                prevDisableEligibilityRows = disableEligibilityRows;
                prevDisableWorkRows = disableWorkRows;
            };

            [...eligibilityFirstRow, ...workFirstRow].forEach(f => {
                if (!f) return;
                f.addEventListener('input', refreshRows);
            });
            refreshRows();

            // Check work fields on page load
            checkWorkFields();

            // Add event listeners to all work table fields for sequential logic
            const workTableFields = document.querySelectorAll(
                'textarea[name="work_position_title[]"], input[name="work_from[]"], input[name="work_to[]"], ' +
                'textarea[name="work_department[]"], textarea[name="work_status[]"], textarea[name="work_govt_service[]"]'
            );
            workTableFields.forEach(field => {
                field.addEventListener('input', checkWorkFields);
                field.addEventListener('change', checkWorkFields);
            });

            // Check eligibility fields on page load
            checkEligibilityFields();

            // Add event listeners to all eligibility table fields for sequential logic
            const eligibilityTableFields = document.querySelectorAll(
                'textarea[name="eligibility[]"], textarea[name="rating[]"], input[name="date[]"], ' +
                'textarea[name="place[]"], textarea[name="license_no[]"], textarea[name="validity[]"]'
            );
            eligibilityTableFields.forEach(field => {
                field.addEventListener('input', checkEligibilityFields);
                field.addEventListener('change', checkEligibilityFields);
            });

            // Next button inline validation: required fields + first rows (allow NA as filled)
            const nextBtn = document.getElementById('next-btn');
            const requiredFields = Array.from(document.querySelectorAll('[required]'));

            const isFilled = (el) => {
                if (el.type === 'file') return el.files && el.files.length > 0;
                if (el.type === 'checkbox' || el.type === 'radio') return el.checked;
                const val = (el.value || '').trim();
                if (isNA(val)) return true;
                return val !== '';
            };

            const firstRowSets = [eligibilityFirstRow, workFirstRow];

            const workRowNames = ['work_from[]','work_to[]','work_position_title[]','work_department[]','work_status[]','work_govt_service[]'];
            const eligibilityRowNames = ['eligibility[]','rating[]','date[]','place[]','license_no[]','validity[]'];

            const clearValidity = () => {
                requiredFields.forEach(el => el.setCustomValidity(''));
                firstRowSets.flat().forEach(f => f?.setCustomValidity(''));
                document.querySelectorAll('[name^="work_"]').forEach(el => el.setCustomValidity(''));
                document.querySelectorAll('[name="eligibility[]"], [name="rating[]"], [name="date[]"], [name="place[]"], [name="license_no[]"], [name="validity[]"]').forEach(el => el.setCustomValidity(''));
            };

            const enforceRowCompleteness = (names, optionalNames = new Set()) => {
                let invalidField = null;
                const columns = names.map(n => Array.from(document.querySelectorAll(`[name="${n}"]`)));
                const maxRows = Math.max(...columns.map(c => c.length));

                for (let row = 0; row < maxRows; row++) {
                    const rowFields = columns.map(c => c[row]).filter(Boolean);
                    if (!rowFields.length) continue;

                    // Skip disabled/readOnly rows
                    const activeRow = rowFields.filter(f => f && !f.disabled && !f.readOnly);
                    if (!activeRow.length) continue;

                    // Trigger completeness if ANY non-optional field in the row has data (not blank/NA)
                    const rowHasData = activeRow.some(f => !optionalNames.has(f.name) && !isNA(f.value) && (f.value || '').trim() !== '');
                    if (!rowHasData) continue;

                    // All non-optional columns must be filled or NA
                    for (const f of activeRow) {
                        if (optionalNames.has(f.name)) continue;
                        const val = (f.value || '').trim();
                        const filled = val !== '' || isNA(val);
                        if (!filled) {
                            f.setCustomValidity('Complete all fields in this row or clear the first column.');
                            if (!invalidField) invalidField = f;
                        }
                    }
                }

                return invalidField;
            };

            const hasMissingRequired = () => {
                const hasMissingRequiredField = requiredFields.some(el => !el.disabled && !el.readOnly && !isFilled(el));

                const firstRowsIncomplete = firstRowSets.some(set => {
                    const state = firstRowState(set);
                    return state.incomplete || state.allBlank;
                });

                const incompleteWorkRowField = enforceRowCompleteness(workRowNames);
                const incompleteEligibilityRowField = enforceRowCompleteness(eligibilityRowNames);

                return hasMissingRequiredField || firstRowsIncomplete || Boolean(incompleteWorkRowField) || Boolean(incompleteEligibilityRowField);
            };

            const focusFirstMissing = () => {
                for (const el of requiredFields) {
                    if (el.disabled || el.readOnly) continue;
                    if (!isFilled(el)) {
                        el.setCustomValidity('This field is required.');
                        el.reportValidity();
                        el.focus();
                        scrollToField(el);
                        return true;
                    }
                }

                const sets = [eligibilityFirstRow, workFirstRow];
                for (const set of sets) {
                    const state = firstRowState(set);
                    if (state.incomplete || state.allBlank) {
                        const firstMissing = set.find(f => f && !isFilled(f)) || set[0];
                        if (firstMissing) {
                            firstMissing.setCustomValidity('Please complete the first row or mark N/A.');
                            firstMissing.reportValidity();
                            firstMissing.focus();
                            scrollToField(firstMissing);
                            return true;
                        }
                    }
                }

                const incompleteWork = enforceRowCompleteness(workRowNames);
                if (incompleteWork) {
                    incompleteWork.reportValidity();
                    incompleteWork.focus();
                    scrollToField(incompleteWork);
                    return true;
                }

                const incompleteElig = enforceRowCompleteness(eligibilityRowNames);
                if (incompleteElig) {
                    incompleteElig.reportValidity();
                    incompleteElig.focus();
                    scrollToField(incompleteElig);
                    return true;
                }

                return false;
            };

            document.addEventListener('input', () => { refreshRows(); clearValidity(); }, true);
            document.addEventListener('change', () => { refreshRows(); clearValidity(); }, true);

            if (nextBtn) {
                nextBtn.addEventListener('click', (e) => {
                    clearValidity();
                    if (hasMissingRequired()) {
                        e.preventDefault();
                        e.stopPropagation();
                        focusFirstMissing();
                    }
                });
            }

            const sessionData = @json(session('pds', []));
            const draftData = @json($data ?? []);
            const flat = {};
            const walk = (obj, prefix = '') => {
                if (obj === null || obj === undefined) return;
                if (typeof obj !== 'object') { if (prefix) flat[prefix] = obj; return; }
                if (Array.isArray(obj)) {
                    obj.forEach((v, i) => walk(v, prefix ? `${prefix}[${i}]` : `${i}`));
                } else {
                    Object.entries(obj).forEach(([k, v]) => walk(v, prefix ? `${prefix}[${k}]` : k));
                }
            };
            walk(sessionData);
            walk(draftData);

            const cssName = (name) => name.replace(/(["'\\])/g, '\\$1');
            const setField = (el, value) => {
                if (!el) return;
                if (el.type === 'checkbox' || el.type === 'radio') {
                    el.checked = Array.isArray(value) ? value.map(String).includes(String(el.value)) : String(el.value) === String(value);
                } else {
                    el.value = value;
                    if (el.tagName === 'TEXTAREA') {
                        el.dispatchEvent(new Event('input'));
                    }
                }
            };

            Object.entries(flat).forEach(([name, value]) => {
                const exact = Array.from(document.querySelectorAll(`[name="${cssName(name)}"]`));
                if (exact.length) {
                    exact.forEach(el => setField(el, value));
                    return;
                }
                const matchIndex = name.match(/\[(\d+)\]$/);
                if (matchIndex) {
                    const idx = parseInt(matchIndex[1], 10);
                    const base = name.replace(/\[\d+\]$/, '[]');
                    const arrFields = Array.from(document.querySelectorAll(`[name="${cssName(base)}"]`));
                    if (arrFields[idx]) setField(arrFields[idx], value);
                }
            });

            // Local cache + draft fetch + autosave (copied from form1)
            const form = document.querySelector('#pds-form2');
            if (!form) return;

            const storageKey = 'pds_form_step2_' + ({{ auth()->id() ?? 0 }});
            const singleSelectCheckboxNames = new Set();
            const storageBase = "{{ asset('storage') }}/";
            const initialSignaturePath = @json($signaturePath ?? '');

            const signaturePreviewImg2 = document.getElementById('signaturePreviewImg2');
            const signaturePlaceholder2 = document.getElementById('signaturePlaceholder2');
            const signatureBox2 = document.getElementById('signatureBox2');
            const signatureDataInput2 = document.getElementById('signature_data2');
            const signaturePathInput2 = document.getElementById('signature_path2');

            const buildSignatureUrl2 = (path) => {
                if (!path) return '';
                const cleaned = path.replace(/^public\//, '');
                return storageBase + cleaned;
            };

            const updateSignaturePreviewFromInputs2 = () => {
                if (!signaturePreviewImg2 || !signaturePlaceholder2) return;
                const dataUrl = signatureDataInput2?.value;
                const pathVal = signaturePathInput2?.value || initialSignaturePath;
                const url = dataUrl || buildSignatureUrl2(pathVal);
                if (url) {
                    signaturePreviewImg2.src = url;
                    signaturePreviewImg2.classList.remove('hidden');
                    signaturePlaceholder2.classList.add('hidden');
                    signatureBox2?.classList.add('signature-has-image');
                } else {
                    signaturePreviewImg2.classList.add('hidden');
                    signaturePlaceholder2.classList.remove('hidden');
                    signatureBox2?.classList.remove('signature-has-image');
                }
            };

            window.handleSignatureUpload2 = (file) => {
                if (!file) return;
                const reader = new FileReader();
                reader.onload = (e) => {
                    const img = new Image();
                    img.onload = () => {
                        const canvas = document.createElement('canvas');
                        canvas.width = img.width;
                        canvas.height = img.height;
                        const ctx = canvas.getContext('2d');
                        ctx.drawImage(img, 0, 0);

                        const imageData = ctx.getImageData(0, 0, canvas.width, canvas.height);
                        const data = imageData.data;
                        const threshold = 240;
                        for (let i = 0; i < data.length; i += 4) {
                            if (data[i] > threshold && data[i + 1] > threshold && data[i + 2] > threshold) {
                                data[i + 3] = 0;
                            }
                        }
                        ctx.putImageData(imageData, 0, 0);

                        const output = canvas.toDataURL('image/png');
                        if (signatureDataInput2) signatureDataInput2.value = output;
                        if (signaturePathInput2) signaturePathInput2.value = '';
                        if (signaturePreviewImg2) {
                            signaturePreviewImg2.src = output;
                            signaturePreviewImg2.classList.remove('hidden');
                        }
                        if (signaturePlaceholder2) signaturePlaceholder2.classList.add('hidden');
                        if (signatureBox2) signatureBox2.classList.add('signature-has-image');
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            };

            // LOAD CACHE (localStorage + optional override from draft)
            const loadCache = (overrideData = null) => {
                let data = {};
                try {
                    data = JSON.parse(localStorage.getItem(storageKey) || '{}');
                } catch (e) {
                    data = {};
                }

                if (overrideData) {
                    // Prefer local cache; only backfill keys missing locally
                    Object.entries(overrideData).forEach(([k, v]) => {
                        if (data[k] === undefined) {
                            data[k] = v;
                        }
                    });
                }

                // Only apply server/session signature if local value is missing
                if (overrideData?.signature_path && signaturePathInput2 && !signaturePathInput2.value) {
                    signaturePathInput2.value = overrideData.signature_path;
                }
                if (overrideData?.signature_data && signatureDataInput2 && !signatureDataInput2.value) {
                    signatureDataInput2.value = overrideData.signature_data;
                }

                // For fields with [] names, ensure arrays apply in order
                Object.entries(data).forEach(([name, stored]) => {
                    const elements = Array.from(form.querySelectorAll(`[name="${name}"]`));

                    if (name.endsWith('[]') && Array.isArray(stored)) {
                        elements.forEach((el, idx) => {
                            const val = stored[idx] ?? '';
                            if (el.type === 'checkbox') {
                                el.checked = Array.isArray(val) ? val.includes(el.value) : String(val) === el.value;
                            } else if (el.type === 'radio') {
                                el.checked = val === el.value;
                            } else {
                                el.value = val;
                            }
                            if (el.tagName === 'TEXTAREA' || el.type === 'text') {
                                el.dispatchEvent(new Event('input', { bubbles: true }));
                            }
                        });
                        return;
                    }

                    elements.forEach(el => {
                        if (el.type === 'checkbox') {
                            if (Array.isArray(stored)) {
                                el.checked = stored.includes(el.value);
                            } else {
                                el.checked = String(stored) === el.value;
                            }
                        }
                        else if (el.type === 'radio') {
                            el.checked = stored === el.value;
                        }
                        else {
                            el.value = stored;
                        }

                        if (el.tagName === 'TEXTAREA' || el.type === 'text') {
                            el.dispatchEvent(new Event('input', { bubbles: true }));
                        }
                    });
                });
            };

            // SAVE CACHE locally (preserve [] groups as arrays)
            const saveCache = () => {
                const data = {};

                Array.from(form.elements).forEach(el => {
                    if (!el.name || el.disabled) return;
                    if (["button","submit","reset","file"].includes(el.type)) return;

                    // Skip very large/base64 fields (e.g., signature data URLs) to avoid quota errors
                    const rawValue = el.value || '';
                    const isLargeDataUrl = typeof rawValue === 'string' && rawValue.length > 2000 && rawValue.startsWith('data:');
                    const skipCacheFields = ['signature_data', 'signature_path', 'signature', 'signature_file'];
                    if (skipCacheFields.includes(el.name) || isLargeDataUrl) {
                        return;
                    }

                    const isArrayField = el.name.endsWith('[]');

                    if (el.type === 'checkbox') {
                        if (singleSelectCheckboxNames.has(el.name)) {
                            if (el.checked) {
                                data[el.name] = rawValue;
                            } else if (!data[el.name]) {
                                data[el.name] = '';
                            }
                        } else {
                            if (!data[el.name]) data[el.name] = isArrayField ? [] : [];
                            if (el.checked) data[el.name].push(rawValue);
                        }
                    }
                    else if (el.type === 'radio') {
                        if (el.checked) data[el.name] = rawValue;
                    }
                    else {
                        if (isArrayField) {
                            if (!data[el.name]) data[el.name] = [];
                            data[el.name].push(rawValue);
                        } else {
                            data[el.name] = rawValue;
                        }
                    }
                });

                try {
                    localStorage.setItem(storageKey, JSON.stringify(data));
                } catch (err) {
                    console.warn('localStorage quota exceeded, skipping cache save', err);
                }
            };

            // AUTOSAVE to server (throttled)
            const autosaveOverlay = document.getElementById('autosaveOverlay2');

            const autoSaveToServer = (() => {
                let timer;
                const retryDelay = 1200;
                const showOverlay = (flag) => {
                    if (!autosaveOverlay) return;
                    autosaveOverlay.classList.toggle('hidden', !flag);
                };
                const send = () => {
                    const formData = new FormData(form);
                    fetch('{{ route('pds.autosave', [], false) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                        },
                        credentials: 'same-origin',
                        body: formData
                    })
                    .then(response => {
                        if (!response.ok) {
                            showOverlay(true);
                            setTimeout(send, retryDelay);
                            throw new Error('Auto-save failed');
                        }
                        return response.json();
                    })
                    .then(data => {
                        const ok = data && data.status === 'ok';
                        if (ok) {
                            showOverlay(false);
                        } else {
                            showOverlay(true);
                            setTimeout(send, retryDelay);
                        }
                    })
                    .catch(() => {
                        showOverlay(true);
                        setTimeout(send, retryDelay);
                    });
                };

                return () => {
                    clearTimeout(timer);
                    timer = setTimeout(send, 800);
                };
            })();

            const persist = () => {
                saveCache();
                autoSaveToServer();
            };

            loadCache();
            updateSignaturePreviewFromInputs2();
            // Persist merged cache once so a fast refresh keeps latest values
            saveCache();

            // If served via static view (no $data), fetch draft and hydrate once
            fetch('{{ route('pds.draft', [], false) }}', { headers: { 'Accept': 'application/json' } })
                .then(r => r.ok ? r.json() : null)
                .then(json => {
                    if (!json || !json.data) return;
                    loadCache(json.data);
                    updateSignaturePreviewFromInputs2();
                    // Persist merged cache once so a fast refresh keeps latest values
                    saveCache();
                })
                .catch(() => {});

            form.addEventListener('input', persist);
            form.addEventListener('change', persist);
        });

        // Check for master date from form1 and apply it
        function syncFromForm1() {
            const masterDate = localStorage.getItem('pds_master_date');
            if (masterDate) {
                console.log('Found master date from localStorage:', masterDate);
                const date2Input = document.querySelector('input[name="date2"]');
                if (date2Input && date2Input.value !== masterDate) {
                    console.log('Updating form2 date to:', masterDate);
                    date2Input.value = masterDate;
                    // Trigger change event to save to cache
                    date2Input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        }

        // Check for master date when page loads
        syncFromForm1();

        // Also check periodically in case user navigates back from form1
        setInterval(syncFromForm1, 1000);
    </script>
    <div class="max-w-6xl mx-auto p-4 font-serif text-sm">

    <table data-section="civil_service" class="border border-black w-full font-['Arial_Narrow','sans-serif']">

      <colgroup>
        <col style="width: 35%;">
        <col style="width: 10%;">
        <col style="width: 15%;">
        <col style="width: 15%;">
        <col style="width: 8%;">
        <col style="width: 8%;">
      </colgroup>
      
    <th class="font-['Arial_Narrow','Arial',sans-serif] text-left bg-[#8a8a8a] text-white  italic text-xl px-2 border-2 border-black font-bold" colspan="6">
      IV.  CIVIL SERVICE ELIGIBILITY
    </th>

    <tr>
      <th class="border bg-[#e7e7e7]" rowspan="2">
        27. CES/CSEE/CAREER SERVICE/RA 1080 (BOARD/ BAR)/UNDER SPECIAL LAWS/CATEGORY II/ IV ELIGIBILITY and ELIGIBILITIES FOR UNIFORMED PERSONNEL
      </th>

      <th class="border bg-[#e7e7e7]" rowspan="2">
        RATING <p>(If Applicable) </p>
      </th>

      <th class="border bg-[#e7e7e7]" rowspan="2">
        DATE OF EXAMINATION / CONFERMENT
      </th>

      <th class="border bg-[#e7e7e7]" rowspan="2">
        PLACE OF EXAMINATION / CONFERMENT
      </th>

       <th class="border bg-[#e7e7e7]" colspan="2">
        LICENSE (if applicable)
      </th>
    </tr>


    <tr>
    <th class="border text-center bg-[#e7e7e7]">NUMBER</th>
    <th class="border text-center bg-[#e7e7e7]">VALID UNTIL</th>
   </tr>

   @for ($i = 0; $i < 7; $i++)
      <tr>
        <td class="border align-top"><textarea rows="1" placeholder="{{ $i === 0 ? 'Eligibility' : '' }}" name="eligibility[]" ></textarea></td>
        <td class="border align-top"><textarea rows="1" placeholder="{{ $i === 0 ? 'Rating' : '' }}" name="rating[]"></textarea></td>
        <td class="border align-top">
          <div class="h-full w-full">
            <input
              type="date"
              name="date[]"
              class="w-full h-full text-lg resize-none
                     focus:outline-none focus:ring-0
                     bg-transparent text-center"
              style="font-size: 14px; padding:2px; border:none; box-sizing:border-box; margin:0; display: none;"
              max="{{ now()->format('Y-m-d') }}" 
              placeholder="{{ $i === 0 ? 'Date' : '' }}"/>
          </div>
        </td>
        <td class="border align-top"><textarea rows="1" placeholder="{{ $i === 0 ? 'Place' : '' }}" name="place[]"></textarea></td>
        <td class="border align-top"><textarea rows="1" placeholder="{{ $i === 0 ? 'License No.' : '' }}" name="license_no[]"></textarea></td>
        <td class="border align-top"><textarea rows="1" placeholder="{{ $i === 0 ? 'Validity' : '' }}" name="validity[]"></textarea></td>
      </tr>
   @endfor
    </table>
    

    <table data-section="work_experience" class="border border-black font-['Arial_Narrow','sans-serif'] w-full">

      <colgroup>
        <col style="width: 8%;">
        <col style="width: 8%;">
        <col style="width: 20%;">
        <col style="width: 28%;">
        <col style="width: 20%;">
        <col style="width: 10%;">
      </colgroup>

      <th class="font-['Arial_Narrow','Arial',sans-serif] text-left bg-[#8a8a8a] text-white  italic text-xl px-2 border-2 border-black font-bold" colspan="6">
      V.  WORK EXPERIENCE 
      <p class="font-extralight text-lg">(Include private employment.  Start from your recent work.) Description of duties should be indicated in the attached Work Experience Sheet.</p>
     </th>

     <tr class="border">

         <th colspan="2" class="border bg-[#e7e7e7]">
        INCLUSIVE DATES OF ATTENDANCE<p>(dd/mm/yyyy)</p>
      </th>

      <th rowspan="2" class="bg-[#e7e7e7]">POSITION TITLE<p>(Write in full/Do not abbreviate)</p></th>

       <th rowspan="2" class="border bg-[#e7e7e7]">
        DEPARTMENT / AGENCY / OFFICE / COMPANY (Write in full/Do not abbreviate)
      </th>

      <th rowspan="2" class="border bg-[#e7e7e7]">
        STATUS OF APPOINTMENT
      </th>

      <th rowspan="2" class="border bg-[#e7e7e7]">
         GOV'T SERVICE (Y/ N)
      </th>
     </tr>

     <tr class=" bg-[#e7e7e7]">
      <th class="border text-center font-light bg-[#e7e7e7]">FROM</th>
      <th class="border text-center font-light bg-[#e7e7e7]">TO</th>
     </tr>

    @for ($i = 0; $i < 27; $i++)
     <tr>
      <td class="border align-top">
        <div class="h-full w-full">
          <input
            type="date"
            name="work_from[]"
            class="w-full h-full text-lg resize-none
                   focus:outline-none focus:ring-0
                   bg-transparent text-center"
            style="font-size: 14px; padding:2px; border:none; box-sizing:border-box; margin:0;"
            placeholder="{{ $i === 0 ? 'From' : '' }}"/>
        </div>
      </td>
      <td class="border align-top">
        <div class="h-full w-full">
          <input
            type="date"
            name="work_to[]"
            class="w-full h-full text-lg resize-none
                   focus:outline-none focus:ring-0
                   bg-transparent text-center"
            style="font-size: 14px; padding:2px; border:none; box-sizing:border-box; margin:0;"
            placeholder="{{ $i === 0 ? 'To' : '' }}"/>
        </div>
      </td>
      <td class="border align-top"><textarea rows="1" placeholder="{{ $i === 0 ? 'Position Title' : '' }}" name="work_position_title[]"></textarea></td>
      <td class="border align-top"><textarea rows="1" placeholder="{{ $i === 0 ? 'Department/Agency/Office/Company' : '' }}" name="work_department[]"></textarea></td>
      <td class="border align-top"><textarea rows="1" placeholder="{{ $i === 0 ? 'Status' : '' }}" name="work_status[]"></textarea></td>
      <td class="border align-top"><textarea rows="1" placeholder="{{ $i === 0 ? 'Y/N' : '' }}" name="work_govt_service[]"></textarea></td>
     </tr>
    @endfor
    <tr>
      <table class="border-black w-full h-15 font-['Arial_Narrow','sans-serif'] italic">
        <colgroup>
          <col style="width: 17%;">
           <col style="width: 20%;">
            <col style="width: 16.02%;">
             <col style="width: 15%;">
        </colgroup>
      <tr>
          <td class="border h-2 text-center text-xl font-bold italic align-middle">
      SIGNATURE
    </td>

       <td class="border" colspan="2">
     <div class="h-full w-full p-2">
        <label id="signatureBox2" data-signature-cell class="signature-box block h-36 w-full cursor-pointer">
          <input
            type="file"
            name="signature_file"
            id="signatureFileInput2"
            accept="image/*"
            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
            onchange="handleSignatureUpload2(this.files[0])"
          >
          <img
            id="signaturePreviewImg2"
            src="{{ !empty($signaturePath) ? Storage::url($signaturePath) : '' }}"
            alt="Signature preview"
            class="absolute inset-0 w-full h-full object-contain {{ empty($signaturePath) ? 'hidden' : '' }}"
          >
          <div id="signaturePlaceholder2" class="absolute inset-0" aria-hidden="true"></div>
        </label>
        <input type="hidden" name="signature_path" id="signature_path2" value="{{ $signaturePath ?? '' }}">
        <input type="hidden" name="signature_data" id="signature_data2">
      </div>
    </td>

      <td class="border text-center text-xl font-bold italic align-middle" colspan="2">
      DATE
    </td>

       <td
          class="border h-10">
          <div class="h-full w-full flex items-center justify-center">
         <input
      type="date"
      name="date2"
      required
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             px-2 py-1 text-center bg-transparent border-none"
    ></input>
      </td>
      </tr>
    </table>

    </tr>
    </table>    

     
      <div class="flex justify-end mr-2 border-b-0 font-['Arial_Narrow','sans-serif']">
    CS FORM 212 (Revised 2025), Page 2 of 5
    </div>

        <div class="flex justify-between mt-4">
            <a href="{{ route('pds.form1') }}" class="px-4 py-2 bg-blue-600 text-white rounded shadow border border-blue-700 hover:bg-blue-700">Previous Page</a>
            <button type="submit" id="next-btn" class="px-4 py-2 bg-blue-600 text-white rounded shadow border border-blue-700 hover:bg-blue-700">Next Page</button>
        </div>
</form>

<style>
/* Custom styling for date inputs - bigger calendar icon and middle text alignment */
input[type="date"] {
  color-scheme: light dark;
  font-size: 30px;
  text-align: center !important;
  display: flex !important;
  align-items: center !important;
  justify-content: center !important;
  padding: 0 !important;
  margin-left: 50px;
}

/* Hide eligibility date inputs by default */
input[name="date[]"] {
  display: none !important;
}

/* Hide work date inputs by default */
input[name="work_from[]"], input[name="work_to[]"] {
  display: none !important;
}

/* Show date inputs when they should be visible */
input[type="date"].visible {
  display: block !important;
}

/* Make calendar icon visible and clickable with blue stroke */
input[type="date"].visible::-webkit-calendar-picker-indicator {
  display: block !important;
  cursor: pointer;
  opacity: 1;
  filter: invert(35%) sepia(100%) saturate(1500%) hue-rotate(190deg) brightness(95%) contrast(95%) !important;
  -webkit-filter: invert(35%) sepia(100%) saturate(4500%) hue-rotate(190deg) brightness(95%) contrast(150%) !important;
  transform: scale(1.5);
  margin-left: 5px;
}

input[type="date"].visible::-moz-calendar-picker-indicator {
  display: block !important;
  cursor: pointer;
  opacity: 1;
  filter: invert(35%) sepia(100%) saturate(1500%) hue-rotate(190deg) brightness(95%) contrast(95%) !important;
  -webkit-filter: invert(35%) sepia(100%) saturate(1500%) hue-rotate(190deg) brightness(95%) contrast(95%) !important;
}

/* Hide calendar icon for the signature date field */
input[name="date2"]::-webkit-calendar-picker-indicator {
  display: none !important;
}

input[name="date2"]::-moz-calendar-picker-indicator {
  display: none !important;
}

input[type="date"].visible::-moz-calendar-picker-indicator {
  display: block !important;
  cursor: pointer;
  opacity: 1;
  filter: invert(35%) sepia(100%) saturate(1500%) hue-rotate(190deg) brightness(95%) contrast(95%) !important;
  -webkit-filter: invert(35%) sepia(100%) saturate(1500%) hue-rotate(190deg) brightness(95%) contrast(95%) !important;
  transform: scale(1.5);
  margin-left: 5px;
}

/* Ensure text is vertically centered and black */
input[type="date"]::-webkit-datetime-edit-text {
  vertical-align: middle;
  color: #000000;
  font-size: 20px;
  text-align: center !important;
}

input[type="date"]::-webkit-datetime-edit-month-field {
  vertical-align: middle;
  font-size: 20px;
  color: #000000;
  text-align: center !important;
}

input[type="date"]::-webkit-datetime-edit-day-field {
  vertical-align: middle;
  font-size: 20px;
  color: #000000;
  text-align: center !important;
}

input[type="date"]::-webkit-datetime-edit-year-field {
  vertical-align: middle;
  font-size: 20px;
  color: #000000;
  text-align: center !important;
}

/* Firefox date input text color and centering */
input[type="date"]::-moz-datetime-edit-text {
  color: #000000;
  text-align: center !important;
}

input[type="date"]::-moz-datetime-edit-month-field {
  color: #000000;
  text-align: center !important;
}

input[type="date"]::-moz-datetime-edit-day-field {
  color: #000000;
  text-align: center !important;
}

input[type="date"]::-moz-datetime-edit-year-field {
  color: #000000;
  text-align: center !important;
}
</style>

<script>
function checkEligibilityFields() {
    // Get all eligibility fields for sequential row logic
    const eligibilityFields = document.querySelectorAll('textarea[name="eligibility[]"]');
    const ratingFields = document.querySelectorAll('textarea[name="rating[]"]');
    const dateFields = document.querySelectorAll('input[name="date[]"]');
    const placeFields = document.querySelectorAll('textarea[name="place[]"]');
    const licenseFields = document.querySelectorAll('textarea[name="license_no[]"]');
    const validityFields = document.querySelectorAll('textarea[name="validity[]"]');
    
    const isNA = (val) => {
        const v = (val || '').trim().toUpperCase();
        return v === 'NA' || v === 'N/A' || v === 'NONE';
    };
    
    const isRowComplete = (index) => {
        const eligVal = eligibilityFields[index]?.value?.trim() || '';
        if (eligVal === '') return false; // Empty first column = incomplete
        if (isNA(eligVal)) return true; // NA in first column = complete (skip row)
        
        // Check if all required fields in the row are filled
        const ratingVal = ratingFields[index]?.value?.trim() || '';
        const dateVal = dateFields[index]?.value?.trim() || '';
        const placeVal = placeFields[index]?.value?.trim() || '';
        
        // Rating, date, and place are required if eligibility is filled (not NA)
        return (ratingVal !== '' || isNA(ratingVal)) && 
               (dateVal !== '' || isNA(dateVal)) && 
               (placeVal !== '' || isNA(placeVal));
    };
    
    const setRowEnabled = (index, enabled) => {
        const fields = [
            eligibilityFields[index],
            ratingFields[index],
            dateFields[index],
            placeFields[index],
            licenseFields[index],
            validityFields[index]
        ];
        
        fields.forEach(field => {
            if (!field) return;
            field.disabled = !enabled;
            field.classList.toggle('bg-gray-200', !enabled);
            field.classList.toggle('text-gray-500', !enabled);
            field.classList.toggle('cursor-not-allowed', !enabled);
            if (!enabled && field.tagName === 'TEXTAREA') {
                field.value = '';
            }
            if (!enabled && field.tagName === 'INPUT') {
                field.value = '';
                field.classList.remove('visible');
            }
        });
    };
    
    // Process each row sequentially
    let allowNextRow = true;
    eligibilityFields.forEach((eligibilityField, index) => {
        const dateField = dateFields[index];
        const hasEligibility = eligibilityField.value.trim() !== '';
        const isNAValue = isNA(eligibilityField.value);
        
        // First row is always enabled
        if (index === 0) {
            setRowEnabled(index, true);
        } else {
            // Enable row only if previous rows are complete
            setRowEnabled(index, allowNextRow);
        }
        
        // Show/hide date field based on eligibility value
        if (dateField && !dateField.disabled) {
            if (hasEligibility && !isNAValue) {
                dateField.classList.add('visible');
                dateField.style.backgroundColor = 'transparent';
            } else {
                dateField.classList.remove('visible');
                if (!isNAValue) {
                    dateField.value = '';
                }
            }
        }
        
        // Update allowNextRow for the next iteration
        if (allowNextRow) {
            allowNextRow = isRowComplete(index);
        }
    });
}

function checkWorkFields() {
    // Get all work fields for sequential row logic
    const workFromFields = document.querySelectorAll('input[name="work_from[]"]');
    const workToFields = document.querySelectorAll('input[name="work_to[]"]');
    const positionFields = document.querySelectorAll('textarea[name="work_position_title[]"]');
    const departmentFields = document.querySelectorAll('textarea[name="work_department[]"]');
    const statusFields = document.querySelectorAll('textarea[name="work_status[]"]');
    const govtServiceFields = document.querySelectorAll('textarea[name="work_govt_service[]"]');
    
    const isNA = (val) => {
        const v = (val || '').trim().toUpperCase();
        return v === 'NA' || v === 'N/A' || v === 'NONE';
    };
    
    const isRowComplete = (index) => {
        const posVal = positionFields[index]?.value?.trim() || '';
        if (posVal === '') return false; // Empty first column = incomplete
        if (isNA(posVal)) return true; // NA in first column = complete (skip row)
        
        // Check if all required fields in the row are filled
        const fromVal = workFromFields[index]?.value?.trim() || '';
        const toVal = workToFields[index]?.value?.trim() || '';
        const deptVal = departmentFields[index]?.value?.trim() || '';
        const statusVal = statusFields[index]?.value?.trim() || '';
        const govtVal = govtServiceFields[index]?.value?.trim() || '';
        
        // All fields are required if position is filled (not NA)
        return (fromVal !== '' || isNA(fromVal)) && 
               (toVal !== '' || isNA(toVal)) && 
               (deptVal !== '' || isNA(deptVal)) &&
               (statusVal !== '' || isNA(statusVal)) &&
               (govtVal !== '' || isNA(govtVal));
    };
    
    const setRowEnabled = (index, enabled) => {
        const fields = [
            workFromFields[index],
            workToFields[index],
            positionFields[index],
            departmentFields[index],
            statusFields[index],
            govtServiceFields[index]
        ];
        
        fields.forEach(field => {
            if (!field) return;
            field.disabled = !enabled;
            field.classList.toggle('bg-gray-200', !enabled);
            field.classList.toggle('text-gray-500', !enabled);
            field.classList.toggle('cursor-not-allowed', !enabled);
            if (!enabled && field.tagName === 'TEXTAREA') {
                field.value = '';
            }
            if (!enabled && field.tagName === 'INPUT') {
                field.value = '';
                field.classList.remove('visible');
            }
        });
    };
    
    // Process each row sequentially
    let allowNextRow = true;
    positionFields.forEach((positionField, index) => {
        const workFromField = workFromFields[index];
        const workToField = workToFields[index];
        const hasPosition = positionField.value.trim() !== '';
        const isNAValue = isNA(positionField.value);
        
        // First row is always enabled
        if (index === 0) {
            setRowEnabled(index, true);
        } else {
            // Enable row only if previous rows are complete
            setRowEnabled(index, allowNextRow);
        }
        
        // Show/hide date fields based on position value and row enabled state
        if (workFromField && !workFromField.disabled) {
            if (hasPosition && !isNAValue) {
                workFromField.classList.add('visible');
                workFromField.style.backgroundColor = 'transparent';
            } else {
                workFromField.classList.remove('visible');
            }
        }
        
        if (workToField && !workToField.disabled) {
            if (hasPosition && !isNAValue) {
                workToField.classList.add('visible');
                workToField.style.backgroundColor = 'transparent';
            } else {
                workToField.classList.remove('visible');
            }
        }
        
        // Update allowNextRow for the next iteration
        if (allowNextRow) {
            allowNextRow = isRowComplete(index);
        }
    });
}

document.addEventListener('DOMContentLoaded', () => {
    // Check eligibility fields on page load
    checkEligibilityFields();
    
    // Add event listeners to all eligibility table fields for sequential logic
    const eligibilityTableFields = document.querySelectorAll(
        'textarea[name="eligibility[]"], textarea[name="rating[]"], input[name="date[]"], ' +
        'textarea[name="place[]"], textarea[name="license_no[]"], textarea[name="validity[]"]'
    );
    eligibilityTableFields.forEach(field => {
        field.addEventListener('input', checkEligibilityFields);
        field.addEventListener('change', checkEligibilityFields);
    });
    
    // Check work fields on page load
    checkWorkFields();
    
    // Add event listeners to all work table fields for sequential logic
    const workTableFields = document.querySelectorAll(
        'input[name="work_from[]"], input[name="work_to[]"], textarea[name="work_position_title[]"], ' +
        'textarea[name="work_department[]"], textarea[name="work_status[]"], textarea[name="work_govt_service[]"]'
    );
    workTableFields.forEach(field => {
        field.addEventListener('input', checkWorkFields);
        field.addEventListener('change', checkWorkFields);
    });
</script>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const sections = @json($highlightedSections ?? []);
    if (!Array.isArray(sections) || sections.length === 0) return;
    sections.forEach(key => {
        const el = document.querySelector(`[data-section="${key}"]`);
        if (el) {
            el.style.outline = '3px solid #ef4444';
            el.style.outlineOffset = '2px';
            el.style.borderRadius = '2px';
        }
    });
});
</script>
</x-app-layout>