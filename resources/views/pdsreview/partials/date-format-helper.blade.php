@php
if (!function_exists('format_pds_date')) {
    function format_pds_date($value, string $format = 'd/m/Y')
    {
        if ($value instanceof \Carbon\CarbonInterface) {
            return $value->format($format);
        }

        if ($value === null) {
            return '';
        }

        $clean = trim((string) $value);
        if ($clean === '') {
            return '';
        }

        $upper = strtoupper($clean);
        $preserveValues = ['NA', 'N/A', 'NONE', 'NO DATA', 'PRESENT', 'CURRENT', '—', '--'];
        if (in_array($upper, $preserveValues, true)) {
            return $value;
        }

        if (!preg_match('/[\\/\-]/', $clean)) {
            return $value;
        }

        try {
            return \Carbon\Carbon::parse($clean)->format($format);
        } catch (\Throwable $e) {
            return $value;
        }
    }
}
@endphp
