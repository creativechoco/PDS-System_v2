<?php

// employee 'auth:web'
// admin 'auth:admin'


use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PdsSubmissionController;
use App\Http\Controllers\PdsStepController;
use App\Http\Controllers\PdsPdfController;
use App\Models\User;
use App\Models\PdsSubmission;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ManageUserController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Hash;
use Illuminate\Http\Request;
use App\Models\AdminUser;
use App\Models\RegistrationUser;
use App\Http\Controllers\PdsController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\AdminUserController;
use App\Http\Controllers\AdminProfileController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileEditRequestController;
use App\Http\Controllers\Auth\OtpController;

Route::get('/', function () {
    return redirect()->route('login');
});

// Employee dashboard
Route::get('/employee', [EmployeeController::class, 'dashboard'])
    ->middleware(['auth:web'])
    ->name('employee.dashboard');

Route::post('/employee/rejection/dismiss', [EmployeeController::class, 'dismissRejection'])
    ->middleware(['auth:web'])
    ->name('employee.rejection.dismiss');

Route::post('/employee/approval/dismiss', [EmployeeController::class, 'dismissApproval'])
    ->middleware(['auth:web'])
    ->name('employee.approval.dismiss');

Route::get('/employee/pds/status', [EmployeeController::class, 'latestPdsStatus'])
    ->middleware(['auth:web'])
    ->name('employee.pds.status');

// Notifications (admin/web authenticated)
Route::middleware(['auth:admin,web'])->group(function () {
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead'])
        ->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])
        ->name('notifications.readAll');
    Route::get('/notifications/latest', [NotificationController::class, 'latest'])
        ->name('notifications.latest');
});

// Dashboard Route
Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth:admin,web'])
    ->name('dashboard');




//PDS Review
if (! function_exists('pdsSubmissions')) {
    function pdsSubmissions(): array
    {
        return [
            [
                'key' => 'pds-1',
                'name' => 'Leslie Alexander',
                'avatar' => 'https://i.pravatar.cc/96?img=47',
                'department' => 'HR',
                'email' => 'leslie.alexander@example.com',
                'submitted_at' => 'Jan 24, 2026 • 2:10 PM',
                'status' => 'Approved',
                'type' => 'Permanent',
            ],
            [
                'key' => 'pds-2',
                'name' => 'Michael Scott',
                'avatar' => 'https://i.pravatar.cc/96?img=12',
                'department' => 'Management',
                'email' => 'michael.scott@example.com',
                'submitted_at' => 'Jan 23, 2026 • 9:45 AM',
                'status' => 'Pending',
                'type' => 'Permanent',
            ],
            [
                'key' => 'pds-3',
                'name' => 'Pam Beesly',
                'avatar' => 'https://i.pravatar.cc/96?img=32',
                'department' => 'Administration',
                'email' => 'pam.beesly@example.com',
                'submitted_at' => 'Jan 22, 2026 • 11:30 AM',
                'status' => 'Approved',
                'type' => 'Job On Call',
            ],
            [
                'key' => 'pds-4',
                'name' => 'Jim Halpert',
                'avatar' => 'https://i.pravatar.cc/96?img=65',
                'department' => 'Sales',
                'email' => 'jim.halpert@example.com',
                'submitted_at' => 'Jan 21, 2026 • 4:05 PM',
                'status' => 'Rejected',
                'type' => 'Job On Call',
            ],
            [
                'key' => 'pds-5',
                'name' => 'Dwight Schrute',
                'avatar' => 'https://i.pravatar.cc/96?img=5',
                'department' => 'Sales',
                'email' => 'dwight.schrute@example.com',
                'submitted_at' => 'Jan 20, 2026 • 1:15 PM',
                'status' => 'Approved',
                'type' => 'Permanent',
            ],
            [
                'key' => 'pds-6',
                'name' => 'Angela Martin',
                'avatar' => 'https://i.pravatar.cc/96?img=17',
                'department' => 'Accounting',
                'email' => 'angela.martin@example.com',
                'submitted_at' => 'Jan 19, 2026 • 10:00 AM',
                'status' => 'Pending',
                'type' => 'Permanent',
            ],
            [
                'key' => 'pds-7',
                'name' => 'Kevin Malone',
                'avatar' => 'https://i.pravatar.cc/96?img=39',
                'department' => 'Accounting',
                'email' => 'kevin.malone@example.com',
                'submitted_at' => 'Jan 18, 2026 • 3:40 PM',
                'status' => 'Approved',
                'type' => 'Job On Call',
            ],
            [
                'key' => 'pds-8',
                'name' => 'Oscar Martinez',
                'avatar' => 'https://i.pravatar.cc/96?img=9',
                'department' => 'Accounting',
                'email' => 'oscar.martinez@example.com',
                'submitted_at' => 'Jan 17, 2026 • 8:55 AM',
                'status' => 'Approved',
                'type' => 'Permanent',
            ],
            [
                'key' => 'pds-9',
                'name' => 'Kelly Kapoor',
                'avatar' => 'https://i.pravatar.cc/96?img=41',
                'department' => 'Customer Service',
                'email' => 'kelly.kapoor@example.com',
                'submitted_at' => 'Jan 16, 2026 • 12:20 PM',
                'status' => 'Rejected',
                'type' => 'Job On Call',
            ],
            [
                'key' => 'pds-10',
                'name' => 'Ryan Howard',
                'avatar' => 'https://i.pravatar.cc/96?img=23',
                'department' => 'Marketing',
                'email' => 'ryan.howard@example.com',
                'submitted_at' => 'Jan 15, 2026 • 5:10 PM',
                'status' => 'Pending',
                'type' => 'Permanent',
            ],
        ];
    }
}







