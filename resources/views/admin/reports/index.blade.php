@extends('layouts.admin')

@section('title', '通報管理')

@section('content')
<div id="app">
    <div class="admin-filters">
        <select id="status-filter" class="form-input" style="width:auto;" onchange="loadReports()">
            <option value="">すべて</option>
            <option value="open" selected>未対応</option>
            <option value="reviewed">確認済み</option>
            <option value="resolved">解決済み</option>
            <option value="dismissed">却下</option>
        </select>
    </div>

    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>セリフ</th>
                    <th>理由</th>
                    <th>通報者</th>
                    <th>状態</th>
                    <th>日時</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody id="reports-body">
                <tr><td colspan="6" class="admin-table__empty">読み込み中...</td></tr>
            </tbody>
        </table>
    </div>
</div>

@push('styles')
<style>
    .admin-filters { margin-bottom: var(--space-4); }
    .admin-table-container { overflow-x: auto; }
    .admin-table { width: 100%; border-collapse: collapse; background: white; border-radius: var(--border-radius); overflow: hidden; }
    .admin-table th { background: var(--color-bg-tertiary); padding: var(--space-3) var(--space-4); text-align: left; font-size: var(--font-size-sm); font-weight: 600; }
    .admin-table td { padding: var(--space-3) var(--space-4); border-top: 1px solid var(--color-border-light); font-size: var(--font-size-sm); }
    .admin-table__empty { text-align: center; color: var(--color-text-secondary); padding: var(--space-8) !important; }
    .badge { display: inline-block; padding: 2px 8px; border-radius: 12px; font-size: var(--font-size-xs); font-weight: 600; }
    .badge--open { background: #FEE2E2; color: #DC2626; }
    .badge--reviewed { background: #FEF3C7; color: #D97706; }
    .badge--resolved { background: #D1FAE5; color: #059669; }
    .badge--dismissed { background: var(--color-bg-tertiary); color: var(--color-text-secondary); }
</style>
@endpush

@push('scripts')
<script>
    const headers = { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-XSRF-TOKEN': decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] || '') };
    const reasonLabels = { spam: 'スパム', inappropriate: '不適切', wrong_info: '誤情報', copyright: '著作権', other: 'その他' };

    function loadReports() {
        const status = document.getElementById('status-filter').value;
        const url = '/api/v1/admin/reports' + (status ? `?status=${status}` : '');
        fetch(url, { credentials: 'include', headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(res => {
                const tbody = document.getElementById('reports-body');
                if (!res.data || res.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="admin-table__empty">通報はありません。</td></tr>';
                    return;
                }
                tbody.innerHTML = res.data.map(r => `
                    <tr id="report-${r.id}">
                        <td>${r.quote?.quote_text?.substring(0, 40) || '-'}...</td>
                        <td>${reasonLabels[r.reason] || r.reason}</td>
                        <td>${r.reporter?.display_name || r.reporter?.username || '-'}</td>
                        <td><span class="badge badge--${r.status}">${r.status}</span></td>
                        <td>${new Date(r.created_at).toLocaleDateString('ja-JP')}</td>
                        <td>
                            ${r.status === 'open' ? `
                                <button class="btn btn--primary" onclick="updateReport(${r.id}, 'resolved')">解決</button>
                                <button class="btn btn--secondary" onclick="updateReport(${r.id}, 'dismissed')">却下</button>
                            ` : '-'}
                        </td>
                    </tr>
                `).join('');
            });
    }

    function updateReport(id, status) {
        fetch(`/api/v1/admin/reports/${id}`, { method: 'PUT', credentials: 'include', headers, body: JSON.stringify({ status }) })
            .then(r => r.json())
            .then(() => loadReports());
    }

    loadReports();
</script>
@endpush
@endsection
