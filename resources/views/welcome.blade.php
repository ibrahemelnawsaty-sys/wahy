<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>بناء القيم - منصة تعليمية تفاعلية</title>
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans+Arabic:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'IBM Plex Sans Arabic', sans-serif; background: #f8f9fa; }
        html { scroll-behavior: smooth; }
    </style>
</head>
<body>
    @include('landing.partials.header')
    @include('landing.partials.hero')
    @include('landing.partials.about')
    @include('landing.partials.values')
    @include('landing.partials.features')
    @include('landing.partials.how-it-works')
    @include('landing.partials.statistics')
    @include('landing.partials.contact')
    @include('landing.partials.footer')

    <script>
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                const href = this.getAttribute('href');
                if (href && href.length > 1) {
                    e.preventDefault();
                    const target = document.querySelector(href);
                    if (target) target.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            });
        });
    </script>
</body>
</html>