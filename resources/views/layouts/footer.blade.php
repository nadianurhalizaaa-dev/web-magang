<style>
    footer.dashboard-footer {
        padding: 1.25rem 2rem;
        background-color: var(--bg-card);
        border-top: 1px solid var(--border-color);
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.8rem;
        color: var(--text-muted);
        margin-top: auto;
    }

    footer.dashboard-footer a {
        color: var(--primary);
        text-decoration: none;
        font-weight: 500;
    }

    footer.dashboard-footer a:hover {
        text-decoration: underline;
    }
</style>

<footer class="dashboard-footer">
    <div>
        &copy; {{ date('Y') }} <strong>{{ $profil->nama_perusahaan ?? 'Sistem Manajemen Magang' }}</strong>. All rights reserved.
    </div>
    <div style="display: flex; gap: 1rem; align-items: center;">
        <span>Backend Version <strong>1.2.0</strong></span>
        <span>&bull;</span>
        <a href="{{ route('api_docs.index') }}">REST API Explorer</a>
    </div>
</footer>
