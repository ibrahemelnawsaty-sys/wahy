<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'الموقع تحت الصيانة' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'IBM Plex Sans Arabic', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
            position: relative;
            overflow: hidden;
        }
        
        /* Background Animation */
        body::before {
            content: '';
            position: absolute;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle at 20% 50%, rgba(255, 255, 255, 0.1) 0%, transparent 50%),
                        radial-gradient(circle at 80% 80%, rgba(255, 255, 255, 0.1) 0%, transparent 50%);
            animation: moveBackground 20s ease-in-out infinite;
        }
        
        @keyframes moveBackground {
            0%, 100% { transform: translate(0, 0); }
            50% { transform: translate(-50px, -50px); }
        }
        
        .maintenance-container {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 24px;
            padding: 60px 40px;
            max-width: 600px;
            width: 100%;
            text-align: center;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            position: relative;
            z-index: 1;
            animation: slideUp 0.6s ease-out;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .maintenance-icon {
            font-size: 80px;
            margin-bottom: 24px;
            animation: bounce 2s ease-in-out infinite;
        }
        
        @keyframes bounce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }
        
        .maintenance-title {
            font-size: 32px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 16px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
        
        .maintenance-message {
            font-size: 18px;
            color: #64748b;
            line-height: 1.8;
            margin-bottom: 32px;
        }
        
        .maintenance-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(102, 126, 234, 0.1);
            padding: 12px 24px;
            border-radius: 12px;
            font-size: 14px;
            color: #667eea;
            font-weight: 600;
            margin-bottom: 32px;
        }
        
        .status-dot {
            width: 8px;
            height: 8px;
            background: #667eea;
            border-radius: 50%;
            animation: pulse 2s ease-in-out infinite;
        }
        
        @keyframes pulse {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.5; transform: scale(1.2); }
        }
        
        .maintenance-links {
            display: flex;
            gap: 12px;
            justify-content: center;
            flex-wrap: wrap;
        }
        
        .maintenance-btn {
            padding: 14px 28px;
            border-radius: 12px;
            font-size: 15px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            box-shadow: 0 4px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.5);
        }
        
        .btn-outline {
            background: transparent;
            color: #667eea;
            border: 2px solid #667eea;
        }
        
        .btn-outline:hover {
            background: rgba(102, 126, 234, 0.1);
            transform: translateY(-2px);
        }
        
        .maintenance-footer {
            margin-top: 40px;
            padding-top: 24px;
            border-top: 1px solid #e2e8f0;
        }
        
        .maintenance-footer p {
            font-size: 14px;
            color: #94a3b8;
        }
        
        @media (max-width: 640px) {
            .maintenance-container {
                padding: 40px 24px;
            }
            
            .maintenance-icon {
                font-size: 64px;
            }
            
            .maintenance-title {
                font-size: 24px;
            }
            
            .maintenance-message {
                font-size: 16px;
            }
            
            .maintenance-links {
                flex-direction: column;
            }
            
            .maintenance-btn {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>
<body>
    <div class="maintenance-container">
        <div class="maintenance-icon">🔧</div>
        
        <h1 class="maintenance-title">{{ $title ?? 'الموقع تحت الصيانة' }}</h1>
        
        <div class="maintenance-status">
            <span class="status-dot"></span>
            <span>جاري العمل على التحسينات</span>
        </div>
        
        <p class="maintenance-message">
            {{ $message ?? 'نعتذر عن الإزعاج. نقوم حالياً بإجراء بعض التحسينات والصيانة لتقديم تجربة أفضل لك.' }}
        </p>
        
        <div class="maintenance-links">
            <a href="/login" class="maintenance-btn btn-primary">
                <span>🔐</span>
                <span>تسجيل دخول الإدارة</span>
            </a>
            <a href="mailto:{{ setting('contact_email', 'info@qiyamm.sa') }}" class="maintenance-btn btn-outline">
                <span>📧</span>
                <span>تواصل معنا</span>
            </a>
        </div>
        
        <div class="maintenance-footer">
            <p>سنعود قريباً بتحسينات رائعة! شكراً لصبركم 💚</p>
        </div>
    </div>
</body>
</html>
