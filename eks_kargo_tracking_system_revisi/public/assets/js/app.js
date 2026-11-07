/**
 * KargoTrack - Core Vanilla JS Interactivity & Business Logic
 */

document.addEventListener('DOMContentLoaded', () => {
    initModalHandlers();
    initLiveSearch();
    initKalkulatorTarif();
    initResiFormValidation();
    initReceiptPreview();
    initDaftarResiModal();
});

/* ==========================================================================
   1. MODAL HANDLERS
   ========================================================================== */
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.style.display = 'flex';
        void modal.offsetHeight;
        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('is-open');
        setTimeout(() => {
            if (!modal.classList.contains('is-open')) {
                modal.style.display = 'none';
            }
        }, 200);
        document.body.style.overflow = '';
    }
}

function initModalHandlers() {
    window.openModal = openModal;
    window.closeModal = closeModal;

    // Close on backdrop click
    document.querySelectorAll('.modal-backdrop').forEach(modal => {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeModal(modal.id);
            }
        });
    });

    // Close on Escape key
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            document.querySelectorAll('.modal-backdrop.is-open').forEach(modal => {
                closeModal(modal.id);
            });
        }
    });
}

/* ==========================================================================
   2. LIVE SEARCH WITHOUT RELOAD (Wilayah & Rute)
   ========================================================================== */
function initLiveSearch() {
    const searchInput = document.getElementById('live-search-input');
    if (!searchInput) return;

    searchInput.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();
        
        // Filter Rute Table Rows
        const ruteRows = document.querySelectorAll('#rute-tbody tr');
        let visibleRuteCount = 0;
        ruteRows.forEach(row => {
            const text = row.getAttribute('data-search') || row.textContent.toLowerCase();
            if (text.includes(query)) {
                row.style.display = '';
                visibleRuteCount++;
            } else {
                row.style.display = 'none';
            }
        });

        // Filter Wilayah Table Rows
        const wilayahRows = document.querySelectorAll('#wilayah-tbody tr');
        wilayahRows.forEach(row => {
            const text = row.getAttribute('data-search') || row.textContent.toLowerCase();
            row.style.display = text.includes(query) ? '' : 'none';
        });

        const ruteCountInfo = document.getElementById('rute-count-info');
        if (ruteCountInfo) {
            ruteCountInfo.textContent = `Menampilkan ${visibleRuteCount} rute cocok dengan "${query}"`;
        }
    });
}

/* ==========================================================================
   3. KALKULATOR TARIF REAL-TIME VIA VANILLA JS
   Rumus Volume: (P x L x T / 6000) * Koli
   Chargeable Weight = max(Aktual, Volume)
   ========================================================================== */
