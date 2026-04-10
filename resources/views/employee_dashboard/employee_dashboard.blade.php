<x-app-layout>
    <div class="py-10">
        <div class="mx-auto sm:px-6 lg:px-20 space-y-10">

            <!-- Personal Data Sheet quick access card -->
            <section class="w-full">
                @php
                    $pdsSubmitted = $stats['pds']['has_submission'] ?? false;
                    $pdsStatus = $stats['pds']['latest_status'] ?? null;
                    $canEditPds = !$pdsSubmitted || $pdsStatus === 'rejected';
                @endphp

                <div class="rounded-2xl bg-white border border-slate-200 shadow-sm p-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="space-y-1">
                        <p class="text-sm uppercase tracking-wide text-emerald-600 font-semibold">Personal Data Sheet</p>
                        <h2 class="text-xl font-bold text-slate-900">
                            {{ $canEditPds ? 'View or edit your PDS' : 'View your PDS' }}
                        </h2>
                        @if($canEditPds)
                            <p class="text-sm text-slate-600">Open your PDS to review details or continue editing any section.</p>
                        @endif
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3 w-full sm:w-auto">
                        <a href="{{ $canEditPds ? route('pds.form1') : route('pds.view') }}" class="inline-flex justify-center items-center gap-2 rounded-xl bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500">
                            {{ $canEditPds ? 'Edit/Resume PDS' : 'View PDS' }}
                        </a>
                    </div>
                </div>
            </section>

            <!-- Greeting + actions -->
            <div class="bg-gradient-to-r from-sky-500 to-blue-600 text-white rounded-2xl shadow-lg p-8 flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div class="space-y-2">
                    <p class="text-sm uppercase tracking-wide text-white/80">Employee workspace</p>
                    <h1 class="text-2xl lg:text-3xl font-semibold">Welcome {{ auth()->user()->name ?? 'Employee' }}</h1>
                    <p class="text-white/80 text-sm lg:text-base">Manage your Personal Data Sheet, track review status, and upload supporting documents.</p>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-12 gap-8 mt-8 relative">

    <!-- Background decoration -->
    <div class="absolute inset-0 overflow-hidden pointer-events-none">
        <div class="absolute -top-20 -left-20 w-80 h-80 bg-gradient-to-br from-blue-100/20 to-purple-100/20 rounded-full blur-3xl"></div>
        <div class="absolute -bottom-20 -right-20 w-96 h-96 bg-gradient-to-tl from-emerald-100/20 to-cyan-100/20 rounded-full blur-3xl"></div>
    </div>

    <div class="md:col-span-3 space-y-6 relative z-10">
        <div class="bg-white/80 backdrop-blur-sm p-6 rounded-2xl border border-gray-100/50 shadow-xl shadow-gray-200/50 hover:shadow-2xl hover:shadow-gray-300/60 transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center mb-4">
                <div class="bg-gradient-to-br from-blue-500 to-indigo-600 p-3 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="font-bold text-gray-800 ml-3 text-lg">Instructions</h3>
            </div>
            <ul class="text-sm text-gray-600 space-y-4">
                <li class="flex items-start">
                    <span class="bg-blue-100 text-blue-600 rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold mr-3 mt-0.5 flex-shrink-0">1</span>
                    <span>Ensure all information is updated before downloading.</span>
                </li>
                <li class="flex items-start">
                    <span class="bg-blue-100 text-blue-600 rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold mr-3 mt-0.5 flex-shrink-0">2</span>
                    <span>Soft copies are generated in **PDF format**.</span>
                </li>
                <li class="flex items-start">
                    <span class="bg-blue-100 text-blue-600 rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold mr-3 mt-0.5 flex-shrink-0">3</span>
                    <span>For hard copies, use **Legal Size (8.5 x 13)** paper as per CSC rules.</span>
                </li>
            </ul>
        </div>
    </div>

    <div class="md:col-span-6 relative z-10">
        <div class="bg-gradient-to-br from-white via-white to-blue-50/30 rounded-3xl shadow-2xl shadow-gray-300/40 border border-blue-100/50 p-10 flex flex-col items-center text-center relative overflow-hidden">
            <!-- Background pattern -->
            <div class="absolute inset-0 opacity-5">
                <div class="absolute inset-0" style="background-image: url('data:image/svg+xml,%3Csvg width="60" height="60" viewBox="0 0 60 60" xmlns="http://www.w3.org/2000/svg"%3E%3Cg fill="none" fill-rule="evenodd"%3E%3Cg fill="%239C92AC" fill-opacity="0.4"%3E%3Cpath d="M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z"/%3E%3C/g%3E%3C/g%3E%3C/svg%3E');"></div>
            </div>
            
            <div class="bg-gradient-to-br from-blue-500 to-indigo-600 p-8 rounded-2xl mb-6 shadow-xl relative">
                <div class="absolute inset-0 bg-white/20 rounded-2xl animate-pulse"></div>
                <svg class="w-[60px] h-[60px] text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
  <path fill-rule="evenodd" d="M9 2.221V7H4.221a2 2 0 0 1 .365-.5L8.5 2.586A2 2 0 0 1 9 2.22ZM11 2v5a2 2 0 0 1-2 2H4v11a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V4a2 2 0 0 0-2-2h-7ZM8 16a1 1 0 0 1 1-1h6a1 1 0 1 1 0 2H9a1 1 0 0 1-1-1Zm1-5a1 1 0 1 0 0 2h6a1 1 0 1 0 0-2H9Z" clip-rule="evenodd"/>
