@extends('layouts.admin')

@section('page-title', 'تعديل مفهوم')

@section('content')
<style>
.form-card {
    background: white;
    border-radius: 12px;
    padding: 32px;
    max-width: 700px;
    margin: 0 auto;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 20px;
}

.form-group {
    display: flex;
    flex-direction: column;
    gap: 8px;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-label {
    font-weight: 600;
    color: #334155;
    font-size: 14px;
}

.form-label.required::after {
    content: " *";
    color: #dc2626;
}

.form-input,
.form-select,
.form-textarea {
    padding: 12px 16px;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    font-size: 14px;
}

.form-textarea {
    min-height: 120px;
    resize: vertical;
}

.btn {
    padding: 12px 24px;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    border: none;
    text-decoration: none;
}

.btn-primary { background: var(--color-primary); color: white; }
.btn-secondary { background: #e2e8f0; color: #475569; }

.form-actions {
    display: flex;
    gap: 12px;
    justify-content: flex-end;
    margin-top: 24px;
}
</style>

<div class="form-card">
    <h2 style="margin-bottom: 24px;">✏️ تعديل مفهوم: {{ $concept->name }}</h2>

    <form method="POST" action="{{ route('admin.concepts.update', $concept) }}">
        @csrf
        @method('PUT')

        <div class="form-grid">
            <div class="form-group full-width">
                <label class="form-label required">القيمة الأساسية</label>
                <select name="value_id" class="form-select" required>
                    <option value="">اختر القيمة</option>
                    @foreach($values as $value)
                    <option value="{{ $value->id }}" {{ old('value_id', $concept->value_id) == $value->id ? 'selected' : '' }}>
                        {{ $value->icon }} {{ $value->name }}
                    </option>
                    @endforeach
                </select>
                @error('value_id')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group full-width">
                <label class="form-label required">اسم المفهوم</label>
                <input type="text" name="name" class="form-input" value="{{ old('name', $concept->name) }}" required>
                @error('name')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group full-width">
                <label class="form-label">الوصف</label>
                <textarea name="description" class="form-textarea">{{ old('description', $concept->description) }}</textarea>
                @error('description')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>

            <div class="form-group full-width">
                <label class="form-label">الترتيب</label>
                <input type="number" name="order" class="form-input" value="{{ old('order', $concept->order) }}" min="0">
                @error('order')
                    <span style="color: #dc2626; font-size: 13px;">{{ $message }}</span>
                @enderror
            </div>
        </div>

        <div class="form-actions">
            <button type="submit" class="btn btn-primary">💾 تحديث المفهوم</button>
            <a href="{{ route('admin.concepts.index') }}" class="btn btn-secondary">❌ إلغاء</a>
        </div>
    </form>
</div>

@endsection
