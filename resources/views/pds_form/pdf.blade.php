@if(!empty($pdfMode))
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>PDS PDF</title>
@if(empty($pdfMode))
  @vite('resources/css/app.css')
@endif
<style>
        /* Print-friendly, spreadsheet-like grid tuned to fit on one A4 page */
        body { font-family: 'Arial', sans-serif; font-size: 18px; margin: 0; padding: 0; max-width: 100%; width: 100%; }
        html, body { background: #fff !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; min-height: 100%; }
        table { width: 100%; table-layout: fixed; border-collapse: collapse; background: #fff !important; }
        /* Apply 4px border to top-level tables without overlapping; remove stacked seams via border-top reset */
        body > table { border: 4px solid #000; margin-top: 0; }
        body > table + table { border-top: 0; }
        .section-table { border: 4px solid #000; margin-top: 0; }
        .section-table + .section-table { border-top: 0; }
        .section-table thead tr, .section-table thead th { page-break-inside: avoid; break-inside: avoid; }
        .border-3 { border: 3px solid #000; }
        .border-2 { border: 2px solid #000; }
        .pds-responsive { overflow-x: auto; }
        .pds-sheet { min-width: 980px; }
        .checkbox-large {
          transform: scale(1.3);
          border: 2px solid black;
          color: black; 
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
         table td {
          font-size: 24px;
                      }
        textarea { border: none; outline: none; padding: 8px; width: 100%; font: inherit; resize: none; background: transparent; line-height: 1.3; display: block; box-sizing: border-box; overflow: hidden; white-space: pre-wrap; word-break: break-word; min-height: 38px; height: auto; }
        textarea:focus { outline: none; box-shadow: none; }
        input[type="text"], textarea { width: 100%; background: transparent; border: none; border-bottom: 1px solid #000; outline: none; resize: none; overflow: hidden; padding: 2px 0; line-height: 1.2; font-family: 'Arial Narrow','Arial',sans-serif; font-size: inherit; }
        textarea:focus { outline: none; box-shadow: none; }
        input:focus { outline: none; box-shadow: none; }
        /* Fully custom checkbox */
        input[type="checkbox"] {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;

            width: 20px;             /* box size */
            height: 20px;
            border: 0.3px solid black; /* solid black border */
            border-radius: 0;        /* square corners */
            margin-right: 8px;
            vertical-align: middle;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            position: relative;
            background-color: white;
        }

        /* Checkmark inside the box */
        input[type="checkbox"]:checked::after {
            content: '✓';  
            color: black;           /* checkmark color */
            font-size: 10px;        /* adjust to fit box */
            line-height: 2;
            margin-top: 1px;
        }

        td, th { padding: 5px; word-wrap: break-word; overflow: visible; line-height: 1.1; vertical-align: middle; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }
        tr { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }
        td:not(.bg-\[#e7e7e7\]), th:not(.bg-\[#e7e7e7\]) { min-height: 22px; height: 22px; }
        /* Preserve colors in Browsershot print/PDF */
        * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }
        .table-wrapper { width: 100%; }
        .border { border: 1px solid #000 !important; }
        .border-2 { border: 2px solid #000 !important; }
        /* Keep header cells distinct and preserve colors for print */
        @media print {
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; color-adjust: exact !important; }
        }
        /* Form controls styled as lined cells */
        textarea { border: none; outline: none; padding: 8px; width: 100%; font: inherit; resize: none; background: transparent; line-height: 1.3; display: block; box-sizing: border-box; overflow: hidden; white-space: pre-wrap; word-break: break-word; min-height: 38px; height: auto; }
        textarea:focus { outline: none; box-shadow: none; }
        input[type="checkbox"] { width: 11px; height: 11px;}
        @if (!empty($pdfMode))
        .flex { display: flex; }
        .items-center { align-items: center; }
        .items-start { align-items: flex-start; }
        .items-end { align-items: flex-end; }
        .justify-between { justify-content: space-between; }
        .justify-center { justify-content: center; }
        .gap-4 { gap: 1rem; }
        .gap-6 { gap: 1.5rem; }
        .gap-3 { gap: 0.75rem; }
        .gap-2 { gap: 0.5rem; }
        .flex-1 { flex: 1 1 0; }
        .w-full { width: 100%; }
        .w-1\/2 { width: 50%; }
        .w-1\/3 { width: 33.333%; }
        .w-2\/3 { width: 66.666%; }
        .w-1\/4 { width: 25%; }
        .w-3\/4 { width: 75%; }
        .w-1\/5 { width: 20%; }
        .w-1\/6 { width: 16.666%; }
        .w-60 { width: 15rem; }
        .w-40 { width: 10rem; }
        .px-2 { padding-left: 0.5rem; padding-right: 0.5rem; }
        .px-4 { padding-left: 1rem; padding-right: 1rem; }
        .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
        .mt-2 { margin-top: 0.5rem; }
        .mt-4 { margin-top: 1rem; }
        .mb-2 { margin-bottom: 0.5rem; }
        .mb-4 { margin-bottom: 1rem; }
        .text-center { text-align: center; }
        .text-sm { font-size: 0.875rem; }
        .text-xs { font-size: 0.75rem; }
        .font-bold { font-weight: 700; }
        .font-semibold { font-weight: 600; }
        .italic { font-style: italic; }
        .uppercase { text-transform: uppercase; }
        .mx-auto { margin-left: auto; margin-right: auto; }
        .p-4 { padding: 1rem; }
        .p-2 { padding: 0.5rem; }
        .p-1 { padding: 0.25rem; }
        .ml-2 { margin-left: 0.5rem; }
        .mr-2 { margin-right: 0.5rem; }
        .ml-4 { margin-left: 1rem; }
        .mr-4 { margin-right: 1rem; }
        .leading-tight { line-height: 1.2; }
        .whitespace-nowrap { white-space: nowrap; }
        .bg-gray-200 { background: #e5e7eb; }
        .bg-gray-300 { background: #d1d5db; }
        .max-w-9xl, .max-w-7xl, .max-w-6xl { max-width: 100%; }
        body { font-family: 'Arial', sans-serif; }
        /* Grid helpers used in the table layout */
        .grid { display: grid; }
        .grid-cols-2 { grid-template-columns: repeat(2, minmax(0,1fr)); }
        .grid-cols-3 { grid-template-columns: repeat(3, minmax(0,1fr)); }
        .grid-cols-4 { grid-template-columns: repeat(4, minmax(0,1fr)); }
        .grid-cols-[100px_120px] { grid-template-columns: 100px 120px; }
        .grid-cols-[200px_1fr] { grid-template-columns: 200px 1fr; }
        .table-fixed { table-layout: fixed; }
        /* Border/color utilities frequently used */
        .border-black { border-color: #000 !important; }
        .border-b-0 { border-bottom: 0 !important; }
        .border-b-2 { border-bottom: 2px solid #000 !important; }
        .border-t { border-top: 1px solid #000 !important; }
        .border-l { border-left: 1px solid #000 !important; }
        .border-r { border-right: 1px solid #000 !important; }
        .border-black\/50 { border-color: rgba(0,0,0,0.5) !important; }
        .bg-\[#e7e7e7\] { background: #e7e7e7 !important; background-color: #e7e7e7 !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .bg-\[#8a8a8a\] { background: #8a8a8a !important; background-color: #8a8a8a !important; color: #fff !important; -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
        .bg-\[#8a8a8a\] * { color: #fff !important; }
        /* Text sizing fallbacks */
        .text-xl { font-size: 1.1rem; }
        .text-2xl { font-size: 1.25rem; }
        .text-3xl { font-size: 1.4rem; }
        .text-4xl { font-size: 1.6rem; }
        .text-base { font-size: 1rem; }
        .text-lg { font-size: 1.05rem; }

        /* Force uniform 3xl sizing for PDF/print while allowing opt-out via .keep-base */
        @media print {
            body,
            table,
            td,
            th,
            p,
            span,
            label,
            div,
            .text-xs,
            .text-sm,
            .text-base,
            .text-lg,
            .text-xl,
            .text-2xl,
            .text-3xl,
            .text-4xl {
                font-size: 2rem !important; /* Even larger for print */
                line-height: 1.3;
            }

            .keep-base {
                font-size: 1rem !important;
                line-height: 1.2;
            }
        }
        /* Width helpers */
        .w-300 { width: 300px; }
        .w-200 { width: 200px; }
        /* Misc */
        .pdf-table-container {
              page-break-after: always; /* each table starts on a new page */
              width: 210mm;
              height: 297mm;
              overflow: hidden;
              display: flex;
              justify-content: center;
              align-items: flex-start;
          }

          .pdf-scale-wrapper {
              transform-origin: top left;
              width: 100%;
          }

          table {
              width: 100%;
              border-collapse: collapse;
          }

          /* Auto page-fit per table (PDF only) */
          .pdf-page {
              page-break-after: always;
              width: 210mm;
              height: 297mm;
              padding: 8mm 8mm 10mm;
              box-sizing: border-box;
              overflow: hidden;
              display: flex;
              justify-content: center;
              align-items: flex-start;
          }

          .pdf-scale {
              transform-origin: top left;
              width: 100%;
          }

          /* Allow width to compress for scaling */
          .pdf-scale table { width: 100%; table-layout: fixed; }

          /* Keep tables flexible in PDF mode */
          body.pdf-mode .pds-sheet { min-width: 100%; max-width: 100%; }

          /* Uniform typography for PDF tables (larger, XL-like) */
          body.pdf-mode table {
              font-family: 'Arial Narrow','Arial',sans-serif;
              font-size: 18px;
              line-height: 1.15;
          }
          body.pdf-mode table td,
          body.pdf-mode table  {
              padding: 5px;
              font-size: 18px !important;
              font-family: 'Arial Narrow','Arial',sans-serif !important;
              font-weight: 400 !important;
              line-height: 1.15 !important;
          }
          body.pdf-mode table td *,
          body.pdf-mode table * {
              font-size: 18px !important;
              font-family: 'Arial Narrow','Arial',sans-serif !important;
              font-weight: 400 !important;
              line-height: 1.15 !important;
          }

          /* Extra specificity to beat table-wide overrides in PDF mode */
          body.pdf-mode table h1.pds-title {
            font-size: 45px !important;
            font-weight: 600 !important;
            font-family: 'Arial Black', Arial, sans-serif !important;
            margin-right: 350px;
          }

          /* Avoid table rows splitting across pages (defensive) */
          .pdf-page table { page-break-inside: avoid; }
          .pdf-page tr { page-break-inside: avoid; }
        .space-y-1 > :not([hidden]) ~ :not([hidden]) { margin-top: 0.25rem; }
        @endif
    </style>
</head>
<body class="{{ !empty($pdfMode) ? 'pdf-mode' : '' }}">
@endif

@php
  // Fallback: fetch signature/photo paths if they weren't passed into the view
  if (empty($signaturePath) || empty($photoPath)) {
      $userId = $userId ?? (\Illuminate\Support\Facades\Auth::id());
      if ($userId) {
          $signatureFiles = \Illuminate\Support\Facades\DB::table('pds_signature_files')->where('user_id', $userId)->first();
          $signaturePath = $signaturePath ?? ($signatureFiles->signature_file_path ?? null);
          $photoPath = $photoPath ?? ($signatureFiles->photo_file_path ?? null);
      }
  }

  // For PDF mode, always use base64 encoding (Chrome headless can't access HTTP URLs)
  // Controller should provide $signatureUrl and $photoUrl as base64, but fallback here if needed
  if (empty($signatureUrl) && !empty($signaturePath)) {
      $fullPath = storage_path('app/public/' . $signaturePath);
      if (file_exists($fullPath)) {
          $signatureUrl = 'data:image/png;base64,' . base64_encode(file_get_contents($fullPath));
      }
  }
  if (empty($photoUrl) && !empty($photoPath)) {
      $fullPath = storage_path('app/public/' . $photoPath);
      if (file_exists($fullPath)) {
          $photoUrl = 'data:image/png;base64,' . base64_encode(file_get_contents($fullPath));
      }
  }
@endphp

<table style="width:100%; border-collapse:collapse; border-bottom:0;" class="no-scale">
<td class="border-black" style="border:4px solid black; border-bottom:0;">
<div class="p-0 font-serif text-sm" @if(!empty($pdfMode)) style="width:100%;max-width:100%;" @endif>
  <!-- HEADER -->
  <header class="mb-2 flex items-start justify-between gap-4 w-full">
  <span style="font-family: 'Arial', Arial, sans-serif; font-weight: 600; font-size: 15px; font-style: italic;">
  CS Form No. 212
  <br>
  <span style="font-family: 'Arial Narrow', Arial, sans-serif; font-weight: 300; font-size: 12px; font-style: italic;">
    Revised 2025
  </span>
</span>


   <h1 class="pds-title">
  PERSONAL DATA SHEET
</h1>
  </header>

  <p class=" font-['Arial','sans-serif'] text-base italic font-bold mb-0">
    WARNING: Any misrepresentation made in the Personal Data Sheet shall cause the filing of administrative/criminal case/s against the person concerned.
 <p class="font-['Arial','sans-serif'] text-xs text-s italic font-bold mb-2" style="margin-top:20px;">
  READ THE ATTACHED GUIDE TO FILLING OUT THE PERSONAL DATA SHEET (PDS) BEFORE ACCOMPLISHING THE PDS FORM. <br>
  Print legibly if accomplished through own handwriting. Tick appropriate boxes 
  <span style="font-style:normal;">&#x2610;</span> and use separate sheet if necessary. 
  Indicate <span class="font-bold">N/A</span> if not applicable. 
  <span class="font-bold">DO NOT ABBREVIATE.</span>
</p>
</td>
</table>




  <!-- MAIN TABLE -->
  <table style="width:100%; border-collapse:collapse; font-family:'Arial Narrow', Arial, sans-serif; font-size:18px; border:4px solid black; border-bottom:0;">
    <!-- FIXED GRID -->
    <colgroup>
      <col style="width:8%">
      <col style="width:9%">
      <col style="width:10%">
      <col style="width:12%">
    </colgroup>

    <!-- SECTION HEADER -->
    <tr>
      <td colspan="4" class="font-['Arial_Narrow','Arial',sans-serif] text-white  italic text-3xl px-2 border-black font-bold" style="background-color:#8a8a8a;color:white; border:4px solid black;">
        I. PERSONAL INFORMATION
      </td>
    </tr>

    <!-- SURNAME -->
    <tr>
      <td class="bg-[#e7e7e7] px-2 align-middle" style="background-color:#e7e7e7;">
        1.  SURNAME
     </td>
          <td class="border relative" colspan="3">
            <div >
              {{ $personal->surname ?? '—' }}
            </div>
</td>
    </tr>

    <!-- FIRST NAME + EXTENSION -->
    <tr>
      <td class="bg-[#e7e7e7] px-2 align-middle" style="background-color:#e7e7e7;">
        2. FIRST NAME
      </td>
       <td class="border relative" colspan="2">
            <div>
               {{ $personal->firstname ?? '—' }}
  </div>
</td>

      <td class="bg-[#e7e7e7] align-top border" style="background-color:#e7e7e7;">
        <span class="italic px-2 text-xs">NAME EXTENSION (JR., SR)</span>
        <div class="ml-2">
           {{ $personal->name_extension ?? '—' }}
      </div>
      </td>
    </tr>

    <!-- MIDDLE NAME -->
    <tr class="text-xl">
      <td class="bg-[#e7e7e7] align-middle" style="padding-left: 18px;background-color:#e7e7e7;">
        MIDDLE NAME
    </td>
      <td colspan="3" class="border border-black h-10 align-middle">
        <div>
          {{ $personal->middlename ?? '—' }}
        </div>
      </td>
    </tr>
@include('pdsreview.partials.date-format-helper')
    <!-- DATE OF BIRTH + CITIZENSHIP -->
    <tr>
      <td class="bg-[#e7e7e7] px-2 align-middle border-black border" style="background-color:#e7e7e7;">
        3. DATE OF BIRTH
        <p class="font-normal ml-4">(dd/mm/yyyy)</p>
      </td>
      <td class="border">
        <div>
          {{ $personal->date_of_birth ?? '—' }}
      </div>
      </td>
        <td rowspan="3" 
        style="background-color:#e7e7e7; border-top:1px solid black; padding:4px; vertical-align:top; font-size:20px;">
      16. CITIZENSHIP
      <p style="text-align:center; margin-top:50px;">
        If holder of dual citizenship,<br>
        please indicate the details.
      </p>
    </td> 
      <td rowspan="3" class="border px-2 align-top text-base">
      <table class="w-full text-lg" style="border-collapse:collapse;">
        <tr>
          <td class="py-1">
            <div class="flex justify-center items-center gap-6">
              <label class="inline-flex items-center gap-2 text-2xl"><input type="checkbox" class="align-middle checkbox-large" name="citizenship[]" value="filipino" {{ optional($personal)->citizenship  == 'filipino' ? 'checked' : '' }} disabled> Filipino</label>
              <label class="inline-flex items-center gap-2"><input type="checkbox" class="align-middle checkbox-large" name="citizenship[]" value="dual_citizenship" {{ optional($personal)->citizenship  == 'dual_citizenship' ? 'checked' : '' }} disabled> Dual Citizenship</label>
            </div>
          </td>
        </tr>
        <tr>
          <td class="py-1">
            <div class="flex justify-center items-center gap-6" style="margin-left:80px">
              <label class="inline-flex items-center gap-2"><input type="checkbox" class="align-middle checkbox-large" name="citizenship[]" value="by_birth" {{ optional($personal)->citizenship  == 'by_birth' ? 'checked' : '' }} disabled> by birth</label>
              <label class="inline-flex items-center gap-2"><input type="checkbox" class="align-middle checkbox-large" name="citizenship[]" value="by_naturalization" {{ optional($personal)->citizenship  == 'by_naturalization' ? 'checked' : '' }} disabled> by naturalization</label>
            </div>
          </td>
        </tr>
        <tr>
          <td class="py-2 text-center">Pls. indicate country:</td>
        </tr>
        <tr>
                <td class="py-1">
        <div class="border-2 border-black text-xl flex justify-center items-center" 
            style="min-height:30px; padding:6px 8px; margin-bottom:10px; font-size:20px;">
          {{ $personal->country ?? '—' }}
        </div>
      </td>
        </tr>
      </table>
    </td>
    </tr>

    <!-- PLACE OF BIRTH -->
    <tr>
      <td class="bg-[#e7e7e7] px-2 border align-middle" style="background-color:#e7e7e7;">
        4. PLACE OF BIRTH
      </td>
      <td class="border h-10">
        <div 
          class="h-full w-full
           min-h-full
           wrap-break-words whitespace-normal
           outline-none
            py-2">
    {{ $personal->place_of_birth ?? '—' }}
      </div>
      </td>
    </tr>

    <!-- SEX AT BIRTH -->
    <tr>
  <td class="bg-[#e7e7e7] align-middle px-2 border" style="background-color:#e7e7e7; vertical-align:middle;">
    5. SEX AT BIRTH
  </td>
  <td class="border" style="padding:4px; vertical-align:middle; text-align:center;">
    <div style="display:grid; grid-template-columns: repeat(2, 1fr); column-gap:50px; justify-items:center;">
      <label style="display:flex; align-items:center; gap:6px; margin-right:12px;">
        <input class="checkbox-large" type="checkbox" value="male" disabled {{ $personal->sex == 'male' ? 'checked' : '' }}>
        Male
      </label>

      <label style="display:flex; align-items:center; gap:6px; margin-right:25px;">
        <input class="checkbox-large" type="checkbox" value="female" disabled {{ $personal->sex == 'female' ? 'checked' : '' }}>
        Female
      </label>

    </div>
  </td>
</tr>

    <!-- SIMPLE ROW TEMPLATE -->
   <td class="px-2" style="background-color:#e7e7e7; vertical-align:top; border:1px solid black;">
    6. CIVIL STATUS
</td>
    <td style="border:1px solid black; vertical-align:middle; text-align:center; padding:2px;">
  <div style="display:flex; justify-content:center; align-items:center; padding:0;">
    <div style="display:grid; grid-template-columns: repeat(2, 1fr); row-gap:20px; column-gap:80px; font-size:12px;">

      <label style="display:flex; align-items:center; gap:4px;" class="text-2xl">
        <input type="checkbox" name="civilstatus[]" value="single" class="checkbox-large" disabled {{ $personal->civil_status == 'single' ? 'checked' : '' }}>
        Single
      </label>

      <label style="display:flex; align-items:center; gap:4px;" class="text-2xl">
        <input type="checkbox" name="civilstatus[]" value="married" class="checkbox-large" disabled {{ $personal->civil_status == 'married' ? 'checked' : '' }}>
        Married
      </label>

      <label style="display:flex; align-items:center; gap:4px;" class="text-2xl">
        <input type="checkbox" name="civilstatus[]" value="widowed" class="checkbox-large" disabled {{ $personal->civil_status == 'widowed' ? 'checked' : '' }}>
        Widowed
      </label>

      <label style="display:flex; align-items:center; gap:4px;"class="text-2xl">
        <input type="checkbox" name="civilstatus[]" value="separated" class="checkbox-large" disabled {{ $personal->civil_status == 'separated' ? 'checked' : '' }}>
        Separated
      </label>

      <label style="display:flex; align-items:center; gap:4px;"class="text-2xl">
        <input type="checkbox" name="civilstatus[]" value="other/s" class="checkbox-large" disabled {{ $personal->civil_status == 'Other/s' ? 'checked' : '' }}>
        Other/s:
      </label>

    </div>
  </div>
</td>

<td rowspan="3" colspan="2"
      class="border align-top bg-[#e7e7e7]"
      style="padding:0;">

    <div class="flex" style="width:100%; height:100%;">
      
      <!-- LEFT TABLE -->
      <table style="width:35%; padding:0; margin:0; border-collapse:collapse; table-layout:fixed; border-right:0; height:100%;">
        <tr>
          <td class="border-black border-t-0" style="background-color:#e7e7e7; vertical-align:top;">
            17. RESIDENTIAL ADDRESS
          </td>
        </tr>
        <tr class="h-10">
          <td class="text-center py-2" style="background-color:#e7e7e7; vertical-align:bottom;">
            ZIP CODE
          </td>
        </tr>
      </table>
      <!-- RIGHT TABLE -->
      <table class="bg-white h-full border-l" style="width:65%; border-collapse:collapse; table-layout:fixed; border-left:0; height:100%;">
        <tr>
          <td class="align-top">
            <div class="grid grid-cols-2 w-full text-center">
              <div class="py-1  whitespace-pre-wrap">{{ $address->present_house_block_lot ?? '—' }}</div>
              <div class="py-1  whitespace-pre-wrap">{{ $address->present_street ?? '—' }}</div>
            </div>
          </td>
        </tr>
        <tr>
            <td class="border-t border-black/50" style="padding:2px; height:30px; vertical-align:middle;">
      <div style="display:grid; grid-template-columns: repeat(2, 1fr); text-align:center; font-size:15px; line-height:1.2;">
        <p style="margin:0;">House/Block/Lot No.</p>
        <p style="margin:0;">Street</p>
      </div>
    </td>
        </tr>
        <tr>
          <td class="h-auto align-top border-t border-black">
            <div class="grid grid-cols-2 w-full text-center">
              <div class="py-1  whitespace-pre-wrap">{{ $address->present_subdivision_village ?? '—' }}</div>
              <div class="py-1  whitespace-pre-wrap">{{ $address->present_barangay ?? '—' }}</div>
            </div>
          </td>
        </tr>
        <tr>
          <td class="border-t border-black/50" style="padding:2px; height:25px; vertical-align:middle;">
      <div style="display:grid; grid-template-columns: repeat(2, 1fr); text-align:center; font-size:15px; line-height:1.2;">
              <p style="margin:0;">Subdivision/Village</p>
              <p style="margin:0;">Barangay</p>
            </div>
          </td>
        </tr>
        <tr>
          <td class="h-auto align-top border-t border-black">
            <div class="grid grid-cols-2 w-full text-center">
              <div class="py-1  whitespace-pre-wrap">{{ $address->present_city_municipality ?? '—' }}</div>
              <div class="py-1  whitespace-pre-wrap">{{ $address->present_province ?? '—' }}</div>
            </div>
          </td>
        </tr>
        <tr>
         <td class="border-t border-black/50" style="padding:2px; height:30px; vertical-align:middle;">
      <div style="display:grid; grid-template-columns: repeat(2, 1fr); text-align:center; font-size:15px; line-height:1.2;">
              <p style="margin:0;">City/Municipality</p>
              <p style="margin:0;">Province</p>
            </div>
          </td>
        </tr>
        <tr>
          <td class="border-t border-black text-center py-2 border-b-0">
            {{ $address->present_zip_code ?? '—' }}
          </td>
        </tr>
      </table>

    </div>
  </td>




    <tr>
      <td class="px-2 border font-['Arial_Narrow','Arial',sans-serif]" style="background-color:#e7e7e7;">7. HEIGHT (m)</td>
      <td class="border px-2 h-10">
        {{ $personal->height ?? '—' }}
      </td>
    </tr>

    <tr>
      <td class="font-['Arial_Narrow','Arial',sans-serif] px-2 border" style="background-color:#e7e7e7;">8. WEIGHT (kg)</td>
      <td class="border px-2 h-10">
        {{ $personal->weight ?? '—' }}
      </td>
    </tr>


      <td class="font-['Arial_Narrow','Arial',sans-serif] px-2 border h-8" style="background-color:#e7e7e7;">9. BLOOD TYPE</td>
      <td class="border px-2 align-middle"> 
       {{ $personal->blood_type ?? '—' }}
      </td>


  <td rowspan="4" colspan="2"
      class="border align-top bg-[#e7e7e7]"
      style="padding:0;">

    <div class="flex w-full h-full" style="background-color:#e7e7e7;">
      
      <!-- LEFT TABLE -->
      <table class="border-b font-['Arial_Narrow','Arial',sans-serif] text-base" style="width:35%; background-color:#e7e7e7; padding:0; margin:0; border-collapse:collapse; table-layout:fixed; border-right:0; height:100%; background-color:#e7e7e7;">
        <tr style="height:100%;">
          <td class="px-2 py-1 align-top text-xl" style="background-color:#e7e7e7; vertical-align:top;">
            18. PERMANENT ADDRESS
          </td>
        </tr>
      </table>

      <!-- RIGHT TABLE -->
      <table class="bg-white border-l" style="width:65%; border-collapse:collapse; table-layout:fixed; border-left:0; height:100%;">
        <tr>
          <td class="align-top">
            <div class="grid grid-cols-2 w-full text-center">
              <div class="py-1  whitespace-pre-wrap">{{ $address->permanent_house_block_lot ?? '—' }}</div>
              <div class="py-1  whitespace-pre-wrap">{{ $address->permanent_street ?? '—' }}</div>
            </div>
          </td>
        </tr>
          
          <tr class="h-2">
           <td class="border-t border-black/50" style="padding:2px; height:30px; vertical-align:middle;">
      <div style="display:grid; grid-template-columns: repeat(2, 1fr); text-align:center; font-size:15px; line-height:1.2;">
              <p style="margin:0;">House/Block/Lot No.</p>
              <p style="margin:0;">Street</p>
            </div>
          </td>
        </tr>
        <tr>
          <td class="h-auto align-top border-t border-black">
            <div class="grid grid-cols-2 w-full text-center">
              <div class="py-1  whitespace-pre-wrap">{{ $address->permanent_subdivision_village ?? '—' }}</div>
              <div class="py-1  whitespace-pre-wrap">{{ $address->permanent_barangay ?? '—' }}</div>
            </div>
          </td>
        </tr>
        <tr>
          <td class="border-t border-black/50" style="padding:2px; height:30px; vertical-align:middle;">
      <div style="display:grid; grid-template-columns: repeat(2, 1fr); text-align:center; font-size:15px; line-height:1.2;">
              <p style="margin:0;">Subdivision/Village</p>
              <p style="margin:0;">Baranggay</p>
            </div>
          </td>
        </tr>
        <tr>
          <td class="h-auto align-top border-t border-black">
            <div class="grid grid-cols-2 w-full text-center">
              <div class="py-1  whitespace-pre-wrap">{{ $address->permanent_city_municipality ?? '—' }}</div>
              <div class="py-1  whitespace-pre-wrap">{{ $address->permanent_province ?? '—' }}</div>
            </div>
          </td>
        </tr>
        <tr >
          <td class="border-t border-black/50" style="padding:2px; height:30px; vertical-align:middle;">
      <div style="display:grid; grid-template-columns: repeat(2, 1fr); text-align:center; font-size:15px; line-height:1.2;">
              <p style="margin:0;">City/Municipality</p>
              <p style="margin:0;">Province</p>
            </div>
          </td>
        </tr>
      </table>

    </div>
  </td>    

    <tr>
      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border" style="background-color:#e7e7e7;">10. UMID ID NO.</td>
      <td class="border px-2 h-10">
        {{ $personal->umid_no ?? '—' }}
      </td>
    </tr>

    <tr>
      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border" style="background-color:#e7e7e7;">11. PAG-IBIG ID NO.</td>
      <td class="border px-2 h-10">
        {{ $personal->pagibig_no ?? '—' }}
      </td>
    </tr>

    <tr>
      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border" style="background-color:#e7e7e7;">12. PHILHEALTH NO.</td>
      <td class="border px-2 h-10">
         {{ $personal->philhealth_no ?? '—' }}
      </td>
    </tr>

    @php
        $childNames = $children->pluck('firstname')->toArray();
        $childDobs = $children->pluck('date_of_birth')->toArray();
        $childRowCount = max(14, count($childNames), count($childDobs));
        $childNames = array_pad($childNames, $childRowCount, '');
        $childDobs = array_pad($childDobs, $childRowCount, '');
        $childIndex = 0;
    @endphp

    
    <tr>
      <td class="bg-[#e7e7e7] font-['Arial_Narrow','Arial',sans-serif] px-2 border h-8" style="background-color:#e7e7e7;">13. PhilSys Number (PSN):</td>
      <td class="border px-2 align-middle">
          {{ $personal->philsys_no ?? '—' }}
      </td>
      <td class="px-2 align-middle border" style="background-color:#e7e7e7; width:35%;">19. TELEPHONE NO.</td>
      <td class="border px-2 align-middle"  style="width:65%;">{{ $contact->telephone_no ?? '—' }}</td>
    </tr>
   
    <tr>
      <td class="font-['Arial_Narrow','Arial',sans-serif] px-2 border" style="background-color:#e7e7e7;">14. TIN ID</td>
      <td class="border px-2 align-middle">
        {{ $personal->tin_no ?? '—' }}
      </td>
      <td class="px-2 align-middle border" style="background-color:#e7e7e7; width:35%;">20. MOBILE NO.</td>
      <td class="  px-2 align-middle border" style="width:65%;">{{ $contact->mobile_no ?? '—' }}</td>
    </tr>

    <tr>
      <td class="bg-[#e7e7e7]    font-['Arial_Narrow','Arial',sans-serif] px-2" style="background-color:#e7e7e7; border:1px solid black; border-bottom:0;">15. AGENCY EMPLOYEE ID</td>
      <td class="  px-2 align-middle">
         {{ $personal->agency_employee_no ?? '—' }}
      </td>
      <td class="bg-[#e7e7e7] px-2 align-middle " style="background-color:#e7e7e7; width:35%; border:1px solid black; border-bottom:0;">21. E-MAIL ADDRESS (if any)</td>
      <td class=" px-2 align-middle" style="width:65%;">{{ $contact->email_address ?? '—' }}</td>
    </tr>

  </table>


  {{-- II. FAMILY BACKGROUND --}}
<table style="width:100%; border-collapse:collapse; font-family:'Arial Narrow','sans-serif'; font-size:18px; border:4px solid black; border-bottom:0;" class="border-black border-b-0">
    <colgroup>
      <col style="width:25%">
      <col style="width:10%">
      <col style="width:12%">
      <col style="width:16%">
      <col style="width:20%">
      <col style="width:22%">
    </colgroup>

    <!-- SECTION HEADER -->
    <tr>
        <td colspan="6" style="background:#8a8a8a; color:#fff; font-style:italic; font-weight:bold; padding:6px; border:4px solid black;" class="text-3xl">
            II. FAMILY BACKGROUND
        </td>
    </tr>

    <!-- SPOUSE + CHILD HEADER -->
    <tr>
        <td style="background:#e7e7e7; padding:4px; vertical-align:middle;">22. SPOUSE'S SURNAME</td>
        <td colspan="3" style="border:1px solid black;">
            {{ $spouse->surname ?? '—' }}
        </td>
        <td style="border:1px solid black; text-align:center;">23. NAME OF CHILDREN</td>
        <td style="border:1px solid black; text-align:center;">DATE OF BIRTH (dd/mm/yyyy)</td>
    </tr>

    @php $childIndex = 0; @endphp

    <!-- Spouse First Name + Extension -->
    <tr>
        <td style="background:#e7e7e7; padding-left:8px;">FIRST NAME</td>
        <td colspan="2" style="border:1px solid black;">{{ $spouse->firstname ?? '—' }}</td>
              <td style="background:#e7e7e7; font-style:italic; padding:4px;">
        <span style="font-size:12px;">NAME EXTENSION (JR., SR)</span><br>
        <span style="font-size:20px; font-style:normal;">
          {{ $spouse->name_extension ?? '—' }}
        </span>
      </td>
        <td style="border:1px solid black; text-align:center;">{{ $childNames[$childIndex] ?? '' }}</td>
        <td style="border:1px solid black; text-align:center;">{{ format_pds_date($childDobs[$childIndex]) ?? '' }}</td>
        @php $childIndex++; @endphp
    </tr>

    <!-- Spouse Middle Name -->
    <tr>
        <td style="background:#e7e7e7; padding-left:8px;">MIDDLE NAME</td>
        <td colspan="3" style="border:1px solid black;">{{ $spouse->middlename ?? '—' }}</td>
        <td style="border:1px solid black; text-align:center;">{{ $childNames[$childIndex] ?? '' }}</td>
        <td style="border:1px solid black; text-align:center;">{{ format_pds_date($childDobs[$childIndex]) ?? '' }}</td>
        @php $childIndex++; @endphp
    </tr>

    <!-- Spouse Occupation -->
    <tr>
        <td style="background:#e7e7e7; padding-left:4px;">OCCUPATION</td>
        <td colspan="3" style="border:1px solid black;">{{ $spouse->occupation ?? '—' }}</td>
        <td style="border:1px solid black; text-align:center;">{{ $childNames[$childIndex] ?? '' }}</td>
        <td style="border:1px solid black; text-align:center;">{{ format_pds_date($childDobs[$childIndex]) ?? '' }}</td>
        @php $childIndex++; @endphp
    </tr>

    <!-- Spouse Employer -->
    <tr>
        <td style="background:#e7e7e7; padding-left:4px;">EMPLOYER/BUSINESS NAME</td>
        <td colspan="3" style="border:1px solid black;">{{ $spouse->employer ?? '—' }}</td>
        <td style="border:1px solid black; text-align:center;">{{ $childNames[$childIndex] ?? '' }}</td>
        <td style="border:1px solid black; text-align:center;">{{ format_pds_date($childDobs[$childIndex]) ?? '' }}</td>
        @php $childIndex++; @endphp
    </tr>

    <!-- Spouse Business Address -->
    <tr>
        <td style="background:#e7e7e7; padding-left:4px;">BUSINESS ADDRESS</td>
        <td colspan="3" style="border:1px solid black;">{{ $spouse->business_address ?? '—' }}</td>
        <td style="border:1px solid black; text-align:center;">{{ $childNames[$childIndex] ?? '' }}</td>
        <td style="border:1px solid black; text-align:center;">{{ format_pds_date($childDobs[$childIndex]) ?? '' }}</td>
        @php $childIndex++; @endphp
    </tr>

    <!-- Spouse Telephone -->
    <tr>
        <td style="background:#e7e7e7; padding-left:4px;">TELEPHONE NO.</td>
        <td colspan="3" style="border:1px solid black;">{{ $spouse->telephone_no ?? '—' }}</td>
        <td style="border:1px solid black; text-align:center;">{{ $childNames[$childIndex] ?? '' }}</td>
        <td style="border:1px solid black; text-align:center;">{{ format_pds_date($childDobs[$childIndex]) ?? '' }}</td>
        @php $childIndex++; @endphp
    </tr>

    <!-- Father -->
    <tr>
        <td style="background:#e7e7e7; padding-left:4px;">24. FATHER'S SURNAME</td>
        <td colspan="3" style="border:1px solid black;">{{ $father->surname ?? '—' }}</td>
        <td style="border:1px solid black; text-align:center;">{{ $childNames[$childIndex] ?? '' }}</td>
        <td style="border:1px solid black; text-align:center;">{{ format_pds_date($childDobs[$childIndex]) ?? '' }}</td>
        @php $childIndex++; @endphp
    </tr>

    <tr>
        <td style="background:#e7e7e7; padding-left:8px;">FIRST NAME</td>
        <td colspan="2" style="border:1px solid black;">{{ $father->firstname ?? '—' }}</td>
        <td style="background:#e7e7e7; font-style:italic; padding:4px;">
        <span style="font-size:12px;">NAME EXTENSION (JR., SR)</span><br>
        <span style="font-size:20px; font-style:normal;">
          {{ $spouse->name_extension ?? '—' }}
        </span>
      </td>
        <td style="border:1px solid black; text-align:center;">{{ $childNames[$childIndex] ?? '' }}</td>
        <td style="border:1px solid black; text-align:center;">{{ format_pds_date($childDobs[$childIndex]) ?? '' }}</td>
        @php $childIndex++; @endphp
    </tr>

    <tr>
        <td style="background:#e7e7e7; padding-left:8px;">MIDDLE NAME</td>
        <td colspan="3" style="border:1px solid black;" class="border-b-0">{{ $father->middlename ?? '—' }}</td>
        <td style="border:1px solid black; text-align:center;">{{ $childNames[$childIndex] ?? '' }}</td>
        <td style="border:1px solid black; text-align:center;">{{ format_pds_date($childDobs[$childIndex]) ?? ''}}</td>
        @php $childIndex++; @endphp
    </tr>

    <!-- Mother -->
    <tr>
        <td style="background:#e7e7e7; padding-left:4px; border:1px solid black; border-top:1px solid black;" colspan="4">25. MOTHER'S MAIDEN NAME</td>
        <td style="border:1px solid black; text-align:center;">{{ $childNames[$childIndex] ?? '' }}</td>
        <td style="border:1px solid black; text-align:center;">{{ format_pds_date($childDobs[$childIndex]) ?? '' }}</td>
        @php $childIndex++; @endphp
    </tr>

    <tr>
        <td style="background:#e7e7e7; padding-left:8px;">SURNAME</td>
        <td colspan="3" style="border:1px solid black;">{{ $mother->surname ?? '—' }}</td>
        <td style="border:1px solid black; text-align:center;">{{ $childNames[$childIndex] ?? '' }}</td>
        <td style="border:1px solid black; text-align:center;">{{ format_pds_date($childDobs[$childIndex]) ?? '' }}</td>
        @php $childIndex++; @endphp
    </tr>

    <tr>
        <td style="background:#e7e7e7; padding-left:8px;">FIRST NAME</td>
        <td colspan="3" style="border:1px solid black;">{{ $mother->firstname ?? '—' }}</td>
        <td style="border:1px solid black; text-align:center;">{{ $childNames[$childIndex] ?? '' }}</td>
        <td style="border:1px solid black; text-align:center;">{{ format_pds_date($childDobs[$childIndex]) ?? ''}}</td>
        @php $childIndex++; @endphp
    </tr>

    <tr style="border-bottom:0;">
        <td style="background:#e7e7e7; padding-left:8px; border-bottom:0;">MIDDLE NAME</td>
        <td colspan="3" style="border:1px solid black; border-bottom:0;" class="border-b-0">{{ $mother->middlename ?? '—' }}</td>
        <td style="border:1px solid black; text-align:center; border-bottom: 0;">{{ $childNames[$childIndex] ?? '' }}</td>
        <td style="border:1px solid black; text-align:center; border-bottom: 0;">{{ format_pds_date($childDobs[$childIndex]) ?? '' }}</td>
        @php $childIndex++; @endphp
    </tr>
</table>
<table class="w-full border-4 border-black border-collapse table-fixed text-base border-t-0" style="font-family:'Arial Narrow','sans-serif'; border:4px solid black; border-top:0;">

  @php
      $normalizeLevel = function($level) {
          if (!$level) return '';
          $map = [
              'elementary' => 'ELEMENTARY',
              'secondary' => 'SECONDARY',
              'vocational' => 'VOCATIONAL / TRADE COURSE',
              'vocational / trade course' => 'VOCATIONAL / TRADE COURSE',
              'vocational/trade course' => 'VOCATIONAL / TRADE COURSE',
              'college' => 'COLLEGE',
              'graduate_studies' => 'GRADUATE STUDIES',
              'graduate studies' => 'GRADUATE STUDIES',
          ];
          $key = strtolower(trim($level));
          return $map[$key] ?? strtoupper($level);
      };

      $normalizedEdu = $education->map(function($rec) use ($normalizeLevel) {
          if (is_array($rec)) {
              $rec['level'] = $normalizeLevel($rec['level'] ?? '');
              return $rec;
          }
          $rec->level = $normalizeLevel($rec->level ?? '');
          return $rec;
      });

      $eduByLevel = $normalizedEdu->groupBy('level');

      $getField = function($rec, string $field) {
          if (!$rec) return '';
          if (is_array($rec)) return $rec[$field] ?? '';
          return $rec->$field ?? '';
      };

      $eduVal = function(string $level, string $field) use ($eduByLevel, $getField, $normalizeLevel) {
          $rec = optional($eduByLevel->get($normalizeLevel($level)))->first();
          return $getField($rec, $field);
      };

      $eduCourse = function(string $level) use ($eduVal) {
          $course = $eduVal($level, 'degree_course');
          if ($course === '') {
              $course = $eduVal($level, 'basic_education');
          }
          return $course;
      };

      $eduHonors = function(string $level) use ($eduVal) {
          $honors = $eduVal($level, 'academic_honors');
          if ($honors === '') {
              $honors = $eduVal($level, 'scholarship_acadhonors');
          }
          return $honors;
      };

      $eduSchool = function(string $level) use ($eduVal) {
          $name = trim((string) $eduVal($level, 'school_name'));
          return $name === '' ? 'NA' : $name;
      };

      $extraRows = function(string $level) use ($eduByLevel, $getField, $normalizeLevel) {
          return optional($eduByLevel->get($normalizeLevel($level)))->slice(1) ?? collect();
      };
  @endphp

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
        class="font-['Arial_Narrow','Arial',sans-serif] font-bold bg-[#8a8a8a] text-white  italic text-3xl px-2" style="background-color:#8a8a8a; color:#fff; border:4px solid black;">
      III. EDUCATIONAL BACKGROUND
    </td>
  </tr>

  <!-- HEADER ROW 1 -->
  <tr>
    <th class="border" rowspan="2" style="font-weight: normal;"><div style="text-align: left;">
    <span style="margin-right: 70px;">26.</span> <span>LEVEL</span>
    </div></th>
    <th class="border" rowspan="2" style="font-weight: normal;">NAME OF SCHOOL<br>(Write in Full)</th>
    <th class="border" rowspan="2" style="font-weight: normal;">BASIC EDUCATION / DEGREE / COURSE<br>(Write in full)</th>
    <th class="border text-center" colspan="2" style="font-weight: normal;">PERIOD OF ATTENDANCE</th>
    <th class="border" rowspan="2" style="font-weight: normal;">
      HIGHEST LEVEL/<br>UNITS EARNED<br>
      <span class="text-base">(if not graduated)</span>
    </th>
    <th class="border" rowspan="2" style="font-weight: normal;">YEAR GRADUATED</th>
    <th class="border" rowspan="2" style="font-weight: normal;">SCHOLARSHIP / ACADEMIC<br>HONORS RECEIVED</th>
</tr>

<tr>
    <th class="border text-center" style="font-weight: normal;">FROM</th>
    <th class="border text-center" style="font-weight: normal;">TO</th>
</tr>


  <!-- DATA ROW -->
  <tr class="min-h-[20]" style="width: 20%;">
    <td class="border text-center align-middle h-20">ELEMENTARY</td>

    <!-- EDITABLE CELL PATTERN -->
     <td
          class="border h-10 align">
          <div class="edu-cell h-full w-full text-center align-middle">
            {{ $eduSchool('elementary') }}
          </div>
      </td>

      <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center align-middle">
            {{ $eduCourse('elementary') }}
      </td>

      <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('elementary','from') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('elementary','to') }}
      </td>

      <td
            class="border h-10">
            <div class="edu-cell h-full w-full text-center">
              {{ $eduVal('elementary','highest_level') }}
        </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('elementary','year_graduated') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduHonors('elementary') }}
      </td>
  </tr>
  @foreach($extraRows('ELEMENTARY') as $rec)
    <tr class="min-h-[20]" style="width: 20%;">
      <td class="border text-center align-middle h-20">&nbsp;</td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'school_name') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'degree_course') ?: $getField($rec,'basic_education') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'from') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'to') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'highest_level') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'year_graduated') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'academic_honors') ?: $getField($rec,'scholarship_acadhonors') }}</div></td>
    </tr>
  @endforeach


   <tr class="min-h-[20]" style="width: 20%;">
    <td class="border text-center align-middle h-20">SECONDARY</td>

    <!-- EDITABLE CELL PATTERN -->
     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center align-middle">
            {{ $eduSchool('secondary') }}
      </td>

      <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduCourse('secondary') }}
      </td>

      <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('secondary','from') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('secondary','to') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('secondary','highest_level') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('secondary','year_graduated') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduHonors('secondary') }}
      </td>
  </tr>
  @foreach($extraRows('SECONDARY') as $rec)
    <tr class="min-h-[20]" style="width: 20%;">
      <td class="border text-center align-middle h-20">&nbsp;</td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'school_name') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'degree_course') ?: $getField($rec,'basic_education') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'from') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'to') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'highest_level') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'year_graduated') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'academic_honors') ?: $getField($rec,'scholarship_acadhonors') }}</div></td>
    </tr>
  @endforeach

   <tr class="min-h-[20]" style="width: 20%;">
    <td class="border text-center align-middle h-20">VOCATIONAL / TRADE COURSE</td>

    <!-- EDITABLE CELL PATTERN -->
    <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center align-middle">
            {{ $eduSchool('vocational') }}
      </td>

      <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduCourse('vocational') }}
      </td>

      <td
          class="border h-10">
          <div class="edu-cell h-full w-full  text-center">
            {{ $eduVal('vocational','from') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('vocational','to') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('vocational','highest_level') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full   text-center">
            {{ $eduVal('vocational','year_graduated') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full   text-center">
            {{ $eduHonors('vocational') }}
      </td>
  </tr>
  @foreach($extraRows('VOCATIONAL / TRADE COURSE') as $rec)
    <tr class="min-h-[20]" style="width: 20%;">
      <td class="border text-center align-middle h-20">&nbsp;</td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'school_name') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'degree_course') ?: $getField($rec,'basic_education') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'from') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'to') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'highest_level') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'year_graduated') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'academic_honors') ?: $getField($rec,'scholarship_acadhonors') }}</div></td>
    </tr>
  @endforeach

   <tr class="min-h-[20]" style="width: 20%;">
    <td class="border text-center align-middle h-20">COLLEGE</td>

    <!-- EDITABLE CELL PATTERN -->
    <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center align-middle">
            {{ $eduSchool('college') }}
      </td>

      <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduCourse('college') }}
      </td>

      <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('college','from') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('college','to') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full   text-center">
            {{ $eduVal('college','highest_level') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('college','year_graduated') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduHonors('college') }}
      </td>
  </tr>
  @foreach($extraRows('COLLEGE') as $rec)
    <tr class="min-h-[20]" style="width: 20%;">
      <td class="border text-center align-middle h-20">&nbsp;</td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'school_name') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'degree_course') ?: $getField($rec,'basic_education') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'from') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'to') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'highest_level') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'year_graduated') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'academic_honors') ?: $getField($rec,'scholarship_acadhonors') }}</div></td>
    </tr>
  @endforeach

  <tr class="min-h-[20]" style="width: 20%;">
    <td class="border text-center align-middle h-20">GRADUATE STUDIES</td>

    <!-- EDITABLE CELL PATTERN -->
   <td
          class="border h-10">
             <div class="edu-cell h-full w-full text-center align-middle">
            {{ $eduSchool('graduate_studies') }}
      </td>

      <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduCourse('graduate_studies') }}
      </td>

      <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('graduate_studies','from') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('graduate_studies','to') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('graduate_studies','highest_level') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduVal('graduate_studies','year_graduated') }}
      </td>

     <td
          class="border h-10">
          <div class="edu-cell h-full w-full text-center">
            {{ $eduHonors('graduate_studies') }}
      </td>
  </tr>
  @foreach($extraRows('GRADUATE STUDIES') as $rec)
    <tr class="min-h-[20]" style="width: 20%;">
      <td class="border text-center align-middle h-20">&nbsp;</td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'school_name') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'degree_course') ?: $getField($rec,'basic_education') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'from') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'to') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'highest_level') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'year_graduated') }}</div></td>
      <td class="border h-10"><div class="edu-cell h-full w-full text-center">{{ $getField($rec,'academic_honors') ?: $getField($rec,'scholarship_acadhonors') }}</div></td>
    </tr>
  @endforeach

 

  <tr>
    <td class="border h-2 text-center text-xl font-bold italic align-middle">
      SIGNATURE
    </td>

 <td class="border" colspan="2">
    <div class="h-full w-full flex flex-col items-center justify-center p-2">
        @if($signatureUrl)
            <img src="{{ $signatureUrl }}" alt="Signature" style="max-height:150px; object-fit:contain;">
        @endif
    </div>
