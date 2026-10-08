@extends('layouts.app')

@section('title', 'Data Alumni Magang - Admin Panel')

@section('content')
<div class="page-header-box">
    <div>
        <h1 class="page-title">Data Alumni Magang</h1>
        <p class="page-subtitle">Kelola informasi peserta, instansi mitra, jurusan, dan testimoni lulusan magang.</p>
    </div>
    <div>
        <button class="btn btn-primary" onclick="openAddModal()">
            <i class="fa-solid fa-user-plus"></i> Tambah Alumni Baru
        </button>
    </div>
</div>

<!-- Search and Filter Bar Card -->
<div class="card" style="margin-bottom: 1.5rem;">
    <div class="card-body" style="padding: 1rem 1.5rem;">
        <form action="{{ route('alumni.index') }}" method="GET" style="display: flex; gap: 1rem; flex-wrap: wrap; align-items: center;">
            <div style="flex-grow: 1; min-width: 240px; position: relative;">
                <input type="text" name="search" class="form-control" placeholder="Cari nama, instansi, atau jurusan..." value="{{ $search }}">
            </div>
            
            <div style="width: 200px;">
                <select name="instansi" class="form-control" onchange="this.form.submit()">
                    <option value="">-- Semua Instansi --</option>
                    @foreach($instansiList as $ins)
                        <option value="{{ $ins }}" {{ $instansi == $ins ? 'selected' : '' }}>{{ $ins }}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-secondary">
                <i class="fa-solid fa-magnifying-glass"></i> Cari
            </button>

            @if($search || $instansi)
                <a href="{{ route('alumni.index') }}" class="btn btn-danger btn-sm">
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
            <i class="fa-solid fa-users" style="color: var(--primary);"></i>
            Daftar Alumni Magang ({{ $alumniList->total() }} Data)
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Alumni</th>
                    <th>Asal Instansi</th>
                    <th>Jurusan</th>
                    <th>Periode Magang</th>
                    <th>Kesan & Pesan</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($alumniList as $index => $item)
                    <tr>
                        <td>{{ $alumniList->firstItem() + $index }}</td>
                        <td>
                            <div style="font-weight: 600; color: var(--text-title);">{{ $item->nama }}</div>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--primary);">{{ $item->asal_instansi }}</div>
                        </td>
                        <td>{{ $item->jurusan }}</td>
                        <td>
                            <span class="badge badge-indigo">{{ $item->periode_magang }}</span>
                        </td>
                        <td style="max-width: 280px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; font-size: 0.85rem; color: #475569;">
                            {{ $item->kesan_pesan }}
                        </td>
                        <td style="text-align: right; white-space: nowrap;">
                            <button class="btn btn-secondary btn-sm" onclick='openDetailModal(@json($item))' title="Lihat Testimoni">
                                <i class="fa-solid fa-eye"></i> Detail
                            </button>
                            <button class="btn btn-edit btn-sm" onclick='openEditModal(@json($item))'>
                                <i class="fa-solid fa-pen-to-square"></i> Edit
                            </button>
                            <form action="{{ route('alumni.delete', $item->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data alumni ini?');">
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
                            Tidak ada data alumni magang yang ditemukan.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($alumniList->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end;">
            {{ $alumniList->links() }}
        </div>
    @endif
</div>

<!-- Modal Form: Tambah Alumni -->
<div class="modal" id="addModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <div style="font-weight: 700; font-size: 1.1rem; color: var(--text-title);">
                <i class="fa-solid fa-user-plus" style="color: var(--primary);"></i> Tambah Alumni Magang Baru
            </div>
            <button onclick="closeModal('addModal')" style="background: none; border: none; font-size: 1.25rem; color: var(--text-muted); cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form action="{{ route('alumni.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Alumni</label>
                    <input type="text" name="nama" class="form-control" placeholder="Contoh: Budi Santoso" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Asal Instansi / Universitas</label>
                        <input type="text" name="asal_instansi" class="form-control" placeholder="Contoh: Universitas Indonesia" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jurusan</label>
                        <input type="text" name="jurusan" class="form-control" placeholder="Contoh: Teknik Informatika" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Periode Magang</label>
                    <input type="text" name="periode_magang" class="form-control" placeholder="Contoh: Batch 1 - 2025 (Januari - Juni 2025)" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Kesan dan Pesan Selama Magang</label>
                    <textarea name="kesan_pesan" class="form-control" rows="4" placeholder="Tuliskan kesan & pesan alumni selama mengikuti program magang..." required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addModal')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Simpan Data</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Form: Edit Alumni -->
<div class="modal" id="editModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <div style="font-weight: 700; font-size: 1.1rem; color: var(--text-title);">
                <i class="fa-solid fa-user-pen" style="color: var(--primary);"></i> Edit Data Alumni Magang
            </div>
            <button onclick="closeModal('editModal')" style="background: none; border: none; font-size: 1.25rem; color: var(--text-muted); cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="editForm" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Alumni</label>
                    <input type="text" name="nama" id="edit_nama" class="form-control" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Asal Instansi</label>
                        <input type="text" name="asal_instansi" id="edit_asal_instansi" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Jurusan</label>
                        <input type="text" name="jurusan" id="edit_jurusan" class="form-control" required>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">Periode Magang</label>
                    <input type="text" name="periode_magang" id="edit_periode_magang" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Kesan dan Pesan Selama Magang</label>
                    <textarea name="kesan_pesan" id="edit_kesan_pesan" class="form-control" rows="4" required></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editModal')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Perbarui Data</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal View: Detail Testimoni -->
<div class="modal" id="detailModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <div style="font-weight: 700; font-size: 1.1rem; color: var(--text-title);">
                <i class="fa-solid fa-quote-left" style="color: var(--primary);"></i> Detail Testimoni Alumni
            </div>
            <button onclick="closeModal('detailModal')" style="background: none; border: none; font-size: 1.25rem; color: var(--text-muted); cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="modal-body">
            <h3 id="detail_nama" style="font-size: 1.25rem; font-weight: 700; color: var(--text-title); margin-bottom: 0.2rem;"></h3>
            <div style="font-size: 0.9rem; color: var(--primary); font-weight: 600; margin-bottom: 0.5rem;" id="detail_instansi"></div>
            <div style="margin-bottom: 1rem;">
                <span class="badge badge-indigo" id="detail_periode"></span>
            </div>
            <div style="background-color: #f8fafc; padding: 1.25rem; border-radius: 12px; border: 1px solid var(--border-color); font-size: 0.95rem; line-height: 1.6; color: var(--text-body); white-space: pre-line;" id="detail_kesan"></div>
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-secondary" onclick="closeModal('detailModal')">Tutup</button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openAddModal() {
        document.getElementById('addModal').style.display = 'flex';
    }

    function openEditModal(item) {
        document.getElementById('editForm').action = '/alumni/' + item.id + '/update';
        document.getElementById('edit_nama').value = item.nama;
        document.getElementById('edit_asal_instansi').value = item.asal_instansi;
        document.getElementById('edit_jurusan').value = item.jurusan;
        document.getElementById('edit_periode_magang').value = item.periode_magang;
        document.getElementById('edit_kesan_pesan').value = item.kesan_pesan;
        
        document.getElementById('editModal').style.display = 'flex';
    }

    function openDetailModal(item) {
        document.getElementById('detail_nama').innerText = item.nama;
        document.getElementById('detail_instansi').innerText = item.asal_instansi + ' (' + item.jurusan + ')';
        document.getElementById('detail_periode').innerText = item.periode_magang;
        document.getElementById('detail_kesan').innerText = item.kesan_pesan;

        document.getElementById('detailModal').style.display = 'flex';
    }
</script>
@endpush
