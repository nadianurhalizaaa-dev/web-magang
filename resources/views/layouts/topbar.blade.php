<style>
    header.topbar {
        height: 64px;
        background-color: var(--bg-card);
        border-bottom: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 0 2rem;
        position: sticky;
        top: 0;
        z-index: 40;
        box-shadow: var(--shadow-sm);
    }

    .topbar-left {
        display: flex;
        align-items: center;
        gap: 1.25rem;
    }

    .page-title-box h1 {
        font-family: 'Outfit', sans-serif;
        font-size: 1.2rem;
        font-weight: 700;
        color: var(--text-title);
        line-height: 1.2;
    }

    .breadcrumb {
        font-size: 0.78rem;
        color: var(--text-muted);
        display: flex;
        align-items: center;
        gap: 0.4rem;
    }

    .topbar-right {
        display: flex;
        align-items: center;
        gap: 1rem;
    }

    .status-badge {
        display: inline-flex;
        align-items: center;
        gap: 0.4rem;
        background-color: #ecfdf5;
        color: #047857;
        padding: 0.35rem 0.75rem;
        border-radius: 20px;
        font-size: 0.75rem;
        font-weight: 600;
        border: 1px solid #a7f3d0;
    }

    .status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: var(--success);
        box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
    }
</style>

<header class="topbar">
    <div class="topbar-left">
        <div class="page-title-box">
            <h1>Backend Administrator</h1>
            <div class="breadcrumb">
                <span>Magang Core</span> &bull; 
                <span style="color: var(--primary); font-weight: 600;">Laravel {{ app()->version() }}</span>
            </div>
        </div>
    </div>

    <div class="topbar-right">
        <div class="status-badge">
            <span class="status-dot"></span>
            <span>REST API Active</span>
        </div>

        <a href="{{ route('api_docs.index') }}" class="btn btn-secondary btn-sm">
            <i class="fa-solid fa-code"></i> API Explorer
        </a>

        <div style="height: 24px; width: 1px; background-color: var(--border-color);"></div>

        <div style="display: flex; align-items: center; gap: 0.5rem; font-size: 0.85rem; font-weight: 600; color: var(--text-title);">
            <i class="fa-solid fa-user-circle" style="color: var(--primary); font-size: 1.2rem;"></i>
            <span>{{ Auth::user()->name ?? 'Admin' }}</span>
        </div>
    </div>
</header>