</td>

    <td class="border text-center text-xl font-bold italic align-middle" colspan="2">
      DATE
    </td>
@include('pdsreview.partials.date-format-helper')
    <td colspan="3"
          class="border h-16">
          <div class="h-full w-full flex items-center justify-center text-lg text-center" style="font-size: 30px;"> 
            {{ format_pds_date($declaration->date_accomplished) ?? '—' }}
          </div>
      </td>
</table>

<div class="w-full text-base keep-base" style="text-align:right; font-family:'Arial_Narrow','sans-serif'; margin-top:10px;">
    CS FORM 212 (Revised 2025), Page 1 of 5
</div>
    </table>
  </div>
</div>
<div style="page-break-before: always;">
{{-- IV. CIVIL SERVICE ELIGIBILITY --}}
<table class="section-table border-b-0"
       style="width:100%; border-collapse:collapse; table-layout:fixed;
               font-family:'Arial Narrow','Arial',sans-serif; border-bottom:0;">

    <colgroup>
        <col style="width:50%;">
        <col style="width:10%;">
        <col style="width:15%;">
        <col style="width:20%;">
        <col style="width:12.5%;">
        <col style="width:12.5%;">
    </colgroup>
    <tr>
        <th colspan="6"
            style="background:#8a8a8a; color:#fff; font-style:italic;
                   text-align:left; padding:6px; border:4px solid black;
                   -webkit-print-color-adjust:exact; print-color-adjust:exact;"
                    class="text-3xl">
            IV. CIVIL SERVICE ELIGIBILITY
        </th>
    </tr>

    <tr style="background:#e7e7e7; -webkit-print-color-adjust:exact; print-color-adjust:exact;" class="text-xl">
        <th  rowspan="2" style="width:80%; border:1px solid black;">
            27. CES/CSEE/CAREER SERVICE/RA 1080 (BOARD/BAR)/UNDER SPECIAL LAWS/CATEGORY II/IV ELIGIBILITY and ELIGIBILITIES FOR UNIFORMED PERSONNEL
        </th>
        <th rowspan="2" style="border:1px solid black;">RATING <br> (If Applicable)</th>
        <th rowspan="2" style="border:1px solid black;">DATE OF EXAMINATION / CONFERMENT</th>
        <th rowspan="2" style="border:1px solid black;">PLACE OF EXAMINATION / CONFERMENT</th>
        <th colspan="2" style="border:1px solid black;">LICENSE <br>(if applicable)</th>
    </tr>

    <tr style="background:#e7e7e7; text-align:center; -webkit-print-color-adjust:exact; print-color-adjust:exact;" class="text-xl">
        <th style="border:1px solid black;">NUMBER</th>
        <th style="border:1px solid black;">VALID UNTIL</th>
    </tr>

    @php
        $rows = $eligibilities ?? collect();
        $maxRows = max(25, $rows->count());
    @endphp

    @for ($i = 0; $i < $maxRows; $i++)
        @php
            $row = $rows[$i] ?? null;
            $bottom = $i === $maxRows - 1 ? 'border-bottom:0;' : '';
        @endphp
        <tr class="text-lg align-middle" style="{{ $bottom }}">
            <td style="border:1px solid black; text-align:center; vertical-align:middle; {{ $bottom }}">{{ $row->eligibility ?? ' ' }}</td>
            <td style="border:1px solid black; text-align:center; vertical-align:middle; {{ $bottom }}">{{ $row->rating ?? ' ' }}</td>
            <td style="border:1px solid black; text-align:center; vertical-align:middle; {{ $bottom }}">{{ $row ? (format_pds_date($row->exam_date ?? null) ?: 'NA') : ' ' }}</td>
            <td style="border:1px solid black; text-align:center; vertical-align:middle; {{ $bottom }}">{{ $row->exam_place ?? ' ' }}</td>
            <td style="border:1px solid black; text-align:center; vertical-align:middle; {{ $bottom }}">{{ $row->license_no ?? ' ' }}</td>
            <td style="border:1px solid black; text-align:center; vertical-align:middle; {{ $bottom }}">{{ $row->validity ?? ' ' }}</td>
        </tr>
    @endfor
