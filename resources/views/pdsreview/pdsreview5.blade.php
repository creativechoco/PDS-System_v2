@if(!empty($pdfMode))
<!DOCTYPE html>
<html lang="en">
<head><meta charset="UTF-8"></head>
<body>
@endif
@if(empty($pdfMode))
<x-app-layout>
@endif
<form method="POST" action="{{ route('pds.submit') }}" class="w-full" enctype="multipart/form-data">
    <div class="max-w-6xl mx-auto p-4 flex justify-end gap-3">
      <a href="{{ route('pdsreview1.pdf') }}" class="px-4 py-2 bg-emerald-600 text-white rounded shadow border border-emerald-700 hover:bg-emerald-700">Preview PDF</a>
      <a href="{{ route('pds.pdf.download') }}" class="px-4 py-2 bg-slate-700 text-white rounded shadow border border-slate-800 hover:bg-slate-800">Download PDF</a>
    </div>
@csrf


<style>
textarea:focus { outline: none; box-shadow: none; }
[contenteditable]:focus { outline: none; margin: 0; padding: 0; }

table { border-collapse: collapse; width: 100%; table-layout: fixed; }
td, th { padding: 4px; vertical-align: middle; white-space: normal; word-break: break-word; overflow-wrap: anywhere; }

@media print {
  * {
    -webkit-print-color-adjust: exact !important;
    print-color-adjust: exact !important;
  }
}
</style>

<script>
// Auto-size textareas so content expands the container (no scrollbars)
function autoSize(el) {
  if (!el) return;
  el.style.height = 'auto';
  el.style.height = `${el.scrollHeight}px`;
}

window.addEventListener('load', () => {
  document.querySelectorAll('textarea').forEach(el => {
    autoSize(el);
    el.addEventListener('input', () => autoSize(el));
  });
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

<table class="border-black w-full font-['Arial_Narrow','Arial',sans-serif] border-2">

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

@php
  $workExperiences = ($workExperiences ?? collect())->values();
  $maxWorkExperience = max(1, $workExperiences->count());
@endphp

@for ($i = 0; $i < $maxWorkExperience; $i++)
@php 
  $work = $workExperiences[$i] ?? null; 
  $accomplishmentsList = [];
  if ($work) {
      if (is_array($work->accomplishments)) {
          $accomplishmentsList = $work->accomplishments;
      } elseif (is_string($work->accomplishments) && $work->accomplishments !== '') {
          $decoded = json_decode($work->accomplishments, true);
          $accomplishmentsList = is_array($decoded) ? $decoded : [];
      }
  }
@endphp
<tr>
<td class="border-2 border-black relative align-top">
<div class="p-3 text-sm space-y-2">
    <div class="whitespace-pre-wrap">{{ $work->duration ?? '' }}</div>
    <div class="whitespace-pre-wrap">{{ $work->position_title ?? '' }}</div>
    <div class="whitespace-pre-wrap">{{ $work->office_unit ?? '' }}</div>
    <div class="whitespace-pre-wrap">{{ $work->immediate_supervisor ?? '' }}</div>
    <div class="whitespace-pre-wrap">{{ $work->agency_location ?? '' }}</div>

    @if(!empty($accomplishmentsList))
        <div class="space-y-1">
            @foreach($accomplishmentsList as $accomplishment)
                @if(!empty(trim($accomplishment)))
                    <div class="whitespace-pre-wrap">{{ $accomplishment }}</div>
                @endif
            @endforeach
        </div>
    @endif

    <div class="whitespace-pre-wrap">{{ $work->duties ?? '' }}</div>
</div>
</td>
</tr>
@endfor

</tbody>
</table>

<!-- SIGNATURE -->
<div class="w-full flex justify-end mt-[3cm] pr-6">
<div class="w-[350px] text-center flex flex-col items-center">
  @php
    $signatureCleanPath = !empty($signaturePath) ? preg_replace('/^public\//', '', $signaturePath) : null;
    $signatureUrl = !empty($signatureCleanPath) ? asset('storage/'.$signatureCleanPath) : null;
  @endphp
  @if($signatureUrl)
    <img src="{{ $signatureUrl }}" alt="Signature" class="max-h-48 object-contain" style="mix-blend-mode: multiply; filter: contrast(1.2) brightness(1.1);" onerror="this.alt='';this.style.display='none';">
  @else
    <div class="text-xs text-gray-600">No signature on file</div>
  @endif
  <div class="border-b-2 border-black w-full"></div>
  <div class="text-sm">(Signature over Printed Name)</div>
</div>
</div>

<!-- DATE -->
<div class="w-full flex justify-end mt-[1cm] pr-6 font-['Arial_Narrow','Arial',sans-serif]">
<div class="w-[350px] text-center relative">

<div class="border-b-2 border-black w-full absolute bottom-6 left-0"></div>
@include('pdsreview.partials.date-format-helper')
<div class="flex justify-center space-x-1 relative">
<div class="text-3xl text-center">{{ format_pds_date($declaration->date_accomplished) ?? '—' }}</div>
</div>

<div class="text-sm">DATE</div>
</div>
</div>

<div class="mt-5 flex justify-end mr-2 text-sm font-['Arial_Narrow','Arial',sans-serif]">
CS FORM 212 (Revised 2025), Page 5 of 5
</div>

@if(empty($pdfMode))
 <a href="{{ route('pdsreview.pdsreview4') }}" class="px-4 py-2 bg-gray-200 text-gray-800 rounded shadow border border-gray-300 hover:bg-gray-300">Previous Page</a>
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
