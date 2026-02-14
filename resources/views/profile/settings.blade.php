@extends('layouts.app')

@section('title', 'プロフィール設定 - LoveAtlas')

@section('content')
<div class="settings">
    <div class="settings__header">
        <h1 class="settings__title">プロフィール設定</h1>
        <a href="{{ route('profile.index') }}" class="btn btn--secondary">← プロフィールに戻る</a>
    </div>

    @if(session('success'))
        <div class="settings__alert settings__alert--success">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="settings__form card">
        @csrf
        @method('PUT')

        {{-- アバター --}}
        <div class="form-group">
            <label class="form-label">アバター画像</label>
            <div class="settings__avatar-section">
                <div class="settings__avatar-preview">
                    @if($user->avatar_path)
                        <img src="{{ asset('storage/' . $user->avatar_path) }}" alt="{{ $user->display_name }}">
                    @else
                        <div class="profile__avatar-placeholder profile__avatar-placeholder--lg">
                            {{ mb_substr($user->display_name, 0, 1) }}
                        </div>
                    @endif
                </div>
                <div class="settings__avatar-upload">
                    <input type="file" name="avatar" id="avatar" accept="image/jpeg,image/png,image/webp" class="form-input">
                    <p class="settings__help">JPG, PNG, WebP (最大 2MB)</p>
                </div>
            </div>
            @error('avatar')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- 表示名 --}}
        <div class="form-group">
            <label for="display_name" class="form-label">表示名 <span class="form-required">*</span></label>
            <input type="text" name="display_name" id="display_name"
                   value="{{ old('display_name', $user->display_name) }}"
                   class="form-input" required maxlength="100">
            @error('display_name')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- 自己紹介 --}}
        <div class="form-group">
            <label for="bio" class="form-label">自己紹介</label>
            <textarea name="bio" id="bio" rows="4" class="form-input"
                      maxlength="500" placeholder="自己紹介を入力 (最大500文字)">{{ old('bio', $user->bio) }}</textarea>
            @error('bio')
                <p class="form-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- メール (読み取り専用) --}}
        <div class="form-group">
            <label class="form-label">メールアドレス</label>
            <input type="email" value="{{ $user->email }}" class="form-input" disabled>
            <p class="settings__help">メールアドレスの変更はサポートへお問い合わせください</p>
        </div>

        {{-- ユーザー名 (読み取り専用) --}}
        <div class="form-group">
            <label class="form-label">ユーザー名</label>
            <input type="text" value="{{ $user->username }}" class="form-input" disabled>
        </div>

        <div class="settings__actions">
            <button type="submit" class="btn btn--primary">保存する</button>
        </div>
    </form>
</div>
@endsection