</svg>

            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-3">Ready to Download?</h2>
            <p class="text-gray-600 mb-8 text-base leading-relaxed max-w-md">Your Personal Data Sheet is up to date and ready for printing or digital submission.</p>
            
            <div class="flex flex-col sm:flex-row gap-4 w-full justify-center">
                <button class="bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-700 hover:to-indigo-700 text-white font-semibold py-4 px-8 rounded-xl transition-all duration-300 flex items-center justify-center shadow-lg hover:shadow-xl hover:-translate-y-0.5 group">
                    <svg class="w-[30px] h-[30px] text-white" aria-hidden="true" xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="currentColor" viewBox="0 0 24 24">
                        <path fill-rule="evenodd" d="M9 2.221V7H4.221a2 2 0 0 1 .365-.5L8.5 2.586A2 2 0 0 1 9 2.22ZM11 2v5a2 2 0 0 1-2 2H4a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2 2 2 0 0 0 2 2h12a2 2 0 0 0 2-2 2 2 0 0 0 2-2v-7a2 2 0 0 0-2-2V4a2 2 0 0 0-2-2h-7Zm-6 9a1 1 0 0 0-1 1v5a1 1 0 1 0 2 0v-1h.5a2.5 2.5 0 0 0 0-5H5Zm1.5 3H6v-1h.5a.5.5 0 0 1 0 1Zm4.5-3a1 1 0 0 0-1 1v5a1 1 0 0 0 1 1h1.376A2.626 2.626 0 0 0 15 15.375v-1.75A2.626 2.626 0 0 0 12.375 11H11Zm1 5v-3h.375a.626.626 0 0 1 .625.626v1.748a.625.625 0 0 1-.626.626H12Zm5-5a1 1 0 0 0-1 1v5a1 1 0 1 0 2 0v-1h1a1 1 0 1 0 0-2h-1v-1h1a1 1 0 1 0 0-2h-2Z" clip-rule="evenodd"/>
                    </svg>
                    Download PDF
                </button>
                <a href="{{ route('pds.view') }}" class="bg-white/80 backdrop-blur-sm border-2 border-gray-200 hover:border-blue-400 hover:bg-blue-50 text-gray-700 font-semibold py-4 px-8 rounded-xl transition-all duration-300 shadow-md hover:shadow-lg hover:-translate-y-0.5 inline-flex items-center justify-center">
                    Preview Form
                </a>
            </div>
        </div>
    </div>

    <div class="md:col-span-3 space-y-6 relative z-10">
        <div class="bg-gradient-to-br from-amber-50 to-orange-50 p-6 rounded-2xl border border-amber-200/50 shadow-xl shadow-amber-200/30 hover:shadow-2xl hover:shadow-amber-300/40 transition-all duration-300 hover:-translate-y-1">
            <div class="flex items-center mb-4">
                <div class="bg-gradient-to-br from-amber-500 to-orange-600 p-3 rounded-xl shadow-lg">
                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="font-bold text-amber-800 ml-3 text-lg">Important Notes</h3>
            </div>
            <div class="bg-white/60 rounded-xl p-4 mb-4">
                <ul class="space-y-2 text-sm text-amber-900">
                        <li class="flex gap-2">
                            <span class="mt-0.5 h-2 w-2 rounded-full bg-amber-500"></span>
                            Keep your contact info up to date for HR notices.
                        </li>
                        <li class="flex gap-2">
                            <span class="mt-0.5 h-2 w-2 rounded-full bg-amber-500"></span>
                            Upload recent certifications as supporting documents.
                        </li>
                        <li class="flex gap-2">
                            <span class="mt-0.5 h-2 w-2 rounded-full bg-amber-500"></span>
                            Track returned submissions and resubmit promptly.
                        </li>
                    </ul>
            </div>
            <div class="border-t border-amber-200/50 pt-4">
                <p class="text-xs text-amber-600 uppercase font-bold tracking-wider mb-2 flex items-center">
                    <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path>
                    </svg>
                    Privacy Policy
                </p>
                <p class="text-xs text-amber-700 leading-relaxed">Your data is encrypted and handled under the Data Privacy Act of 2012.</p>
            </div>
             
        </div>
    </div>

</div>

        </div>
    </div>
</x-app-layout>
