<x-app-layout>
<div id="autosaveOverlay5" class="autosave-overlay hidden">Saving…</div>
<form id="pds-form5" method="POST" action="{{ route('pds.submit', [], false) }}" class="w-full" enctype="multipart/form-data">
@csrf
<style>
textarea:focus { outline: none; box-shadow: none; }
[contenteditable]:focus { outline: none; margin: 0; padding: 0; }

body { margin: 0; }
.autosave-overlay { position: fixed; inset: 0; background: rgba(255,255,255,0.8); display: flex; align-items: center; justify-content: center; z-index: 9999; font-size: 20px; font-weight: 700; color: #111; }
.autosave-overlay.hidden { display: none; }

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
  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', () => {
  const form = document.getElementById('pds-form5');
  const autosaveOverlay = document.getElementById('autosaveOverlay5');
  const submitBtn = document.getElementById('submit-pds-btn');
  if (!form || !submitBtn) return;

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

  const isNA = (val) => {
    const v = (val || '').trim().toUpperCase();
    return v === 'NA' || v === 'N/A' || v === 'NONE';
  };

  const isFilled = (el) => {
    if (el.type === 'file') return el.files && el.files.length > 0;
    if (el.type === 'checkbox' || el.type === 'radio') return el.checked;
    const val = (el.value || '').trim();
    if (isNA(val)) return true;
    return val !== '';
  };

  const storageKey = 'pds_form_step5_' + ({{ auth()->id() ?? 0 }});
  const singleSelectCheckboxNames = new Set();
  const storageBase = "{{ asset('storage') }}/";
  const initialSignaturePath = @json($signaturePath ?? '');

  const signaturePreviewImg5 = document.getElementById('signaturePreviewImg5');
  const signaturePlaceholder5 = document.getElementById('signaturePlaceholder5');
  const signatureBox5 = document.getElementById('signatureBox5');
  const signatureDataInput5 = document.getElementById('signature_data5');
  const signaturePathInput5 = document.getElementById('signature_path5');

  const buildSignatureUrl5 = (path) => {
    if (!path) return '';
    const cleaned = path.replace(/^public\//, '');
    return storageBase + cleaned;
  };

  const updateSignaturePreview5 = () => {
    if (!signaturePreviewImg5 || !signaturePlaceholder5) return;
    const dataUrl = signatureDataInput5?.value;
    const pathVal = signaturePathInput5?.value || initialSignaturePath;
    const url = dataUrl || buildSignatureUrl5(pathVal);
    if (url) {
      signaturePreviewImg5.src = url;
      signaturePreviewImg5.classList.remove('hidden');
      signaturePlaceholder5.classList.add('hidden');
      signatureBox5?.classList.add('signature-has-image');
    } else {
      signaturePreviewImg5.classList.add('hidden');
      signaturePlaceholder5.classList.remove('hidden');
      signatureBox5?.classList.remove('signature-has-image');
    }
  };

  window.handleSignatureUpload5 = (file) => {
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
        if (signatureDataInput5) signatureDataInput5.value = output;
        if (signaturePathInput5) signaturePathInput5.value = '';
        updateSignaturePreview5();
        saveCache();
        autoSaveToServer();
      };
      img.src = e.target.result;
    };
    reader.readAsDataURL(file);
  };

  const autoSize = (el) => {
    if (!el) return;
    el.style.height = 'auto';
    el.style.height = `${el.scrollHeight}px`;
  };

  // Helper: create accomplishment input element (used in multiple places)
  const createAccomplishmentInput = () => {
    const div = document.createElement('div');
    div.className = 'flex items-center space-x-2';
    const input = document.createElement('input');
    input.type = 'text';
    input.name = 'accomplishments[]';
    input.className = 'border border-gray-300 bg-transparent text-sm p-1 focus:outline-none flex-grow';
    const removeButton = document.createElement('button');
    removeButton.type = 'button';
    removeButton.className = 'remove-accomplishment text-red-500 hover:text-red-700 font-semibold';
    removeButton.textContent = '×';
    div.appendChild(input);
    div.appendChild(removeButton);
    return { div, input };
  };

  const loadCache = (overrideData = null) => {
    let data = {};
    let localData = {};

    // Always try to load localStorage first (for real-time sync during session)
    try {
      localData = JSON.parse(localStorage.getItem(storageKey) || '{}');
      console.log('[loadCache] localStorage loaded:', localData);
    } catch (e) {
      localData = {};
    }

    // Normalize server data (draft/session) to use []-suffixed keys
    let serverData = {};
    if (overrideData && Object.keys(overrideData).length > 0) {
      // Map remarks -> remarks[] if needed
      if (overrideData.remarks && !overrideData['remarks[]']) {
        overrideData['remarks[]'] = overrideData.remarks;
      }

      const arrayFieldAliases = [
        'duration',
        'position_title',
        'office_unit',
        'immediate_supervisor',
        'agency_location',
        'accomplishments',
        'duties'
      ];

      arrayFieldAliases.forEach((field) => {
        const aliasKey = `${field}[]`;
        if (Array.isArray(overrideData[field]) && !overrideData[aliasKey]) {
          overrideData[aliasKey] = overrideData[field];
        }
      });

      serverData = overrideData;
      console.log('[loadCache] serverData normalized:', serverData);
    }

    // IMPORTANT: localStorage takes priority even for empty values
    if (localData && Object.keys(localData).length > 0) {
      data = { ...(serverData || {}), ...localData };
      console.log('[loadCache] merged data (localStorage wins):', data);
    } else if (serverData && Object.keys(serverData).length > 0) {
      data = serverData;
      console.log('[loadCache] using serverData only:', data);
    } else {
      data = {};
      console.log('[loadCache] no data, starting fresh');
    }

    // Debug: Log the key fields we're trying to load
    console.log('[loadCache] after merge - duration[]:', data['duration[]']);
    console.log('[loadCache] after merge - position_title[]:', data['position_title[]']);
    console.log('[loadCache] after merge - office_unit[]:', data['office_unit[]']);
    console.log('[loadCache] after merge - immediate_supervisor[]:', data['immediate_supervisor[]']);
    console.log('[loadCache] after merge - agency_location[]:', data['agency_location[]']);

    if (overrideData?.signature_path && signaturePathInput5) {
      signaturePathInput5.value = overrideData.signature_path;
    }
    if (overrideData?.signature_data && signatureDataInput5) {
      signatureDataInput5.value = overrideData.signature_data;
    }

    // Ensure enough work-experience rows exist before populating values
    try {
      const rowKeys = ['duration[]','position_title[]','office_unit[]','immediate_supervisor[]','agency_location[]','duties[]'];
      const lengths = rowKeys.map(k => Array.isArray(data[k]) ? data[k].length : 0);
      const accLen = Array.isArray(data.accomplishments_indexed) ? data.accomplishments_indexed.length : 0;
      const savedRowCount = typeof data._row_count === 'number' ? data._row_count : 0;
      const rowsNeeded = Math.max(1, savedRowCount, ...lengths, accLen);
      const tbody = document.getElementById('remarks-rows');
      if (tbody) {
        const rowsNow = tbody.querySelectorAll('tr');
        const existingCount = Math.max(0, rowsNow.length - 1); // minus instructions row
        const baseRow = rowsNow.length > 1 ? rowsNow[1] : null;
        if (baseRow) {
          for (let i = existingCount; i < rowsNeeded; i++) {
            const clone = baseRow.cloneNode(true);
            // clear values
            Array.from(clone.querySelectorAll('input[type="text"], textarea')).forEach(el => { el.value = ''; });
            // normalize accomplishment container/buttons
            const accContainer = clone.querySelector('#accomplishments-container');
            if (accContainer) {
              accContainer.removeAttribute('id');
              accContainer.classList.add('accomplishments-container');
              while (accContainer.children.length) accContainer.removeChild(accContainer.lastChild);
              // ensure two inputs
              const { div: div1, input: input1 } = createAccomplishmentInput();
              const { div: div2, input: input2 } = createAccomplishmentInput();
              accContainer.appendChild(div1);
              accContainer.appendChild(div2);
              // Attach reactive and event listeners to accomplishment inputs
              if (window.attachReactive) {
                window.attachReactive(input1);
                window.attachReactive(input2);
              }
              [input1, input2].forEach(input => {
                input.addEventListener('input', () => {
                  if (window.updateRemarksCounter) window.updateRemarksCounter();
                });
                input.addEventListener('change', () => {
                  if (window.updateRemarksCounter) window.updateRemarksCounter();
                });
              });
            } else {
              const byClass = clone.querySelector('.accomplishments-container');
              if (byClass && byClass.children.length < 2) {
                while (byClass.children.length < 2) {
                  const { div, input } = createAccomplishmentInput();
                  byClass.appendChild(div);
                  // Attach reactive and event listeners
                  if (window.attachReactive) window.attachReactive(input);
                  input.addEventListener('input', () => {
                    if (window.updateRemarksCounter) window.updateRemarksCounter();
                  });
                  input.addEventListener('change', () => {
                    if (window.updateRemarksCounter) window.updateRemarksCounter();
                  });
                }
              }
            }
            const addBtn = clone.querySelector('#add-accomplishment');
            if (addBtn) { addBtn.removeAttribute('id'); addBtn.classList.add('add-accomplishment'); }
            tbody.appendChild(clone);
            
            // Attach reactive and event listeners to ALL inputs in the restored row
            const restoredInputs = clone.querySelectorAll('input[type="text"], textarea');
            restoredInputs.forEach(el => {
              if (window.attachReactive) window.attachReactive(el);
              el.addEventListener('input', () => {
                if (window.updateRemarksCounter) window.updateRemarksCounter();
              });
              el.addEventListener('change', () => {
                if (window.updateRemarksCounter) window.updateRemarksCounter();
              });
            });
          }
        }
      }
    } catch (_) {}

  Object.entries(data).forEach(([name, stored]) => {
      if (name === 'remarks[]' && Array.isArray(stored)) {
        const rows = getRemarks();
        if (rows.length) {
          const val = stored[0] ?? '';
          rows[0].value = val;
          rows[0].dispatchEvent(new Event('input', { bubbles: true }));
        }
        return;
      }

      const elements = Array.from(form.querySelectorAll(`[name="${name}"]`));

      if (name.endsWith('[]') && Array.isArray(stored)) {
        if (name === 'accomplishments[]') {
          const indexed = Array.isArray(data.accomplishments_indexed) ? data.accomplishments_indexed : null;
          const tbody = document.getElementById('remarks-rows');
          if (indexed && tbody) {
            const rows = Array.from(tbody.querySelectorAll('tr')).slice(1); // skip instructions
            indexed.forEach((bucket, rowIdx) => {
              const tr = rows[rowIdx];
              if (!tr || !Array.isArray(bucket)) return;
              const container = tr.querySelector('#accomplishments-container') || tr.querySelector('.accomplishments-container');
              if (!container) return;
              // Ensure inputs match bucket length exactly
              // First, trim extras from the end
              let inputs = Array.from(container.querySelectorAll('input[name="accomplishments[]"]'));
              while (inputs.length > bucket.length) {
                const last = inputs.pop();
                const wrap = last && last.parentElement;
                if (wrap && wrap.parentElement === container) wrap.remove();
              }
              // Then, add more until we match length
              while (inputs.length < bucket.length) {
                const { div, input } = createAccomplishmentInput();
                container.appendChild(div);
                if (window.attachReactive) window.attachReactive(input);
                // Add event listeners for counter updates
                input.addEventListener('input', () => {
                  if (window.updateRemarksCounter) window.updateRemarksCounter();
                });
                input.addEventListener('change', () => {
                  if (window.updateRemarksCounter) window.updateRemarksCounter();
                });
                inputs = Array.from(container.querySelectorAll('input[name="accomplishments[]"]'));
              }
              // Populate values for this row only
              bucket.forEach((val, i) => {
                if (inputs[i]) {
                  inputs[i].value = val;
                  if (window.attachReactive) window.attachReactive(inputs[i]);
                }
              });
            });
          }
        } else {
          elements.forEach((el, idx) => {
            const val = stored[idx] ?? '';
            if (el.type === 'checkbox') {
              el.checked = Array.isArray(val) ? val.includes(el.value) : String(val) === el.value;
            } else if (el.type === 'radio') {
              el.checked = val === el.value;
            } else {
              el.value = val; // Always apply localStorage value (including empty)
            }
            if (el.tagName === 'TEXTAREA' || el.type === 'text') {
              el.dispatchEvent(new Event('input', { bubbles: true }));
            }
          });
        }
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
          el.value = stored; // Always apply localStorage value (including empty)
        }

        if (el.tagName === 'TEXTAREA' || el.type === 'text') {
          el.dispatchEvent(new Event('input', { bubbles: true }));
        }
      });
    });

    updateSignaturePreview5();
  };

  const saveCache = () => {
    const data = {};
    // Always capture row count first (critical for reconstruction)
    try {
      const tbody = document.getElementById('remarks-rows');
      if (tbody) {
        const rows = Array.from(tbody.querySelectorAll('tr')).slice(1); // skip instructions
        data._row_count = rows.length;
      }
    } catch (_) {}

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

    // Capture accomplishments per work-entry to preserve exact positions
    try {
      const tbody = document.getElementById('remarks-rows');
      if (tbody) {
        const rows = Array.from(tbody.querySelectorAll('tr')).slice(1); // skip instructions
        const accPerRow = rows.map(tr => {
          const container = tr.querySelector('#accomplishments-container') || tr.querySelector('.accomplishments-container');
          if (!container) return [];
          const inputs = Array.from(container.querySelectorAll('input[name="accomplishments[]"]'));
          return inputs.map(i => i.value || '');
        });
        data.accomplishments_indexed = accPerRow;
      }
    } catch (_) {}

    // Debug: Log the key fields we're trying to save
    console.log('[saveCache] duration[]:', data['duration[]']);
    console.log('[saveCache] position_title[]:', data['position_title[]']);
    console.log('[saveCache] office_unit[]:', data['office_unit[]']);
    console.log('[saveCache] immediate_supervisor[]:', data['immediate_supervisor[]']);
    console.log('[saveCache] agency_location[]:', data['agency_location[]']);
    
    // Ensure empty values are preserved (don't filter out empty strings)
    const criticalFields = ['duration[]', 'position_title[]', 'office_unit[]', 'immediate_supervisor[]', 'agency_location[]'];
    criticalFields.forEach(field => {
      if (!(field in data)) {
        data[field] = [];
      }
    });

    try {
      localStorage.setItem(storageKey, JSON.stringify(data));
      console.log('[saveCache] saved to localStorage');
    } catch (err) {
      console.warn('localStorage quota exceeded, skipping cache save', err);
    }
  };

  const autoSaveToServer = (() => {
    // Throttled autosave similar to Form 1 to avoid spamming the server
    let timer;
    let failureCount = 0;
    const baseDelay = 1200;
    const maxDelay = 8000;
    const maxRetries = 3;
    const showOverlay = (flag) => {
      if (!autosaveOverlay) return;
      autosaveOverlay.classList.toggle('hidden', !flag);
    };

    const send = () => {
      const formData = new FormData(form);
      // Include per-row accomplishments in autosave payload
      try {
        const tbody = document.getElementById('remarks-rows');
        if (tbody) {
          const rows = Array.from(tbody.querySelectorAll('tr')).slice(1);
          rows.forEach((tr, rowIdx) => {
            const container = tr.querySelector('#accomplishments-container') || tr.querySelector('.accomplishments-container');
            if (!container) return;
            const inputs = Array.from(container.querySelectorAll('input[name="accomplishments[]"]'));
            inputs.forEach(input => {
              formData.append(`accomplishments_indexed[${rowIdx}][]`, input.value || '');
            });
          });
        }
      } catch (_) {}

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
          // Session/CSRF issues – show overlay and stop exponential retrying
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
          const delay = Math.min(baseDelay * Math.pow(2, failureCount - 1), maxDelay);
          setTimeout(send, delay);
        }
      });
    };

    return () => {
      clearTimeout(timer);
      // Small debounce window so multiple keystrokes are batched into one autosave
      timer = setTimeout(send, 800);
    };
  })();

  const requiredFields = Array.from(form.querySelectorAll('[required]'));

  const MAX_REMARKS_CHARS = 60 * 1024; // ~60 KB cap (below DB text limit)

  const updateRemarksCounter = () => {
    const counterEl = document.getElementById('remarksCounter');
    if (!counterEl) return;

    let total = 0;
    // Count ALL text inputs and textareas within the work experience table
    const workExperienceTable = document.querySelector('#remarks-rows');
    if (workExperienceTable) {
      const fields = workExperienceTable.querySelectorAll(
        'input[type="text"], textarea'
      );
      fields.forEach((f) => {
        if (f.disabled) return;
        const v = f.value || '';
        total += v.length;
      });
    }
    
    // Also count the main remarks field if it exists
    const remarksFields = form.querySelectorAll('textarea[name="remarks[]"]');
    remarksFields.forEach((f) => {
      if (f.disabled) return;
      const v = f.value || '';
      total += v.length;
    });

    counterEl.textContent = `${total.toLocaleString()} / ${MAX_REMARKS_CHARS.toLocaleString()}`;
  };

  const getRemarks = () => Array.from(form.querySelectorAll('textarea[name="remarks[]"]'));

  const validateRequired = () => {
    const hasEmptyRemarks = getRemarks().some(t => !isFilled(t));

    const hasMissingRequired = requiredFields.some(el => {
      if (el.disabled || el.readOnly) return false;
      if (el.offsetParent === null) return false;
      return !isFilled(el);
    });

    const hasMissing = hasEmptyRemarks || hasMissingRequired;

    if (submitBtn) {
      submitBtn.disabled = hasMissing;
      if (hasMissing) {
        submitBtn.setAttribute('aria-disabled', 'true');
        submitBtn.classList.add('opacity-50','cursor-not-allowed','pointer-events-none');
      } else {
        submitBtn.removeAttribute('aria-disabled');
        submitBtn.classList.remove('opacity-50','cursor-not-allowed','pointer-events-none');
      }
    }
  };

  const attachReactive = (el) => {
    // Treat all fields inside the work-experience table (and any remarks[] fields)
    // as part of a single global character budget.
    const isRemarksField = el.name === 'remarks[]' || !!el.closest('#remarks-rows');
    autoSize(el);
    const enforceLimit = () => {
      if (!isRemarksField) return;

      // Compute total characters across all relevant fields (same set as the counter)
      let total = 0;

      const workExperienceTable = document.querySelector('#remarks-rows');
      if (workExperienceTable) {
        const fields = workExperienceTable.querySelectorAll('input[type="text"], textarea');
        fields.forEach((f) => {
          if (f.disabled) return;
          const v = f.value || '';
          total += v.length;
        });
      }

      const remarksFields = form.querySelectorAll('textarea[name="remarks[]"]');
      remarksFields.forEach((f) => {
        if (f.disabled) return;
        const v = f.value || '';
        total += v.length;
      });

      if (total <= MAX_REMARKS_CHARS) return;

      // We're over budget – trim the current field's value so that
      // the overall sum never exceeds MAX_REMARKS_CHARS.
      const currentValue = el.value || '';
      const overBy = total - MAX_REMARKS_CHARS;
      if (overBy <= 0) return;

      const newLength = Math.max(0, currentValue.length - overBy);
      if (newLength >= currentValue.length) return;

      const { selectionStart, selectionEnd } = el;
      el.value = currentValue.slice(0, newLength);

      // Restore cursor position as close to the end as possible
      if (typeof selectionStart === 'number' && typeof selectionEnd === 'number') {
        const pos = Math.min(newLength, newLength);
        el.selectionStart = pos;
        el.selectionEnd = pos;
      }
    };

    // Input/change handlers (persist() is called globally on form input event)
    el.addEventListener('input', () => {
      enforceLimit();
      autoSize(el);
      validateRequired();
      updateRemarksCounter();
    });
    el.addEventListener('change', () => {
      enforceLimit();
      autoSize(el);
      validateRequired();
      updateRemarksCounter();
    });

    if (isRemarksField) enforceLimit();
  };

  // Persist function: save to localStorage immediately, then autosave to server
  let hasUserInput = false;
  const persist = () => {
    saveCache(); // Synchronous localStorage save (instant)
    if (hasUserInput) {
      autoSaveToServer(); // Async server save (non-blocking)
    }
  };

  // Expose functions to global scope for accomplishment script
  window.attachReactive = attachReactive;
  window.saveCache = saveCache;
  window.autoSaveToServer = autoSaveToServer;
  window.persist = persist;
  window.createAccomplishmentInput = createAccomplishmentInput;
  window.updateRemarksCounter = updateRemarksCounter;

  // Initial hooks
  getRemarks().forEach(attachReactive);
  Array.from(form.querySelectorAll('input[type="text"], textarea')).forEach(attachReactive);

  loadCache({ ...(draftData || {}), ...(sessionData || {}) });
  updateSignaturePreview5();
  validateRequired();
  updateRemarksCounter();

  // Persist merged cache once on load so fast refresh keeps latest values
  saveCache();
  
  // If we have hydrated data, push to server once
  const initialPayload = { ...(draftData || {}), ...(sessionData || {}) };
  const hasHydratedData = initialPayload && Object.keys(initialPayload).length > 0;
  if (hasHydratedData) {
    hasUserInput = true;
    autoSaveToServer();
  }

  // Trigger persist on every form input (like Form 1)
  form.addEventListener('input', (e) => {
    hasUserInput = true;
    persist();
  });

  if (submitBtn) {
    submitBtn.addEventListener('click', (e) => {
      if (submitBtn.getAttribute('aria-disabled') === 'true') {
        e.preventDefault();
        e.stopPropagation();
        validateRequired();
      }
    });
  }

  // Check for master date from form1 and apply it
  function ensureMasterDateSeed() {
    const date5Input = document.querySelector('input[name="date5"]');
    const existing = localStorage.getItem('pds_master_date');
    const candidate = existing || date5Input?.value;
    if (candidate && !existing) {
      console.log('Seeding master date from form5/input value:', candidate);
      localStorage.setItem('pds_master_date', candidate);
    }
    return candidate || null;
  }

  function syncFromForm1() {
    const masterDate = ensureMasterDateSeed();
    if (masterDate) {
      console.log('Using master date:', masterDate);
      const date5Input = document.querySelector('input[name="date5"]');
      if (date5Input && date5Input.value !== masterDate) {
        console.log('Updating form5 date to:', masterDate);
        date5Input.value = masterDate;
        // Trigger change event to save to cache
        date5Input.dispatchEvent(new Event('change', { bubbles: true }));
      }
    }
  }

  // Check for master date when page loads
  syncFromForm1();

  // Also check periodically in case user navigates back from form1
  setInterval(syncFromForm1, 1000);
});
</script>