</table>

{{-- V. WORK EXPERIENCE --}}
<table class="section-table"
       style="width:100%; border-collapse:collapse; border-bottom:0; font-family:'Arial Narrow','Arial',sans-serif;">
    <colgroup>
        <col style="width:8%;">
        <col style="width:8%;">
        <col style="width:20%;">
        <col style="width:28%;">
        <col style="width:20%;">
        <col style="width:10%;">
    </colgroup>

    <tr>
        <th colspan="6" style="background:#8a8a8a; color:#fff; font-style:italic; font-size:18px;
                               text-align:left; padding:6px; border:4px solid black;
                               -webkit-print-color-adjust:exact; print-color-adjust:exact; font-size:23px;">
            V. WORK EXPERIENCE <br>
            <span style="font-weight:100; font-size:20px;">
                (Include private employment. Start from your recent work. Description of duties should be indicated in the attached Work Experience Sheet.)
            </span>
        </th>
    </tr>

    <tr style="background:#e7e7e7; -webkit-print-color-adjust:exact; print-color-adjust:exact;" class="text-xl">
        <th colspan="2" style="border:1px solid black;">INCLUSIVE DATES OF ATTENDANCE <br> <span>(dd/mm/yyyy)</span></th>
        <th rowspan="2" style="border:1px solid black;">POSITION TITLE <br> <span>(Write in full/Do not abbreviate)</span></th>
        <th rowspan="2" style="border:1px solid black;">DEPARTMENT / AGENCY / OFFICE / COMPANY <br> <span>(Write in full/Do not abbreviate)</span></th>
        <th rowspan="2" style="border:1px solid black;">STATUS OF APPOINTMENT</th>
        <th rowspan="2" style="border:1px solid black;">GOV'T SERVICE <br> (Y/N)</th>
    </tr>

    <tr style="background:#e7e7e7; text-align:center; -webkit-print-color-adjust:exact; print-color-adjust:exact;" class="text-xl">
        <th style="border:1px solid black;">FROM</th>
        <th style="border:1px solid black;">TO</th>
    </tr>
