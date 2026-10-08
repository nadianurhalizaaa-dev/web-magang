@extends('layouts.app')

@section('title', 'REST API Explorer & React Integration - Admin Panel')

@section('content')
<div class="page-header-box">
    <div>
        <h1 class="page-title">REST API Explorer & React Integration</h1>
        <p class="page-subtitle">Daftar lengkap endpoint REST API beserta petunjuk & contoh kode integrasi projek React.</p>
    </div>
</div>

<!-- Integration Info Banner Card -->
<div class="card" style="margin-bottom: 2rem;">
    <div class="card-body" style="padding: 1.5rem 2rem;">
        <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 1rem;">
            <div style="display: flex; align-items: center; gap: 1.25rem;">
                <div style="width: 52px; height: 52px; border-radius: var(--radius-md); background: var(--primary-light); display: flex; align-items: center; justify-content: center; color: var(--primary); font-size: 1.6rem;">
                    <i class="fa-brands fa-react"></i>
                </div>
                <div>
                    <h2 style="font-family: 'Outfit', sans-serif; font-size: 1.25rem; font-weight: 700; color: var(--text-title); margin-bottom: 0.25rem;">
                        Base API Endpoint URL
                    </h2>
                    <div style="font-size: 0.9rem; color: var(--text-muted); display: flex; align-items: center; gap: 0.5rem;">
                        <code style="color: var(--primary); background: #f1f5f9; padding: 0.25rem 0.65rem; border-radius: 6px; font-weight: 700; font-size: 0.95rem;">{{ $baseUrl }}/api</code>
                    </div>
                </div>
            </div>
            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                <span class="badge badge-emerald"><i class="fa-solid fa-circle-check"></i> CORS Enabled</span>
                <span class="badge badge-indigo"><i class="fa-solid fa-shield-halved"></i> Sanctum Token Auth</span>
            </div>
        </div>
    </div>
</div>

