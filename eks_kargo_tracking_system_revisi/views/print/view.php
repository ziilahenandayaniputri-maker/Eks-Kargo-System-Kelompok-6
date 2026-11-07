<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle ?? 'Cetak Resi KargoTrack') ?></title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="<?= base_url('assets/css/style.css?v=1.1.0') ?>">
    <script src="<?= base_url('assets/js/jsbarcode.min.js?v=1.1.0') ?>"></script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }
            body {
                background: white !important;
                padding: 0 !important;
                margin: 0 !important;
            }
        }
    </style>
</head>
<body class="bg-slate-100 min-h-screen p-4 sm:p-8 flex flex-col items-center">

<!-- Print Action Header (Hidden during print) -->
<div class="no-print bg-white p-4 rounded-xl border border-slate-200 shadow-sm max-w-xl w-full mb-6 flex items-center justify-between">
    <div>
        <div class="text-xs text-slate-400">Pratinjau Format Cetak</div>
        <div class="text-sm font-bold text-slate-800"><?= ($format === 'a4') ? 'Surat Muatan Kargo (A4)' : 'Struk Thermal 58 mm' ?></div>
    </div>
    <div class="flex gap-2">
        <button type="button" onclick="window.close()" class="px-3 py-1.5 border border-slate-200 rounded text-xs font-semibold hover:bg-slate-50">
            Tutup
        </button>
        <button type="button" onclick="window.print()" class="px-4 py-1.5 bg-[#f59e0b] hover:bg-[#d97706] text-white rounded text-xs font-bold shadow flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
            <span>Cetak Sekarang</span>
        </button>
    </div>
</div>

<!-- Rendered Receipt Document (Polymorphic HTML) -->
<div class="<?= ($format === 'a4') ? 'max-w-3xl w-full' : 'max-w-[340px] w-full' ?> bg-white p-2">
    <?= $renderedHtml ?>
</div>

<script>
    // Inisialisasi barcode scannable via JsBarcode
    window.addEventListener('DOMContentLoaded', () => {
        const noResi = '<?= e($resi['no_resi'] ?? 'KT-CRB-261030-0047') ?>';
        const thermalSvg = document.getElementById('barcode-thermal-svg');
        const a4Svg = document.getElementById('barcode-a4-svg');

        if (thermalSvg && typeof JsBarcode !== 'undefined') {
            JsBarcode(thermalSvg, noResi, {
                format: "CODE128",
                width: 1.6,
                height: 40,
                displayValue: false
            });
        }

        if (a4Svg && typeof JsBarcode !== 'undefined') {
            JsBarcode(a4Svg, noResi, {
                format: "CODE128",
                width: 1.8,
                height: 40,
                displayValue: false
            });
        }
    });
</script>

</body>
</html>

