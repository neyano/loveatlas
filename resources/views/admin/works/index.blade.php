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
        <button class="btn btn--primary" onclick="showForm()">＋ 新規追加</button>
    </div>

    <!-- 新規追加・編集フォーム -->
    <div id="work-form" class="card work-form" style="display:none;">
        <h3 id="form-title" class="work-form__title">新規作品</h3>
        <input type="hidden" id="form-id">
        <div class="work-form__grid">
            <div class="work-form__field">
                <label class="form-label">作品名 <span class="required">*</span></label>
                <input type="text" id="form-work-title" class="form-input" required>
            </div>
            <div class="work-form__field">
                <label class="form-label">原題</label>
                <input type="text" id="form-title-original" class="form-input">
            </div>
            <div class="work-form__field">
                <label class="form-label">タイプ <span class="required">*</span></label>
                <select id="form-type" class="form-input">
                    <option value="movie">映画</option>
                    <option value="anime">アニメ</option>
                    <option value="drama">ドラマ</option>
                    <option value="novel">小説</option>
                    <option value="game">ゲーム</option>
                    <option value="other">その他</option>
                </select>
            </div>
            <div class="work-form__field">
                <label class="form-label">年</label>
                <input type="number" id="form-year" class="form-input" min="1900" max="2100">
            </div>
            <div class="work-form__field">
                <label class="form-label">国</label>
                <input type="text" id="form-country" class="form-input">
            </div>
            <div class="work-form__field">
                <label class="form-label">外部URL</label>
                <input type="url" id="form-external-url" class="form-input" placeholder="https://...">
            </div>
            <div class="work-form__field work-form__field--full">
                <label class="form-label">説明</label>
                <textarea id="form-description" class="form-input" rows="3"></textarea>
            </div>
        </div>
        <div id="form-errors" class="work-form__errors" style="display:none;"></div>
        <div class="work-form__actions">
            <button class="btn btn--primary" onclick="saveWork()">保存</button>
            <button class="btn btn--secondary" onclick="hideForm()">キャンセル</button>
        </div>
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
                    <th>操作</th>
                </tr>
            </thead>
            <tbody id="works-body">
                <tr><td colspan="6" class="admin-table__empty">読み込み中...</td></tr>
            </tbody>
        </table>
    </div>

