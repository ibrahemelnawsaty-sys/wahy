@extends('layouts.parent')

@section('title', 'تفاصيل ' . $child->name)

@push('styles')
<style>
    /* ===== CSS Variables ===== */
    :root {
        --primary: #667eea;
        --primary-dark: #5a67d8;
        --secondary: #764ba2;
        --accent: #f093fb;
        --success: #48bb78;
        --warning: #f6ad55;
        --danger: #fc8181;
        --gold: #ecc94b;
        --dark: #1a202c;
        --gray-800: #2d3748;
        --gray-600: #718096;
        --gray-400: #a0aec0;
        --gray-200: #e2e8f0;
        --gray-100: #f7fafc;
        --card-bg: rgba(255, 255, 255, 0.95);
        --glass: rgba(255, 255, 255, 0.15);
        --glass-border: rgba(255, 255, 255, 0.25);
        --radius-sm: 10px;
        --radius-md: 16px;
        --radius-lg: 24px;
        --radius-xl: 32px;
        --shadow-sm: 0 2px 8px rgba(0,0,0,0.06);
        --shadow-md: 0 8px 32px rgba(0,0,0,0.08);
        --shadow-lg: 0 16px 48px rgba(0,0,0,0.12);
        --shadow-glow: 0 0 40px rgba(102, 126, 234, 0.15);
    }

    /* ===== Animations ===== */
    @keyframes fadeInUp {
        from { opacity: 0; transform: translateY(30px); }
        to { opacity: 1; transform: translateY(0); }
    }
    @keyframes fadeInScale {
        from { opacity: 0; transform: scale(0.9); }
        to { opacity: 1; transform: scale(1); }
    }
    @keyframes shimmer {
        0% { background-position: -200% center; }
        100% { background-position: 200% center; }
    }
    @keyframes float {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
    }
    @keyframes pulse-ring {
        0% { transform: scale(0.95); opacity: 1; }
        100% { transform: scale(1.3); opacity: 0; }
    }
    @keyframes progressGrow {
        from { width: 0; }
    }
    @keyframes countUp {
        from { opacity: 0; transform: translateY(10px); }
        to { opacity: 1; transform: translateY(0); }
    }

    .animate-in { animation: fadeInUp 0.6s ease-out forwards; opacity: 0; }
    .animate-scale { animation: fadeInScale 0.5s ease-out forwards; opacity: 0; }
    .delay-1 { animation-delay: 0.1s; }
    .delay-2 { animation-delay: 0.2s; }
    .delay-3 { animation-delay: 0.3s; }
    .delay-4 { animation-delay: 0.4s; }
    .delay-5 { animation-delay: 0.5s; }
    .delay-6 { animation-delay: 0.6s; }

    /* ===== Page Container ===== */
    .cd-page {
        max-width: 1300px;
        margin: 0 auto;
        padding: 30px 20px 60px;
    }

    /* ===== Back Button ===== */
    .cd-back {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        color: rgba(255,255,255,0.9);
        text-decoration: none;
        font-weight: 600;
        font-size: 15px;
        padding: 12px 24px;
        border-radius: var(--radius-md);
        background: var(--glass);
        backdrop-filter: blur(20px);
        border: 1px solid var(--glass-border);
        transition: all 0.3s;
        margin-bottom: 24px;
    }
    .cd-back:hover {
        background: rgba(255,255,255,0.25);
        transform: translateX(5px);
        color: white;
    }

    /* ===== Hero Header ===== */
    .cd-hero {
        background: var(--card-bg);
        backdrop-filter: blur(40px);
        border-radius: var(--radius-xl);
        padding: 0;
        box-shadow: var(--shadow-lg), var(--shadow-glow);
        overflow: hidden;
        margin-bottom: 28px;
        border: 1px solid rgba(255,255,255,0.5);
    }

    .cd-hero-top {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 60%, var(--accent) 100%);
        padding: 40px 40px 60px;
        position: relative;
        overflow: hidden;
    }
    .cd-hero-top::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -20%;
        width: 400px;
        height: 400px;
        border-radius: 50%;
        background: rgba(255,255,255,0.06);
    }
    .cd-hero-top::after {
        content: '';
        position: absolute;
        bottom: -60%;
        left: -10%;
        width: 300px;
        height: 300px;
        border-radius: 50%;
        background: rgba(255,255,255,0.04);
    }

    .cd-hero-content {
        display: flex;
        align-items: center;
        gap: 30px;
        position: relative;
        z-index: 2;
    }

    .cd-avatar {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        backdrop-filter: blur(10px);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 44px;
        font-weight: 800;
        color: white;
        border: 4px solid rgba(255,255,255,0.35);
        flex-shrink: 0;
        position: relative;
        animation: float 4s ease-in-out infinite;
    }
    .cd-avatar::after {
        content: '';
        position: absolute;
        inset: -6px;
        border-radius: 50%;
        border: 2px solid rgba(255,255,255,0.15);
        animation: pulse-ring 2.5s ease-out infinite;
    }

    .cd-hero-info { flex: 1; }
    .cd-hero-name {
        font-size: 34px;
        font-weight: 800;
        color: white;
        margin-bottom: 8px;
        letter-spacing: -0.5px;
    }
    .cd-hero-meta {
        display: flex;
        flex-wrap: wrap;
        gap: 18px;
        margin-top: 10px;
    }
    .cd-meta-tag {
        display: flex;
        align-items: center;
        gap: 8px;
        color: rgba(255,255,255,0.9);
        font-size: 14px;
        font-weight: 500;
        background: rgba(255,255,255,0.12);
        padding: 6px 16px;
        border-radius: 50px;
        backdrop-filter: blur(5px);
    }

    /* ===== Level Bar ===== */
    .cd-hero-bottom {
        padding: 24px 40px;
        display: flex;
        align-items: center;
        gap: 24px;
        background: white;
    }
    .cd-level-badge {
        background: linear-gradient(135deg, var(--gold) 0%, #d69e2e 100%);
        color: white;
        font-size: 15px;
        font-weight: 800;
        padding: 10px 20px;
        border-radius: var(--radius-sm);
        white-space: nowrap;
        box-shadow: 0 4px 12px rgba(236,201,75,0.3);
        text-shadow: 0 1px 3px rgba(0,0,0,0.15);
    }
    .cd-level-progress {
        flex: 1;
    }
    .cd-level-text {
        display: flex;
        justify-content: space-between;
        font-size: 13px;
        color: var(--gray-600);
        margin-bottom: 6px;
        font-weight: 600;
    }
    .cd-progress-track {
        height: 10px;
        background: var(--gray-200);
        border-radius: 10px;
        overflow: hidden;
    }
    .cd-progress-fill {
        height: 100%;
        background: linear-gradient(90deg, var(--primary), var(--secondary), var(--accent));
        background-size: 200% auto;
        animation: shimmer 3s linear infinite, progressGrow 1.5s ease-out;
        border-radius: 10px;
    }
    .cd-hero-encourage-btn {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 12px 24px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        background-size: 200% auto;
        color: white;
        border: none;
        border-radius: var(--radius-sm);
        font-size: 14px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.35s;
        box-shadow: 0 4px 16px rgba(102, 126, 234, 0.35);
        white-space: nowrap;
        font-family: 'IBM Plex Sans Arabic', sans-serif;
        animation: shimmer 3s linear infinite;
    }
    .cd-hero-encourage-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(102, 126, 234, 0.45);
    }
    .cd-hero-encourage-btn i {
        color: #ff6b9d;
    }
    .cd-hero-encourage-btn small {
        font-size: 11px;
        opacity: 0.8;
        background: rgba(255,255,255,0.2);
        padding: 2px 8px;
        border-radius: 20px;
    }

    /* ===== Stats Grid ===== */
    .cd-stats {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
        gap: 18px;
        margin-bottom: 28px;
    }
    .cd-stat {
        background: var(--card-bg);
        border-radius: var(--radius-lg);
        padding: 28px 20px;
        text-align: center;
        box-shadow: var(--shadow-md);
        border: 1px solid rgba(255,255,255,0.5);
        transition: all 0.35s cubic-bezier(0.34, 1.56, 0.64, 1);
        position: relative;
        overflow: hidden;
    }
    .cd-stat:hover {
        transform: translateY(-6px);
        box-shadow: var(--shadow-lg), var(--shadow-glow);
    }
    .cd-stat::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        height: 4px;
        border-radius: 4px 4px 0 0;
    }
    .cd-stat:nth-child(1)::before { background: linear-gradient(90deg, #667eea, #764ba2); }
    .cd-stat:nth-child(2)::before { background: linear-gradient(90deg, #ecc94b, #d69e2e); }
    .cd-stat:nth-child(3)::before { background: linear-gradient(90deg, #48bb78, #38a169); }
    .cd-stat:nth-child(4)::before { background: linear-gradient(90deg, #f6ad55, #ed8936); }
    .cd-stat:nth-child(5)::before { background: linear-gradient(90deg, #fc8181, #f56565); }
    .cd-stat:nth-child(6)::before { background: linear-gradient(90deg, #f093fb, #ec4899); }

    .cd-stat-icon {
        font-size: 42px;
        margin-bottom: 12px;
        display: block;
    }
    .cd-stat-value {
        font-size: 34px;
        font-weight: 800;
        color: var(--dark);
        line-height: 1;
        margin-bottom: 6px;
        animation: countUp 0.8s ease-out;
    }
    .cd-stat-label {
        font-size: 13px;
        color: var(--gray-600);
        font-weight: 600;
    }

    /* ===== Two-Column Layout ===== */
    .cd-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 28px;
        margin-bottom: 28px;
    }
    .cd-grid-full {
        grid-column: 1 / -1;
    }

    /* ===== Card ===== */
    .cd-card {
        background: var(--card-bg);
        border-radius: var(--radius-lg);
        padding: 28px;
        box-shadow: var(--shadow-md);
        border: 1px solid rgba(255,255,255,0.5);
        transition: all 0.3s;
    }
    .cd-card:hover {
        box-shadow: var(--shadow-lg);
    }
    .cd-card-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        gap: 10px;
        padding-bottom: 16px;
        border-bottom: 2px solid var(--gray-200);
    }
    .cd-card-title i {
        color: var(--primary);
    }

    /* ===== Ranking Cards ===== */
    .cd-rankings {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 14px;
    }
    .cd-rank-item {
        background: linear-gradient(135deg, #f7fafc, #edf2f7);
        border-radius: var(--radius-md);
        padding: 20px;
        text-align: center;
        border: 1px solid var(--gray-200);
        transition: all 0.3s;
    }
    .cd-rank-item:hover {
        background: linear-gradient(135deg, #ebf8ff, #e2e8f0);
        transform: scale(1.02);
    }
    .cd-rank-icon {
        font-size: 30px;
        margin-bottom: 8px;
    }
    .cd-rank-value {
        font-size: 28px;
        font-weight: 800;
        color: var(--primary-dark);
    }
    .cd-rank-label {
        font-size: 12px;
        color: var(--gray-600);
        font-weight: 600;
        margin-top: 4px;
    }
    .cd-rank-total {
        font-size: 11px;
        color: var(--gray-400);
        margin-top: 2px;
    }

    /* ===== Weekly Heatmap ===== */
    .cd-week-grid {
        display: grid;
        grid-template-columns: repeat(7, 1fr);
        gap: 10px;
    }
    .cd-week-day {
        text-align: center;
        padding: 16px 8px;
        border-radius: var(--radius-md);
        background: var(--gray-100);
        border: 2px solid transparent;
        transition: all 0.3s;
    }
    .cd-week-day.active {
        background: linear-gradient(135deg, rgba(102,126,234,0.1), rgba(118,75,162,0.1));
        border-color: var(--primary);
    }
    .cd-week-day.today {
        box-shadow: 0 0 0 3px rgba(102,126,234,0.2);
    }
    .cd-week-name {
        font-size: 12px;
        font-weight: 700;
        color: var(--gray-600);
        margin-bottom: 8px;
    }
    .cd-week-count {
        font-size: 26px;
        font-weight: 800;
        color: var(--dark);
    }
    .cd-week-count.zero { color: var(--gray-400); }
    .cd-week-date {
        font-size: 11px;
        color: var(--gray-400);
        margin-top: 4px;
    }

    /* ===== Progress Chart ===== */
    .cd-chart-container {
        position: relative;
        height: 300px;
    }

    /* ===== Values Progress ===== */
    .cd-value-item {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 16px 0;
        border-bottom: 1px solid var(--gray-200);
    }
    .cd-value-item:last-child { border-bottom: none; }
    .cd-value-icon {
        width: 48px;
        height: 48px;
        border-radius: var(--radius-sm);
        background: linear-gradient(135deg, rgba(102,126,234,0.12), rgba(118,75,162,0.12));
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 22px;
        flex-shrink: 0;
    }
    .cd-value-info { flex: 1; }
    .cd-value-name {
        font-weight: 700;
        color: var(--dark);
        margin-bottom: 6px;
        font-size: 15px;
    }
    .cd-value-bar {
        height: 8px;
        background: var(--gray-200);
        border-radius: 8px;
        overflow: hidden;
    }
    .cd-value-fill {
        height: 100%;
        border-radius: 8px;
        background: linear-gradient(90deg, var(--primary), var(--secondary));
        transition: width 1.5s ease-out;
    }
    .cd-value-stats {
        text-align: center;
        min-width: 60px;
    }
    .cd-value-score {
        font-size: 20px;
        font-weight: 800;
        color: var(--primary);
    }
    .cd-value-count {
        font-size: 11px;
        color: var(--gray-400);
    }

    /* ===== Activities ===== */
    .cd-activity {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 18px;
        border-radius: var(--radius-md);
        border: 1.5px solid var(--gray-200);
        margin-bottom: 12px;
        transition: all 0.3s;
        background: white;
    }
    .cd-activity:hover {
        border-color: var(--primary);
        box-shadow: 0 4px 16px rgba(102,126,234,0.12);
        transform: translateX(-4px);
    }
    .cd-activity-icon {
        width: 50px;
        height: 50px;
        border-radius: var(--radius-sm);
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 24px;
        flex-shrink: 0;
    }
    .cd-activity-icon.completed {
        background: linear-gradient(135deg, #c6f6d5, #9ae6b4);
    }
    .cd-activity-icon.pending {
        background: linear-gradient(135deg, #fefcbf, #fef08a);
    }
    .cd-activity-icon.failed {
        background: linear-gradient(135deg, #fed7d7, #feb2b2);
    }
    .cd-activity-info { flex: 1; min-width: 0; }
    .cd-activity-title {
        font-weight: 700;
        color: var(--dark);
        font-size: 15px;
        margin-bottom: 4px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .cd-activity-lesson {
        font-size: 13px;
        color: var(--gray-600);
    }
    .cd-activity-time {
        font-size: 12px;
        color: var(--gray-400);
        margin-top: 2px;
    }
    .cd-activity-score {
        padding: 6px 16px;
        border-radius: 50px;
        font-weight: 700;
        font-size: 14px;
        flex-shrink: 0;
    }
    .cd-activity-score.high {
        background: linear-gradient(135deg, #48bb78, #38a169);
        color: white;
    }
    .cd-activity-score.mid {
        background: linear-gradient(135deg, #f6ad55, #ed8936);
        color: white;
    }
    .cd-activity-score.low {
        background: linear-gradient(135deg, #fc8181, #f56565);
        color: white;
    }

    /* ===== Badges ===== */
    .cd-badges {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(130px, 1fr));
        gap: 14px;
    }
    .cd-badge {
        text-align: center;
        padding: 24px 12px;
        background: linear-gradient(135deg, #f7fafc, #edf2f7);
        border-radius: var(--radius-md);
        border: 2px solid var(--gray-200);
        transition: all 0.4s cubic-bezier(0.34, 1.56, 0.64, 1);
    }
    .cd-badge:hover {
        transform: translateY(-6px) scale(1.05);
        border-color: var(--gold);
        box-shadow: 0 12px 24px rgba(236,201,75,0.2);
    }
    .cd-badge-icon {
        font-size: 44px;
        margin-bottom: 10px;
        display: block;
    }
    .cd-badge-name {
        font-size: 13px;
        font-weight: 700;
        color: var(--gray-800);
    }
    .cd-badge-date {
        font-size: 11px;
        color: var(--gray-400);
        margin-top: 4px;
    }

    /* ===== Teachers ===== */
    .cd-teacher {
        display: flex;
        align-items: center;
        gap: 16px;
        padding: 18px;
        border-radius: var(--radius-md);
        background: linear-gradient(135deg, #f7fafc, #edf2f7);
        margin-bottom: 12px;
        transition: all 0.3s;
        border: 1px solid transparent;
    }
    .cd-teacher:hover {
        background: linear-gradient(135deg, #ebf8ff, #e2e8f0);
        border-color: var(--primary);
        transform: translateX(-4px);
    }
    .cd-teacher-avatar {
        width: 52px;
        height: 52px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 800;
        font-size: 20px;
        flex-shrink: 0;
    }
    .cd-teacher-info { flex: 1; }
    .cd-teacher-name {
        font-weight: 700;
        color: var(--dark);
        font-size: 16px;
    }
    .cd-teacher-email {
        font-size: 13px;
        color: var(--gray-600);
    }
    .cd-teacher-btn {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        border: none;
        padding: 10px 22px;
        border-radius: var(--radius-sm);
        cursor: pointer;
        font-weight: 700;
        font-size: 14px;
        transition: all 0.3s;
        text-decoration: none;
    }
    .cd-teacher-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(102,126,234,0.35);
        color: white;
    }

    /* ===== Empty State ===== */
    .cd-empty {
        text-align: center;
        padding: 50px 20px;
        color: var(--gray-400);
    }
    .cd-empty-icon {
        font-size: 54px;
        margin-bottom: 14px;
        opacity: 0.6;
    }
    .cd-empty-text {
        font-size: 15px;
        font-weight: 600;
    }

    /* ===== Streak Card Special ===== */
    .cd-streak-card {
        background: linear-gradient(135deg, #ff6b35, #f7931a, #ffd700);
        border-radius: var(--radius-lg);
        padding: 28px;
        color: white;
        text-align: center;
        box-shadow: 0 8px 24px rgba(255,107,53,0.3);
        position: relative;
        overflow: hidden;
    }
    .cd-streak-card::before {
        content: '🔥';
        position: absolute;
        top: -10px;
        left: 10px;
        font-size: 80px;
        opacity: 0.15;
    }
    .cd-streak-value {
        font-size: 56px;
        font-weight: 900;
        line-height: 1;
        margin-bottom: 6px;
        text-shadow: 0 3px 8px rgba(0,0,0,0.2);
    }
    .cd-streak-label {
        font-size: 16px;
        font-weight: 600;
        opacity: 0.95;
    }
    .cd-streak-best {
        margin-top: 12px;
        font-size: 13px;
        opacity: 0.8;
        background: rgba(255,255,255,0.15);
        display: inline-block;
        padding: 4px 14px;
        border-radius: 50px;
    }

    /* ===== Encouragement Button ===== */
    .cd-encourage-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        width: 100%;
        padding: 18px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 50%, #f093fb 100%);
        background-size: 200% auto;
        color: white;
        border: none;
        border-radius: var(--radius-lg);
        font-size: 18px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.4s;
        box-shadow: 0 8px 24px rgba(102, 126, 234, 0.35);
        margin-bottom: 28px;
        font-family: 'IBM Plex Sans Arabic', sans-serif;
        animation: shimmer 3s linear infinite;
    }
    .cd-encourage-btn:hover {
        transform: translateY(-3px);
        box-shadow: 0 12px 32px rgba(102, 126, 234, 0.45);
    }
    .cd-encourage-btn:active {
        transform: translateY(0);
    }
    .cd-encourage-btn i {
        font-size: 22px;
    }

    /* ===== Encouragement Modal ===== */
    .cd-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.6);
        backdrop-filter: blur(8px);
        z-index: 9999;
        justify-content: center;
        align-items: center;
        padding: 20px;
    }
    .cd-modal-overlay.active {
        display: flex;
    }
    .cd-modal {
        background: white;
        border-radius: var(--radius-xl);
        width: 100%;
        max-width: 650px;
        max-height: 90vh;
        overflow-y: auto;
        box-shadow: 0 32px 80px rgba(0, 0, 0, 0.3);
        animation: fadeInScale 0.35s ease-out;
    }
    .cd-modal-header {
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 60%, var(--accent) 100%);
        padding: 28px 32px;
        border-radius: var(--radius-xl) var(--radius-xl) 0 0;
        color: white;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }
    .cd-modal-title {
        font-size: 22px;
        font-weight: 800;
    }
    .cd-modal-close {
        background: rgba(255,255,255,0.2);
        border: none;
        color: white;
        width: 40px;
        height: 40px;
        border-radius: 50%;
        font-size: 20px;
        cursor: pointer;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    .cd-modal-close:hover {
        background: rgba(255,255,255,0.35);
        transform: rotate(90deg);
    }
    .cd-modal-body {
        padding: 28px 32px;
    }

    /* Categories */
    .cd-praise-categories {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
        margin-bottom: 24px;
    }
    .cd-praise-cat {
        text-align: center;
        padding: 14px 8px;
        border-radius: var(--radius-md);
        border: 2px solid var(--gray-200);
        cursor: pointer;
        transition: all 0.3s;
        background: white;
        font-family: 'IBM Plex Sans Arabic', sans-serif;
    }
    .cd-praise-cat:hover {
        border-color: var(--primary);
        background: rgba(102,126,234,0.04);
    }
    .cd-praise-cat.active {
        border-color: var(--primary);
        background: linear-gradient(135deg, rgba(102,126,234,0.1), rgba(118,75,162,0.1));
        box-shadow: 0 4px 12px rgba(102,126,234,0.15);
    }
    .cd-praise-cat-icon {
        font-size: 28px;
        margin-bottom: 6px;
    }
    .cd-praise-cat-label {
        font-size: 12px;
        font-weight: 700;
        color: var(--gray-800);
    }

    /* Suggested Texts */
    .cd-suggestions {
        margin-bottom: 20px;
    }
    .cd-suggest-title {
        font-size: 14px;
        font-weight: 700;
        color: var(--gray-600);
        margin-bottom: 10px;
        display: flex;
        align-items: center;
        gap: 6px;
    }
    .cd-suggest-grid {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .cd-suggest-item {
        padding: 12px 16px;
        border-radius: var(--radius-sm);
        border: 1.5px solid var(--gray-200);
        cursor: pointer;
        transition: all 0.25s;
        font-size: 13px;
        font-weight: 500;
        color: var(--gray-800);
        background: var(--gray-100);
        line-height: 1.6;
        font-family: 'IBM Plex Sans Arabic', sans-serif;
    }
    .cd-suggest-item:hover {
        border-color: var(--primary);
        background: rgba(102,126,234,0.06);
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.06);
    }
    .cd-suggest-item.selected {
        border-color: var(--primary);
        background: linear-gradient(135deg, rgba(102,126,234,0.08), rgba(118,75,162,0.08));
        color: var(--primary-dark);
        font-weight: 700;
    }

    /* Textarea */
    .cd-praise-textarea {
        width: 100%;
        min-height: 100px;
        padding: 16px;
        border: 2px solid var(--gray-200);
        border-radius: var(--radius-md);
        font-family: 'IBM Plex Sans Arabic', sans-serif;
        font-size: 15px;
        resize: vertical;
        transition: border-color 0.3s;
        color: var(--dark);
    }
    .cd-praise-textarea:focus {
        outline: none;
        border-color: var(--primary);
        box-shadow: 0 0 0 4px rgba(102,126,234,0.1);
    }
    .cd-praise-textarea::placeholder {
        color: var(--gray-400);
    }

    /* Submit */
    .cd-praise-submit {
        width: 100%;
        padding: 16px;
        background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
        color: white;
        border: none;
        border-radius: var(--radius-md);
        font-size: 17px;
        font-weight: 700;
        cursor: pointer;
        transition: all 0.3s;
        margin-top: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 10px;
        font-family: 'IBM Plex Sans Arabic', sans-serif;
    }
    .cd-praise-submit:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(102,126,234,0.4);
    }
    .cd-praise-submit:disabled {
        opacity: 0.6;
        cursor: not-allowed;
        transform: none;
    }
    .cd-praise-submit .spinner {
        display: none;
        width: 20px;
        height: 20px;
        border: 3px solid rgba(255,255,255,0.3);
        border-top-color: white;
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
    }
    @keyframes spin {
        to { transform: rotate(360deg); }
    }

    /* Success popup */
    .cd-success-popup {
        display: none;
        position: fixed;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%) scale(0.8);
        background: white;
        border-radius: var(--radius-xl);
        padding: 50px 40px;
        text-align: center;
        z-index: 10001;
        box-shadow: 0 32px 80px rgba(0,0,0,0.25);
        animation: fadeInScale 0.4s ease-out forwards;
    }
    .cd-success-popup.show {
        display: block;
    }
    .cd-success-icon {
        font-size: 80px;
        margin-bottom: 16px;
        animation: float 2s ease-in-out infinite;
    }
    .cd-success-title {
        font-size: 24px;
        font-weight: 800;
        color: var(--dark);
        margin-bottom: 8px;
    }
    .cd-success-text {
        font-size: 15px;
        color: var(--gray-600);
        margin-bottom: 24px;
    }
    .cd-success-close {
        background: linear-gradient(135deg, var(--primary), var(--secondary));
        color: white;
        border: none;
        padding: 12px 32px;
        border-radius: var(--radius-sm);
        font-weight: 700;
        cursor: pointer;
        font-size: 15px;
        font-family: 'IBM Plex Sans Arabic', sans-serif;
    }

    @media (max-width: 600px) {
        .cd-praise-categories { grid-template-columns: repeat(2, 1fr); }
        .cd-suggest-grid { grid-template-columns: 1fr; }
        .cd-modal-body { padding: 20px; }
        .cd-modal-header { padding: 20px; }
    }

    /* ===== Responsive ===== */
    @media (max-width: 900px) {
        .cd-grid { grid-template-columns: 1fr; }
        .cd-hero-content { flex-direction: column; text-align: center; }
        .cd-hero-meta { justify-content: center; }
        .cd-hero-bottom { flex-direction: column; text-align: center; }
        .cd-rankings { grid-template-columns: 1fr 1fr; }
        .cd-stats { grid-template-columns: repeat(3, 1fr); }
        .cd-hero-name { font-size: 26px; }
    }
    @media (max-width: 600px) {
        .cd-page { padding: 16px 12px 40px; }
        .cd-stats { grid-template-columns: repeat(2, 1fr); gap: 10px; }
        .cd-stat { padding: 20px 12px; }
        .cd-stat-value { font-size: 26px; }
        .cd-week-grid { grid-template-columns: repeat(7, 1fr); gap: 4px; }
        .cd-week-day { padding: 10px 4px; }
        .cd-week-count { font-size: 18px; }
        .cd-hero-top { padding: 24px 20px 40px; }
        .cd-hero-bottom { padding: 20px; }
        .cd-avatar { width: 80px; height: 80px; font-size: 32px; }
        .cd-badges { grid-template-columns: repeat(3, 1fr); }
    }
</style>
@endpush

@section('content')
<div class="cd-page">
    {{-- Back Button --}}
    <a href="{{ route('parent.dashboard') }}" class="cd-back animate-in">
        <i class="fas fa-arrow-right"></i>
        العودة للوحة التحكم
    </a>

    {{-- Hero Header --}}
    <div class="cd-hero animate-in delay-1">
        <div class="cd-hero-top">
            <div class="cd-hero-content">
                <div class="cd-avatar">
                    {{ mb_substr($child->name, 0, 1) }}
                </div>
                <div class="cd-hero-info">
                    <h1 class="cd-hero-name">{{ $child->name }}</h1>
                    <div class="cd-hero-meta">
                        @if($child->school)
                        <span class="cd-meta-tag">
                            <i class="fas fa-school"></i>
                            {{ $child->school->name }}
                        </span>
                        @endif
                        @if($classroom)
                        <span class="cd-meta-tag">
                            <i class="fas fa-chalkboard"></i>
                            {{ $classroom->name }}
                        </span>
                        @endif
                        <span class="cd-meta-tag">
                            <i class="fas fa-calendar"></i>
                            انضم {{ $child->created_at->diffForHumans() }}
                        </span>
                        @if($child->email)
                        <span class="cd-meta-tag">
                            <i class="fas fa-envelope"></i>
                            {{ $child->email }}
                        </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
        <div class="cd-hero-bottom">
            <div class="cd-level-badge">
                ⭐ المستوى {{ $level }}
            </div>
            <div class="cd-level-progress">
                <div class="cd-level-text">
                    <span>التقدم نحو المستوى {{ $level + 1 }}</span>
                    <span>{{ $levelProgress }}%</span>
                </div>
                <div class="cd-progress-track">
                    <div class="cd-progress-fill" style="width: {{ $levelProgress }}%"></div>
                </div>
            </div>
            <button class="cd-hero-encourage-btn" onclick="openEncourageModal()">
                <i class="fas fa-heart"></i>
                <span>💪 أرسل تحفيز</span>
                <small>+5 نقاط</small>
            </button>
        </div>
    </div>

    {{-- Stats Grid --}}
    <div class="cd-stats">
        <div class="cd-stat animate-scale delay-1">
            <span class="cd-stat-icon">⭐</span>
            <div class="cd-stat-value">{{ number_format($stats['total_points']) }}</div>
            <div class="cd-stat-label">النقاط الكلية</div>
        </div>
        <div class="cd-stat animate-scale delay-2">
            <span class="cd-stat-icon">💰</span>
            <div class="cd-stat-value">{{ number_format($stats['total_coins']) }}</div>
            <div class="cd-stat-label">العملات</div>
        </div>
        <div class="cd-stat animate-scale delay-3">
            <span class="cd-stat-icon">✅</span>
            <div class="cd-stat-value">{{ $stats['completed_activities'] }}</div>
            <div class="cd-stat-label">أنشطة مكتملة</div>
        </div>
        <div class="cd-stat animate-scale delay-4">
            <span class="cd-stat-icon">⏳</span>
            <div class="cd-stat-value">{{ $stats['pending_activities'] }}</div>
            <div class="cd-stat-label">قيد المراجعة</div>
        </div>
        <div class="cd-stat animate-scale delay-5">
            <span class="cd-stat-icon">📊</span>
            <div class="cd-stat-value">{{ $stats['average_score'] }}%</div>
            <div class="cd-stat-label">متوسط الدرجات</div>
        </div>
        <div class="cd-stat animate-scale delay-6">
            <span class="cd-stat-icon">🏅</span>
            <div class="cd-stat-value">{{ $badges->count() }}</div>
            <div class="cd-stat-label">الشارات</div>
        </div>
    </div>

    {{-- Two Column: Streak + Rankings --}}
    <div class="cd-grid">
        {{-- Streak --}}
        <div class="animate-in delay-2">
            <div class="cd-streak-card">
                <div class="cd-streak-value">{{ $streak ? $streak->current_streak : 0 }}</div>
                <div class="cd-streak-label">🔥 أيام متتالية</div>
                @if($streak && $streak->longest_streak)
                <div class="cd-streak-best">🏆 أطول سلسلة: {{ $streak->longest_streak }} يوم</div>
                @endif
            </div>
        </div>

        {{-- Rankings --}}
        <div class="cd-card animate-in delay-3">
            <div class="cd-card-title">
                <i class="fas fa-trophy"></i>
                الترتيب
            </div>
            <div class="cd-rankings">
                @if($classRank)
                <div class="cd-rank-item">
                    <div class="cd-rank-icon">🏫</div>
                    <div class="cd-rank-value">#{{ $classRank }}</div>
                    <div class="cd-rank-label">في الفصل</div>
                </div>
                @endif
                @if($schoolRank)
                <div class="cd-rank-item">
                    <div class="cd-rank-icon">🏆</div>
                    <div class="cd-rank-value">#{{ $schoolRank }}</div>
                    <div class="cd-rank-label">في المدرسة</div>
                    <div class="cd-rank-total">من {{ $totalStudents }} طالب</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Weekly Activity Heatmap --}}
    <div class="cd-card animate-in delay-3" style="margin-bottom: 28px;">
        <div class="cd-card-title">
            <i class="fas fa-calendar-week"></i>
            نشاط الأسبوع
        </div>
        <div class="cd-week-grid">
            @foreach($weeklyActivity as $index => $day)
            <div class="cd-week-day {{ $day['count'] > 0 ? 'active' : '' }} {{ $index === 6 ? 'today' : '' }}">
                <div class="cd-week-name">{{ $day['day'] }}</div>
                <div class="cd-week-count {{ $day['count'] == 0 ? 'zero' : '' }}">{{ $day['count'] }}</div>
                <div class="cd-week-date">{{ $day['date'] }}</div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Progress Chart --}}
    <div class="cd-card animate-in delay-4" style="margin-bottom: 28px;">
        <div class="cd-card-title">
            <i class="fas fa-chart-line"></i>
            التقدم خلال 30 يوم
        </div>
        <div class="cd-chart-container">
            <canvas id="progressChart"></canvas>
        </div>
    </div>

    {{-- Two Column: Values + Badges --}}
    <div class="cd-grid">
        {{-- Values Progress --}}
        <div class="cd-card animate-in delay-4">
            <div class="cd-card-title">
                <i class="fas fa-gem"></i>
                أداء القيم
            </div>
            @forelse($valuesProgress as $value)
            <div class="cd-value-item">
                <div class="cd-value-icon">{{ $value->icon ?? '💎' }}</div>
                <div class="cd-value-info">
                    <div class="cd-value-name">{{ $value->name }}</div>
                    <div class="cd-value-bar">
                        <div class="cd-value-fill" style="width: {{ min(($value->avg_score ?? 0), 100) }}%"></div>
                    </div>
                </div>
                <div class="cd-value-stats">
                    <div class="cd-value-score">{{ round($value->avg_score ?? 0) }}%</div>
                    <div class="cd-value-count">{{ $value->completed_count }} نشاط</div>
                </div>
            </div>
            @empty
            <div class="cd-empty">
                <div class="cd-empty-icon">💎</div>
                <div class="cd-empty-text">لم يتم إكمال أي قيمة بعد</div>
            </div>
            @endforelse
        </div>

        {{-- Badges --}}
        <div class="cd-card animate-in delay-5">
            <div class="cd-card-title">
                <i class="fas fa-award"></i>
                الشارات المحققة
            </div>
            @if($badges->count() > 0)
            <div class="cd-badges">
                @foreach($badges as $badge)
                <div class="cd-badge">
                    <span class="cd-badge-icon">{{ $badge->icon ?? '🏆' }}</span>
                    <div class="cd-badge-name">{{ $badge->name }}</div>
                    @if($badge->pivot && $badge->pivot->created_at)
                    <div class="cd-badge-date">{{ $badge->pivot->created_at->diffForHumans() }}</div>
                    @endif
                </div>
                @endforeach
            </div>
            @else
            <div class="cd-empty">
                <div class="cd-empty-icon">🏅</div>
                <div class="cd-empty-text">لم يحصل على شارات بعد</div>
            </div>
            @endif
        </div>
    </div>

    {{-- Recent Activities --}}
    <div class="cd-card animate-in delay-5" style="margin-bottom: 28px;">
        <div class="cd-card-title">
            <i class="fas fa-history"></i>
            آخر الأنشطة
        </div>
        @forelse($recentActivities as $submission)
        @php
            // completed = اجتاز آلياً، approved = اعتمده المعلم → كلاهما ✅
            $statusIcon = match($submission->status) {
                'completed', 'approved' => 'completed',
                'pending', 'needs_review' => 'pending',
                default => 'failed',
            };
            $statusEmoji = match($submission->status) {
                'completed', 'approved' => '✅',
                'pending', 'needs_review' => '⏳',
                default => '❌',
            };
            $scoreClass = ($submission->score ?? 0) >= 80 ? 'high' : (($submission->score ?? 0) >= 50 ? 'mid' : 'low');
        @endphp
        <div class="cd-activity">
            <div class="cd-activity-icon {{ $statusIcon }}">
                {{ $statusEmoji }}
            </div>
            <div class="cd-activity-info">
                <div class="cd-activity-title">{{ $submission->activity->title ?? 'نشاط' }}</div>
                <div class="cd-activity-lesson">
                    📚 {{ $submission->activity->lesson->title ?? '' }}
                </div>
                <div class="cd-activity-time">
                    ⏰ {{ $submission->created_at->diffForHumans() }}
                </div>
            </div>
            @if($submission->score !== null)
            <div class="cd-activity-score {{ $scoreClass }}">
                {{ $submission->score }}%
            </div>
            @endif
        </div>
        @empty
        <div class="cd-empty">
            <div class="cd-empty-icon">📝</div>
            <div class="cd-empty-text">لا توجد أنشطة حديثة</div>
        </div>
        @endforelse
    </div>

    {{-- Teachers --}}
    @if($teachers->count() > 0)
    <div class="cd-card animate-in delay-6">
        <div class="cd-card-title">
            <i class="fas fa-chalkboard-teacher"></i>
            المعلمون
        </div>
        @foreach($teachers as $teacher)
        <div class="cd-teacher">
            <div class="cd-teacher-avatar">
                {{ mb_substr($teacher->name, 0, 1) }}
            </div>
            <div class="cd-teacher-info">
                <div class="cd-teacher-name">{{ $teacher->name }}</div>
                <div class="cd-teacher-email">{{ $teacher->email }}</div>
            </div>
            <a href="{{ route('parent.messages') }}" class="cd-teacher-btn">
                <i class="fas fa-comment-dots"></i> تواصل
            </a>
        </div>
        @endforeach
    </div>
    @endif
</div>
{{-- Encouragement Modal --}}
<div class="cd-modal-overlay" id="encourageModal">
    <div class="cd-modal">
        <div class="cd-modal-header">
            <div class="cd-modal-title">💪 رسالة تحفيزية لـ {{ $child->name }}</div>
            <button class="cd-modal-close" onclick="closeEncourageModal()">&times;</button>
        </div>
        <div class="cd-modal-body">
            {{-- Categories --}}
            <div class="cd-praise-categories">
                <button class="cd-praise-cat active" data-type="encouragement" onclick="selectCategory(this)">
                    <div class="cd-praise-cat-icon">💪</div>
                    <div class="cd-praise-cat-label">تشجيع</div>
                </button>
                <button class="cd-praise-cat" data-type="achievement" onclick="selectCategory(this)">
                    <div class="cd-praise-cat-icon">🏆</div>
                    <div class="cd-praise-cat-label">إنجاز</div>
                </button>
                <button class="cd-praise-cat" data-type="behavior" onclick="selectCategory(this)">
                    <div class="cd-praise-cat-icon">⭐</div>
                    <div class="cd-praise-cat-label">سلوك</div>
                </button>
                <button class="cd-praise-cat" data-type="custom" onclick="selectCategory(this)">
                    <div class="cd-praise-cat-icon">✍️</div>
                    <div class="cd-praise-cat-label">مخصص</div>
                </button>
            </div>

            {{-- Suggestions --}}
            <div class="cd-suggestions">
                <div class="cd-suggest-title">
                    <i class="fas fa-lightbulb" style="color: var(--gold);"></i>
                    اختر نصاً مقترحاً أو اكتب رسالتك
                </div>
                <div class="cd-suggest-grid" id="suggestionsGrid">
                    {{-- Filled by JS --}}
                </div>
            </div>

            {{-- Textarea --}}
            <textarea class="cd-praise-textarea" id="praiseMessage" placeholder="اكتب رسالتك التحفيزية هنا... 💬" maxlength="1000"></textarea>

            {{-- Submit --}}
            <button class="cd-praise-submit" id="praiseSubmitBtn" onclick="submitPraise()">
                <span class="spinner" id="praiseSpinner"></span>
                <i class="fas fa-paper-plane"></i>
                إرسال التشجيع
            </button>
        </div>
    </div>
</div>

{{-- Success Popup --}}
<div class="cd-success-popup" id="successPopup">
    <div class="cd-success-icon">🎉</div>
    <div class="cd-success-title">تم الإرسال بنجاح!</div>
    <div class="cd-success-text" id="successText">تم إرسال رسالتك التحفيزية وحصلت على 5 نقاط ✨</div>
    <button class="cd-success-close" onclick="closeSuccessPopup()">حسناً</button>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('progressChart');
    if (ctx) {
        new Chart(ctx.getContext('2d'), {
            type: 'line',
            data: {
                labels: {!! json_encode($chartData['labels']) !!},
                datasets: [
                    {
                        label: 'النقاط',
                        data: {!! json_encode($chartData['points']) !!},
                        borderColor: '#667eea',
                        backgroundColor: 'rgba(102, 126, 234, 0.08)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#667eea',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 7,
                    },
                    {
                        label: 'الأنشطة المكتملة',
                        data: {!! json_encode($chartData['activities']) !!},
                        borderColor: '#48bb78',
                        backgroundColor: 'rgba(72, 187, 120, 0.08)',
                        borderWidth: 3,
                        tension: 0.4,
                        fill: true,
                        pointBackgroundColor: '#48bb78',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2,
                        pointRadius: 4,
                        pointHoverRadius: 7,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: {
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 20,
                            font: {
                                family: "'IBM Plex Sans Arabic', sans-serif",
                                size: 13,
                                weight: '600'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(26, 32, 44, 0.95)',
                        padding: 14,
                        titleFont: {
                            family: "'IBM Plex Sans Arabic', sans-serif",
                            size: 14,
                            weight: '700'
                        },
                        bodyFont: {
                            family: "'IBM Plex Sans Arabic', sans-serif",
                            size: 13
                        },
                        cornerRadius: 12,
                        borderColor: 'rgba(102, 126, 234, 0.3)',
                        borderWidth: 1,
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0,0,0,0.04)',
                            drawBorder: false,
                        },
                        ticks: {
                            font: {
                                family: "'IBM Plex Sans Arabic', sans-serif",
                                size: 12
                            },
                            padding: 10,
                        }
                    },
                    x: {
                        grid: {
                            display: false,
                        },
                        ticks: {
                            font: {
                                family: "'IBM Plex Sans Arabic', sans-serif",
                                size: 11
                            },
                            maxRotation: 45,
                            padding: 8,
                        }
                    }
                }
            }
        });
    }
});

// ===== Encouragement System =====
const suggestions = {
    encouragement: [
        'أنا فخور بك يا بطل! استمر في التقدم 💪',
        'أنت تبذل جهداً رائعاً، واصل التألق ⭐',
        'كل يوم أنت أفضل من الأمس، أحسنت! 🌟',
        'أنت مصدر فخر لعائلتك، بارك الله فيك 🤲',
        'لا تستسلم أبداً، أنت قادر على تحقيق أحلامك! 🚀',
        'ما شاء الله عليك! تعلمك اليوم هو نجاحك غداً 📚',
    ],
    achievement: [
        'مبروك على إنجازك الرائع! أنت نجم حقيقي 🌟',
        'تستحق كل التقدير على ما حققته، أحسنت! 👏',
        'إنجازاتك تثبت أنك مميز، واصل التفوق! 🏆',
        'كل خطوة تخطوها تقربك من القمة، بارك الله فيك! 🏅',
        'نتائجك تعكس اجتهادك، تبارك الرحمن! 🎯',
        'أنت تُثبت كل يوم أنك قادر على التميز! 💎',
    ],
    behavior: [
        'أخلاقك الجميلة تسعدني كثيراً، بارك الله فيك 🌺',
        'سلوكك الراقي يدل على تربيتك الطيبة، أحسنت! 💐',
        'تعاونك مع زملائك يجعلني أفتخر بك! 🤝',
        'أدبك واحترامك للآخرين شيء يُفرح القلب 💝',
        'صدقك وأمانتك من أجمل صفاتك، حفظك الله! 🌿',
        'تصرفاتك الحسنة تجعل الجميع يحبك! 😊',
    ],
    custom: [
        'رسالة من القلب لابني الغالي... ❤️',
        'أريد أن أقول لك شيئاً مهماً... 💬',
    ]
};

let currentPraiseType = 'encouragement';

function openEncourageModal() {
    document.getElementById('encourageModal').classList.add('active');
    loadSuggestions('encouragement');
    document.body.style.overflow = 'hidden';
}

function closeEncourageModal() {
    document.getElementById('encourageModal').classList.remove('active');
    document.body.style.overflow = '';
}

function selectCategory(btn) {
    document.querySelectorAll('.cd-praise-cat').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');
    currentPraiseType = btn.dataset.type;
    loadSuggestions(currentPraiseType);
}

function loadSuggestions(type) {
    const grid = document.getElementById('suggestionsGrid');
    const items = suggestions[type] || [];
    grid.innerHTML = items.map(text =>
        `<div class="cd-suggest-item" onclick="selectSuggestion(this, '${text.replace(/'/g, "\\'")}')">` +
            text +
        '</div>'
    ).join('');
}

function selectSuggestion(el, text) {
    document.querySelectorAll('.cd-suggest-item').forEach(s => s.classList.remove('selected'));
    el.classList.add('selected');
    document.getElementById('praiseMessage').value = text;
    document.getElementById('praiseMessage').focus();
}

function submitPraise() {
    const message = document.getElementById('praiseMessage').value.trim();
    if (!message) {
        document.getElementById('praiseMessage').style.borderColor = '#f56565';
        document.getElementById('praiseMessage').placeholder = 'الرجاء كتابة رسالة تحفيزية...';
        return;
    }

    const btn = document.getElementById('praiseSubmitBtn');
    const spinner = document.getElementById('praiseSpinner');
    btn.disabled = true;
    spinner.style.display = 'block';

    fetch('{{ route("parent.child.praise", $child->id) }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            praise_message: message,
            praise_type: currentPraiseType
        })
    })
    .then(res => res.json())
    .then(data => {
        btn.disabled = false;
        spinner.style.display = 'none';

        if (data.success) {
            closeEncourageModal();
            document.getElementById('praiseMessage').value = '';
            document.getElementById('successText').textContent = data.message || 'تم إرسال رسالتك التحفيزية وحصلت على 5 نقاط ✨';
            document.getElementById('successPopup').classList.add('show');
        } else {
            alert(data.error || 'حدث خطأ، حاول مرة أخرى');
        }
    })
    .catch(err => {
        btn.disabled = false;
        spinner.style.display = 'none';
        console.error('Praise error:', err);
        alert('حدث خطأ في الاتصال');
    });
}

function closeSuccessPopup() {
    document.getElementById('successPopup').classList.remove('show');
}

// Close modal on overlay click
document.getElementById('encourageModal').addEventListener('click', function(e) {
    if (e.target === this) closeEncourageModal();
});
</script>
@endpush