@include('pdsreview.partials.date-format-helper')
    @php
        $workRows = ($workExperiences ?? ($work ?? collect()))->values(); // keep user-entered order
        $workRows = $workRows->take(23); // cap to 20 rows max in PDF
        $maxWorkRows = max(23, $workRows->count());
    @endphp
    @for ($i = 0; $i < $maxWorkRows; $i++)
        @php $workRow = $workRows[$i] ?? null; @endphp
        <tr class="text-lg align-middle">
            <td style="border:1px solid black; text-align:center; vertical-align:middle;">{{ $workRow ? (format_pds_date($workRow->from ?? null) ?: ($i === 0 ? 'NA' : ' ')) : ($i === 0 ? 'NA' : ' ') }}</td>
            <td style="border:1px solid black; text-align:center; vertical-align:middle;">{{ $workRow ? (format_pds_date($workRow->to ?? null) ?: ($i === 0 ? 'NA' : ' ')) : ($i === 0 ? 'NA' : ' ') }}</td>
            <td style="border:1px solid black; text-align:center; vertical-align:middle;">{{ $workRow->position_title ?? ' ' }}</td>
            <td style="border:1px solid black; text-align:center; vertical-align:middle;">{{ $workRow->department ?? ' ' }}</td>
            <td style="border:1px solid black; text-align:center; vertical-align:middle;">{{ $workRow->status ?? ' ' }}</td>
            <td style="border:1px solid black; text-align:center; vertical-align:middle;">{{ $workRow->govt_service ?? ' ' }}</td>
        </tr>
    @endfor
