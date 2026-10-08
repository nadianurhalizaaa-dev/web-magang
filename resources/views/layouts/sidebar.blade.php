<style>
    aside.sidebar {
        width: var(--sidebar-width);
        background-color: var(--bg-sidebar);
        color: #f8fafc;
        display: flex;
        flex-direction: column;
        position: fixed;
        top: 0;
        bottom: 0;
        left: 0;
        z-index: 50;
        box-shadow: 4px 0 20px rgba(0, 0, 0, 0.08);
        transition: all 0.3s ease;
    }

    .sidebar-brand {
        padding: 1.5rem 1.25rem;
        display: flex;
        align-items: center;
        gap: 0.85rem;
        border-bottom: 1px solid rgba(255, 255, 255, 0.08);
    }

    .brand-logo {
        width: 42px;
        height: 42px;
        background: linear-gradient(135deg, #6366f1, #06b6d4);
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 1.3rem;
        box-shadow: 0 4px 12px rgba(99, 102, 241, 0.35);
    }

    .brand-name {
        font-family: 'Outfit', sans-serif;
        font-size: 1.2rem;
        font-weight: 700;
        color: #ffffff;
        letter-spacing: -0.3px;
        line-height: 1.1;
    }

    .brand-sub {
        font-size: 0.7rem;
        color: #94a3b8;
        text-transform: uppercase;
        letter-spacing: 0.6px;
        font-weight: 600;
    }

    .sidebar-nav {
        padding: 1.25rem 0.85rem;
        flex-grow: 1;
        display: flex;
        flex-direction: column;
        gap: 0.35rem;
        overflow-y: auto;
    }

    .nav-section-label {
        font-size: 0.7rem;
        text-transform: uppercase;
        color: #64748b;
        font-weight: 700;
        letter-spacing: 0.8px;
        padding: 0.85rem 0.75rem 0.35rem 0.75rem;
    }

    .nav-item {
        display: flex;
        align-items: center;
        gap: 0.85rem;
        padding: 0.75rem 0.9rem;
        color: #94a3b8;
        text-decoration: none;
        border-radius: 10px;
        font-size: 0.9rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .nav-item:hover {
        background-color: rgba(255, 255, 255, 0.08);
        color: #ffffff;
    }

    .nav-item.active {
        background: linear-gradient(135deg, #4f46e5, #4338ca);
        color: #ffffff;
        box-shadow: 0 4px 14px rgba(79, 70, 229, 0.35);
    }

    .nav-item i {
        font-size: 1.1rem;
        width: 22px;
        text-align: center;
    }

    .sidebar-user {
        padding: 1rem 1.25rem;
        border-top: 1px solid rgba(255, 255, 255, 0.08);
        display: flex;
        align-items: center;
        gap: 0.75rem;
        background: rgba(0, 0, 0, 0.2);
    }

    .user-avatar-placeholder {
        width: 38px;
        height: 38px;
        border-radius: 50%;
        background: linear-gradient(135deg, #3b82f6, #6366f1);
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-weight: 700;
        font-size: 0.95rem;
    }

    .user-info {
        line-height: 1.25;
        overflow: hidden;
    }

    .user-name {
        font-size: 0.85rem;
        font-weight: 600;
        color: #f8fafc;
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }

    .user-role {
        font-size: 0.75rem;
        color: #94a3b8;
        white-space: nowrap;
        text-overflow: ellipsis;
        overflow: hidden;
    }
</style>

<aside class="sidebar">
    <div class="sidebar-brand">
        <div class="brand-logo">
            <i class="fa-solid fa-graduation-cap"></i>
        </div>
        <div>
            <div class="brand-name">PORTAL MAGANG</div>
            <div class="brand-sub">Admin Enterprise</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section-label">Main Core</div>

        <a href="{{ route('alumni.index') }}" class="nav-item {{ request()->routeIs('alumni.*') || request()->routeIs('admin.dashboard') ? 'active' : '' }}">
            <i class="fa-solid fa-users-gear"></i>
            <span>Data Alumni Magang</span>
        </a>

        <a href="{{ route('aktivitas.index') }}" class="nav-item {{ request()->routeIs('aktivitas.*') ? 'active' : '' }}">
            <i class="fa-solid fa-calendar-check"></i>
            <span>Aktivitas Magang</span>
        </a>

        <a href="{{ route('profil.index') }}" class="nav-item {{ request()->routeIs('profil.*') ? 'active' : '' }}">
            <i class="fa-solid fa-building-user"></i>
            <span>Profil Perusahaan</span>
        </a>

        <a href="{{ route('komentar.index') }}" class="nav-item {{ request()->routeIs('komentar.*') ? 'active' : '' }}">
            <i class="fa-solid fa-comments"></i>
            <span>Komentar & Masukan</span>
        </a>

        <div class="nav-section-label">Administration</div>

        <a href="{{ route('users.index') }}" class="nav-item {{ request()->routeIs('users.*') ? 'active' : '' }}">
            <i class="fa-solid fa-user-shield"></i>
            <span>Manajemen User Admin</span>
        </a>

        <div class="nav-section-label">Developers</div>

        <a href="{{ route('api_docs.index') }}" class="nav-item {{ request()->routeIs('api_docs.*') ? 'active' : '' }}">
            <i class="fa-solid fa-code"></i>
            <span>REST API Explorer</span>
        </a>
    </nav>

    <div class="sidebar-user">
        <div class="user-avatar-placeholder">
            {{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}
        </div>
        <div class="user-info" style="flex-grow: 1;">
            <div class="user-name">{{ Auth::user()->name ?? 'Administrator' }}</div>
            <div class="user-role">{{ ucfirst(Auth::user()->role ?? 'Admin') }}</div>
        </div>
        <form action="{{ route('logout') }}" method="POST" style="margin-left: auto;">
            @csrf
            <button type="submit" style="background: none; border: none; color: #ef4444; cursor: pointer; font-size: 1.15rem;" title="Logout">
                <i class="fa-solid fa-right-from-bracket"></i>
            </button>
        </form>
    </div>
</aside>
