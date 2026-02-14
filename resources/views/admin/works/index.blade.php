@extends('layouts.admin')

@section('title', '作品管理')

@section('content')
    <div class="admin-filters">
        <input type="text" id="search-input" class="form-input" style="width:auto;min-width:250px;" placeholder="作品名で検索" oninput="debounceSearch()">
        <select id="type-filter" class="form-input" style="width:auto;" onchange="loadWorks()">
            <option value="">全タイプ</option>
            <option value="movie">映画</option>
            <option value="anime">アニメ</option>
            <option value="drama">ドラマ</option>
            <option value="novel">小説</option>
            <option value="game">ゲーム</option>
            <option value="other">その他</option>
        </select>
    </div>

    <div class="admin-table-container">
        <table class="admin-table">
            <thead>
                <tr>
                    <th>作品名</th>
                    <th>タイプ</th>
                    <th>年</th>
                    <th>セリフ数</th>
                    <th>承認</th>
                </tr>
            </thead>
            <tbody id="works-body">
                <tr><td colspan="5" class="admin-table__empty">読み込み中...</td></tr>
            </tbody>
        </table>
    </div>

@push('styles')
<style>
    .admin-filters { margin-bottom: var(--space-4); display: flex; gap: var(--space-2); flex-wrap: wrap; }
    .admin-table-container { overflow-x: auto; }
    .admin-table { width: 100%; border-collapse: collapse; background: white; border-radius: var(--border-radius); overflow: hidden; }
    .admin-table th { background: var(--color-bg-tertiary); padding: var(--space-3) var(--space-4); text-align: left; font-size: var(--font-size-sm); font-weight: 600; }
    .admin-table td { padding: var(--space-3) var(--space-4); border-top: 1px solid var(--color-border-light); font-size: var(--font-size-sm); }
    .admin-table__empty { text-align: center; color: var(--color-text-secondary); padding: var(--space-8) !important; }
    .badge--approved { background: #D1FAE5; color: #059669; }
    .badge--pending { background: #FEF3C7; color: #D97706; }
</style>
@endpush

@push('scripts')
<script>
    let searchTimer;
    let allWorks = [];

    function debounceSearch() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadWorks, 300);
    }

    function loadWorks() {
        const search = document.getElementById('search-input').value.toLowerCase();
        const type = document.getElementById('type-filter').value;

        let filtered = allWorks;
        if (search) filtered = filtered.filter(w => w.title.toLowerCase().includes(search) || (w.title_original || '').toLowerCase().includes(search));
        if (type) filtered = filtered.filter(w => w.type === type);

        renderWorks(filtered);
    }

    function renderWorks(works) {
        const tbody = document.getElementById('works-body');
        if (!works.length) {
            tbody.innerHTML = '<tr><td colspan="5" class="admin-table__empty">作品が見つかりません。</td></tr>';
            return;
        }
        tbody.innerHTML = works.map(w => `
            <tr>
                <td><strong>${w.title}</strong>${w.title_original ? '<br><small>' + w.title_original + '</small>' : ''}</td>
                <td>${w.type}</td>
                <td>${w.year ?? '-'}</td>
                <td>${w.quotes_count ?? 0}</td>
                <td><span class="badge ${w.is_approved ? 'badge--approved' : 'badge--pending'}">${w.is_approved ? '承認済' : '未承認'}</span></td>
            </tr>
        `).join('');
    }

    // 作品一覧は専用 API がまだないので直接 DB から取得する簡易エンドポイントを使う
    // 暫定: /api/v1/admin/works を呼ぶ
    fetch('/api/v1/admin/works', { credentials: 'include', headers: { 'Accept': 'application/json' } })
        .then(r => r.ok ? r.json() : Promise.reject())
        .then(res => {
            allWorks = res.data || [];
            loadWorks();
        })
        .catch(() => {
            document.getElementById('works-body').innerHTML = '<tr><td colspan="5" class="admin-table__empty">作品 API は準備中です。</td></tr>';
        });
</script>
@endpush
@endsection