<!-- Full Width API Endpoints Accordion Table -->
<div class="card">
    <div class="card-header">
        <div class="card-title">
            <i class="fa-solid fa-list-check" style="color: var(--primary);"></i>
            Daftar Endpoints REST API (Klik tombol panah <i class="fa-solid fa-chevron-down" style="font-size: 0.8rem;"></i> untuk melihat Kode React)
        </div>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 90px;">Method</th>
                    <th style="width: 220px;">Endpoint URL</th>
                    <th>Nama & Deskripsi Endpoint</th>
                    <th style="width: 150px;">Akses Otentikasi</th>
                    <th style="text-align: right; width: 160px;">Detail React</th>
                </tr>
            </thead>
            <tbody>
                @foreach($apiEndpoints as $ep)
                    <!-- Main Summary Row -->
                    <tr class="api-main-row" onclick="toggleAccordion('{{ $ep['id'] }}')">
                        <td>
                            @if($ep['method'] == 'GET')
                                <span class="badge badge-indigo" style="font-weight: 700; font-size: 0.8rem;">GET</span>
                            @else
                                <span class="badge badge-emerald" style="font-weight: 700; font-size: 0.8rem;">POST</span>
                            @endif
                        </td>
                        <td>
                            <code style="font-weight: 700; color: var(--primary); font-size: 0.9rem;">{{ $ep['url'] }}</code>
                        </td>
                        <td>
                            <div style="font-weight: 600; color: var(--text-title); margin-bottom: 0.15rem;">{{ $ep['name'] }}</div>
                            <div style="font-size: 0.825rem; color: var(--text-muted);">{{ $ep['description'] }}</div>
                        </td>
                        <td>
                            @if($ep['auth'])
                                <span class="badge badge-amber"><i class="fa-solid fa-lock"></i> Sanctum Token</span>
                            @else
                                <span class="badge badge-slate"><i class="fa-solid fa-lock-open"></i> Public</span>
                            @endif
                        </td>
                        <td style="text-align: right;">
                            <button type="button" class="btn btn-secondary btn-sm" onclick="event.stopPropagation(); toggleAccordion('{{ $ep['id'] }}');">
                                <span>Detail React</span>
                                <i class="fa-solid fa-chevron-down accordion-icon" id="icon-{{ $ep['id'] }}"></i>
                            </button>
                        </td>
                    </tr>

                    <!-- Collapsible Accordion Content Row -->
                    <tr id="detail-{{ $ep['id'] }}" class="accordion-detail-row" style="display: none; background-color: #f8fafc;">
                        <td colspan="5" style="padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border-color);">
                            
                            <div style="background-color: #ffffff; border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow: hidden; box-shadow: var(--shadow-sm);">
                                <!-- Detail Header Bar -->
                                <div style="padding: 1rem 1.25rem; background-color: #f1f5f9; border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 0.75rem;">
                                    <div style="display: flex; align-items: center; gap: 0.75rem;">
                                        <i class="fa-brands fa-react" style="color: #0284c7; font-size: 1.2rem;"></i>
                                        <span style="font-weight: 700; color: var(--text-title); font-size: 0.9rem;">
                                            Kode Integrasi React (.jsx) &mdash; {{ $ep['name'] }}
                                        </span>
                                    </div>
                                    <button class="btn btn-primary btn-sm" onclick="copyReactSnippet('code-{{ $ep['id'] }}', 'btn-copy-{{ $ep['id'] }}')">
                                        <i class="fa-solid fa-copy"></i> <span id="btn-copy-{{ $ep['id'] }}">Salin Kode React</span>
                                    </button>
                                </div>

                                <!-- Request Headers Info Bar -->
                                <div style="padding: 0.75rem 1.25rem; background-color: #ffffff; border-bottom: 1px solid var(--border-color); font-size: 0.8rem; color: var(--text-muted); display: flex; gap: 1.5rem; flex-wrap: wrap;">
                                    <div><strong>Headers:</strong> <code style="color: var(--primary);">Accept: application/json</code></div>
                                    @if($ep['auth'])
                                        <div><strong>Auth Header:</strong> <code style="color: #d97706;">Authorization: Bearer &lt;YOUR_SANCTUM_TOKEN&gt;</code></div>
                                    @endif
                                </div>

                                <!-- Code Viewer Container -->
                                <div style="background-color: #0f172a; padding: 1rem;">
                                    <pre id="code-{{ $ep['id'] }}" style="background: #090d16; padding: 1rem; border-radius: var(--radius-sm); border: 1px solid rgba(255, 255, 255, 0.08); font-family: monospace; font-size: 0.825rem; color: #38bdf8; overflow-x: auto; max-height: 380px; line-height: 1.5; white-space: pre;">{{ $ep['react_code'] }}</pre>
                                </div>
                            </div>

                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<style>
    .api-main-row:hover {
        background-color: #f1f5f9 !important;
    }
    .accordion-icon {
        transition: transform 0.25s ease;
        font-size: 0.8rem;
    }
    .accordion-icon.open {
        transform: rotate(180deg);
    }
</style>
@endsection

@push('scripts')
<script>
    function toggleAccordion(id) {
        const detailRow = document.getElementById('detail-' + id);
        const icon = document.getElementById('icon-' + id);

        if (detailRow.style.display === 'none' || detailRow.style.display === '') {
            detailRow.style.display = 'table-row';
            icon.classList.add('open');
        } else {
            detailRow.style.display = 'none';
            icon.classList.remove('open');
        }
    }

    function copyReactSnippet(codeId, btnId) {
        const codeText = document.getElementById(codeId).innerText;
        navigator.clipboard.writeText(codeText).then(() => {
            const btnSpan = document.getElementById(btnId);
            btnSpan.innerText = 'Berhasil Disalin!';
            setTimeout(() => { btnSpan.innerText = 'Salin Kode React'; }, 2000);
        });
    }
</script>
@endpush
