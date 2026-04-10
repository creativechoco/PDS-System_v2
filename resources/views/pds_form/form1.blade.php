<x-app-layout>
 <link href="https://cdn.jsdelivr.net/npm/tom-select/dist/css/tom-select.css" rel="stylesheet">    
<div id="autosaveOverlay" class="autosave-overlay hidden">Saving…</div>
<form id="pds-form1" method="POST" action="{{ route('pds.saveStep', [1], false) }}" enctype="multipart/form-data">
    @csrf
    <style>
      textarea.no-uppercase {
        text-transform: none !important;
      }
      textarea[name="email_address"] {
        text-transform: none !important;
      }
      .no-uppercase, .no-uppercase * {
        text-transform: none !important;
      }
        /* Print-friendly, spreadsheet-like grid */
        table { border-collapse: collapse; width: 100%; }
        td, th { padding: 4px; vertical-align: top; }
        /* Only apply borders where classes already exist */
        .border { border: 1px solid #000 !important; }
        .border-2 { border: 2px solid #000 !important; }
        
        /* Prevent uppercase conversion for email field */
        .no-uppercase {
            text-transform: none !important;
        }
        .signature-box {
            position: relative;
            background: repeating-linear-gradient(45deg, #f5f5f5, #f5f5f5 10px, #e5e5e5 10px, #e5e5e5 20px);
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

        /* Form controls styled as lined cells */
        textarea { border: none; outline: none; padding: 8px; width: 100%; font: inherit; resize: none; background: transparent; line-height: 1.3; display: block; box-sizing: border-box; overflow: hidden; white-space: pre-wrap; word-break: break-word; min-height: 38px; height: auto; }
        textarea:focus { outline: none; box-shadow: none; }
        input[type="checkbox"] { width: 12px; height: 12px; }
        /* Prevent focused fields from hiding under sticky header when scrolled into view */
        input, textarea, select { scroll-margin-top: 240px; }
        .autosave-overlay { position: fixed; inset: 0; background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; z-index: 9999; font-size: 20px; font-weight: 700; color: #111; }
        .autosave-overlay.hidden { display: none; }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/tom-select/dist/js/tom-select.complete.min.js"></script>
   <script>
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('pds-form1');
    // Prefill from session cache (pds) so going back restores values
    const sessionData = @json(session('pds', []));
    const draftData = @json($data ?? []);
    // Shared cache key for all form1 scripts (avoid double-const redeclare across script tags)
    window.storageKey = window.storageKey || ('pds_form_step1_' + ({{ auth()->id() ?? 0 }}));
    const storageKey = window.storageKey;
    const triggerPersist = () => {
        if (typeof window.persist === 'function') {
            window.persist();
        } else if (typeof window.saveCache === 'function') {
            window.saveCache();
        }
    };

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

    // Draft data takes precedence, then session cache
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

    // Apply flattened data to fields
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

    // Ensure country is always set to Philippines by default
    const countryInput = document.querySelector('input[name="country"]');
    if (countryInput && !countryInput.value) {
        countryInput.value = 'Philippines';
        countryInput.dispatchEvent(new Event('change', { bubbles: true }));
    }

    // Force textarea input to uppercase (except email field)
    document.querySelectorAll('textarea:not([name="email_address"])').forEach(el => {
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

    // Helper: detect NA / N/A / NONE
    const isNA = (val) => {
        const v = (val || '').trim().toUpperCase();
        return v === 'NA' || v === 'N/A' || v === 'NONE';
    };

    // Convert email to lowercase when loaded from database
    const emailField = document.querySelector('textarea[name="email_address"]');
    if (emailField && emailField.value) {
        emailField.value = emailField.value.toLowerCase();
    }

    // Collect []-suffixed fields
    const names = new Set();
    document.querySelectorAll('input[name$="[]"], textarea[name$="[]"]').forEach(el => {
        const name = el.getAttribute('name');
        if (name) names.add(name);
    });

    // Include education rows (school_name) for per-row NA logic
    document.querySelectorAll('textarea[name*="[school_name]"]').forEach(el => {
        const name = el.getAttribute('name');
        if (name) names.add(name);
    });


    // Special handling: children rows
    const childrenNameFields = Array.from(document.querySelectorAll('textarea[name="children_familybg[]"]'));
    const childrenDobFields = Array.from(document.querySelectorAll('textarea[name="children_dateofbirth_familybg[]"]'));

    const refreshChildren = () => {
        const firstName = childrenNameFields[0];
        const firstDob = childrenDobFields[0];
        if (!firstName || !firstDob) return;

        // If first row is NA in both columns, lock down the rest (leave them blank) and keep first row as NA
        const firstIsNA = isNA(firstName.value) && isNA(firstDob.value);

        if (firstIsNA) {
            firstName.value = 'NA';
            firstDob.value = 'NA';
        }

        childrenNameFields.forEach((f, idx) => {
            const shouldDisable = firstIsNA && idx >= 1;
            if (shouldDisable) {
                f.value = '';
            }
            f.disabled = shouldDisable;
            f.readOnly = shouldDisable;
            f.classList.toggle('bg-gray-200', shouldDisable);
            f.classList.toggle('text-gray-500', shouldDisable);
            f.classList.toggle('cursor-not-allowed', shouldDisable);
            f.classList.toggle('pointer-events-none', shouldDisable);
        });

        childrenDobFields.forEach((f, idx) => {
            const shouldDisable = firstIsNA && idx >= 1;
            if (shouldDisable) {
                f.value = '';
            }
            f.disabled = shouldDisable;
            f.readOnly = shouldDisable;
            f.classList.toggle('bg-gray-200', shouldDisable);
            f.classList.toggle('text-gray-500', shouldDisable);
            f.classList.toggle('cursor-not-allowed', shouldDisable);
            f.classList.toggle('pointer-events-none', shouldDisable);
        });
    };

    childrenNameFields.forEach(f => f.addEventListener('input', refreshChildren));
    childrenDobFields.forEach(f => f.addEventListener('input', refreshChildren));
    refreshChildren();

    // Handle all other [] and education fields
    names.forEach(name => {
        if (name === 'children_familybg[]' || name === 'children_dateofbirth_familybg[]') return;

        const selectorName = name.replace(/["'\\]/g, '\\$&');
        const fields = Array.from(document.querySelectorAll(`input[name="${selectorName}"], textarea[name="${selectorName}"]`));
        if (!fields.length) return;

        const refreshArray = () => {
            const firstNAIndex = fields.findIndex(f => isNA(f.value));
            fields.forEach((f, idx) => {
                const shouldDisable = firstNAIndex !== -1 && idx > firstNAIndex;
                f.disabled = shouldDisable;
                f.classList.toggle('bg-gray-200', shouldDisable);
                f.classList.toggle('text-gray-500', shouldDisable);
                f.classList.toggle('cursor-not-allowed', shouldDisable);
            });
        };

        // Education row special handling
        if (selectorName.includes('education[') && selectorName.endsWith('[school_name]')) {
            const schoolFields = fields;
            const rowSelectors = ['[basic_education]', '[from]', '[to]', '[highest_level]', '[year_graduated]', '[scholarship_acadhonors]'];

            const refreshRow = () => {
                schoolFields.forEach(schoolField => {
                    const isRowNA = isNA(schoolField.value);
                    const rowPrefix = schoolField.name.replace(/\[school_name\]$/, '');
                    rowSelectors.forEach(sel => {
                        const targetName = `${rowPrefix}${sel}`;
                        const targets = document.querySelectorAll(`textarea[name="${targetName}"], input[name="${targetName}"]`);
                        targets.forEach(target => {
                            target.disabled = isRowNA;
                            target.readOnly = isRowNA;
                            target.classList.toggle('bg-gray-200', isRowNA);
                            target.classList.toggle('text-gray-500', isRowNA);
                            target.classList.toggle('cursor-not-allowed', isRowNA);
                            target.classList.toggle('pointer-events-none', isRowNA);
                        });
                    });
                });
            };

            schoolFields.forEach(f => f.addEventListener('input', refreshRow));
            refreshRow();
            return;
        }

        fields.forEach(f => f.addEventListener('input', refreshArray));
        refreshArray();
    });

    // Next button validation (required fields + first-row completeness for array tables)
    const nextBtn = document.getElementById('next-btn');
    const requiredFields = Array.from(document.querySelectorAll('input[required], textarea[required], select[required]'));
    const checkboxGroups = ['sex[]', 'civilstatus[]', 'citizenship[]'];

    // First-row completeness: children table (first row must be fully filled or all N/A)
    const firstRowFields = [
        document.querySelector('textarea[name="children_familybg[]"]'),
        document.querySelector('textarea[name="children_dateofbirth_familybg[]"]')
    ].filter(Boolean);

    const firstRowState = (fields) => {
        const values = fields.map(f => (f?.value || '').trim());
        const allNA = values.length && values.every(v => isNA(v));
        const hasBlank = values.some(v => v === '');
        const anyNA = values.some(v => v !== '' && isNA(v));
        const anyRealData = values.some(v => v !== '' && !isNA(v));
        const allFilled = values.length > 0 && !hasBlank;
        const validReal = allFilled && anyRealData && !anyNA;
        return { allNA, hasBlank, validReal };
    };

    const scrollToField = (el) => {
        if (!el) return;
        const nav = document.querySelector('nav');
        const navHeight = nav?.getBoundingClientRect().height || 80;
        const offset = navHeight + 540; // generous buffer so field lands well below navbar
        const anchor = el.closest('td, th') || el; // use table cell as anchor when possible

        const applyOffset = () => {
            const targetY = Math.max(anchor.getBoundingClientRect().top + window.pageYOffset - offset, 0);
            window.scrollTo({ top: targetY, behavior: 'auto' });
        };

        // Immediate scroll, then re-apply for stability
        applyOffset();
        requestAnimationFrame(applyOffset);
        setTimeout(applyOffset, 140);
        setTimeout(applyOffset, 300);
    };

    const findMissingChildDob = () => {
        for (let i = 0; i < childrenNameFields.length; i++) {
            const nameField = childrenNameFields[i];
            const dobField = childrenDobFields[i];
            if (!nameField || !dobField) continue;

            const disabled = nameField.disabled || dobField.disabled || nameField.readOnly || dobField.readOnly;
            if (disabled) {
                dobField.setCustomValidity('');
                continue;
            }

            const nameVal = (nameField.value || '').trim();
            const dobVal = (dobField.value || '').trim();
            const hasName = nameVal !== '' && !isNA(nameVal);
            const dobBlank = dobVal === '';

            if (hasName && dobBlank) {
                dobField.setCustomValidity('Date of birth is required for this child.');
                return dobField;
            }

            dobField.setCustomValidity('');
        }
        return null;
    };

    const validateRequired = () => {
        const missingRequiredInputs = requiredFields.some(el => {
            if (el.disabled || el.readOnly) return false;
            if (el.type === 'file') return !(el.files && el.files.length > 0);
            return !((el.value || '').trim());
        });

        const missingCheckboxGroup = checkboxGroups.some(name => {
            const boxes = Array.from(document.querySelectorAll(`input[type="checkbox"][name="${name}"]`));
            if (!boxes.length) return false;
            const noneChecked = !boxes.some(b => b.checked);

            // Surface native message and clear it for the whole group
            boxes.forEach(box => box.setCustomValidity(noneChecked ? 'Please select an option in this group.' : ''));

            return noneChecked;
        });

        const missingChildDobField = findMissingChildDob();

        const firstRowStateResult = firstRowState(firstRowFields);
        const firstRowIncomplete = !(firstRowStateResult.allNA || firstRowStateResult.validReal);

        return missingRequiredInputs || missingCheckboxGroup || firstRowIncomplete || !!missingChildDobField;
    };

    const focusFirstMissing = () => {
        for (const el of requiredFields) {
            if (el.disabled || el.readOnly) continue;
            if (el.type === 'file') {
                if (!(el.files && el.files.length > 0)) {
                    el.focus();
                    scrollToField(el);
                    return true;
                }
                continue;
            }
            if (!((el.value || '').trim())) {
                el.focus();
                scrollToField(el);
                return true;
            }
        }

        for (const name of checkboxGroups) {
            const boxes = Array.from(document.querySelectorAll(`input[type="checkbox"][name="${name}"]`));
            if (!boxes.length) continue;
            if (!boxes.some(b => b.checked)) {
                boxes[0].focus();
                scrollToField(boxes[0]);
                return true;
            }
        }

        const missingChildDobField = findMissingChildDob();
        if (missingChildDobField) {
            missingChildDobField.reportValidity();
            missingChildDobField.focus();
            scrollToField(missingChildDobField);
            setTimeout(() => missingChildDobField.setCustomValidity(''), 1200);
            return true;
        }

        const firstState = firstRowState(firstRowFields);
        if (!(firstState.allNA || firstState.validReal)) {
            const firstMissing = firstRowFields.find(f => f && !(f.value || '').trim());
            const target = firstMissing || firstRowFields[0];
            if (target) {
                target.setCustomValidity('Please fill in the first child row or mark both fields N/A.');
                target.reportValidity();
                target.focus();
                scrollToField(target);
                setTimeout(() => target.setCustomValidity(''), 1200);
            }
            return true;
        }

        return false;
    };

    requiredFields.forEach(el => {
        el.addEventListener('input', validateRequired);
        el.addEventListener('change', validateRequired);
    });

    checkboxGroups.forEach(name => {
        document.querySelectorAll(`input[type="checkbox"][name="${name}"]`).forEach(box => {
            box.addEventListener('change', validateRequired);
        });
    });

    validateRequired();

    if (form && nextBtn) {
        form.addEventListener('submit', (e) => {
            // Ensure country is set on submit
            if (countryInput && !countryInput.value) {
                countryInput.value = 'Philippines';
            }

            const middleNameInput = document.getElementById('middlename');
            if (middleNameInput && !middleNameInput.value.trim()) {
                e.preventDefault();
                e.stopPropagation();
                alert('Middle name is required.');
                middleNameInput.focus();
                scrollToField(middleNameInput);
                return;
            }

            const hasMissing = validateRequired();
            if (hasMissing) {
                e.preventDefault();
                e.stopPropagation();

                // Trigger native validity UI for checkbox groups
                for (const name of checkboxGroups) {
                    const boxes = Array.from(document.querySelectorAll(`input[type="checkbox"][name="${name}"]`));
                    if (boxes.length && !boxes.some(b => b.checked)) {
                        boxes[0].reportValidity();
                        break;
                    }
                }

                focusFirstMissing();
            }
        });
    }

});
</script>
    <div class="max-w-6xl mx-auto p-4 font-serif text-sm">
  <!-- HEADER -->
  <header class="mb-4 flex items-start justify-between gap-4">
    <div class="text-sm font-bold italic font-['Arial_Narrow','sans-serif']">CS Form No. 212
      <br>
    <span class="font-light text-sm italic font-['Arial_Narrow','sans-serif']">Revised 2025</span>
    </div>
    <h1 class="font-extrabold text-4xl text-center mb-4 font-['Arial_Black','sans-serif'] flex-1">
      PERSONAL DATA SHEET
    </h1>
  </header>

  <p class=" font-['Arial','sans-serif'] text-base italic font-bold mb-1">
    WARNING: Any misrepresentation made in the Personal Data Sheet shall cause the filing of administrative/criminal case/s.
  </p>
  <p class=" font-['Arial','sans-serif'] text-base text-s italic font-bold mb-2">
    READ THE ATTACHED GUIDE TO FILLING OUT THE PERSONAL DATA SHEET (PDS) BEFORE ACCOMPLISHING THE PDS FORM.
  </p>

  <p class="  font-['Arial_Narrow','sans-serif'] text-base mb-3">
    Print legibly if accomplished through own handwriting. Tick appropriate boxes <span style="font-style:normal;">&#x2610;</span> and use separate sheet if necessary. Indicate <span class="font-bold">N/A</span> if not applicable. <span class="font-bold">DO NOT ABBREVIATE.</span>
  </p>

  <!-- MAIN TABLE -->
  <table data-section="personal_information" class="align-middle w-full border border-black  table-fixed  font-['Arial_Narrow','sans-serif'] text-base w-[100%]" >

    <!-- FIXED GRID -->
    <colgroup>
      <col style="width:8%">
      <col style="width:9%">
      <col style="width:10%">
      <col style="width:13%">
    </colgroup>

    <!-- SECTION HEADER -->
    <tr>
      <td colspan="4" class="font-['Arial_Narrow','Arial',sans-serif] bg-[#8a8a8a] text-white  italic text-xl px-2 border-2 border-black font-bold">
        I. PERSONAL INFORMATION
      </td>
    </tr>

    <!-- SURNAME -->
    <tr>
      <td class="bg-[#e7e7e7] px-2 align-middle">
        1.  SURNAME
     </td>
          <td class="border relative" colspan="3" style="height: 60px;">
            <div class="absolute inset-0 flex items-center">
    <textarea
      name="surname"
      id="surname"
      required
      rows="1"
      class="w-full px-2 text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden"
      placeholder="Enter Surname"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
  </div>
</td>
    </tr>

    <!-- FIRST NAME + EXTENSION -->
    <tr>
      <td class="bg-[#e7e7e7]  px-2 align-middle">
        2. FIRST NAME
      </td>
       <td class="border relative" colspan="2" style="height: 60px;">
            <div class="absolute inset-0 flex items-center">
    <textarea
      name="firstname"
      id="firstname"
      rows="1"
      required
      class="w-full px-2 text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden"
      placeholder="Enter Firstname"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
  </div>
</td>

      <td class="bg-[#e7e7e7] align-top border">
        <span class="italic text-xs px-2">NAME EXTENSION (JR., SR)</span>
        <div
          class="h-full w-full
           min-h-full
           wrap-break-words whitespace-normal
           outline-none
           text-lg">
           <textarea
      name="employee_name_extension"
      id="name_extension"
      rows="1"
      class="w-full px-2 text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden"
      placeholder="(Optional)"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </div>
      </td>
    </tr>

    <!-- MIDDLE NAME -->
    <tr>
      <td class="bg-[#e7e7e7] align-middle px-5 border-b-2"> 
        MIDDLE NAME
      </td>
      <td colspan="3" class="border border-b-2 h-10 align-middle">
        <div  class="h-full w-full
           min-h-full
           wrap-break-words whitespace-normal
           outline-none
           text-lg">
           <textarea
      name="middlename"
      id="middlename"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-2"
      placeholder="Enter Middle Name"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
        </div>
      </td>
    </tr>

    <!-- DATE OF BIRTH + CITIZENSHIP -->
    <tr>
      <td class="bg-[#e7e7e7] px-2 align-middle border">
    3. DATE OF BIRTH
    <p class="text-base font-normal ml-6">(dd/mm/yyyy)</p>
  </td>

  <td class="border h-10">
    <div class="h-full w-full min-h-full p-0">
      <input 
        type="date"
        name="date_of_birth"
        required
        class="block w-full h-full text-medium
             focus:outline-none focus:ring-0
             bg-transparent"
        placeholder="dd/mm/yyyy"
        autocomplete="off"
        style="font-size:23px; padding:8px; border:none; box-sizing:border-box; margin:0;" />
    </div>
  </td>
      <td rowspan="3" class="bg-[#e7e7e7] px-2 align-top border-l-5">
        16. CITIZENSHIP
        <p class="text-base mt-8 text-center">
          If holder of dual citizenship, <br>
          please indicate the details.
        </p>
      </td>



      <td rowspan="3" class="border px-2 align-top text-base">

        <div class="flex flex-col items-center text-center w-full space-y-1">
          <div class="flex flex-wrap justify-center gap-6 mr-20">
            <label class="inline-flex items-center gap-2"><input type="checkbox" class="mt-1 mb-1" name="citizenship[]" value="filipino"> Filipino</label>
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="citizenship[]" value="dual_citizenship"> Dual Citizenship</label>
          </div>
          <div class="flex flex-wrap justify-center gap-6 ml-20">
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="citizenship[]" value="by_birth"> by birth</label>
            <label class="inline-flex items-center gap-2"><input type="checkbox" name="citizenship[]" value="by_naturalization"> by naturalization</label>
          </div>
           <p class="py-3 flex justify-center">Pls. indicate country:</p>
        <div class="border mb-2 mt-1 w-full text-center" style="min-height: 38px;">
          <span class="text-2xl font-['Arial_Narrow','Arial',sans-serif]">Philippines</span>
          <input type="hidden" name="country" value="Philippines" required>
        </div>
        </div>
      </td>
    </tr>

    <!-- PLACE OF BIRTH -->
    <tr>
      <td class="bg-[#e7e7e7] px-2 border align-middle">
        4. PLACE OF BIRTH
      </td>
      <td class="border px-2 h-10">
        <div 
          class="h-full w-full
           min-h-full
           wrap-break-words whitespace-normal
           outline-none
            py-2
           text-lg">
    <textarea
      name="place_of_birth"
      id="place_of_birth"
      required
      rows="1"
      class="w-full px-2 text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden"
      placeholder="Enter Place of Birth"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </div>
      </td>
    </tr>

    <!-- SEX AT BIRTH -->
    <tr>
      <td class="bg-[#e7e7e7] align-middle px-2 border">
        5. SEX AT BIRTH
      </td>
      <td class="border px-2 text-base">
        <label class="mr-10 ml-2">
          <input type="checkbox" name="sex[]" value="male"> Male
        </label>
        <label>
          <input type="checkbox" name="sex[]" value="female" class="ml-7"> Female
        </label>
      </td>
    </tr>

    <!-- SIMPLE ROW TEMPLATE -->
      <td class="bg-[#e7e7e7] align-top py-5 px-2 border">6. CIVIL STATUS</td>
      <td class="border px-2 py-1 align-middle h-2">
    <div class="p-2">
      <div class="grid grid-cols-[100px_120px] gap-y-2 gap-x-4 text-base ">
        
        <label class="flex items-center gap-2">
          <input type="checkbox" name="civilstatus[]" value="single" class="w-3 h-3">
          Single
        </label>

        <label class="flex items-center gap-2">
          <input type="checkbox" name="civilstatus[]" value="married" class="w-3 h-3">
          Married
        </label>

        <label class="flex items-center gap-2">
          <input type="checkbox" name="civilstatus[]" value="widowed" class="w-3 h-3">
          Widowed
        </label>

        <label class="flex items-center gap-2">
          <input type="checkbox" name="civilstatus[]" value="separated" class="w-3 h-3">
          Separated
        </label>

        <label class="flex items-center gap-2 col-span-2">
          <input type="checkbox" name="civilstatus[]" value="other/s" class="w-3 h-3">
          Other/s:
        </label>

      </div>
    </div>
  </td>


  <td rowspan="3" colspan="2"
      class="border p-0 align-top bg-[#e7e7e7]">

    <div class="flex w-full h-full">
      
      <!-- LEFT TABLE -->
      <table class="bg-[#e7e7e7]" style="width:35%;">
        <tr>
          <td class="px-2 py-1 align-top border-black border-r">
            17. RESIDENTIAL ADDRESS
          </td>
        </tr>
        <tr class="h-10">
          <td class="text-center text-xl py-2 border-r border-black">
            ZIP CODE
          </td>
        </tr>
      </table>

      <!-- RIGHT TABLE -->
      <table class="bg-white" style="width:65%; border-collapse:collapse;">

          <tr>
            <td class="h-auto align-top">
              <div class="flex mt-2 w-full gap-2">
                <textarea required rows="1"
                  class="w-25 px-2 py-2 text-lg resize-none bg-transparent border-none outline-none text-center whitespace-pre-wrap overflow-hidden"
                  name="house_block_lot"
                  placeholder="House/Block/Lot No."
                  oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"></textarea>
                <textarea required rows="1"
                  class="w-35 px-2 py-2 text-lg resize-none bg-transparent border-none outline-none text-center whitespace-pre-wrap overflow-hidden"
                  placeholder="Street"
                  name="street"
                  oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"></textarea>
              </div>
            </td>
          </tr>
          
          <tr class="h-2">
          <td class="border-t border-black/50">
            <div class="flex items-center justify-between px-4 gap-4">
              <p class="flex-1 text-center">House/Block/Lot No.</p>
              <p class="flex-1 text-center ml-5">Street</p>
            </div>
          </td>
        </tr>
<tr><td class="border-t border-black"></td></tr>
           <tr>
            <td class="h-auto align-top">
              <div class="flex mt-2 w-full gap-2">
                <textarea required rows="1"
                  class="w-25 px-2 py-2 text-lg resize-none bg-transparent border-none outline-none text-center whitespace-pre-wrap overflow-hidden"
                  name="subdivision_village"
                  placeholder="Subdivision/Village"
                  oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"></textarea>
                <textarea required rows="1"
                  class="w-35 px-2 py-2 text-lg resize-none bg-transparent border-none outline-none text-center whitespace-pre-wrap overflow-hidden"
                  name="baranggay"
                  placeholder="Barangay"
                  oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"></textarea>
              </div>
            </td>
          </tr>
          <tr class="h-2">
          <td class="border-t border-black/50 ">
            <div class="flex items-center justify-between px-4 gap-4">
              <p class="flex-1 text-center">Subdivision/Village</p>
              <p class="flex-1 text-center ml-5">Baranggay</p>
            </div>
          </td>
        </tr>
        <tr><td class="border-t border-black"></td></tr>
          <tr>
            <td class="h-auto align-top">
              <div class="flex mt-2 w-full gap-2">
                <textarea required rows="1"
                  class="w-25 px-2 py-2 text-lg resize-none bg-transparent border-none outline-none text-center whitespace-pre-wrap overflow-hidden"
                  name="city_municipality"
                  placeholder="City/Municipality"
                  oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"></textarea>
                <textarea required rows="1"
                  class="w-35 px-2 py-2 text-lg resize-none bg-transparent border-none outline-none text-center whitespace-pre-wrap overflow-hidden"
                  name="province"
                  placeholder="Province"
                  oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"></textarea>
              </div>
            </td>
          </tr>

          <tr >
          <td class="border-t border-black/50">
            <div class="flex items-center justify-between px-4 gap-4">
              <p class="flex-1 text-center">City/Municipality</p>
              <p class="flex-1 text-center ml-4">Province</p>
            </div>
          </td>
        </tr>
        <tr class="h-2">
          <td>
            <div class="flex">
            </div>
          </td>
        </tr>
        <tr class="h-2">
          <td class="border-t border-black">
            <div>
            
            </div>
          </td>
        </tr>

        <tr class="h-2">
          <td>
            <div>
            
            </div>
          </td>
        </tr>

        
        <tr class="h-2">
          <td>
             <div class="border-none h-8 mb-2 mt-1 text-center">
          <textarea
      name="zip_code"
      required
      rows="1"
      class="w-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden text-center align-top"
      placeholder="Enter Zip Code"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
        </div>
          </td>
        </tr>
      </table>

    </div>
  </td>




    <tr>
      <td class="bg-[#e7e7e7] px-2 border font-['Arial_Narrow','Arial',sans-serif]">7. HEIGHT (m)</td>
      <td class="border px-2 h-10">
        <textarea
      name="height"
      required
      rows="1"
      class="w-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden align-top"
      placeholder="Enter Height"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>
    </tr>

    <tr>
      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border">8. WEIGHT (kg)</td>
      <td class="border px-2 h-10">
        <textarea
      name="weight"
      required
      rows="1"
      class="w-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden align-top"
      placeholder="Enter Weight"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>
    </tr>


      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border h-8">9. BLOOD TYPE</td>
      <td class="border px-2 align-middle"> 
        <textarea
      name="blood_type"
      required
      rows="1"
      class="w-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden align-top"
      placeholder="Enter Blood Type"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>


  <td rowspan="4" colspan="2"
      class="border p-0 align-top bg-[#e7e7e7]">

    <div class="flex w-full h-full">
      
      <!-- LEFT TABLE -->
      <table class="bg-[#e7e7e7] border-b font-['Arial_Narrow','Arial',sans-serif] text-base" style="width:35%;">
        <tr>
          <td class="px-2 py-1 align-top border-r border-black">
            18. PERMANENT ADDRESS
            <button type="button" 
                    onclick="copyResidentialToPermanent()" 
                    class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-1 px-2 rounded transition-colors duration-200 mt-10 text-center"
                    style="font-size: 15px; white-space: nowrap; display: block; width: fit-content; margin-left: auto; margin-right: auto;">
              Same as Above
            </button>
          </td>
        </tr>
      </table>

      <!-- RIGHT TABLE -->
      <table class="bg-white" style="width:65%; border-collapse:collapse;">

            <tr>
            <td class="h-auto align-top">
              <div class="flex mt-2 w-full gap-2">
                <textarea required rows="1"
                  class="w-25 px-2 py-2 text-lg resize-none bg-transparent border-none outline-none text-center whitespace-pre-wrap overflow-hidden"
                  name="permanent_house_block_lot"
                  placeholder="House/Block/Lot No."
                  oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"></textarea>
                <textarea required rows="1"
                  class="w-35 px-2 py-2 text-lg resize-none bg-transparent border-none outline-none text-center whitespace-pre-wrap overflow-hidden"
                  name="permanent_street"
                  placeholder="Street"
                  oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"></textarea>
              </div>
            </td>
          </tr>
          
          <tr class="h-2">
          <td class="border-t border-black/50">
            <div class="flex items-center justify-between px-4 gap-4">
              <p class="flex-1 text-center">House/Block/Lot No.</p>
              <p class="flex-1 text-center ml-5">Street</p>
            </div>
          </td>
        </tr>
<tr><td class="border-t border-black"></td></tr>
           <tr>
            <td class="h-auto align-top">
              <div class="flex mt-2 w-full gap-2">
                <textarea required rows="1"
                  class="w-25 px-2 py-2 text-lg resize-none bg-transparent border-none outline-none text-center whitespace-pre-wrap overflow-hidden"
                  name="permanent_subdivision_village"
                  placeholder="Subdivision/Village"
                  oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"></textarea>
                <textarea required rows="1"
                  class="w-35 px-2 py-2 text-lg resize-none bg-transparent border-none outline-none text-center whitespace-pre-wrap overflow-hidden"
                  name="permanent_baranggay"
                  placeholder="Baranggay"
                  oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"></textarea>
              </div>
            </td>
          </tr>
          <tr class="h-2">
          <td class="border-t border-black/50 ">
            <div class="flex items-center justify-between px-4 gap-4">
              <p class="flex-1 text-center">Subdivision/Village</p>
              <p class="flex-1 text-center ml-5">Baranggay</p>
            </div>
          </td>
        </tr>
        <tr><td class="border-t border-black"></td></tr>
          <tr>
            <td class="h-auto align-top">
              <div class="flex mt-2 w-full gap-2">
                <textarea required rows="1"
                  class="w-25 px-2 py-2 text-lg resize-none bg-transparent border-none outline-none text-center whitespace-pre-wrap overflow-hidden"
                  name="permanent_city_municipality"
                  placeholder="City/Municipality"
                  oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"></textarea>
                <textarea required rows="1"
                  class="w-35 px-2 py-2 text-lg resize-none bg-transparent border-none outline-none text-center whitespace-pre-wrap overflow-hidden"
                  name="permanent_province"
                  placeholder="Province"
                  oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"></textarea>
              </div>
            </td>
          </tr>

          <tr >
          <td class="border-t border-black/50">
            <div class="flex">
              <p class="text-center w-full">City/Municipality</p>
              <p class="text-center w-full ml-4">Province</p>
            </div>
          </td>
        </tr>
        <tr class="h-10">
          <td class="border-t border-black">
            <div>
            
            </div>
          </td>
        </tr>
      </table>

    </div>
  </td>    

  <tr>
      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border">10. UMID ID NO.</td>
      <td class="border px-2 h-10">

        <textarea
      name="umid_id_no"
      required
      rows="1"
      class="w-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden align-top"
      placeholder="Enter UMID ID NO."
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>
    </tr>

    <tr>
      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border">11. PAG-IBIG ID NO.</td>
      <td class="border px-2 h-10">
        <textarea
      name="pagibig_id_no"
      required
      rows="1"
      class="w-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden align-top"
      placeholder="Enter PAGIBIG ID NO."
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>
    </tr>

    <tr>
      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border">12. PHILHEALTH NO.</td>
      <td class="border px-2 h-10">
        <textarea
      name="philhealth_no"
      required
      rows="1"
      class="w-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden align-top"
      placeholder="Enter PHILHEALTH NO."
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>
    </tr>

    
    <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif]  px-2 border h-8">13. PhilSys Number (PSN):</td>
      <td class="border px-2 align-middle">
          <textarea
      name="philsys_no"
      required
      rows="1"
      class="w-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden align-top"
      placeholder="Enter PhilSys Number (PSN)"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>
    
  
    <td rowspan="1" colspan="2"
      class="border p-0 align-top bg-[#e7e7e7]">

    <div class="flex w-full h-full">
      
      <!-- LEFT TABLE -->
      <table class="border-l bg-[#e7e7e7] border-b-0 border-r-0 h-10 font-['Arial_Narrow','Arial',sans-serif]" style="width: 53.4%;">

        <tr>
          <td class="px-2 align-middle w-full">
            19. TELEPHONE NO.
          </td>
        </tr>
      </table>

      <!-- RIGHT TABLE -->
      <table class="bg-white w-300 border-l border-r border-t-0">
          <tr class="h-5">
            <td class="border-black border-l">
              <div class="flex">
             <textarea
      name="telephone_no"
      required
      rows="1"
      class="w-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden align-top px-2"
      placeholder="Enter Telephone NO."
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
            </div>
            </td>
          </tr>
      </table>
    </div>
  </td>
       
    <tr>
      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border">14. TIN ID</td>
      <td class="border px-2 h-10">
        <textarea
      name="tin_no"
      required
      rows="1"
      class="w-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden align-top"
      placeholder="Enter TIN ID"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

       <td rowspan="1" colspan="2"
      class="border p-0 align-top bg-[#e7e7e7]">

    <div class="flex w-full h-full">
      
      <!-- LEFT TABLE -->
      <table class=" border-l bg-[#e7e7e7] border-b-0 border-r-0 h-10 font-['Arial_Narrow','Arial',sans-serif]" style="width: 53.4%;">
        <tr>
          <td class="px-2 py-1 align-middle">
            20. MOBILE NO.
          </td>
        </tr>
      </table>

      <!-- RIGHT TABLE -->
      <table class="bg-white w-300 border-l border-r border-t-0">
          <tr class="h-5">
            <td class="border-black border-l">
              <div class="flex">
            <textarea
      name="mobile_no"
      required
      rows="1"
      class="w-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden align-top px-2"
      placeholder="Enter Mobile NO."
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
            </div>
            </td>
          </tr>
      </table>
    </div>
  </td>
    </tr>

    <tr>
      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border">15. AGENCY EMPLOYEE ID</td>
      <td class="border px-2 h-10">
         <textarea
      name="agency_employee_no"
      required
      rows="1"
      class="w-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden align-top"
      placeholder="Enter Agency Employee ID"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

       <td rowspan="1" colspan="2"
      class="border p-0 align-top bg-[#e7e7e7]">

    <div class="flex w-full h-full">
      
      <!-- LEFT TABLE -->
      <table class="border-l  bg-[#e7e7e7] border-b-0 border-r-0 h-10 font-['Arial_Narrow','Arial',sans-serif]" style="width: 53.4%;">
        <tr>
          <td class="px-2 align-middle">
           21. E-MAIL ADDRESS (if any)
          </td>
        </tr>
      </table>

      <!-- RIGHT TABLE -->
      <table class="bg-white w-300 border-l border-r border-t-0 h-10">
          <tr class="h-2">
            <td class="border-black border-l">
              <div>
             <textarea
      name="email_address"
      required
      rows="1"
      class="w-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden align-top px-2 no-uppercase"
      placeholder="Enter Email Address"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px'; this.value = this.value;"
      style="text-transform: none !important;"
    ></textarea>
            </div>
            </td>
          </tr>
      </table>
    </div>
  </td>

      
    </tr>

  </table>


<<<<<<< HEAD
   <table class="w-full border border-black border-collapse table-fixed font-['Arial_Narrow','sans-serif'] text-base">
=======
>>>>>>> dc9d6034f6ba41402addb6c20f822c4de336e6f4
   <table data-section="family_background" class="w-full border border-black border-collapse table-fixed font-['Arial_Narrow','sans-serif'] text-base">
  <style>
    /* Show education add buttons only on hover */
    tr[data-education-base] .edu-add-btn {
      opacity: 0;
      pointer-events: none;
      transition: opacity 0.15s ease;
    }
    tr.row-add-hover .edu-add-btn {
      opacity: 1;
      pointer-events: auto;
    }

    /* Emphasize add area with a bottom highlight on hover */
    tr[data-education-base] td.add-btn-cell.has-add-btn {
      position: relative;
      overflow: visible;
    }
    tr[data-education-base] td.add-btn-cell.has-add-btn::after {
      content: '';
      position: absolute;
      left: 0;
      right: 0;
      bottom: -1px;
      height: 3px;
      background: #059669;
      opacity: 0;
      transition: opacity 0.15s ease;
      pointer-events: none;
    }
    tr.row-add-hover td.add-btn-cell.has-add-btn::after {
      opacity: 1;
    }

    /* Extend hover highlight across the full row bottom border */
    tr.row-add-hover td {
      box-shadow: inset 0 -2px 0 #059669;
      border-bottom-color: #059669;
    }
  </style>
<<<<<<< HEAD
=======
   <table class="w-full border border-black border-collapse table-fixed font-['Arial_Narrow','sans-serif'] text-base">
>>>>>>> dc9d6034f6ba41402addb6c20f822c4de336e6f4

    <!-- FIXED GRID -->
    <colgroup>
      <col style="width:20%">
      <col style="width:10%">
      <col style="width:12%">
      <col style="width: 16%">
    </colgroup>

    <!-- SECTION HEADER -->
    <tr>
      <td colspan="6" class="font-['Arial_Narrow','Arial',sans-serif] font-bold bg-[#8a8a8a] text-white  italic text-xl px-2 border-2 border-black border-t">
        II. FAMILY BACKGROUND
      </td>
    </tr>


     <tr>
      <td class="bg-[#e7e7e7] px-2 align-middle">
        22. SPOUSE'S SURNAME
      </td>
      <td colspan="3"
          class="border">
           <textarea
      name="spouse_surname"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2"
      placeholder="Enter Spouse's Surname"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

      <td class="border text-center bg-[#e7e7e7] ">
        23. NAME of CHILDREN  (Write full name and list all)
      </td>

      <td class="border text-center bg-[#e7e7e7]">
        DATE OF BIRTH (dd/mm/yyyy) 
      </td>

    </tr>

    @php
        $draftChildrenNames = $data['children_familybg'] ?? [];
        $draftChildrenDobs = $data['children_dateofbirth_familybg'] ?? [];

        $childNames = collect(old('children_familybg', $draftChildrenNames));
        $childDobs = collect(old('children_dateofbirth_familybg', $draftChildrenDobs));
        $childRowCount = max(14, $childNames->count(), $childDobs->count());
        while ($childNames->count() < $childRowCount) { $childNames->push(''); }
        while ($childDobs->count() < $childRowCount) { $childDobs->push(''); }
    @endphp

    


    <!-- FIRST NAME + EXTENSION -->
    <tr>
      <td class="bg-[#e7e7e7] px-8 align-middle">
        FIRST NAME
      </td>
       <td colspan="2"
          class="border">
           <textarea
      name="spouse_firstname"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2"
      placeholder="Enter Firstname"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

      <td class="bg-[#e7e7e7] align-top">
        <span class="italic text-xs px-2">NAME EXTENSION (JR., SR)</span>
        <div>
            <textarea
      name="spouse_name_extension"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2"
      placeholder="(Optional)"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
        </div>
      </td>

      @php
          $childName = $childNames->shift();
          $childDob = $childDobs->shift();
      @endphp
      <td class="border">
       <div class="h-full w-full">
         <textarea
      name="children_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childName }}</textarea>
          </div>
      </td>

      <td class="border">
       <div  class="h-full w-full">
         <input
      type="date"
      name="children_dateofbirth_familybg[]"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             bg-transparent text-center"
      style="font-size: 18px; padding:4px; border:none; box-sizing:border-box; margin:0;"
      value="{{ $childDob }}"
      max="{{ now()->format('Y-m-d') }}" />
       </div>
      </td>
    </tr>

    <!-- MIDDLE NAME -->
    <tr>
      <td class="bg-[#e7e7e7]  px-8 "> 
        MIDDLE NAME
      </td>
      <td colspan="3"
          class="border border-b-2 h-10">
          <div class="h-full w-full">
         <textarea
      name="spouse_middlename"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-2"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
      placeholder="Enter Middle Name"
    ></textarea>
      </td>

      @php
          $childName = $childNames->shift();
          $childDob = $childDobs->shift();
      @endphp
      <td class="border">
       <div class="h-full w-full">
         <textarea
      name="children_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childName }}</textarea>
          </div>
      </td>

      <td class="border">
       <div  class="h-full w-full">
         <input
      type="date"
      name="children_dateofbirth_familybg[]"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             bg-transparent text-center"
      style="font-size: 18px; padding:4px; border:none; box-sizing:border-box; margin:0;"
      value="{{ $childDob }}"
      max="{{ now()->format('Y-m-d') }}" />
       </div>
      </td>
    </tr>

      <tr>
      <td class="bg-[#e7e7e7] px-2 border border-t-2">OCCUPATION</td>
       
      <td colspan="3"
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="spouse_occupation"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-2"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
      placeholder="Enter Occupation"
    ></textarea>
      </td>

    @php
        $childName = $childNames->shift();
        $childDob = $childDobs->shift();
    @endphp
    <td class="border">
       <div class="h-full w-full">
         <textarea
      name="children_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childName }}</textarea>
          </div>
      </td>

      <td class="border">
       <div  class="h-full w-full">
         <input
      type="date"
      name="children_dateofbirth_familybg[]"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             bg-transparent text-center"
      style="font-size: 18px; padding:4px; border:none; box-sizing:border-box; margin:0;"
      value="{{ $childDob }}"
      max="{{ now()->format('Y-m-d') }}" />
       </div>
      </td>
    </tr>

      <tr>
      <td class="bg-[#e7e7e7]  px-2 border">EMPLOYER/BUSINESS NAME</td>
      
      <td colspan="3"
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="spouse_employer_business_name"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-2"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
      placeholder="Enter Enmployer/Business Name"
    ></textarea>
      </td>


      @php
          $childName = $childNames->shift();
          $childDob = $childDobs->shift();
      @endphp
        <td class="border">
       <div class="h-full w-full">
         <textarea
      name="children_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childName }}</textarea>
          </div>
      </td>

      <td class="border">
       <div  class="h-full w-full">
         <input
      type="date"
      name="children_dateofbirth_familybg[]"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             bg-transparent text-center"
      style="font-size: 18px; padding:4px; border:none; box-sizing:border-box; margin:0;"
      value="{{ $childDob }}"
      max="{{ now()->format('Y-m-d') }}" />
       </div>
      </td>
    </tr>

      <tr>
      <td class="bg-[#e7e7e7]  px-2 border">BUSINESS ADDRESS</td>
      
      <td colspan="3"
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="spouse_business_address"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-2"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
      placeholder="Enter Business Address"
    ></textarea>
      </td>


    @php
        $childName = $childNames->shift();
        $childDob = $childDobs->shift();
    @endphp
     <td class="border">
       <div class="h-full w-full">
         <textarea
      name="children_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childName }}</textarea>
          </div>
      </td>

      <td class="border">
       <div  class="h-full w-full">
         <input
      type="date"
      name="children_dateofbirth_familybg[]"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             bg-transparent text-center"
      style="font-size: 18px; padding:4px; border:none; box-sizing:border-box; margin:0;"
      value="{{ $childDob }}"
      max="{{ now()->format('Y-m-d') }}" />
       </div>
      </td>
    </tr>

      <tr>
      <td class="bg-[#e7e7e7]  px-2 border">TELEPHONE NO.</td>
      
       <td colspan="3"
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="spouse_telephone_no"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-2"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
      placeholder="Enter Telephone NO."
    ></textarea>
      </td>

      @php
          $childName = $childNames->shift();
          $childDob = $childDobs->shift();
      @endphp
      <td class="border">
       <div class="h-full w-full">
         <textarea
      name="children_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childName }}</textarea>
          </div>
      </td>

      <td class="border">
       <div  class="h-full w-full">
         <input
      type="date"
      name="children_dateofbirth_familybg[]"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             bg-transparent text-center"
      style="font-size: 18px; padding:4px; border:none; box-sizing:border-box; margin:0;"
      value="{{ $childDob }}"
      max="{{ now()->format('Y-m-d') }}" />
       </div>
      </td>
    </tr>


    <tr>
      <td class="bg-[#e7e7e7]  px-2 align-middle">
        24. FATHER'S SURNAME
      </td>
      

       <td colspan="3"
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="father_surname"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-2"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
      placeholder="Enter Father's Surname"
    ></textarea>
      </td>

      @php
          $childName = $childNames->shift();
          $childDob = $childDobs->shift();
      @endphp
       <td class="border">
       <div class="h-full w-full">
         <textarea
      name="children_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childName }}</textarea>
          </div>
      </td>

      <td class="border">
       <div  class="h-full w-full">
         <input
      type="date"
      name="children_dateofbirth_familybg[]"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             bg-transparent text-center"
      style="font-size: 18px; padding:4px; border:none; box-sizing:border-box; margin:0;"
      value="{{ $childDob }}"
      max="{{ now()->format('Y-m-d') }}" />
       </div>
      </td>
    </tr>

    <!-- FIRST NAME + EXTENSION -->
    <tr>
      <td class="bg-[#e7e7e7] px-8 align-middle">
        FIRST NAME
      </td>

       <td colspan="2"
          class="border">
           <textarea
      name="father_firstname"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2"
      placeholder="Enter Firstname"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

      <td class="bg-[#e7e7e7] align-top">
        <span class="italic text-xs px-2">NAME EXTENSION (JR., SR)</span>
        <div>
            <textarea
      name="father_name_extension"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2"
      placeholder="(Optional)"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
        </div>
      </td>
      @php
          $childName = $childNames->shift();
          $childDob = $childDobs->shift();
      @endphp
       <td class="border">
       <div class="h-full w-full">
         <textarea
      name="children_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childName }}</textarea>
          </div>
      </td>

      <td class="border">
       <div  class="h-full w-full">
         <input
      type="date"
      name="children_dateofbirth_familybg[]"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             bg-transparent text-center"
      style="font-size: 18px; padding:4px; border:none; box-sizing:border-box; margin:0;"
      value="{{ $childDob }}"
      max="{{ now()->format('Y-m-d') }}" />
       </div>
      </td>
    </tr>

    <!-- MIDDLE NAME -->
    <tr>
      <td class="bg-[#e7e7e7]  px-8"> 
        MIDDLE NAME
      </td>

      <td colspan="3"
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="father_middlename"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
      placeholder="Enter Middle Name"
    ></textarea>
      </td>

     @php
         $childName = $childNames->shift();
         $childDob = $childDobs->shift();
     @endphp
     <td class="border">
       <div class="h-full w-full">
         <textarea
      name="children_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childName }}</textarea>
          </div>
      </td>

      <td class="border">
       <div  class="h-full w-full">
         <input
      type="date"
      name="children_dateofbirth_familybg[]"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             bg-transparent text-center"
      style="font-size: 18px; padding:4px; border:none; box-sizing:border-box; margin:0;"
      value="{{ $childDob }}"
      max="{{ now()->format('Y-m-d') }}" />
       </div>
      </td>
    </tr>



    <tr>
      <td class="bg-[#e7e7e7] px-2 align-middle border-t-2" colspan="4">
        25. MOTHER'S MAIDEN NAME
      </td>

      @php
          $childName = $childNames->shift();
          $childDob = $childDobs->shift();
      @endphp
      <td class="border">
       <div class="h-full w-full">
         <textarea
      name="children_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childName }}</textarea>
          </div>
      </td>

      <td class="border">
       <div  class="h-full w-full">
         <input
      type="date"
      name="children_dateofbirth_familybg[]"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             bg-transparent text-center"
      style="font-size: 18px; padding:4px; border:none; box-sizing:border-box; margin:0;"
      value="{{ $childDob }}"
      max="{{ now()->format('Y-m-d') }}" />
       </div>
      </td>
    </tr>

    <!-- MIDDLE NAME -->
    <tr>
      <td class="bg-[#e7e7e7] align-middle  px-8"> 
        SURNAME
      </td>
      
      <td colspan="3"
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="mother_surname"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
      placeholder="Enter Surname"
    ></textarea>
      </td>

      @php
          $childName = $childNames->shift();
          $childDob = $childDobs->shift();
      @endphp
      <td class="border">
       <div class="h-full w-full">
         <textarea
      name="children_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childName }}</textarea>
          </div>
      </td>

      <td class="border">
       <div  class="h-full w-full">
         <input
      type="date"
      name="children_dateofbirth_familybg[]"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             bg-transparent text-center"
      style="font-size: 18px; padding:4px; border:none; box-sizing:border-box; margin:0;"
      value="{{ $childDob }}"
      max="{{ now()->format('Y-m-d') }}" />
       </div>
      </td>
    </tr>


    <tr>
      <td class="bg-[#e7e7e7]  px-8 align-middle">
       FIRST NAME
      </td>
      
      <td colspan="3"
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="mother_firstname"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
      placeholder="Enter First Name"
    ></textarea>
      </td>

      @php
          $childName = $childNames->shift();
          $childDob = $childDobs->shift();
      @endphp
      <td class="border">
       <div class="h-full w-full">
         <textarea
      name="children_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childName }}</textarea>
          </div>
      </td>

      <td class="border">
       <div  class="h-full w-full">
         <input
      type="date"
      name="children_dateofbirth_familybg[]"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             bg-transparent text-center"
      style="font-size: 18px; padding:4px; border:none; box-sizing:border-box; margin:0;"
      value="{{ $childDob }}"
      max="{{ now()->format('Y-m-d') }}" />
       </div>
      </td>
    </tr>

    <tr>
      <td class="bg-[#e7e7e7] px-8 align-middle">
        MIDDLE NAME
      </td>
      
      <td colspan="3"
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="mother_middlename"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
      placeholder="Enter Middle Name"
    ></textarea>
      </td>

      
      @php
          $childName = $childNames->shift();
          $childDob = $childDobs->shift();
      @endphp
       <td class="border">
       <div class="h-full w-full">
         <textarea
      name="children_familybg[]"
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    >{{ $childName }}</textarea>
          </div>
      </td>

      <td class="border">
       <div  class="h-full w-full">
         <input
      type="date"
      name="children_dateofbirth_familybg[]"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             bg-transparent text-center"
      style="font-size: 18px; padding:4px; border:none; box-sizing:border-box; margin:0;"
      value="{{ $childDob }}"
      max="{{ now()->format('Y-m-d') }}" />
       </div>
      </td>
    </tr>
  </table>


<table data-section="educational_background" class="w-full border border-black border-collapse table-fixed font-['Arial_Narrow','sans-serif'] text-base">

  <!-- EXACT COLUMN GRID (8 columns) -->
  <colgroup>
    <col style="width:27%"> <!-- LEVEL -->
    <col style="width:30%"> <!-- SCHOOL -->
    <col style="width:33%"> <!-- COURSE -->
    <col style="width:10%"> <!-- FROM -->
    <col style="width:10%"> <!-- TO -->
    <col style="width:17%"> <!-- HIGHEST -->
    <col style="width:15%"> <!-- YEAR -->
    <col style="width:17.5%"> <!-- HONORS -->
  </colgroup>

  <tr>
    <td colspan="8"
        class="font-['Arial_Narrow','Arial',sans-serif] font-bold bg-[#8a8a8a] text-white  italic text-xl px-2 border-2 border-black">
      III. EDUCATIONAL BACKGROUND
    </td>
  </tr>

  <!-- HEADER ROW 1 -->
  <tr>
    <th class="border font-light" rowspan="2">26. LEVEL</th>
    <th class="border font-light" rowspan="2">NAME OF SCHOOL<p>(Write in Full)</p></th>
    <th class="border font-light" rowspan="2">BASIC EDUCATION / DEGREE / COURSE<p>(Write in full)</p></th>
    <th class="border text-center font-light" colspan="2">PERIOD OF ATTENDANCE</th>
    <th class="border font-light" rowspan="2">
      HIGHEST LEVEL/<br>UNITS EARNED<br>
      <span class="text-base">(if not graduated)</span>
    </th>
    <th class="border font-light" rowspan="2">YEAR GRADUATED</th>
    <th class="border font-light" rowspan="2">SCHOLARSHIP / ACADEMIC<br>HONORS RECEIVED</th>
  </tr>

  <!-- HEADER ROW 2 -->
  <tr>
    <th class="border text-center font-light">FROM</th>
    <th class="border text-center font-light">TO</th>
  </tr>

  <!-- DATA ROW -->
  <tr class="min-h-[20]" style="width: 20%;" data-education-base="ELEMENTARY">
    <td class="border text-center align-middle h-20">ELEMENTARY</td>

    <!-- EDITABLE CELL PATTERN -->
     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[elementary][school_name]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[elementary][basic_education]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[elementary][from]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[elementary][to]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

      <td
            class="border h-10">
            <div class="h-full w-full">
          <textarea
        name="education[elementary][highest_level]"
        required
        rows="1"
        class="w-full h-full text-lg resize-none
              focus:outline-none focus:ring-0
              whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
        oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
      ></textarea>
        </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[elementary][year_graduated]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10 relative">
          <div class="h-full w-full">
         <textarea
      name="education[elementary][scholarship_acadhonors]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </div>
      </td>
  </tr>


   <tr class="min-h-[20]" style="width: 20%;" data-education-base="SECONDARY">
    <td class="border text-center align-middle h-20">SECONDARY</td>

    <!-- EDITABLE CELL PATTERN -->
     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[secondary][school_name]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[secondary][basic_education]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[secondary][from]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[secondary][to]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[secondary][highest_level]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[secondary][year_graduated]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[secondary][scholarship_acadhonors]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </div>
      </td>
  </tr>

   <tr class="min-h-[20]" style="width: 20%;">
    <td class="border text-center align-middle h-20">VOCATIONAL / TRADE COURSE</td>

    <!-- EDITABLE CELL PATTERN -->
    <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[vocational][school_name]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[vocational][basic_education]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[vocational][from]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[vocational][to]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[vocational][highest_level]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[vocational][year_graduated]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10 relative add-btn-cell has-add-btn">
          <div class="h-full w-full">
         <textarea
      name="education[vocational][scholarship_acadhonors]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </div>
      </td>
  </tr>

   <tr class="min-h-[20]" style="width: 20%;">
    <td class="border text-center align-middle h-20">COLLEGE</td>

    <!-- EDITABLE CELL PATTERN -->
    <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[college][school_name]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[college][basic_education]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[college][from]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[college][to]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[college][highest_level]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[college][year_graduated]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10 relative add-btn-cell has-add-btn">
          <div class="h-full w-full">
         <textarea
      name="education[college][scholarship_acadhonors]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </div>
      </td>
  </tr>

   <tr class="min-h-[20]" style="width: 20%;">
    <td class="border text-center align-middle h-20">GRADUATE STUDIES</td>

    <!-- EDITABLE CELL PATTERN -->
   <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[graduate_studies][school_name]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[graduate_studies][basic_education]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

      <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[graduate_studies][from]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[graduate_studies][to]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[graduate_studies][highest_level]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[graduate_studies][year_graduated]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </td>

     <td
          class="border h-10">
          <div class="h-full w-full">
         <textarea
      name="education[graduate_studies][scholarship_acadhonors]"
      required
      rows="1"
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             whitespace-pre-wrap overflow-hidden px-2 py-3 text-center"
      oninput="this.style.height='auto'; this.style.height=this.scrollHeight+'px';"
    ></textarea>
      </div>
      </td>
  </tr>

  <tr>
    <td class="border h-2 text-center text-xl font-bold italic align-middle">
      SIGNATURE
    </td>

    <td class="border" colspan="2">
      <div class="h-full w-full p-2">
        <label id="signatureBox" data-signature-cell class="signature-box block h-36 w-full cursor-pointer">
          <input
            type="file"
            name="signature_file"
            id="signatureFileInput"
            accept="image/*"
            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer"
            onchange="handleSignatureUpload(this.files[0])"
          >
          <img
            id="signaturePreviewImg"
            src="{{ !empty($signaturePath) ? Storage::url($signaturePath) : '' }}"
            alt="Signature preview"
            class="absolute inset-0 w-full h-full object-contain {{ empty($signaturePath) ? 'hidden' : '' }}"
          >
          <div id="signaturePlaceholder" class="absolute inset-0 flex items-center justify-center text-center text-xs text-gray-600 px-3">
            Upload signature here
          </div>
        </label>

        <input type="hidden" name="signature_path" id="signature_path" value="{{ $signaturePath ?? '' }}">
        <input type="hidden" name="signature_data" id="signature_data">
      </div>
    </td>

    <td class="border text-center text-xl font-bold italic align-middle" colspan="2">
      DATE
    </td>

    <td colspan="3"
          class="border h-10">
          <div class="h-full w-full flex items-center justify-center">
         <input
      type="date"
      name="date1"
      required
      class="w-full h-full text-lg resize-none
             focus:outline-none focus:ring-0
             px-2 py-1 text-center bg-transparent border-none"
    ></input>
      </td>
</table>

<table class="bg-transparent">
    <div class="flex justify-end mr-2 font-['Arial_Narrow','sans-serif']">
    CS FORM 212 (Revised 2025), Page 1 of 5
    </div>
</table>

    <div class="flex justify-end mt-4">
        <button type="submit" id="next-btn" class="px-4 py-2 bg-blue-600 text-white rounded shadow border border-blue-700 hover:bg-blue-700 print:text-white print:bg-blue-600">Next Page</button>
    </div>
    </div>
    </form>
<script>
        function copyResidentialToPermanent() {
            // Copy residential address to permanent address
            const fields = [
                'house_block_lot', 'permanent_house_block_lot',
                'street', 'permanent_street', 
                'subdivision_village', 'permanent_subdivision_village',
                'baranggay', 'permanent_baranggay',
                'city_municipality', 'permanent_city_municipality',
                'province', 'permanent_province'
            ];
            
            let hasChanges = false;
            
            for (let i = 0; i < fields.length; i += 2) {
                const sourceField = document.querySelector(`[name="${fields[i]}"]`);
                const targetField = document.querySelector(`[name="${fields[i+1]}"]`);
                
                if (sourceField && targetField) {
                    const oldValue = targetField.value;
                    targetField.value = sourceField.value;
                    // Trigger the auto-resize for textareas
                    targetField.style.height = 'auto';
                    targetField.style.height = targetField.scrollHeight + 'px';
                    
                    // Check if value actually changed
                    if (oldValue !== targetField.value) {
                        hasChanges = true;
                        // Trigger input event to notify autosave
                        targetField.dispatchEvent(new Event('input', { bubbles: true }));
                    }
                }
            }
            
            // Trigger autosave if any changes were made
            if (hasChanges && typeof persist === 'function') {
                hasUserInput = true;
                persist();
            }
        }

        function checkChildrenFields() {
            // Get all children name and date of birth fields
            const childrenNameFields = document.querySelectorAll('textarea[name="children_familybg[]"]');
            const childrenDobFields = document.querySelectorAll('input[name="children_dateofbirth_familybg[]"]');
            
            // Check if first child name is "NA"
            const firstChildName = childrenNameFields[0]?.value.trim().toUpperCase() || '';
            const isNA = firstChildName === 'NA' || firstChildName === 'N/A' || firstChildName === 'N.A.';
            
            // Process each row
            childrenNameFields.forEach((nameField, index) => {
                const dobField = childrenDobFields[index];
                const hasName = nameField.value.trim() !== '';
                
                if (index === 0) {
                    // First row - check NA logic and sequential logic
                    if (isNA) {
                        // If first child is NA, disable first row too
                        nameField.disabled = false; // Allow editing to remove NA
                        nameField.style.backgroundColor = 'transparent';
                        
                        if (dobField) {
                            dobField.disabled = true;
                            dobField.style.backgroundColor = '#f5f5f5';
                            dobField.style.display = 'none';
                            dobField.value = '';
                        }
                    } else {
                        // Normal sequential logic for first row
                        nameField.disabled = false;
                        nameField.style.backgroundColor = 'transparent';
                        
                        if (dobField) {
                            if (hasName) {
                                dobField.disabled = false;
                                dobField.style.display = 'block';
                                dobField.style.backgroundColor = 'transparent';
                            } else {
                                dobField.style.display = 'none';
                                dobField.value = '';
                            }
                        }
                    }
                } else {
                    // Remaining rows - check NA logic and sequential logic
                    if (isNA) {
                        // If first child is NA, disable all remaining rows
                        nameField.disabled = true;
                        nameField.style.backgroundColor = '#f5f5f5';
                        nameField.value = '';
                        
                        if (dobField) {
                            dobField.disabled = true;
                            dobField.style.backgroundColor = '#f5f5f5';
                            dobField.style.display = 'none';
                            dobField.value = '';
                        }
                    } else {
                        // Normal sequential logic for remaining rows
                        nameField.disabled = false;
                        nameField.style.backgroundColor = 'transparent';
                        
                        if (dobField) {
                            if (hasName) {
                                dobField.disabled = false;
                                dobField.style.display = 'block';
                                dobField.style.backgroundColor = 'transparent';
                            } else {
                                dobField.style.display = 'none';
                                dobField.value = '';
                            }
                        }
                    }
                }
            });
        }

        document.addEventListener('DOMContentLoaded', () => {

  // Check children fields on page load
  checkChildrenFields();
  
  // Add event listeners to all children name fields
  const childrenNameFields = document.querySelectorAll('textarea[name="children_familybg[]"]');
  childrenNameFields.forEach(field => {
    field.addEventListener('input', checkChildrenFields);
    field.addEventListener('change', checkChildrenFields);
  });

  const form = document.querySelector('#pds-form1');
  const autosaveOverlay = document.getElementById('autosaveOverlay');
  if (!form) {
    console.error('Form element #pds-form1 not found!');
    return;
  }
  console.log('Form element found:', form);

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
  

  // Reuse shared cache key defined in first script
  const storageKey = window.storageKey || ('pds_form_step1_' + ({{ auth()->id() ?? 0 }}));
  const singleSelectCheckboxNames = new Set(['sex[]','civilstatus[]','citizenship[]']);
  const storageBase = "{{ asset('storage') }}/";
  const initialSignaturePath = @json($signaturePath ?? '');

  const signaturePreviewImg = document.getElementById('signaturePreviewImg');
  const signaturePlaceholder = document.getElementById('signaturePlaceholder');
  const signatureBox = document.getElementById('signatureBox');
  const signatureDataInput = document.getElementById('signature_data');
  const signaturePathInput = document.getElementById('signature_path');
  const signatureFileName = document.getElementById('signatureFileName');

  const buildSignatureUrl = (path) => {
    if (!path) return '';
    const cleaned = path.replace(/^public\//, '');
    return storageBase + cleaned;
  };

  const updateSignaturePreviewFromInputs = () => {
    if (!signaturePreviewImg || !signaturePlaceholder) return;
    const dataUrl = signatureDataInput?.value;
    const pathVal = signaturePathInput?.value || initialSignaturePath;
    const url = dataUrl || buildSignatureUrl(pathVal);
    if (url) {
      signaturePreviewImg.src = url;
      signaturePreviewImg.classList.remove('hidden');
      signaturePlaceholder.classList.add('hidden');
      signatureBox?.classList.add('signature-has-image');
    } else {
      signaturePreviewImg.classList.add('hidden');
      signaturePlaceholder.classList.remove('hidden');
      signatureBox?.classList.remove('signature-has-image');
    }
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

    // Map single-select checkbox arrays from draft/session (keys without []) into form fields
    const hydrateSingleSelect = (baseKey) => {
      const val = Array.isArray(data[baseKey]) ? data[baseKey][0] : data[baseKey];
      if (val === undefined) return;
      const targetName = `${baseKey}[]`;
      form.querySelectorAll(`input[type="checkbox"][name="${targetName}"]`).forEach(el => {
        el.checked = String(el.value) === String(val);
      });
    };

    ['sex', 'civilstatus', 'citizenship'].forEach(hydrateSingleSelect);

    // Only apply server/session signature if local value is missing
    if (overrideData?.signature_path && signaturePathInput && !signaturePathInput.value) {
      signaturePathInput.value = overrideData.signature_path;
    }
    if (overrideData?.signature_data && signatureDataInput && !signatureDataInput.value) {
      signatureDataInput.value = overrideData.signature_data;
    }

    // For fields with [] names, ensure arrays apply in order
    const arrayBucket = {};
    Object.entries(data).forEach(([name, stored]) => {
      if (name.endsWith('[]') && Array.isArray(stored)) {
        arrayBucket[name] = stored;
      }
    });

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

  // --- Signature upload with auto background removal (single source for all forms) ---
  window.handleSignatureUpload = (file) => {
    if (!file) return;
    if (signatureFileName) signatureFileName.textContent = file.name;

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
        const threshold = 240; // treat near-white as background
        for (let i = 0; i < data.length; i += 4) {
          if (data[i] > threshold && data[i + 1] > threshold && data[i + 2] > threshold) {
            data[i + 3] = 0; // transparent
          }
        }
        ctx.putImageData(imageData, 0, 0);

        const output = canvas.toDataURL('image/png');
        signatureDataInput.value = output;
        signaturePathInput.value = '';
        signaturePreviewImg.src = output;
        signaturePreviewImg.classList.remove('hidden');
        signaturePlaceholder.classList.add('hidden');
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
  };

  // AUTOSAVE to server (throttled with retry + overlay until OK)
  const autoSaveToServer = (() => {
    let timer;
    let failureCount = 0;
    const baseDelay = 1200;
    const maxDelay = 8000;
    const maxRetries = 4; // avoid runaway spamming
    const showOverlay = (flag) => {
      if (!autosaveOverlay) return;
      autosaveOverlay.classList.toggle('hidden', !flag);
    };

    const appendSingleSelectGroups = (formData) => {
      ['sex', 'civilstatus', 'citizenship'].forEach((base) => {
        const selected = form.querySelector(`input[name="${base}[]"]:checked`);
        if (selected) {
          formData.set(base, selected.value);
        }
      });
    };

    // When no dynamic education extra rows exist, explicitly send empty arrays
    const appendEmptyEducationExtras = (formData) => {
      const extraKeys = [
        'education_extra_level',
        'education_extra_school_name',
        'education_extra_basic_education',
        'education_extra_from',
        'education_extra_to',
        'education_extra_highest_level',
        'education_extra_year_graduated',
        'education_extra_scholarship_acadhonors'
      ];
      extraKeys.forEach((key) => {
        if (!form.querySelector(`[name="${key}[]"]`)) {
          formData.append(`${key}[]`, '');
        }
      });
    };

    const send = () => {
      if (!navigator.onLine) {
        console.warn('Auto-save skipped: offline');
        showOverlay(true);
        failureCount += 1;
        const offlineDelay = Math.min(baseDelay * failureCount, maxDelay);
        timer = setTimeout(send, offlineDelay);
        return;
      }
      const formData = new FormData(form);
      appendSingleSelectGroups(formData);
      appendEmptyEducationExtras(formData);
      console.log('Auto-saving to server...');
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
          console.error('Auto-save failed:', response.status, response.statusText);
          showOverlay(true);
          if (response.status === 419 || response.status === 401) {
            // Stop retrying when session/CSRF is invalid; user should refresh/login again
            throw new Error('Auto-save halted: auth/CSRF');
          }
          failureCount += 1;
          const delay = Math.min(baseDelay * Math.pow(2, failureCount - 1), maxDelay);
          if (failureCount <= maxRetries) {
            setTimeout(send, delay);
          }
          throw new Error('Auto-save failed');
        }
        return response.json();
      })
      .then(data => {
        console.log('Auto-save response:', data);
        const ok = data && data.status === 'ok';
        if (ok) {
          showOverlay(false);
          failureCount = 0;
        } else {
          showOverlay(true);
          failureCount += 1;
          const delay = Math.min(baseDelay * Math.pow(2, failureCount - 1), maxDelay);
          if (failureCount <= maxRetries) {
            setTimeout(send, delay);
          }
        }
      })
      .catch(error => {
        console.error('Auto-save error:', error);
        showOverlay(true);
        if (String(error).includes('auth/CSRF')) return; // already halted above
        failureCount += 1;
        const delay = Math.min(baseDelay * Math.pow(2, failureCount - 1), maxDelay);
        if (failureCount <= maxRetries) {
          setTimeout(send, delay);
        }
      });
    };

    return () => {
      clearTimeout(timer);
      timer = setTimeout(send, 800);
    };
  })();

  let hydrated = true; // allow immediate caching even before hydration completes

  let hasUserInput = false;

  const persist = () => {
    console.log('Persist function called');
    saveCache();
    if (hasUserInput) {
      autoSaveToServer();
    }
  };

  // Expose persist so add/remove buttons can trigger it
  window.persist = persist;

  // Hydrate: server/session defaults, then local cache overrides
  const initialPayload = { ...(draftData || {}), ...(sessionData || {}) };
  loadCache(initialPayload);
  hydrated = true;
  // Persist merged cache once so a fast refresh keeps latest values
  saveCache();
  updateSignaturePreviewFromInputs();
  // If we hydrated with any data, push it to the server once so drafts persist after login
  const hasHydratedData = initialPayload && Object.keys(initialPayload).length > 0;
  if (hasHydratedData) {
    hasUserInput = true; // allow autosave to fire
    autoSaveToServer();
  }

  // Debug: Check if form element exists
  console.log('Form element:', form);
  console.log('Form event listener attached');

  // Enforce single select behavior for specific checkbox groups
  document.querySelectorAll('input[type="checkbox"]').forEach(box => {
    if (!singleSelectCheckboxNames.has(box.name)) return;
    box.addEventListener('change', () => {
      if (!box.checked) return;
      document.querySelectorAll(`input[type="checkbox"][name="${box.name}"]`).forEach(other => {
        if (other !== box) other.checked = false;
      });
      saveCache();
    });
  });

  // If served via static view (no $data), fetch draft and hydrate once
  fetch('{{ route('pds.draft') }}', { headers: { 'Accept': 'application/json' } })
    .then(r => r.ok ? r.json() : null)
    .then(json => {
      if (!json || !json.data) return;
      // Apply server data as defaults; keep existing local overrides
      loadCache(json.data);
      updateSignaturePreviewFromInputs();
      // Persist merged cache once so a fast refresh keeps latest values
      hydrated = true;
      saveCache();
      // Push fetched draft to server to keep DB in sync
      if (json.data && Object.keys(json.data).length > 0) {
        hasUserInput = true;
        autoSaveToServer();
      }
    })
    .catch(() => {});

  form.addEventListener('input', (e) => {
    console.log('Form input event triggered on:', e.target.name, e.target.type);
    hasUserInput = true;
    persist();
  });

  // Sync all date fields across forms using localStorage - form1 is the master
  // Exclude personal DOB (date_of_birth) so it stays independent
  window.syncAllDates = function(selectedDate) {
    console.log('syncAllDates called with:', selectedDate);
    if (!selectedDate) return;
    
    // Save to localStorage for cross-page synchronization
    localStorage.setItem('pds_master_date', selectedDate);
    console.log('Saved master date to localStorage:', selectedDate);
    
    // Update all date inputs on current page (excluding form1 and personal DOB)
    const dateInputs = document.querySelectorAll('input[type="date"][name^="date"]:not([name="date_of_birth"])');
    console.log('Found date inputs on current page:', dateInputs.length);
    
    dateInputs.forEach(input => {
      console.log('Processing input:', input.name, 'current value:', input.value);
      // Skip form1's date input - it's the master
      if (input.name === 'date1') return;
      // Skip personal DOB so it never syncs with the master date
      if (input.name === 'date_of_birth') return;
      
      if (input.value !== selectedDate) {
        console.log('Updating', input.name, 'from', input.value, 'to', selectedDate);
        input.value = selectedDate;
        // Trigger change event to save to cache
        input.dispatchEvent(new Event('change', { bubbles: true }));
      }
    });
  };

  // Initialize sync when form1 date changes
  const form1DateInput = document.querySelector('input[name="date1"]');
  if (form1DateInput) {
    const storedMasterDate = localStorage.getItem('pds_master_date');
    if (storedMasterDate && !form1DateInput.value) {
      console.log('Applying master date from localStorage to form1:', storedMasterDate);
      form1DateInput.value = storedMasterDate;
      form1DateInput.dispatchEvent(new Event('change', { bubbles: true }));
    }

    form1DateInput.addEventListener('change', function() {
      console.log('Form1 date changed to:', this.value);
      syncAllDates(this.value);
    });

    // Seed master date on initial load so other forms can pick it up without requiring a change event
    if (form1DateInput.value) {
      console.log('Seeding master date from existing form1 value:', form1DateInput.value);
      syncAllDates(form1DateInput.value);
    }
  }

});
</script>

<style>
/* Custom styling for date inputs - bigger calendar icon and middle text alignment */
input[type="date"] {
  color-scheme: light dark;
  font-size: 30px;
  text-align: center !important;
  padding-right: 40px;
  position: relative;
  margin-left: 100px;
}

/* Make calendar icon bigger and blue in WebKit browsers (Chrome, Safari, Edge) */
input[type="date"]::-webkit-calendar-picker-indicator {
  width: 30px;
  height: 30px;
  cursor: pointer;
  background-size: 30px 30px;
  background-color: transparent;
  filter: invert(35%) sepia(100%) saturate(1500%) hue-rotate(190deg) brightness(95%) contrast(95%) !important;
  opacity: 1 !important;
  -webkit-filter: invert(35%) sepia(100%) saturate(4500%) hue-rotate(190deg) brightness(95%) contrast(150%) !important;
  vertical-align: middle;
  position: absolute;
  right: 5px;
}

/* Make calendar icon bigger and blue in Firefox */
input[type="date"]::-moz-calendar-picker-indicator {
  width: 30px;
  height: 30px;
  cursor: pointer;
  background-size: 30px 30px;
  background-color: transparent;
  filter: invert(35%) sepia(100%) saturate(1500%) hue-rotate(190deg) brightness(95%) contrast(95%) !important;
  opacity: 1 !important;
  -webkit-filter: invert(35%) sepia(100%) saturate(1500%) hue-rotate(190deg) brightness(95%) contrast(95%) !important;
  vertical-align: middle;
  position: absolute;
  right: 5px;
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
</style>

<script>
// Immediate protection for email field - runs before any other scripts
document.addEventListener('DOMContentLoaded', () => {
    const emailField = document.querySelector('textarea[name="email_address"]');
    if (emailField) {
        // Force text-transform to none with highest priority
        emailField.style.textTransform = 'none';
        emailField.style.setProperty('text-transform', 'none', 'important');
        emailField.classList.add('no-uppercase');
        
        // Remove ALL event listeners (clean slate)
        const clone = emailField.cloneNode(true);
        clone.value = emailField.value;
        emailField.parentNode.replaceChild(clone, emailField);
        
        // Ensure style is maintained
        clone.style.textTransform = 'none';
        clone.style.setProperty('text-transform', 'none', 'important');
    }
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