</table>

{{-- SIGNATURE & DATE --}}
<table class="section-table"
       style="width:100%; border-collapse:collapse; font-family:'Arial Narrow','sans-serif';">
    <colgroup>
        <col style="width:17%;">
        <col style="width:20%;">
        <col style="width:16.1%;">
        <col style="width:15%;">
    </colgroup>
    <tr>
        <td style="text-align:center; font-weight:bold; font-size:18px; border:1px solid black; border-top:0; vertical-align:middle; font-style:italic;">
            SIGNATURE
        </td>

        <td colspan="2" style="border:1px solid black; border-top:0;">
            <div style="height:100%; width:100%; display:flex; align-items:center; justify-content:center; padding:6px;">
                @if($signatureUrl)
                  <img src="{{ $signatureUrl }}" alt="Signature" style="max-height:150px; object-fit:contain;">
                @endif
            </div>
        </td>

        <td style="border:1px solid black; border-top:0; text-align:center; font-weight:bold; font-size:18px; vertical-align:middle; font-style:italic;">
            DATE
        </td>

         <td colspan="2"
          class="h-16">
          <div class="h-full w-full flex items-center justify-center text-lg text-center" style="font-size: 30px;">
            {{ format_pds_date($declaration->date_accomplished) ?? '—' }}
          </div>
      </td>
    </tr>
</table>  
      <div class="text-base w-full keep-base" style=" margin-top: 10px; text-align:right; font-family:'Arial_Narrow','sans-serif';">
    CS FORM 212 (Revised 2025), Page 2 of 5
</div>
</div>
</div>

<div style="page-break-before: always;"></div>
  <table class="section-table" style="width:100%; border-collapse:collapse; font-family:'Arial Narrow','Arial',sans-serif; page-break-inside: avoid; break-inside: avoid; border-bottom:0;">

  <colgroup>
    <col style="width:35%;">
    <col style="width:12%;">
    <col style="width:12%;">
    <col style="width:12%;">
    <col style="width:29%;">
  </colgroup>

  <tr>
    <th colspan="5"
        style="background:#8a8a8a; color:#fff;
               font-style:italic; font-size:18px;
               text-align:left; padding:6px;
               border:4px solid black;
               -webkit-print-color-adjust:exact;
               print-color-adjust:exact; font-size:23px;">
      VI. VOLUNTARY WORK OR INVOLVEMENT IN CIVIC / NON-GOVERNMENTAL / PEOPLE / VOLUNTARY ORGANIZATION
    </th>
  </tr>

  <tr>
    <th rowspan="2" style="background:#e7e7e7; border:1px solid black;
         -webkit-print-color-adjust:exact; print-color-adjust:exact;" class="text-xl">
      29. NAME & ADDRESS OF ORGANIZATION (Write in full)
    </th>

    <th colspan="2" style="background:#e7e7e7; border:1px solid black;
         -webkit-print-color-adjust:exact; print-color-adjust:exact;" class="text-xl">
      INCLUSIVE DATES <br> <span>(dd/mm/yyyy)</span>
    </th>

    <th rowspan="2" style="background:#e7e7e7; border:1px solid black;
         -webkit-print-color-adjust:exact; print-color-adjust:exact;" class="text-xl">
      NUMBER OF HOURS
    </th>

    <th rowspan="2" style="background:#e7e7e7; border:1px solid black;
         -webkit-print-color-adjust:exact; print-color-adjust:exact;" class="text-xl">
      POSITION / NATURE OF WORK
    </th>
  </tr>

  <tr>
    <th style="background:#e7e7e7; border:1px solid black;
         -webkit-print-color-adjust:exact; print-color-adjust:exact;" class="text-xl">FROM</th>
    <th style="background:#e7e7e7; border:1px solid black;
         -webkit-print-color-adjust:exact; print-color-adjust:exact;" class="text-xl">TO</th>
  </tr>

  @php
    $volRows = ($voluntaryWorks ?? ($voluntary ?? collect()))->values(); // keep user-entered order
    $maxRows = max(15, $volRows->count());
    // Scale down the table if we have more than 15 rows so it fits on a single page
    $volScale = $maxRows > 15 ? round(15 / $maxRows, 4) : 1;
  @endphp

