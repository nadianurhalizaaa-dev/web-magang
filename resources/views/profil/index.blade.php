@extends('layouts.app')

@section('title', 'Profil Perusahaan - Admin Panel')

@section('content')
<div class="page-header-box">
    <div>
        <h1 class="page-title">Profil Perusahaan & Kantor</h1>
        <p class="page-subtitle">Kelola informasi instansi, deskripsi, visi, misi, serta kontak yang ditampilkan di API Beranda.</p>
    </div>
</div>

<div style="display: grid; grid-template-columns: 2fr 1fr; gap: 1.5rem;">
    <!-- Main Form Settings -->
    <div class="card">
        <div class="card-header">
            <div class="card-title">
                <i class="fa-solid fa-building-flag" style="color: var(--primary);"></i>
                Pengaturan Informasi Perusahaan
            </div>
        </div>
        <div class="card-body">
            <form action="{{ route('profil.update') }}" method="POST">
                @csrf

                <!-- Section 1: Data Umum -->
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-title); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-circle-info" style="color: var(--primary);"></i> Identitas Perusahaan
                </h3>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Nama Perusahaan / Kantor</label>
                        <input type="text" name="nama_perusahaan" class="form-control" value="{{ $profil->nama_perusahaan ?? '' }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Bidang Usaha</label>
                        <input type="text" name="bidang_usaha" class="form-control" value="{{ $profil->bidang_usaha ?? '' }}" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Deskripsi Singkat</label>
                    <textarea name="deskripsi_singkat" class="form-control" rows="2" required>{{ $profil->deskripsi_singkat ?? '' }}</textarea>
                </div>

                <div class="form-group">
                    <label class="form-label">Tentang Perusahaan / Kantor (Penjelasan Lengkap)</label>
                    <textarea name="tentang_kantor" class="form-control" rows="4" required>{{ $profil->tentang_kantor ?? '' }}</textarea>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 1.5rem 0;">

                <!-- Section 2: Visi & Misi -->
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-title); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-bullseye" style="color: #0891b2;"></i> Visi & Misi Perusahaan
                </h3>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Visi Perusahaan</label>
                        <textarea name="visi" class="form-control" rows="3">{{ $profil->visi ?? '' }}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Misi Perusahaan</label>
                        <textarea name="misi" class="form-control" rows="3">{{ $profil->misi ?? '' }}</textarea>
                    </div>
                </div>

                <hr style="border: 0; border-top: 1px solid var(--border-color); margin: 1.5rem 0;">

                <!-- Section 3: Kontak & Alamat -->
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-title); margin-bottom: 1rem; display: flex; align-items: center; gap: 0.5rem;">
                    <i class="fa-solid fa-location-dot" style="color: #059669;"></i> Kontak & Alamat Official
                </h3>

                <div class="form-group">
                    <label class="form-label">Alamat Kantor Lengkap</label>
                    <input type="text" name="alamat" class="form-control" value="{{ $profil->alamat ?? '' }}">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Email Official</label>
                        <input type="email" name="email" class="form-control" value="{{ $profil->email ?? '' }}">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Nomor Telepon Kontak</label>
                        <input type="text" name="telepon" class="form-control" value="{{ $profil->telepon ?? '' }}">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">Website Official (URL)</label>
                    <input type="url" name="website" class="form-control" value="{{ $profil->website ?? '' }}" placeholder="https://example.com">
                </div>

                <div style="display: flex; justify-content: flex-end; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-primary" style="padding: 0.75rem 1.5rem;">
                        <i class="fa-solid fa-floppy-disk"></i> Simpan Perubahan Profil
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Preview & Live Info Sidebar Card -->
    <div style="display: flex; flex-direction: column; gap: 1.5rem;">
        <div class="card">
            <div class="card-header">
                <div class="card-title">
                    <i class="fa-solid fa-eye" style="color: var(--primary);"></i> Preview Singkat Profil
                </div>
            </div>
            <div class="card-body">
                <div style="text-align: center; margin-bottom: 1.25rem;">
                    <div style="width: 56px; height: 56px; border-radius: 16px; background: linear-gradient(135deg, #6366f1, #06b6d4); display: inline-flex; align-items: center; justify-content: center; color: white; font-size: 1.6rem; margin-bottom: 0.5rem;">
                        <i class="fa-solid fa-building"></i>
                    </div>
                    <h2 style="font-size: 1.2rem; font-weight: 700; color: var(--text-title);">{{ $profil->nama_perusahaan ?? 'Nama Perusahaan' }}</h2>
                    <span class="badge badge-indigo" style="margin-top: 0.3rem;">{{ $profil->bidang_usaha ?? 'Bidang Usaha' }}</span>
                </div>

                <div style="font-size: 0.875rem; color: var(--text-body); background: #f8fafc; padding: 1rem; border-radius: 10px; border: 1px solid var(--border-color); margin-bottom: 1rem;">
                    <strong>Deskripsi:</strong><br>
                    {{ $profil->deskripsi_singkat ?? 'Belum ada deskripsi singkat.' }}
                </div>

                <div style="font-size: 0.8rem; color: var(--text-muted); display: flex; flex-direction: column; gap: 0.5rem;">
                    <div><i class="fa-solid fa-envelope" style="width: 20px; color: var(--primary);"></i> {{ $profil->email ?? 'Belum diisi' }}</div>
                    <div><i class="fa-solid fa-phone" style="width: 20px; color: #059669;"></i> {{ $profil->telepon ?? 'Belum diisi' }}</div>
                    <div><i class="fa-solid fa-globe" style="width: 20px; color: #0891b2;"></i> {{ $profil->website ?? 'Belum diisi' }}</div>
                </div>
            </div>
        </div>

        <div class="card" style="background-color: #0f172a; color: white; border: none;">
            <div class="card-body">
                <h4 style="font-family: 'Outfit', sans-serif; font-size: 1rem; color: #38bdf8; margin-bottom: 0.5rem;">
                    <i class="fa-solid fa-plug"></i> JSON REST API Live
                </h4>
                <p style="font-size: 0.8rem; color: #94a3b8; margin-bottom: 1rem;">
                    Data profil di atas akan secara otomatis di-outputkan ke REST API untuk konsumsi React/Frontend:
                </p>
                <div style="background: #1e293b; padding: 0.75rem; border-radius: 8px; font-family: monospace; font-size: 0.8rem; color: #38bdf8; word-break: break-all;">
                    GET /api/beranda/profil
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
