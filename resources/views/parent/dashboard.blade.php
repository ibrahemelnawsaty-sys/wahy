@extends('layouts.parent')

@section('title', 'لوحة تحكم ولي الأمر - متابعة شاملة')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/student-glass.css') }}?v={{ time() }}">
<style>
    :root {
        --parent-primary: #667eea;
        --parent-secondary: #764ba2;
        --parent-accent: #f093fb;
        --parent-success: #10b981;
        --parent-warning: #f59e0b;
        --parent-info: #3b82f6;
        --parent-danger: #ef4444;
    }
    
    body {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        background-attachment: fixed;
        min-height: 100vh;
        position: relative;
        overflow-x: hidden;
    }
    
    /* Animated Background Orbs */
    body::before,
    body::after {
        content: '';
        position: fixed;
        border-radius: 50%;
        filter: blur(100px);
        opacity: 0.2;
        z-index: 0;
        pointer-events: none;
        animation: floatOrb 20s ease-in-out infinite;
    }
    
    body::before {
        width: 500px;
        height: 500px;
        background: radial-gradient(circle, #f093fb 0%, transparent 70%);
        top: -150px;
        right: -150px;
    }
    
    body::after {
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, #667eea 0%, transparent 70%);
        bottom: -100px;
        left: -100px;
        animation-delay: -10s;
    }
    
    @keyframes floatOrb {
        0%, 100% { transform: translate(0, 0) scale(1); }
        33% { transform: translate(30px, -30px) scale(1.1); }
        66% { transform: translate(-20px, 20px) scale(0.9); }
    }

    /* P2-D: تعطيل orbs المتحركة الثقيلة + backdrop-filter على الجوال */
    @media (max-width: 768px) {
        body::before,
        body::after { display: none !important; }
        .dashboard-hero::before { display: none !important; }
        [style*="backdrop-filter"],
        [class*="backdrop"] {
            backdrop-filter: none !important;
            -webkit-backdrop-filter: none !important;
        }
    }
    
    .parent-dashboard-container {
        position: relative;
        z-index: 1;
        max-width: 1400px;
        margin: 0 auto;
        padding: 2rem 1.5rem;
        padding-bottom: 4rem;
    }
    
    /* Premium Header Section */
    .dashboard-hero {
        text-align: center;
        margin-bottom: 3rem;
        padding: 2.5rem 0;
        position: relative;
    }
    
    .dashboard-hero::before {
        content: '';
        position: absolute;
        top: 0;
        left: 50%;
        transform: translateX(-50%);
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 70%);
        border-radius: 50%;
        filter: blur(40px);
    }
    
    .dashboard-hero h1 {
        font-size: 3rem;
        font-weight: 800;
        color: white;
        margin-bottom: 1rem;
        text-shadow: 0 4px 20px rgba(0,0,0,0.2);
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
    }
    
    .dashboard-hero h1 .hero-text {
        background: linear-gradient(135deg, #ffffff 0%, rgba(255,255,255,0.9) 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .dashboard-hero h1 .hero-icon {
        font-size: 3.5rem;
        -webkit-text-fill-color: initial;
        filter: drop-shadow(0 4px 8px rgba(0,0,0,0.2));
    }
    
    .dashboard-hero p {
        font-size: 1.25rem;
        color: rgba(255,255,255,0.95);
        font-weight: 500;
        position: relative;
        z-index: 2;
    }
    
    /* Premium Stats Grid */
    .premium-stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 1.5rem;
        margin-bottom: 3rem;
    }
    
    .premium-stat-card {
        background: rgba(255, 255, 255, 0.85);
        backdrop-filter: blur(50px) saturate(200%);
        -webkit-backdrop-filter: blur(50px) saturate(200%);
        border-radius: 24px;
        padding: 2rem;
        border: 1.5px solid rgba(255, 255, 255, 0.3);
        box-shadow: 
            0 20px 60px rgba(0, 0, 0, 0.15),
            0 8px 24px rgba(0, 0, 0, 0.1),
            inset 0 1px 0 rgba(255, 255, 255, 0.6);
        position: relative;
        overflow: hidden;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        cursor: pointer;
    }
    
    .premium-stat-card::before {
        content: '';
        position: absolute;
        top: -50%;
        left: -50%;
        width: 200%;
        height: 200%;
        background: linear-gradient(45deg, transparent, rgba(255,255,255,0.1), transparent);
        transform: rotate(45deg);
        transition: all 0.6s;
        opacity: 0;
    }
    
    .premium-stat-card:hover::before {
        opacity: 1;
        left: 100%;
    }
    
    .premium-stat-card:hover {
        transform: translateY(-8px) scale(1.02);
        box-shadow: 
            0 30px 80px rgba(0, 0, 0, 0.2),
            0 12px 32px rgba(0, 0, 0, 0.15),
            inset 0 1px 0 rgba(255, 255, 255, 0.8);
    }
    
    .stat-icon-wrapper {
        width: 80px;
        height: 80px;
        border-radius: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 1.5rem;
        font-size: 2.5rem;
        position: relative;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    
    .premium-stat-card:hover .stat-icon-wrapper {
        transform: scale(1.15) rotate(5deg);
    }
    
    .stat-card-1 .stat-icon-wrapper {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        box-shadow: 0 12px 32px rgba(102, 126, 234, 0.4);
    }
    
    .stat-card-2 .stat-icon-wrapper {
        background: linear-gradient(135deg, #fbbf24 0%, #f59e0b 100%);
        box-shadow: 0 12px 32px rgba(251, 191, 36, 0.4);
    }
    
    .stat-card-3 .stat-icon-wrapper {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        box-shadow: 0 12px 32px rgba(16, 185, 129, 0.4);
    }
    
    .stat-card-4 .stat-icon-wrapper {
        background: linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%);
        box-shadow: 0 12px 32px rgba(139, 92, 246, 0.4);
    }
    
    .stat-value {
        font-size: 2.5rem;
        font-weight: 800;
        color: #1a202c;
        text-align: center;
        margin-bottom: 0.5rem;
        background: linear-gradient(135deg, #1a202c 0%, #4a5568 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .stat-label {
        font-size: 0.95rem;
        color: #64748b;
        text-align: center;
        font-weight: 600;
        letter-spacing: 0.3px;
    }
    
    /* Empty State Premium */
    .premium-empty-state {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(50px) saturate(200%);
        -webkit-backdrop-filter: blur(50px) saturate(200%);
        border-radius: 32px;
        padding: 5rem 3rem;
        text-align: center;
        border: 1.5px solid rgba(255, 255, 255, 0.4);
        box-shadow: 0 30px 80px rgba(0, 0, 0, 0.15);
        position: relative;
        overflow: hidden;
    }
    
    .premium-empty-state::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 400px;
        height: 400px;
        background: radial-gradient(circle, rgba(102, 126, 234, 0.1) 0%, transparent 70%);
        border-radius: 50%;
    }
    
    .empty-icon {
        font-size: 6rem;
        margin-bottom: 1.5rem;
        opacity: 0.6;
        animation: floatEmpty 3s ease-in-out infinite;
        position: relative;
        z-index: 2;
    }
    
    @keyframes floatEmpty {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-20px); }
    }
    
    .empty-title {
        font-size: 1.75rem;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 1rem;
        position: relative;
        z-index: 2;
    }
    
    .empty-text {
        font-size: 1rem;
        color: #64748b;
        line-height: 1.6;
        position: relative;
        z-index: 2;
    }
    
    /* Children Cards - Premium Design */
    .children-grid-premium {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(380px, 1fr));
        gap: 2rem;
    }
    
    .premium-child-card {
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(50px) saturate(200%);
        -webkit-backdrop-filter: blur(50px) saturate(200%);
        border-radius: 28px;
        overflow: hidden;
        border: 1.5px solid rgba(255, 255, 255, 0.4);
        box-shadow: 
            0 25px 70px rgba(0, 0, 0, 0.15),
            0 10px 30px rgba(0, 0, 0, 0.1),
            inset 0 1px 0 rgba(255, 255, 255, 0.6);
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
        position: relative;
        animation: slideUpFade 0.6s ease-out forwards;
        opacity: 0;
        transform: translateY(30px);
    }
    
    @keyframes slideUpFade {
        to {
            opacity: 1;
            transform: translateY(0);
        }
    }
    
    .premium-child-card:nth-child(1) { animation-delay: 0.1s; }
    .premium-child-card:nth-child(2) { animation-delay: 0.2s; }
    .premium-child-card:nth-child(3) { animation-delay: 0.3s; }
    .premium-child-card:nth-child(4) { animation-delay: 0.4s; }
    .premium-child-card:nth-child(5) { animation-delay: 0.5s; }
    .premium-child-card:nth-child(6) { animation-delay: 0.6s; }
    
    .premium-child-card:hover {
        transform: translateY(-12px) scale(1.02);
        box-shadow: 
            0 35px 90px rgba(0, 0, 0, 0.2),
            0 15px 40px rgba(0, 0, 0, 0.15),
            inset 0 1px 0 rgba(255, 255, 255, 0.8);
    }
    
    /* Card Header Premium */
    .child-card-header-premium {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        padding: 2.5rem 2rem;
        text-align: center;
        position: relative;
        overflow: hidden;
    }
    
    .child-card-header-premium::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(255,255,255,0.2) 0%, transparent 70%);
        border-radius: 50%;
        animation: pulseGlow 3s ease-in-out infinite;
    }
    
    @keyframes pulseGlow {
        0%, 100% { opacity: 0.3; transform: scale(1); }
        50% { opacity: 0.6; transform: scale(1.2); }
    }
    
    .child-avatar-premium {
        width: 120px;
        height: 120px;
        border-radius: 50%;
        border: 5px solid rgba(255, 255, 255, 0.4);
        object-fit: cover;
        box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
        margin: 0 auto 1.5rem;
        position: relative;
        z-index: 2;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    
    .premium-child-card:hover .child-avatar-premium {
        transform: scale(1.1) rotate(5deg);
        box-shadow: 0 15px 50px rgba(0, 0, 0, 0.4);
    }
    
    .child-name-premium {
        font-size: 1.75rem;
        font-weight: 800;
        color: white;
        margin-bottom: 0.75rem;
        text-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        position: relative;
        z-index: 2;
    }
    
    .child-relationship-premium {
        color: rgba(255, 255, 255, 0.95);
        font-size: 1rem;
        font-weight: 600;
        position: relative;
        z-index: 2;
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        background: rgba(255, 255, 255, 0.15);
        padding: 0.5rem 1rem;
        border-radius: 20px;
        backdrop-filter: blur(10px);
    }
    
    /* Card Body Premium */
    .child-card-body-premium {
        padding: 2rem;
    }
    
    /* School Info Premium */
    .school-info-premium {
        background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);
        border-radius: 20px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border: 1px solid rgba(102, 126, 234, 0.1);
        position: relative;
        overflow: hidden;
    }
    
    .school-info-premium::before {
        content: '';
        position: absolute;
        top: 0;
        right: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
    }
    
    .info-item-premium {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin-bottom: 1rem;
        padding: 0.75rem;
        background: white;
        border-radius: 12px;
        transition: all 0.2s;
    }
    
    .info-item-premium:last-child {
        margin-bottom: 0;
    }
    
    .info-item-premium:hover {
        transform: translateX(-5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
    }
    
    .info-icon {
        width: 44px;
        height: 44px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.25rem;
        flex-shrink: 0;
    }
    
    .info-icon-school {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }
    
    .info-icon-class {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        color: white;
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3);
    }
    
    .info-content {
        flex: 1;
    }
    
    .info-label {
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 600;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 0.25rem;
    }
    
    .info-value {
        font-size: 1rem;
        font-weight: 700;
        color: #1a202c;
    }
    
    /* Stats Grid Premium */
    .child-stats-grid-premium {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
        margin-bottom: 1.5rem;
    }
    
    .child-stat-mini {
        background: linear-gradient(135deg, #ffffff 0%, #f8fafc 100%);
        border-radius: 16px;
        padding: 1.25rem;
        text-align: center;
        border: 2px solid transparent;
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        position: relative;
        overflow: hidden;
    }
    
    .child-stat-mini::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        background: linear-gradient(90deg, #667eea 0%, #764ba2 100%);
        transform: scaleX(0);
        transition: transform 0.3s;
    }
    
    .child-stat-mini:hover::before {
        transform: scaleX(1);
    }
    
    .child-stat-mini:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.1);
        border-color: rgba(102, 126, 234, 0.2);
    }
    
    .stat-mini-icon {
        font-size: 2rem;
        margin-bottom: 0.5rem;
        display: block;
    }
    
    .stat-mini-value {
        font-size: 1.5rem;
        font-weight: 800;
        margin-bottom: 0.25rem;
        background: linear-gradient(135deg, #1a202c 0%, #4a5568 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
    
    .stat-mini-label {
        font-size: 0.75rem;
        color: #64748b;
        font-weight: 600;
    }
    
    .stat-mini-1 .stat-mini-icon { color: #f59e0b; }
    .stat-mini-2 .stat-mini-icon { color: #3b82f6; }
    .stat-mini-3 .stat-mini-icon { color: #ef4444; }
    .stat-mini-4 .stat-mini-icon { color: #8b5cf6; }
    
    /* Rankings Premium */
    .rankings-premium {
        background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
        border-radius: 20px;
        padding: 1.5rem;
        margin-bottom: 1.5rem;
        border: 2px solid rgba(251, 191, 36, 0.2);
        position: relative;
        overflow: hidden;
    }
    
    .rankings-premium::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -50%;
        width: 200px;
        height: 200px;
        background: radial-gradient(circle, rgba(251, 191, 36, 0.1) 0%, transparent 70%);
        border-radius: 50%;
    }
    
    .rankings-title {
        font-size: 1rem;
        font-weight: 700;
        color: #9a3412;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
        position: relative;
        z-index: 2;
    }
    
    .rankings-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 0.75rem;
        position: relative;
        z-index: 2;
    }
    
    .rank-item-premium {
        background: white;
        border-radius: 12px;
        padding: 0.875rem;
        display: flex;
        align-items: center;
        justify-content: space-between;
        border: 1px solid rgba(251, 191, 36, 0.1);
        transition: all 0.2s;
    }
    
    .rank-item-premium:hover {
        transform: translateX(-3px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        border-color: rgba(251, 191, 36, 0.3);
    }
    
    .rank-label {
        display: flex;
        align-items: center;
        gap: 0.5rem;
        color: #6b7280;
        font-size: 0.8rem;
        font-weight: 600;
    }
    
    .rank-value {
        font-weight: 800;
        font-size: 1.1rem;
    }
    
    .rank-class .rank-value { color: #667eea; }
    .rank-school .rank-value { color: #10b981; }
    .rank-city .rank-value { color: #f59e0b; }
    .rank-country .rank-value { color: #8b5cf6; }
    
    /* Recent Activities Premium */
    .activities-section-premium {
        margin-bottom: 1.5rem;
    }
    
    .activities-title {
        font-size: 1rem;
        font-weight: 700;
        color: #374151;
        margin-bottom: 1rem;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .activities-list {
        max-height: 180px;
        overflow-y: auto;
        padding-right: 0.5rem;
    }
    
    .activity-item-premium {
        background: linear-gradient(135deg, #f9fafb 0%, #f3f4f6 100%);
        border-right: 4px solid #667eea;
        border-radius: 12px;
        padding: 1rem;
        margin-bottom: 0.75rem;
        transition: all 0.2s;
        position: relative;
        overflow: hidden;
    }
    
    .activity-item-premium::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        width: 4px;
        height: 100%;
        background: linear-gradient(180deg, #667eea 0%, #764ba2 100%);
        transform: scaleY(0);
        transition: transform 0.3s;
    }
    
    .activity-item-premium:hover::before {
        transform: scaleY(1);
    }
    
    .activity-item-premium:hover {
        transform: translateX(-5px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
        background: white;
    }
    
    .activity-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 1rem;
        position: relative;
        z-index: 2;
    }
    
    .activity-title {
        flex: 1;
        color: #1f2937;
        font-size: 0.875rem;
        font-weight: 600;
        display: flex;
        align-items: center;
        gap: 0.5rem;
    }
    
    .activity-date {
        font-size: 0.75rem;
        color: #9ca3af;
        white-space: nowrap;
    }
    
    /* Action Button Premium */
    .action-btn-premium {
        display: block;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        text-align: center;
        padding: 1.125rem;
        border-radius: 16px;
        text-decoration: none;
        font-weight: 700;
        font-size: 1rem;
        box-shadow: 0 8px 24px rgba(102, 126, 234, 0.35);
        transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1);
        position: relative;
        overflow: hidden;
        border: none;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.75rem;
    }
    
    .action-btn-premium::before {
        content: '';
        position: absolute;
        top: 50%;
        left: 50%;
        width: 0;
        height: 0;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.3);
        transform: translate(-50%, -50%);
        transition: width 0.6s, height 0.6s;
    }
    
    .action-btn-premium:hover::before {
        width: 300px;
        height: 300px;
    }
    
    .action-btn-premium:hover {
        transform: translateY(-3px) scale(1.02);
        box-shadow: 0 12px 32px rgba(102, 126, 234, 0.45);
    }
    
    .action-btn-premium:active {
        transform: translateY(-1px) scale(1);
    }
    
    .action-btn-text {
        position: relative;
        z-index: 2;
    }
    
    /* Custom Scrollbar */
    .activities-list::-webkit-scrollbar {
        width: 6px;
    }
    
    .activities-list::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 10px;
    }
    
    .activities-list::-webkit-scrollbar-thumb {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        border-radius: 10px;
    }
    
    .activities-list::-webkit-scrollbar-thumb:hover {
        background: #764ba2;
    }
    
    /* Responsive */
    @media (max-width: 768px) {
        .dashboard-hero h1 {
            font-size: 2rem;
        }
        
        .dashboard-hero p {
            font-size: 1rem;
        }
        
        .premium-stats-grid {
            grid-template-columns: 1fr;
            gap: 1rem;
        }
        
        .children-grid-premium {
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        
        .child-stats-grid-premium {
            grid-template-columns: repeat(2, 1fr);
            gap: 0.5rem;
        }
        
        .stat-value {
            font-size: 2rem;
        }
    }
    
    /* Reduced Motion */
    @media (prefers-reduced-motion: reduce) {
        * {
            animation-duration: 0.01ms !important;
            animation-iteration-count: 1 !important;
            transition-duration: 0.01ms !important;
        }
    }
</style>
@endpush

@section('content')
<div class="parent-dashboard-container">
    <!-- Premium Header Section -->
    <div class="dashboard-hero">
        <h1><span class="hero-icon">👨‍👩‍👧‍👦</span> <span class="hero-text">لوحة تحكم ولي الأمر</span></h1>
        <p>متابعة شاملة وتفصيلية لتقدم أبنائك الأكاديمي</p>
    </div>

    @if($childrenData->isEmpty())
        <!-- Premium Empty State -->
        <div class="premium-empty-state">
            <div class="empty-icon">👨‍👩‍👧‍👦</div>
            <h3 class="empty-title">لا يوجد أبناء مسجلين</h3>
            <p class="empty-text">لم يتم ربط أي طلاب بحسابك حتى الآن. يرجى التواصل مع إدارة المدرسة لإضافة أبنائك.</p>
        </div>
    @else
        <!-- Premium Statistics Overview -->
        <div class="premium-stats-grid">
            <div class="premium-stat-card stat-card-1">
                <div class="stat-icon-wrapper">👥</div>
                <div class="stat-value">{{ $childrenData->count() }}</div>
                <div class="stat-label">إجمالي الأبناء</div>
            </div>
            
            <div class="premium-stat-card stat-card-2">
                <div class="stat-icon-wrapper">⭐</div>
                <div class="stat-value">{{ number_format($childrenData->sum('total_points')) }}</div>
                <div class="stat-label">إجمالي النقاط</div>
            </div>
            
            <div class="premium-stat-card stat-card-3">
                <div class="stat-icon-wrapper">📚</div>
                <div class="stat-value">{{ $childrenData->sum('completed_lessons') }}</div>
                <div class="stat-label">الدروس المكتملة</div>
            </div>
            
            <div class="premium-stat-card stat-card-4">
                <div class="stat-icon-wrapper">🏅</div>
                <div class="stat-value">{{ $childrenData->sum('badges_count') }}</div>
                <div class="stat-label">إجمالي الشارات</div>
            </div>
        </div>

        {{-- إحصائيات مقارنة المدرسة --}}
        @if($schoolComparison)
        <div style="margin: 2rem 0; background: rgba(255,255,255,0.9); backdrop-filter: blur(50px) saturate(200%); -webkit-backdrop-filter: blur(50px) saturate(200%); border-radius: 28px; border: 1.5px solid rgba(255,255,255,0.4); box-shadow: 0 25px 70px rgba(0,0,0,0.15), 0 10px 30px rgba(0,0,0,0.1), inset 0 1px 0 rgba(255,255,255,0.6); overflow: hidden;">
            {{-- رأس القسم --}}
            <div style="background: linear-gradient(135deg, #0ea5e9 0%, #6366f1 50%, #8b5cf6 100%); padding: 1.75rem 2rem; position: relative; overflow: hidden;">
                <div style="position: absolute; top: -50%; right: -50%; width: 300px; height: 300px; background: radial-gradient(circle, rgba(255,255,255,0.15) 0%, transparent 70%); border-radius: 50%;"></div>
                <h2 style="margin: 0 0 0.5rem; font-size: 1.5rem; font-weight: 800; color: white; position: relative; z-index: 2; display: flex; align-items: center; gap: 0.75rem;">
                    <span>📊</span>
                    <span>مستوى مدرسة ابنك مقارنة بالمدارس الأخرى</span>
                </h2>
                <p style="margin: 0; color: rgba(255,255,255,0.9); font-size: 0.95rem; position: relative; z-index: 2;">{{ $schoolComparison['school_name'] }} — الترتيب <strong>#{{ $schoolComparison['school_rank'] }}</strong> من أصل {{ $schoolComparison['total_schools'] }} مدرسة</p>
            </div>

            {{-- بطاقات المقارنة --}}
            <div style="padding: 2rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 1.5rem;">
                {{-- الطلاب --}}
                <div style="background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); border-radius: 20px; padding: 1.5rem; border: 1px solid rgba(16,185,129,0.15); position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -20px; left: -20px; width: 80px; height: 80px; background: rgba(16,185,129,0.08); border-radius: 50%;"></div>
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; position: relative; z-index: 2;">
                        <span style="font-size: 1.5rem;">👨‍🎓</span>
                        <span style="font-weight: 700; font-size: 1rem; color: #065f46;">الطلاب</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 0.75rem;">
                        <div>
                            <div style="font-size: 0.75rem; color: #6b7280; font-weight: 600; margin-bottom: 0.25rem;">مدرستك</div>
                            <div style="font-size: 2rem; font-weight: 800; color: #059669;">{{ $schoolComparison['school_students'] }}</div>
                        </div>
                        <div style="text-align: left;">
                            <div style="font-size: 0.75rem; color: #6b7280; font-weight: 600; margin-bottom: 0.25rem;">متوسط المدارس</div>
                            <div style="font-size: 1.5rem; font-weight: 700; color: #94a3b8;">{{ $schoolComparison['all_avg_students'] }}</div>
                        </div>
                    </div>
                    @php $studentPct = $schoolComparison['all_avg_students'] > 0 ? min(100, round(($schoolComparison['school_students'] / $schoolComparison['all_avg_students']) * 100)) : 100; @endphp
                    <div style="height: 8px; background: rgba(16,185,129,0.15); border-radius: 4px; overflow: hidden;">
                        <div style="height: 100%; width: {{ $studentPct }}%; background: linear-gradient(90deg, #10b981, #059669); border-radius: 4px; transition: width 1s ease;"></div>
                    </div>
                    <div style="text-align: left; font-size: 0.75rem; color: {{ $studentPct >= 100 ? '#059669' : '#ef4444' }}; font-weight: 600; margin-top: 0.25rem;">{{ $studentPct >= 100 ? '↑ أعلى من المتوسط' : '↓ أقل من المتوسط' }}</div>
                </div>

                {{-- المعلمين --}}
                <div style="background: linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%); border-radius: 20px; padding: 1.5rem; border: 1px solid rgba(59,130,246,0.15); position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -20px; left: -20px; width: 80px; height: 80px; background: rgba(59,130,246,0.08); border-radius: 50%;"></div>
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; position: relative; z-index: 2;">
                        <span style="font-size: 1.5rem;">👩‍🏫</span>
                        <span style="font-weight: 700; font-size: 1rem; color: #1e40af;">المعلمين</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 0.75rem;">
                        <div>
                            <div style="font-size: 0.75rem; color: #6b7280; font-weight: 600; margin-bottom: 0.25rem;">مدرستك</div>
                            <div style="font-size: 2rem; font-weight: 800; color: #2563eb;">{{ $schoolComparison['school_teachers'] }}</div>
                        </div>
                        <div style="text-align: left;">
                            <div style="font-size: 0.75rem; color: #6b7280; font-weight: 600; margin-bottom: 0.25rem;">متوسط المدارس</div>
                            <div style="font-size: 1.5rem; font-weight: 700; color: #94a3b8;">{{ $schoolComparison['all_avg_teachers'] }}</div>
                        </div>
                    </div>
                    @php $teacherPct = $schoolComparison['all_avg_teachers'] > 0 ? min(100, round(($schoolComparison['school_teachers'] / $schoolComparison['all_avg_teachers']) * 100)) : 100; @endphp
                    <div style="height: 8px; background: rgba(59,130,246,0.15); border-radius: 4px; overflow: hidden;">
                        <div style="height: 100%; width: {{ $teacherPct }}%; background: linear-gradient(90deg, #3b82f6, #2563eb); border-radius: 4px; transition: width 1s ease;"></div>
                    </div>
                    <div style="text-align: left; font-size: 0.75rem; color: {{ $teacherPct >= 100 ? '#2563eb' : '#ef4444' }}; font-weight: 600; margin-top: 0.25rem;">{{ $teacherPct >= 100 ? '↑ أعلى من المتوسط' : '↓ أقل من المتوسط' }}</div>
                </div>

                {{-- متوسط النقاط --}}
                <div style="background: linear-gradient(135deg, #fefce8 0%, #fef9c3 100%); border-radius: 20px; padding: 1.5rem; border: 1px solid rgba(234,179,8,0.15); position: relative; overflow: hidden;">
                    <div style="position: absolute; top: -20px; left: -20px; width: 80px; height: 80px; background: rgba(234,179,8,0.08); border-radius: 50%;"></div>
                    <div style="display: flex; align-items: center; gap: 0.75rem; margin-bottom: 1rem; position: relative; z-index: 2;">
                        <span style="font-size: 1.5rem;">⭐</span>
                        <span style="font-weight: 700; font-size: 1rem; color: #854d0e;">متوسط النقاط</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 0.75rem;">
                        <div>
                            <div style="font-size: 0.75rem; color: #6b7280; font-weight: 600; margin-bottom: 0.25rem;">مدرستك</div>
                            <div style="font-size: 2rem; font-weight: 800; color: #ca8a04;">{{ number_format($schoolComparison['school_avg_points']) }}</div>
                        </div>
                        <div style="text-align: left;">
                            <div style="font-size: 0.75rem; color: #6b7280; font-weight: 600; margin-bottom: 0.25rem;">متوسط المدارس</div>
                            <div style="font-size: 1.5rem; font-weight: 700; color: #94a3b8;">{{ number_format($schoolComparison['all_avg_points']) }}</div>
                        </div>
                    </div>
                    @php $pointsPct = $schoolComparison['all_avg_points'] > 0 ? min(100, round(($schoolComparison['school_avg_points'] / $schoolComparison['all_avg_points']) * 100)) : 100; @endphp
                    <div style="height: 8px; background: rgba(234,179,8,0.15); border-radius: 4px; overflow: hidden;">
                        <div style="height: 100%; width: {{ $pointsPct }}%; background: linear-gradient(90deg, #eab308, #ca8a04); border-radius: 4px; transition: width 1s ease;"></div>
                    </div>
                    <div style="text-align: left; font-size: 0.75rem; color: {{ $pointsPct >= 100 ? '#ca8a04' : '#ef4444' }}; font-weight: 600; margin-top: 0.25rem;">{{ $pointsPct >= 100 ? '↑ أعلى من المتوسط' : '↓ أقل من المتوسط' }}</div>
                </div>
            </div>
        </div>
        @endif

        <!-- Premium Children Cards -->
        <div class="children-grid-premium">
            @foreach($childrenData as $child)
                <div class="premium-child-card">
                    <!-- Premium Gradient Header -->
                    <div class="child-card-header-premium">
                        <div style="position: relative; z-index: 2;">
                            @php $childInitial = mb_substr($child['name'] ?? '?', 0, 1); @endphp
                            <img src="{{ $child['avatar_url'] ?? '' }}"
                                 alt="{{ $child['name'] }}"
                                 class="child-avatar-premium"
                                 onerror="this.outerHTML='<div class=&quot;child-avatar-premium&quot; style=&quot;display:flex;align-items:center;justify-content:center;font-weight:800;color:white;background:linear-gradient(135deg,#667eea,#764ba2);font-size:42px;&quot;>{{ $childInitial }}</div>'">
                            <h3 class="child-name-premium">{{ $child['name'] }}</h3>
                            <div class="child-relationship-premium">
                                <span>👤</span>
                                <span>{{ $child['relationship'] }}</span>
                            </div>
                        </div>
                    </div>

                    <!-- Premium Card Body -->
                    <div class="child-card-body-premium">
                        <!-- School & Class Info Premium -->
                        <div class="school-info-premium">
                            <div class="info-item-premium">
                                <div class="info-icon info-icon-school">
                                    🏫
                                </div>
                                <div class="info-content">
                                    <div class="info-label">المدرسة</div>
                                    <div class="info-value">{{ $child['school'] }}</div>
                                </div>
                            </div>
                            <div class="info-item-premium">
                                <div class="info-icon info-icon-class">
                                    📚
                                </div>
                                <div class="info-content">
                                    <div class="info-label">الفصل / المرحلة</div>
                                    <div class="info-value">{{ $child['classroom'] }} - {{ $child['grade'] }}</div>
                                </div>
                            </div>
                        </div>

                        <!-- Statistics Grid Premium -->
                        <div class="child-stats-grid-premium">
                            <div class="child-stat-mini stat-mini-1">
                                <span class="stat-mini-icon">⭐</span>
                                <div class="stat-mini-value">{{ number_format($child['total_points']) }}</div>
                                <div class="stat-mini-label">نقطة</div>
                            </div>
                            
                            <div class="child-stat-mini stat-mini-2">
                                <span class="stat-mini-icon">📖</span>
                                <div class="stat-mini-value">{{ $child['completed_lessons'] }}</div>
                                <div class="stat-mini-label">درس مكتمل</div>
                            </div>
                            
                            <div class="child-stat-mini stat-mini-3">
                                <span class="stat-mini-icon">🔥</span>
                                <div class="stat-mini-value">{{ $child['streak_days'] }}</div>
                                <div class="stat-mini-label">يوم متتالي</div>
                            </div>
                            
                            <div class="child-stat-mini stat-mini-4">
                                <span class="stat-mini-icon">🏅</span>
                                <div class="stat-mini-value">{{ $child['badges_count'] }}</div>
                                <div class="stat-mini-label">شارة</div>
                            </div>
                        </div>

                        <!-- Rankings Premium -->
                        @if($child['class_rank'] || $child['school_rank'] || $child['city_rank'] || $child['country_rank'])
                        <div class="rankings-premium">
                            <h4 class="rankings-title">
                                <span>🏆</span>
                                <span>الترتيب والإنجازات</span>
                            </h4>
                            <div class="rankings-grid">
                                @if($child['class_rank'])
                                    <div class="rank-item-premium rank-class">
                                        <div class="rank-label">
                                            <span>👥</span>
                                            <span>الفصل</span>
                                        </div>
                                        <div class="rank-value">#{{ $child['class_rank'] }}</div>
                                    </div>
                                @endif
                                
                                @if($child['school_rank'])
                                    <div class="rank-item-premium rank-school">
                                        <div class="rank-label">
                                            <span>🏫</span>
                                            <span>المدرسة</span>
                                        </div>
                                        <div class="rank-value">#{{ $child['school_rank'] }}</div>
                                    </div>
                                @endif
                                
                                @if($child['city_rank'])
                                    <div class="rank-item-premium rank-city">
                                        <div class="rank-label">
                                            <span>🏙️</span>
                                            <span>المدينة</span>
                                        </div>
                                        <div class="rank-value">#{{ $child['city_rank'] }}</div>
                                    </div>
                                @endif
                                
                                @if($child['country_rank'])
                                    <div class="rank-item-premium rank-country">
                                        <div class="rank-label">
                                            <span>🌍</span>
                                            <span>الدولة</span>
                                        </div>
                                        <div class="rank-value">#{{ $child['country_rank'] }}</div>
                                    </div>
                                @endif
                            </div>
                        </div>
                        @endif

                        <!-- Recent Activities Premium -->
                        @if($child['recent_activities']->isNotEmpty())
                            <div class="activities-section-premium">
                                <h4 class="activities-title">
                                    <span>⏰</span>
                                    <span>آخر الأنشطة</span>
                                </h4>
                                <div class="activities-list">
                                    @foreach($child['recent_activities']->take(3) as $activity)
                                        <div class="activity-item-premium">
                                            <div class="activity-content">
                                                <div class="activity-title">
                                                    <span>✅</span>
                                                    <span>{{ \Illuminate\Support\Str::limit($activity->activity->title ?? 'نشاط', 35) }}</span>
                                                </div>
                                                <div class="activity-date">{{ $activity->created_at->diffForHumans() }}</div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif

                        <!-- Action Button Premium -->
                        <a href="{{ route('parent.child.details', $child['id']) }}" class="action-btn-premium">
                            <span class="action-btn-text">📊 عرض التفاصيل الكاملة</span>
                        </a>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