<div style="page-break-inside: avoid;">
  <div style="transform-origin: top left; transform: scale({{ $volScale }}); width: calc(100% / {{ $volScale }});">
    @for ($i = 0; $i < $maxRows; $i++)
      @php
          $row = $volRows[$i] ?? null;
          $bottom = $i === $maxRows - 1 ? 'border-bottom:0;' : '';
      @endphp
      <tr style="{{ $bottom }}">
        <td style="border:1px solid black; text-align:center; {{ $bottom }}">{{ $row->organization ?? ' ' }}</td>
        <td style="border:1px solid black; text-align:center; {{ $bottom }}">{{ format_pds_date($row?->from) ?: ($i === 0 ? 'NA' : ' ') }}</td>
        <td style="border:1px solid black; text-align:center; {{ $bottom }}">{{ format_pds_date($row?->to) ?: ($i === 0 ? 'NA' : ' ') }}</td>
        <td style="border:1px solid black; text-align:center; {{ $bottom }}">{{ $row->hours ?? ($i === 0 ? 'NA' : ' ') }}</td>
        <td style="border:1px solid black; text-align:center; {{ $bottom }}">{{ $row->position ?? ' ' }}</td>
      </tr>
    @endfor
  </div>
</div>

</table>

    

   <table class="section-table" style="width:100%; border-collapse:collapse; font-family:'Arial Narrow','Arial',sans-serif; page-break-inside: avoid; break-inside: avoid; border-bottom:0;">

  <colgroup>
    <col style="width:40%;">
    <col style="width:12%;">
    <col style="width:12%;">
    <col style="width:12%;">
    <col style="width:12%;">
    <col style="width:12%;">
  </colgroup>

  <tr>
    <th colspan="6"
        style="background:#8a8a8a; color:#fff;
               font-style:italic; font-size:18px;
               text-align:left; padding:6px;
               border:4px solid black;
               -webkit-print-color-adjust:exact;
               print-color-adjust:exact;" font-size:23px;>
      VII. LEARNING AND DEVELOPMENT (L&D) INTERVENTIONS/TRAINING PROGRAMS ATTENDED
    </th>
  </tr>

  <tr>
    <th rowspan="2" style="background:#e7e7e7; border:1px solid black;
         -webkit-print-color-adjust:exact; print-color-adjust:exact;" class="text-xl">
      30. TITLE OF LEARNING AND DEVELOPMENT INTERVENTIONS/TRAINING PROGRAMS
    </th>

    <th colspan="2" style="background:#e7e7e7; border:1px solid black;
         -webkit-print-color-adjust:exact; print-color-adjust:exact;" class="text-xl">
      INCLUSIVE DATES OF ATTENDANCE
    </th>

    <th rowspan="2" style="background:#e7e7e7; border:1px solid black;
         -webkit-print-color-adjust:exact; print-color-adjust:exact;" class="text-xl">
      NUMBER OF HOURS
    </th>

    <th rowspan="2" style="background:#e7e7e7; border:1px solid black;
         -webkit-print-color-adjust:exact; print-color-adjust:exact;" class="text-xl">
      Type of L&D
    </th>

    <th rowspan="2" style="background:#e7e7e7; border:1px solid black;
         -webkit-print-color-adjust:exact; print-color-adjust:exact;" class="text-xl">
      CONDUCTED/SPONSORED BY
    </th>
  </tr>

  <tr>
    <th style="background:#e7e7e7; border:1px solid black;
         -webkit-print-color-adjust:exact; print-color-adjust:exact;" class="text-xl">FROM</th>
    <th style="background:#e7e7e7; border:1px solid black;
         -webkit-print-color-adjust:exact; print-color-adjust:exact;" class="text-xl">TO</th>
  </tr>

  @php
    $trainingRows = ($training ?? ($learning ?? collect()))->values(); // keep user-entered order
    $trainingRows = $trainingRows->take(23); // cap to 21 rows max in PDF
    $maxTraining = max(23, $trainingRows->count());
  @endphp

  @for ($i = 0; $i < $maxTraining; $i++)
    @php
        $trow = $trainingRows[$i] ?? null;
        $bottom = $i === $maxTraining - 1 ? 'border-bottom:0;' : '';
    @endphp
    <tr style="{{ $bottom }}">
      <td style="border:1px solid black; text-align:center; {{ $bottom }}">{{ $trow->title ?? ' ' }}</td>
      <td style="border:1px solid black; text-align:center; {{ $bottom }}">{{ format_pds_date($trow?->from) ?: ($i === 0 ? 'NA' : ' ') }}</td>
      <td style="border:1px solid black; text-align:center; {{ $bottom }}">{{ format_pds_date($trow?->to) ?: ($i === 0 ? 'NA' : ' ') }}</td>
      <td style="border:1px solid black; text-align:center; {{ $bottom }}">{{ $trow->hours ?? ($i === 0 ? 'NA' : ' ') }}</td>
      <td style="border:1px solid black; text-align:center; {{ $bottom }}">{{ $trow->type_of_ld ?? ' ' }}</td>
      <td style="border:1px solid black; text-align:center; {{ $bottom }}">{{ $trow->conducted_by ?? ' ' }}</td>
    </tr>
  @endfor

</table>
{{-- VIII. OTHER INFORMATION --}}
<table style="width:100%; border-collapse:collapse; font-family:'Arial Narrow','Arial',sans-serif; border-bottom:0;" border="1">

    <colgroup>
        <col style="width:5.13%;">
        <col style="width:6.5%;">
        <col style="width:4.30%;">
    </colgroup>

    <tr>
        <th colspan="3"
            style="background:#8a8a8a; color:#fff;
                   font-style:italic; font-size:18px;
                   text-align:left; padding:6px;
                   border:4px solid black;
                   -webkit-print-color-adjust:exact;
                   print-color-adjust:exact; font-size:23px;">
            VIII. OTHER INFORMATION
        </th>
    </tr>

    <tr style="background:#e7e7e7; -webkit-print-color-adjust:exact; print-color-adjust:exact;" class="text-xl">
        <th style="border:1px solid black;">SPECIAL SKILLS and HOBBIES</th>
        <th style="border:1px solid black;">NON-ACADEMIC DISTINCTIONS / RECOGNITION <br> <span>(Write in full)</span></th>
        <th style="border:1px solid black;">MEMBERSHIP IN ASSOCIATION / ORGANIZATION <br> <span>(Write in full)</span></th>
    </tr>

    @php
        $otherCollection = $other ?? ($otherInfo ?? collect());
        $skills = $otherCollection->where('category', 'skills')->pluck('description')->values();
        $recognition = $otherCollection->where('category', 'recognition')->pluck('description')->values();
        $assoc = $otherCollection->where('category', 'association')->pluck('description')->values();
        $maxOther = max(8, $skills->count(), $recognition->count(), $assoc->count());
    @endphp

    @for ($i = 0; $i < $maxOther; $i++)
    <tr>
        <td style="border:1px solid black; text-align:center; vertical-align:top;">{{ $skills[$i] ?? ' ' }}</td>
        <td style="border:1px solid black; text-align:center; vertical-align:top;">{{ $recognition[$i] ?? ' ' }}</td>
        <td style="border:1px solid black; text-align:center; vertical-align:top;">{{ $assoc[$i] ?? ' ' }}</td>
    </tr>
    @endfor

</table>

{{-- SIGNATURE & DATE --}}
<table style="width:100%; border-collapse:collapse; font-family:'Arial Narrow','sans-serif'; font-style:italic;" border="1">

    <colgroup>
        <col style="width:32.2%;">
        <col style="width:15.7%;">
        <col style="width:14.72%;">
        <col style="width:10.4%;">
    </colgroup>

    <tr>
        <td style="text-align:center; font-weight:bold; font-size:18px; border:1px solid black; border-top:0;">
            SIGNATURE
        </td>

        <td colspan="2" style="border:1px solid black; border-top:0;">
            <div style="height:100%; width:100%; display:flex; align-items:center; justify-content:center; padding:6px;">
                @if($signatureUrl)
                  <img src="{{ $signatureUrl }}" alt="Signature" style="max-height:150px; object-fit:contain;">
                @endif
            </div>
        </td>

        <td style="border:1px solid black; text-align:center; border-top:0; font-weight:bold; font-size:18px;">
            DATE
        </td>

        <td
          class="h-16">
          <div class="h-full w-full flex items-center justify-center text-center" style="font-size: 30px; border-top:0;">
            {{ format_pds_date($declaration->date_accomplished) ?? '—' }}
          </div>
      </td>
    </tr>
</table>
    <div class="text-base w-full keep-base" style=" margin-top: 10px; text-align:right; font-family:'Arial_Narrow','sans-serif';">
    CS FORM 212 (Revised 2025), Page 3 of 5
</div>
<div style="page-break-before: always;"></div>
  <div class="w-full font-serif text-sm">
  <div class="pds-sheet w-full" style="max-width:100%;">

   <table class="section-table declarations-table" style="width:100%; border-collapse:collapse; font-family:'Arial Narrow','Arial',sans-serif; border-bottom:0; font-size:12px;">
    <!-- ======================= 34 ======================= -->
<tr>
  <td style="border:1px solid black; width:66%; vertical-align:top; padding:10px;">
    34. Are you related by consanguinity or affinity to the appointing or recommending authority, or to the
    chief of bureau or office or to the person who has immediate supervision over you in the Office,
    Bureau or Department where you will be appointed?
    <div style="margin-left:40px; margin-top:45px;">a. within the third degree?</div>
    <div style="margin-left:40px; margin-top:20px;">b. within the fourth degree (for Local Government Unit – Career Employees)?</div>
  </td>

  <td style="border:1px solid black; width:34%; vertical-align:top; padding:10px;">

    <!-- 34A -->
    <div style="display:flex; align-items:center; gap:10px; margin-top:72px;">
      <label style="display:flex; align-items:center; gap:2px; margin:0;">
        <input type="checkbox" class="checkbox-large" style="width:13px; height:13px; margin-bottom:6px;" disabled @checked(($declaration->q34_a ?? '') === 'YES')>
        YES
      </label>
      <label style="display:flex; align-items:center; gap:2px; margin:0;">
        <input type="checkbox" class="checkbox-large" style="width:13px; height:13px; margin-bottom:6px;" disabled @checked(($declaration->q34_a ?? '') === 'NO')>
        NO
      </label>
    </div>

    <!-- 34B -->
    <div style="display:flex; align-items:center; gap:10px; margin-top:8px;">
      <label style="display:flex; align-items:center; gap:2px; margin:0;">
        <input type="checkbox" class="checkbox-large" style="width:13px; height:13px;" disabled @checked(($declaration->q34_b ?? '') === 'YES')>
        YES
      </label>
      <label style="display:flex; align-items:center; gap:2px; margin:0;">
        <input type="checkbox" class="checkbox-large" style="width:13px; height:13px;" disabled @checked(($declaration->q34_b ?? '') === 'NO')>
        NO
      </label>
    </div>

    <div style="margin-top:8px;">if yes, give details:</div>

    <input type="text" style="width:100%; border:none; border-bottom:1px solid black;"
      Disabled value="{{ $declaration->q34_a_details ?? '' }}">

    <input type="text" style="width:100%; border:none; border-bottom:1px solid black; margin-top:5px;"
      Disabled value="{{ $declaration->q34_b_details ?? '' }}">
  </td>
</tr>


