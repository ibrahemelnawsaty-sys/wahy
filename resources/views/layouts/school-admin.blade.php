<!DOCTYPE html>
<html lang="ar" dir="rtl" data-theme="{{ $branding['site_theme'] ?? 'light' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @include('partials.head-meta')
    @include('partials.theme-vars')
    <title>@yield('title', 'لوحة مدير المدرسة - بناء القيم')</title>
    
    <!-- Bootstrap RTL CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    
    <!-- Glass Notifications CSS -->
    <link rel="stylesheet" href="{{ asset('css/glass-notifications.css') }}">
    
    <style>
        * {
            font-family: 'Cairo', sans-serif;
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: #f8fafc;
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* === SIDEBAR === */
        .school-sidebar {
            position: fixed;
            right: 0;
            top: 0;
            width: 280px;
            height: 100vh;
            background: linear-gradient(180deg, var(--color-primary, #667eea) 0%, var(--color-secondary, #764ba2) 100%);
            box-shadow: -4px 0 30px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .school-sidebar.collapsed {
            transform: translateX(100%);
        }

        /* Sidebar Header */
        .sidebar-header {
            padding: 30px 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }

        .sidebar-logo {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 15px;
        }

        .logo-icon {
            width: 50px;
            height: 50px;
            background: rgba(255, 255, 255, 0.25);
            border-radius: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 26px;
            color: white;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        .logo-text {
            flex: 1;
        }

        .logo-title {
            font-size: 20px;
            font-weight: 800;
            color: white;
            margin-bottom: 3px;
            letter-spacing: -0.5px;
        }

        .logo-subtitle {
            font-size: 13px;
            color: rgba(255, 255, 255, 0.85);
            font-weight: 500;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px;
            background: rgba(255, 255, 255, 0.15);
            border-radius: 12px;
            backdrop-filter: blur(10px);
        }

        .user-avatar {
            width: 42px;
            height: 42px;
            background: linear-gradient(135deg, #ffecd2 0%, #fcb69f 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: 700;
            color: var(--color-secondary, #764ba2);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .user-details {
            flex: 1;
        }

        .user-name {
            font-size: 14px;
            font-weight: 700;
            color: white;
            margin-bottom: 2px;
        }

        .user-role {
            font-size: 12px;
            color: rgba(255, 255, 255, 0.8);
            font-weight: 500;
        }

        /* Sidebar Navigation */
        .sidebar-nav {
            flex: 1;
            padding: 20px 0;
            overflow-y: auto;
            overflow-x: hidden;
        }

        .sidebar-nav::-webkit-scrollbar {
            width: 6px;
        }

        .sidebar-nav::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
        }

        .sidebar-nav::-webkit-scrollbar-track {
            background: transparent;
        }

        .nav-section {
            margin-bottom: 25px;
            padding: 0 20px;
        }

        .nav-section-title {
            font-size: 11px;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.6);
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-bottom: 10px;
            padding: 0 12px;
        }

        .nav-menu {
            list-style: none;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 13px 16px;
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            font-size: 15px;
            font-weight: 600;
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 100%;
            height: 100%;
            background: rgba(255, 255, 255, 0.15);
            transform: translateX(100%);
            transition: transform 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .nav-link:hover::before {
            transform: translateX(0);
        }

        .nav-link:hover {
            color: white;
            transform: translateX(-5px);
        }

        .nav-link.active {
            background: rgba(255, 255, 255, 0.25);
            color: white;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
        }

        .nav-link.active::after {
            content: '';
            position: absolute;
            right: 0;
            top: 50%;
            transform: translateY(-50%);
            width: 4px;
            height: 60%;
            background: white;
            border-radius: 10px 0 0 10px;
        }

        .nav-icon {
            width: 22px;
            height: 22px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            position: relative;
            z-index: 1;
        }

        .nav-text {
            flex: 1;
            position: relative;
            z-index: 1;
        }

        .nav-badge {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a6f 100%);
            color: white;
            font-size: 11px;
            font-weight: 700;
            padding: 3px 8px;
            border-radius: 12px;
            position: relative;
            z-index: 1;
            box-shadow: 0 4px 10px rgba(255, 107, 107, 0.4);
        }

        /* Sidebar Footer */
        .sidebar-footer {
            padding: 20px;
            background: rgba(0, 0, 0, 0.15);
            border-top: 1px solid rgba(255, 255, 255, 0.15);
        }

        .logout-btn {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            padding: 14px;
            background: rgba(255, 255, 255, 0.2);
            border: 2px solid rgba(255, 255, 255, 0.3);
            color: white;
            font-size: 15px;
            font-weight: 700;
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            backdrop-filter: blur(10px);
        }

        .logout-btn:hover {
            background: rgba(255, 255, 255, 0.3);
            border-color: rgba(255, 255, 255, 0.5);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
        }

        /* === MAIN CONTENT === */
        .main-wrapper {
            margin-right: 280px;
            min-height: 100vh;
            transition: margin-right 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .main-wrapper.expanded {
            margin-right: 0;
        }

        /* Top Header */
        .top-header {
            background: white;
            padding: 20px 35px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 100;
        }

        .header-left {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .menu-toggle {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, var(--color-primary, #667eea) 0%, var(--color-secondary, #764ba2) 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 20px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.3);
        }

        .menu-toggle:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }

        .page-title-wrapper {
            display: flex;
            flex-direction: column;
        }

        .page-title {
            font-size: 24px;
            font-weight: 800;
            color: #1a202c;
            margin-bottom: 3px;
        }

        .page-breadcrumb {
            font-size: 14px;
            color: #718096;
            font-weight: 500;
        }

        .page-breadcrumb a {
            color: var(--color-primary, #667eea);
            text-decoration: none;
            transition: color 0.2s;
        }

        .page-breadcrumb a:hover {
            color: var(--color-secondary, #764ba2);
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .header-btn {
            width: 45px;
            height: 45px;
            background: #f7fafc;
            border: none;
            border-radius: 12px;
            color: #4a5568;
            font-size: 18px;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
        }

        .header-btn:hover {
            background: #edf2f7;
            color: var(--color-primary, #667eea);
            transform: translateY(-2px);
        }

        .notification-badge {
            position: absolute;
            top: 8px;
            right: 8px;
            width: 8px;
            height: 8px;
            background: #f56565;
            border-radius: 50%;
            border: 2px solid white;
        }

        /* Main Content */
        .main-content {
            padding: 35px;
        }

        /* Responsive */
        @media (max-width: 992px) {
            .school-sidebar {
                transform: translateX(100%);
            }

            .school-sidebar.show {
                transform: translateX(0);
            }

            .main-wrapper {
                margin-right: 0;
            }
        }

        @media (max-width: 768px) {
            .top-header {
                padding: 15px 20px;
            }

            .page-title {
                font-size: 20px;
            }

            .main-content {
                padding: 20px;
            }

            .school-sidebar {
                width: 260px;
            }

            .header-right .header-btn:not(:first-child) {
                display: none;
            }
        }

        /* Bootstrap Overrides & Custom Styles */
        .card {
            border-radius: 16px;
            box-shadow: 0 2px 15px rgba(0, 0, 0, 0.05);
            border: none;
        }

        .card-header {
            background: white;
            border-bottom: 1px solid #e2e8f0;
            padding: 20px;
            border-radius: 16px 16px 0 0;
        }

        .card-body {
            padding: 25px;
        }

        .btn {
            border-radius: 10px;
            padding: 10px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--color-primary, #667eea) 0%, var(--color-secondary, #764ba2) 100%);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .btn-success {
            background: linear-gradient(135deg, #48c774 0%, #3ec46d 100%);
            border: none;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(72, 199, 116, 0.3);
        }

        .btn-info {
            background: linear-gradient(135deg, #48c6ef 0%, #6f86d6 100%);
            border: none;
        }

        .btn-info:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(72, 198, 239, 0.3);
        }

        .btn-warning {
            background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
            border: none;
            color: white;
        }

        .btn-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(251, 191, 36, 0.3);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
            border: none;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(245, 101, 101, 0.3);
        }

        .btn-sm {
            padding: 6px 12px;
            font-size: 14px;
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            background: #f7fafc;
            color: #4a5568;
            font-weight: 700;
            border: none;
            padding: 15px;
            font-size: 14px;
        }

        .table tbody td {
            padding: 15px;
            vertical-align: middle;
            border-bottom: 1px solid #e2e8f0;
        }

        .table tbody tr:hover {
            background: #f7fafc;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 8px;
            font-weight: 600;
            font-size: 12px;
        }

        .form-control, .form-select {
            border-radius: 10px;
            border: 2px solid #e2e8f0;
            padding: 12px 16px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--color-primary, #667eea);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-label {
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 8px;
        }

        .alert {
            border-radius: 12px;
            border: none;
            padding: 16px 20px;
            font-weight: 600;
        }

        .alert-success {
            background: linear-gradient(135deg, #48c774 0%, #3ec46d 100%);
            color: white;
        }

        .alert-danger {
            background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%);
            color: white;
        }

        .pagination {
            gap: 8px;
        }

        .page-link {
            border-radius: 8px;
            border: 2px solid #e2e8f0;
            color: var(--color-primary, #667eea);
            font-weight: 600;
            padding: 8px 14px;
            margin: 0 4px;
        }

        .page-link:hover {
            background: var(--color-primary, #667eea);
            color: white;
            border-color: var(--color-primary, #667eea);
        }

        .page-item.active .page-link {
            background: var(--color-primary, #667eea);
            border-color: var(--color-primary, #667eea);
        }

        @stack('styles')
    </style>

    @include('partials.theme-toggle')
</head>
<body>
    <a href="#sch-main-content" class="skip-to-content" style="position:absolute;top:-40px;right:0;background:#1e293b;color:#fff;padding:8px 16px;z-index:9999;text-decoration:none;border-radius:0 0 0 8px;" onfocus="this.style.top='0'" onblur="this.style.top='-40px'">تخطي إلى المحتوى الرئيسي</a>
    <!-- Sidebar -->
    <aside class="school-sidebar" id="schoolSidebar">
        <!-- Header -->
        <div class="sidebar-header">
            <div class="sidebar-logo">
                <div class="logo-icon">
                    <i class="fas fa-school"></i>
                </div>
                <div class="logo-text">
                    <div class="logo-title">{{ $branding['site_name'] ?? 'بناء القيم' }}</div>
                    <div class="logo-subtitle">مدير المدرسة</div>
                </div>
            </div>
            <div class="user-info" style="position: relative;" id="schAvatarDropdownContainer">
                <div class="user-avatar" id="schAvatarToggleBtn" style="cursor: pointer; overflow: hidden; padding: 0;">
                    <img src="{{ auth()->user()->avatar_url }}" alt="صورة" style="width: 100%; height: 100%; object-fit: cover; border-radius: 50%;">
                </div>
                <div class="user-details">
                    <div class="user-name">{{ auth()->user()->name }}</div>
                    <div class="user-role">{{ auth()->user()->school->name ?? 'مدير المدرسة' }}</div>
                </div>

                <!-- Dropdown Menu -->
                <div id="schAvatarDropdownMenu" style="display: none; position: absolute; top: calc(100% + 10px); right: 0; width: 260px; background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.15); z-index: 1000; overflow: hidden;">
                    <div style="padding: 8px;">
                        <label for="schAvatarUploadInput" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 10px; cursor: pointer; transition: background 0.2s; color: #334155; font-weight: 600; font-size: 14px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                            <span>📷</span> تغيير الصورة
                        </label>
                        <input type="file" id="schAvatarUploadInput" accept="image/jpeg,image/png,image/jpg,image/webp" style="display: none;">

                        <a href="{{ route('school-admin.settings') }}" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 10px; text-decoration: none; transition: background 0.2s; color: #334155; font-weight: 600; font-size: 14px;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">
                            <span>⚙️</span> الإعدادات
                        </a>

                        <div style="height: 1px; background: #e2e8f0; margin: 4px 16px;"></div>

                        <form action="{{ route('logout') }}" method="POST" style="margin: 0;">
                            @csrf
                            <button type="submit" style="display: flex; align-items: center; gap: 12px; padding: 12px 16px; border-radius: 10px; width: 100%; border: none; background: transparent; cursor: pointer; transition: background 0.2s; color: #ef4444; font-weight: 600; font-size: 14px; font-family: inherit;" onmouseover="this.style.background='#fef2f2'" onmouseout="this.style.background='transparent'">
                                <span>🚪</span> تسجيل الخروج
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Navigation -->
        <nav class="sidebar-nav">
            <!-- القسم الرئيسي -->
            <div class="nav-section">
                <div class="nav-section-title">القسم الرئيسي</div>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="{{ route('school-admin.dashboard') }}" class="nav-link {{ request()->routeIs('school-admin.dashboard') ? 'active' : '' }}">
                            <span class="nav-icon"><i class="fas fa-home"></i></span>
                            <span class="nav-text">لوحة التحكم</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- إدارة المستخدمين -->
            <div class="nav-section">
                <div class="nav-section-title">إدارة المستخدمين</div>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="{{ route('school-admin.teachers') }}" class="nav-link {{ request()->routeIs('school-admin.teachers*') ? 'active' : '' }}">
                            <span class="nav-icon"><i class="fas fa-chalkboard-teacher"></i></span>
                            <span class="nav-text">المعلمون</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('school-admin.students') }}" class="nav-link {{ request()->routeIs('school-admin.students*') ? 'active' : '' }}">
                            <span class="nav-icon"><i class="fas fa-user-graduate"></i></span>
                            <span class="nav-text">الطلاب</span>
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('school-admin.parents') }}" class="nav-link {{ request()->routeIs('school-admin.parents*') ? 'active' : '' }}">
                            <span class="nav-icon"><i class="fas fa-users"></i></span>
                            <span class="nav-text">أولياء الأمور</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- إدارة الفصول -->
            <div class="nav-section">
                <div class="nav-section-title">إدارة الفصول</div>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="{{ route('school-admin.classrooms') }}" class="nav-link {{ request()->routeIs('school-admin.classrooms*') ? 'active' : '' }}">
                            <span class="nav-icon"><i class="fas fa-chalkboard"></i></span>
                            <span class="nav-text">الفصول الدراسية</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- التواصل -->
            <div class="nav-section">
                <div class="nav-section-title">التواصل</div>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="{{ route('school-admin.messages.index') }}" class="nav-link {{ request()->routeIs('school-admin.messages*') ? 'active' : '' }}">
                            <span class="nav-icon"><i class="fas fa-comments"></i></span>
                            <span class="nav-text">الرسائل</span>
                            @php
                                $unreadMessages = \App\Models\Message::where('receiver_id', auth()->id())
                                    ->where('is_read', false)
                                    ->count();
                            @endphp
                            @if($unreadMessages > 0)
                                <span class="nav-badge">{{ $unreadMessages }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('messages.bulk.inbox') }}" class="nav-link {{ request()->routeIs('messages.bulk.inbox') ? 'active' : '' }}">
                            <span class="nav-icon"><i class="fas fa-inbox"></i></span>
                            <span class="nav-text">الرسائل الجماعية</span>
                            @php
                                $bulkUnreadMessages = \App\Models\BulkMessageRecipient::where('user_id', auth()->id())
                                    ->whereNull('read_at')
                                    ->count();
                            @endphp
                            @if($bulkUnreadMessages > 0)
                                <span class="nav-badge" style="background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); box-shadow: 0 4px 10px rgba(245, 158, 11, 0.4);">{{ $bulkUnreadMessages }}</span>
                            @endif
                        </a>
                    </li>
                    {{-- Issue #109: إظهار رابط تفاعل أولياء الأمور في القائمة الجانبية --}}
                    <li class="nav-item">
                        <a href="{{ route('school-admin.parent-engagement') }}" class="nav-link {{ request()->routeIs('school-admin.parent-engagement') ? 'active' : '' }}">
                            <span class="nav-icon"><i class="fas fa-heart"></i></span>
                            <span class="nav-text">تفاعل أولياء الأمور</span>
                        </a>
                    </li>
                    {{-- N12: مقارنات الاستبيانات القبلية/البعدية --}}
                    <li class="nav-item">
                        <a href="{{ route('school-admin.surveys.comparisons') }}" class="nav-link {{ request()->routeIs('school-admin.surveys.*') ? 'active' : '' }}">
                            <span class="nav-icon"><i class="fas fa-chart-bar"></i></span>
                            <span class="nav-text">مقارنات الاستبيانات</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- استيراد وتصدير البيانات -->
            <div class="nav-section">
                <div class="nav-section-title">استيراد وتصدير البيانات</div>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="{{ route('school-admin.excel-management') }}" class="nav-link {{ request()->routeIs('school-admin.excel-management') || request()->routeIs('school-admin.download-template') || request()->routeIs('school-admin.import-users') ? 'active' : '' }}">
                            <span class="nav-icon"><i class="fas fa-file-excel"></i></span>
                            <span class="nav-text">استيراد/تصدير Excel</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- الإحصائيات والتصنيف -->
            <div class="nav-section">
                <div class="nav-section-title">الإحصائيات والتصنيف</div>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="{{ route('school-admin.statistics') }}" class="nav-link {{ request()->routeIs('school-admin.statistics') ? 'active' : '' }}">
                            <span class="nav-icon"><i class="fas fa-chart-bar"></i></span>
                            <span class="nav-text">الإحصائيات والتصنيف</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- الطلبات -->
            <div class="nav-section">
                <div class="nav-section-title">الطلبات والموافقات</div>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="{{ route('school-admin.requests') }}" class="nav-link {{ request()->routeIs('school-admin.requests*') ? 'active' : '' }}">
                            <span class="nav-icon"><i class="fas fa-clipboard-list"></i></span>
                            <span class="nav-text">طلبات التسجيل</span>
                            @php
                                $pendingCount = \App\Models\RegistrationRequest::where('school_id', auth()->user()->school_id)
                                    ->where('status', 'pending')
                                    ->count();
                            @endphp
                            @if($pendingCount > 0)
                                <span class="nav-badge">{{ $pendingCount }}</span>
                            @endif
                        </a>
                    </li>
                    <li class="nav-item">
                        <a href="{{ route('school-admin.registration-links') }}" class="nav-link {{ request()->routeIs('school-admin.registration-links') ? 'active' : '' }}">
                            <span class="nav-icon"><i class="fas fa-link"></i></span>
                            <span class="nav-text">روابط التسجيل</span>
                        </a>
                    </li>
                </ul>
            </div>

            <!-- الإعدادات -->
            <div class="nav-section">
                <div class="nav-section-title">الإعدادات</div>
                <ul class="nav-menu">
                    <li class="nav-item">
                        <a href="{{ route('school-admin.settings') }}" class="nav-link {{ request()->routeIs('school-admin.settings') ? 'active' : '' }}">
                            <span class="nav-icon"><i class="fas fa-cog"></i></span>
                            <span class="nav-text">إعدادات المدرسة</span>
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- تبديل الأدوار -->
        @include('components.role-switcher')

        <!-- Footer -->
        <div class="sidebar-footer">
            <form method="POST" action="{{ route('logout') }}" style="margin: 0;">
                @csrf
                <button type="submit" class="logout-btn">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>تسجيل الخروج</span>
                </button>
            </form>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="main-wrapper" id="mainWrapper">
        <!-- Top Header -->
        <header class="top-header">
            <div class="header-left">
                <button class="menu-toggle" id="menuToggle">
                    <i class="fas fa-bars"></i>
                </button>
                <div class="page-title-wrapper">
                    <h1 class="page-title">@yield('page-title', 'لوحة التحكم')</h1>
                    <div class="page-breadcrumb">
                        @hasSection('breadcrumb')
                            @yield('breadcrumb')
                        @else
                            <a href="{{ route('school-admin.dashboard') }}">الرئيسية</a>
                        @endif
                    </div>
                </div>
            </div>
            <div class="header-right">
                <!-- Settings Button -->
                <a href="{{ route('school-admin.settings') }}" class="header-btn" title="الإعدادات">
                    <i class="fas fa-cog"></i>
                </a>

                <!-- Notifications Dropdown -->
                <div style="position: relative; display: inline-block;">
                    @php
                        // إشعارات مدير المدرسة فقط (ليست إشعارات الطلاب)
                        $adminNotificationTypes = [
                            'registration_request', 'teacher_update', 'system_alert',
                            'school_announcement', 'report_ready', 'new_registration',
                            'teacher_message', 'admin_notification', 'bulk_message',
                            'approval_required', 'student_report', 'class_update'
                        ];
                        $adminNotifications = \App\Models\Notification::where('notifiable_type', 'App\\Models\\User')
                            ->where('notifiable_id', auth()->id())
                            ->whereIn('type', $adminNotificationTypes)
                            ->latest()
                            ->take(10)
                            ->get();
                        $adminUnreadCount = \App\Models\Notification::where('notifiable_type', 'App\\Models\\User')
                            ->where('notifiable_id', auth()->id())
                            ->whereIn('type', $adminNotificationTypes)
                            ->whereNull('read_at')
                            ->count();
                    @endphp
                    <button class="header-btn" id="notificationBtn" title="الإشعارات" style="position: relative;">
                        <i class="fas fa-bell"></i>
                        @if($adminUnreadCount > 0)
                            <span class="notification-badge" style="position: absolute; top: -5px; right: -5px; background: #f56565; color: white; border-radius: 10px; padding: 2px 6px; font-size: 10px; font-weight: 700; min-width: 18px; text-align: center;">{{ $adminUnreadCount > 9 ? '9+' : $adminUnreadCount }}</span>
                        @endif
                    </button>
                    
                    <!-- Notifications Dropdown Panel -->
                    <div id="notificationsPanel" style="display: none; position: absolute; top: calc(100% + 10px); left: 0; width: 380px; max-height: 500px; background: white; border-radius: 16px; box-shadow: 0 20px 60px rgba(0,0,0,0.15); z-index: 1000; overflow: hidden;">
                        <div style="background: linear-gradient(135deg, var(--color-primary, #667eea) 0%, var(--color-secondary, #764ba2) 100%); padding: 20px; color: white;">
                            <div style="display: flex; justify-content: space-between; align-items: center;">
                                <h3 style="font-size: 18px; font-weight: 700; margin: 0;">إشعارات الإدارة</h3>
                                <div style="display: flex; align-items: center; gap: 8px;">
                                    <button id="muteNotificationsBtn" onclick="event.stopPropagation(); if(window.messagesRealTime){window.messagesRealTime.toggleMute();}" title="كتم/تفعيل صوت الإشعارات" style="background: rgba(255,255,255,0.2); border: none; color: white; padding: 6px 12px; border-radius: 8px; font-size: 14px; cursor: pointer; font-weight: 600; display: flex; align-items: center; gap: 6px; transition: all 0.2s;">
                                        <i class="fas fa-volume-up" id="muteIconInPanel"></i>
                                        <span id="muteTextInPanel" style="font-size: 12px;">الصوت</span>
                                    </button>
                                    @if($adminUnreadCount > 0)
                                        <form action="{{ route('notifications.read-all') }}" method="POST" style="margin: 0;">
                                            @csrf
                                            <button type="submit" style="background: rgba(255,255,255,0.2); border: none; color: white; padding: 6px 12px; border-radius: 8px; font-size: 12px; cursor: pointer; font-weight: 600;">تحديد الكل كمقروء</button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                        
                        <div style="max-height: 400px; overflow-y: auto;">
                            @forelse($adminNotifications as $notification)
                                <div style="padding: 15px 20px; border-bottom: 1px solid #f0f0f0; {{ is_null($notification->read_at) ? 'background: #f8f9ff;' : '' }} cursor: pointer; transition: background 0.2s;" 
                                     onclick="markAsRead('{{ $notification->id }}', '{{ $notification->action_url ?? '#' }}')">
                                    <div style="display: flex; gap: 12px; align-items: start;">
                                        <div style="width: 40px; height: 40px; border-radius: 10px; background: linear-gradient(135deg, var(--color-primary, #667eea) 0%, var(--color-secondary, #764ba2) 100%); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                            <i class="fas fa-{{ 
                                                str_contains($notification->type, 'registration') ? 'user-plus' : 
                                                (str_contains($notification->type, 'teacher') ? 'chalkboard-teacher' : 
                                                (str_contains($notification->type, 'report') ? 'chart-bar' : 
                                                (str_contains($notification->type, 'message') ? 'envelope' : 'bell'))) 
                                            }}" style="color: white; font-size: 16px;"></i>
                                        </div>
                                        <div style="flex: 1;">
                                            <h4 style="font-size: 14px; font-weight: 700; color: #2d3748; margin: 0 0 5px 0;">{{ $notification->title ?? 'إشعار جديد' }}</h4>
                                            <p style="font-size: 13px; color: #718096; margin: 0 0 5px 0; line-height: 1.5;">{{ $notification->message ?? '' }}</p>
                                            <span style="font-size: 11px; color: #a0aec0;">{{ $notification->created_at->diffForHumans() }}</span>
                                        </div>
                                        @if(is_null($notification->read_at))
                                            <div style="width: 8px; height: 8px; border-radius: 50%; background: var(--color-primary, #667eea); flex-shrink: 0; margin-top: 5px;"></div>
                                        @endif
                                    </div>
                                </div>
                            @empty
                                <div style="padding: 60px 20px; text-align: center; color: #a0aec0;">
                                    <i class="fas fa-bell-slash" style="font-size: 48px; margin-bottom: 15px; opacity: 0.3;"></i>
                                    <p style="font-size: 14px; margin: 0;">لا توجد إشعارات إدارية</p>
                                </div>
                            @endforelse
                        </div>
                        
                        @if($adminNotifications->count() > 0)
                            <div style="padding: 15px 20px; text-align: center; border-top: 1px solid #f0f0f0;">
                                <a href="{{ route('notifications.index') }}" style="color: var(--color-primary, #667eea); font-weight: 600; font-size: 14px; text-decoration: none;">عرض جميع الإشعارات</a>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <main class="main-content" id="sch-main-content">
            @if(session('success'))
                <div style="background: linear-gradient(135deg, #48c774 0%, #3ec46d 100%); color: white; padding: 18px 24px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 8px 20px rgba(72, 199, 116, 0.3); display: flex; align-items: center; gap: 15px;">
                    <i class="fas fa-check-circle" style="font-size: 24px;"></i>
                    <span style="font-size: 15px; font-weight: 600;">{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div style="background: linear-gradient(135deg, #f56565 0%, #e53e3e 100%); color: white; padding: 18px 24px; border-radius: 12px; margin-bottom: 25px; box-shadow: 0 8px 20px rgba(245, 101, 101, 0.3); display: flex; align-items: center; gap: 15px;">
                    <i class="fas fa-exclamation-circle" style="font-size: 24px;"></i>
                    <span style="font-size: 15px; font-weight: 600;">{{ session('error') }}</span>
                </div>
            @endif

            @yield('content')
        </main>
    </div>

    <script>
        // Toggle Sidebar
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.getElementById('schoolSidebar');
        const mainWrapper = document.getElementById('mainWrapper');

        menuToggle.addEventListener('click', () => {
            if (window.innerWidth > 992) {
                sidebar.classList.toggle('collapsed');
                mainWrapper.classList.toggle('expanded');
            } else {
                sidebar.classList.toggle('show');
            }
        });

        // Close sidebar on mobile when clicking outside
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 992) {
                if (!sidebar.contains(e.target) && !menuToggle.contains(e.target) && sidebar.classList.contains('show')) {
                    sidebar.classList.remove('show');
                }
            }
        });

        // Handle window resize
        window.addEventListener('resize', () => {
            if (window.innerWidth > 992) {
                sidebar.classList.remove('show');
                sidebar.classList.remove('collapsed');
                mainWrapper.classList.remove('expanded');
            }
        });

        // Notifications Dropdown
        const notificationBtn = document.getElementById('notificationBtn');
        const notificationsPanel = document.getElementById('notificationsPanel');

        if (notificationBtn && notificationsPanel) {
            notificationBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                notificationsPanel.style.display = notificationsPanel.style.display === 'none' ? 'block' : 'none';
            });

            // Close notifications panel when clicking outside
            document.addEventListener('click', (e) => {
                if (!notificationsPanel.contains(e.target) && !notificationBtn.contains(e.target)) {
                    notificationsPanel.style.display = 'none';
                }
            });
        }

        // Mark notification as read
        function markAsRead(notificationId, actionUrl) {
            fetch(`/notifications/${notificationId}/read`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            }).then(() => {
                if (actionUrl && actionUrl !== '#') {
                    window.location.href = actionUrl;
                } else {
                    location.reload();
                }
            });
        }

        // Avatar Dropdown Toggle
        (function() {
            const toggleBtn = document.getElementById('schAvatarToggleBtn');
            const dropdownMenu = document.getElementById('schAvatarDropdownMenu');
            const container = document.getElementById('schAvatarDropdownContainer');
            const avatarInput = document.getElementById('schAvatarUploadInput');

            if (toggleBtn && dropdownMenu) {
                toggleBtn.addEventListener('click', function(e) {
                    e.stopPropagation();
                    dropdownMenu.style.display = dropdownMenu.style.display === 'none' ? 'block' : 'none';
                });

                document.addEventListener('click', function(e) {
                    if (container && !container.contains(e.target)) {
                        dropdownMenu.style.display = 'none';
                    }
                });
            }

            if (avatarInput) {
                avatarInput.addEventListener('change', function() {
                    if (!this.files || !this.files[0]) return;
                    const formData = new FormData();
                    formData.append('avatar', this.files[0]);
                    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);

                    fetch('{{ route("profile.update-avatar") }}', {
                        method: 'POST',
                        body: formData,
                        headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        if (data.success) {
                            document.querySelectorAll('#schAvatarToggleBtn img').forEach(img => img.src = data.avatar_url);
                            alert(data.message);
                        } else {
                            alert(data.message || 'حدث خطأ');
                        }
                    })
                    .catch(() => alert('حدث خطأ أثناء رفع الصورة'));
                    this.value = '';
                });
            }
        })();
    </script>

    <!-- Bootstrap JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Glass Notifications JS -->
    <script src="{{ asset('js/glass-notifications.js') }}"></script>
    
    <!-- Real-Time Messages System -->
    <script src="{{ asset('js/messages-realtime.js') }}?v={{ time() }}"></script>

    @stack('scripts')
    
    <!-- Survey Popup Component -->
    @include('components.survey-popup')

    @stack('after-content')
</body>
</html>
