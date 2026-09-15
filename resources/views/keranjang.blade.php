@extends('layouts.app')

@section('content')

<style>
    body {
        background: #ffffff !important;
       font-family: 'Poppins', sans-serif;
    }

    .cart-wrapper {
        width: 100%;
        padding: 20px 0;
    }

    /* Header */
    .cart-header {
        display: flex;
        align-items: center;
        gap: 10px;
        padding: 20px 30px;
        font-weight: 600;
        color: #123A63;
        font-size: 17px;
    }

    .cart-header a {
        text-decoration: none;
        color: #123A63;
        font-size: 15px;
        font-weight: 700;
        width: 38px;
        height: 38px;
        border: 2px solid #123A63;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        line-height: 1;
    }

    /* Empty State */
    .empty-cart {
        margin-top: 60px;
        text-align: center;
        color: #123A63;
    }

    .empty-cart img {
        width: 90px;
        opacity: .85;
    }

    .empty-cart h3 {
        margin-top: 20px;
        font-size: 18px;
        font-weight: 600;
    }

    .empty-cart p {
        font-size: 14px;
        color: #6c757d;
    }

    /* Cart Item */
    .cart-item {
        background: #F8FBFF;
        border: 1px solid #DCE6F2;
        border-radius: 10px;
        padding: 10px 14px;
        margin-bottom: 8px;
    }

    .item-title {
        font-size: 14px;
        font-weight: 700;
        color: #123A63;
        margin-bottom: 1px;
    }

    .item-desc {
        font-size: 13px;
        color: #6c757d;
        margin: 4px 0;
    }

    .item-details {
        margin: 3px 0 0;
        padding-left: 16px;
        color: #4f6377;
        font-size: 11px;
        line-height: 1.35;
    }

    .item-price {
        font-size: 13px;
        color: #123A63;
        font-weight: bold;
        line-height: 1.2;
    }

    .remove-btn {
        border: none;
        background: transparent;
        color: #d9534f;
        font-size: 13px;
        cursor: pointer;
    }

    /* Footer Total */
    .cart-footer {
        background: #123A63;
        color: white;
        padding: 20px;
        border-radius: 10px;
        margin-top: 20px;
    }

    .btn-checkout {
        background: white;
        color: #123A63;
        width: 100%;
        padding: 12px;
        border-radius: 8px;
        border: none;
        margin-top: 15px;
        font-weight: 600;
        cursor: pointer;
    }

    .cart-content {
        padding: 0 30px 30px;
    }

    .qty-control {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        background: #ffffff;
        border: 1px solid #dce6f2;
        border-radius: 999px;
        padding: 2px 7px;
    }

    .qty-btn {
        border: none;
        background: #123A63;
        color: #fff;
        width: 22px;
        height: 22px;
        border-radius: 50%;
        font-size: 14px;
        line-height: 1;
        cursor: pointer;
    }

    .qty-value {
        min-width: 18px;
        text-align: center;
        font-weight: 600;
        color: #123A63;
    }

    .item-subtotal {
        font-size: 12px;
        color: #4b647f;
        margin-top: 1px;
    }

    .item-main {
        flex: 1;
        min-width: 0;
    }

    .item-actions {
        display: flex;
        flex-direction: column;
        align-items: flex-end;
        gap: 4px;
    }

    .request-card {
        background: #ffffff;
        border: 2px solid #1e5fa8;
        border-radius: 16px;
        padding: 16px;
        box-shadow: 0 6px 14px rgba(18, 58, 99, 0.12);
    }

    .request-title {
        font-weight: 700;
        color: #123A63;
        font-size: 15px;
        margin-bottom: 10px;
    }

    .request-card .form-label {
        margin-bottom: 4px;
        font-size: 13px;
    }

    .request-card .required-mark {
        color: #dc3545;
        font-weight: 700;
    }

    .swal-validation-popup {
        width: min(38rem, calc(100vw - 2rem));
        padding: 1.2rem 1.1rem 0.95rem;
        border-radius: 1rem;
    }

    .swal-validation-container {
        align-items: flex-start !important;
        padding-top: 7.25rem !important;
        padding-bottom: 1.25rem !important;
    }

    .swal-validation-popup .swal2-icon {
        width: 4.25rem !important;
        height: 4.25rem !important;
        margin: 0.1rem auto 0.5rem !important;
    }

    .swal-validation-popup .swal2-icon .swal2-icon-content {
        font-size: 2.4rem !important;
    }

    .swal-validation-title {
        font-size: 1.35rem;
        line-height: 1.2;
        font-weight: 700;
        margin-bottom: 0.45rem;
    }

    .swal-validation-content {
        margin: 0 auto;
        max-width: 33rem;
        text-align: left;
    }

    .swal-validation-intro {
        font-size: 0.82rem;
        color: #6c757d;
        margin-bottom: 0.5rem;
    }

    .swal-validation-list {
        margin: 0;
        padding-left: 1.1rem;
        color: #4b5563;
        font-size: 0.84rem;
        line-height: 1.45;
        columns: 2;
        column-gap: 1.9rem;
    }

    .swal-validation-list li {
        break-inside: avoid;
        margin-bottom: 0.32rem;
    }

    .swal-validation-button {
        min-width: 4.4rem;
        border-radius: 0.65rem !important;
        font-size: 0.9rem !important;
        font-weight: 600 !important;
        padding: 0.6rem 0.9rem !important;
    }

    .request-card .form-control,
    .request-card .form-select {
        min-height: 32px;
        font-size: 13px;
        line-height: 1.2;
        padding: 5px 10px;
    }

    .request-card textarea.form-control {
        min-height: 78px;
        padding-top: 8px;
        padding-bottom: 8px;
    }

    .request-card .small {
        font-size: 11px;
    }

    .request-card .form-check {
        margin-top: 10px !important;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    /* checkbox border hitam */

    .form-check-input {
        border: 1px solid #000;
    }

    .request-card .form-check-input {
        width: 16px;
        height: 16px;
        margin-top: 0;
        /* checkbox border hitam */
        border: 1px solid #000;
    }

    .request-card .form-check-label {
        font-size: 13px;
        line-height: 1.2;
        margin-bottom: 0;
    }

    .summary-card {
        background: #123A63;
        color: #fff;
        border-radius: 14px;
        padding: 8px 18px;
        min-height: 60px;
        display: flex;
        align-items: center;
        box-shadow: 0 6px 14px rgba(18, 58, 99, 0.2);
    }

    .summary-row {
        display: flex;
        justify-content: space-between;
        font-size: 14px;
        margin-bottom: 8px;
    }

    .summary-total {
        display: flex;
        justify-content: space-between;
        align-items: center;
        width: 100%;
        font-size: 15px;
        font-weight: 700;
        margin-top: 0;
    }

    .authority-text {
        font-size: 12px;
        color: #4b647f;
        margin-top: 12px;
    }

    .info-note {
        display: inline-block;
        background: #e7f1ff;
        color: #123A63;
        font-size: 12px;
        font-weight: 600;
        padding: 6px 10px;
        border-radius: 8px;
        border: 1px solid #c7dcf7;
    }

    .btn-submit {
        background: #123A63;
        color: #fff;
        border: none;
        padding: 10px 20px;
        border-radius: 10px;
        font-weight: 600;
        box-shadow: 0 6px 12px rgba(18, 58, 99, 0.2);
    }

    .signature-canvas {
        width: 100%;
        height: 200px;
        border: 1px dashed #123A63;
        border-radius: 10px;
        background: #f8fbff;
        touch-action: none;
    }

    /* RESPONSIVE */
    @media (max-width: 576px) {
        .cart-header {
            padding: 20px;
            font-size: 17px;
        }
        .item-title {
            font-size: 13px;
            line-height: 1.2;
        }
        .btn-checkout { font-size: 14px; }
        .cart-item {
            padding: 9px 10px;
        }
        .item-price {
            font-size: 12px;
            line-height: 1.2;
        }
        .item-details {
            font-size: 10px;
            padding-left: 14px;
        }
        .item-subtotal {
            font-size: 10px;
            margin-top: 0;
        }
        .remove-btn {
            font-size: 12px;
        }
        .qty-control {
            gap: 4px;
            padding: 2px 6px;
        }
        .qty-btn {
            width: 20px;
            height: 20px;
            font-size: 12px;
        }
        .qty-value {
            min-width: 16px;
            font-size: 11px;
        }
        .summary-card {
            min-height: 52px;
            padding: 6px 14px;
            border-radius: 12px;
        }
        .summary-total {
            font-size: 13px;
        }
        .request-card {
            padding: 14px;
            border-radius: 14px;
        }
        .request-title {
            font-size: 13px;
            margin-bottom: 9px;
        }
        .request-card .form-label {
            font-size: 10px;
            margin-bottom: 4px;
            line-height: 1.15;
        }
        .request-card .form-control,
        .request-card .form-select {
            min-height: 30px;
            font-size: 11px;
            padding: 4px 8px;
        }
        .request-card .form-control::placeholder,
        .request-card textarea.form-control::placeholder {
            font-size: 10px;
            line-height: 1.2;
        }
        .request-card textarea.form-control {
            min-height: 64px;
            padding-top: 6px;
            padding-bottom: 6px;
        }
        .request-card .small {
            font-size: 10px;
        }
        .request-company-grid {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            row-gap: 9px;
            column-gap: 10px;
        }
        .request-company-grid > [class*="col-"] {
            width: auto;
            padding: 0 !important;
        }
        .request-company-grid > .col-12 {
            grid-column: 1 / -1;
        }
        .request-company-grid > .upload-field {
            grid-column: 1 / -1;
        }
        .request-company-grid > .upload-field .form-label {
            white-space: nowrap;
        }
        .info-note {
            font-size: 10px;
            line-height: 1.3;
            padding: 5px 8px;
        }
        .request-card .form-check-label {
            font-size: 11px;
            line-height: 1.2;
        }
        .request-card .form-check-input {
            width: 14px;
            height: 14px;
        }

        .swal-validation-popup {
            width: min(26rem, calc(100vw - 1.5rem));
            padding: 1rem 0.9rem 0.85rem;
            border-radius: 1rem;
        }

        .swal-validation-container {
            padding-top: 6.25rem !important;
            padding-bottom: 1rem !important;
        }

        .swal-validation-popup .swal2-icon {
            width: 3.7rem !important;
            height: 3.7rem !important;
            margin-bottom: 0.4rem !important;
        }

        .swal-validation-popup .swal2-icon .swal2-icon-content {
            font-size: 2.05rem !important;
        }

        .swal-validation-title {
            font-size: 1.18rem;
        }

        .swal-validation-content {
            max-width: 100%;
        }

        .swal-validation-intro {
            font-size: 0.76rem;
        }

        .swal-validation-list {
            font-size: 0.8rem;
            line-height: 1.38;
            columns: 1;
            column-gap: 0;
        }

        .swal-validation-button {
            min-width: 4.2rem;
            font-size: 0.85rem !important;
            padding: 0.58rem 0.85rem !important;
        }

        .btn-submit {
            font-size: 12px;
            padding: 8px 14px;
            border-radius: 8px;
        }
    }
</style>

<div class="cart-wrapper">

    <div class="cart-header">
        <a href="/daftar_pelayanan"><i class="bi bi-arrow-left"></i></a>
        <span>Keranjang Pelayanan</span>
    </div>

    {{-- ========== BAGIAN KOSONG (HILANGKAN JIKA ADA ITEM) ========== --}}
    <div class="empty-cart" id="cartEmpty">
        <img src="https://cdn-icons-png.flaticon.com/512/102/102661.png">
        <h3>Keranjang Kosong</h3>
        <p>Anda belum menambahkan layanan apa pun</p>
    </div>
    {{-- ============================================================= --}}

    <div class="cart-content" id="cartContent" style="display:none;">
        <div class="row g-4">
            <div class="col-lg-5">
                <div class="d-flex justify-content-end mb-2">
                    <button type="button" class="btn btn-outline-danger btn-sm" id="clearCartBtn">Kosongkan Keranjang</button>
                </div>
                <div id="cartItems"></div>
                <div class="summary-card mt-3">
                    <div class="summary-total">
                        <span>Total</span>
                        <span id="listSubtotal">Rp. 0,-</span>
                    </div>
                </div>
            </div>
            <div class="col-lg-7">
                <div class="request-card mb-4">
                    <div class="request-title">Informasi Perusahaan</div>
                    <div class="row g-2 request-company-grid">
                        <div class="col-md-6">
                            <label class="form-label">Nama Perusahaan <span class="required-mark">*</span></label>
                            <input type="text" class="form-control" name="company_name" placeholder="PT. Contoh Perusahaan" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nama Penanggung Jawab <span class="required-mark">*</span></label>
                            <input type="text" class="form-control" name="responsible_name" id="responsibleName" placeholder="Nama Lengkap" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Email Perusahaan <span class="required-mark">*</span></label>
                            <input type="email" class="form-control" name="company_email" placeholder="email@perusahaan.com" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nomor Telepon (WA) <span class="required-mark">*</span></label>
                            <input type="text" class="form-control" name="company_phone" placeholder="Nomor Telepon" inputmode="numeric" pattern="[0-9]*" data-only-digits required>
                            <div class="small text-danger mt-1 d-none" data-phone-warning>Hanya boleh angka.</div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jenis Perusahaan <span class="required-mark">*</span></label>
                            <select class="form-select" name="company_type" required>
                                <option value="" selected disabled>Pilih jenis perusahaan</option>
                                <option value="Food & Beverage (FnB)">Food & Beverage (FnB)</option>
                                <option value="Manufaktur">Manufaktur</option>
                                <option value="Retail / Perdagangan">Retail / Perdagangan</option>
                                <option value="Teknologi / IT">Teknologi / IT</option>
                                <option value="Konstruksi">Konstruksi</option>
                                <option value="Logistik & Transportasi">Logistik & Transportasi</option>
                                <option value="Kesehatan">Kesehatan</option>
                                <option value="Pendidikan">Pendidikan</option>
                                <option value="Perhotelan / Hospitality">Perhotelan / Hospitality</option>
                                <option value="Jasa Profesional (Konsultan, Legal, dll)">Jasa Profesional (Konsultan, Legal, dll)</option>
                                <option value="Keuangan / Fintech">Keuangan / Fintech</option>
                                <option value="Pertanian / Agribisnis">Pertanian / Agribisnis</option>
                                <option value="Energi & Pertambangan">Energi & Pertambangan</option>
                                <option value="Properti / Real Estate">Properti / Real Estate</option>
                                <option value="Industri Kreatif">Industri Kreatif</option>
                                <option value="Lainnya">Lainnya</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Provinsi <span class="required-mark">*</span></label>
                            <select class="form-select" name="company_province" id="companyProvince" required>
                                <option value="" selected disabled>Pilih provinsi</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Kota/Kabupaten <span class="required-mark">*</span></label>
                            <select class="form-select" name="company_city" id="companyCity" disabled required>
                                <option value="" selected disabled>Pilih kota/kabupaten</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jumlah Pekerja <span class="required-mark">*</span></label>
                            <input type="number" min="1" class="form-control" name="worker_count" placeholder="Contoh: 50" required>
                        </div>
                        <div class="col-md-6 upload-field">
                            <label class="form-label">Upload Surat/Dokumen Permohonan <span class="required-mark">*</span></label>
                            <input type="file" class="form-control" name="order_proof" accept=".pdf,.jpg,.jpeg,.png" required>
                            <div class="small text-muted mt-1">Format: PDF/JPG/PNG, maks 5MB.</div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">Alamat Perusahaan <span class="required-mark">*</span></label>
                            <textarea class="form-control" name="company_address" rows="3" placeholder="Alamat Lengkap Perusahaan" required></textarea>
                        </div>
                    </div>

                    <div class="mt-4">
                        <span class="info-note">Apakah nama diatas memiliki wewenang dalam menandatangani surat perjanjian kerjasama/SPK?</span>
                    </div>
                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" id="authoritySameAsResponsible">
                        <label class="form-check-label" for="authoritySameAsResponsible">
                            Ya, sama dengan penanggung jawab
                        </label>
                    </div>
                    <div class="row g-3 mt-1">
                        <div class="col-md-6">
                            <label class="form-label">Nama <span class="required-mark">*</span></label>
                            <input type="text" class="form-control" name="authority_name" id="authorityName" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Jabatan <span class="required-mark">*</span></label>
                            <input type="text" class="form-control" name="authority_role" id="authorityRole" required>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button class="btn-submit">Kirim Permintaan</button>
                </div>
            </div>
        </div>
    </div>


</div>

<div class="modal fade" id="signatureModal" tabindex="-1" aria-labelledby="signatureModalLabel" aria-hidden="true" data-signature-modal>
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title" id="signatureModalLabel">Tanda Tangan Penanggung Jawab</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-2">Silakan bubuhkan tanda tangan sebelum permintaan diproses.</p>
                <canvas class="signature-canvas" data-signature-canvas></canvas>
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <span class="text-muted small">Gunakan mouse atau sentuhan.</span>
                    <button type="button" class="btn btn-outline-secondary btn-sm" data-signature-clear>Hapus</button>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary btn-sm" data-signature-submit>Simpan & Kirim</button>
            </div>
        </div>
    </div>
</div>

<script>
    const isAuthenticated = @json(auth()->check());
    const loginUrl = @json(route('login'));

    function csrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    }

    function formatRupiah(value) {
        return 'Rp. ' + Number(value).toLocaleString('id-ID') + ',-';
    }

    function renderCart(data) {
        const items = data.items || [];
        const emptyEl = document.getElementById('cartEmpty');
        const contentEl = document.getElementById('cartContent');
        const itemsEl = document.getElementById('cartItems');

        if (!items.length) {
            emptyEl.style.display = 'block';
            contentEl.style.display = 'none';
            document.getElementById('listSubtotal').textContent = formatRupiah(0);
            return;
        }

        emptyEl.style.display = 'none';
        contentEl.style.display = 'block';

        itemsEl.innerHTML = items.map(item => {
            const label = item.category ? `${item.category} - ${item.name}` : item.name;
            const details = Array.isArray(item.details) ? item.details : [];
            const detailsHtml = details.length
                ? `<ul class="item-details">${details.map(detail => `<li>${detail}</li>`).join('')}</ul>`
                : '';

            if (item.item_type === 'package') {
                return `
                    <div class="cart-item d-flex justify-content-between align-items-center gap-2">
                        <div class="item-main pe-2">
                            <div class="item-title">${label}</div>
                            ${detailsHtml}
                            <div class="item-price">${formatRupiah(item.price)} x 1</div>
                            <div class="item-subtotal">Subtotal: ${formatRupiah(item.subtotal)}</div>
                        </div>
                        <div class="item-actions">
                            <button class="remove-btn" data-action="remove-package" data-package-key="${item.package_key || ''}">Hapus</button>
                        </div>
                    </div>
                `;
            }

            return `
                <div class="cart-item d-flex justify-content-between align-items-center gap-2">
                    <div class="item-main pe-2">
                        <div class="item-title">${label}</div>
                        <div class="item-price">${formatRupiah(item.price)}</div>
                        <div class="item-subtotal">Subtotal: ${formatRupiah(item.subtotal)}</div>
                    </div>
                    <div class="item-actions text-end">
                        <div class="qty-control">
                            <button class="qty-btn" data-action="minus" data-id="${item.id}" data-qty="${item.qty}">-</button>
                            <span class="qty-value">${item.qty}</span>
                            <button class="qty-btn" data-action="plus" data-id="${item.id}" data-qty="${item.qty}">+</button>
                        </div>
                        <button class="remove-btn" data-action="remove" data-id="${item.id}">Hapus</button>
                    </div>
                </div>
            `;
        }).join('');

        document.getElementById('listSubtotal').textContent = formatRupiah(data.subtotal || 0);
    }

    async function fetchCart() {
        if (!isAuthenticated) {
            renderCart({ items: [], subtotal: 0 });
            return;
        }

        const response = await fetch('/cart', {
            headers: { 'Accept': 'application/json' },
            credentials: 'same-origin',
        });

        if (response.status === 401) {
            window.location.href = loginUrl;
            return;
        }

        if (!response.ok) {
            renderCart({ items: [], subtotal: 0 });
            return;
        }

        const data = await response.json();
        renderCart(data);
    }

    async function updateItem(id, qty) {
        const response = await fetch(`/cart/items/${id}`, {
            method: 'PATCH',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            credentials: 'same-origin',
            body: JSON.stringify({ qty }),
        });

        if (!response.ok) {
            return;
        }

        const data = await response.json();
        renderCart(data);
    }

    async function removeItem(id) {
        const response = await fetch(`/cart/items/${id}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            return;
        }

        const data = await response.json();
        renderCart(data);
    }

    async function removePackage(packageKey) {
        const response = await fetch(`/cart/packages/${encodeURIComponent(packageKey)}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            return;
        }

        const data = await response.json();
        renderCart(data);
    }

    async function clearCart() {
        const response = await fetch('/cart/items', {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken(),
            },
            credentials: 'same-origin',
        });

        if (!response.ok) {
            return;
        }

        const data = await response.json();
        renderCart(data);
    }

    document.addEventListener('click', (event) => {
        const button = event.target.closest('.qty-btn, .remove-btn');
        if (!button) {
            return;
        }

        const action = button.getAttribute('data-action');
        const id = parseInt(button.getAttribute('data-id'), 10);
        const currentQty = parseInt(button.getAttribute('data-qty') || '1', 10);

        if (action === 'plus') {
            updateItem(id, currentQty + 1);
        } else if (action === 'minus') {
            if (currentQty === 1) {
                const confirmDelete = () => removeItem(id);
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        title: 'Hapus parameter?',
                        text: 'Jumlah sudah 1. Hapus dari keranjang?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Hapus',
                        cancelButtonText: 'Batal',
                        confirmButtonColor: '#d33'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            confirmDelete();
                        }
                    });
                } else if (confirm('Hapus parameter dari keranjang?')) {
                    confirmDelete();
                }
                return;
            }
            updateItem(id, currentQty - 1);
        } else if (action === 'remove') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Hapus parameter?',
                    text: 'Tindakan ini tidak dapat dibatalkan.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#d33'
                }).then((result) => {
                    if (result.isConfirmed) {
                        removeItem(id);
                    }
                });
            } else if (confirm('Hapus parameter dari keranjang?')) {
                removeItem(id);
            }
        } else if (action === 'remove-package') {
            const packageKey = button.getAttribute('data-package-key') || '';
            if (!packageKey) {
                return;
            }

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'Hapus paket?',
                    text: 'Paket akan dihapus dari keranjang.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#d33'
                }).then((result) => {
                    if (result.isConfirmed) {
                        removePackage(packageKey);
                    }
                });
            } else if (confirm('Hapus paket dari keranjang?')) {
                removePackage(packageKey);
            }
        }
    });

    const clearCartBtn = document.getElementById('clearCartBtn');
    clearCartBtn?.addEventListener('click', () => {
        const doClear = () => clearCart();
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'Kosongkan keranjang?',
                text: 'Semua parameter di keranjang akan dihapus.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Kosongkan',
                cancelButtonText: 'Batal',
                confirmButtonColor: '#d33'
            }).then((result) => {
                if (result.isConfirmed) {
                    doClear();
                }
            });
        } else if (confirm('Kosongkan semua parameter di keranjang?')) {
            doClear();
        }
    });

    fetchCart();

    const sameAsResponsible = document.getElementById('authoritySameAsResponsible');
    const responsibleName = document.getElementById('responsibleName');
    const authorityName = document.getElementById('authorityName');
    const provinceSelect = document.getElementById('companyProvince');
    const citySelect = document.getElementById('companyCity');
    const submitBtn = document.querySelector('.btn-submit');
    const authorityRole = document.getElementById('authorityRole');
    const phoneInput = document.querySelector('[data-only-digits]');
    const phoneWarning = document.querySelector('[data-phone-warning]');
    const signatureModalEl = document.querySelector('[data-signature-modal]');
    const signatureCanvas = document.querySelector('[data-signature-canvas]');
    const signatureClearBtn = document.querySelector('[data-signature-clear]');
    const signatureSubmitBtn = document.querySelector('[data-signature-submit]');
    const signatureModal = signatureModalEl && window.bootstrap ? new window.bootstrap.Modal(signatureModalEl) : null;
    let signatureBackdrop = null;

    let signatureIsEmpty = true;
    let signatureCtx = null;
    let signatureDrawing = false;
    let signaturePayload = null;

    if (sameAsResponsible && responsibleName && authorityName) {
        const syncName = () => {
            if (sameAsResponsible.checked) {
                authorityName.value = responsibleName.value;
                authorityName.setAttribute('readonly', 'readonly');
            } else {
                authorityName.value = '';
                authorityName.removeAttribute('readonly');
            }
        };

        sameAsResponsible.addEventListener('change', syncName);
        responsibleName.addEventListener('input', syncName);
    }

    if (phoneInput) {
        phoneInput.addEventListener('input', () => {
            const sanitized = phoneInput.value.replace(/[^0-9]/g, '');
            const hadInvalid = phoneInput.value !== sanitized;
            if (hadInvalid) {
                phoneInput.value = sanitized;
            }
            if (phoneWarning) {
                phoneWarning.classList.toggle('d-none', !hadInvalid);
            }
        });
    }

    const resetSignatureCanvas = () => {
        if (!signatureCanvas) {
            return;
        }
        const ratio = window.devicePixelRatio || 1;
        const rect = signatureCanvas.getBoundingClientRect();
        signatureCanvas.width = rect.width * ratio;
        signatureCanvas.height = rect.height * ratio;
        signatureCtx = signatureCanvas.getContext('2d');
        signatureCtx.setTransform(1, 0, 0, 1, 0, 0);
        signatureCtx.scale(ratio, ratio);
        signatureCtx.lineWidth = 2;
        signatureCtx.lineCap = 'round';
        signatureCtx.lineJoin = 'round';
        signatureCtx.strokeStyle = '#000000';
        signatureCtx.clearRect(0, 0, rect.width, rect.height);
        signatureIsEmpty = true;
    };

    const getCanvasPoint = (event) => {
        const rect = signatureCanvas.getBoundingClientRect();
        return {
            x: event.clientX - rect.left,
            y: event.clientY - rect.top,
        };
    };

    const startDrawing = (event) => {
        if (!signatureCtx) {
            return;
        }
        signatureDrawing = true;
        const { x, y } = getCanvasPoint(event);
        signatureCtx.beginPath();
        signatureCtx.moveTo(x, y);
        signatureIsEmpty = false;
    };

    const drawLine = (event) => {
        if (!signatureDrawing || !signatureCtx) {
            return;
        }
        const { x, y } = getCanvasPoint(event);
        signatureCtx.lineTo(x, y);
        signatureCtx.stroke();
    };

    const stopDrawing = () => {
        signatureDrawing = false;
        signatureCtx?.closePath();
    };

    if (signatureCanvas) {
        signatureCanvas.addEventListener('pointerdown', (event) => {
            signatureCanvas.setPointerCapture(event.pointerId);
            startDrawing(event);
        });
        signatureCanvas.addEventListener('pointermove', drawLine);
        signatureCanvas.addEventListener('pointerup', stopDrawing);
        signatureCanvas.addEventListener('pointerleave', stopDrawing);
    }

    if (signatureClearBtn) {
        signatureClearBtn.addEventListener('click', () => {
            resetSignatureCanvas();
        });
    }

    const openSignatureModal = () => {
        if (!signatureModalEl) {
            return;
        }
        if (signatureModal) {
            signatureModal.show();
            return;
        }
        signatureModalEl.classList.add('show');
        signatureModalEl.style.display = 'block';
        signatureModalEl.removeAttribute('aria-hidden');
        signatureModalEl.setAttribute('aria-modal', 'true');
        signatureBackdrop = document.createElement('div');
        signatureBackdrop.className = 'modal-backdrop fade show';
        document.body.appendChild(signatureBackdrop);
        resetSignatureCanvas();
    };

    const closeSignatureModal = (resetPayload = false) => {
        if (!signatureModalEl) {
            return;
        }
        if (signatureModal) {
            signatureModal.hide();
            if (resetPayload) {
                signaturePayload = null;
            }
            return;
        }
        signatureModalEl.classList.remove('show');
        signatureModalEl.style.display = 'none';
        signatureModalEl.setAttribute('aria-hidden', 'true');
        signatureModalEl.removeAttribute('aria-modal');
        if (signatureBackdrop) {
            signatureBackdrop.remove();
            signatureBackdrop = null;
        }
        if (resetPayload) {
            signaturePayload = null;
        }
    };

    if (signatureModalEl) {
        signatureModalEl.addEventListener('shown.bs.modal', () => {
            resetSignatureCanvas();
        });
        signatureModalEl.querySelectorAll('[data-bs-dismiss="modal"]').forEach((btn) => {
            btn.addEventListener('click', () => closeSignatureModal(true));
        });
        signatureModalEl.addEventListener('click', (event) => {
            if (event.target === signatureModalEl) {
                closeSignatureModal(true);
            }
        });
    }

    const submitPermohonan = async (payload) => {
        try {
            const formData = new FormData();
            Object.entries(payload || {}).forEach(([key, value]) => {
                formData.append(key, value ?? '');
            });
            const orderProofFile = document.querySelector('[name="order_proof"]')?.files?.[0];
            if (orderProofFile) {
                formData.append('order_proof', orderProofFile);
            }

            const response = await fetch('/permohonan', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                },
                credentials: 'same-origin',
                body: formData,
            });

            const data = await response.json();
            if (!response.ok) {
                let message = data?.message || 'Gagal mengirim permohonan.';
                if (data?.errors) {
                    if (Array.isArray(data.errors.company_email) && data.errors.company_email.length > 0) {
                        message = data.errors.company_email[0];
                    } else {
                        const firstErrorKey = Object.keys(data.errors)[0];
                        if (firstErrorKey && Array.isArray(data.errors[firstErrorKey])) {
                            message = data.errors[firstErrorKey][0];
                        }
                    }
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: message });
                } else {
                    alert(message);
                }
                return;
            }

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil',
                    text: `Permohonan terkirim dengan kode ${data.kode || ''}.`,
                }).then(() => {
                    window.location.href = '/riwayat_pelayanan';
                });
            } else {
                alert('Permohonan terkirim.');
                window.location.href = '/riwayat_pelayanan';
            }
        } catch (error) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Gagal', text: 'Terjadi kesalahan jaringan.' });
            } else {
                alert('Terjadi kesalahan jaringan.');
            }
        }
    };

    if (submitBtn) {
        submitBtn.addEventListener('click', async () => {
            if (!isAuthenticated) {
                window.location.href = loginUrl;
                return;
            }

            const requiredFields = document.querySelectorAll('.request-card [required]');
            const missingFields = [];
            let firstMissingField = null;
            for (const field of requiredFields) {
                const isFileInput = field.type === 'file';
                const isSelect = field.tagName === 'SELECT';
                const value = typeof field.value === 'string' ? field.value.trim() : field.value;
                const isEmpty = isFileInput
                    ? !(field.files && field.files.length > 0)
                    : isSelect
                        ? !value
                        : value === '';

                if (isEmpty) {
                    const labelText = field.closest('[class*="col-"]')
                        ?.querySelector('.form-label')
                        ?.textContent
                        ?.replace('*', '')
                        ?.trim() || 'Field wajib';
                    missingFields.push(labelText);
                    if (!firstMissingField) {
                        firstMissingField = field;
                    }
                }
            }

            if (missingFields.length > 0) {
                const escapedItems = missingFields.map((label) => {
                    const option = document.createElement('option');
                    option.textContent = label;
                    return `<li>${option.innerHTML}</li>`;
                }).join('');
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Cek dan Lengkapi Data',
                        html: `
                            <div class="swal-validation-content">
                                <div class="swal-validation-intro">Bagian berikut masih belum diisi:</div>
                                <ul class="swal-validation-list">${escapedItems}</ul>
                            </div>
                        `,
                        customClass: {
                            container: 'swal-validation-container',
                            popup: 'swal-validation-popup',
                            title: 'swal-validation-title',
                            confirmButton: 'swal-validation-button',
                        },
                        confirmButtonText: 'OK',
                    });
                } else {
                    alert(`Lengkapi field wajib:\n- ${missingFields.join('\n- ')}`);
                }
                firstMissingField?.focus();
                return;
            }

            for (const field of requiredFields) {
                if (typeof field.reportValidity === 'function' && !field.reportValidity()) {
                    return;
                }
            }

            const payload = {
                company_name: document.querySelector('[name="company_name"]')?.value || '',
                responsible_name: document.querySelector('[name="responsible_name"]')?.value || '',
                company_email: document.querySelector('[name="company_email"]')?.value || '',
                company_phone: document.querySelector('[name="company_phone"]')?.value || '',
                company_type: document.querySelector('[name="company_type"]')?.value || '',
                company_province: document.querySelector('[name="company_province"]')?.value || '',
                company_city: document.querySelector('[name="company_city"]')?.value || '',
                company_address: document.querySelector('[name="company_address"]')?.value || '',
                worker_count: document.querySelector('[name="worker_count"]')?.value || '',
                authority_same: sameAsResponsible?.checked ? 1 : 0,
                authority_name: authorityName?.value || '',
                authority_role: authorityRole?.value || '',
            };

            const orderProofFile = document.querySelector('[name="order_proof"]')?.files?.[0];
            if (!payload.worker_count || Number(payload.worker_count) < 1) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Validasi', text: 'Jumlah pekerja wajib diisi minimal 1.' });
                } else {
                    alert('Jumlah pekerja wajib diisi minimal 1.');
                }
                return;
            }
            if (!orderProofFile) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Validasi', text: 'Upload bukti pemesanan wajib diisi.' });
                } else {
                    alert('Upload bukti pemesanan wajib diisi.');
                }
                return;
            }

            signaturePayload = payload;
            openSignatureModal();
        });
    }

    if (signatureSubmitBtn) {
        signatureSubmitBtn.addEventListener('click', async () => {
            if (!signatureCanvas || signatureIsEmpty || !signaturePayload) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({ icon: 'warning', title: 'Tanda tangan', text: 'Silakan isi tanda tangan terlebih dahulu.' });
                } else {
                    alert('Silakan isi tanda tangan terlebih dahulu.');
                }
                return;
            }

            const payload = {
                ...signaturePayload,
                responsible_signature: signatureCanvas.toDataURL('image/png'),
            };
            closeSignatureModal();
            signaturePayload = null;
            await submitPermohonan(payload);
        });
    }

    if (provinceSelect && citySelect) {
        let provinceCityMap = null;

        const resetCityOptions = (disabled = true) => {
            citySelect.innerHTML = '<option value="" selected disabled>Pilih kota/kabupaten</option>';
            citySelect.disabled = disabled;
        };

        resetCityOptions(true);

        provinceSelect.addEventListener('change', () => {
            const province = provinceSelect.value;
            const cities = (provinceCityMap && provinceCityMap[province]) || [];
            resetCityOptions(cities.length === 0);
            cities.forEach((city) => {
                const option = document.createElement('option');
                option.value = city;
                option.textContent = city;
                citySelect.appendChild(option);
            });
        });

        const loadWilayahData = async () => {
            try {
                const response = await fetch('/data/indonesia-provinces-cities.json', {
                    headers: { 'Accept': 'application/json' },
                });
                if (!response.ok) {
                    throw new Error('Gagal memuat data wilayah.');
                }
                provinceCityMap = await response.json();
                const provinces = Object.keys(provinceCityMap).sort((a, b) => a.localeCompare(b, 'id'));
                provinceSelect.innerHTML = '<option value="" selected disabled>Pilih provinsi</option>';
                provinces.forEach((province) => {
                    const option = document.createElement('option');
                    option.value = province;
                    option.textContent = province;
                    provinceSelect.appendChild(option);
                });
                provinceSelect.disabled = false;
            } catch (error) {
                provinceSelect.disabled = true;
                resetCityOptions(true);
            }
        };

        loadWilayahData();
    }
</script>

@endsection
