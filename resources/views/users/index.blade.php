@extends('layouts.app')

@section('title', 'Manajemen User Admin - Admin Panel')

@section('content')
<div class="page-header-box">
    <div>
        <h1 class="page-title">Manajemen User Administrator</h1>
        <p class="page-subtitle">Kelola akun admin yang memiliki akses login dan hak akses ke dashboard backend ini.</p>
    </div>
    <div>
        <button class="btn btn-primary" onclick="openAddUserModal()">
            <i class="fa-solid fa-user-shield"></i> Tambah Administrator Baru
        </button>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-users-gear" style="color: var(--primary);"></i>
            Daftar Administrator Sistem ({{ $users->total() }} User)
        </div>
    </div>

    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama User</th>
                    <th>Email</th>
                    <th>Role / Peran</th>
                    <th>Tanggal Terdaftar</th>
                    <th style="text-align: right;">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $index => $u)
                    <tr>
                        <td>{{ $users->firstItem() + $index }}</td>
                        <td>
                            <div style="display: flex; align-items: center; gap: 0.75rem;">
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #6366f1, #3b82f6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.85rem;">
                                    {{ strtoupper(substr($u->name, 0, 1)) }}
                                </div>
                                <div>
                                    <div style="font-weight: 600; color: var(--text-title);">{{ $u->name }}</div>
                                    @if(Auth::id() == $u->id)
                                        <span class="badge badge-emerald" style="font-size: 0.65rem;">Akun Anda</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td>{{ $u->email }}</td>
                        <td>
                            @if($u->role == 'admin')
                                <span class="badge badge-indigo"><i class="fa-solid fa-user-shield"></i> Admin</span>
                            @else
                                <span class="badge badge-slate"><i class="fa-solid fa-user"></i> Pengguna</span>
                            @endif
                        </td>
                        <td>{{ $u->created_at ? $u->created_at->format('d M Y, H:i') : '-' }}</td>
                        <td style="text-align: right; white-space: nowrap;">
                            <button class="btn btn-edit btn-sm" onclick='openEditUserModal(@json($u))'>
                                <i class="fa-solid fa-user-pen"></i> Edit
                            </button>
                            
                            @if(Auth::id() != $u->id)
                                <form action="{{ route('users.delete', $u->id) }}" method="POST" style="display: inline-block;" onsubmit="return confirm('Apakah Anda yakin ingin menghapus akun administrator ini?');">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-sm">
                                        <i class="fa-solid fa-trash"></i> Hapus
                                    </button>
                                </form>
                            @else
                                <button class="btn btn-secondary btn-sm" disabled title="Tidak dapat menghapus akun sendiri">
                                    <i class="fa-solid fa-lock"></i> Aktif
                                </button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" style="text-align: center; padding: 2rem; color: var(--text-muted);">
                            Belum ada user administrator terdaftar.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($users->hasPages())
        <div style="padding: 1rem 1.5rem; border-top: 1px solid var(--border-color); display: flex; justify-content: flex-end;">
            {{ $users->links() }}
        </div>
    @endif
</div>

<!-- Modal Form: Tambah Admin -->
<div class="modal" id="addUserModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <div style="font-weight: 700; font-size: 1.1rem; color: var(--text-title);">
                <i class="fa-solid fa-user-plus" style="color: var(--primary);"></i> Tambah Administrator Baru
            </div>
            <button onclick="closeModal('addUserModal')" style="background: none; border: none; font-size: 1.25rem; color: var(--text-muted); cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form action="{{ route('users.store') }}" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="name" class="form-control" placeholder="Contoh: Admin Pengelola" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Alamat Email Login</label>
                    <input type="email" name="email" class="form-control" placeholder="contoh: admin@magang.com" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Password Login</label>
                        <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role Akses</label>
                        <select name="role" class="form-control" required>
                            <option value="admin">Admin</option>
                            <option value="user">Pengguna</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('addUserModal')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Buat Akun Admin</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Form: Edit Admin -->
<div class="modal" id="editUserModal">
    <div class="modal-dialog">
        <div class="modal-header">
            <div style="font-weight: 700; font-size: 1.1rem; color: var(--text-title);">
                <i class="fa-solid fa-user-pen" style="color: var(--primary);"></i> Edit Data Administrator
            </div>
            <button onclick="closeModal('editUserModal')" style="background: none; border: none; font-size: 1.25rem; color: var(--text-muted); cursor: pointer;"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <form id="editUserForm" method="POST">
            @csrf
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Nama Lengkap</label>
                    <input type="text" name="name" id="edit_user_name" class="form-control" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Alamat Email Login</label>
                    <input type="email" name="email" id="edit_user_email" class="form-control" required>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Ganti Password (Opsional)</label>
                        <input type="password" name="password" class="form-control" placeholder="Biarkan kosong jika tidak diubah">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Role Akses</label>
                        <select name="role" id="edit_user_role" class="form-control" required>
                            <option value="admin">Admin</option>
                            <option value="user">Pengguna</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal('editUserModal')">Batal</button>
                <button type="submit" class="btn btn-primary"><i class="fa-solid fa-save"></i> Perbarui Admin</button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function openAddUserModal() {
        document.getElementById('addUserModal').style.display = 'flex';
    }

    function openEditUserModal(user) {
        document.getElementById('editUserForm').action = '/users/' + user.id + '/update';
        document.getElementById('edit_user_name').value = user.name;
        document.getElementById('edit_user_email').value = user.email;
        document.getElementById('edit_user_role').value = user.role || 'admin';
        
        document.getElementById('editUserModal').style.display = 'flex';
    }
</script>
@endpush