//PDS Review route
Route::get('/pds-form', [App\Http\Controllers\PdsReviewController::class, 'index'])
    ->middleware(['auth:admin', 'verified'])
    ->name('pds.form');

Route::get('/pds-form/latest', [App\Http\Controllers\PdsReviewController::class, 'latest'])
    ->middleware(['auth:admin', 'verified'])
    ->name('pds.form.latest');

Route::post('/pds-form/{id}/status', [App\Http\Controllers\PdsReviewController::class, 'updateStatus'])
    ->middleware(['auth:admin', 'verified'])
    ->name('pds.updateStatus');

Route::get('/pds-preview/{user}', [App\Http\Controllers\PdsPdfController::class, 'previewForAdmin'])
    ->middleware(['auth:admin', 'verified'])
    ->name('pds.preview.admin');

Route::get('/pds-preview/{user}/download', [App\Http\Controllers\PdsPdfController::class, 'downloadForAdmin'])
    ->middleware(['auth:admin', 'verified'])
    ->name('pds.preview.admin.download');

//Export/Download logic
if (! function_exists('buildPdsSubmissionsXlsx')) {
    function buildPdsSubmissionsXlsx(array $columns, array $rows, array $colWidths): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to create XLSX.');
        }

        $zip->addFromString('[Content_Types].xml', <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>
XML);

        $zip->addFromString('_rels/.rels', <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML);

        $zip->addFromString('xl/_rels/workbook.xml.rels', <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>
XML);

        $zip->addFromString('xl/workbook.xml', <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="PDS Submissions" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>
XML);

        // Styles: normal, header, approved, pending, rejected
        $zip->addFromString('xl/styles.xml', <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="2">
    <font><sz val="12"/><color theme="1"/><name val="Arial"/></font>
    <font><sz val="12"/><color rgb="FFFFFFFF"/><name val="Arial"/><b/></font>
  </fonts>
  <fills count="6">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF4F46E5"/><bgColor indexed="64"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF22C55E"/><bgColor indexed="64"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFF59E0B"/><bgColor indexed="64"/></patternFill></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FFEF4444"/><bgColor indexed="64"/></patternFill></fill>
  </fills>
  <borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>
  <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
  <cellXfs count="5">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFill="1" applyFont="1" applyAlignment="1"><alignment horizontal="left" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="1" fillId="3" borderId="0" xfId="0" applyFill="1" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="1" fillId="4" borderId="0" xfId="0" applyFill="1" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
    <xf numFmtId="0" fontId="1" fillId="5" borderId="0" xfId="0" applyFill="1" applyFont="1" applyAlignment="1"><alignment horizontal="center" vertical="center" wrapText="1"/></xf>
  </cellXfs>
  <cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>
</styleSheet>
XML);

        $sheetRows = [];

        // Column widths
        $colsXml = '<cols>';
        foreach (array_values($colWidths) as $i => $width) {
            $colsXml .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . $width . '" customWidth="1" />';
        }
        $colsXml .= '</cols>';

        // Header row style index 1
        $rowIndex = 1;
        $cells = '';
        foreach ($columns as $colIndex => $value) {
            $cells .= '<c r="' . chr(65 + $colIndex) . $rowIndex . '" t="inlineStr" s="1"><is><t>' . htmlspecialchars($value, ENT_XML1) . '</t></is></c>';
        }
        $sheetRows[] = '<row r="' . $rowIndex . '">' . $cells . '</row>';

        foreach ($rows as $row) {
            $rowIndex++;
            $cells = '';
            $values = [
                $row['name'] ?? '',
                $row['department'] ?? '',
                $row['email'] ?? '',
                $row['submitted_at'] ?? '',
                $row['type'] ?? '',
                $row['status'] ?? '',
            ];

            $status = strtolower(trim($row['status'] ?? ''));
            $statusStyle = match ($status) {
                'approved' => 2,
                'pending' => 3,
                'rejected' => 4,
                default => 0,
            };

            foreach ($values as $colIndex => $value) {
                $styleId = ($colIndex === 5) ? $statusStyle : 0;
                $cells .= '<c r="' . chr(65 + $colIndex) . $rowIndex . '" t="inlineStr" s="' . $styleId . '"><is><t>' . htmlspecialchars($value, ENT_XML1) . '</t></is></c>';
            }
            $sheetRows[] = '<row r="' . $rowIndex . '">' . $cells . '</row>';
        }

        $sheetXml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . $colsXml
            . '<sheetData>' . implode('', $sheetRows) . '</sheetData>'
            . '</worksheet>';

        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

        $zip->close();

        $content = file_get_contents($tmp);
        @unlink($tmp);
        return $content;
    }
}