function initKalkulatorTarif() {
    const calcForm = document.getElementById('calc-form');
    if (!calcForm) return;

    const ruteSelect = document.getElementById('calc-rute');
    const pInput = document.getElementById('calc-panjang');
    const lInput = document.getElementById('calc-lebar');
    const tInput = document.getElementById('calc-tinggi');
    const beratInput = document.getElementById('calc-berat');
    const koliInput = document.getElementById('calc-koli');
    const layananRadios = document.querySelectorAll('.calc-layanan-radio');

    const displayTotal = document.getElementById('calc-display-total');
    const displayVolume = document.getElementById('calc-display-volume');
    const displayChargeable = document.getElementById('calc-display-chargeable');
    const displayRate = document.getElementById('calc-display-rate');
    const btnBuatResi = document.getElementById('btn-buat-resi-dari-tarif');

    function calculate() {
        const selectedOption = ruteSelect.options[ruteSelect.selectedIndex];
        const baseRate = parseFloat(selectedOption?.dataset?.tarif || 4500);

        const p = parseFloat(pInput.value) || 0;
        const l = parseFloat(lInput.value) || 0;
        const t = parseFloat(tInput.value) || 0;
        const beratAktual = parseFloat(beratInput.value) || 1;
        const koli = Math.max(1, parseInt(koliInput.value) || 1);

        let layanan = 'Reguler';
        layananRadios.forEach(radio => {
            if (radio.checked) layanan = radio.value;
        });

        // Standar Volumetrik Kargo Darat
        const divisor = (layanan === 'Express Kargo') ? 5000 : 6000;
        const singleVolume = (p > 0 && l > 0 && t > 0) ? (p * l * t) / divisor : 0;
        const totalVolume = Math.round((singleVolume * koli) * 10) / 10;

        // Chargeable Weight
        const serviceMinWeight = layanan === 'Express Kargo' ? 2 : (layanan === 'Trucking FTL' ? 10 : 1);
        const routeMinWeight = parseFloat(selectedOption?.dataset?.minBerat || 1);
        const chargeableWeight = Math.max(beratAktual, totalVolume, serviceMinWeight, routeMinWeight);
        const isVolumeHeavier = (totalVolume > beratAktual);
        const isMinimumApplied = chargeableWeight > Math.max(beratAktual, totalVolume);

        // Multiplier Layanan
        let rateMultiplier = 1.0;
        if (layanan === 'Express Kargo') rateMultiplier = 1.35;
        if (layanan === 'Trucking FTL' && chargeableWeight >= 100) rateMultiplier = 0.85;
        else if (layanan === 'Trucking FTL' && chargeableWeight >= 50) rateMultiplier = 0.90;

        const totalOngkir = Math.round(chargeableWeight * baseRate * rateMultiplier);

        // Update UI
        if (displayTotal) {
            displayTotal.textContent = 'Rp ' + totalOngkir.toLocaleString('id-ID');
        }
        if (displayVolume) {
            displayVolume.textContent = totalVolume.toFixed(1) + ' kg';
        }
        if (displayChargeable) {
            const typeLabel = isMinimumApplied ? '(minimum)' : (isVolumeHeavier ? '(volume)' : '(aktual)');
            displayChargeable.textContent = `${chargeableWeight.toFixed(1)} kg ${typeLabel}`;
        }
        if (displayRate) {
            displayRate.textContent = 'Rp ' + baseRate.toLocaleString('id-ID') + ' / kg';
        }
    }

    // Attach Event Listeners
    [ruteSelect, pInput, lInput, tInput, beratInput, koliInput].forEach(el => {
        if (el) {
            el.addEventListener('input', calculate);
            el.addEventListener('change', calculate);
        }
    });

    layananRadios.forEach(radio => radio.addEventListener('change', calculate));

    // Handle Button "Buat resi dari tarif ini"
    if (btnBuatResi) {
        btnBuatResi.addEventListener('click', () => {
            const ruteId = ruteSelect.value;
            const p = pInput.value;
            const l = lInput.value;
            const t = tInput.value;
            const berat = beratInput.value;
            const koli = koliInput.value;
            let layanan = 'Reguler';
            layananRadios.forEach(radio => {
                if (radio.checked) layanan = radio.value;
            });

            const queryParams = new URLSearchParams({
                rute_id: ruteId,
                panjang: p,
                lebar: l,
                tinggi: t,
                berat_aktual: berat,
                jumlah_koli: koli,
                layanan: layanan
            });

            window.location.href = window.BASE_URL + '/resi?' + queryParams.toString();
        });
    }

    // Run once on init
    calculate();
}

/* ==========================================================================
   4. FORM VALIDATION PENERBITAN RESI
   Validasi ketat nomor telepon (08... 10-13 digit) & Kode Pos (5 digit)
   ========================================================================== */
function initResiFormValidation() {
    const form = document.getElementById('form-resi');
    if (!form) return;

    const telpInput = document.getElementById('telp_penerima');
    const telpError = document.getElementById('telp-penerima-error');

    const posInput = document.getElementById('kode_pos_penerima');
    const posError = document.getElementById('kode-pos-error');

    function validatePhone() {
        if (!telpInput) return true;
        const val = telpInput.value.replace(/[^0-9]/g, '');
        // Format valid: mulai 08, panjang 10 - 13 digit
        const isValid = /^08[0-9]{8,11}$/.test(val);

        if (!isValid && val.length > 0) {
            telpInput.classList.add('is-invalid');
            if (telpError) telpError.classList.remove('hidden');
            return false;
        } else {
            telpInput.classList.remove('is-invalid');
            if (telpError) telpError.classList.add('hidden');
            return true;
        }
    }

    function validatePostal() {
        if (!posInput) return true;
        const val = posInput.value.trim();
        // Format valid: tepat 5 digit angka
        const isValid = /^[0-9]{5}$/.test(val);

        if (!isValid && val.length > 0) {
            posInput.classList.add('is-invalid');
            if (posError) posError.classList.remove('hidden');
            return false;
        } else {
            posInput.classList.remove('is-invalid');
            if (posError) posError.classList.add('hidden');
            return true;
        }
    }

    if (telpInput) {
        telpInput.addEventListener('input', validatePhone);
        telpInput.addEventListener('blur', validatePhone);
    }

    if (posInput) {
        posInput.addEventListener('input', validatePostal);
        posInput.addEventListener('blur', validatePostal);
    }

    form.addEventListener('submit', function(e) {
        const phoneOk = validatePhone();
        const posOk = validatePostal();

        if (!phoneOk || !posOk) {
            e.preventDefault();
            alert('Mohon perbaiki data formulir yang ditandai dengan garis merah.');
        }
    });
}

