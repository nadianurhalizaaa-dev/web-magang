@extends('layouts.app')

@section('title', 'Komentar & Masukan Pengguna - Admin Panel')

@section('content')
<div class="page-header-box">
    <div>
        <h1 class="page-title">Komentar & Masukan Pengguna</h1>
        <p class="page-subtitle">Kelola ulasan, testimoni, rating bintang, dan masukan pengguna aplikasi magang.</p>
    </div>
    <div>
        <button class="btn btn-primary" onclick="openAddKomentarModal()">
            <i class="fa-solid fa-comment-medical"></i> Tambah Komentar Manual
        </button>
    </div>
</div>


<!-- Search & Filter Card -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1rem 1.5rem;">
        <form action="{{ route('komentar.index') }}" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
            <div style="flex-grow: 1; min-width: 240px;">
                <input type="text" name="search" class="form-control" placeholder="Cari nama pengguna, email, atau isi komentar..." value="{{ $search }}">
            </div>
            
            <div style="width: 180px;">
                <select name="status" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Semua Status --</option>
                    <option value="published" {{ $status == 'published' ? 'selected' : '' }}>Dipublikasi</option>
                    <option value="pending" {{ $status == 'pending' ? 'selected' : '' }}>Pending / Moderasi</option>
                </select>
            </div>

            <button type="submit" class="btn btn-secondary">
                <i class="fa-solid fa-magnifying-glass"></i> Cari
            </button>

            @if($search || $status)
                <a href="{{ route('komentar.index') }}" class="btn btn-danger btn-sm">
                    <i class="fa-solid fa-xmark"></i> Reset
                </a>
            @endif
        </form>
    </div>
</div>

