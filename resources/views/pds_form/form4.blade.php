<x-app-layout>
<form id="pds-form4" method="POST" action="{{ route('pds.saveStep', [4], false) }}" enctype="multipart/form-data">
@csrf
  <style>
    body { font-family: 'Arial Narrow','Arial',sans-serif; }
    table { border-collapse: collapse; }
    .border-3 { border: 3px solid #000; }
    .border-2 { border: 2px solid #000; }
    .border-black { border-color: #000; }
    input[type="text"], textarea { width: 100%; background: transparent; border: none; border-bottom: 1px solid #000; outline: none; resize: none; overflow: hidden; padding: 2px 0; line-height: 1.2; font-family: 'Arial Narrow','Arial',sans-serif; font-size: inherit; }
    textarea:focus { outline: none; box-shadow: none; }
    input:focus { outline: none; box-shadow: none; }
    /* Mobile responsiveness: allow horizontal scroll and tighter spacing */
    .pds-responsive { overflow-x: auto; }
    .pds-sheet { min-width: 980px; }
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
    @media (max-width: 768px) {
      .pds-sheet { min-width: 760px; }
      td, th { padding: 4px; }
      .max-w-6xl { padding: 0.75rem; }
    }
    @media (max-width: 640px) {
      .pds-sheet { min-width: 680px; }
      td, th { padding: 3px; }
    }
    @media print { * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; } }

    textarea { border: none; outline: none; padding: 8px; width: 100%; font: inherit; resize: none; background: transparent; line-height: 1.3; display: block; box-sizing: border-box; overflow: hidden; white-space: pre-wrap; word-break: break-word; min-height: 38px; height: auto; }
    textarea:focus { outline: none; box-shadow: none; }
  </style>
  <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/selfie_segmentation/selfie_segmentation.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@mediapipe/camera_utils/camera_utils.js"></script>
  <script>
    // PHOTO capture helpers with face detection gating
    let photoStream;
    let faceModelsLoaded = false;
    let detectTimer = null;
    let brightnessOkFlag = false;
    const minDetectionScore = 0.9;
    let identifying = false;
    let segmentation;
    let segmentationScriptPromise = null;
    let segmentationReady = false;
    let guideReady = false;
    const photoConstraints = { video: { width: { ideal: 1280 }, height: { ideal: 720 } } };
    const photoCacheKey = 'pds_form4_photo_data';
    const statusEl = () => document.getElementById('photoStatus');
    const brightnessThreshold = 100;
    const overlayEl = () => document.getElementById('photoOverlay');
    const guideCircle = () => document.querySelector('#photoOverlay .guide-circle');
    const captureBtn = () => document.getElementById('photoCaptureBtn');
    const segmentationBase = 'https://cdn.jsdelivr.net/npm/@mediapipe/selfie_segmentation/';

    function loadSegmentationBundle() {
      if (segmentationScriptPromise) return segmentationScriptPromise;
      segmentationScriptPromise = new Promise((resolve, reject) => {
        if (window.SelfieSegmentation || window.selfieSegmentation) return resolve();
        const script = document.createElement('script');
        script.src = `${segmentationBase}selfie_segmentation.js`;
        script.async = true;
        script.onload = () => resolve();
        script.onerror = () => {
          segmentationScriptPromise = null;
          reject(new Error('Failed to load Mediapipe SelfieSegmentation'));
        };
        document.head.appendChild(script);
      });
      return segmentationScriptPromise;
    }

    async function waitForFaceApi(retries = 50) {
      if (window.faceapi) return true;
      let attempts = 0;
      while (!window.faceapi && attempts < retries) {
        await new Promise(res => setTimeout(res, 100));
        attempts++;
      }
      return !!window.faceapi;
    }

    async function loadFaceModels() {
      if (faceModelsLoaded) return;
      const ready = await waitForFaceApi();
      if (!ready) throw new Error('faceapi not loaded');
      await Promise.all([
        faceapi.nets.tinyFaceDetector.loadFromUri('/models')
      ]);
      faceModelsLoaded = true;
    }

    function setStatus(msg, color = 'text-rose-600') {
      const el = statusEl();
      if (!el) return;
      el.textContent = msg || '';
      el.className = `text-xs font-semibold ${color} text-center`;
    }

    async function startPhotoCamera() {
      const video = document.getElementById('photoVideo');
      if (!video) return;
      try {
        await loadFaceModels();
        photoStream = await navigator.mediaDevices.getUserMedia(photoConstraints);
        video.srcObject = photoStream;
        await video.play();
        video.style.transform = 'scaleX(-1)';
        video.classList.remove('hidden');
        overlayEl()?.classList.remove('hidden');
        document.getElementById('photoStartBtn')?.classList.add('hidden');
        guideReady = false;
        setGuideState(false);
        captureBtn()?.classList.remove('hidden');
        setStatus('Center your face in the guide.');
        startDetectionLoop();
      } catch (err) {
        alert('Unable to access camera. Please check permissions or use file upload.');
        console.error(err);
      }
    }

    function stopPhotoCamera() {
      if (photoStream) {
        photoStream.getTracks().forEach(t => t.stop());
        photoStream = null;
      }
      stopDetectionLoop();
      setStatus('');
      overlayEl()?.classList.add('hidden');
      captureBtn()?.classList.add('hidden');
      setGuideState(false);
    }

    function startDetectionLoop() {
      stopDetectionLoop();
      detectTimer = setInterval(runDetectionTick, 700);
    }

    function stopDetectionLoop() {
      if (detectTimer) {
        clearInterval(detectTimer);
        detectTimer = null;
      }
    }

    function setGuideState(isReady) {
      guideReady = !!isReady;
      const circle = guideCircle();
      const btn = captureBtn();
      if (circle) {
        circle.className = 'absolute w-[70%] h-[70%] rounded-full border-4 border-dashed guide-circle';
        circle.style.borderColor = isReady ? '#10b981' : '#f43f5e';
        circle.style.boxShadow = isReady
          ? '0 0 0 2px rgba(16,185,129,0.25)'
          : '0 0 0 2px rgba(244,63,94,0.25)';
      }
      if (btn) {
        if (isReady) {
          btn.removeAttribute('disabled');
        } else {
          btn.setAttribute('disabled', 'true');
        }
      }
    }

    async function checkEligibility() {
      const video = document.getElementById('photoVideo');
      if (!video) return false;
      const detection = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions());
      if (!detection) {
        setStatus('No clear face detected.');
        setGuideState(false);
        return false;
      }

      const box = detection.box;
      const ratioW = box.width / (video.videoWidth || 1);
      const ratioH = box.height / (video.videoHeight || 1);
      const faceRatio = Math.max(ratioW, ratioH);
      if (faceRatio < 0.45) {
        setStatus('Move closer so your face fills the circle.');
        setGuideState(false);
        return false;
      }
      if (faceRatio > 0.55) {
        setStatus('Move back slightly so your face fits the circle.');
        setGuideState(false);
        return false;
      }

      const faceCenterX = (box.x + box.width / 2) / (video.videoWidth || 1);
      const faceCenterY = (box.y + box.height / 2) / (video.videoHeight || 1);
      const dx = Math.abs(faceCenterX - 0.5);
      const dy = Math.abs(faceCenterY - 0.6);
      const dyWeight = 1.15;
      const faceRadius = faceRatio / 2;
      const circleRadius = 0.5;
      const margin = 0.0;
      const maxCenterDistance = Math.max(0.005, circleRadius - margin - faceRadius);
      const centerDistance = Math.hypot(dx, dy * dyWeight);
      const strictDistance = maxCenterDistance * 0.3;
      if (centerDistance > strictDistance) {
        setStatus('Center your face in the guide.');
        setGuideState(false);
        return false;
      }

      const brightness = checkLighting(video);
      brightnessOkFlag = brightness >= brightnessThreshold + 5;
      if (!brightnessOkFlag) {
        setStatus('Lighting too low. Move to a brighter spot.');
        setGuideState(false);
        return false;
      }

      if (detection.score && detection.score < minDetectionScore) {
        setStatus('Face not clear. Hold steady.');
        setGuideState(false);
        return false;
      }

      setStatus('Face looks good. Tap Capture.', 'text-emerald-600');
      setGuideState(true);
      return true;
    }

    async function runDetectionTick() {
      const ok = await checkEligibility();
      if (!ok) return;
      setStatus('Face looks good. Tap Capture.', 'text-emerald-600');
      setGuideState(true);
    }

    function checkLighting(video) {
      const canvas = document.createElement('canvas');
      canvas.width = video.videoWidth;
      canvas.height = video.videoHeight;
      const ctx = canvas.getContext('2d');
      ctx.drawImage(video, 0, 0);
      const data = ctx.getImageData(0, 0, canvas.width, canvas.height).data;
      let brightness = 0;
      for (let i = 0; i < data.length; i += 4) {
        brightness += (data[i] + data[i + 1] + data[i + 2]) / 3;
      }
      brightness /= data.length / 4;
      return brightness;
    }

    function dataUrlToFile(dataUrl, fileName) {
      const arr = dataUrl.split(',');
      const mime = arr[0].match(/:(.*?);/)[1];
      const bstr = atob(arr[1]);
      let n = bstr.length;
      const u8arr = new Uint8Array(n);
      while (n--) u8arr[n] = bstr.charCodeAt(n);
      return new File([u8arr], fileName, { type: mime });
    }

    async function capturePhoto() {
      if (!faceModelsLoaded) {
        setStatus('Loading face detection… please wait.');
        return;
      }
      if (identifying) return;
      const video = document.getElementById('photoVideo');
      const canvas = document.createElement('canvas');
      const img = document.getElementById('photoPreview');
      const placeholder = document.getElementById('photoPlaceholder');
      const hiddenInput = document.getElementById('photoData');
      const fileInput = document.getElementById('photoFile');
      if (!video || !img || !placeholder || !hiddenInput || !fileInput) return;
      if (!photoStream) return alert('Camera is not active. Click "Open camera" first.');

      identifying = true;
      captureBtn()?.setAttribute('disabled', 'true');
      setStatus('Identifying…', 'text-sky-600');

      try {
        canvas.width = video.videoWidth || 640;
        canvas.height = video.videoHeight || 480;
        const ctx = canvas.getContext('2d');
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
        const dataUrlRaw = canvas.toDataURL('image/jpeg', 0.9);

        const ok = await checkEligibility();
        if (!ok || !brightnessOkFlag) {
          setStatus('Center and lighten your face, then recapture.');
          setGuideState(false);
          return;
        }

        let dataUrl = dataUrlRaw;

      // Optional background removal via Mediapipe Selfie Segmentation (post-capture)
      try {
        setStatus('Removing background…', 'text-sky-600');
        const cutout = await removeBackgroundWithSegmentation(canvas, dataUrl);
        if (cutout) dataUrl = cutout;
        // Compress segmented output to reduce upload size
        const tmp = document.createElement('canvas');
        tmp.width = canvas.width;
        tmp.height = canvas.height;
        const tctx = tmp.getContext('2d');
        const imgObj = new Image();
        await new Promise((res) => {
          imgObj.onload = res;
          imgObj.src = dataUrl;
        });
        tctx.drawImage(imgObj, 0, 0, tmp.width, tmp.height);
        dataUrl = tmp.toDataURL('image/jpeg', 0.7);
      } catch (e) {
        console.warn('Background removal failed, using original', e);
      }

      img.src = dataUrl;
      img.classList.remove('hidden');
      placeholder.classList.add('hidden');
      hiddenInput.value = dataUrl;
      try { localStorage.setItem(photoCacheKey, dataUrl); } catch (e) { console.warn('Photo cache failed', e); }
      hiddenInput.dispatchEvent(new Event('input', { bubbles: true }));

      const file = dataUrlToFile(dataUrl, 'photo.png');
      const transfer = new DataTransfer();
      transfer.items.add(file);
      fileInput.files = transfer.files;

      stopPhotoCamera();
      document.getElementById('photoStartBtn')?.classList.remove('hidden');
      video.classList.add('hidden');
      overlayEl()?.classList.add('hidden');
      captureBtn()?.classList.add('hidden');
      setGuideState(false);
    }
      finally {
        identifying = false;
        if (photoStream) {
          captureBtn()?.removeAttribute('disabled');
        }
      }
    }

    async function ensureSegmentation() {
      if (segmentationReady) return;
      await loadSegmentationBundle();
      const ns = window.SelfieSegmentation || window.selfieSegmentation || {};
      const SegClass = ns.SelfieSegmentation || ns; // handle both namespace and direct constructor
      if (typeof SegClass !== 'function') throw new Error('SelfieSegmentation constructor not found');
      segmentation = new SegClass({
        locateFile: (file) => `${segmentationBase}${file}`
      });
      segmentation.setOptions({ modelSelection: 1 }); // higher quality
      segmentationReady = true;
    }

    function runSegmentation(imageCanvas) {
      return new Promise((resolve, reject) => {
        if (!segmentationReady) return reject(new Error('Segmentation not ready'));
        segmentation.onResults((results) => resolve(results));
        segmentation.send({ image: imageCanvas });
      });
    }

    async function removeBackgroundWithSegmentation(imageCanvas, fallbackDataUrl) {
      try {
        await ensureSegmentation();
      } catch (e) {
        console.warn('Segmentation unavailable, using original', e);
        return fallbackDataUrl;
      }
      const results = await runSegmentation(imageCanvas);
      if (!results || !results.segmentationMask) return fallbackDataUrl;

      const w = imageCanvas.width;
      const h = imageCanvas.height;

      // Create blurred mask and threshold it (alpha channel)
      const maskCanvas = document.createElement('canvas');
      maskCanvas.width = w;
      maskCanvas.height = h;
      const mctx = maskCanvas.getContext('2d');
      mctx.filter = 'blur(3px)';
      mctx.drawImage(results.segmentationMask, 0, 0, w, h);
      mctx.filter = 'none';
      const maskData = mctx.getImageData(0, 0, w, h);
      const data = maskData.data;
      const threshold = 0.8 * 255; // remove weak pixels
      for (let i = 0; i < data.length; i += 4) {
        const alpha = data[i + 3];
        const a = alpha > threshold ? 255 : 0;
        data[i + 3] = a;
        data[i] = data[i + 1] = data[i + 2] = 255;
      }
      mctx.putImageData(maskData, 0, 0);
      // slight feather
      mctx.filter = 'blur(1px)';
      mctx.drawImage(maskCanvas, 0, 0, w, h);
      mctx.filter = 'none';

      // Composite: apply mask then white background
      const personCanvas = document.createElement('canvas');
      personCanvas.width = w;
      personCanvas.height = h;
      const pctx = personCanvas.getContext('2d');
      pctx.drawImage(imageCanvas, 0, 0, w, h);
      pctx.globalCompositeOperation = 'destination-in';
      pctx.drawImage(maskCanvas, 0, 0, w, h);
      pctx.globalCompositeOperation = 'destination-over';
      pctx.fillStyle = '#ffffff';
      pctx.fillRect(0, 0, w, h);
      pctx.globalCompositeOperation = 'source-over';

      const output = document.createElement('canvas');
      output.width = w;
      output.height = h;
      const outCtx = output.getContext('2d');
      outCtx.drawImage(personCanvas, 0, 0, w, h);

      return output.toDataURL('image/png');
    }

    let validateRequired;

    function applyPhotoDataUrl(dataUrl) {
      const img = document.getElementById('photoPreview');
      const placeholder = document.getElementById('photoPlaceholder');
      const hiddenInput = document.getElementById('photoData');
      const fileInput = document.getElementById('photoFile');
      const video = document.getElementById('photoVideo');
      if (!dataUrl || !img || !placeholder || !hiddenInput || !fileInput) return;

      img.src = dataUrl;
      img.classList.remove('hidden');
      placeholder.classList.add('hidden');
      hiddenInput.value = dataUrl;
      try { localStorage.setItem(photoCacheKey, dataUrl); } catch (e) { console.warn('Photo cache failed', e); }

      const file = dataUrlToFile(dataUrl, 'photo.jpg');
      const transfer = new DataTransfer();
      transfer.items.add(file);
      fileInput.files = transfer.files;

      stopPhotoCamera();
      if (video) {
        video.classList.add('hidden');
      }
      if (typeof validateRequired === 'function') validateRequired();
    }

    function previewPhotoFromFile(file) {
      const hiddenInput = document.getElementById('photoData');
      if (!file || !hiddenInput) return;

      const reader = new FileReader();
      reader.onload = (e) => {
        const dataUrl = e.target?.result || '';
        applyPhotoDataUrl(dataUrl);
      };
      reader.readAsDataURL(file);
    }

    // thumbmark
    function previewThumb(event) {
      const file = event?.target?.files?.[0];
      if (!file) return;

      const img = document.getElementById('thumbPreview');
      const placeholder = document.getElementById('thumbPlaceholder');

      const reader = new FileReader();
      reader.onload = (e) => {
        if (img) {
          img.src = e.target?.result || '';
          img.classList.remove('hidden');
        }
        if (placeholder) {
          placeholder.classList.add('hidden');
        }
      };
      reader.readAsDataURL(file);
    }

    // Auto-grow textareas used in the references table and ID/date fields
    function autoSize(el) {
      el.style.height = 'auto';
      el.style.height = `${el.scrollHeight}px`;
    }

    document.addEventListener('DOMContentLoaded', () => {
      const form = document.querySelector('#pds-form4');
      if (!form) return;

      const storageBase = "{{ asset('storage') }}/";
      const initialSignaturePath = @json($signaturePath ?? '');

      const signatureDataInput4 = document.getElementById('signature_data4');
      const signaturePathInput4 = document.getElementById('signature_path4');
      const signaturePreviews4 = [
        document.getElementById('signaturePreviewImg4a'),
        document.getElementById('signaturePreviewImg4b')
      ];
      const signaturePlaceholders4 = [
        document.getElementById('signaturePlaceholder4a'),
        document.getElementById('signaturePlaceholder4b')
      ];
      const signatureBoxes4 = [
        document.getElementById('signatureBox4a'),
        document.getElementById('signatureBox4b')
      ];

      const buildSignatureUrl4 = (path) => {
        if (!path) return '';
        const cleaned = path.replace(/^public\//, '');
        return storageBase + cleaned;
      };

      const updateSignaturePreview4 = () => {
        const dataUrl = signatureDataInput4?.value;
        const pathVal = signaturePathInput4?.value || initialSignaturePath;
        const url = dataUrl || buildSignatureUrl4(pathVal);
        signaturePreviews4.forEach((img, idx) => {
          if (!img) return;
          if (url) {
            img.src = url;
            img.classList.remove('hidden');
            signaturePlaceholders4[idx]?.classList.add('hidden');
            signatureBoxes4[idx]?.classList.add('signature-has-image');
          } else {
            img.classList.add('hidden');
            signaturePlaceholders4[idx]?.classList.remove('hidden');
            signatureBoxes4[idx]?.classList.remove('signature-has-image');
          }
        });
      };

      window.handleSignatureUpload4 = (file) => {
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
            if (signatureDataInput4) signatureDataInput4.value = output;
            if (signaturePathInput4) signaturePathInput4.value = '';
            updateSignaturePreview4();
          };
          img.src = e.target.result;
        };
        reader.readAsDataURL(file);
      };

      // Auto-size all textareas so content grows the box instead of showing scrollbars
      const autoSizeAllTextareas = () => {
        document.querySelectorAll('textarea').forEach(el => {
          autoSize(el);
          el.removeEventListener('input', el._autoSizeHandler || (() => {}));
          const handler = () => autoSize(el);
          el._autoSizeHandler = handler;
          el.addEventListener('input', handler);
        });
      };

      autoSizeAllTextareas();
      document.querySelectorAll('.ref-field, .grow-field').forEach(el => {
        autoSize(el);
        el.addEventListener('input', () => autoSize(el));
      });

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

      const loadCachedPhoto = () => {
        const hiddenInput = document.getElementById('photoData');
        if (!hiddenInput || hiddenInput.value) return;
        try {
          const cached = localStorage.getItem(photoCacheKey);
          if (cached) applyPhotoDataUrl(cached);
        } catch (e) {
          console.warn('Photo cache load failed', e);
        }
      };

      loadCachedPhoto();

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
        if (name === 'photo_data' && value) {
          applyPhotoDataUrl(value);
          return;
        }

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

      const names = new Set();
      document.querySelectorAll('input[name$="[]"], textarea[name$="[]"]').forEach(el => {
        const name = el.getAttribute('name');
        if (name) names.add(name);
      });

      // First-row logic for reference table
      const rowGroup = (namesArr) => namesArr.map(n => Array.from(document.querySelectorAll(`[name="${n}"]`))).filter(arr => arr.length).map(arr => arr[0]);
      const referenceFirstRow = rowGroup(['reference_name[]','reference_address[]','reference_contact[]']);
      const referenceRowNames = ['reference_name[]','reference_address[]','reference_contact[]'];
      const govFields = ['gov_id','licence_passport_id','id_issue_date_place'];
      const photoFileInput = document.getElementById('photoFile');
      const photoDataInput = document.getElementById('photoData');

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
        updateSignaturePreview4();
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

      const enforceRowCompleteness = (namesArr, optionalNames = new Set()) => {
        let invalidField = null;
        const columns = namesArr.map(n => Array.from(document.querySelectorAll(`[name="${n}"]`)));
        const maxRows = Math.max(...columns.map(c => c.length));

        for (let row = 0; row < maxRows; row++) {
          const rowFields = columns.map(c => c[row]).filter(Boolean);
          if (!rowFields.length) continue;

          const active = rowFields.filter(f => f && !f.disabled && !f.readOnly);
          if (!active.length) continue;

          const rowHasData = active.some(f => !optionalNames.has(f.name) && !isNA(f.value) && (f.value || '').trim() !== '');
          if (!rowHasData) continue;

          for (const f of active) {
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

      let prevDisableReferences = null;

      const refreshRows = () => {
        const refFirst = referenceFirstRow[0];
        const disableRefs = refFirst ? isNA(refFirst.value) : false;

        disableFollowingRows(['reference_name[]','reference_address[]','reference_contact[]'], disableRefs);

        if (disableRefs) {
          fillRowWithNA(['reference_name[]','reference_address[]','reference_contact[]'], 0);
        } else if (prevDisableReferences === true && disableRefs === false) {
          clearRowNA(['reference_address[]','reference_contact[]'], 0);
        }

        prevDisableReferences = disableRefs;
      };

      const scrollToField = (el) => {
        if (!el) return;
        const nav = document.querySelector('nav');
        const navHeight = nav?.getBoundingClientRect().height || 80;
        const offset = navHeight + 200;
        const anchor = el.closest('td, th, label, div') || el;
        const applyOffset = () => {
          const rect = anchor.getBoundingClientRect();
          const targetY = Math.max(rect.top + window.pageYOffset - offset, 0);
          window.scrollTo({ top: targetY, behavior: 'auto' });
        };
        applyOffset();
        requestAnimationFrame(applyOffset);
        setTimeout(applyOffset, 140);
        setTimeout(applyOffset, 320);
      };

      referenceFirstRow.forEach(f => f?.addEventListener('input', refreshRows));
      refreshRows();

      const nextBtn = document.getElementById('pds4-next');
      const requiredFields = Array.from(document.querySelectorAll('[required]'));

      const isFilled = (el) => {
        if (el.type === 'file') return el.files && el.files.length > 0;
        if (el.type === 'checkbox' || el.type === 'radio') return el.checked;
        const val = (el.value || '').trim();
        if (isNA(val)) return true;
        return val !== '';
      };

      const conditionalGroups = [];
      const seenNames = new Set();
      document.querySelectorAll('input[type="checkbox"][name]').forEach(box => {
        const name = box.getAttribute('name');
        if (!name || seenNames.has(name)) return;
        const boxes = Array.from(document.querySelectorAll(`input[type="checkbox"][name="${name}"]`));
        if (boxes.length < 2) return;
        seenNames.add(name);

        const scopedDetails = Array.from(document.querySelectorAll(`input[type="text"][data-detail-for="${name}"], textarea[data-detail-for="${name}"]`));
        const details = scopedDetails.length ? scopedDetails : (() => {
          const td = box.closest('td');
          return td ? Array.from(td.querySelectorAll('input[type="text"], textarea')).filter(el => !boxes.includes(el)) : [];
        })();

        conditionalGroups.push({ name, boxes, details, detailCache: {}, detailDisabledState: {} });

        boxes.forEach(activeBox => {
          activeBox.addEventListener('change', () => {
            if (activeBox.checked) {
              boxes.forEach(b => { if (b !== activeBox) b.checked = false; });
            }
            validateRequired();
            refreshSequential();
          });
        });
      });

      const groupMap = new Map(conditionalGroups.map(g => [g.name, g]));
      const sequentialOrder = [
        'q34_a','q34_b','q35_a','q35_b','q36','q37','q38_a','q38_b','q39','q40_a','q40_b','q40_c'
      ];

      const setGroupEnabled = (name, enabled) => {
        const group = groupMap.get(name);
        if (!group) return;
        group.boxes.forEach(b => {
          b.disabled = !enabled;
          b.parentElement?.classList.toggle('opacity-60', !enabled);
        });
        group.details.forEach(el => {
          el.disabled = !enabled;
          el.classList.toggle('bg-gray-200', !enabled);
          el.classList.toggle('text-gray-500', !enabled);
          el.classList.toggle('cursor-not-allowed', !enabled);
        });
      };

      const isGroupAnswered = (group) => group?.boxes?.some(b => b.checked);

      const isGroupComplete = (group) => {
        if (!group) return false;
        const [yesBox, noBox] = group.boxes;
        const yesChecked = yesBox && yesBox.checked;
        const noChecked = noBox && noBox.checked;
        if (noChecked) return true;
        if (!yesChecked) return false;
        const detailMissing = group.details.some(el => {
          if (el.disabled || el.readOnly || el.offsetParent === null) return false;
          return !isFilled(el);
        });
        return !detailMissing;
      };

      const refreshSequential = () => {
        let allow = true;
        sequentialOrder.forEach(name => {
          const group = groupMap.get(name);
          if (!group) return;
          setGroupEnabled(name, allow);
          if (allow && !isGroupComplete(group)) {
            allow = false;
          }
        });
      };

      const firstRowSets = [referenceFirstRow];

      const visibleFields = () => Array.from(document.querySelectorAll('input:not([type="hidden"]), textarea, select'))
        .filter(el => !el.disabled && !el.readOnly && el.offsetParent !== null);

      window.validateRequired = validateRequired = () => {
        let firstMissing = null;

        // Clear old custom validity to avoid stale errors
        document.querySelectorAll('input, textarea, select').forEach(el => el.setCustomValidity(''));

        requiredFields.forEach(el => {
          if (firstMissing || el.disabled || el.readOnly) return;
          if (el.offsetParent === null) return; // ignore hidden required controls (e.g., camera file input)
          if (!isFilled(el)) {
            firstMissing = el;
          }
        });

        if (!firstMissing) {
          for (const name of govFields) {
            const field = form.querySelector(`[name="${name}"]`);
            if (field && !field.disabled && !field.readOnly && (field.value || '').trim() === '') {
              firstMissing = field;
              break;
            }
          }
        }

        for (const group of conditionalGroups) {
          const [yesBox, noBox] = group.boxes;
          const yesChecked = yesBox && yesBox.checked;
          const noChecked = noBox && noBox.checked;

          group.details.forEach(el => {
            const disable = noChecked || (!yesChecked && !noChecked);
            el.disabled = disable;
            el.classList.toggle('bg-gray-200', disable);
            el.classList.toggle('text-gray-500', disable);
            el.classList.toggle('cursor-not-allowed', disable);
            const cacheKey = el.name;
            const wasDisabled = group.detailDisabledState[cacheKey] ?? false;
            group.detailDisabledState[cacheKey] = disable;

            if (!disable && wasDisabled) {
              if (typeof group.detailCache[cacheKey] === 'string' && el.value === '') {
                el.value = group.detailCache[cacheKey];
              }
            }

            if (!disable) {
              group.detailCache[cacheKey] = el.value;
            }

            if (disable) {
              group.detailCache[cacheKey] = el.value;
              el.value = '';
            }
          });

          if (!firstMissing && !yesChecked && !noChecked) {
            firstMissing = yesBox || noBox;
          }

          if (!firstMissing && yesChecked) {
            const detailMissingEl = group.details.find(el => {
              if (el.disabled || el.readOnly || el.offsetParent === null) return false;
              return !isFilled(el);
            });
            if (detailMissingEl) {
              firstMissing = detailMissingEl;
            }
          }
        }

        if (!firstMissing) {
          for (const set of firstRowSets) {
            const state = firstRowState(set);
            if (state.incomplete || state.allBlank) {
              firstMissing = set.find(f => f && !isFilled(f)) || set[0];
              break;
            }
          }
        }

        if (!firstMissing) {
          const incompleteRef = enforceRowCompleteness(referenceRowNames);
          if (incompleteRef) firstMissing = incompleteRef;
        }

        if (!nextBtn) return null;
        nextBtn.removeAttribute('aria-disabled');
        nextBtn.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
        return firstMissing;
      };

      document.addEventListener('input', () => { refreshRows(); refreshSequential(); validateRequired(); }, true);
      document.addEventListener('change', () => { refreshRows(); refreshSequential(); validateRequired(); }, true);
      validateRequired();

      if (nextBtn) {
        nextBtn.addEventListener('click', (e) => {
          const firstMissing = validateRequired();
          if (firstMissing) {
            e.preventDefault();
            e.stopPropagation();
            if (!firstMissing.validationMessage) {
              firstMissing.setCustomValidity('Please complete this field.');
            }
            firstMissing.reportValidity();
            firstMissing.focus({ preventScroll: true });
            scrollToField(firstMissing);
          }
        });
      }

      // Local cache + autosave/draft
      const storageKey = 'pds_form_step4_' + ({{ auth()->id() ?? 0 }});
      const singleSelectCheckboxNames = new Set([
        'q34_a','q34_b','q35_a','q35_b','q36','q37','q38_a','q38_b','q39','q40_a','q40_b','q40_c'
      ]);

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
        if (overrideData?.signature_path && signaturePathInput4 && !signaturePathInput4.value) {
          signaturePathInput4.value = overrideData.signature_path;
        }
        if (overrideData?.signature_data && signatureDataInput4 && !signatureDataInput4.value) {
          signatureDataInput4.value = overrideData.signature_data;
        }

        Object.entries(data).forEach(([rawName, stored]) => {
          let name = rawName;
          let elements = Array.from(form.querySelectorAll(`[name="${name}"]`));

          // Server draft arrays come back without [] (e.g., reference_name), but fields use []
          if (!elements.length && Array.isArray(stored) && !name.endsWith('[]')) {
            const altName = `${name}[]`;
            const altElements = Array.from(form.querySelectorAll(`[name="${altName}"]`));
            if (altElements.length) {
              name = altName;
              elements = altElements;
            }
          }

          if (!elements.length) return;

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

      const autoSaveToServer = (() => {
        let timer;
        let failureCount = 0;
        const retryDelay = 1200;
        const maxRetries = 3;
        return () => {
          clearTimeout(timer);
          timer = setTimeout(() => {
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
                throw new Error('Auto-save unauthorized');
              }
              if (!response.ok) {
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
              } else {
                throw new Error('Auto-save response not ok');
              }
            })
            .catch(() => {
              if (failureCount < maxRetries) {
                failureCount += 1;
                setTimeout(() => autoSaveToServer(), retryDelay);
              }
            });
          }, 800);
        };
      })();
      const persist = () => {
        saveCache();
        autoSaveToServer();
      };

      loadCache();
      updateSignaturePreview4();
      // Persist merged cache once so a fast refresh keeps latest values
      saveCache();

      refreshSequential();
      validateRequired();
      loadCachedPhoto();

      fetch('{{ route('pds.draft', [], false) }}', { headers: { 'Accept': 'application/json' } })
        .then(r => r.ok ? r.json() : null)
        .then(json => {
          if (!json || !json.data) return;
          loadCache(json.data);
          updateSignaturePreview4();
          // Persist merged cache once so a fast refresh keeps latest values
          saveCache();
          refreshSequential();
          validateRequired();
          loadCachedPhoto();
        })
        .catch(() => {});

      form.addEventListener('input', persist);
      form.addEventListener('change', persist);
    });

    // Check for master date from form1 and apply it
    function ensureMasterDateSeed() {
      const date4Input = document.querySelector('input[name="date4"]');
      const existing = localStorage.getItem('pds_master_date');
      const candidate = existing || date4Input?.value;
      if (candidate && !existing) {
        localStorage.setItem('pds_master_date', candidate);
      }
      return candidate || null;
    }

    function syncFromForm1() {
      const masterDate = ensureMasterDateSeed();
      if (masterDate) {
        const date4Input = document.querySelector('input[name="date4"]');
        if (date4Input && date4Input.value !== masterDate) {
          date4Input.value = masterDate;
          // Trigger change event to save to cache
          date4Input.dispatchEvent(new Event('change', { bubbles: true }));
        }
      }
    }

    // Check for master date when page loads
    syncFromForm1();

    // Also check periodically in case user navigates back from form1
    setInterval(syncFromForm1, 1000);
  </script>
  
  <div class="max-w-6xl mx-auto p-4 font-serif text-sm pds-responsive">
  <div class="pds-sheet">

  <table class="border-black w-full text-sm border-2 border-b-0">
    <tr>
      <td class="border w-2/3 align-top border-black">
        <div class="ml-4 mb-3 mt-3">
          34. Are you related by consanguinity or affinity to the appointing or recommending authority, or to the
          chief of bureau or office or to the person who has immediate supervision over you in the Office,
          Bureau or Department where you will be appointed,
          <p class="ml-10 mt-5">a. within the third degree?</p>
          <p class="ml-10 mt-3">b. within the fourth degree (for Local Government Unit – Career Employees)?</p>
        </div>
      </td>
      <td class="border px-2 align-top border-black">
        <div class="flex h-full gap-20 mt-20">
          <label class="flex items-center gap-2"><input type="checkbox" name="q34_a" value="YES"> YES</label>
          <label class="flex items-center gap-2"><input type="checkbox" name="q34_a" value="NO"> NO</label>
        </div>
        <div class="flex h-full gap-20 mt-2">
          <label class="flex items-center gap-2"><input type="checkbox" name="q34_b" value="YES"> YES</label>
          <label class="flex items-center gap-3"><input type="checkbox" name="q34_b" value="NO"> NO</label>
        </div>
        <p class="mt-2">if yes, give details:</p>
        <input type="text" class="mb-2 w-full" name="q34_a_details" data-detail-for="q34_a">
        <input type="text" class="mb-2 w-full" name="q34_b_details" data-detail-for="q34_b">
      </td>
    </tr>

    <tr>
      <td class="border w-2/3 align-top border-b-0 border-black">
        <div class="ml-5 mb-3 mt-3">35. a. Have you ever been found guilty of any administrative offense?</div>
      </td>
      <td class="border px-2 align-top border-black">
        <div class="flex h-full gap-20 mt-2">
          <label class="flex items-center gap-2"><input type="checkbox" name="q35_a" value="YES"> YES</label>
          <label class="flex items-center gap-2"><input type="checkbox" name="q35_a" value="NO"> NO</label>
        </div>
        <p class="mt-2">if yes, give details:</p>
        <input type="text" class="mb-2 w-full" name="q35_a_details" data-detail-for="q35_a">
      </td>
    </tr>


    <tr>
      <td class="w-2/3 align-top border-t-0 border-black">
        <div class="ml-10 mb-3 mt-3">b. Have you been criminally charged before any court?</div>
      </td>
      <td class="border px-2 border-black">
        <div class="flex h-full gap-20 mt-2">
          <label class="flex items-center gap-2"><input type="checkbox" name="q35_b" value="YES"> YES</label>
          <label class="flex items-center gap-2"><input type="checkbox" name="q35_b" value="NO"> NO</label>
        </div>
        <p class="mt-2 mb-2">if yes, give details:</p>
        <span class="ml-9">Date Filed:</span> <input class="border-b mb-2 mr-40" type="text" data-detail-for="q35_b" name="q35_b_details_date">
        <span class="ml-1">Status of Case/s:</span> <input class="border-b mb-2" type="text" data-detail-for="q35_b" name="q35_b_details_status">
      </td>
    </tr>

 <tr>
      <td class="border w-2/3 align-top border-black">
              <div class="ml-5 mb-3 mt-3">36. Have you ever been convicted of any crime or violation of any law, decree, ordinance or regulation by any court or tribunal?
        </div>
        
      </td>
      
     <td class="border px-2 border-black">
    <div class="flex h-full gap-20 mt-3">
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q36" value="YES"> YES
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q36" value="NO"> NO
      </label>
    </div>

  <p class="mt-2">if yes, give details:</p>
    <input class="border-b mb-2 w-full" type="text" data-detail-for="q36" name="q36_details">

</tr>


<tr>
      <td class="border w-2/3 align-top border-black">
              <div class="ml-5 mb-3 mt-3">37. Have you ever been separated from the service in any of the following modes: resignation, retirement, dropped from the rolls, dismissal, termination, end of term, finished contract or phased out (abolition) in the public or private sector?
        </div>
      </td>
     <td class="border px-2 border-black">
    <div class="flex h-full gap-20 mt-3">
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q37" value="YES"> YES
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q37" value="NO"> NO
      </label>
    </div>

  <p class="mt-2">if yes, give details:</p>
    <input class="border-b mb-2 w-full" type="text" data-detail-for="q37" name="q37_details">

</tr>



<tr>
      <td class="border w-2/3 align-top border-black border-b-0">
              <div class="ml-5 mb-3 mt-3">38. a. Have you ever been a candidate in a national or local election held within the last year (except Barangay election)?
        </div>
        
      </td>
      
     <td class=" px-2 border-black">
    <div class="flex h-full gap-20 mt-2">
      <label class="flex items-center gap-2 mt-1">
        <input type="checkbox" name="q38_a" value="YES"> YES
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q38_a" value="NO"> NO
      </label>
    </div>

  <p class="mt-2">if yes, give details:</p>
    <input class="border-b mb-2 w-full" type="text" data-detail-for="q38_a" name="q38_a_details">

</tr>


 <tr>
      <td class="border w-2/3 align-top border-t-0 border-black">
              <div class="ml-10 mb-3 mt-3">b. Have you resigned from the government service during the three (3)-month period before the last election to promote/actively campaign for a national or local candidate?
    
        </div>
        
      </td>
      
     <td class="border  px-2  border-black">
    <div class="flex h-full gap-20 mt-2">
      <label class="flex items-center gap-2 mt-1">
        <input type="checkbox" name="q38_b" value="YES"> YES
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q38_b" value="NO"> NO
      </label>
    </div>

  <p class="mt-2 mb-2">if yes, give details:</p>   
  <input class="border-b mb-2 w-full" type="text" data-detail-for="q38_b" name="q38_b_details">
   

</tr>



<tr>
      <td class="border w-2/3 align-top border-t-0 border-black border">
              <div class="ml-5 mb-3 mt-3">39. Have you acquired the status of an immigrant or permanent resident of another country?
        </div>
        
      </td>
      
     <td class="border  px-2 border-black">
    <div class="flex h-full gap-20 mt-2">
      <label class="flex items-center gap-2 mt-1">
        <input type="checkbox" name="q39" value="YES"> YES
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q39" value="NO"> NO
      </label>
    </div>

  <p class="mt-2 mb-2">if yes, give details:</p>   
  <input class="border-b mb-2 w-full" type="text" data-detail-for="q39" name="q39_details">
   

</tr>


<tr>
      <td class="border w-2/3 align-top border-t-0 border-black">
              <div class="ml-5 mb-3 mt-3">40. Pursuant to: (a) Indigenous People's Act (RA 8371); (b) Magna Carta for Disabled Persons (RA 7277, as amended); and (c) Expanded Solo Parents Welfare Act (RA 11861), please answer the following items:

                <p class="ml-6 mt-2 mb-18">a. Are you a member of any indigenous group?</p>
                <p class="ml-6 mb-18 mt-20">b. Are you a person with disability?</p>
                <p class="ml-6 flex mt-16">c. Are you a solo parent?</p>
        </div>
        
      </td>
      
     <td class="border  px-2 border-black">
    <div class="flex h-full gap-20 mt-12">
      <label class="flex items-center gap-2 mt-1">
        <input type="checkbox" name="q40_a" value="YES"> YES
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q40_a" value="NO"> NO
      </label>
    </div>

      <p class="mt-2 mb-2">if yes, give details:</p>   
  <input class="border-b mb-2 w-full" type="text" data-detail-for="q40_a" name="q40_a_details">

     <div class="flex h-full gap-20 mt-2">
      <label class="flex items-center gap-2 mt-1">
        <input type="checkbox" name="q40_b" value="YES"> YES
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q40_b" value="NO"> NO
      </label>
    </div>

      <p class="mt-2 mb-2">if yes, give details:</p>   
  <input class="border-b mb-2 w-full" type="text" data-detail-for="q40_b" name="q40_b_details">

     <div class="flex h-full gap-20 mt-2">
      <label class="flex items-center gap-2 mt-1">
        <input type="checkbox" name="q40_c" value="YES"> YES
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q40_c" value="NO"> NO
      </label>
    </div>
  <p class="mt-2 mb-2">if yes, give details:</p>   
  <input class="border-b mb-2 w-full" type="text" data-detail-for="q40_c" name="q40_c_details">
</td>
</tr>

    </table> 

    <table data-section="references" class="w-full h-full border-l-2 border-b-0 border-black font-['Arial_Narrow','Arial',sans-serif]">
      <tr>
        <td class="border-l-3 border border-t-2 border-r-2 border-b-3 border-black" colspan="3">
          <span class="ml-2">41. REFERENCES </span><span class="font-semibold">(Person not related by consanguinity or affinity to applicant / appointee)</span>
        </td>
        <td class="flex-1 w-1/4 align-top text-center border-l-0 border-r-2 border-t-2 border-black" rowspan="11">
  <div class="mt-12 flex flex-col items-center">

    <!-- PASSPORT PHOTO -->
    <div class="w-full flex flex-col items-center gap-2">
      <div class="border-2 border-black w-[3.5cm] h-[4.5cm] flex items-center justify-center text-xs italic text-center relative overflow-hidden">
        <img id="photoPreview" class="absolute inset-0 w-full h-full object-cover hidden" />
        <div id="photoPlaceholder">
          Passport-sized unfiltered<br>
          picture taken within<br>
          the last 6 months<br>
          4.5 cm × 3.5 cm
        </div>
        <video id="photoVideo" class="absolute inset-0 w-full h-full object-cover hidden" playsinline style="transform: scaleX(-1);"></video>
        <div id="photoOverlay" class="pointer-events-none absolute inset-0 flex items-center justify-center hidden">
          <svg id="photoProgressSvg" class="absolute inset-0 w-full h-full" viewBox="0 0 140 180" fill="none">
            <rect x="4" y="4" width="132" height="172" rx="12" stroke="rgba(255,255,255,0.25)" stroke-width="6"></rect>
            <rect id="photoProgressRing" x="4" y="4" width="132" height="172" rx="12" stroke="#0ea5e9" stroke-width="6" stroke-linecap="round"
              stroke-dasharray="616" stroke-dashoffset="616"></rect>
          </svg>
          <div class="absolute w-[70%] h-[70%] rounded-full border-4 border-dashed border-rose-500 shadow-[0_0_0_2px_rgba(244,63,94,0.25)] guide-circle"></div>
        </div>
      </div>

      <p id="photoStatus" class="text-xs text-center text-rose-600 font-semibold min-h-[18px]"></p>

        <div class="flex flex-col items-center gap-2 text-xs w-full">
          <button type="button" id="photoStartBtn" class="px-3 py-1 bg-emerald-600 text-white rounded shadow" onclick="startPhotoCamera()">Open camera</button>
          <button type="button" id="photoCaptureBtn" class="px-3 py-1 bg-emerald-600 text-white rounded shadow hidden disabled:opacity-60" onclick="capturePhoto()" disabled>Capture</button>
        <!-- Upload option -->
        <label class="px-3 py-1 bg-indigo-600 text-white rounded shadow cursor-pointer">
          Upload photo
          <input id="photoFile" type="file" name="photo" accept="image/*" class="hidden" onchange="previewPhotoFromFile(this.files[0])">
        </label>
        <input type="hidden" id="photoData" name="photo_data">
      </div>

      <div class="mt-1 text-xs">PHOTO</div>
    </div>

    <!-- THUMB MARK -->
    <label class="cursor-pointer mt-6">
      <div class="mt-20 border-2 border-black w-[4.5cm] h-[4.5cm] flex items-center justify-center text-xs italic text-center relative overflow-hidden">
        <img id="thumbPreview" class="absolute inset-0 w-full h-full object-cover hidden" />
        <div id="thumbPlaceholder" class="mt-auto border-black border-t border-l-0 border-b-0 border-r-0 w-full">
          Right Thumbmark
        </div>
      </div>
      <input type="file" name="thumbmark" accept="image/*" class="hidden" onchange="previewThumb(event)">
    </label>
  </div>
</td>
      </tr>
      <tr class="border border-r-0 border-black">
        <th class="border font-light w-24 border-l-3 border-black">NAME</th>
        <th class="border font-light border-black">OFFICE / RESIDENTIAL ADDRESS </th>
        <th class="border font-light w-52 border-r-2 border-black">CONTACT NO. AND / OR EMAIL</th>
      </tr>
      @for ($i = 0; $i < 7; $i++)
      <tr class="border border-r-0 border-l-3 border-black align-top">
        <td class="border border-black align-top p-0 w-60">
          <textarea name="reference_name[]" class="align-middle text-center ref-field w-full border-none outline-none p-2 text-xs resize-none" rows="1" placeholder=""></textarea>
        </td>
        <td class="border border-black align-top p-0">
          <textarea name="reference_address[]" class="align-middle text-center ref-field w-full border-none outline-none p-2 text-xs resize-none" rows="1" placeholder=""></textarea>
        </td>
        <td class="border border-r-3 align-top p-0 border-r-2 border-black">
          <textarea name="reference_contact[]" class="align-middle text-center ref-field w-full border-none outline-none p-2 text-xs resize-none" rows="1" placeholder=""></textarea>
        </td>
      </tr>
      @endfor
      <tr class="border justify-center">
        <td colspan="3" class="text-justify px-2 h-20 font-semibold border-t-2 border-black border-b-2 border-r-2">
          42. I declare under oath that I have personally accomplished this Personal Data Sheet which is a true, correct, and complete statement pursuant to the provisions of pertinent laws, rules, and regulations of the Republic 
          <span class="px-4">of the Philippines. I authorize the agency head/authorized representative to verify/validate the contents stated herein. I  agree that any misrepresentation made in this document and its attachments shall cause the filing of administrative/criminal case/s against me.</span> 
        </td>
      </tr>
      <tr>
        <td class="pr-5 p-0 align-top w-[40%] border-l border-black border-r-0">
        <table class="text-xs border-2 border-black ml-2 mt-2 mb-2 w-full border-collapse">
  <!-- HEADER -->
  <tr>
    <td class="border px-2 py-1 font-semibold h-[1.06cm] align-top border-black" colspan="2">
      Government Issued ID (i.e. Passport, GSIS, SSS, PRC, Driver's License, etc.)<br>
      <span class="italic font-normal">
        PLEASE INDICATE ID Number and Date of Issuance
      </span>
    </td>
  </tr>

  <!-- ROW 1 -->
  <tr>
    <td class="border px-2 py-1 h-8 align-middle border-black">
      Government Issued ID:
    </td>
    <td class="border px-2 py-1 h-8 align-middle border-black">
      <textarea class="text-base w-full h-full resize-none outline-none align-middle" name="gov_id"></textarea>
    </td>
  </tr>

  <!-- ROW 2 -->
  <tr>
    <td class="border px-2 py-1 h-10 align-middle border-black">
      ID/License/Passport No.:
    </td>
    <td class="border px-2 py-1 h-10 align-middle border-black">
      <textarea class="text-base w-full h-full resize-none outline-none" name="licence_passport_id"></textarea>
    </td>
  </tr>

  <!-- ROW 3 -->
  <tr>
    <td class="border px-2 py-1 h-8 align-middle border-black">
      Date/Place of Issuance:
    </td>
    <td class="border px-2 py-1 h-8 align-middle border-black">
      <textarea class="text-base w-full h-full resize-none outline-none" name="id_issue_date_place"></textarea>
    </td>
  </tr>

</table>
        </td>
        <td class="p-0 align-top w-[35%] border-b-0 border-l-0 border-r-0 border-black" colspan="2">
          <table class="w-full border-collapse text-xs border-3 mt-2 border-2 mb-2">
            <tr>
              <td class="h-[4.45cm] border-black text-center align-middle italic text-red-600 relative p-1">
                <label id="signatureBox4a" class="signature-box block h-full w-full cursor-default relative">
                  <input
                    type="file"
                    name="signature_file"
                    id="signatureFileInput4"
                    accept="image/*"
                    class="absolute inset-0 w-full h-full opacity-0 cursor-not-allowed pointer-events-none"
                    disabled
                  >
                  <img
                    id="signaturePreviewImg4a"
                    src="{{ !empty($signaturePath) ? Storage::url($signaturePath) : '' }}"
                    alt="Signature preview"
                    class="absolute inset-0 w-full h-full object-contain {{ empty($signaturePath) ? 'hidden' : '' }}"
                  >
                  <div id="signaturePlaceholder4a" class="absolute inset-0" aria-hidden="true"></div>
                </label>
                <input type="hidden" name="signature_path" id="signature_path4" value="{{ $signaturePath ?? '' }}">
                <input type="hidden" name="signature_data" id="signature_data4">
              </td>
            </tr>
            <tr>
              <td class="border border-black text-center py-1">Signature (Sign inside the box)</td>
            </tr>
           <tr>
  <td>
    <div class="relative flex justify-center py-2 h-10">
      <div class="flex items-center justify-center w-full">
        <input
          type="date"
          name="date4"
          class="w-full h-full text-center text-lg bg-transparent border-none focus:outline-none px-2 py-1"
        />
      </div>
    </div>
  </td>
</tr>
            <tr>
              <td class="border border-black  text-center py-1">Date Accomplished</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
    <table class="border-3 border-t-0 border-black w-full font-['Arial_Narrow','Arial',sans-serif]">
      <tr>
        <td class="p-2 text-center align-middle font-semibold text-sm">
          SUBSCRIBED AND SWORN to before me this <span id="masterDateDisplay" style="display: inline-block; border-bottom: 1px solid black; min-width: 150px;"></span>, affiant exhibiting his/her validly issued government ID as indicated above.
        </td>
        <script>
          document.addEventListener('DOMContentLoaded', function() {
            const masterDate = localStorage.getItem('pds_master_date');
            const dateDisplay = document.getElementById('masterDateDisplay');
            if (masterDate) {
              // Format YYYY-MM-DD to DD/MM/YY
              const date = new Date(masterDate);
              const day = String(date.getDate()).padStart(2, '0');
              const month = String(date.getMonth() + 1).padStart(2, '0');
              const year = String(date.getFullYear()); // Get last 2 digits
              dateDisplay.textContent = `${day}/${month}/${year}`;
            } else {
              dateDisplay.textContent = '_____________________________';
            }
          });
        </script>
      </tr>
      <tr>
        <td class="p-2 align-top text-center">
          <table class="w-1/3 mx-auto h-full border-collapse text-xs border-3">
            <tr>
  <td class="border-black h-24 text-center align-middle italic text-red-600 relative overflow-hidden">

    <label id="signatureBox4b" class="signature-box block h-full w-full cursor-default relative">
      <input
        type="file"
        name="signature_file"
        id="signatureFileInput4b"
        accept="image/*"
        class="absolute inset-0 w-full h-full opacity-0 cursor-not-allowed pointer-events-none"
        disabled
      />

      <img
        id="signaturePreviewImg4b"
        src="{{ !empty($signaturePath) ? Storage::url($signaturePath) : '' }}"
        alt="Signature preview"
        class="absolute inset-0 w-full h-full object-contain {{ empty($signaturePath) ? 'hidden' : '' }}"
      >

      <div id="signaturePlaceholder4b" class="absolute inset-0" aria-hidden="true"></div>
    </label>

  </td>
</tr>
            <tr><td class="border-black border text-center py-2 font-semibold">Person Administering Oath</td></tr>
          </table>
        </td>
      </tr>
    </table>
     <div class="flex justify-end mr-2 border-b-0 font-['Arial_Narrow','sans-serif'] text-sm">
    CS FORM 212 (Revised 2025), Page 4 of 5
    </div>
      <div class="flex justify-between mt-4">
    <a href="{{ route('pds.form3') }}" class="px-4 py-2 bg-blue-600 text-white rounded shadow border border-blue-700 hover:bg-blue-700 print:text-white print:bg-blue-600">Previous Page</a>
    <button type="submit" id="pds4-next" class="px-4 py-2 bg-blue-600 text-white rounded shadow border border-blue-700 hover:bg-blue-700 print:text-white print:bg-blue-600">Next Page</button>
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
  text-align: center;
}

input[type="date"]::-webkit-datetime-edit-month-field {
  vertical-align: middle;
  font-size: 16px;
  color: #000000;
  text-align: center;
}

input[type="date"]::-webkit-datetime-edit-day-field {
  vertical-align: middle;
  font-size: 16px;
  color: #000000;
  text-align: center;
}

input[type="date"]::-webkit-datetime-edit-year-field {
  vertical-align: middle;
  font-size: 16px;
  color: #000000;
  text-align: center;
}

/* Firefox date input text color and centering */
input[type="date"]::-moz-datetime-edit-text {
  color: #000000;
  text-align: center;
}

input[type="date"]::-moz-datetime-edit-month-field {
  color: #000000;
  text-align: center;
}

input[type="date"]::-moz-datetime-edit-day-field {
  color: #000000;
  text-align: center;
}

input[type="date"]::-moz-datetime-edit-year-field {
  color: #000000;
  text-align: center;
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