<!-- =============================== -->
<!-- PAGE CONTENT -->
<!-- =============================== -->

<div class="max-w-6xl mx-auto p-4 font-serif text-sm">

<table>
  <th class="flex text-left font-['Arial_Narrow','Arial',sans-serif] italic font-semibold">
    Attachment to CS Form No. 212
  </th>
</table>

<table data-section="work_experience_sheet" class="border-black w-full font-['Arial_Narrow','Arial',sans-serif] border-2">

<tr>
  <th class="text-base font-semibold italic bg-[#8a8a8a] text-white border border-black border-b-2">
    WORK EXPERIENCE SHEET
  </th>
</tr>

<tbody id="remarks-rows">

<tr>
<td class="border-t-2 h-20 p-3 text-base italic border-b-2 border-black">
<span class="p-1 font-semibold">Instructions:</span>
1. Include only the work experiences relevant to the position being applied to.
<p class="p-2 ml-20">
2. The duration should include start and finish dates, if known, month in abbreviated form, if known, and year in full. For the current position, use the word Present, e.g., 1998-Present. Work experience should be listed from most recent first.  
</p>
</td>
</tr>

<tr>
<td class="border-2 border-black relative align-top">
<div class="p-3 text-sm">
    <ul class="list-none space-y-2">
        <li>
            <p class="font-semibold inline">Duration:</p>
            <input type="text" name="duration[]" class="border border-gray-300 bg-transparent text-sm p-1 focus:outline-none ml-2" style="width: calc(100% - 100px);">
        </li>
        <li>
            <p class="font-semibold inline">Position:</p>
            <input type="text" name="position_title[]" class="border border-gray-300 bg-transparent text-sm p-1 focus:outline-none ml-2" style="width: calc(100% - 100px);">
        </li>
        <li>
            <p class="font-semibold inline">Name of Office/Unit:</p>
            <input type="text" name="office_unit[]" class="border border-gray-300 bg-transparent text-sm p-1 focus:outline-none ml-2" style="width: calc(100% - 180px);">
        </li>
        <li>
            <p class="font-semibold inline">Immediate Supervisor:</p>
            <input type="text" name="immediate_supervisor[]" class="border border-gray-300 bg-transparent text-sm p-1 focus:outline-none ml-2" style="width: calc(100% - 180px);">
        </li>
        <li>
            <p class="font-semibold inline">Name of Agency/Organization and Location:</p>
            <input type="text" name="agency_location[]" class="border border-gray-300 bg-transparent text-sm p-1 focus:outline-none ml-2 mt-1" style="width: calc(100% - 280px);">
        </li>
        <li class="mt-3">
            <p class="font-semibold">List of Accomplishments and Contributions (if any)</p>
            <div class="ml-6 mt-2 space-y-1" id="accomplishments-container">
                <input type="text" name="accomplishments[]" class="border border-gray-300 bg-transparent text-sm p-1 focus:outline-none w-full">
                <input type="text" name="accomplishments[]" class="border border-gray-300 bg-transparent text-sm p-1 focus:outline-none w-full">
            </div>
            <div class="ml-6 mt-2 flex justify-end">
                <button type="button" id="add-accomplishment" class="text-blue-500 hover:text-blue-700 font-semibold">+ Add Accomplishment</button>
            </div>
        </li>
        <li class="mt-3">
            <p class="font-semibold">Summary of Actual Duties</p>
            <div class="ml-6 mt-2">
                <textarea name="duties[]" class="border border-gray-300 w-full p-3 resize-none text-sm focus:outline-none bg-transparent" style="min-height:100px; white-space:pre-wrap; overflow:hidden;" aria-label="Summary of Actual Duties"></textarea>
            </div>
        </li>
        <li class="mt-4 pt-2 border-t border-gray-300">
            <div class="ml-6 mt-2 flex justify-end">
                <button type="button" class="remove-work-experience text-red-500 hover:text-red-700 font-semibold">× Remove Work Experience</button>
            </div>
        </li>
    </ul>
