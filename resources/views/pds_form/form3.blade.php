<x-app-layout>
<div id="autosaveOverlay3" class="autosave-overlay hidden">Saving…</div>
<form id="pds-form3" method="POST" action="{{ route('pds.saveStep', [3], false) }}" enctype="multipart/form-data">
@csrf
    <style>
  table {
    border-collapse: collapse;
    width: 100%;
 }
        td, th { padding: 4px; vertical-align: middle; }
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
        textarea:focus { outline: none; box-shadow: none; }
        textarea { border: none; outline: none; padding: 8px; width: 100%; font: inherit; resize: none; background: transparent; line-height: 1.3; display: block; box-sizing: border-box; overflow: hidden; white-space: pre-wrap; word-break: break-word; min-height: 38px; height: auto; }
        input[type="checkbox"] { width: 12px; height: 12px; }
        .autosave-overlay { position: fixed; inset: 0; background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; z-index: 9999; font-size: 20px; font-weight: 700; color: #111; }
        .autosave-overlay.hidden { display: none; }
    </style>
    <script>
        function checkVoluntaryFields() {
            // Get all voluntary fields for sequential row logic
            const organizationFields = document.querySelectorAll('textarea[name="voluntary_organization[]"]');
            const voluntaryFromFields = document.querySelectorAll('input[name="voluntary_from[]"]');
            const voluntaryToFields = document.querySelectorAll('input[name="voluntary_to[]"]');
            const hoursFields = document.querySelectorAll('input[name="voluntary_hours[]"]');
            const positionFields = document.querySelectorAll('textarea[name="voluntary_position_nature_of_work[]"]');
            
            const isNA = (val) => {
                const v = (val || '').trim().toUpperCase();
                return v === 'NA' || v === 'N/A' || v === 'NONE';
            };
            
            const isRowComplete = (index) => {
                const orgVal = organizationFields[index]?.value?.trim() || '';
                if (orgVal === '') return false; // Empty first column = incomplete
                if (isNA(orgVal)) return true; // NA in first column = complete (skip row)
                
                // Check if all required fields in the row are filled
                const fromVal = voluntaryFromFields[index]?.value?.trim() || '';
                const toVal = voluntaryToFields[index]?.value?.trim() || '';
                const hoursVal = hoursFields[index]?.value?.trim() || '';
                const posVal = positionFields[index]?.value?.trim() || '';
                
                // All fields are required if organization is filled (not NA)
                return (fromVal !== '' || isNA(fromVal)) && 
                       (toVal !== '' || isNA(toVal)) && 
                       (hoursVal !== '' || isNA(hoursVal)) &&
                       (posVal !== '' || isNA(posVal));
            };
            
            const setRowEnabled = (index, enabled) => {
                const fields = [
                    organizationFields[index],
                    voluntaryFromFields[index],
                    voluntaryToFields[index],
                    hoursFields[index],
                    positionFields[index]
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
            organizationFields.forEach((organizationField, index) => {
                const voluntaryFromField = voluntaryFromFields[index];
                const voluntaryToField = voluntaryToFields[index];
                const hasOrganization = organizationField.value.trim() !== '';
                const isNAValue = isNA(organizationField.value);
                
                // First row is always enabled
                if (index === 0) {
                    setRowEnabled(index, true);
                } else {
                    // Enable row only if previous rows are complete
                    setRowEnabled(index, allowNextRow);
                }
                
                // Show/hide date fields based on organization value and row enabled state
                if (voluntaryFromField && !voluntaryFromField.disabled) {
                    if (hasOrganization && !isNAValue) {
                        voluntaryFromField.classList.add('visible');
                        voluntaryFromField.style.backgroundColor = 'transparent';
                    } else {
                        voluntaryFromField.classList.remove('visible');
                    }
                }
                
                if (voluntaryToField && !voluntaryToField.disabled) {
                    if (hasOrganization && !isNAValue) {
                        voluntaryToField.classList.add('visible');
                        voluntaryToField.style.backgroundColor = 'transparent';
                    } else {
                        voluntaryToField.classList.remove('visible');
                    }
                }
                
                // Update allowNextRow for the next iteration
                if (allowNextRow) {
                    allowNextRow = isRowComplete(index);
                }
            });
        }

        function checkLearningFields() {
            // Get all learning fields for sequential row logic
            const titleFields = document.querySelectorAll('textarea[name="learning_title_of_ld[]"]');
            const learningFromFields = document.querySelectorAll('input[name="learning_from[]"]');
            const learningToFields = document.querySelectorAll('input[name="learning_to[]"]');
            const hoursFields = document.querySelectorAll('input[name="learning_hours[]"]');
            const typeFields = document.querySelectorAll('textarea[name="learning_type_of_ld[]"]');
            const conductedFields = document.querySelectorAll('textarea[name="learning_conducted_sponsored_by[]"]');
            
            const isNA = (val) => {
                const v = (val || '').trim().toUpperCase();
                return v === 'NA' || v === 'N/A' || v === 'NONE';
            };
            
            const isRowComplete = (index) => {
                const titleVal = titleFields[index]?.value?.trim() || '';
                if (titleVal === '') return false; // Empty first column = incomplete
                if (isNA(titleVal)) return true; // NA in first column = complete (skip row)
                
                // Check if all required fields in the row are filled
                const fromVal = learningFromFields[index]?.value?.trim() || '';
                const toVal = learningToFields[index]?.value?.trim() || '';
                const hoursVal = hoursFields[index]?.value?.trim() || '';
                const typeVal = typeFields[index]?.value?.trim() || '';
                const conductedVal = conductedFields[index]?.value?.trim() || '';
                
                // All fields are required if title is filled (not NA)
                return (fromVal !== '' || isNA(fromVal)) && 
                       (toVal !== '' || isNA(toVal)) && 
                       (hoursVal !== '' || isNA(hoursVal)) &&
                       (typeVal !== '' || isNA(typeVal)) &&
                       (conductedVal !== '' || isNA(conductedVal));
            };
            
            const setRowEnabled = (index, enabled) => {
                const fields = [
                    titleFields[index],
                    learningFromFields[index],
                    learningToFields[index],
                    hoursFields[index],
                    typeFields[index],
                    conductedFields[index]
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
            titleFields.forEach((titleField, index) => {
                const learningFromField = learningFromFields[index];
                const learningToField = learningToFields[index];
                const hasTitle = titleField.value.trim() !== '';
                const isNAValue = isNA(titleField.value);
                
                // First row is always enabled
                if (index === 0) {
                    setRowEnabled(index, true);
                } else {
                    // Enable row only if previous rows are complete
                    setRowEnabled(index, allowNextRow);
                }
                
                // Show/hide date fields based on title value and row enabled state
                if (learningFromField && !learningFromField.disabled) {
                    if (hasTitle && !isNAValue) {
                        learningFromField.classList.add('visible');
                        learningFromField.style.backgroundColor = 'transparent';
                    } else {
                        learningFromField.classList.remove('visible');
                    }
                }
                
                if (learningToField && !learningToField.disabled) {
                    if (hasTitle && !isNAValue) {
                        learningToField.classList.add('visible');
                        learningToField.style.backgroundColor = 'transparent';
                    } else {
                        learningToField.classList.remove('visible');
                    }
                }
                
                // Update allowNextRow for the next iteration
                if (allowNextRow) {
                    allowNextRow = isRowComplete(index);
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => {
            const form = document.querySelector('#pds-form3');
            if (!form) return;

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

            // Uppercase enforcement
            document.querySelectorAll('textarea').forEach(el => {
                el.addEventListener('input', () => {
                    const start = el.selectionStart;
                    const end = el.selectionEnd;
                    const upper = el.value.toUpperCase();
                    if (el.value !== upper) {
                        el.value = upper;
                        el.setSelectionRange(start, end);
                    }
                });
            });

            // NA handling helpers
            const isNA = (val) => {
                const v = (val || '').trim().toUpperCase();
                return v === 'NA' || v === 'N/A' || v === 'NONE';
            };

            // First-row logic per table
            const rowGroup = (namesArr) => namesArr.map(n => Array.from(document.querySelectorAll(`[name="${n}"]`))).filter(arr => arr.length).map(arr => arr[0]);
            const voluntaryFirstRow = rowGroup(['voluntary_organization[]','voluntary_from[]','voluntary_to[]','voluntary_hours[]','voluntary_position_nature_of_work[]']);
            const learningFirstRow = rowGroup(['learning_title_of_ld[]','learning_from[]','learning_to[]','learning_hours[]','learning_type_of_ld[]','learning_conducted_sponsored_by[]']);
            const otherInfoFirstRow = rowGroup(['special_skills_hobbies[]','non_academic_distinctions_recognition[]','membership_in_association_organization[]']);

            const disableFollowingRows = (namesArr, disable) => {
                namesArr.forEach(n => {
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

            const fillRowWithNA = (namesArr, rowIndex = 0) => {
                namesArr.forEach(n => {
                    const fields = Array.from(document.querySelectorAll(`[name="${n}"]`));
                    const target = fields[rowIndex];
                    if (target) target.value = 'NA';
                });
            };

            const clearRowNA = (namesArr, rowIndex = 0) => {
                namesArr.forEach(n => {
                    const fields = Array.from(document.querySelectorAll(`[name="${n}"]`));
                    const target = fields[rowIndex];
                    if (target && isNA(target.value)) target.value = '';
                });
            };

            let prevDisableVoluntary = null;
            let prevDisableLearning = null;
            let prevDisableOther = null;

            const firstColIsNA = (fields) => {
                const first = fields[0];
                if (!first) return false;
                return isNA(first.value);
            };

            const firstRowState = (fields) => {
                const active = fields.filter(f => f && !f.disabled && !f.readOnly);
                const optionalNames = new Set();

                // Check if first column has NA - if so, row is complete
                const firstField = active[0];
                if (firstField && isNA(firstField.value)) {
                    return { allBlank: false, allNA: true, incomplete: false };
                }

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

            const refreshRows = () => {
                const disableVoluntary = firstColIsNA(voluntaryFirstRow);
                const disableLearning = firstColIsNA(learningFirstRow);
                const disableOther = firstColIsNA(otherInfoFirstRow);

                const voluntaryNames = ['voluntary_organization[]','voluntary_from[]','voluntary_to[]','voluntary_hours[]','voluntary_position_nature_of_work[]'];
                const learningNames = ['learning_title_of_ld[]','learning_from[]','learning_to[]','learning_hours[]','learning_type_of_ld[]','learning_conducted_sponsored_by[]'];
                const otherNames = ['special_skills_hobbies[]','non_academic_distinctions_recognition[]','membership_in_association_organization[]'];

                disableFollowingRows(voluntaryNames, disableVoluntary);
                disableFollowingRows(learningNames, disableLearning);
                disableFollowingRows(otherNames, disableOther);

                if (disableVoluntary) {
                    fillRowWithNA(voluntaryNames, 0);
                } else if (prevDisableVoluntary === true && disableVoluntary === false) {
                    clearRowNA(voluntaryNames.slice(1), 0);
                }

                if (disableLearning) {
                    fillRowWithNA(learningNames, 0);
                } else if (prevDisableLearning === true && disableLearning === false) {
                    clearRowNA(learningNames.slice(1), 0);
                }

                if (disableOther) {
                    fillRowWithNA(otherNames, 0);
                } else if (prevDisableOther === true && disableOther === false) {
                    clearRowNA(otherNames.slice(1), 0);
                }

                prevDisableVoluntary = disableVoluntary;
                prevDisableLearning = disableLearning;
                prevDisableOther = disableOther;
            };

            [...voluntaryFirstRow, ...learningFirstRow, ...otherInfoFirstRow].forEach(f => {
                if (!f) return;
                f.addEventListener('input', refreshRows);
            });
            refreshRows();

            // Check voluntary fields on page load
            checkVoluntaryFields();
            
            // Add event listeners to all voluntary table fields for sequential logic
            const voluntaryTableFields = document.querySelectorAll(
                'textarea[name="voluntary_organization[]"], input[name="voluntary_from[]"], input[name="voluntary_to[]"], ' +
                'input[name="voluntary_hours[]"], textarea[name="voluntary_position_nature_of_work[]"]'
            );
            voluntaryTableFields.forEach(field => {
                field.addEventListener('input', checkVoluntaryFields);
                field.addEventListener('change', checkVoluntaryFields);
            });

            // Check learning fields on page load
            checkLearningFields();
            
            // Add event listeners to all learning table fields for sequential logic
            const learningTableFields = document.querySelectorAll(
                'textarea[name="learning_title_of_ld[]"], input[name="learning_from[]"], input[name="learning_to[]"], ' +
                'input[name="learning_hours[]"], textarea[name="learning_type_of_ld[]"], textarea[name="learning_conducted_sponsored_by[]"]'
            );
            learningTableFields.forEach(field => {
                field.addEventListener('input', checkLearningFields);
                field.addEventListener('change', checkLearningFields);
            });
            
            // Add sequential logic for Other Information table
            const checkOtherInfoFields = () => {
                const skillsFields = document.querySelectorAll('textarea[name="special_skills_hobbies[]"]');
                const distinctionsFields = document.querySelectorAll('textarea[name="non_academic_distinctions_recognition[]"]');
                const membershipFields = document.querySelectorAll('textarea[name="membership_in_association_organization[]"]');
                
                const isNA = (val) => {
                    const v = (val || '').trim().toUpperCase();
                    return v === 'NA' || v === 'N/A' || v === 'NONE';
                };
                
                const isRowComplete = (index) => {
                    const skillsVal = skillsFields[index]?.value?.trim() || '';
                    const distinctionsVal = distinctionsFields[index]?.value?.trim() || '';
                    const membershipVal = membershipFields[index]?.value?.trim() || '';
                    
                    // Row is complete if all fields are filled or all are NA
                    const allEmpty = skillsVal === '' && distinctionsVal === '' && membershipVal === '';
                    if (allEmpty) return false;
                    
                    // If any field has NA, consider it filled
                    const skillsFilled = skillsVal !== '' || isNA(skillsVal);
                    const distinctionsFilled = distinctionsVal !== '' || isNA(distinctionsVal);
                    const membershipFilled = membershipVal !== '' || isNA(membershipVal);
                    
                    return skillsFilled && distinctionsFilled && membershipFilled;
                };
                
                const setRowEnabled = (index, enabled) => {
                    const fields = [
                        skillsFields[index],
                        distinctionsFields[index],
                        membershipFields[index]
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
                    });
                };
                
                // Process each row sequentially
                let allowNextRow = true;
                for (let index = 0; index < skillsFields.length; index++) {
                    // First row is always enabled
                    if (index === 0) {
                        setRowEnabled(index, true);
                    } else {
                        setRowEnabled(index, allowNextRow);
                    }
                    
                    // Update allowNextRow for the next iteration
                    if (allowNextRow) {
                        allowNextRow = isRowComplete(index);
                    }
                }
            };
            
            // Check other info fields on page load
            checkOtherInfoFields();
            
            // Add event listeners to all other info table fields
            const otherInfoTableFields = document.querySelectorAll(
                'textarea[name="special_skills_hobbies[]"], textarea[name="non_academic_distinctions_recognition[]"], ' +
                'textarea[name="membership_in_association_organization[]"]'
            );
            otherInfoTableFields.forEach(field => {
                field.addEventListener('input', checkOtherInfoFields);
                field.addEventListener('change', checkOtherInfoFields);
            });

            // Next button gating
            const nextBtn = document.getElementById('pds3-next');
            const requiredFields = Array.from(document.querySelectorAll('[required]'));

            const isFilled = (el) => {
                if (el.type === 'file') return el.files && el.files.length > 0;
                if (el.type === 'checkbox' || el.type === 'radio') return el.checked;
                const val = (el.value || '').trim();
                if (isNA(val)) return true;
                return val !== '';
            };

            const firstRowSets = [voluntaryFirstRow, learningFirstRow, otherInfoFirstRow];

            const clearValidity = () => {
                requiredFields.forEach(el => el.setCustomValidity(''));
                firstRowSets.flat().forEach(f => f?.setCustomValidity(''));
                document.querySelectorAll('[name^="voluntary_"]').forEach(el => el.setCustomValidity(''));
                document.querySelectorAll('[name^="learning_"]').forEach(el => el.setCustomValidity(''));
                document.querySelectorAll('[name^="special_skills_hobbies"], [name^="non_academic_distinctions_recognition"], [name^="membership_in_association_organization"]').forEach(el => el.setCustomValidity(''));
            };

            const scrollToField = (el) => {
                if (!el) return;
                const nav = document.querySelector('nav');
                const navHeight = nav?.getBoundingClientRect().height || 80;
                const offset = navHeight + 540;
                const anchor = el.closest('td, th') || el;
                const targetY = Math.max(anchor.getBoundingClientRect().top + window.pageYOffset - offset, 0);
                window.scrollTo({ top: targetY, behavior: 'auto' });
            };

            const voluntaryRowNames = ['voluntary_organization[]','voluntary_from[]','voluntary_to[]','voluntary_hours[]','voluntary_position_nature_of_work[]'];
            const learningRowNames = ['learning_title_of_ld[]','learning_from[]','learning_to[]','learning_hours[]','learning_type_of_ld[]','learning_conducted_sponsored_by[]'];
            const otherInfoRowNames = ['special_skills_hobbies[]','non_academic_distinctions_recognition[]','membership_in_association_organization[]'];

            const enforceRowCompleteness = (names, optionalNames = new Set()) => {
                let invalidField = null;
                const columns = names.map(n => Array.from(document.querySelectorAll(`[name="${n}"]`)));
                const maxRows = Math.max(...columns.map(c => c.length));

                for (let row = 0; row < maxRows; row++) {
                    const rowFields = columns.map(c => c[row]).filter(Boolean);
                    if (!rowFields.length) continue;

                    const active = rowFields.filter(f => f && !f.disabled && !f.readOnly);
                    if (!active.length) continue;

                    // Check if first column has NA - if so, skip validation for this row
                    const firstColumnField = active.find(f => f.name === names[0]);
                    if (firstColumnField && isNA(firstColumnField.value)) {
                        continue; // Skip this row if first column is NA
                    }

                    const rowHasData = active.some(f => !optionalNames.has(f.name) && !isNA(f.value) && (f.value || '').trim() !== '');
                    if (!rowHasData) continue;

                    for (const f of active) {
                        if (optionalNames.has(f.name)) continue;
                        const val = (f.value || '').trim();
                        const filled = val !== '' || isNA(val);
                        if (!filled) {
                            f.setCustomValidity('Complete all fields in this row or clear the entries.');
                            if (!invalidField) invalidField = f;
                        }
                    }
                }

                return invalidField;
            };

            const validateRequired = () => {
                const hasMissingRequired = requiredFields.some(el => !el.disabled && !el.readOnly && !isFilled(el));

                const firstRowsIncomplete = firstRowSets.some(set => {
                    const state = firstRowState(set);
                    return state.incomplete || state.allBlank;
                });

                const incompleteVoluntary = enforceRowCompleteness(voluntaryRowNames);
                const incompleteLearning = enforceRowCompleteness(learningRowNames);
                const incompleteOther = enforceRowCompleteness(otherInfoRowNames);

                return hasMissingRequired || firstRowsIncomplete || incompleteVoluntary || incompleteLearning || incompleteOther;
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

                for (const set of firstRowSets) {
                    const state = firstRowState(set);
                    if (state.incomplete || state.allBlank) {
                        const missing = set.find(f => f && !isFilled(f));
                        const target = missing || set[0];
                        if (target) {
                            target.setCustomValidity('Please complete the first row or mark N/A.');
                            target.reportValidity();
                            target.focus();
                            scrollToField(target);
                            setTimeout(() => target.setCustomValidity(''), 1200);
                            return true;
                        }
                    }
                }

                const firstInvalid = enforceRowCompleteness(voluntaryRowNames) || enforceRowCompleteness(learningRowNames) || enforceRowCompleteness(otherInfoRowNames);
                if (firstInvalid) {
                    firstInvalid.reportValidity();
                    firstInvalid.focus();
                    scrollToField(firstInvalid);
                    setTimeout(() => firstInvalid.setCustomValidity(''), 1200);
                    return true;
                }

                return false;
            };

            document.addEventListener('input', () => { refreshRows(); clearValidity(); }, true);
            document.addEventListener('change', () => { refreshRows(); clearValidity(); }, true);

            if (nextBtn) {
                nextBtn.addEventListener('click', (e) => {
                    const hasMissing = validateRequired();
                    if (hasMissing) {
                        e.preventDefault();
                        e.stopPropagation();
                        focusFirstMissing();
                    }
                });
            }

            // Local cache + autosave/draft
            const storageKey = 'pds_form_step3_' + ({{ auth()->id() ?? 0 }});
            const singleSelectCheckboxNames = new Set();
            const storageBase = "{{ asset('storage') }}/";
            const initialSignaturePath = @json($signaturePath ?? '');

            const signaturePreviewImg3 = document.getElementById('signaturePreviewImg3');
            const signaturePlaceholder3 = document.getElementById('signaturePlaceholder3');
            const signatureBox3 = document.getElementById('signatureBox3');
            const signatureDataInput3 = document.getElementById('signature_data3');
            const signaturePathInput3 = document.getElementById('signature_path3');

            const buildSignatureUrl3 = (path) => {
                if (!path) return '';
                const cleaned = path.replace(/^public\//, '');
                return storageBase + cleaned;
            };

            const updateSignaturePreviewFromInputs3 = () => {
                if (!signaturePreviewImg3 || !signaturePlaceholder3) return;
                const dataUrl = signatureDataInput3?.value;
                const pathVal = signaturePathInput3?.value || initialSignaturePath;
                const url = dataUrl || buildSignatureUrl3(pathVal);
                if (url) {
                    signaturePreviewImg3.src = url;
                    signaturePreviewImg3.classList.remove('hidden');
                    signaturePlaceholder3.classList.add('hidden');
                    signatureBox3?.classList.add('signature-has-image');
                } else {
                    signaturePreviewImg3.classList.add('hidden');
                    signaturePlaceholder3.classList.remove('hidden');
                    signatureBox3?.classList.remove('signature-has-image');
                }
            };

            window.handleSignatureUpload3 = (file) => {
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
                        if (signatureDataInput3) signatureDataInput3.value = output;
                        if (signaturePathInput3) signaturePathInput3.value = '';
                        updateSignaturePreviewFromInputs3();
                    };
                    img.src = e.target.result;
                };
                reader.readAsDataURL(file);
            };

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
                if (overrideData?.signature_path && signaturePathInput3 && !signaturePathInput3.value) {
                    signaturePathInput3.value = overrideData.signature_path;
                }
                if (overrideData?.signature_data && signatureDataInput3 && !signatureDataInput3.value) {
                    signatureDataInput3.value = overrideData.signature_data;
                }
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
                updateSignaturePreviewFromInputs3();
            };

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

            const autosaveOverlay = document.getElementById('autosaveOverlay3');

            const autoSaveToServer = (() => {
                let timer;
                let failureCount = 0;
                const retryDelay = 1200;
                const maxRetries = 3;
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
                        if (response.status === 401 || response.status === 419) {
                            showOverlay(true);
                            throw new Error('Auto-save unauthorized');
                        }
                        if (!response.ok) {
                            showOverlay(true);
                            throw new Error('Auto-save failed');
                        }
                        return response.json().catch(() => {
                            throw new Error('Auto-save invalid JSON');
                        });
                    })
                    .then(data => {
                        const ok = data && data.status === 'ok';
                        if (ok) {
                            failureCount = 0;
                            showOverlay(false);
                        } else {
                            throw new Error('Auto-save response not ok');
                        }
                    })
                    .catch(() => {
                        showOverlay(true);
                        if (failureCount < maxRetries) {
                            failureCount += 1;
                            setTimeout(send, retryDelay);
                        }
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
            // Persist merged cache once so a fast refresh keeps latest values
            saveCache();

            fetch('{{ route('pds.draft', [], false) }}', { headers: { 'Accept': 'application/json' } })
                .then(r => r.ok ? r.json() : null)
                .then(json => {
                    if (!json || !json.data) return;
                    loadCache(json.data);
                    updateSignaturePreviewFromInputs3();
                    // Persist merged cache once so a fast refresh keeps latest values
                    saveCache();
                })
                .catch(() => {});

            form.addEventListener('input', () => { persist(); });
            form.addEventListener('change', () => { persist(); });
        });

        // Check for master date from form1 and apply it
        function ensureMasterDateSeed() {
            const date3Input = document.querySelector('input[name="date3"]');
            const existing = localStorage.getItem('pds_master_date');
            const candidate = existing || date3Input?.value;
            if (candidate && !existing) {
                console.log('Seeding master date from form3/input value:', candidate);
                localStorage.setItem('pds_master_date', candidate);
            }
            return candidate || null;
        }

        function syncFromForm1() {
            const masterDate = ensureMasterDateSeed();
            if (masterDate) {
                console.log('Using master date:', masterDate);
                const date3Input = document.querySelector('input[name="date3"]');
                if (date3Input && date3Input.value !== masterDate) {
                    console.log('Updating form3 date to:', masterDate);
                    date3Input.value = masterDate;
                    // Trigger change event to save to cache
                    date3Input.dispatchEvent(new Event('change', { bubbles: true }));
                }
            }
        }

        // Check for master date when page loads
        syncFromForm1();

        // Also check periodically in case user navigates back from form1
        setInterval(syncFromForm1, 1000);
    </script>
    <div class="max-w-6xl mx-auto p-4 font-serif text-sm">

    <table data-section="voluntary_work" class="border border-black w-full font-['Arial_Narrow','Arial',sans-serif]">

      <colgroup>
        <col style="width: 29.5%;">
        <col style="width: 5%;">
        <col style="width: 5%;">
        <col style="width: 5%;">
        <col style="width: 20%;">
      </colgroup>
      
    <th class="font-['Arial_Narrow','Arial',sans-serif] text-left bg-[#8a8a8a] text-white  italic text-xl px-2 border-2 border-black font-bold"colspan="5">
      VI. VOLUNTARY WORK OR INVOLVEMENT IN CIVIC / NON-GOVERNMENTAL / PEOPLE / VOLUNTARY ORGANIZATION 
    </th>

    <tr>
      <th class="border bg-[#e7e7e7]" rowspan="2">
        29. NAME & ADDRESS OF ORGANIZATION (Write in full)
      </th>

      <th class="border bg-[#e7e7e7]" colspan="2">
        INCLUSIVE DATES <p>(dd/mm/yyyy)</p>
      </th>


      <th class="border bg-[#e7e7e7]" rowspan="2">
        NUMBER OF <p>HOURS</p>
      </th>

      <th class="border bg-[#e7e7e7]" rowspan="2">
        POSITION / NATURE OF WORK 
      </th>
    </tr>


    <tr>
    <th class="border text-center bg-[#e7e7e7]">FROM</th>
    <th class="border text-center bg-[#e7e7e7]">TO</th>
   </tr>

   @for ($i = 0; $i < 7; $i++)
      <tr>
      <td class="border h-10"><textarea rows="1" placeholder="{{ $i === 0 ? 'Organization' : '' }}" name="voluntary_organization[]"></textarea></td>
      <td class="border h-10">
        <div class="h-full w-full">
          <input
            type="date"
            name="voluntary_from[]"
            class="w-full h-full text-lg resize-none
                   focus:outline-none focus:ring-0
                   bg-transparent text-center"
            style="font-size: 14px; padding:2px; border:none; box-sizing:border-box; margin:0; display: none;"
            placeholder="{{ $i === 0 ? 'From' : '' }}"/>
        </div>
      </td>
      <td class="border h-10">
        <div class="h-full w-full">
          <input
            type="date"
            name="voluntary_to[]"
            class="w-full h-full text-lg resize-none
                   focus:outline-none focus:ring-0
                   bg-transparent text-center"
            style="font-size: 14px; padding:2px; border:none; box-sizing:border-box; margin:0; display: none;"
            placeholder="{{ $i === 0 ? 'To' : '' }}"/>
        </div>
      </td>
      <td class="border h-10"><input type="number" placeholder="{{ $i === 0 ? 'Hours' : '' }}" name="voluntary_hours[]" class="w-full h-full text-lg resize-none focus:outline-none focus:ring-0 bg-transparent text-center" style="font-size: 14px; padding:4px; border:none; box-sizing:border-box; margin:0;" min="0"/></td>
      <td class="border h-10"><textarea rows="1" placeholder="{{ $i === 0 ? 'Position/Nature of Work' : '' }}" name="voluntary_position_nature_of_work[]"></textarea></td>
      </tr>
   @endfor

    </table>
    

    <table data-section="learning_development" class="border border-black font-['Arial_Narrow','Arial',sans-serif]">
      
      <colgroup>
        <col style="width: 45.5%;">
        <col style="width: 8%;">
        <col style="width: 7.5%;">
        <col style="width: 8%;">
        <col style="width: 12%;">
      </colgroup>

      <th class="font-['Arial_Narrow','Arial',sans-serif] text-left bg-[#8a8a8a] text-white  italic text-xl px-2 border-2 border-black font-bold" colspan="6">
      VII.  LEARNING AND DEVELOPMENT (L&D) INTERVENTIONS/TRAINING PROGRAMS ATTENDED
     </th>

     <tr class="border">

      <th rowspan="2" class="bg-[#e7e7e7] text-center">30. TITLE OF LEARNING AND DEVELOPMENT INTERVENTIONS/TRAINING PROGRAMS <p>(Write in full)</p></th>

      <th colspan="2" class="border bg-[#e7e7e7]">
        INCLUSIVE DATES OF ATTENDANCE<p>(dd/mm/yyyy)</p>
      </th>

       <th rowspan="2" class="border bg-[#e7e7e7]">
        NUMBER OF HOURS
      </th>

      <th rowspan="2" class="border bg-[#e7e7e7]">
        Type of L&D
     <p>(Managerial/ Supervisory/
        Technical / etc) </p>
      </th>

      <th rowspan="2" class="border bg-[#e7e7e7]">
         CONDUCTED/ SPONSORED BY (Write in full)
      </th>
     </tr>

     <tr>
      <th class="border text-center font-light bg-[#e7e7e7]">FROM</th>
      <th class="border text-center font-light bg-[#e7e7e7]">TO</th>
     </tr>

    @for ($i = 0; $i < 27; $i++)
     <tr>
      <td class="border h-10"><textarea rows="1" placeholder="{{ $i === 0 ? 'Title of L&D / Training' : '' }}" name="learning_title_of_ld[]"></textarea></td>
      <td class="border h-10">
        <div class="h-full w-full">
          <input
            type="date"
            name="learning_from[]"
            class="w-full h-full text-lg resize-none
                   focus:outline-none focus:ring-0
                   bg-transparent text-center"
            style="font-size: 14px; padding:2px; border:none; box-sizing:border-box; margin:0; display: none;"
            placeholder="{{ $i === 0 ? 'From' : '' }}"/>
        </div>
      </td>
      <td class="border h-10">
        <div class="h-full w-full">
          <input
            type="date"
            name="learning_to[]"
            class="w-full h-full text-lg resize-none
                   focus:outline-none focus:ring-0
                   bg-transparent text-center"
            style="font-size: 14px; padding:2px; border:none; box-sizing:border-box; margin:0; display: none;"
            placeholder="{{ $i === 0 ? 'To' : '' }}"/>
        </div>
      </td>
      <td class="border h-10"><input type="number" placeholder="{{ $i === 0 ? 'Hours' : '' }}" name="learning_hours[]" class="w-full h-full text-lg resize-none focus:outline-none focus:ring-0 bg-transparent text-center" style="font-size: 14px; padding:4px; border:none; box-sizing:border-box; margin:0;" min="0"/></td>
      <td class="border h-10"><textarea rows="1" placeholder="{{ $i === 0 ? 'Type of L&D' : '' }}" name="learning_type_of_ld[]"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="{{ $i === 0 ? 'Conducted/Sponsored By' : '' }}" name="learning_conducted_sponsored_by[]"></textarea></td>
     </tr>
    @endfor

    </table>

    <table data-section="other_information" class="border border-black w-full font-['Arial_Narrow','Arial',sans-serif]">

      <colgroup>
        <col style="width: 5.11%;">
         <col style="width: 6.5%;">
          <col style="width: 4.39%;">
      </colgroup>
      <th class="font-['Arial_Narrow','Arial',sans-serif] text-left bg-[#8a8a8a] text-white  italic text-xl px-2 border-2 border-black font-bold" colspan="3">
        VIII.  OTHER INFORMATION
      </th>

      <tr class=" bg-[#e7e7e7]">
        <th class="border">
        SPECIAL SKILLS and HOBBIES
      </th>

      <th class="border">
        NON-ACADEMIC DISTINCTIONS / RECOGNITION <p>(Write in full)</p>
      </th>

      <th class="border" >
          MEMBERSHIP IN ASSOCIATION / ORGANIZATION <p>(Write in full)</p>
      </th>

  
      </tr>

    @for ($i = 0; $i < 7; $i++)
      <tr>
      <td class="border h-10"><textarea rows="1" placeholder="{{ $i === 0 ? 'Special Skills and Hobbies' : '' }}" name="special_skills_hobbies[]"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="{{ $i === 0 ? 'Non-Academic Distinctions/Recognition' : '' }}" name="non_academic_distinctions_recognition[]"></textarea></td>
      <td class="border h-10"><textarea rows="1" placeholder="{{ $i === 0 ? 'Membership in Association/Organization' : '' }}" name="membership_in_association_organization[]"></textarea></td> 
      </tr>
    @endfor

    </table>

    <table class="border border-black w-full h-15 font-['Arial_Narrow','sans-serif']">
        <colgroup>
          <col style="width: 30.5%;">
           <col style="width: 20.5%;">
            <col style="width: 19.3%;">
             <col style="width: 9.5%;">
        </colgroup>
      <tr>
      <td class="border h-2 text-center text-xl font-bold italic align-middle">
      SIGNATURE
    </td>

       <td class="border" colspan="2">
     <div class="h-full w-full p-2">
        <label id="signatureBox3" data-signature-cell class="signature-box block h-36 w-full cursor-default">
          <input
            type="file"
            name="signature_file"
            id="signatureFileInput3"
            accept="image/*"
            class="absolute inset-0 w-full h-full opacity-0 cursor-not-allowed pointer-events-none"
            disabled
          >
          <img
            id="signaturePreviewImg3"
            src="{{ !empty($signaturePath) ? Storage::url($signaturePath) : '' }}"
            alt="Signature preview"
            class="absolute inset-0 w-full h-full object-contain {{ empty($signaturePath) ? 'hidden' : '' }}"
          >
          <div id="signaturePlaceholder3" class="absolute inset-0" aria-hidden="true"></div>
        </label>
        <input type="hidden" name="signature_path" id="signature_path3" value="{{ $signaturePath ?? '' }}">
        <input type="hidden" name="signature_data" id="signature_data3">
      </div>
    </td>


      <td class="border text-center text-xl font-bold italic align-middle" colspan="2">
      DATE
    </td>

        <td colspan="2"
          class="border h-10">
          <div class="h-full w-full flex items-center justify-center">
         <input
      type="date"
      name="date3"
      required
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             px-2 py-1 text-center bg-transparent border-none"
    ></input>
      </td>
      </tr>
    </table>

     <div class="flex justify-end mr-2 border-b-0 font-['Arial_Narrow','sans-serif']">
    CS FORM 212 (Revised 2025), Page 3 of 5
    </div>
    <div class="flex justify-between mt-4">
        <a id="pds3-prev" href="{{ route('pds.form2') }}" class="px-4 py-2 bg-blue-600 text-white rounded shadow border border-blue-700 hover:bg-blue-700 print:text-white print:bg-blue-600">Previous Page</a>
        <button type="submit" id="pds3-next" class="px-4 py-2 bg-blue-600 text-white rounded shadow border border-blue-700 hover:bg-blue-700 print:text-white print:bg-blue-600">Next Page</button>
    </div>
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

/* Hide calendar icon since date is synced from form1 */
input[type="date"]::-webkit-calendar-picker-indicator {
  display: none;
}

input[type="date"]::-moz-calendar-picker-indicator {
  display: none;
}

/* Ensure text is vertically centered and black */
input[type="date"]::-webkit-datetime-edit-text {
  vertical-align: middle;
  color: #000000;
  font-size: 16px;
  text-align: center !important;
}

input[type="date"]::-webkit-datetime-edit-month-field {
  vertical-align: middle;
  font-size: 16px;
  color: #000000;
  text-align: center !important;
}

input[type="date"]::-webkit-datetime-edit-day-field {
  vertical-align: middle;
  font-size: 16px;
  color: #000000;
  text-align: center !important;
}

input[type="date"]::-webkit-datetime-edit-year-field {
  vertical-align: middle;
  font-size: 16px;
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

/* Hide voluntary and learning date inputs by default */
input[name="voluntary_from[]"], input[name="voluntary_to[]"], 
input[name="learning_from[]"], input[name="learning_to[]"] {
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
  width: 20px;
  height: 20px;
  padding: 2px;
  border-radius: 2px;
}

input[type="date"].visible::-moz-calendar-picker-indicator {
  display: block !important;
  cursor: pointer;
  opacity: 1;
  filter: invert(35%) sepia(100%) saturate(1500%) hue-rotate(190deg) brightness(95%) contrast(95%) !important;
  -webkit-filter: invert(35%) sepia(100%) saturate(1500%) hue-rotate(190deg) brightness(95%) contrast(95%) !important;
  width: 20px;
  height: 20px;
  padding: 2px;
  border-radius: 2px;
}
</style>

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