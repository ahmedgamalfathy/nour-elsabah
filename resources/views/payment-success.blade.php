{{-- <!DOCTYPE html> --}}
{{-- <html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>نجاح الدفع</title>
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            font-family: Arial, sans-serif;
            background-color: #f4f4f9;
        }
        .container {
            text-align: center;
            padding: 20px;
            background-color: #fff;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
            max-width: 500px;
            width: 100%;
        }
        h1 {
            color: #28a745;
            font-size: 2em;
            margin-bottom: 10px;
        }
        p {
            color: #555;
            font-size: 1.2em;
        }
    </style>
</head>
<body>
<div class="container">
    <h1>تمت عملية الدفع بنجاح!</h1>
    <p>شكراً لك على الدفع. سنقوم بمعالجة طلبك في أقرب وقت ممكن.</p>
</div>
</body>
</html> --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
        }
    </style>
</head>
<body>
    <div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center">
        <div class="card shadow-lg border-0 rounded-4 p-5 animate__animated animate__fadeIn" style="max-width: 600px; width: 90%;">
            <div class="text-success display-1 mb-4 animate__animated animate__bounceIn" style="animation: bounce 2s infinite;">✓</div>
            <h1 class="display-4 mb-4 animate__animated animate__fadeInUp">Payment Successful!</h1>
            <div class="bg-light p-4 rounded-3 mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                <p class="lead p-0 m-0">Your payment has been processed successfully.</p>
            </div>
            <p class="text-muted animate__animated animate__fadeInUp" style="animation-delay: 0.4s">Thank you for your purchase.</p>
            <a href="https://elmotech-ecommerce.vercel.app/en" class="btn btn-primary btn-lg rounded-pill px-5 py-3 mt-4 animate__animated animate__fadeInUp" style="animation-delay: 0.6s">
                Return to Home
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <style>
        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {transform: translateY(0);}
            40% {transform: translateY(-20px);}
            60% {transform: translateY(-10px);}
        }
    </style>
</body>
</html>