@push('styles')
<style>
    .admin-filters { margin-bottom: var(--space-4); display: flex; gap: var(--space-2); flex-wrap: wrap; align-items: center; }
    .admin-table-container { overflow-x: auto; }
    .admin-table { width: 100%; border-collapse: collapse; background: white; border-radius: var(--border-radius); overflow: hidden; }
    .admin-table th { background: var(--color-bg-tertiary); padding: var(--space-3) var(--space-4); text-align: left; font-size: var(--font-size-sm); font-weight: 600; }
    .admin-table td { padding: var(--space-3) var(--space-4); border-top: 1px solid var(--color-border-light); font-size: var(--font-size-sm); }
    .admin-table__empty { text-align: center; color: var(--color-text-secondary); padding: var(--space-8) !important; }
    .badge--approved { background: #D1FAE5; color: #059669; }
    .badge--pending { background: #FEF3C7; color: #D97706; }
    .badge--rejected { background: #FEE2E2; color: #DC2626; }
    .work-form { margin-bottom: var(--space-4); padding: var(--space-5); }
    .work-form__title { margin-bottom: var(--space-4); font-size: var(--font-size-lg); font-weight: 600; }
    .work-form__grid { display: grid; grid-template-columns: 1fr 1fr; gap: var(--space-3); }
    .work-form__field--full { grid-column: 1 / -1; }
    .work-form__actions { margin-top: var(--space-4); display: flex; gap: var(--space-2); }
    .work-form__errors { margin-top: var(--space-3); padding: var(--space-3); background: #FEE2E2; color: #DC2626; border-radius: var(--border-radius); font-size: var(--font-size-sm); }
    .form-label { display: block; font-size: var(--font-size-sm); font-weight: 500; margin-bottom: var(--space-1); }
    .required { color: #DC2626; }
    .admin-table .btn { padding: 2px 10px; font-size: var(--font-size-xs); }
    .btn--danger { background: #DC2626; color: white; border: none; border-radius: var(--border-radius); cursor: pointer; }
    .btn--danger:hover { background: #B91C1C; }
    .quotes-count-link { color: var(--color-primary, #3498DB); text-decoration: underline; cursor: pointer; font-weight: 600; }
    .quotes-count-link:hover { opacity: 0.8; }
    .quotes-detail { background: var(--color-bg-secondary, #F8F9FA); }
    .quotes-detail td { padding: 0 !important; }
    .quotes-detail__inner { padding: var(--space-3) var(--space-4); }
    .quotes-detail__list { list-style: none; padding: 0; margin: 0; display: flex; flex-direction: column; gap: var(--space-3); }
    .quote-item { background: white; border-radius: var(--border-radius); padding: var(--space-3) var(--space-4); border: 1px solid var(--color-border-light, #eee); }
    .quote-item__text { font-size: var(--font-size-sm); line-height: 1.6; margin-bottom: var(--space-1); }
    .quote-item__meta { font-size: var(--font-size-xs); color: var(--color-text-secondary, #888); display: flex; gap: var(--space-3); flex-wrap: wrap; align-items: center; }
    .quotes-detail__empty { font-size: var(--font-size-sm); color: var(--color-text-secondary, #888); padding: var(--space-2) 0; }
    .quotes-detail__header { font-weight: 600; font-size: var(--font-size-sm); margin-bottom: var(--space-2); }
</style>
@endpush

@push('scripts')
<script>
    const headers = {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-XSRF-TOKEN': decodeURIComponent(document.cookie.match(/XSRF-TOKEN=([^;]+)/)?.[1] || '')
    };
    let searchTimer;
    let allWorks = [];
    let openQuotesId = null;

    function debounceSearch() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(loadWorks, 300);
    }

    function showForm(work = null) {
        document.getElementById('work-form').style.display = 'block';
        document.getElementById('form-errors').style.display = 'none';
        if (work) {
            document.getElementById('form-title').textContent = '作品を編集';
            document.getElementById('form-id').value = work.id;
            document.getElementById('form-work-title').value = work.title || '';
            document.getElementById('form-title-original').value = work.title_original || '';
            document.getElementById('form-type').value = work.type || 'movie';
            document.getElementById('form-year').value = work.year || '';
            document.getElementById('form-country').value = work.country || '';
            document.getElementById('form-description').value = work.description || '';
            document.getElementById('form-external-url').value = work.external_url || '';
        } else {
            document.getElementById('form-title').textContent = '新規作品';
            document.getElementById('form-id').value = '';
            document.getElementById('form-work-title').value = '';
            document.getElementById('form-title-original').value = '';
            document.getElementById('form-type').value = 'movie';
            document.getElementById('form-year').value = '';
            document.getElementById('form-country').value = '';
            document.getElementById('form-description').value = '';
            document.getElementById('form-external-url').value = '';
        }
        document.getElementById('form-work-title').focus();
    }

    function hideForm() {
        document.getElementById('work-form').style.display = 'none';
    }

    function saveWork() {
        const id = document.getElementById('form-id').value;
        const body = {
            title: document.getElementById('form-work-title').value,
            title_original: document.getElementById('form-title-original').value || null,
            type: document.getElementById('form-type').value,
            year: document.getElementById('form-year').value ? parseInt(document.getElementById('form-year').value) : null,
            country: document.getElementById('form-country').value || null,
            description: document.getElementById('form-description').value || null,
            external_url: document.getElementById('form-external-url').value || null,
        };

        const url = id ? `/api/v1/admin/works/${id}` : '/api/v1/admin/works';
        const method = id ? 'PUT' : 'POST';

        fetch(url, { method, credentials: 'include', headers, body: JSON.stringify(body) })
            .then(r => {
                if (!r.ok) return r.json().then(d => Promise.reject(d));
                return r.json();
            })
            .then(() => {
                hideForm();
                fetchWorks();
            })
            .catch(err => {
                const errDiv = document.getElementById('form-errors');
                if (err.errors) {
                    errDiv.innerHTML = Object.values(err.errors).flat().join('<br>');
                } else {
                    errDiv.textContent = err.message || 'エラーが発生しました。';
                }
                errDiv.style.display = 'block';
            });
    }

    function editWork(id) {
        const work = allWorks.find(w => w.id === id);
        if (work) showForm(work);
    }

    function deleteWork(id) {
        if (!confirm('この作品を削除しますか？')) return;
        fetch(`/api/v1/admin/works/${id}`, { method: 'DELETE', credentials: 'include', headers })
            .then(r => {
                if (!r.ok) return r.json().then(d => Promise.reject(d));
                return r.json();
            })
            .then(() => fetchWorks())
            .catch(err => alert(err.message || '削除に失敗しました。'));
    }

    function approveWork(id) {
        fetch(`/api/v1/admin/works/${id}/approve`, { method: 'PUT', credentials: 'include', headers })
            .then(r => r.json())
            .then(() => fetchWorks());
    }

    function toggleQuotes(workId) {
        const detailRow = document.getElementById(`quotes-${workId}`);
        if (detailRow) {
            detailRow.remove();
            openQuotesId = null;
            return;
        }
        // 他の開いている詳細を閉じる
        if (openQuotesId !== null) {
            const prev = document.getElementById(`quotes-${openQuotesId}`);
            if (prev) prev.remove();
        }
        openQuotesId = workId;

        const workRow = document.getElementById(`work-row-${workId}`);
        const tr = document.createElement('tr');
        tr.id = `quotes-${workId}`;
        tr.className = 'quotes-detail';
        tr.innerHTML = `<td colspan="6"><div class="quotes-detail__inner"><div class="quotes-detail__header">セリフ一覧</div><div>読み込み中...</div></div></td>`;
        workRow.after(tr);

        fetch(`/api/v1/admin/works/${workId}/quotes`, { credentials: 'include', headers: { 'Accept': 'application/json' } })
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(res => {
                const quotes = res.data || [];
                const inner = tr.querySelector('.quotes-detail__inner');
                if (!quotes.length) {
                    inner.innerHTML = '<div class="quotes-detail__header">セリフ一覧</div><div class="quotes-detail__empty">この作品にセリフはまだありません。</div>';
                    return;
                }
                const statusLabel = s => s === 'approved' ? '<span class="badge badge--approved">承認済</span>'
                    : s === 'rejected' ? '<span class="badge badge--rejected">却下</span>'
                    : '<span class="badge badge--pending">承認待ち</span>';

                inner.innerHTML = `
                    <div class="quotes-detail__header">セリフ一覧 (${quotes.length}件)</div>
                    <ul class="quotes-detail__list">
                        ${quotes.map(q => `
                            <li class="quote-item">
                                <div class="quote-item__text">${escHtml(q.quote_text)}</div>
                                <div class="quote-item__meta">
                                    ${q.character_name ? '<span>' + escHtml(q.character_name) + '</span>' : ''}
                                    <span>${q.user?.display_name || q.user?.username || '-'}</span>
                                    <span>${q.location?.name || '-'}</span>
                                    ${statusLabel(q.status)}
                                    <span>${q.likes_count ?? 0} いいね</span>
                                </div>
                                ${q.scene_description ? '<div class="quote-item__meta" style="margin-top:4px;">' + escHtml(q.scene_description) + '</div>' : ''}
                            </li>
                        `).join('')}
                    </ul>
                `;
            })
            .catch(() => {
                const inner = tr.querySelector('.quotes-detail__inner');
                inner.innerHTML = '<div class="quotes-detail__empty">セリフの読み込みに失敗しました。</div>';
            });
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
            tbody.innerHTML = '<tr><td colspan="6" class="admin-table__empty">作品が見つかりません。</td></tr>';
            return;
        }
        openQuotesId = null;
        tbody.innerHTML = works.map(w => `
            <tr id="work-row-${w.id}">
                <td><strong>${escHtml(w.title)}</strong>${w.title_original ? '<br><small>' + escHtml(w.title_original) + '</small>' : ''}</td>
                <td>${escHtml(w.type)}</td>
                <td>${w.year ?? '-'}</td>
                <td>
                    ${w.quotes_count > 0
                        ? `<span class="quotes-count-link" onclick="toggleQuotes(${w.id})">${w.quotes_count}件</span>`
                        : '<span style="color:var(--color-text-secondary)">0</span>'}
                </td>
                <td>
                    <button class="badge ${w.is_approved ? 'badge--approved' : 'badge--pending'}" onclick="approveWork(${w.id})" style="cursor:pointer;border:none;">
                        ${w.is_approved ? '承認済' : '未承認'}
                    </button>
                </td>
                <td>
                    <button class="btn btn--secondary" onclick="editWork(${w.id})">編集</button>
                    <button class="btn btn--danger" onclick="deleteWork(${w.id})">削除</button>
                </td>
            </tr>
        `).join('');
    }

    function escHtml(str) {
        const d = document.createElement('div');
        d.textContent = str;
        return d.innerHTML;
    }

    function fetchWorks() {
        fetch('/api/v1/admin/works', { credentials: 'include', headers: { 'Accept': 'application/json' } })
            .then(r => r.ok ? r.json() : Promise.reject())
            .then(res => {
                allWorks = res.data || [];
                loadWorks();
            })
            .catch(() => {
                document.getElementById('works-body').innerHTML = '<tr><td colspan="6" class="admin-table__empty">読み込みに失敗しました。</td></tr>';
            });
    }

    fetchWorks();
</script>
@endpush
@endsection