// Download ni siya ayaw sa hilabta, mali ni siya kay data gi download pero as is sa ni hehe labyu mariane
if (! function_exists('buildPdsDocx')) {
    function buildPdsDocx(array $submission): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'docx');
        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to create DOCX.');
        }

        $zip->addFromString('[Content_Types].xml', <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/word/document.xml" ContentType="application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml"/>
</Types>
XML);

        $zip->addFromString('_rels/.rels', <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="word/document.xml"/>
</Relationships>
XML);

        $zip->addFromString('word/_rels/document.xml.rels', <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"/>
XML);

        $safe = fn ($value) => htmlspecialchars((string) $value, ENT_XML1);

        $name = $safe($submission['name'] ?? '—');
        $dept = $safe($submission['department'] ?? '—');
        $email = $safe($submission['email'] ?? '—');
        $submitted = $safe($submission['submitted_at'] ?? '—');
        $status = $safe($submission['status'] ?? '—');
        $type = $safe($submission['type'] ?? '—');

        $zip->addFromString('word/document.xml', <<<XML
<?xml version="1.0" encoding="UTF-8" standalone="yes"?>
<w:document xmlns:w="http://schemas.openxmlformats.org/wordprocessingml/2006/main">
  <w:body>
    <w:p><w:r><w:t>PDS Submission</w:t></w:r></w:p>
    <w:p><w:r><w:t>Name: {$name}</w:t></w:r></w:p>
    <w:p><w:r><w:t>Department: {$dept}</w:t></w:r></w:p>
    <w:p><w:r><w:t>Email: {$email}</w:t></w:r></w:p>
    <w:p><w:r><w:t>Submitted: {$submitted}</w:t></w:r></w:p>
    <w:p><w:r><w:t>Type: {$type}</w:t></w:r></w:p>
    <w:p><w:r><w:t>Status: {$status}</w:t></w:r></w:p>
    <w:p><w:r><w:t xml:space="preserve">Preview: see attached image or system record.</w:t></w:r></w:p>
    <w:sectPr><w:pgSz w:w="12240" w:h="15840"/><w:pgMar w:top="1440" w:right="1440" w:bottom="1440" w:left="1440"/></w:sectPr>
  </w:body>
</w:document>
XML);

        $zip->close();

        $content = file_get_contents($tmp);
        @unlink($tmp);
        return $content;
    }
}

