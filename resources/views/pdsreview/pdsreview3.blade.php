@if(!empty($pdfMode))
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"></head>
<body>
@endif
@if(empty($pdfMode))
<x-app-layout>
@endif
<form method="POST" action="{{ route('pds.saveStep', 3) }}" enctype="multipart/form-data">
    <div class="max-w-6xl mx-auto p-4 flex justify-end gap-3">
      <a href="{{ route('pdsreview1.pdf') }}" class="px-4 py-2 bg-emerald-600 text-white rounded shadow border border-emerald-700 hover:bg-emerald-700">Preview PDF</a>
      <a href="{{ route('pds.pdf.download') }}" class="px-4 py-2 bg-slate-700 text-white rounded shadow border border-slate-800 hover:bg-slate-800">Download PDF</a>
    </div>
@csrf
    <style>
  body { margin: 0px; }
  table {
    border-collapse: collapse;
    width: 100%;
    table-layout: fixed;
 }
        td, th { padding: 4px; vertical-align: middle; white-space: normal; word-break: break-word; overflow-wrap: anywhere; }
        .border { border: 1px solid #000 !important; }
        .border-2 { border: 2px solid #000 !important; }
        @media print {
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        textarea { border: none; outline: none; padding: 8px; width: 100%; font: inherit; resize: none; background: transparent; line-height: 1.3; display: block; box-sizing: border-box; overflow: hidden; white-space: pre-wrap; word-break: break-word; min-height: 38px; height: auto; }
        textarea:focus { outline: none; box-shadow: none; }
        td { height: 38px; min-height: 38px; vertical-align: middle; }
    </style>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const sessionData = @json(session('pds', []));
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

            // NA locking for all [] groups on this page (first NA/N/A/NONE disables fields BELOW it)
            const isNA = (val) => {
                const v = (val || '').trim().toUpperCase();
                return v === 'NA' || v === 'N/A' || v === 'NONE';
            };

            const names = new Set();
            document.querySelectorAll('input[name$="[]"], textarea[name$="[]"]').forEach(el => {
                const name = el.getAttribute('name');
                if (name) names.add(name);
            });

            names.forEach(name => {
                const selectorName = name.replace(/["'\\]/g, '\\$&');
                const fields = Array.from(document.querySelectorAll(`input[name="${selectorName}"]` + `, textarea[name="${selectorName}"]`));
                if (!fields.length) return;

                const refresh = () => {
                    const firstNAIndex = fields.findIndex(f => isNA(f.value));
                    fields.forEach((f, idx) => {
                        const shouldDisable = firstNAIndex !== -1 && idx > firstNAIndex;
                        f.disabled = shouldDisable;
                        f.classList.toggle('bg-gray-200', shouldDisable);
                        f.classList.toggle('text-gray-500', shouldDisable);
                        f.classList.toggle('cursor-not-allowed', shouldDisable);
                    });
                };

                fields.forEach(f => f.addEventListener('input', refresh));
                refresh();
            });

            // Next button gating: require all visible fields (treat NA/N/A/NONE as filled)
            const nextBtn = document.getElementById('pds3-next');
            const visibleFields = () => Array.from(document.querySelectorAll('input:not([type="hidden"]), textarea, select'))
                .filter(el => !el.disabled && !el.readOnly && el.offsetParent !== null);

            const isFilled = (el) => {
                if (el.type === 'file') return el.files && el.files.length > 0;
                if (el.type === 'checkbox' || el.type === 'radio') return el.checked;
                const val = (el.value || '').trim();
                if (isNA(val)) return true;
                return val !== '';
            };

            const validateRequired = () => {
                const hasMissing = visibleFields().some(el => !isFilled(el));

                if (!nextBtn) return;
                if (hasMissing) {
                    nextBtn.setAttribute('aria-disabled', 'true');
                    nextBtn.classList.add('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                } else {
                    nextBtn.removeAttribute('aria-disabled');
                    nextBtn.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                }
            };

            document.addEventListener('input', validateRequired, true);
            document.addEventListener('change', validateRequired, true);
            validateRequired();

            if (nextBtn) {
                nextBtn.addEventListener('click', (e) => {
                    if (nextBtn.getAttribute('aria-disabled') === 'true') {
                        e.preventDefault();
                        e.stopPropagation();
                        validateRequired();
                    }
                });
            }
        });
    </script>
    <div class="max-w-6xl mx-auto p-4 font-serif text-sm">
 @include('pdsreview.partials.date-format-helper')
    <table class="border border-black w-full font-['Arial_Narrow','Arial',sans-serif]">

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

    @php
    $volRows = ($voluntaryWorks ?? collect())->values();
    $maxRows = max(7, $volRows->count());
@endphp

@for ($i = 0; $i < $maxRows; $i++)
  @php $row = $volRows[$i] ?? null; @endphp
  <tr>
    <td class="border align-middle text-center">{{ $row->organization ?? ' ' }}</td>
    <td class="border align-middle text-center">{{ $row ? (format_pds_date($row->from) ?: ($i === 0 ? 'NA' : ' ')) : ($i === 0 ? 'NA' : ' ') }}</td>
    <td class="border align-middle text-center">{{ $row ? (format_pds_date($row->to) ?: ($i === 0 ? 'NA' : ' ')) : ($i === 0 ? 'NA' : ' ') }}</td>
    <td class="border align-middle text-center">{{ $row->hours ?? ' ' }}</td>
    <td class="border align-middle text-center">{{ $row->position ?? ' ' }}</td>
  </tr>
@endfor

    </table>
    

    <table class="border border-black font-['Arial_Narrow','Arial',sans-serif]">
      
      <colgroup>
        <col style="width: 45.5%;">
        <col style="width: 8%;">
        <col style="width: 7.5%;">
        <col style="width: 8%;">
        <col style="width: 12%;">
        <col style="width: 19%;">
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

      @php
    $trainingRows = ($training ?? collect())->values(); // keep user-entered order
    $maxTraining = max(21, $trainingRows->count());
@endphp

@for ($i = 0; $i < $maxTraining; $i++)
  @php $trow = $trainingRows[$i] ?? null; @endphp
  <tr>
    <td class="border align-middle text-center">{{ $trow->title ?? ' ' }}</td>
    <td class="border align-middle text-center">{{ $trow ? (format_pds_date($trow->from) ?: ($i === 0 ? 'NA' : ' ')) : ($i === 0 ? 'NA' : ' ') }}</td>
    <td class="border align-middle text-center">{{ $trow ? (format_pds_date($trow->to) ?: ($i === 0 ? 'NA' : ' ')) : ($i === 0 ? 'NA' : ' ') }}</td>
    <td class="border align-middle text-center">{{ $trow->hours ?? ' ' }}</td>
    <td class="border align-middle text-center">{{ $trow->type_of_ld ?? ' ' }}</td>
    <td class="border align-middle text-center">{{ $trow->conducted_by ?? ' ' }}</td>
  </tr>
@endfor

    </table>

    <table class="border border-black w-full font-['Arial_Narrow','Arial',sans-serif]">

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

    @php
    $otherCollection = $other ?? collect();
    $skills = $otherCollection->where('category', 'skills')->pluck('description')->values();
    $recognition = $otherCollection->where('category', 'recognition')->pluck('description')->values();
    $assoc = $otherCollection->where('category', 'association')->pluck('description')->values();
    $maxOther = max(7, $skills->count(), $recognition->count(), $assoc->count());
@endphp

@for ($i = 0; $i < $maxOther; $i++)
  <tr>
    <td class="border align-middle text-center">{{ $skills[$i] ?? ' ' }}</td>
    <td class="border align-middle text-center">{{ $recognition[$i] ?? ' ' }}</td>
    <td class="border align-middle text-center">{{ $assoc[$i] ?? ' ' }}</td>
  </tr>
@endfor

    </table>

    <table class="border border-black w-full h-15 font-['Arial_Narrow','sans-serif'] italic">
        <colgroup>
          <col style="width: 31.95%;">
           <col style="width: 20.9%;">
            <col style="width: 19.7%;">
             <col style="width: 10%;">
        </colgroup>
      <tr>
      <td class="text-center font-bold text-xl border">
          SIGNATURE
      </td>

       <td class="border" colspan="2">
      <div class="h-full w-full flex flex-col items-center justify-center p-2 space-y-2">
        @php
          $signatureCleanPath = !empty($signaturePath) ? preg_replace('/^public\//', '', $signaturePath) : null;
          $signatureUrl = !empty($signatureCleanPath) ? asset('storage/'.$signatureCleanPath) : null;
        @endphp
        @if($signatureUrl)
          <img src="{{ $signatureUrl }}"
     alt="Signature"
     class="object-contain"
     style="max-height: 150px;
            mix-blend-mode: multiply;
            filter: contrast(1.2) brightness(1.1);"
     onerror="this.alt='';this.style.display='none';">
        @else
          <div class="text-xs text-gray-600">No signature on file</div>
        @endif
      </div>
    </td>


      <td class="border text-center font-bold text-xl">
        DATE
      </td>

        <td colspan="2"
          class="border">
          <div class="h-full w-full flex items-center justify-center">
         <div class="text-3xl text-center">{{format_pds_date($declaration->date_accomplished) ?? '—' }}</div>
      </td>
      </tr>
    </table>

     <div class="flex justify-end mr-2 border-b-0 font-['Arial_Narrow','sans-serif']">
    CS FORM 212 (Revised 2025), Page 3 of 5
    </div>
    @if(empty($pdfMode))
    <div class="flex justify-between mt-4 font-['Arial_Narrow','sans-serif']">
        <a href="{{ route('pdsreview.pdsreview2') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded shadow border border-gray-300 hover:bg-gray-300">Previous Page</a>
            <a href="{{ route('pdsreview.pdsreview4') }}" id="next-btn" class="px-4 py-2 bg-blue-600 text-white rounded shadow border border-blue-700 hover:bg-blue-700">Next Page</a>
    </div>
    @endif
    </div>
</form>
@if(empty($pdfMode))
</x-app-layout>
@endif
@if(!empty($pdfMode))
</body>
</html>
@endif