@extends('layouts.admin')

@section('title', 'ダッシュボード')

@section('content')
    <div class="admin-stats">
        <div class="admin-stats__grid">
            <div class="card admin-stats__card">
                <div class="admin-stats__label">承認待ちセリフ</div>
                <div class="admin-stats__value" id="stat-pending">-</div>
                <a href="{{ route('admin.quotes.index') }}" class="admin-stats__link">確認する</a>
            </div>
            <div class="card admin-stats__card">
                <div class="admin-stats__label">未対応の通報</div>
                <div class="admin-stats__value" id="stat-reports">-</div>
                <a href="{{ route('admin.reports.index') }}" class="admin-stats__link">確認する</a>
            </div>
            <div class="card admin-stats__card">
                <div class="admin-stats__label">総ユーザー数</div>
                <div class="admin-stats__value" id="stat-users">-</div>
                <a href="{{ route('admin.users.index') }}" class="admin-stats__link">管理する</a>
            </div>
            <div class="card admin-stats__card">
                <div class="admin-stats__label">承認済みセリフ</div>
                <div class="admin-stats__value" id="stat-quotes">-</div>
            </div>
        </div>
    </div>

@push('styles')
<style>
    .admin-stats__grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
        gap: var(--space-4);
    }
    .admin-stats__card {
        text-align: center;
        padding: var(--space-6);
    }
    .admin-stats__label {
        font-size: var(--font-size-sm);
        color: var(--color-text-secondary);
        margin-bottom: var(--space-2);
    }
    .admin-stats__value {
        font-size: var(--font-size-3xl);
        font-weight: 700;
        color: var(--color-text);
        margin-bottom: var(--space-2);
    }
    .admin-stats__link {
        font-size: var(--font-size-xs);
        font-weight: 600;
    }
</style>
@endpush

@push('scripts')
<script>
    fetch('/api/v1/admin/stats', { credentials: 'include', headers: { 'Accept': 'application/json' } })
        .then(r => r.json())
        .then(data => {
            document.getElementById('stat-pending').textContent = data.quotes?.pending ?? 0;
            document.getElementById('stat-reports').textContent = data.reports?.open ?? 0;
            document.getElementById('stat-users').textContent = data.users?.total ?? 0;
            document.getElementById('stat-quotes').textContent = data.quotes?.approved ?? 0;
        })
        .catch(() => {});
</script>
@endpush
@endsection