//Export PDS Submissions (styled XLSX)
Route::get('/pds-form/export', function () {
    if (! class_exists(ZipArchive::class)) {
        abort(500, 'ZipArchive PHP extension is required to export XLSX. Please enable php_zip.');
    }

    $submissions = pdsSubmissions();
    $columns = ['Name', 'Department', 'Email', 'Submitted', 'Type', 'Status'];
    $colWidths = [28, 18, 30, 22, 14, 12];

    $xlsx = buildPdsSubmissionsXlsx($columns, $submissions, $colWidths);

    return response($xlsx, 200, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'Content-Disposition' => 'attachment; filename="BFAR_PDS_Submissions_' . date('Y-m-d') . '.xlsx"',
    ]);
})->middleware(['auth', 'verified'])->name('pds.export');

//Download individual PDS (DOCX placeholder)
Route::get('/pds-form/{key}/download', function (string $key) {
    if (! class_exists(ZipArchive::class)) {
        abort(500, 'ZipArchive PHP extension is required to export DOCX. Please enable php_zip.');
    }

    $submission = collect(pdsSubmissions())->firstWhere('key', $key);
    if (! $submission) {
        abort(404, 'Submission not found.');
    }

    $docx = buildPdsDocx($submission);

    $safeName = preg_replace('/[^A-Za-z0-9_-]+/', '_', $submission['name'] ?? 'PDS');
    return response($docx, 200, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'Content-Disposition' => 'attachment; filename="PDS_' . $safeName . '_' . date('Y-m-d') . '.docx"',
    ]);
})->middleware(['auth', 'verified'])->name('pds.download');



Route::get('/manage-user', [ManageUserController::class, 'index'])
    ->middleware(['auth:admin'])
    ->name('manage-user');

Route::patch('/manage-user/{user}', [ManageUserController::class, 'update'])
    ->middleware(['auth:admin'])
    ->name('manage-user.update');

Route::delete('/manage-user/{user}', [ManageUserController::class, 'destroy'])
    ->middleware(['auth:admin'])
    ->name('manage-user.destroy');

Route::post('/manage-user/{user}/archive', [ManageUserController::class, 'archiveUser'])
    ->middleware(['auth:admin'])
    ->name('manage-user.archive');

// Archive routes
Route::get('/archive', [ManageUserController::class, 'archive'])
    ->middleware(['auth:admin'])
    ->name('archive');

Route::post('/archive/{user}/unarchive', [ManageUserController::class, 'unarchiveUser'])
    ->middleware(['auth:admin'])
    ->name('archive.unarchive');

Route::delete('/archive/{user}', [ManageUserController::class, 'destroy'])
    ->middleware(['auth:admin'])
    ->name('archive.delete');

Route::get('/archive/export', function () {
    if (! class_exists(ZipArchive::class)) {
        abort(500, 'ZipArchive PHP extension is required to export XLSX. Please enable php_zip.');
    }

    $employees = \App\Models\User::select('name', 'unit', 'email', 'phone', 'type', 'status', 'location_assigned', 'archived_at', 'archived_by')
        ->where('is_archive', true)
        ->latest('archived_at')
        ->get()
        ->map(function ($employee) {
            return [
                'name' => $employee->name,
                'department' => $employee->unit,
                'email' => $employee->email,
                'phone' => $employee->phone,
                'type' => $employee->type,
                'status' => $employee->status,
                'location' => $employee->location_assigned,
                'archived_at' => $employee->archived_at?->format('Y-m-d H:i:s'),
                'archived_by' => $employee->archived_by,
            ];
        })
        ->toArray();

    $columns = ['Name', 'Department', 'Email', 'Phone', 'Employee Status', 'Status', 'Place of Assignment', 'Archived At', 'Archived By'];
    $colWidths = [25, 18, 32, 18, 18, 14, 30, 20, 20];

    $xlsx = buildEmployeesXlsx($columns, $employees, $colWidths);

    return response($xlsx, 200, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'Content-Disposition' => 'attachment; filename="BFAR_Archived_Employees_' . date('Y-m-d') . '.xlsx"',
    ]);
})->middleware(['auth:admin'])->name('archive.export');


