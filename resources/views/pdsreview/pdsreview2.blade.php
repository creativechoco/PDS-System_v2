@if(!empty($pdfMode))
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"></head>
<body>
@endif
@if(empty($pdfMode))
<x-app-layout>
@endif
<form method="POST" action="{{ route('pds.saveStep', 2) }}" enctype="multipart/form-data">
    <div class="max-w-6xl mx-auto p-4 flex justify-end gap-3">
      <a href="{{ route('pdsreview1.pdf') }}" class="px-4 py-2 bg-emerald-600 text-white rounded shadow border border-emerald-700 hover:bg-emerald-700">Preview PDF</a>
      <a href="{{ route('pds.pdf.download') }}" class="px-4 py-2 bg-slate-700 text-white rounded shadow border border-slate-800 hover:bg-slate-800">Download PDF</a>
    </div>
@csrf
    <style>
        body { margin: 0px; }
        table { border-collapse: collapse; width: 100%; table-layout: fixed; }
        td, th { padding: 4px; vertical-align: middle; white-space: normal; word-break: break-word; overflow-wrap: anywhere; }

        .border { border: 1px solid #000 !important; }
        .border-2 { border: 2px solid #000 !important; }
        @media print {
            * { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        }
        textarea { border: none; outline: none; padding: 8px; width: 100%; font: inherit; resize: none; background: transparent; line-height: 1.3; display: block; box-sizing: border-box; overflow: hidden; white-space: pre-wrap; word-break: break-word; min-height: 38px; height: auto; }

        textarea:focus { outline: none; box-shadow: none; }
        input[type="checkbox"] { width: 12px; height: 12px; }
        td { height: 38px; min-height: 38px; vertical-align: middle; }
    </style>  
    <div class="max-w-6xl mx-auto p-4 font-serif text-sm">
  @include('pdsreview.partials.date-format-helper')

    <table class="border border-black w-full font-['Arial_Narrow','sans-serif']">

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

  @php
    $rows = $eligibilities ?? collect();
    $maxRows = max(7, $rows->count()); // 7 display rows
@endphp

@for ($i = 0; $i < $maxRows; $i++)
  @php $row = $rows[$i] ?? null; @endphp
  <tr>
    <td class="border align-middle text-center">{{ $row->eligibility ?? ' ' }}</td>
    <td class="border align-middle text-center">{{ $row->rating ?? ' ' }}</td>
    <td class="border align-middle text-center">{{ $row ? (format_pds_date($row->exam_date ?? null) ?: 'NA') : ' ' }}</td>
    <td class="border align-middle text-center">{{ $row->exam_place ?? ' ' }}</td>
    <td class="border align-middle text-center">{{ $row->license_no ?? ' ' }}</td>
    <td class="border align-middle text-center">{{ $row->validity ?? ' ' }}</td>
  </tr>
@endfor
    </table>
    

    <table class="border border-black font-['Arial_Narrow','sans-serif'] w-full">

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

   @php
    $workRows = ($workExperiences ?? collect())->values(); // keep user-entered order
    $maxRows = max(27, $workRows->count());
@endphp

@for ($i = 0; $i < $maxRows; $i++)
  @php $workRow = $workRows[$i] ?? null; @endphp
  <tr>
    <td class="border align-middle text-center">{{ $workRow ? (format_pds_date($workRow->from ?? null) ?: ($i === 0 ? 'NA' : ' ')) : ($i === 0 ? 'NA' : ' ') }}</td>
    <td class="border align-middle text-center">{{ $workRow ? (format_pds_date($workRow->to ?? null) ?: ($i === 0 ? 'NA' : ' ')) : ($i === 0 ? 'NA' : ' ') }}</td>
    <td class="border align-middle text-center">{{ $workRow->position_title ?? " " }}</td>
    <td class="border align-middle text-center">{{ $workRow->department ?? " " }}</td>
    <td class="border align-middle text-center">{{ $workRow->status ?? " " }}</td>
    <td class="border align-middle text-center">{{ $workRow->govt_service ?? " " }}</td>
  </tr>
@endfor
    </table>

    <table class="border-black w-full h-15 font-['Arial_Narrow','sans-serif']">
      <colgroup>
        <col style="width: 17%;">
        <col style="width: 20%;">
        <col style="width: 16.02%;">
        <col style="width: 15%;">
      </colgroup>
      <tr>
        <td class="text-center font-bold text-xl border align-middle italic">
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

        <td class="border text-center font-bold text-xl align-middle italic">
          DATE
        </td>


       <td class="border h-10">
  <div class="h-full w-full flex items-center justify-center">
    <div class="text-3xl text-center">
      {{ format_pds_date($declaration->date_accomplished ?? null) ?? '—' }}
    </div>
  </div>
</td>
      </tr>
    </table>

     
      <div class="flex justify-end mr-2 border-b-0 font-['Arial_Narrow','sans-serif']">
    CS FORM 212 (Revised 2025), Page 2 of 5
    </div>


    @if(empty($pdfMode))
    <div class="flex justify-between mt-4">
        <a href="{{ route('pdsreview.pdsreview1') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded shadow border border-gray-300 hover:bg-gray-300">Previous Page</a>
        <a href="{{ route('pdsreview.pdsreview3') }}" id="next-btn" class="px-4 py-2 bg-blue-600 text-white rounded shadow border border-blue-700 hover:bg-blue-700">Next Page</a>
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