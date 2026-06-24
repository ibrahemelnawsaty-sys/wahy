@extends('layouts.admin')

@section('title', 'تفاصيل الرسالة')

@section('content')
<div class="admin-container">
    <!-- Header -->
    <div class="admin-page-header">
        <div>
            <h1 class="admin-page-title">📨 تفاصيل الرسالة #{{ $message->id }}</h1>
            <p class="admin-page-subtitle">عرض تفاصيل الرسالة والسياق الكامل للمحادثة</p>
        </div>
        <div style="display: flex; gap: 10px;">
            <a href="{{ route('admin.messages-log.conversation', $message->conversation_id) }}" class="btn btn-secondary">
                💬 عرض المحادثة الكاملة
            </a>
            <a href="{{ route('admin.messages-log.index') }}" class="btn btn-primary">
                ← العودة للقائمة
            </a>
        </div>
    </div>

    <!-- Message Details Card -->
    <div class="admin-card" style="margin-bottom: 20px;">
        <div class="admin-card-header">
            <h3>📋 معلومات الرسالة</h3>
        </div>
        <div class="admin-card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <!-- Sender Info -->
                <div style="padding: 20px; background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%); border-radius: 10px; border-right: 4px solid #667eea;">
                    <div style="font-size: 12px; color: #666; margin-bottom: 8px; font-weight: 600;">المرسل</div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 18px;">
                            {{ mb_substr($message->sender->name ?? 'غ', 0, 1) }}
                        </div>
                        <div>
                            <div style="font-weight: 600; font-size: 16px;">{{ $message->sender->name ?? 'غير معروف' }}</div>
                            <div style="font-size: 13px; color: #666;">{{ $message->sender->email ?? '-' }}</div>
                            <div style="font-size: 12px; color: #999; margin-top: 2px;">
                                <span style="background: #667eea; color: white; padding: 2px 8px; border-radius: 10px;">{{ $message->sender->role ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Receiver Info -->
                <div style="padding: 20px; background: linear-gradient(135deg, #f093fb15 0%, #f5576c15 100%); border-radius: 10px; border-right: 4px solid #f093fb;">
                    <div style="font-size: 12px; color: #666; margin-bottom: 8px; font-weight: 600;">المستقبل</div>
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 18px;">
                            {{ mb_substr($message->receiver->name ?? 'غ', 0, 1) }}
                        </div>
                        <div>
                            <div style="font-weight: 600; font-size: 16px;">{{ $message->receiver->name ?? 'غير معروف' }}</div>
                            <div style="font-size: 13px; color: #666;">{{ $message->receiver->email ?? '-' }}</div>
                            <div style="font-size: 12px; color: #999; margin-top: 2px;">
                                <span style="background: #f093fb; color: white; padding: 2px 8px; border-radius: 10px;">{{ $message->receiver->role ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Info -->
                <div style="padding: 20px; background: linear-gradient(135deg, #43e97b15 0%, #38f9d715 100%); border-radius: 10px; border-right: 4px solid #43e97b;">
                    <div style="font-size: 12px; color: #666; margin-bottom: 8px; font-weight: 600;">حالة الرسالة</div>
                    <div style="margin-bottom: 12px;">
                        @if($message->is_read)
                            <span style="display: inline-flex; align-items: center; gap: 6px; background: #d4edda; color: #155724; padding: 8px 16px; border-radius: 20px; font-weight: 600;">
                                <span style="font-size: 18px;">✓</span> مقروءة
                            </span>
                        @else
                            <span style="display: inline-flex; align-items: center; gap: 6px; background: #fff3cd; color: #856404; padding: 8px 16px; border-radius: 20px; font-weight: 600;">
                                <span style="font-size: 18px;">⏳</span> غير مقروءة
                            </span>
                        @endif
                    </div>
                    <div style="font-size: 13px; color: #666;">
                        <div style="margin-bottom: 4px;">
                            <strong>تاريخ الإرسال:</strong><br>
                            {{ $message->created_at->format('Y-m-d H:i:s') }}
                        </div>
                        @if($message->read_at)
                            <div>
                                <strong>تاريخ القراءة:</strong><br>
                                {{ $message->read_at->format('Y-m-d H:i:s') }}
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Message Content -->
    <div class="admin-card" style="margin-bottom: 20px;">
        <div class="admin-card-header">
            <h3>💬 محتوى الرسالة</h3>
        </div>
        <div class="admin-card-body">
            <div style="padding: 20px; background: #f8f9fa; border-radius: 10px; border-right: 4px solid #667eea; min-height: 100px; white-space: pre-wrap; line-height: 1.6;">
                {{ $message->message }}
            </div>
        </div>
    </div>

    <!-- Conversation Context -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>🔄 سياق المحادثة ({{ $conversationMessages->count() }} رسالة)</h3>
        </div>
        <div class="admin-card-body">
            <div style="max-height: 600px; overflow-y: auto;">
                @foreach($conversationMessages as $msg)
                    <div style="padding: 15px; margin-bottom: 10px; border-radius: 10px; {{ $msg->id === $message->id ? 'background: #fff3cd; border: 2px solid #ffc107;' : 'background: #f8f9fa;' }}">
                        <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 10px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="width: 36px; height: 36px; border-radius: 50%; background: {{ $msg->sender_id === $message->sender_id ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' : 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)' }}; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 14px;">
                                    {{ mb_substr($msg->sender->name ?? 'غ', 0, 1) }}
                                </div>
                                <div>
                                    <div style="font-weight: 600;">{{ $msg->sender->name ?? 'غير معروف' }}</div>
                                    <div style="font-size: 12px; color: #666;">{{ $msg->created_at->format('Y-m-d H:i') }}</div>
                                </div>
                            </div>
                            <div style="display: flex; gap: 8px; align-items: center;">
                                @if($msg->id === $message->id)
                                    <span style="background: #ffc107; color: white; padding: 4px 10px; border-radius: 12px; font-size: 11px; font-weight: 600;">الرسالة الحالية</span>
                                @endif
                                @if($msg->is_read)
                                    <span style="color: #28a745; font-size: 18px;" title="مقروءة">✓</span>
                                @else
                                    <span style="color: #ffc107; font-size: 18px;" title="غير مقروءة">⏳</span>
                                @endif
                            </div>
                        </div>
                        <div style="padding-right: 46px; white-space: pre-wrap; line-height: 1.5;">
                            {{ $msg->message }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Actions -->
    <div style="margin-top: 20px; display: flex; gap: 10px; justify-content: center;">
        <a href="{{ route('admin.messages-log.conversation', $message->conversation_id) }}" class="btn btn-primary">
            💬 عرض المحادثة الكاملة
        </a>
        <a href="{{ route('admin.messages-log.index') }}" class="btn btn-secondary">
            📋 العودة للسجل
        </a>
        <form action="{{ route('admin.messages-log.destroy', $message->id) }}" method="POST" style="display: inline;" onsubmit="return confirm('هل أنت متأكد من حذف هذه الرسالة؟ هذا الإجراء لا يمكن التراجع عنه.')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-danger">
                🗑️ حذف الرسالة
            </button>
        </form>
    </div>
</div>
@endsection