</div>
</td>
</tr>

</tbody>
</table>


<script>
document.addEventListener('DOMContentLoaded', function() {
  // Ensure first block also has classes so delegated handlers work across multiple rows
  const firstContainer = document.getElementById('accomplishments-container');
  if (firstContainer) firstContainer.classList.add('accomplishments-container');
  const firstAddBtn = document.getElementById('add-accomplishment');
  if (firstAddBtn) firstAddBtn.classList.add('add-accomplishment');

  // Delegated: add accomplishment in the nearest work block
  document.addEventListener('click', function(e) {
    if (e.target.classList.contains('add-accomplishment')) {
      const li = e.target.closest('li');
      const container = li ? (li.querySelector('#accomplishments-container') || li.querySelector('.accomplishments-container')) : null;
      if (!container) return;

      const { div, input } = window.createAccomplishmentInput();
      container.appendChild(div);
      if (window.attachReactive) window.attachReactive(input);
      // Add direct event listeners for immediate counter updates
      input.addEventListener('input', () => {
        if (window.updateRemarksCounter) window.updateRemarksCounter();
      });
      input.addEventListener('change', () => {
        if (window.updateRemarksCounter) window.updateRemarksCounter();
      });
      // Immediate save after adding accomplishment
      if (window.persist) window.persist();
      // Update counter to include new empty field
      if (window.updateRemarksCounter) window.updateRemarksCounter();
    }
  });

  // Delegated: remove accomplishment with per-block minimum of 2 inputs
  document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-accomplishment')) {
      const fieldDiv = e.target.parentElement;
      const block = e.target.closest('li');
      const container = block ? (block.querySelector('#accomplishments-container') || block.querySelector('.accomplishments-container')) : null;
      if (!container) return;
      if (container.children.length > 2) {
        fieldDiv.remove();
        // Immediate save after removing accomplishment
        if (window.persist) window.persist();
        // Update counter to remove the deleted field
        if (window.updateRemarksCounter) window.updateRemarksCounter();
      } else {
        alert('You must have at least 2 accomplishment fields.');
      }
    }
  });

  // Add Work Experience: clone the original work row (2nd tr in tbody)
  const addWorkBtn = document.getElementById('add-work-experience');
  if (addWorkBtn) {
    addWorkBtn.addEventListener('click', function() {
      const tbody = document.getElementById('remarks-rows');
      if (!tbody) return;
      const rows = tbody.querySelectorAll('tr');
      if (rows.length < 2) return; // need base row to clone
      const baseRow = rows[1];
      const clone = baseRow.cloneNode(true);

      // Clear values in clone
      Array.from(clone.querySelectorAll('input[type="text"], textarea')).forEach(el => {
        el.value = '';
      });

      // Make accomplishment controls class-based in clone to avoid duplicate IDs
      const accContainer = clone.querySelector('#accomplishments-container');
      if (accContainer) {
        accContainer.removeAttribute('id');
        accContainer.classList.add('accomplishments-container');
      } else {
        // if already class-based, ensure it's set
        const accByClass = clone.querySelector('.accomplishments-container');
        if (!accByClass) {
          const fallback = clone.querySelector('div.ml-6.mt-2.space-y-1');
          if (fallback) fallback.classList.add('accomplishments-container');
        }
      }
      const addBtn = clone.querySelector('#add-accomplishment');
      if (addBtn) {
        addBtn.removeAttribute('id');
        addBtn.classList.add('add-accomplishment');
      }

      // Ensure at least two accomplishment inputs exist in each new block
      const container = clone.querySelector('.accomplishments-container') || clone.querySelector('#accomplishments-container');
      if (container) {
        while (container.children.length) container.removeChild(container.lastChild);
        const { div: div1, input: input1 } = window.createAccomplishmentInput();
        const { div: div2, input: input2 } = window.createAccomplishmentInput();
        container.appendChild(div1);
        container.appendChild(div2);
        if (window.attachReactive) {
          window.attachReactive(input1);
          window.attachReactive(input2);
        }
        // Add direct event listeners for immediate counter updates
        [input1, input2].forEach(input => {
          input.addEventListener('input', () => {
            if (window.updateRemarksCounter) window.updateRemarksCounter();
          });
          input.addEventListener('change', () => {
            if (window.updateRemarksCounter) window.updateRemarksCounter();
          });
        });
      }

      tbody.appendChild(clone);
      // Attach reactive to new inputs
      const newInputs = clone.querySelectorAll('input[type="text"], textarea');
      Array.from(newInputs).forEach(el => {
        if (window.attachReactive) window.attachReactive(el);
        // Also add direct event listeners for immediate counter updates
        el.addEventListener('input', () => {
          if (window.updateRemarksCounter) window.updateRemarksCounter();
        });
        el.addEventListener('change', () => {
          if (window.updateRemarksCounter) window.updateRemarksCounter();
        });
      });
      // Immediate save after adding work experience row
      if (window.persist) window.persist();
      // Update counter to include new row's fields
      if (window.updateRemarksCounter) window.updateRemarksCounter();
    });
  }

  // Delegated: remove entire work experience row, keep at least one
  document.addEventListener('click', function(e) {
    if (e.target.classList.contains('remove-work-experience')) {
      const tbody = document.getElementById('remarks-rows');
      if (!tbody) return;
      const rows = Array.from(tbody.querySelectorAll('tr'));
      const workRows = rows.slice(1); // exclude instructions row
      if (workRows.length <= 1) {
        alert('You must have at least one work experience entry.');
        return;
      }
      const tr = e.target.closest('tr');
      if (tr && tbody.contains(tr)) {
        tr.remove();
        // Immediate save after removing work experience row
        if (window.persist) window.persist();
        // Update counter to remove the deleted row's fields
        if (window.updateRemarksCounter) window.updateRemarksCounter();
      }
    }
  });
});
</script>

