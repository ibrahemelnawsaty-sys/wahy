@extends('layouts.admin')

@section('title', 'عرض المحادثة')

@section('content')
<div class="admin-container">
    <!-- Header -->
    <div class="admin-page-header">
        <div>
            <h1 class="admin-page-title">💬 المحادثة بين {{ $conversation->user1->name }} و {{ $conversation->user2->name }}</h1>
            <p class="admin-page-subtitle">عرض جميع الرسائل في هذه المحادثة</p>
        </div>
        <div>
            <a href="{{ route('admin.messages-log.index') }}" class="btn btn-primary">
                ← العودة للسجل
            </a>
        </div>
    </div>

    <!-- Conversation Stats -->
    <div class="admin-stats-grid" style="margin-bottom: 30px; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));">
        <div class="admin-stat-card">
            <div class="admin-stat-icon" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                💬
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-value">{{ $stats['total_messages'] }}</div>
                <div class="admin-stat-label">إجمالي الرسائل</div>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                📬
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-value">{{ $stats['unread_messages'] }}</div>
                <div class="admin-stat-label">رسائل غير مقروءة</div>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon" style="background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%);">
                📅
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-value">{{ $stats['first_message_date'] ? $stats['first_message_date']->format('Y-m-d') : '-' }}</div>
                <div class="admin-stat-label">أول رسالة</div>
            </div>
        </div>

        <div class="admin-stat-card">
            <div class="admin-stat-icon" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);">
                🕐
            </div>
            <div class="admin-stat-content">
                <div class="admin-stat-value">{{ $stats['last_message_date'] ? $stats['last_message_date']->format('Y-m-d') : '-' }}</div>
                <div class="admin-stat-label">آخر رسالة</div>
            </div>
        </div>
    </div>

    <!-- Participants Info -->
    <div class="admin-card" style="margin-bottom: 20px;">
        <div class="admin-card-header">
            <h3>👥 المشاركون في المحادثة</h3>
        </div>
        <div class="admin-card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                <!-- User 1 -->
                <div style="padding: 20px; background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%); border-radius: 10px; border-right: 4px solid #667eea;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 24px;">
                            {{ mb_substr($conversation->user1->name, 0, 1, "UTF-8") }}
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 600; font-size: 18px; margin-bottom: 4px;">{{ $conversation->user1->name }}</div>
                            <div style="font-size: 13px; color: #666; margin-bottom: 4px;">{{ $conversation->user1->email }}</div>
                            <div>
                                <span style="background: #667eea; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                    {{ $conversation->user1->role }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e0e0e0;">
                        <div style="font-size: 13px; color: #666;">
                            <strong>الرسائل المرسلة:</strong> {{ $messages->where('sender_id', $conversation->user1->id)->count() }}
                        </div>
                    </div>
                </div>

                <!-- User 2 -->
                <div style="padding: 20px; background: linear-gradient(135deg, #f093fb15 0%, #f5576c15 100%); border-radius: 10px; border-right: 4px solid #f093fb;">
                    <div style="display: flex; align-items: center; gap: 15px;">
                        <div style="width: 60px; height: 60px; border-radius: 50%; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 24px;">
                            {{ mb_substr($conversation->user2->name, 0, 1, "UTF-8") }}
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 600; font-size: 18px; margin-bottom: 4px;">{{ $conversation->user2->name }}</div>
                            <div style="font-size: 13px; color: #666; margin-bottom: 4px;">{{ $conversation->user2->email }}</div>
                            <div>
                                <span style="background: #f093fb; color: white; padding: 4px 12px; border-radius: 12px; font-size: 12px; font-weight: 600;">
                                    {{ $conversation->user2->role }}
                                </span>
                            </div>
                        </div>
                    </div>
                    <div style="margin-top: 15px; padding-top: 15px; border-top: 1px solid #e0e0e0;">
                        <div style="font-size: 13px; color: #666;">
                            <strong>الرسائل المرسلة:</strong> {{ $messages->where('sender_id', $conversation->user2->id)->count() }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Messages Timeline -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>📜 سجل الرسائل ({{ $messages->count() }})</h3>
        </div>
        <div class="admin-card-body">
            <div style="max-height: 800px; overflow-y: auto; padding: 20px;">
                @if($messages->count() > 0)
                    @foreach($messages as $message)
                        <div style="display: flex; gap: 15px; margin-bottom: 20px; {{ $message->sender_id === $conversation->user1->id ? 'flex-direction: row;' : 'flex-direction: row-reverse;' }}">
                            <!-- Avatar -->
                            <div style="flex-shrink: 0;">
                                <div style="width: 48px; height: 48px; border-radius: 50%; background: {{ $message->sender_id === $conversation->user1->id ? 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)' : 'linear-gradient(135deg, #f093fb 0%, #f5576c 100%)' }}; display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 18px;">
                                    {{ mb_substr($message->sender->name, 0, 1, "UTF-8") }}
                                </div>
                            </div>

                            <!-- Message Content -->
                            <div style="flex: 1; max-width: 70%;">
                                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 6px; {{ $message->sender_id === $conversation->user1->id ? '' : 'flex-direction: row-reverse;' }}">
                                    <span style="font-weight: 600; font-size: 14px;">{{ $message->sender->name }}</span>
                                    <span style="font-size: 12px; color: #999;">{{ $message->created_at->format('Y-m-d H:i') }}</span>
                                    @if($message->is_read)
                                        <span style="color: #28a745; font-size: 14px;" title="مقروءة">✓✓</span>
                                    @else
                                        <span style="color: #999; font-size: 14px;" title="غير مقروءة">✓</span>
                                    @endif
                                </div>
                                <div style="padding: 15px 20px; background: {{ $message->sender_id === $conversation->user1->id ? '#667eea' : '#f093fb' }}; color: white; border-radius: {{ $message->sender_id === $conversation->user1->id ? '0 15px 15px 15px' : '15px 0 15px 15px' }}; white-space: pre-wrap; line-height: 1.6; box-shadow: 0 2px 8px rgba(0,0,0,0.1);">
                                    {{ $message->message }}
                                </div>
                                <div style="margin-top: 6px; font-size: 11px; color: #999; {{ $message->sender_id === $conversation->user1->id ? 'text-align: left;' : 'text-align: right;' }}">
                                    <a href="{{ route('admin.messages-log.show', $message->id) }}" style="color: #667eea; text-decoration: none;">
                                        عرض التفاصيل →
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div style="text-align: center; padding: 60px 20px; color: #999;">
                        <div style="font-size: 48px; margin-bottom: 15px;">📭</div>
                        <p style="font-size: 18px; margin: 0;">لا توجد رسائل في هذه المحادثة</p>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

<style>
/* Custom scrollbar for messages */
.admin-card-body > div::-webkit-scrollbar {
    width: 8px;
}

.admin-card-body > div::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 10px;
}

.admin-card-body > div::-webkit-scrollbar-thumb {
    background: #667eea;
    border-radius: 10px;
}

.admin-card-body > div::-webkit-scrollbar-thumb:hover {
    background: #5568d3;
}
</style>
@endsection
