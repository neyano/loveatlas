@extends('layouts.admin')

@section('title', 'ユーザー管理')

@section('content')
<div id="app">
    <div class="admin-filters">
        <input type="text" id="search-input" class="form-input" style="width:auto;min-width:250px;" placeholder="ユーザー名・メールで検索" oninput="debounceSearch()">
        <select id="role-filter" class="form-input" style="width:auto;" onchange="loadUsers()">
            <option value="">全権限</option>
            <option value="user">user</option>
            <option value="moderator">moderator</option>
            <option value="admin">admin</option>
        </select>
    </div>

    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>ユーザー名</th>
                    <th>メール</th>
                    <th>権限</th>
                    <th>投稿数</th>
                    <th>状態</th>
                    <th>操作</th>
                </tr>
            </thead>
            <tbody id="users-body">
                <tr><td colspan="6" class="admin-table__empty">読み込み中...</td></tr>
            </tbody>
        </table>
    </div>
</div>

@push('styles')
<style>
    .admin-filters { margin-bottom: var(--space-4); display: flex; gap: var(--space-2); flex-wrap: wrap; }
    .admin-table-container { overflow-x: auto; }
    .admin-table { width: 100%; border-collapse: collapse; background: white; border-radius: var(--border-radius); overflow: hidden; }
    .admin-table th { background: var(--color-bg-tertiary); padding: var(--space-3) var(--space-4); text-align: left; font-size: var(--font-size-sm); font-weight: 600; }
    .admin-table td { padding: var(--space-3) var(--space-4); border-top: 1px solid var(--color-border-light); font-size: var(--font-size-sm); }
    .admin-table__empty { text-align: center; color: var(--color-text-secondary); padding: var(--space-8) !important; }
    .badge--active { background: #D1FAE5; color: #059669; }
    .badge--banned { background: #FEE2E2; color: #DC2626; }
</style>
@endpush

@push('scripts')
<script>
    const headers = { 'Accept': 'application/json', 'Content-Type': 'application/json', 'X-XSRF-TOKEN': decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] || '') };
    let searchTimer;

    function debounceSearch() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadUsers, 300);
    }

    function loadUsers() {
        const search = document.getElementById('search-input').value;
        const role = document.getElementById('role-filter').value;
        const params = new URLSearchParams();
        if (search) params.set('search', search);
        if (role) params.set('role', role);

        fetch(`/api/v1/admin/users?${params}`, { credentials: 'include', headers: { 'Accept': 'application/json' } })
            .then(r => r.json())
            .then(res => {
                const tbody = document.getElementById('users-body');
                if (!res.data || res.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="6" class="admin-table__empty">ユーザーが見つかりません。</td></tr>';
                    return;
                }
                tbody.innerHTML = res.data.map(u => `
                    <tr>
                        <td><strong>${u.display_name}</strong><br><small>@${u.username}</small></td>
                        <td>${u.email}</td>
                        <td>
                            <select onchange="changeRole(${u.id}, this.value)" class="form-input" style="width:auto;padding:2px 8px;font-size:var(--font-size-xs);">
                                <option value="user" ${u.role === 'user' ? 'selected' : ''}>user</option>
                                <option value="moderator" ${u.role === 'moderator' ? 'selected' : ''}>moderator</option>
                                <option value="admin" ${u.role === 'admin' ? 'selected' : ''}>admin</option>
                            </select>
                        </td>
                        <td>${u.quotes_count ?? 0}</td>
                        <td><span class="badge ${u.is_active ? 'badge--active' : 'badge--banned'}">${u.is_active ? '有効' : 'BAN'}</span></td>
                        <td>
                            <button class="btn btn--secondary" onclick="toggleBan(${u.id})">${u.is_active ? 'BAN' : '解除'}</button>
                        </td>
                    </tr>
                `).join('');
            });
    }

    function changeRole(id, role) {
        fetch(`/api/v1/admin/users/${id}/role`, { method: 'PUT', credentials: 'include', headers, body: JSON.stringify({ role }) });
    }

    function toggleBan(id) {
        if (!confirm('この操作を実行しますか？')) return;
        fetch(`/api/v1/admin/users/${id}/ban`, { method: 'PUT', credentials: 'include', headers })
            .then(() => loadUsers());
    }

    loadUsers();
</script>
@endpush
@endsection