<div class="max-w-6xl mx-auto flex justify-end text-xs text-gray-600 pr-3 pb-2" id="remarksCounter">0 / 61,440</div>


<div class="mt-4 flex justify-end">
  <button type="button" id="add-work-experience" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 font-['Arial_Narrow','Arial',sans-serif] text-sm font-semibold">+ Add Work Experience</button>
</div>

<!-- SIGNATURE -->
<div class="w-full flex justify-end mt-[3cm] pr-6">
  <div class="w-[350px] text-center">
    <label id="signatureBox5" data-signature-cell class="signature-box block h-36 w-full cursor-default">
      <input
        type="file"
        name="signature_file"
        id="signatureFileInput5"
        accept="image/*"
        class="absolute inset-0 w-full h-full opacity-0 cursor-not-allowed pointer-events-none"
        disabled
      >
      <img
        id="signaturePreviewImg5"
        src="{{ !empty($signaturePath) ? Storage::url($signaturePath) : '' }}"
        alt="Signature preview"
        class="absolute inset-0 w-full h-full object-contain {{ empty($signaturePath) ? 'hidden' : '' }}"
      >
      <div id="signaturePlaceholder5" class="absolute inset-0" aria-hidden="true"></div>
    </label>
    <input type="hidden" name="signature_path" id="signature_path5" value="{{ $signaturePath ?? '' }}">
    <input type="hidden" name="signature_data" id="signature_data5">
     <div class="w-100 border-b-2 border-black mb-1"></div>
    <div class="mt-2 text-sm">(Signature over Printed Name)</div>
  </div>