<!-- ======================= 35A ======================= -->
<tr>
  <td style="border:1px solid black; vertical-align:top; padding:10px;">
    35. a. Have you ever been found guilty of any administrative offense?
  </td>

  <td style="border:1px solid black; vertical-align:top; padding:10px;">

    <table style="width:auto; border-collapse:collapse;">
  <tr>
    <td style="padding:0;">
      <input type="checkbox"
      class="checkbox-large"
             style="width:13px; height:13px; margin-right:4px;"
             disabled
             @checked(($declaration->q35_a ?? '') === 'YES')>
      YES
    </td>

    <td style="padding:0 0 0 15px;">
      <input type="checkbox"
         class="checkbox-large"
             style="width:13px; height:13px; margin-right:4px;"
             disabled
             @checked(($declaration->q35_a ?? '') === 'NO')>
      NO
    </td>
  </tr>
</table>

    <div style="margin-top:8px;">if yes, give details:</div>

    <input type="text" style="width:100%; border:none; border-bottom:1px solid black;"
      Disabled value="{{ $declaration->q35_a_details ?? '' }}">
  </td>
</tr>


<!-- ======================= 35B ======================= -->
<tr>
  <td style="border:1px solid black; vertical-align:top; padding:10px;">
    <div style="margin-left:30px;">b. Have you been criminally charged before any court?</div>
  </td>

  <td style="border:1px solid black; vertical-align:top; padding:10px;">

    <table style="width:auto; border-collapse:collapse;">
  <tr>
    <td style="padding:0;">
      <input type="checkbox"
      class="checkbox-large"
             style="width:13px; height:13px; margin-right:4px;"
             disabled
             @checked(($declaration->q35_b ?? '') === 'YES')>
      YES
    </td>

    <td style="padding:0 0 0 15px;">
      <input type="checkbox"
         class="checkbox-large"
             style="width:13px; height:13px; margin-right:4px;"
             disabled
             @checked(($declaration->q35_b ?? '') === 'NO')>
      NO
    </td>
  </tr>
</table>

    <div style="margin-top:8px;">if yes, give details:</div>

    <div style="margin-top:5px;">
      Date Filed:
      <input type="text" style="border:none; border-bottom:1px solid black; width:100%;"
        Disabled value="{{ $declaration->q35_b_details_date ?? '' }}">
    </div>

    <div style="margin-top:5px;">
      Status of Case/s:
      <input type="text" style="border:none; border-bottom:1px solid black; width:100%;"
        Disabled value="{{ $declaration->q35_b_details_status ?? '' }}">
    </div>

  </td>
</tr>


<!-- ======================= 36 ======================= -->
<tr>
  <td style="border:1px solid black; vertical-align:top; padding:10px;">
    36. Have you ever been convicted of any crime or violation of any law, decree, ordinance or regulation by any court or tribunal?
  </td>

  <td style="border:1px solid black; vertical-align:top; padding:10px;">

    <table style="width:auto; border-collapse:collapse;">
  <tr>
    <td style="padding:0;">
      <input type="checkbox"
      class="checkbox-large"
             style="width:13px; height:13px; margin-right:4px;"
             disabled
             @checked(($declaration->q36 ?? '') === 'YES')>
      YES
    </td>

    <td style="padding:0 0 0 15px;">
      <input type="checkbox"
         class="checkbox-large"
             style="width:13px; height:13px; margin-right:4px;"
             disabled
             @checked(($declaration->q36 ?? '') === 'NO')>
      NO
    </td>
  </tr>
</table>

    <div style="margin-top:8px;">if yes, give details:</div>

    <input type="text" style="width:100%; border:none; border-bottom:1px solid black;"
      Disabled value="{{ $declaration->q36_details ?? '' }}">
  </td>
</tr>


<!-- ======================= 37 ======================= -->
<tr>
  <td style="border:1px solid black; vertical-align:top; padding:10px;">
    37. Have you ever been separated from the service in any of the following modes: resignation, retirement, dropped from the rolls, dismissal, termination, end of term, finished contract or phased out (abolition) in the public or private sector?
  </td>

  <td style="border:1px solid black; vertical-align:top; padding:10px;">

    <table style="width:auto; border-collapse:collapse;">
  <tr>
    <td style="padding:0;">
      <input type="checkbox"
      class="checkbox-large"
             style="width:13px; height:13px; margin-right:4px;"
             disabled
             @checked(($declaration->q37 ?? '') === 'YES')>
      YES
    </td>

    <td style="padding:0 0 0 15px;">
      <input type="checkbox"
         class="checkbox-large"
             style="width:13px; height:13px; margin-right:4px;"
             disabled
             @checked(($declaration->q37 ?? '') === 'NO')>
      NO
    </td>
  </tr>
</table>

    <div style="margin-top:8px;">if yes, give details:</div>

    <input type="text" style="width:100%; border:none; border-bottom:1px solid black;"
      Disabled value="{{ $declaration->q37_details ?? '' }}">
  </td>
</tr>


<!-- ======================= 38A ======================= -->
<tr>
  <td style="border:1px solid black; vertical-align:top; padding:10px;">
    38. a. Have you ever been a candidate in a national or local election held within the last year (except Barangay election)?
  </td>

  <td style="border:1px solid black; vertical-align:top; padding:10px;">

    <table style="width:auto; border-collapse:collapse;">
  <tr>
    <td style="padding:0;">
      <input type="checkbox"
      class="checkbox-large"
             style="width:13px; height:13px; margin-right:4px;"
             disabled
             @checked(($declaration->q38_a ?? '') === 'YES')>
      YES
    </td>

    <td style="padding:0 0 0 15px;">
      <input type="checkbox"
         class="checkbox-large"
             style="width:13px; height:13px; margin-right:4px;"
             disabled
             @checked(($declaration->q38_a ?? '') === 'NO')>
      NO
    </td>
  </tr>
</table>

    <div style="margin-top:8px;">if yes, give details:</div>

    <input type="text" style="width:100%; border:none; border-bottom:1px solid black;"
      Disabled value="{{ $declaration->q38_a_details ?? '' }}">
  </td>
</tr>


<!-- ======================= 38B ======================= -->
<tr>
  <td style="border:1px solid black; vertical-align:top; padding:10px;">
    <div style="margin-left:30px;">
      b. Have you resigned from the government service during the three (3)-month period before the last election to promote/actively campaign for a national or local candidate?
    </div>
  </td>

  <td style="border:1px solid black; vertical-align:top; padding:10px;">

    <table style="width:auto; border-collapse:collapse;">
  <tr>
    <td style="padding:0;">
      <input type="checkbox"
      class="checkbox-large"
             style="width:13px; height:13px; margin-right:4px;"
             disabled
             @checked(($declaration->q38_b ?? '') === 'YES')>
      YES
    </td>

    <td style="padding:0 0 0 15px;">
      <input type="checkbox"
         class="checkbox-large"
             style="width:13px; height:13px; margin-right:4px;"
             disabled
             @checked(($declaration->q38_b ?? '') === 'NO')>
      NO
    </td>
  </tr>
</table>

    <div style="margin-top:8px;">if yes, give details:</div>

    <input type="text" style="width:100%; border:none; border-bottom:1px solid black;"
      Disabled value="{{ $declaration->q38_b_details ?? '' }}">
  </td>
</tr>


<!-- ======================= 39 ======================= -->
<tr>
  <td style="border:1px solid black; vertical-align:top; padding:10px;">
    39. Have you acquired the status of an immigrant or permanent resident of another country?
  </td>

  <td style="border:1px solid black; vertical-align:top; padding:10px;">

    <table style="width:auto; border-collapse:collapse;">
  <tr>
    <td style="padding:0;">
      <input type="checkbox"
      class="checkbox-large"
             style="width:13px; height:13px; margin-right:4px;"
             disabled
             @checked(($declaration->q39 ?? '') === 'YES')>
      YES
    </td>

    <td style="padding:0 0 0 15px;">
      <input type="checkbox"
         class="checkbox-large"
             style="width:13px; height:13px; margin-right:4px;"
             disabled
             @checked(($declaration->q39 ?? '') === 'NO')>
      NO
    </td>
  </tr>
</table>

    <div style="margin-top:8px;">if yes, give details:</div>

    <input type="text" style="width:100%; border:none; border-bottom:1px solid black;"
      Disabled value="{{ $declaration->q39_details ?? '' }}">
  </td>
</tr>


<!-- ======================= 40 ======================= -->
<tr>
  <td style="border:1px solid black; vertical-align:top; padding:10px;">
    40. Pursuant to RA 8371, RA 7277 (as amended), and RA 11861:

    <div style="margin-left:30px; margin-top:15px;">a. Are you a member of any indigenous group?</div>
    <div style="margin-left:30px; margin-top:40px;">b. Are you a person with disability?</div>
    <div style="margin-left:30px; margin-top:40px;">c. Are you a solo parent?</div>
  </td>

  <td style="border:1px solid black; vertical-align:top; padding:10px;">

    <!-- 40A -->
    <table style="width:auto; border-collapse:collapse; margin-top:35px;">
  <tr>
    <td style="padding:0;">
      <input type="checkbox"
      class="checkbox-large"
             style="width:13px; height:13px; margin-right:4px;"
             disabled
             @checked(($declaration->q40_a ?? '') === 'YES')>
      YES
    </td>

    <td style="padding:0 0 0 15px;">
      <input type="checkbox"
         class="checkbox-large"
             style="width:13px; height:13px; margin-right:4px;"
             disabled
             @checked(($declaration->q40_a ?? '') === 'NO')>
      NO
    </td>
  </tr>
</table>
    <input type="text" style="width:100%; border:none; border-bottom:1px solid black; margin-bottom:10px;"
      Disabled value="{{ $declaration->q40_a_details ?? '' }}">

    <!-- 40B -->
    <table style="width:auto; border-collapse:collapse;">
  <tr>
    <td style="padding:0;">
      <input type="checkbox"
      class="checkbox-large"
             style="width:13px; height:13px; margin-right:4px;"
             disabled
             @checked(($declaration->q40_b ?? '') === 'YES')>
      YES
    </td>

    <td style="padding:0 0 0 15px;">
      <input type="checkbox"
         class="checkbox-large"
             style="width:13px; height:13px; margin-right:4px;"
             disabled
             @checked(($declaration->q40_b ?? '') === 'NO')>
      NO
    </td>
  </tr>
</table>
    <input type="text" style="width:100%; border:none; border-bottom:1px solid black; margin-bottom:10px;"
      Disabled value="{{ $declaration->q40_b_details ?? '' }}">

    <!-- 40C -->
    <table style="width:auto; border-collapse:collapse;">
  <tr>
    <td style="padding:0;">
      <input type="checkbox"
      class="checkbox-large"
             style="width:13px; height:13px; margin-right:4px;"
             disabled
             @checked(($declaration->q40_c ?? '') === 'YES')>
      YES
    </td>

    <td style="padding:0 0 0 15px;">
      <input type="checkbox"
         class="checkbox-large"
             style="width:13px; height:13px; margin-right:4px;"
             disabled
             @checked(($declaration->q40_c ?? '') === 'NO')>
      NO
    </td>
  </tr>
</table>
    <input type="text" style="width:100%; border:none; border-bottom:1px solid black;"
      Disabled value="{{ $declaration->q40_c_details ?? '' }}">

  </td>
</tr>
    </table> 

    <table class="section-table w-full h-full font-['Arial_Narrow','Arial',sans-serif]" style="border-collapse:collapse;">
      <tr>
        <td colspan="3" style="border-right:0; border:2px solid black;">
          <span class="ml-2">41. REFERENCES </span><span class="font-semibold">(Person not related by consanguinity or affinity to applicant / appointee)</span>
        </td>
        <td rowspan="11"
    style="
        width:25%;
        vertical-align:top;
        text-align:center;
        border:2px solid black;
        border-left:0;
    ">

    <div style="margin-top:20mm;">

        <!-- PASSPORT PHOTO -->
        <div style="margin-bottom:3mm;">

            <div style="
                width:35mm;
                height:45mm;
                border:2px solid black;
                margin:0 auto;
                position:relative;
                overflow:hidden;
                font-size:10px;
                font-style:italic;
                text-align:center;
                display:flex;
                align-items:center;
                justify-content:center;
            ">

                @if($photoUrl)
                  <img src="{{ $photoUrl }}"
                       style="position:absolute; top:0; left:0; width:100%; height:100%; object-fit:cover;"
                       alt="Photo">
                @else
            
                @endif

            </div>

            <div style="font-size:10px; margin-top:2mm;">
                PHOTO
            </div>

        </div>


        <!-- THUMB MARK -->
        <div style="margin-top:30mm;">

            <div style="
                width:45mm;
                height:45mm;
                border:2px solid black;
                margin:0 auto;
                position:relative;
                overflow:hidden;
            ">

                <img id="thumbPreview"
                     style="
                        position:absolute;
                        top:0;
                        left:0;
                        width:100%;
                        height:100%;
                        object-fit:cover;
                        display:none;
                     ">

                <div style="
                    position:absolute;
                    bottom:0;
                    left:0;
                    width:100%;
                    border-top:1px solid black;
                    text-align:center;
                    font-size:10px;
                    font-style:italic;
                    padding:2mm 0;
                    background:white;
                ">
                    Right Thumbmark
                </div>
            </div>
        </div>
    </div>
