<div class="modal fade" id="{{ $modalId }}" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ $formAction }}" method="POST">
                @csrf
                <input type="hidden" name="produk_id" value="{{ $produk->id }}">
                <div class="modal-body pt-0">
                    <div class="row g-4">
                        {{-- Kolom Gambar Dinamis --}}
                        <div class="col-md-5">
                            @if($produk->diskon_persen > 0)
                                <span class="badge bg-danger mb-2">Hemat {{ $produk->diskon_persen }}%</span>
                            @endif
                            @if($produk->gambar)
                                <img id="{{ $idPrefix }}-img-preview"
                                     src="{{ asset('storage/' . $produk->gambar) }}"
                                     class="w-100 rounded-3"
                                     style="aspect-ratio: 1 / 1; object-fit: cover;"
                                     alt="{{ $produk->nama_produk }}">
                            @else
                                <img id="{{ $idPrefix }}-img-preview" src="" class="w-100 rounded-3 d-none" style="aspect-ratio: 1 / 1; object-fit: cover;" alt="{{ $produk->nama_produk }}">
                                <div id="{{ $idPrefix }}-img-kosong" class="w-100 bg-light rounded-3 d-flex align-items-center justify-content-center text-muted" style="aspect-ratio: 1 / 1;">
                                    No Image
                                </div>
                            @endif
                        </div>

                        {{-- Kolom Detail --}}
                        <div class="col-md-7">
                            <h4 class="fw-bold mb-1">{{ $produk->nama_produk }}</h4>
                            @if($produk->material)
                                <p class="text-muted small mb-2">Bahan: {{ $produk->material }}</p>
                            @endif

                            <div class="mb-3">
                                @if($produk->diskon_persen > 0)
                                    <span class="fs-4 fw-bold text-danger">Rp {{ number_format($produk->harga_promo, 0, ',', '.') }}</span>
                                    <span class="text-muted text-decoration-line-through ms-2">Rp {{ number_format($produk->harga_satuan, 0, ',', '.') }}</span>
                                @else
                                    <span class="fs-4 fw-bold text-primary">Rp {{ number_format($produk->harga_satuan, 0, ',', '.') }}</span>
                                @endif
                            </div>

                            {{-- Pilih Warna (cuma warna yang benar-benar tersedia untuk produk ini) --}}
                            <h6 class="fw-semibold mb-2">Pilih Warna</h6>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                @foreach($produk->warnaTersedia() as $i => $warna)
                                    @php
                                        $imgWarna = $produk->gambarUntukWarna($warna);
                                        $imgWarnaUrl = $imgWarna ? asset('storage/' . $imgWarna) : '';
                                    @endphp
                                    <input type="radio" class="btn-check radio-warna-{{ $idPrefix }}" name="warna"
                                           id="{{ $idPrefix }}-warna-{{ $i }}" value="{{ $warna }}"
                                           data-img="{{ $imgWarnaUrl }}"
                                           {{ $i === 0 ? 'checked' : '' }} required>
                                    <label class="btn btn-outline-dark rounded-pill px-3" for="{{ $idPrefix }}-warna-{{ $i }}">
                                        {{ $warna }}
                                    </label>
                                @endforeach
                            </div>

                            {{-- Pilih Ukuran --}}
                            <h6 class="fw-semibold mb-2">Pilih Ukuran</h6>
                            <div class="d-flex flex-wrap gap-2 mb-3">
                                @foreach(\App\Models\Produk::UKURAN_OPTIONS as $i => $ukuran)
                                    <input type="radio" class="btn-check radio-ukuran-{{ $idPrefix }}" name="ukuran"
                                           id="{{ $idPrefix }}-ukuran-{{ $i }}" value="{{ $ukuran }}"
                                           {{ $ukuran === 'M' ? 'checked' : '' }} required>
                                    <label class="btn btn-outline-dark rounded-pill px-3" for="{{ $idPrefix }}-ukuran-{{ $i }}">
                                        {{ $ukuran }}
                                    </label>
                                @endforeach
                            </div>

                            {{-- Jumlah & Info Stok Varian --}}
                            <h6 class="fw-semibold mb-2">Jumlah</h6>
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <button type="button" class="btn btn-outline-secondary" onclick="ubahJumlahModal('{{ $idPrefix }}-qty', -1)">-</button>
                                <input type="number" name="kuantitas" id="{{ $idPrefix }}-qty" class="form-control text-center" style="width: 70px;" value="1" min="1" required>
                                <button type="button" class="btn btn-outline-secondary" onclick="ubahJumlahModal('{{ $idPrefix }}-qty', 1)">+</button>
                                <span class="ms-2 small text-muted">Stok Varian: <strong id="{{ $idPrefix }}-stok-text" class="text-dark">0</strong></span>
                            </div>

                            @if($showPembayaran)
                                <h6 class="fw-semibold mb-2">Metode Pembayaran</h6>
                                <div class="mb-3">
                                    <select name="metode_pembayaran" class="form-select" required onchange="toggleEwalletModal(this, '{{ $idPrefix }}')">
                                        <option value="Transfer Bank">Transfer Bank</option>
                                        <option value="E-Wallet">E-Wallet</option>
                                    </select>
                                </div>
                                <div class="mb-3 ewallet-provider-field" id="{{ $idPrefix }}-ewallet-field" style="display:none;">
                                    <label class="form-label">Pilih E-Wallet</label>
                                    <select name="ewallet_provider" class="form-select">
                                        @foreach(\App\Models\Pelanggan::EWALLET_OPTIONS as $ewallet)
                                            <option value="{{ $ewallet }}" {{ $ewalletFavorit === $ewallet ? 'selected' : '' }}>{{ $ewallet }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            <div class="d-flex gap-2 mt-3">
                                <button type="button" class="btn btn-outline-secondary flex-fill" data-bs-dismiss="modal">Batal</button>
                                <button type="submit" id="{{ $idPrefix }}-submit-btn" class="btn {{ $submitClass }} flex-fill">{{ $submitLabel }}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function() {
    // Data varian produk dipassing ke JS
    const variansData = @json($produk->varians);
    const prefix = "{{ $idPrefix }}";

    function updateVarianInfo() {
        const warnaSelected = document.querySelector(`.radio-warna-${prefix}:checked`)?.value;
        const ukuranSelected = document.querySelector(`.radio-ukuran-${prefix}:checked`)?.value;
        const imgRadio = document.querySelector(`.radio-warna-${prefix}:checked`)?.dataset.img;

        // 1. Ganti Foto Produk sesuai warna yang diklik
        const imgEl = document.getElementById(`${prefix}-img-preview`);
        const imgKosongEl = document.getElementById(`${prefix}-img-kosong`);
        if (imgEl) {
            if (imgRadio) {
                imgEl.src = imgRadio;
                imgEl.classList.remove('d-none');
                if (imgKosongEl) imgKosongEl.classList.add('d-none');
            } else {
                imgEl.classList.add('d-none');
                if (imgKosongEl) imgKosongEl.classList.remove('d-none');
            }
        }

        // 2. Cari stok spesifik varian ini
        const v = variansData.find(item => item.warna === warnaSelected && item.ukuran === ukuranSelected);
        const stok = v ? v.stok : 0;

        const stokText = document.getElementById(`${prefix}-stok-text`);
        const submitBtn = document.getElementById(`${prefix}-submit-btn`);
        const qtyInput = document.getElementById(`${prefix}-qty`);

        stokText.textContent = stok;
        qtyInput.max = stok;

        if (stok <= 0) {
            stokText.className = "text-danger fw-bold";
            stokText.textContent = "Habis";
            submitBtn.disabled = true;
        } else {
            stokText.className = "text-success fw-bold";
            submitBtn.disabled = false;
        }
    }

    document.querySelectorAll(`.radio-warna-${prefix}, .radio-ukuran-${prefix}`).forEach(el => {
        el.addEventListener('change', updateVarianInfo);
    });

    // Jalankan sekali saat modal pertama kali dirender
    updateVarianInfo();
})();
</script>