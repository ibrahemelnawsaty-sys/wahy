<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'بناء القيم')</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            font-family: 'IBM Plex Sans Arabic', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            background-attachment: fixed;
            min-height: 100vh;
        }
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
    </style>
    @stack('styles')
</head>
<body>
    <!-- Header -->
    <header style="background: rgba(255,255,255,0.95); box-shadow: 0 4px 20px rgba(0,0,0,0.1); position: sticky; top: 0; z-index: 1000;">
        <div style="max-width: 1400px; margin: 0 auto; padding: 15px 20px; display: flex; justify-content: space-between; align-items: center;">
            <div style="display: flex; align-items: center; gap: 20px;">
                <div style="width: 50px; height: 50px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 50%; display: flex; align-items: center; justify-content: center;">
                    <span style="font-size: 28px;">🏆</span>
                </div>
                <div>
                    <h1 style="font-size: 22px; font-weight: 700; color: #1a202c;">بناء القيم</h1>
                    <p style="font-size: 13px; color: #718096;">مرحباً، {{ auth()->user()->name }}</p>
                </div>
            </div>
            
            <div style="display: flex; align-items: center; gap: 25px;">
                <!-- الرسائل -->
                <a href="{{ route('messages.index') }}" style="text-decoration: none; position: relative; display: flex; align-items: center; gap: 8px; background: white; padding: 10px 18px; border-radius: 50px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); transition: all 0.3s;">
                    <span style="font-size: 22px;">💬</span>
                    @php
                        $unreadCount = \App\Models\Message::where('receiver_id', auth()->id())->where('is_read', false)->count();
                    @endphp
                    @if($unreadCount > 0)
                        <span style="background: #ef4444; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 600; position: absolute; top: -5px; left: -5px;">{{ $unreadCount }}</span>
                    @endif
                </a>
                
                <!-- الرسائل الجماعية -->
                <a href="{{ route('messages.bulk.inbox') }}" style="text-decoration: none; position: relative; display: flex; align-items: center; gap: 8px; background: white; padding: 10px 18px; border-radius: 50px; box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1); transition: all 0.3s;">
                    <span style="font-size: 22px;">📬</span>
                    @php
                        $bulkUnreadCount = \App\Models\BulkMessageRecipient::where('user_id', auth()->id())->whereNull('read_at')->count();
                    @endphp
                    @if($bulkUnreadCount > 0)
                        <span style="background: #f59e0b; color: white; border-radius: 50%; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 600; position: absolute; top: -5px; left: -5px;">{{ $bulkUnreadCount }}</span>
                    @endif
                </a>
                
                <!-- النقاط -->
                <div style="display: flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #ffd700, #ffed4e); padding: 10px 20px; border-radius: 50px; box-shadow: 0 4px 15px rgba(255, 215, 0, 0.3);">
                    <span style="font-size: 22px;">⭐</span>
                    <span style="font-weight: 700; font-size: 18px; color: #7c3aed;">{{ $stats['total_points'] ?? 0 }}</span>
                </div>
                
                <!-- Streak -->
                @if(isset($streak) && $streak && $streak->current_streak > 0)
                <div style="display: flex; align-items: center; gap: 8px; background: linear-gradient(135deg, #ff6b6b, #ff8787); padding: 10px 18px; border-radius: 50px; box-shadow: 0 4px 15px rgba(255, 107, 107, 0.3);">
                    <span style="font-size: 22px;">🔥</span>
                    <span style="font-weight: 700; font-size: 18px; color: white;">{{ $streak->current_streak }}</span>
                </div>
                @endif
                
                <!-- تبديل الأدوار -->
                @if(auth()->user()->hasMultipleRoles())
                    <div style="position: relative;">
                        <button onclick="toggleRoleSwitcher()" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; padding: 10px 18px; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 14px; display: flex; align-items: center; gap: 8px;">
                            <i class="fas fa-sync-alt"></i>
                            <span>تبديل الدور</span>
                        </button>
                        <div id="roleSwitcherMenu" style="display: none; position: absolute; top: 50px; left: 0; background: white; border-radius: 12px; box-shadow: 0 8px 30px rgba(0,0,0,0.15); padding: 12px; min-width: 200px; z-index: 1000;">
                            @php
                                $currentRole = auth()->user()->getCurrentRole();
                                $allRoles = auth()->user()->getAllRoles();
                            @endphp
                            @foreach($allRoles as $role)
                                @if($role !== $currentRole)
                                    <form method="POST" action="{{ route('switch.role', $role) }}" style="margin:0;">
                                        @csrf
                                        <button type="submit" style="display: flex; align-items: center; gap: 10px; padding: 10px; background:none; border:none; width:100%; cursor:pointer; color: #1e293b; border-radius: 8px; transition: all 0.2s; margin-bottom: 4px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                                            <i class="{{ App\Models\User::getRoleIcon($role) }}" style="color: #667eea;"></i>
                                            <span style="font-weight: 600; font-size: 14px;">{{ App\Models\User::getRoleNameAr($role) }}</span>
                                        </button>
                                    </form>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <script>
                        function toggleRoleSwitcher() {
                            const menu = document.getElementById('roleSwitcherMenu');
                            menu.style.display = menu.style.display === 'none' ? 'block' : 'none';
                        }
                        document.addEventListener('click', function(e) {
                            const menu = document.getElementById('roleSwitcherMenu');
                            if (!e.target.closest('#roleSwitcherMenu') && !e.target.closest('button[onclick="toggleRoleSwitcher()"]')) {
                                menu.style.display = 'none';
                            }
                        });
                    </script>
                @endif
                
                <!-- Logout -->
                <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                    @csrf
                    <button type="submit" style="background: #e53e3e; color: white; border: none; padding: 10px 20px; border-radius: 10px; cursor: pointer; font-weight: 600; font-size: 14px; transition: all 0.3s;">
                        تسجيل الخروج
                    </button>
                </form>
            </div>
        </div>
    </header>

    <!-- Main Content -->
    <main class="container" style="padding-top: 30px;">
        @yield('content')
    </main>

    @stack('scripts')
    <!-- Real-Time Messages System -->
    <script src="{{ asset('js/messages-realtime.js') }}"></script>
</body>
</html>
