<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تعذّر فتح الدور - {{ setting('site_name', 'منصة قيمّ') }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        * { font-family: 'Cairo', 'Segoe UI', Tahoma, sans-serif; box-sizing: border-box; }
        body {
            margin: 0; min-height: 100vh; display: flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); padding: 20px;
        }
        .card-wrap {
            width: 100%; max-width: 480px; background: #fff; border-radius: 22px;
            box-shadow: 0 25px 60px rgba(0,0,0,0.25); overflow: hidden;
        }
        .card-head {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            padding: 34px 28px; text-align: center; color: #fff;
        }
        .card-head .icon {
            width: 74px; height: 74px; margin: 0 auto 16px; border-radius: 50%;
            background: rgba(255,255,255,0.2); display: flex; align-items: center; justify-content: center;
            font-size: 34px;
        }
        .card-head h1 { margin: 0; font-size: 22px; font-weight: 800; }
        .card-head p { margin: 6px 0 0; font-size: 14px; opacity: 0.95; }
        .card-body { padding: 28px; }
        .role-name {
            display: inline-block; background: #eef2ff; color: #4338ca; font-weight: 700;
            font-size: 14px; padding: 6px 16px; border-radius: 999px; margin-bottom: 16px;
        }
        .reason-box {
            background: #fffbeb; border: 1px solid #fde68a; border-radius: 14px;
            padding: 18px 20px; color: #92400e; font-size: 15px; line-height: 1.9; font-weight: 500;
            display: flex; gap: 12px; align-items: flex-start; margin-bottom: 26px;
        }
        .reason-box i { color: #d97706; font-size: 20px; margin-top: 2px; flex-shrink: 0; }
        .btn-primary-role {
            display: flex; align-items: center; justify-content: center; gap: 10px; width: 100%;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: #fff; border: none;
            padding: 15px; border-radius: 12px; font-weight: 700; font-size: 16px; cursor: pointer;
            box-shadow: 0 8px 20px rgba(102,126,234,0.35); transition: transform .18s ease, box-shadow .2s ease;
        }
        .btn-primary-role:hover { transform: translateY(-2px); box-shadow: 0 12px 26px rgba(102,126,234,0.45); }
        .btn-logout {
            display: flex; align-items: center; justify-content: center; gap: 8px; width: 100%;
            background: #f1f5f9; color: #475569; border: none; padding: 13px; border-radius: 12px;
            font-weight: 600; font-size: 14px; cursor: pointer; margin-top: 12px; transition: background .2s ease;
        }
        .btn-logout:hover { background: #e2e8f0; }
        form { margin: 0; }
    </style>
</head>
<body>
    <div class="card-wrap">
        <div class="card-head">
            <div class="icon"><i class="fas fa-triangle-exclamation"></i></div>
            <h1>تعذّر فتح هذا الدور</h1>
            <p>لا يمكن الانتقال إلى لوحة هذا الدور حالياً</p>
        </div>
        <div class="card-body">
            <div style="text-align: center;">
                <span class="role-name"><i class="fas fa-user-tag me-1"></i> {{ $roleName }}</span>
            </div>

            <div class="reason-box">
                <i class="fas fa-circle-info"></i>
                <span>{{ $reason }}</span>
            </div>

            <form method="POST" action="{{ route('switch.role.reset') }}">
                @csrf
                <button type="submit" class="btn-primary-role">
                    <i class="fas fa-arrow-right-to-bracket"></i>
                    العودة إلى حسابي الأساسيّ ({{ $primaryRoleName }})
                </button>
            </form>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn-logout">
                    <i class="fas fa-sign-out-alt"></i>
                    تسجيل الخروج
                </button>
            </form>
        </div>
    </div>
</body>
</html>