</div>

<!-- DATE -->
<div class="w-full flex justify-end mt-[1cm] pr-6 font-['Arial_Narrow','Arial',sans-serif]">
<div class="w-[350px] text-center relative">

<div class="border-b-2 border-black w-full absolute bottom-6 left-0"></div>

<div class="flex justify-center items-center h-10 relative">
<input type="date" name="date5" required class="w-full h-full text-center bg-transparent border-none text-base px-2 py-1 focus:outline-none focus:ring-0">
</div>

<div class="text-sm">DATE</div>
</div>
</div>

<div class="mt-5 flex justify-end mr-2 text-sm font-['Arial_Narrow','Arial',sans-serif]">
CS FORM 212 (Revised 2025), Page 5 of 5
</div>

<a href="{{ route('pds.form4') }}"
class="px-4 py-2 bg-blue-600 text-white rounded">
Previous Page</a>

<div class="mt-3 flex justify-end">
<button id="submit-pds-btn"
  type="button"
  disabled
  data-submitpds-trigger
  data-submitpds-form="pds-form5"
  aria-disabled="true"
  class="px-5 py-2 bg-green-600 text-white rounded opacity-50 cursor-not-allowed pointer-events-none">
  Submit PDS
</button>
</div>
  
</div>
</form>
<x-submitpds />

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