<!-- Data Table Card -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-comments" style="color: var(--primary);"></i>
            Daftar Komentar Pengguna ({{ $komentarList->total() }} Data)
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Pengguna / User</th>
                    <th>Rating Bintang</th>
                    <th>Isi Komentar / Masukan</th>
                    <th>Status</th>
                    <th>Tanggal Input</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($komentarList as $index => $k)
                    <tr>
                        <td>{{ $komentarList->firstItem() + $index }}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.6rem;">
                                <div style="width: 32px; height: 32px; border-radius: 50%; background: linear-gradient(135deg, #10b981, #059669); color: white; display: flex; align-items: center; justify-content: center; font-size: 0.8rem; font-weight: 700; flex-shrink: 0;">
                                    {{ strtoupper(substr($k->user->name ?? $k->nama_pengguna ?? 'P', 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: var(--text-title); font-size: 0.9rem;">
                                        {{ $k->user->name ?? $k->nama_pengguna }}
                                        @if($k->user)
                                            <span style="font-size: 0.7rem; background: #e0e7ff; color: #3730a3; padding: 2px 6px; border-radius: 4px; margin-left: 4px;">Terverifikasi</span>
                                        @endif
                                    </div>
                                    <div style="font-size: 0.78rem; color: var(--text-muted);">{{ $k->user->email ?? $k->email ?? 'Tidak ada email' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <div style="color: #f59e0b; font-size: 0.85rem; display: flex; gap: 0.15rem;">
                                @for($i = 1; $i <= 5; $i++)
                                    @if($i <= $k->rating)
                                        <i class="fa-solid fa-star"></i>
                                    @else
                                        <i class="fa-regular fa-star" style="color: #cbd5e1;"></i>
                                    @endif
                                @endfor
                                <span style="font-weight: 700; color: var(--text-title); margin-left: 0.3rem;">({{ $k->rating }})</span>
                            </div>
                        </td>
                        <td style="max-width: 320px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 0.85rem; color: #334155;">
                            {{ $k->komentar }}
                        </td>
                        <td>
                            @if($k->status == 'published')
                                <span class="badge badge-emerald"><i class="fa-solid fa-circle-check"></i> Published</span>
                            @else
                                <span class="badge badge-amber"><i class="fa-solid fa-clock"></i> Pending</span>
                            @endif
                        </td>
                        <td>{{ $k->created_at ? $k->created_at->format('d M Y, H:i') : '-' }}</td>
                        <td style="text-align: right; white-space: nowrap;">
                            <form action="{{ route('komentar.toggle', $k->id) }}" method="POST" style="display: inline-block;">
                                @csrf
                                <button type="submit" class="btn btn-secondary btn-sm" title="Ubah status publish/pending">
                                    @if($k->status == 'published')
                                        <i class="fa-solid fa-eye-slash" style="color: #d97706;"></i> Unpublish
                                    @else
                                        <i class="fa-solid fa-check" style="color: #059669;"></i> Publish
                                    @endif
                                </button>
                            </form>
                            <button type="button" class="btn btn-secondary btn-sm" data-item="{{ json_encode($k) }}" onclick="handleDetailKomentarClick(this)" title="Lihat Komentar">
                                <i class="fa-solid fa-eye"></i> Detail
                            </button>
                            <form action="{{ route('komentar.delete', $k->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus komentar ini?');">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fa-solid fa-trash"></i> Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                            Belum ada komentar pengguna terdaftar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($komentarList->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end;">
            {{ $komentarList->links() }}
        </div>
    @endif
</div>

<!-- Modal Form: Tambah Komentar -->
<div class="modal" id="addKomentarModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <div style="font-weight: 700; font-size: 1.1rem; color: var(--text-title);">
                <i class="fa-solid fa-comment-medical" style="color: var(--primary);"></i> Tambah Komentar Pengguna
            </div>
            <button type="button" onclick="closeModal('addKomentarModal')" style="background: none; border: none; font-size: 1.25rem; color: var(--text-muted); cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form action="{{ route('komentar.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Pengguna</label>
                    <input type="text" name="nama_pengguna" class="form-control" placeholder="Contoh: Rina Amalia" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email (Opsional)</label>
                        <input type="email" name="email" class="form-control" placeholder="rina@example.com">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Rating Bintang (1-5)</label>
                        <select name="rating" class="form-control" required>
                            <option value="5">⭐⭐⭐⭐⭐ (5 Bintang - Sangat Bagus)</option>
                            <option value="4">⭐⭐⭐⭐ (4 Bintang - Bagus)</option>
                            <option value="3">⭐⭐⭐ (3 Bintang - Cukup)</option>
                            <option value="2">⭐⭐ (2 Bintang - Kurang)</option>
                            <option value="1">⭐ (1 Bintang - Sangat Kurang)</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Isi Komentar & Masukan Aplikasi</label>
                    <textarea name="komentar" class="form-control" rows="4" placeholder="Tuliskan masukan atau ulasan pengguna..." required></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Status Publikasi</label>
                    <select name="status" class="form-control" required>
                        <option value="published">Published (Tampil di API)</option>
                        <option value="pending">Pending Moderasi</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addKomentarModal')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Simpan Komentar</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal View: Detail Komentar -->
<div class="modal" id="detailKomentarModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <div style="font-weight: 700; font-size: 1.1rem; color: var(--text-title);">
                <i class="fa-solid fa-comment-dots" style="color: var(--primary);"></i> Detail Komentar & Masukan
            </div>
            <button type="button" onclick="closeModal('detailKomentarModal')" style="background: none; border: none; font-size: 1.25rem; color: var(--text-muted); cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <h3 id="detail_nama_pengguna" style="font-size: 1.2rem; font-weight: 700; color: var(--text-title); margin-bottom: 0.2rem;"></h3>
            <div style="font-size: 0.85rem; color: var(--text-muted); margin-bottom: 0.75rem;" id="detail_email_pengguna"></div>
            <div style="margin-bottom: 1rem; color: #f59e0b; font-size: 1.1rem;" id="detail_rating_bintang"></div>
            <div style="background-color: #f8fafc; padding: 1.25rem; border-radius: 12px; border: 1px solid var(--border-color); font-size: 0.95rem; line-height: 1.6; color: var(--text-body); white-space: pre-line;" id="detail_komentar_teks"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('detailKomentarModal')">Tutup</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openAddKomentarModal() {
        document.getElementById('addKomentarModal').style.display = 'flex';
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
        }
    }

    function handleDetailKomentarClick(el) {
        try {
            const item = JSON.parse(el.getAttribute('data-item'));
            openDetailKomentarModal(item);
        } catch (e) {
            console.error('Error parsing komentar item:', e);
        }
    }

    function openDetailKomentarModal(item) {
        const name = item.user ? item.user.name : item.nama_pengguna;
        const email = item.user ? item.user.email : (item.email ? item.email : 'Tidak ada email');

        document.getElementById('detail_nama_pengguna').innerText = name;
        document.getElementById('detail_email_pengguna').innerText = email;
        
        let stars = '';
        for (let i = 1; i <= 5; i++) {
            if (i <= item.rating) {
                stars += '<i class="fa-solid fa-star"></i> ';
            } else {
                stars += '<i class="fa-regular fa-star" style="color: #cbd5e1;"></i> ';
            }
        }
        stars += `<span style="font-size: 0.9rem; color: var(--text-title); font-weight: 700;">(${item.rating} / 5 Bintang)</span>`;
        document.getElementById('detail_rating_bintang').innerHTML = stars;
        
        document.getElementById('detail_komentar_teks').innerText = item.komentar;

        document.getElementById('detailKomentarModal').style.display = 'flex';
    }
</script>
@endpush
