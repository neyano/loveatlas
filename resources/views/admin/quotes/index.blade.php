@extends('layouts.admin')

@section('title', 'セリフ承認管理')

@section('content')
<div id="app">
    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>セリフ</th>
                    <th>作品</th>
                    <th>投稿者</th>
                    <th>投稿日</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody id="quotes-body">
                <tr><td colspan="5" class="admin-table__empty">読み込み中...</td></tr>
            </tbody>
        </table>
    </div>
</div>

@push('styles')
<style>
    .admin-table-container { overflow-x: auto; }
    .admin-table { width: 100%; border-collapse: collapse; background: white; border-radius: var(--border-radius); overflow: hidden; }
    .admin-table th { background: var(--color-bg-tertiary); padding: var(--space-3) var(--space-4); text-align: left; font-size: var(--font-size-sm); font-weight: 600; }
    .admin-table td { padding: var(--space-3) var(--space-4); border-top: 1px solid var(--color-border-light); font-size: var(--font-size-sm); vertical-align: top; }
    .admin-table__empty { text-align: center; color: var(--color-text-secondary); padding: var(--space-8) !important; }
    .admin-table__quote { max-width: 300px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .admin-table__actions { display: flex; gap: var(--space-2); }
</style>
@endpush

@push('scripts')
<script>
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content;
    const headers = { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-XSRF-TOKEN': decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] || '') };

    function loadQuotes() {
        fetch('/api/v1/admin/quotes/pending', { credentials: 'include', headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(res => {
                const tbody = document.getElementById('quotes-body');
                if (!res.data || res.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="5" class="admin-table__empty">承認待ちのセリフはありません。</td></tr>';
                    return;
                }
                tbody.innerHTML = res.data.map(q => `
                    <tr id="quote-${q.id}">
                        <td class="admin-table__quote" title="${q.quote_text}">${q.quote_text.substring(0, 50)}${q.quote_text.length > 50 ? '...' : ''}</td>
                        <td>${q.work?.title || '-'}</td>
                        <td>${q.user?.display_name || q.user?.username || '-'}</td>
                        <td>${new Date(q.created_at).toLocaleDateString('ja-JP')}</td>
                        <td class="admin-table__actions">
                            <button class="btn btn--primary" onclick="approve(${q.id})">承認</button>
                            <button class="btn btn--secondary" onclick="reject(${q.id})">拒否</button>
                        </td>
                    </tr>
                `).join('');
            });
    }

    function approve(id) {
        fetch(`/api/v1/admin/quotes/${id}/approve`, { method: 'PUT', credentials: 'include', headers })
            .then(r => r.json())
            .then(() => { document.getElementById(`quote-${id}`)?.remove(); });
    }

    function reject(id) {
        if (!confirm('このセリフを拒否しますか？')) return;
        fetch(`/api/v1/admin/quotes/${id}/reject`, { method: 'PUT', credentials: 'include', headers })
            .then(r => r.json())
            .then(() => { document.getElementById(`quote-${id}`)?.remove(); });
    }

    loadQuotes();
</script>
@endpush
@endsection