</td>
      </tr>
      <tr class="border border-black">
        <th class="border font-light w-24 border-black">NAME</th>
        <th class="border font-light border-black">OFFICE / RESIDENTIAL ADDRESS </th>
        <th class="border font-light w-52 border-black">CONTACT NO. AND / OR EMAIL</th>
      </tr>
      @php
        // Reindex to zero-based keys so array-style access works for all saved references
        $refRows = ($references ?? collect())->values();
        $maxRef = max(7, $refRows->count());
      @endphp
      @for ($i = 0; $i < $maxRef; $i++)
      @php $ref = $refRows[$i] ?? null; @endphp
      <tr class="align-top" style="border:2px solid black;">
        <td class="border border-black align-top p-0 w-60 text-center" style="height:25px;">
          {{ $ref->name ?? ($i === 0 ? 'N/A' : '') }}
        </td>
        <td class="border border-black align-top p-0 text-center" style="height:25px;">
          {{ $ref->address ?? ($i === 0 ? 'N/A' : '') }}
        </td>
        <td class="border border-black align-top p-0 text-center" style="height:25px;">
          {{ $ref->contact ?? ($i === 0 ? 'N/A' : '') }}
        </td>
      </tr>
      @endfor
      <tr class="border justify-center">
        <td colspan="3" class="text-justify px-2 h-20 font-semibold border-2 text-base">
          42. I declare under oath that I have personally accomplished this Personal Data Sheet which is a true, correct, and complete statement pursuant to the provisions of pertinent laws, rules, and regulations of the Republic of the Philippines. I authorize the agency head/authorized representative to verify/validate the contents stated herein. I  agree that any misrepresentation made in this document and its attachments shall cause the filing of administrative/criminal case/s against me.
        </td>
      </tr>
      <tr>
        <td class="pr-5 p-0 align-top  border-l border-black border-r-0 flex-1">
          <table class="border-collapse text-xs border-2 ml-2 h-[5.71cm]" style="width:13cm;">
    <!-- HEADER -->
    <tr style="height:2.5cm;">
        <td colspan="2"
            style="border:1px solid black; padding:6px; font-weight:bold;" class="text-base">
            Government Issued ID (i.e. Passport, GSIS, SSS, PRC, Driver's License, etc.)<br>
            <span style="font-style:italic; font-weight:normal;">
                PLEASE INDICATE ID Number and Date of Issuance
            </span>
        </td>
    </tr>

    <!-- ROW 1 -->
    <tr style="height:1.4cm;" class="text-base">
        <td style="border:1px solid black; padding:6px; vertical-align:middle; width:40%;">
            Government Issued ID:
        </td>
        <td style="border:1px solid black; padding:6px;" class="text-base">
            {{ $idInfo->gov_id ?? '' }}
        </td>
    </tr>

    <!-- ROW 2 -->
    <tr style="height:1.4cm;" class="text-base">
        <td style="border:1px solid black; padding:6px; vertical-align:middle;" class="text-base"> 
            ID/License/Passport No.:
        </td>
        <td style="border:1px solid black; padding:6px;" class="text-base">
            {{ $idInfo->passport_licence_id ?? '' }}
        </td>
    </tr>

    <!-- ROW 3 -->
    <tr style="height:1.4cm;" class="text-base">
        <td style="border:1px solid black; padding:6px; vertical-align:middle;" class="text-base">
            Date/Place of Issuance:
        </td>
        <td style="border:1px solid black; padding:6px;" class="text-base">
            {{ $idInfo->date_place_issuance ?? '' }}
        </td>
    </tr>

</table>

        </td>
        <td class="p-0 align-top border-b-0 border-l-0 border-r-0 border-black" colspan="2" style="width:20%;">
          <table class="border-collapse text-xs border-3 mt-2 border-2 mb-2 mx-auto" style="margin-left: 10px; width:12.1cm; margin-left: 195px;">
          <td class="border-black text-center align-middle italic text-red-600">
    <div style="height:3.4cm; display:flex; align-items:center; justify-content:center; position:relative; overflow:hidden;" class="text-base">
        @if($signatureUrl)
          <img src="{{ $signatureUrl }}" alt="Signature" style="max-height:5cm; object-fit:contain;">
        @else
        @endif
    </div>
</td>
            <tr>
              <td class="border border-black text-center py-1 text-base">Signature (Sign inside the box)</td>
            </tr>
           <tr>
  <td>
    <div class="relative flex justify-center py-2  text-base">
      <div class="flex items-center space-x-1 relative">
        <span class="text-base text-center" style="font-size: 30px;">{{ format_pds_date($declaration->date_accomplished) ?? '—' }}</span>
      </div>
    </div>
  </td>
</tr>
            <tr>
              <td class="border border-black  text-center py-1  text-base">Date Accomplished</td>
            </tr>
          </table>
        </td>
      </tr>
    </table>
    <table class="section-table  border-t-0 border-black w-full font-['Arial_Narrow','Arial',sans-serif]">
      <tr>
        <td class="p-2 text-center align-middle font-semibold text-sm">
          SUBSCRIBED AND SWORN to before me this <span style="border-bottom:1px solid black; min-width:150px; display:inline-block;">{{ format_pds_date($declaration->date_accomplished) ?? '' }}</span>, affiant exhibiting his/her validly issued government ID as indicated above.
        </td>
      </tr>
      <tr>
        <td class="p-2 align-top text-center">
          <table class="w-1/3 mx-auto h-full border-collapse text-xs border-2">
            <tr>
  <td class="border-black text-center align-middle italic text-red-600 relative" style="height:9.375rem;">
    @if($signatureUrl)
      <img src="{{ $signatureUrl }}" alt="Signature" class="absolute inset-0 w-full h-full" style="object-fit:contain; max-height:9.375rem;">
    @endif
  </td>
</tr>
            <tr><td class="border-black border text-center py-2 font-semibold text-base">Person Administering Oath</td></tr>
          </table>
        </td>
      </tr>
    </table>
     <div class="w-full text-base keep-base" style=" margin-top: 10px; text-align:right; font-family:'Arial_Narrow','sans-serif';">
    CS FORM 212 (Revised 2025), Page 4 of 5
</div>
  </div>
<div style="page-break-before: always;"></div>
<div class="page-wrap" style="width:100%;">
<table style="width:100%; border-collapse:collapse;">
  <th class="flex text-left font-['Arial_Narrow','Arial',sans-serif] italic font-semibold">
    Attachment to CS Form No. 212
  </th>
</table>

<style>
  /* Allow long remarks to naturally flow across PDF pages */
  .remarks-table { page-break-inside: auto; page-break-after: auto; }
  /* Override pdf-mode !important for the declarations section */
  body.pdf-mode table.declarations-table,
  body.pdf-mode table.declarations-table td,
  body.pdf-mode table.declarations-table * { font-size: 12px !important; }
  .remarks-table tr, .remarks-table td { page-break-inside: auto; }
  .remarks-content { page-break-inside: auto; page-break-after: auto; }
  body { margin: 0; padding: 0; }
  .section-table { width: 100% !important; }
  .page-wrap { width: 100%; margin: 0 auto; box-sizing: border-box; }
</style>

<table class="section-table remarks-table w-full font-['Arial_Narrow','Arial',sans-serif]" style="border-collapse:collapse;">

<tr>
  <th class="text-base font-semibold italic bg-[#8a8a8a] text-white border border-black border-b-2" style="font-weight:700;">
    WORK EXPERIENCE SHEET
  </th>
</tr>

<tbody id="remarks-rows">

<tr>
<td class="border-t-2 h-20 p-3 text-base italic border-b-2 border-black">
  <div class="p-1 font-semibold">Instructions:</div>
  <div style="margin-left:18px;">1. Include only the work experiences relevant to the position being applied to.</div>
  <div style="margin-left:18px; margin-top:6px;">2. The duration should include start and finish dates, if known, month in abbreviated form, if known, and year in full. For the current position, use the word Present, e.g., 1998-Present. Work experience should be listed from most recent first.</div>
</td>
</tr>

@php
  $remarkRows = ($remarks ?? collect())->values();
  $maxRemark = max(1, $remarkRows->count());
@endphp

@for ($i = 0; $i < $maxRemark; $i++)
@php 
  $row = $remarkRows[$i] ?? null;
  $duration = $row->duration ?? '';
  $position = $row->position_title ?? '';
  $office = $row->office_unit ?? '';
  $supervisor = $row->immediate_supervisor ?? '';
  $agency = $row->agency_location ?? '';
  $accomplishments = $row->accomplishments ?? [];
  if (is_string($accomplishments)) {
    $accomplishments = json_decode($accomplishments, true) ?? [];
  }
  $duties = $row->duties ?? '';

  $lineItems = collect([
      $duration,
      $position,
      $office,
      $supervisor,
      $agency,
    ])
    ->map(function ($value) {
        return isset($value) ? trim($value) : '';
    })
    ->filter(function ($value) {
        return $value !== '';
    })
    ->values();

  if (!empty($accomplishments)) {
      foreach ($accomplishments as $acc) {
          $acc = isset($acc) ? trim($acc) : '';
          if ($acc !== '') {
              $lineItems->push($acc);
          }
      }
  }

  if (isset($duties)) {
      $dutiesTrimmed = trim($duties);
      if ($dutiesTrimmed !== '') {
          $lineItems->push($dutiesTrimmed);
      }
  }

  $remarkHtml = $lineItems
      ->map(function ($line) {
          return e($line);
      })
      ->implode('<br>');

  $hasData = $remarkHtml !== '';
@endphp
<tr>
<td class="border-2 border-black relative align-top">
  <div
    class="remarks-content border-none w-full p-5 text-xl"
    style="white-space:pre-line; box-sizing:border-box; page-break-inside:auto; overflow:visible; font-size:20px; line-height:1.3;"
  >@if($hasData){!! $remarkHtml !!}@endif</div>
</td>
</tr>
@endfor

</tbody>
</table>

<!-- SIGNATURE / DATE (aligned right like pdsreview5) -->
<div class="w-full flex justify-end" style="margin-top:100px; padding-right:8px;">
  <div class="text-center" style="width:460px; margin-left:auto; display:flex; flex-direction:column; align-items:center; gap:8px;">
    @if($signatureUrl)
      <img
        src="{{ $signatureUrl }}"
        alt="Signature"
        style="max-height:150px; object-fit:contain; margin-top:0; margin-bottom:-30px;"
      >
    @endif
    <span>{{ trim(($personal->firstname ?? '') . ' ' . ($personal->middlename ?? '') . ' ' . ($personal->surname ?? '')) ?: 'Employee' }}</span>
    <div class="border-b-2 border-black" style="height:20px; width:100%;"></div>
    <div class="mt-2 text-sm">(Signature over Printed Name)</div>
  </div>
</div> 

<div class="w-full flex justify-end" style="margin-top:50px; padding-right:8px; font-family:'Arial Narrow','Arial',sans-serif;">
  <div class="text-center relative" style="width:460px; margin-left:auto;">
    <div class="h-full w-full flex items-center justify-center text-lg text-center" style="font-size: 30px;">
            {{ format_pds_date($declaration->date_accomplished) ?? '—' }}
          </div>
    <div class="border-b-2 border-black w-full absolute mt-10" style="bottom:26px; left:0; width:100%;"></div>
    <div class="text-sm" style="margin-top:8px; margin-bottom:40px">DATE</div>
  </div>
</div>


<div class="w-full text-base keep-base" style=" margin-top: 10px; text-align:right; font-family:'Arial_Narrow','sans-serif';">
    CS FORM 212 (Revised 2025), Page 5 of 5
</div>
</div>
@if(!empty($pdfMode))
</body>
</html>
@endif