<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>استعادة كلمة المرور</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f7f6;
            margin: 0;
            padding: 0;
            text-align: right;
        }
        .container {
            max-width: 600px;
            margin: 40px auto;
            background-color: #ffffff;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
        .header {
            background-color: #2563eb;
            color: #ffffff;
            padding: 30px;
            text-align: center;
        }
        .content {
            padding: 40px;
        }
        .button {
            display: inline-block;
            padding: 12px 24px;
            background-color: #2563eb;
            color: #ffffff;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            margin: 20px 0;
        }
        .footer {
            padding: 20px;
            background-color: #f9fafb;
            color: #6b7280;
            font-size: 12px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>فولتا (Volta)</h1>
        </div>
        <div class="content">
            <h2>مرحباً!</h2>
            <p>لقد تلقيت هذا البريد الإلكتروني لأننا تلقينا طلباً لاستعادة كلمة المرور لحسابك في متجر فولتا.</p>
            <p>يرجى النقر على الزر أدناه لإكمال العملية:</p>
            <center>
                <a href="{{ $url }}" class="button" style="color: white;">استعادة كلمة المرور</a>
            </center>
            <p>هذا الرابط صالح لمدة 60 دقيقة من الآن.</p>
            <p>إذا لم تطلب استعادة كلمة المرور، فلا داعي لاتخاذ أي إجراء آخر.</p>
            <p>شكراً لك،<br>فريق عمل فولتا</p>
        </div>
        <div class="footer">
            <p>&copy; {{ date('Y') }} Volta Store. جميع الحقوق محفوظة.</p>
        </div>
    </div>
</body>
</html>
