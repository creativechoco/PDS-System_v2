@if(!empty($pdfMode))
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"></head>
<body>
@endif
@if(empty($pdfMode))
<x-app-layout>
@endif
<form method="POST" action="{{ route('pds.saveStep', 4) }}" enctype="multipart/form-data">
    <div class="max-w-6xl mx-auto p-4 flex justify-end gap-3">
      <a href="{{ route('pdsreview1.pdf') }}" class="px-4 py-2 bg-emerald-600 text-white rounded shadow border border-emerald-700 hover:bg-emerald-700">Preview PDF</a>
      <a href="{{ route('pds.pdf.download') }}" class="px-4 py-2 bg-slate-700 text-white rounded shadow border border-slate-800 hover:bg-slate-800">Download PDF</a>
    </div>
@csrf
  <style>
    body { margin: 0px; font-family: 'Arial Narrow','Arial',sans-serif; }
    table { border-collapse: collapse; width: 100%; table-layout: fixed; }
    .border-3 { border: 3px solid #000; }
    .border-2 { border: 2px solid #000; }
    .border-black { border-color: #000; }
    input[type="text"], textarea { width: 100%; background: transparent; border: none; border-bottom: 1px solid #000; outline: none; resize: none; overflow: hidden; padding: 2px 0; line-height: 1.2; font-family: 'Arial Narrow','Arial',sans-serif; font-size: inherit; }
    textarea:focus { outline: none; box-shadow: none; }
    input:focus { outline: none; box-shadow: none; }
    td, th { padding: 4px; vertical-align: middle; white-space: normal; word-break: break-word; overflow-wrap: anywhere; }
    /* Mobile responsiveness: allow horizontal scroll and tighter spacing */
    .pds-responsive { overflow-x: auto; }
    .pds-sheet { min-width: 980px; }
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
  <script>
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

    const autoGrow = (el) => {
        el.style.height = 'auto';
        el.style.height = `${el.scrollHeight}px`;
    }
  </script>
  <div class="max-w-6xl mx-auto p-4 font-serif text-sm">

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
          <label class="flex items-center gap-2"><input type="checkbox" name="q34_a" value="YES" Disabled @checked(($declaration->q34_a ?? '') === 'YES')> YES</label>
          <label class="flex items-center gap-2"><input type="checkbox" name="q34_a" value="NO" Disabled @checked(($declaration->q34_a ?? '') === 'NO')> NO</label>
        </div>
        <div class="flex h-full gap-20 mt-2">
          <label class="flex items-center gap-2"><input type="checkbox" name="q34_b" value="YES" Disabled @checked(($declaration->q34_b ?? '') === 'YES')> YES</label>
          <label class="flex items-center gap-3"><input type="checkbox" name="q34_b" value="NO" Disabled @checked(($declaration->q34_b ?? '') === 'NO')> NO</label>
        </div>
        <p class="mt-2">if yes, give details:</p>
        <input type="text" class="mb-2 w-full" name="q34_a_details" data-detail-for="q34_a" Disabled value="{{ $declaration->q34_a_details ?? '' }}">
        <input type="text" class="mb-2 w-full" name="q34_b_details" data-detail-for="q34_b" Disabled value="{{ $declaration->q34_b_details ?? '' }}">
      </td>
    </tr>

    <tr>
      <td class="border w-2/3 align-top border-b-0 border-black">
        <div class="ml-5 mb-3 mt-3">35. a. Have you ever been found guilty of any administrative offense?</div>
      </td>
      <td class="border px-2 align-top border-black">
        <div class="flex h-full gap-20 mt-2">
          <label class="flex items-center gap-2"><input type="checkbox" name="q35_a" Disabled value="YES" @checked(($declaration->q35_a ?? '') === 'YES')> YES</label>
          <label class="flex items-center gap-2"><input type="checkbox" name="q35_a" Disabled value="NO" @checked(($declaration->q35_a ?? '') === 'NO')> NO</label>
        </div>
        <p class="mt-2">if yes, give details:</p>
        <input type="text" class="mb-2 w-full" name="q35_a_details" data-detail-for="q35_a" Disabled value="{{ $declaration->q35_a_details ?? '' }}">
      </td>
    </tr>


    <tr>
      <td class="w-2/3 align-top border-t-0 border-black">
        <div class="ml-10 mb-3 mt-3">b. Have you been criminally charged before any court?</div>
      </td>
      <td class="border px-2 border-black">
        <div class="flex h-full gap-20 mt-2">
          <label class="flex items-center gap-2"><input type="checkbox" name="q35_b" Disabled value="YES" @checked(($declaration->q35_b ?? '') === 'YES')> YES</label>
          <label class="flex items-center gap-2"><input type="checkbox" name="q35_b" Disabled value="NO" @checked(($declaration->q35_b ?? '') === 'NO')> NO</label>
        </div>
        <p class="mt-2 mb-2">if yes, give details:</p>
        <span class="ml-9">Date Filed:</span> <input class="border-b mb-2 mr-40" type="text" Disabled data-detail-for="q35_b" name="q35_b_details_date" value="{{ $declaration->q35_b_details_date ?? '' }}">
        <span class="ml-1">Status of Case/s:</span> <input class="border-b mb-2" type="text" Disabled data-detail-for="q35_b" name="q35_b_details_status" value="{{ $declaration->q35_b_details_status ?? '' }}">
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
        <input type="checkbox" name="q36" value="YES" Disabled @checked(($declaration->q36 ?? '') === 'YES')> YES
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q36" value="NO" Disabled @checked(($declaration->q36 ?? '') === 'NO')> NO
      </label>
    </div>

  <p class="mt-2">if yes, give details:</p>
    <input class="border-b mb-2 w-full" type="text" Disabled name="q36_details" data-detail-for="q36" value="{{ $declaration->q36_details ?? '' }}">

</tr>


<tr>
      <td class="border w-2/3 align-top border-black">
              <div class="ml-5 mb-3 mt-3">37. Have you ever been separated from the service in any of the following modes: resignation, retirement, dropped from the rolls, dismissal, termination, end of term, finished contract or phased out (abolition) in the public or private sector?
        </div>
      </td>
     <td class="border px-2 border-black">
    <div class="flex h-full gap-20 mt-3">
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q37" value="YES" Disabled @checked(($declaration->q37 ?? '') === 'YES')> YES
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q37" value="NO" Disabled @checked(($declaration->q37 ?? '') === 'NO')> NO
      </label>
    </div>

  <p class="mt-2">if yes, give details:</p>
    <input class="border-b mb-2 w-full" type="text" Disabled name="q37_details" data-detail-for="q37" value="{{ $declaration->q37_details ?? '' }}">

</tr>



<tr>
      <td class="border w-2/3 align-top border-black">
              <div class="ml-5 mb-3 mt-3">38. a. Have you ever been a candidate in a national or local election held within the last year (except Barangay election)?
        </div>
        
      </td>
      
     <td class="border px-2 border-black">
    <div class="flex h-full gap-20 mt-2">
      <label class="flex items-center gap-2 mt-1">
        <input type="checkbox" name="q38_a" value="YES" Disabled @checked(($declaration->q38_a ?? '') === 'YES')> YES
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q38_a" value="NO" Disabled @checked(($declaration->q38_a ?? '') === 'NO')> NO
      </label>
    </div>

  <p class="mt-2">if yes, give details:</p>
    <input class="border-b mb-2 w-full" type="text" Disabled name="q38_a_details" data-detail-for="q38_a" value="{{ $declaration->q38_a_details ?? '' }}">

</tr>


 <tr>
      <td class="border w-2/3 align-top border-t-0 border-black">
              <div class="ml-10 mb-3 mt-3">b. Have you resigned from the government service during the three (3)-month period before the last election to promote/actively campaign for a national or local candidate?
    
        </div>
        
      </td>
      
     <td class="border  px-2  border-black">
    <div class="flex h-full gap-20 mt-2">
      <label class="flex items-center gap-2 mt-1">
        <input type="checkbox" name="q38_b" value="YES" Disabled @checked(($declaration->q38_b ?? '') === 'YES')> YES
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q38_b" value="NO" Disabled @checked(($declaration->q38_b ?? '') === 'NO')> NO
      </label>
    </div>

  <p class="mt-2 mb-2">if yes, give details:</p>   
  <input class="border-b mb-2 w-full" type="text" Disabled name="q38_b_details" data-detail-for="q38_b" value="{{ $declaration->q38_b_details ?? '' }}">   

</tr>



<tr>
      <td class="border w-2/3 align-top border-t-0 border-black border">
              <div class="ml-5 mb-3 mt-3">39. Have you acquired the status of an immigrant or permanent resident of another country?
        </div>
        
      </td>
      
     <td class="border  px-2 border-black">
    <div class="flex h-full gap-20 mt-2">
      <label class="flex items-center gap-2 mt-1">
        <input type="checkbox" name="q39" value="YES" Disabled @checked(($declaration->q39 ?? '') === 'YES')> YES
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q39" value="NO" Disabled @checked(($declaration->q39 ?? '') === 'NO')> NO
      </label>
    </div>

  <p class="mt-2 mb-2">if yes, give details:</p>   
  <input class="border-b mb-2 w-full" type="text" Disabled name="q39_details" data-detail-for="q39" value="{{ $declaration->q39_details ?? '' }}">   

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
        <input type="checkbox" name="q40_a" value="YES" Disabled @checked(($declaration->q40_a ?? '') === 'YES')> YES
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q40_a" value="NO" Disabled @checked(($declaration->q40_a ?? '') === 'NO')> NO
      </label>
    </div>

      <p class="mt-2 mb-2">if yes, give details:</p>   
  <input class="border-b mb-2 w-full" type="text" Disabled name="q40_a_details" data-detail-for="q40_a" value="{{ $declaration->q40_a_details ?? '' }}">

     <div class="flex h-full gap-20 mt-2">
      <label class="flex items-center gap-2 mt-1">
        <input type="checkbox" name="q40_b" value="YES" @checked(($declaration->q40_b ?? '') === 'YES')> YES
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q40_b" value="NO" Disabled @checked(($declaration->q40_b ?? '') === 'NO')> NO
      </label>
    </div>

      <p class="mt-2 mb-2">if yes, give details:</p>   
  <input class="border-b mb-2 w-full" type="text" Disabled name="q40_b_details" data-detail-for="q40_b" value="{{ $declaration->q40_b_details ?? '' }}">

     <div class="flex h-full gap-20 mt-2">
      <label class="flex items-center gap-2 mt-1">
        <input type="checkbox" name="q40_c" value="YES" Disabled @checked(($declaration->q40_c ?? '') === 'YES')> YES
      </label>
      <label class="flex items-center gap-2">
        <input type="checkbox" name="q40_c" value="NO" Disabled @checked(($declaration->q40_c ?? '') === 'NO')> NO
      </label>
    </div>
  <p class="mt-2 mb-2">if yes, give details:</p>   
  <input class="border-b mb-2 w-full" type="text" name="q40_c_details" Disabled data-detail-for="q40_c" value="{{ $declaration->q40_c_details ?? '' }}">
</td>
</tr>

    </table> 

    <table class="w-full h-full border-l-2 border-b-0 border-black font-['Arial_Narrow','Arial',sans-serif]">
      <tr>
        <td class="border-l-3 border border-t-2 border-r-2 border-b-3 border-black" colspan="3">
          <span class="ml-2">41. REFERENCES </span><span class="font-semibold">(Person not related by consanguinity or affinity to applicant / appointee)</span>
        </td>
        <td class="flex-1 w-1/4 align-top text-center border-l-0 border-r-2 border-t-2 border-black" rowspan="11">
  <div class="mt-12 flex flex-col items-center">

    <!-- PASSPORT PHOTO -->
    @php $photoUrl = !empty($photoPath) ? asset('storage/'.$photoPath) : null; @endphp
    <label class="cursor-pointer">
      <div class="border-2 border-black w-[3.5cm] h-[4.5cm] flex items-center justify-center text-xs italic text-center relative overflow-hidden">
        <img id="photoPreview" src="{{ $passportPhotoUrl ?? '' }}" class="absolute inset-0 w-full h-full object-cover {{ empty($passportPhotoUrl) ? 'hidden' : '' }}" />
        <div id="photoPlaceholder" class="{{ empty($passportPhotoUrl) ? '' : 'hidden' }}">
          Passport-sized unfiltered<br>
          picture taken within<br>
          the last 6 months<br>
          4.5 cm × 3.5 cm
        </div>
      </div>
    </label>

    <div class="mt-2 text-xs">PHOTO</div>

    <!-- THUMB MARK -->
    <label class="cursor-pointer mt-6">
      <div class="mt-20 border-2 border-black w-[4.5cm] h-[4.5cm] flex items-center justify-center text-xs italic text-center relative overflow-hidden">
        <img id="thumbPreview" class="absolute inset-0 w-full h-full object-cover hidden" />
        <div id="thumbPlaceholder" class="mt-auto border-black border-t border-l-0 border-b-0 border-r-0 w-full">
          Right Thumbmark
        </div>
      </div>
    </label>
  </div>
</td>
      </tr>
      <tr class="border border-r-0 border-black">
        <th class="border font-light w-24 border-l-3 border-black">NAME</th>
        <th class="border font-light border-black">OFFICE / RESIDENTIAL ADDRESS </th>
        <th class="border font-light w-52 border-r-2 border-black">CONTACT NO. AND / OR EMAIL</th>
      </tr>
      @php
        // Reindex to zero-based keys so array-style access works for all saved references
        $refRows = ($references ?? collect())->values();
        $hasReferences = $refRows->filter(fn($ref) => !empty($ref->name) || !empty($ref->address) || !empty($ref->contact))->isNotEmpty();
        $maxRef = max(7, $refRows->count());
      @endphp
      @for ($i = 0; $i < $maxRef; $i++)
      @php $ref = $hasReferences ? ($refRows[$i] ?? null) : null; @endphp
      <tr class="border border-r-0 border-l-3 border-black align-top">
        <td class="border border-black align-top p-0 w-60 text-center" style="height:25px;">
          {{ !$hasReferences && $i === 0 ? 'N/A' : ($ref->name ?? '') }}
        </td>
        <td class="border border-black align-top p-0 text-center" style="height:25px;">
          {{ !$hasReferences && $i === 0 ? 'N/A' : ($ref->address ?? '') }}
        </td>
        <td class="border border-r-3 align-top p-0 border-r-2 border-black text-center" style="height:25px;">
          {{ !$hasReferences && $i === 0 ? 'N/A' : ($ref->contact ?? '') }}
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
        <td class="pr-5 p-0 align-top border-l border-black border-r-0" style="width: 60% !important; max-width: 60% !important; flex: none !important;">
        <table class="border-collapse text-xs border-2 ml-2 mt-2 h-[5.71cm] w-[10cm]">
  <!-- HEADER -->
  <tr>
    <td class="border px-2 py-1 font-semibold h-5 border-black" colspan="2">
      Government Issued ID (i.e. Passport, GSIS, SSS, PRC, Driver's License, etc.)<br>
      <span class="italic font-normal">
        PLEASE INDICATE ID Number and Date of Issuance
      </span>
    </td>
  </tr>

  <!-- ROW 1 -->
  <tr>
    <td class="border px-2 py-1 h-5 align-middle border-black">
      Government Issued ID:
    </td>
    <td class="border px-2 py-1 w-2/3 border-black">
      {{ $idInfo->gov_id ?? '' }}
    </td>
  </tr>

  <!-- ROW 2 -->
  <tr>
    <td class="border px-2 py-1 h-5 align-middle border-black">
      ID/License/Passport No.:
    </td>
    <td class="border px-2 py-1 border-black">
      {{ $idInfo->passport_licence_id?? '' }}
    </td>
  </tr>

  <!-- ROW 3 -->
  <tr>
    <td class="border px-2 py-1 h-5 align-middle border-black">
      Date/Place of Issuance:
    </td>
    <td class="border px-2 py-1 border-black">
      {{ $idInfo->date_place_issuance ?? '' }}
    </td>
  </tr>

</table>
        </td>
        <td class="p-0 align-top border-b-0 border-l-0 border-r-0 border-black" colspan="2">
          @php
            $signatureCleanPath = !empty($signaturePath) ? preg_replace('/^public\//', '', $signaturePath) : null;
            $signatureUrl = !empty($signatureCleanPath) ? asset('storage/'.$signatureCleanPath) : null;
          @endphp
          <table class="border-collapse text-xs border-3 mt-2 border-2 mb-2 w-[11.6cm]" style="margin-left: 122px;">
            <tr>
              <td class="h-[2.74cm] border-black text-center align-middle italic text-red-600 relative p-1">
                @if($signatureUrl)
                  <img src="{{ $signatureUrl }}" alt="Signature" class="absolute inset-0 w-full h-full object-contain" style="max-height:6cm; mix-blend-mode: multiply; filter: contrast(1.2) brightness(1.1);" onerror="this.alt='';this.style.display='none';">
                @else
                  (wet signature / e-signature / digital certificate)
                @endif
              </td>
            </tr>
            <tr>
              <td class="border border-black text-center py-1">Signature (Sign inside the box)</td>
            </tr>
           <tr>
            @include('pdsreview.partials.date-format-helper')
  <td>
    <div class="relative flex justify-center py-2">
      <div class="flex items-center space-x-1 relative">
        <div class="text-3xl text-center">{{ format_pds_date($declaration->date_accomplished) ?? '—' }}</div>
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
    @include('pdsreview.partials.date-format-helper')
    <table class="border-3 border-t-0 border-black w-full font-['Arial_Narrow','Arial',sans-serif]">
      <tr>
        <td class="p-2 text-center align-middle font-semibold text-sm">
          SUBSCRIBED AND SWORN to before me this <span style="border-bottom:1px solid black; min-width:150px; display:inline-block;">{{ format_pds_date($declaration->date_accomplished) ?? '' }}</span>, affiant exhibiting his/her validly issued government ID as indicated above.
        </td>
      </tr>
      <tr>
        <td class="p-2 align-top text-center">
          <table class="w-1/3 mx-auto h-full border-collapse text-xs border-3">
            <tr>
  <td class="border-black h-24 text-center align-middle italic text-red-600 relative overflow-hidden">

    @if($signatureUrl)
      <img src="{{ $signatureUrl }}" alt="Signature" class="absolute inset-0 w-full h-full object-contain" style="max-height:6.5cm; mix-blend-mode: multiply; filter: contrast(1.2) brightness(1.1);" onerror="this.alt='';this.style.display='none';">
    @else
      <!-- Placeholder / Text -->
      <div id="signaturePlaceholder">
        (wet signature / e-signature / digital certificate except for notary public)
      </div>
    @endif

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
      @if(empty($pdfMode))
      <div class="flex justify-between mt-4">
        <a href="{{ route('pdsreview.pdsreview3') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded shadow border border-gray-300 hover:bg-gray-300">Previous Page</a>
        <a href="{{ route('pdsreview.pdsreview5') }}" id="next-btn" class="px-4 py-2 bg-blue-600 text-white rounded shadow border border-blue-700 hover:bg-blue-700">Next Page</a>
      </div>
      @endif
</form>
@if(empty($pdfMode))
</x-app-layout>
@endif
@if(!empty($pdfMode))
</body>
</html>
@endif