<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>رسالة تواصل جديدة - منصة قيمّ</title>
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f4f4f4;
            direction: rtl;
            margin: 0;
            padding: 20px;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #10B981;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #1A1A1A;
            margin: 0;
            font-size: 24px;
        }
        .content {
            color: #333;
            line-height: 1.8;
        }
        .info-row {
            margin-bottom: 15px;
            padding: 10px;
            background: #FAFAFA;
            border-radius: 4px;
        }
        .info-label {
            font-weight: bold;
            color: #10B981;
        }
        .message-box {
            background: #F9FAFB;
            padding: 15px;
            border-right: 4px solid #10B981;
            margin: 20px 0;
        }
        .footer {
            text-align: center;
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #E5E7EB;
            color: #7A7A7A;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🌟 رسالة تواصل جديدة</h1>
        </div>

        <div class="content">
            <div class="info-row">
                <span class="info-label">الاسم:</span>
                <span>{{ $data['full_name'] }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">البريد الإلكتروني:</span>
                <span>{{ $data['email'] }}</span>
            </div>

            <div class="info-row">
                <span class="info-label">نوع المستخدم:</span>
                <span>
                    @switch($data['user_type'])
                        @case('school') مدرسة @break
                        @case('teacher') معلم @break
                        @case('parent') ولي أمر @break
                        @case('student') طالب @break
                        @case('institution') جهة تعليمية @break
                        @default {{ $data['user_type'] }}
                    @endswitch
                </span>
            </div>

            <div class="message-box">
                <p><strong>الرسالة:</strong></p>
                <p>{{ $data['message'] }}</p>
            </div>

            <div class="info-row">
                <span class="info-label">عنوان IP:</span>
                <span>{{ $data['ip_address'] }}</span>
            </div>
        </div>

        <div class="footer">
            <p>منصة قيمّ - نظام إدارة القيم المدرسية</p>
            <p>تم إرسال هذا البريد تلقائياً، يرجى عدم الرد عليه</p>
        </div>
    </div>
</body>
</html>
