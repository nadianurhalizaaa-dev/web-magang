@extends('layouts.app')

@section('title', 'Aktivitas Magang - Admin Panel')

@section('content')
<div class="page-header-box">
    <div>
        <h1 class="page-title">Aktivitas Magang</h1>
        <p class="page-subtitle">Kelola jurnal kegiatan, dokumentasi foto, agenda magang, dan ulasan komentar pengguna.</p>
    </div>
    <div>
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fa-solid fa-plus"></i> Input Aktivitas Baru
        </button>
    </div>
</div>

<!-- Search and Filter Bar Card -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1rem 1.5rem;">
        <form action="{{ route('aktivitas.index') }}" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
            <div style="flex-grow: 1; min-width: 240px; position: relative;">
                <input type="text" name="search" class="form-control" placeholder="Cari judul, deskripsi, atau lokasi aktivitas..." value="{{ $search }}">
            </div>
            
            <div style="width: 200px;">
                <select name="kategori" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Semua Kategori --</option>
                    @foreach($kategoriList as $kat)
                        <option value="{{ $kat }}" {{ $kategori == $kat ? 'selected' : '' }}>{{ $kat }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-secondary">
                <i class="fa-solid fa-magnifying-glass"></i> Cari
            </button>

            @if($search || $kategori)
                <a href="{{ route('aktivitas.index') }}" class="btn btn-danger btn-sm">
                    <i class="fa-solid fa-xmark"></i> Reset Filter
                </a>
            @endif
        </form>
    </div>
</div>

<!-- Data Table Card -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-calendar-check" style="color: var(--primary);"></i>
            Daftar Aktivitas Magang ({{ $aktivitasList->total() }} Kegiatan)
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Foto</th>
                    <th>Judul Aktivitas</th>
                    <th>Di-Input Oleh (Admin)</th>
                    <th>Tanggal Kegiatan</th>
                    <th>Kategori & Lokasi</th>
                    <th>Komentar Pengguna</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($aktivitasList as $index => $item)
                    <tr>
                        <td>{{ $aktivitasList->firstItem() + $index }}</td>
                        <td>
                            @if($item->foto)
                                <img src="{{ asset($item->foto) }}" alt="Foto Kegiatan" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color); cursor: pointer;" data-item="{{ json_encode($item) }}" onclick="handleDetailClick(this)">
                            @else
                                <div style="width: 50px; height: 50px; border-radius: 8px; background: #f1f5f9; display: flex; align-items: center; justify-content: center; color: #94a3b8; font-size: 1.1rem; border: 1px dashed #cbd5e1;">
                                    <i class="fa-regular fa-image"></i>
                                </div>
                            @endif
                        </td>
                        <td>
                            <div style="font-weight: 700; color: var(--text-title); font-size: 0.95rem;">{{ $item->judul }}</div>
                            <div style="font-size: 0.8rem; color: #64748b; max-width: 240px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                {{ $item->deskripsi }}
                            </div>
                        </td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <div style="width: 28px; height: 28px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #3b82f6); color: white; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700;">
                                    {{ strtoupper(substr($item->user->name ?? 'A', 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight: 600; font-size: 0.85rem; color: var(--text-title);">{{ $item->user->name ?? 'Admin Portal' }}</div>
                                    <div style="font-size: 0.72rem; color: #94a3b8;">{{ $item->user->email ?? '-' }}</div>
                                </div>
                            </div>
                        </td>
                        <td>
                            <span class="badge badge-indigo">
                                <i class="fa-regular fa-calendar" style="margin-right: 4px;"></i> {{ \Carbon\Carbon::parse($item->tanggal)->translatedFormat('d M Y') }}
                            </span>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--primary); font-size: 0.85rem;">{{ $item->kategori ?? 'Umum' }}</div>
                            <div style="font-size: 0.75rem; color: #64748b;">
                                <i class="fa-solid fa-location-dot" style="font-size: 0.7rem; color: #ef4444;"></i> {{ $item->lokasi ?? 'Kantor Utama' }}
                            </div>
                        </td>
                        <td>
                            <button type="button" class="btn btn-secondary btn-sm" data-item="{{ json_encode($item) }}" onclick="handleCommentsClick(this)" style="border-radius: 20px; font-size: 0.8rem; padding: 0.25rem 0.75rem;">
                                <i class="fa-solid fa-comments" style="color: #6366f1;"></i> {{ $item->komentar->count() }} Komentar
                            </button>
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <button type="button" class="btn btn-secondary btn-sm" data-item="{{ json_encode($item) }}" onclick="handleDetailClick(this)" title="Lihat Detail Aktivitas">
                                <i class="fa-solid fa-eye"></i> Detail
                            </button>
                            <button type="button" class="btn btn-edit btn-sm" data-item="{{ json_encode($item) }}" onclick="handleEditClick(this)">
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </button>
                            <form action="{{ route('aktivitas.delete', $item->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus aktivitas magang ini?');">
                                @csrf
                                <button type="submit" class="btn btn-danger btn-sm">
                                    <i class="fa-solid fa-trash"></i> Hapus
                                </button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 2.5rem; color: var(--text-muted);">
                            Belum ada aktivitas magang yang di-input oleh Admin.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($aktivitasList->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end;">
            {{ $aktivitasList->links() }}
        </div>
    @endif
</div>

<!-- Modal Form: Tambah Aktivitas -->
<div class="modal" id="addModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <div style="font-weight: 700; font-size: 1.1rem; color: var(--text-title);">
                <i class="fa-solid fa-calendar-plus" style="color: var(--primary);"></i> Input Aktivitas Magang Baru
            </div>
            <button type="button" onclick="closeModal('addModal')" style="background: none; border: none; font-size: 1.25rem; color: var(--text-muted); cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form action="{{ route('aktivitas.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Judul Aktivitas / Kegiatan</label>
                    <input type="text" name="judul" class="form-control" placeholder="Contoh: Workshop Pengenalan Framework Laravel & React" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tanggal Kegiatan</label>
                        <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kategori</label>
                        <input type="text" name="kategori" class="form-control" placeholder="Contoh: Workshop / Monitoring / Orientasi">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Lokasi Kegiatan</label>
                    <input type="text" name="lokasi" class="form-control" placeholder="Contoh: Ruang Rapat Diskominfotik / Daring Via Zoom">
                </div>
                <div class="form-group">
                    <label class="form-label">Foto / Dokumentasi Kegiatan (Opsional)</label>
                    <input type="file" name="foto" class="form-control" accept="image/*">
                    <small style="font-size: 0.75rem; color: #64748b; margin-top: 2px; display: block;">Format gambar: JPG, PNG, WEBP, GIF (Maksimal 5MB)</small>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi Rinci Aktivitas</label>
                    <textarea name="deskripsi" class="form-control" rows="4" placeholder="Jelaskan secara detail agenda, sasaran, atau hasil kegiatan magang yang di-input admin..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Simpan Aktivitas</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Form: Edit Aktivitas -->
<div class="modal" id="editModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <div style="font-weight: 700; font-size: 1.1rem; color: var(--text-title);">
                <i class="fa-solid fa-pen-to-square" style="color: var(--primary);"></i> Edit Aktivitas Magang
            </div>
            <button type="button" onclick="closeModal('editModal')" style="background: none; border: none; font-size: 1.25rem; color: var(--text-muted); cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="editForm" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Judul Aktivitas</label>
                    <input type="text" name="judul" id="edit_judul" class="form-control" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Tanggal Kegiatan</label>
                        <input type="date" name="tanggal" id="edit_tanggal" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Kategori</label>
                        <input type="text" name="kategori" id="edit_kategori" class="form-control">
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Lokasi Kegiatan</label>
                    <input type="text" name="lokasi" id="edit_lokasi" class="form-control">
                </div>
                <div class="form-group">
                    <label class="form-label">Foto / Dokumentasi Kegiatan (Kosongkan jika tidak diubah)</label>
                    <input type="file" name="foto" class="form-control" accept="image/*">
                    <div id="edit_foto_preview" style="margin-top: 0.5rem;"></div>
                </div>
                <div class="form-group">
                    <label class="form-label">Deskripsi Rinci Aktivitas</label>
                    <textarea name="deskripsi" id="edit_deskripsi" class="form-control" rows="4" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Perbarui Aktivitas</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal View: Detail Aktivitas -->
<div class="modal" id="detailModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <div style="font-weight: 700; font-size: 1.1rem; color: var(--text-title);">
                <i class="fa-solid fa-circle-info" style="color: var(--primary);"></i> Detail Aktivitas Magang
            </div>
            <button type="button" onclick="closeModal('detailModal')" style="background: none; border: none; font-size: 1.25rem; color: var(--text-muted); cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div id="detail_foto_container" style="margin-bottom: 1rem; display: none;">
                <img id="detail_foto" src="" style="width: 100%; max-height: 280px; object-fit: cover; border-radius: 12px; border: 1px solid var(--border-color);">
            </div>
            <h3 id="detail_judul" style="font-size: 1.25rem; font-weight: 700; color: var(--text-title); margin-bottom: 0.4rem;"></h3>
            <div style="display: flex; gap: 0.75rem; align-items: center; margin-bottom: 1rem; flex-wrap: wrap;">
                <span class="badge badge-indigo" id="detail_tanggal"></span>
                <span class="badge" style="background: #e0e7ff; color: #3730a3;" id="detail_kategori"></span>
                <span style="font-size: 0.85rem; color: #64748b;" id="detail_lokasi"></span>
            </div>
            <div style="font-size: 0.8rem; color: #64748b; margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border-color);" id="detail_admin"></div>
            <div style="background-color: #f8fafc; padding: 1.25rem; border-radius: 12px; border: 1px solid var(--border-color); font-size: 0.95rem; line-height: 1.6; color: var(--text-body); white-space: pre-line;" id="detail_deskripsi"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('detailModal')">Tutup</button>
        </div>
    </div>
</div>

<!-- Modal View & Kelola: Komentar Pengguna -->
<div class="modal" id="commentsModal">
    <div class="modal-dialog" style="max-width: 650px;">
        <div class="modal-header">
            <div style="font-weight: 700; font-size: 1.1rem; color: var(--text-title);">
                <i class="fa-solid fa-comments" style="color: var(--primary);"></i> Komentar Pengguna pada Aktivitas
            </div>
            <button type="button" onclick="closeModal('commentsModal')" style="background: none; border: none; font-size: 1.25rem; color: var(--text-muted); cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <div style="margin-bottom: 1rem; padding-bottom: 0.75rem; border-bottom: 1px solid var(--border-color);">
                <div style="font-weight: 700; font-size: 1rem; color: var(--text-title);" id="comments_aktivitas_judul"></div>
                <div style="font-size: 0.8rem; color: #64748b;" id="comments_aktivitas_sub"></div>
            </div>

            <div id="commentsContainer" style="display: flex; flex-direction: column; gap: 0.85rem; max-height: 380px; overflow-y: auto; padding-right: 0.25rem;">
                <!-- Comments rendered via JS -->
            </div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('commentsModal')">Tutup</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openAddModal() {
        document.getElementById('addModal').style.display = 'flex';
    }

    function closeModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.style.display = 'none';
        }
    }

    function getItemFromElement(el) {
        try {
            return JSON.parse(el.getAttribute('data-item'));
        } catch (e) {
            console.error('Error parsing item data:', e);
            return null;
        }
    }

    function handleCommentsClick(el) {
        const item = getItemFromElement(el);
        if (item) openCommentsModal(item);
    }

    function handleDetailClick(el) {
        const item = getItemFromElement(el);
        if (item) openDetailModal(item);
    }

    function handleEditClick(el) {
        const item = getItemFromElement(el);
        if (item) openEditModal(item);
    }

    function openEditModal(item) {
        document.getElementById('editForm').action = '/aktivitas/' + item.id + '/update';
        document.getElementById('edit_judul').value = item.judul || '';
        document.getElementById('edit_tanggal').value = item.tanggal || '';
        document.getElementById('edit_kategori').value = item.kategori || '';
        document.getElementById('edit_lokasi').value = item.lokasi || '';
        document.getElementById('edit_deskripsi').value = item.deskripsi || '';
        
        const previewContainer = document.getElementById('edit_foto_preview');
        if (item.foto) {
            previewContainer.innerHTML = `
                <div style="display: flex; align-items: center; gap: 0.75rem; background: #f8fafc; padding: 0.5rem; border-radius: 8px; border: 1px solid #e2e8f0;">
                    <img src="/${item.foto}" style="width: 45px; height: 45px; object-fit: cover; border-radius: 6px;">
                    <span style="font-size: 0.8rem; color: #64748b;">Foto saat ini tersimpan</span>
                </div>
            `;
        } else {
            previewContainer.innerHTML = '';
        }

        document.getElementById('editModal').style.display = 'flex';
    }

    function openDetailModal(item) {
        const fotoContainer = document.getElementById('detail_foto_container');
        const fotoImg = document.getElementById('detail_foto');

        if (item.foto) {
            fotoImg.src = '/' + item.foto;
            fotoContainer.style.display = 'block';
        } else {
            fotoContainer.style.display = 'none';
        }

        document.getElementById('detail_judul').innerText = item.judul || '';
        document.getElementById('detail_tanggal').innerText = item.tanggal || '';
        document.getElementById('detail_kategori').innerText = item.kategori ? item.kategori : 'Umum';
        document.getElementById('detail_lokasi').innerText = '📍 ' + (item.lokasi ? item.lokasi : 'Kantor Utama');
        document.getElementById('detail_admin').innerText = 'Di-Input oleh: ' + (item.user ? item.user.name : 'Admin Portal') + ' (' + (item.user ? item.user.email : '-') + ')';
        document.getElementById('detail_deskripsi').innerText = item.deskripsi || '';

        document.getElementById('detailModal').style.display = 'flex';
    }

    function openCommentsModal(item) {
        document.getElementById('comments_aktivitas_judul').innerText = item.judul || '';
        document.getElementById('comments_aktivitas_sub').innerText = 'Tanggal: ' + (item.tanggal || '') + ' | Oleh: ' + (item.user ? item.user.name : 'Admin');

        const container = document.getElementById('commentsContainer');
        container.innerHTML = '';

        if (!item.komentar || item.komentar.length === 0) {
            container.innerHTML = `
                <div style="text-align: center; padding: 2rem; color: #94a3b8; font-size: 0.9rem;">
                    <i class="fa-regular fa-comment-dots" style="font-size: 2rem; margin-bottom: 0.5rem; display: block;"></i>
                    Belum ada komentar dari pengguna pada aktivitas magang ini.
                </div>
            `;
        } else {
            const csrfToken = document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '';

            item.komentar.forEach(km => {
                const commentDate = new Date(km.created_at).toLocaleString('id-ID', {
                    day: 'numeric', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit'
                });

                const div = document.createElement('div');
                div.style.cssText = 'background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 10px; padding: 0.85rem 1rem; position: relative;';
                
                div.innerHTML = `
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 0.4rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <div style="width: 26px; height: 26px; border-radius: 50%; background: #4f46e5; color: white; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700;">
                                ${km.user && km.user.name ? km.user.name.charAt(0).toUpperCase() : 'U'}
                            </div>
                            <div>
                                <div style="font-weight: 700; font-size: 0.85rem; color: #1e293b;">${km.user ? km.user.name : 'Pengguna App'}</div>
                                <div style="font-size: 0.72rem; color: #94a3b8;">${commentDate}</div>
                            </div>
                        </div>
                        <form action="/aktivitas/komentar/${km.id}/delete" method="POST" onsubmit="return confirm('Hapus komentar pengguna ini?');">
                            <input type="hidden" name="_token" value="${csrfToken}">
                            <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 0.85rem;" title="Hapus Komentar">
                                <i class="fa-solid fa-trash-can"></i>
                            </button>
                        </form>
                    </div>
                    <div style="font-size: 0.9rem; color: #334155; line-height: 1.4; padding-left: 2.1rem;">
                        ${km.komentar}
                    </div>
                `;
                container.appendChild(div);
            });
        }

        document.getElementById('commentsModal').style.display = 'flex';
    }
</script>
@endpush
