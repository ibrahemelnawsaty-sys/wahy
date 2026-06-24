<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $survey->title }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .survey-container {
            background: white;
            border-radius: 16px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 700px;
            width: 100%;
            padding: 40px;
            animation: fadeIn 0.5s ease-in;
        }
        /* P1-E: دعم Dark Mode */
        html[data-theme="dark"] .survey-container {
            background: #1e293b;
            color: #e2e8f0;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.6);
        }
        html[data-theme="dark"] body {
            background: linear-gradient(135deg, #312e81 0%, #4c1d95 100%);
        }
        html[data-theme="dark"] input,
        html[data-theme="dark"] textarea,
        html[data-theme="dark"] select {
            background: #0f172a;
            color: #e2e8f0;
            border-color: rgba(255, 255, 255, 0.1);
        }
        html[data-theme="dark"] label { color: #cbd5e1; }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .survey-header {
            text-align: center;
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 2px solid #e2e8f0;
        }

        .survey-icon {
            font-size: 64px;
            margin-bottom: 16px;
        }

        .survey-title {
            font-size: 28px;
            font-weight: 700;
            color: #1e293b;
            margin-bottom: 12px;
        }

        .survey-description {
            font-size: 16px;
            color: #64748b;
            line-height: 1.6;
        }

        .question-item {
            background: #f8fafc;
            border-radius: 12px;
            padding: 24px;
            margin-bottom: 24px;
            border: 2px solid #e2e8f0;
        }

        .question-number {
            display: inline-block;
            background: #667eea;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 600;
            margin-bottom: 12px;
        }

        .question-text {
            font-size: 18px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 16px;
        }

        .required-badge {
            color: #dc2626;
            font-size: 14px;
            margin-right: 4px;
        }

        .form-input,
        .form-textarea,
        .form-select {
            width: 100%;
            padding: 12px 16px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 15px;
            transition: border-color 0.2s;
        }

        .form-input:focus,
        .form-textarea:focus,
        .form-select:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-textarea {
            min-height: 120px;
            resize: vertical;
        }

        .radio-group,
        .checkbox-group {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .radio-item,
        .checkbox-item {
            display: flex;
            align-items: center;
            padding: 12px;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.2s;
        }

        .radio-item:hover,
        .checkbox-item:hover {
            border-color: #667eea;
            background: #f1f5f9;
        }

        .radio-item input,
        .checkbox-item input {
            margin-left: 12px;
            width: 20px;
            height: 20px;
            cursor: pointer;
        }

        .radio-item label,
        .checkbox-item label {
            cursor: pointer;
            flex: 1;
            font-size: 15px;
            color: #334155;
        }

        .submit-container {
            margin-top: 32px;
            text-align: center;
        }

        .btn-submit {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            padding: 16px 48px;
            border-radius: 8px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.4);
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.6);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .error-message {
            background: #fee2e2;
            border: 2px solid #dc2626;
            color: #dc2626;
            padding: 12px 16px;
            border-radius: 8px;
            margin-bottom: 24px;
            font-size: 14px;
        }

        .success-message {
            background: #dcfce7;
            border: 2px solid #16a34a;
            color: #16a34a;
            padding: 16px;
            border-radius: 8px;
            text-align: center;
            font-size: 16px;
            font-weight: 600;
        }

        .warning-message {
            background: linear-gradient(135deg, #fef3c7 0%, #fde68a 100%);
            border: 3px solid #f59e0b;
            color: #92400e;
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 24px;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.2);
        }

        .warning-message h3 {
            font-size: 18px;
            margin-bottom: 12px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .warning-message p {
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 16px;
        }

        .btn-login {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
            color: white;
            border: none;
            padding: 12px 32px;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            transition: transform 0.2s, box-shadow 0.2s;
            box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(245, 158, 11, 0.6);
        }

        @media (max-width: 768px) {
            .survey-container {
                padding: 24px;
            }

            .survey-title {
                font-size: 24px;
            }

            .question-item {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="survey-container">
        <div class="survey-header">
            <div class="survey-icon">📋</div>
            <h1 class="survey-title">{{ $survey->title }}</h1>
            @if($survey->description)
                <p class="survey-description">{{ $survey->description }}</p>
            @endif
        </div>

        @if($survey->requires_login && !auth()->check())
            <div class="warning-message">
                <h3>🔐 تسجيل الدخول مطلوب</h3>
                <p>هذا الاستبيان يتطلب تسجيل الدخول أولاً. يرجى تسجيل الدخول للمتابعة وملء الاستبيان.</p>
                <a href="{{ route('login') }}" class="btn-login">تسجيل الدخول الآن</a>
            </div>
        @else

        @if(session('success'))
            <div class="success-message">
                ✅ {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="error-message">
                <strong>❌ يوجد أخطاء:</strong>
                <ul style="margin-top: 8px; padding-right: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('survey.submit', $survey) }}">
            @csrf

            @foreach($survey->questions as $index => $question)
                <div class="question-item">
                    <span class="question-number">سؤال {{ $index + 1 }}</span>
                    <div class="question-text">
                        {{ $question->question_text }}
                        @if($question->is_required)
                            <span class="required-badge">*</span>
                        @endif
                    </div>

                    @if($question->question_type === 'text')
                        <input type="text" 
                               name="answers[{{ $question->id }}]" 
                               class="form-input" 
                               value="{{ old('answers.' . $question->id) }}"
                               {{ $question->is_required ? 'required' : '' }}
                               placeholder="اكتب إجابتك هنا...">

                    @elseif($question->question_type === 'textarea')
                        <textarea name="answers[{{ $question->id }}]" 
                                  class="form-textarea" 
                                  {{ $question->is_required ? 'required' : '' }}
                                  placeholder="اكتب إجابتك هنا...">{{ old('answers.' . $question->id) }}</textarea>

                    @elseif($question->question_type === 'email')
                        <input type="email" 
                               name="answers[{{ $question->id }}]" 
                               class="form-input" 
                               value="{{ old('answers.' . $question->id) }}"
                               {{ $question->is_required ? 'required' : '' }}
                               placeholder="example@email.com">

                    @elseif($question->question_type === 'phone')
                        <input type="tel" 
                               name="answers[{{ $question->id }}]" 
                               class="form-input" 
                               value="{{ old('answers.' . $question->id) }}"
                               {{ $question->is_required ? 'required' : '' }}
                               placeholder="05xxxxxxxx">

                    @elseif($question->question_type === 'select')
                        <select name="answers[{{ $question->id }}]" 
                                class="form-select" 
                                {{ $question->is_required ? 'required' : '' }}>
                            <option value="">اختر إجابة...</option>
                            @foreach($question->options as $option)
                                <option value="{{ $option }}" {{ old('answers.' . $question->id) === $option ? 'selected' : '' }}>
                                    {{ $option }}
                                </option>
                            @endforeach
                        </select>

                    @elseif($question->question_type === 'radio')
                        <div class="radio-group">
                            @foreach($question->options as $optionIndex => $option)
                                <div class="radio-item">
                                    <input type="radio" 
                                           name="answers[{{ $question->id }}]" 
                                           id="q{{ $question->id }}_opt{{ $optionIndex }}" 
                                           value="{{ $option }}"
                                           {{ old('answers.' . $question->id) === $option ? 'checked' : '' }}
                                           {{ $question->is_required ? 'required' : '' }}>
                                    <label for="q{{ $question->id }}_opt{{ $optionIndex }}">{{ $option }}</label>
                                </div>
                            @endforeach
                        </div>

                    @elseif($question->question_type === 'checkbox')
                        <div class="checkbox-group">
                            @foreach($question->options as $optionIndex => $option)
                                <div class="checkbox-item">
                                    <input type="checkbox" 
                                           name="answers[{{ $question->id }}][]" 
                                           id="q{{ $question->id }}_opt{{ $optionIndex }}" 
                                           value="{{ $option }}"
                                           {{ is_array(old('answers.' . $question->id)) && in_array($option, old('answers.' . $question->id)) ? 'checked' : '' }}>
                                    <label for="q{{ $question->id }}_opt{{ $optionIndex }}">{{ $option }}</label>
                                </div>
                            @endforeach
                        </div>

                    @elseif($question->question_type === 'rating')
                        <div class="radio-group">
                            @for($i = 1; $i <= 5; $i++)
                                <div class="radio-item">
                                    <input type="radio"
                                           name="answers[{{ $question->id }}]"
                                           id="q{{ $question->id }}_r{{ $i }}"
                                           value="{{ $i }}"
                                           {{ (string) old('answers.' . $question->id) === (string) $i ? 'checked' : '' }}
                                           {{ $question->is_required ? 'required' : '' }}>
                                    <label for="q{{ $question->id }}_r{{ $i }}">{{ $i }} ⭐</label>
                                </div>
                            @endfor
                        </div>

                    @elseif($question->question_type === 'scale')
                        <div class="radio-group">
                            @for($i = 1; $i <= 10; $i++)
                                <div class="radio-item">
                                    <input type="radio"
                                           name="answers[{{ $question->id }}]"
                                           id="q{{ $question->id }}_s{{ $i }}"
                                           value="{{ $i }}"
                                           {{ (string) old('answers.' . $question->id) === (string) $i ? 'checked' : '' }}
                                           {{ $question->is_required ? 'required' : '' }}>
                                    <label for="q{{ $question->id }}_s{{ $i }}">{{ $i }}</label>
                                </div>
                            @endfor
                        </div>
                    @endif
                </div>
            @endforeach

            <div class="submit-container">
                <button type="submit" class="btn-submit">📤 إرسال الإجابات</button>
            </div>
        </form>
        
        @endif
    </div>
</body>
</html>