/* ==========================================================================
   5. RECEIPT LIVE PREVIEW SYNC & JSBARCODE
   ========================================================================== */
function initReceiptPreview() {
    const previewContainer = document.getElementById('receipt-preview-container');
    if (!previewContainer) return;

    const namaPenerimaInput = document.getElementById('nama_penerima');
    const alamatPenerimaInput = document.getElementById('alamat_penerima');
    const ruteSelect = document.getElementById('resi_rute_id');
    const beratInput = document.getElementById('resi_berat');
    const koliInput = document.getElementById('resi_koli');
    const layananSelect = document.getElementById('resi_layanan');
    const pInput = document.getElementById('resi_panjang');
    const lInput = document.getElementById('resi_lebar');
    const tInput = document.getElementById('resi_tinggi');

    const previewBarcodeSvg = document.getElementById('preview-barcode-svg');
    const previewNoResi = document.getElementById('preview-no-resi');
    const previewPenerimaNama = document.getElementById('preview-penerima-nama');
    const previewPenerimaAlamat = document.getElementById('preview-penerima-alamat');
    const previewRute = document.getElementById('preview-rute');
    const previewLayanan = document.getElementById('preview-layanan');
    const previewBerat = document.getElementById('preview-berat');
    const previewOngkir = document.getElementById('preview-ongkir');
    const previewLacakText = document.getElementById('preview-lacak-text');

    function updatePreview() {
        if (namaPenerimaInput && previewPenerimaNama) {
            previewPenerimaNama.textContent = namaPenerimaInput.value || 'Nama Penerima';
        }

        if (alamatPenerimaInput && previewPenerimaAlamat) {
            previewPenerimaAlamat.textContent = alamatPenerimaInput.value || 'Alamat Penerima';
        }

        if (ruteSelect && previewRute) {
            const opt = ruteSelect.options[ruteSelect.selectedIndex];
            const asalKode = opt?.dataset?.asalKode || 'CRB';
            const tujuanKode = opt?.dataset?.tujuanKode || 'JKT';
            previewRute.textContent = `${asalKode} → ${tujuanKode}`;
        }

        if (layananSelect && previewLayanan) {
            previewLayanan.textContent = layananSelect.value || 'Reguler';
        }

        const beratAktual = parseFloat(beratInput?.value || 14);
        const koli = parseInt(koliInput?.value || 2);
        const p = parseFloat(pInput?.value || 0);
        const l = parseFloat(lInput?.value || 0);
        const t = parseFloat(tInput?.value || 0);

        if (previewBerat) {
            previewBerat.textContent = `${beratAktual} kg / ${koli} koli`;
        }

        // Hitung total ongkir sinkron dengan polimorfisme layanan
        const opt = ruteSelect ? ruteSelect.options[ruteSelect.selectedIndex] : null;
        const tarif = parseFloat(opt?.dataset?.tarif || 4500);
        const layanan = layananSelect ? layananSelect.value : 'Reguler';

        let divisor = 6000;
        let rateMultiplier = 1.0;
        if (layanan === 'Express Kargo') {
            divisor = 5000;
            rateMultiplier = 1.35;
        }

        const volumeRaw = (p > 0 && l > 0 && t > 0) ? ((p * l * t) / divisor) * koli : 0;
        const volume = Math.round(volumeRaw * 10) / 10;
        const serviceMinWeight = layanan === 'Express Kargo' ? 2 : (layanan === 'Trucking FTL' ? 10 : 1);
        const routeMinWeight = parseFloat(opt?.dataset?.minBerat || 1);
        const chargeable = Math.max(beratAktual, volume, serviceMinWeight, routeMinWeight);

        if (layanan === 'Trucking FTL' && chargeable >= 100) {
            rateMultiplier = 0.85;
        } else if (layanan === 'Trucking FTL' && chargeable >= 50) {
            rateMultiplier = 0.90;
        }

        const total = Math.round(chargeable * tarif * rateMultiplier);

        if (previewOngkir) {
            previewOngkir.textContent = 'Rp ' + total.toLocaleString('id-ID');
        }

        // Render Barcode via JsBarcode
        const resiCode = previewNoResi ? previewNoResi.textContent.trim() : 'KT-CRB-261030-0047';
        if (previewBarcodeSvg && typeof JsBarcode !== 'undefined') {
            try {
                JsBarcode(previewBarcodeSvg, resiCode, {
                    format: "CODE128",
                    width: 1.5,
                    height: 38,
                    displayValue: false
                });
            } catch (err) {
                console.warn('JsBarcode render error', err);
            }
        }

        if (previewLacakText) {
            previewLacakText.textContent = `Lacak: kargotrack.id/${resiCode}`;
        }
    }

    [namaPenerimaInput, alamatPenerimaInput, ruteSelect, beratInput, koliInput, layananSelect, pInput, lInput, tInput].forEach(el => {
        if (el) {
            el.addEventListener('input', updatePreview);
            el.addEventListener('change', updatePreview);
        }
    });

    updatePreview();
}