if (! function_exists('buildEmployeesXlsx')) {
    function buildEmployeesXlsx(array $columns, array $rows, array $colWidths): string
    {
        $tmp = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new ZipArchive();
        if ($zip->open($tmp, ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('Unable to create XLSX.');
        }

        $zip->addFromString('[Content_Types].xml', <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
  <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
  <Default Extension="xml" ContentType="application/xml"/>
  <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
  <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
  <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
</Types>
XML);

        $zip->addFromString('_rels/.rels', <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
</Relationships>
XML);

        $zip->addFromString('xl/_rels/workbook.xml.rels', <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
  <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
  <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
</Relationships>
XML);

        $zip->addFromString('xl/workbook.xml', <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
  <sheets>
    <sheet name="Employees" sheetId="1" r:id="rId1"/>
  </sheets>
</workbook>
XML);

        // Styles: normal and header with green fill
        $zip->addFromString('xl/styles.xml', <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
  <fonts count="2">
    <font><sz val="12"/><color theme="1"/><name val="Arial"/></font>
    <font><sz val="12"/><color rgb="FFFFFFFF"/><name val="Arial"/><b/></font>
  </fonts>
  <fills count="3">
    <fill><patternFill patternType="none"/></fill>
    <fill><patternFill patternType="gray125"/></fill>
    <fill><patternFill patternType="solid"><fgColor rgb="FF0fb3e4"/><bgColor indexed="64"/></patternFill></fill>
  </fills>
  <borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>
  <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
  <cellXfs count="2">
    <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
    <xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFill="1" applyFont="1" applyAlignment="1">
      <alignment horizontal="left" vertical="center" wrapText="1"/>
    </xf>
  </cellXfs>
  <cellStyles count="1"><cellStyle name="Normal" xfId="0" builtinId="0"/></cellStyles>
</styleSheet>
XML);

        // Worksheet
        $sheetRows = [];

        // Column widths
        $colsXml = '<cols>';
        foreach (array_values($colWidths) as $i => $width) {
            $colsXml .= '<col min="' . ($i + 1) . '" max="' . ($i + 1) . '" width="' . $width . '" customWidth="1" />';
        }
        $colsXml .= '</cols>';

        // Header row with style 1
        $rowIndex = 1;
        $cells = '';
        foreach ($columns as $colIndex => $value) {
            $cells .= '<c r="' . chr(65 + $colIndex) . $rowIndex . '" t="inlineStr" s="1"><is><t>' . htmlspecialchars($value, ENT_XML1) . '</t></is></c>';
        }
        $sheetRows[] = '<row r="' . $rowIndex . '">' . $cells . '</row>';

        // Data rows style 0
        foreach ($rows as $row) {
            $rowIndex++;
            $cells = '';
            $values = [
                $row['name'],
                $row['department'],
                $row['email'],
                $row['phone'],
                $row['type'],
                $row['status'],
                $row['location'],
            ];
            foreach ($values as $colIndex => $value) {
                $cells .= '<c r="' . chr(65 + $colIndex) . $rowIndex . '" t="inlineStr" s="0"><is><t>' . htmlspecialchars($value, ENT_XML1) . '</t></is></c>';
            }
            $sheetRows[] = '<row r="' . $rowIndex . '">' . $cells . '</row>';
        }

        $sheetXml = '<?xml version="1.0" encoding="UTF-8"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . $colsXml
            . '<sheetData>' . implode('', $sheetRows) . '</sheetData>'
            . '</worksheet>';

        $zip->addFromString('xl/worksheets/sheet1.xml', $sheetXml);

        $zip->close();

        $content = file_get_contents($tmp);
        @unlink($tmp);
        return $content;
    }
}

//Manage User Export Route
Route::get('/manage-user/export', function () {
    if (! class_exists(ZipArchive::class)) {
        abort(500, 'ZipArchive PHP extension is required to export XLSX. Please enable php_zip.');
    }

    $employees = manageUserEmployees();
    $columns = ['Name', 'Department', 'Email', 'Phone', 'Employee Status', 'Status', 'Place of Assignment'];
    $colWidths = [30, 18, 32, 18, 18, 14, 36];

    $xlsx = buildEmployeesXlsx($columns, $employees, $colWidths);

    return response($xlsx, 200, [
        'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'Content-Disposition' => 'attachment; filename="BFAR_Employees_' . date('Y-m-d') . '.xlsx"',
    ]);
})->middleware(['auth:admin'])->name('manage-user.export');

// Admin Users
Route::get('/admin-users', [AdminUserController::class, 'index'])
    ->middleware(['auth:admin'])
    ->name('admin.users');

Route::get('/admin-users/{adminUser}', [AdminUserController::class, 'show'])
    ->middleware(['auth:admin'])
    ->name('admin.users.show');

Route::patch('/admin-users/{adminUser}', [AdminUserController::class, 'update'])
    ->middleware(['auth:admin'])
    ->name('admin.users.update');

Route::delete('/admin-users/{adminUser}', [AdminUserController::class, 'destroy'])
    ->middleware(['auth:admin'])
    ->name('admin.users.destroy');

// Admin user creation (frontend modal submission)
Route::post('/admin-users', [AdminUserController::class, 'store'])
    ->middleware(['auth:admin'])
    ->name('admin-users.store');

// Admin profile (name/email/password only)
Route::middleware(['auth:admin'])->group(function () {
    Route::get('/admin/profile', [AdminProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::patch('/admin/profile', [AdminProfileController::class, 'updateProfile'])->name('admin.profile.update');
    Route::put('/admin/profile/password', [AdminProfileController::class, 'updatePassword'])->name('admin.profile.password');
});

// Admin Activity History
Route::get('/admin/activity', [AdminActivityController::class, 'index'])
    ->middleware(['auth:admin'])
    ->name('admin.activity.index');


// Employee creation (Add Employee modal)
Route::post('/registration-users', function (Request $request) {
    $validated = $request->validate([
        'full_name' => ['required', 'string', 'max:255', 'unique:registration_users,full_name'],
    ]);

    $employee = RegistrationUser::create([
        'full_name' => $validated['full_name'],
    ]);

    return response()->json([
        'message' => 'Employee added successfully.',
        'employee' => $employee->only(['id', 'full_name', 'created_at']),
    ], 201);
})->middleware(['auth:admin'])->name('registration-users.store');

//Profile Route
Route::middleware('auth:admin,web')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::post('/profile/request-edit', [ProfileController::class, 'requestEdit'])->name('profile.requestEdit');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Fallback: if someone lands on GET /pds/submit, send them to the PDS form instead of 405
    Route::get('/pds/submit', function () {
        return redirect()->route('pds.form1');
    })->name('pds.submit.get');

    Route::post('/pds/submit', [PdsSubmissionController::class, 'store'])->name('pds.submit');
    Route::post('/pds/save-step/{step}', [PdsStepController::class, 'saveStep'])->name('pds.saveStep');
    Route::post('/pds/autosave', [PdsStepController::class, 'autoSave'])
    ->name('pds.autosave');
    Route::get('/pds/draft', [PdsStepController::class, 'draft'])->name('pds.draft');
    Route::get('/pds/pdf', [PdsPdfController::class, 'download'])->name('pds.pdf');
});

// Profile edit requests (admin only)
Route::middleware(['auth:admin'])->group(function () {
    Route::post('/profile-edit-requests/{profileEditRequest}/approve', [ProfileEditRequestController::class, 'approve'])->name('profile-edit-requests.approve');
    Route::post('/profile-edit-requests/{profileEditRequest}/reject', [ProfileEditRequestController::class, 'reject'])->name('profile-edit-requests.reject');
});

// Employee routes
Route::middleware(['auth','role:employee'])->group(function () {
    Route::get('/employee', [EmployeeController::class, 'dashboard'])->name('employee.dashboard');
    Route::get('/employee/pds/form1', [PdsStepController::class, 'form1'])->name('pds.form1');
    Route::get('/employee/pds/form2', [PdsStepController::class, 'form2'])->name('pds.form2');
    Route::get('/employee/pds/form3', [PdsStepController::class, 'form3'])->name('pds.form3');
    Route::get('/employee/pds/form4', [PdsStepController::class, 'form4'])->name('pds.form4');
    Route::get('/employee/pds/form5', [PdsStepController::class, 'form5'])->name('pds.form5');
});



// Employee routes
Route::middleware(['auth','role:employee'])->group(function () {
Route::get('/pds/view', [PdsController::class, 'view'])->name('pds.view');
    Route::get('/employee/pdsreview/form1', [PdsController::class, 'view'])->name('pdsreview.pdsreview1');
    Route::get('/employee/pdsreview/form2', [PdsController::class, 'review2'])->name('pdsreview.pdsreview2');
    Route::get('/employee/pdsreview/form3', [PdsController::class, 'review3'])->name('pdsreview.pdsreview3');
    Route::get('/employee/pdsreview/form4', [PdsController::class, 'review4'])->name('pdsreview.pdsreview4');
    Route::get('/employee/pdsreview/form5', [PdsController::class, 'review5'])->name('pdsreview.pdsreview5');
    Route::get('/employee/pdsreview/form1/pdf', [PdsPdfController::class, 'preview1'])->name('pdsreview1.pdf');
});


Route::get('/pds/pdf-preview', [PdsPdfController::class, 'preview'])
    ->name('pds.pdf.preview')
    ->middleware('signed'); 


Route::middleware('auth')->group(function () {
    Route::get('/pds/pdf-download', [PdsPdfController::class, 'download'])
        ->name('pds.pdf.download');
});


Route::get('/verification-status', function () {
    return ['verified' => auth()->user()->hasVerifiedEmail()];
})->middleware(['auth'])->name('verification.status.simple');

Route::get('/verification-stream', function () {

    $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function () {

        // 🔥 Disable ALL buffering layers
        while (ob_get_level() > 0) {
            ob_end_flush();
        }
        ob_implicit_flush(true);

        // 🔥 Force server + browser to start streaming immediately
        echo str_repeat(' ', 2048) . "\n";
        flush();

        // Get authenticated user ID once
        $userId = auth()->id();
        if (!$userId) {
            return;
        }

        // Initial fetch
        $user = \App\Models\User::find($userId);
        if (!$user) {
            return;
        }

        $lastVerified = $user->hasVerifiedEmail();

        // Send initial state immediately
        echo "data: " . json_encode([
            'verified' => $lastVerified,
            'redirect' => $lastVerified
                ? (strtolower($user->role ?? '') === 'employee'
                    ? '/employee'
                    : route('dashboard', absolute: false))
                : null,
        ]) . "\n\n";

        flush();

        // If already verified, stop immediately
        if ($lastVerified) {
            return;
        }

        $heartbeatCounter = 0;

        while (true) {

            // Stop if client disconnects
            if (connection_aborted()) {
                break;
            }

            // Re-fetch user (fresh data)
            $user = \App\Models\User::find($userId);
            if (!$user) {
                echo "data: " . json_encode(['error' => 'user_not_found']) . "\n\n";
                flush();
                break;
            }

            $currentVerified = $user->hasVerifiedEmail();

            // Only send update if status changed
            if ($currentVerified !== $lastVerified) {

                $target = strtolower($user->role ?? '') === 'employee'
                    ? '/employee'
                    : route('dashboard', absolute: false);

                echo "data: " . json_encode([
                    'verified' => $currentVerified,
                    'redirect' => $currentVerified ? $target : null,
                ]) . "\n\n";

                flush();

                $lastVerified = $currentVerified;

                // If verified, end stream
                if ($currentVerified) {
                    break;
                }
            }

            // Heartbeat every ~14 seconds
            $heartbeatCounter++;
            if ($heartbeatCounter >= 14) { // 14 * 1 second = 14 seconds
                echo "data: " . json_encode(['heartbeat' => true]) . "\n\n";
                flush();
                $heartbeatCounter = 0;
            }

            sleep(1); // Check every 1 second for faster response
        }
    });

    // 🔥 SSE Headers (optimized)
    $response->headers->set('Content-Type', 'text/event-stream');
    $response->headers->set('Cache-Control', 'no-cache, no-transform');
    $response->headers->set('Connection', 'keep-alive');
    $response->headers->set('X-Accel-Buffering', 'no'); // Nginx

    return $response;

})->middleware(['auth'])->name('verification.stream');

Route::get('/otp/resend', [OtpController::class, 'resend'])
    ->name('otp.resend')
    ->middleware('guest');

    Route::middleware('web')->group(function () {
    Route::get('/otp', [OtpController::class, 'show'])->name('otp.show');
    Route::post('/otp/verify', [OtpController::class, 'verify'])->name('otp.verify');
    Route::post('/otp/resend', [OtpController::class, 'resend'])->name('otp.resend');
    Route::post('/otp/cancel', [OtpController::class, 'cancel'])->name('otp.cancel');
});

require __DIR__.'/auth.php';