/* ==========================================================================
   6. DAFTAR RESI MODAL & AJAX SEARCH
   ========================================================================== */
function initDaftarResiModal() {
    const modalSearch = document.getElementById('modal-search-resi');
    const tbody = document.getElementById('tbody-daftar-resi');

    function loadResiList(query = '') {
        if (!tbody) return;
        tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-slate-400">Memuat data resi...</td></tr>';

        fetch(window.BASE_URL + '/resi/search?q=' + encodeURIComponent(query))
            .then(res => res.json())
            .then(res => {
                if (!res.success || !res.data || res.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-slate-400">Tidak ada resi ditemukan.</td></tr>';
                    return;
                }

                let html = '';
                res.data.forEach(r => {
                    const statusClass = (r.status_pengiriman === 'Diterima penerima') ? 'badge-success' :
                                      (r.status_pengiriman === 'Dalam perjalanan' ? 'badge-warning' : 'badge-info');

                    html += `
                        <tr>
                            <td class="font-mono font-bold text-teal-900">${escapeHtml(r.no_resi)}</td>
                            <td>
                                <div class="font-bold">${escapeHtml(r.nama_penerima)}</div>
                                <div class="text-[11px] text-slate-400">Dari: ${escapeHtml(r.nama_pengirim)}</div>
                            </td>
                            <td>${escapeHtml(r.asal_nama || 'CRB')} → ${escapeHtml(r.tujuan_nama || 'JKT')}</td>
                            <td>
                                <div>${escapeHtml(r.isi_paket)}</div>
                                <div class="text-[11px] text-slate-500">${r.berat_dikenakan} kg (${r.jumlah_koli} koli)</div>
                            </td>
                            <td class="font-bold text-slate-800">Rp ${Number(r.total_ongkir).toLocaleString('id-ID')}</td>
                            <td><span class="badge ${statusClass}">${escapeHtml(r.status_pengiriman)}</span></td>
                            <td class="text-right whitespace-nowrap">
                                <a href="${window.BASE_URL}/tracking?resi=${encodeURIComponent(r.no_resi)}" class="text-xs text-teal-700 hover:text-teal-900 font-semibold mr-2">
                                    Lacak
                                </a>
                                <a href="${window.BASE_URL}/resi/printReceipt/${r.id}/thermal" target="_blank" class="text-xs text-amber-600 hover:text-amber-800 font-semibold">
                                    Cetak
                                </a>
                            </td>
                        </tr>
                    `;
                });
                tbody.innerHTML = html;
            })
            .catch(() => {
                tbody.innerHTML = '<tr><td colspan="7" class="text-center py-4 text-rose-500">Gagal memuat daftar resi.</td></tr>';
            });
    }

    if (modalSearch) {
        let debounceTimer;
        modalSearch.addEventListener('input', function() {
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(() => {
                loadResiList(this.value.trim());
            }, 300);
        });
    }

    // Load initial list on modal open
    window.loadDaftarResi = loadResiList;
    loadResiList();
}

function escapeHtml(text) {
    if (!text) return '';
    return text.toString()
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
}
