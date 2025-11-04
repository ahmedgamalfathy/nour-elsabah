{{-- <!DOCTYPE html> --}}
{{-- <html lang="ar">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>فشل الدفع</title>
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
            color: #dc3545;
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
    <h1>فشل عملية الدفع!</h1>
    <p>للأسف، لم تكتمل عملية الدفع. يرجى المحاولة مرة أخرى أو التواصل مع الدعم.</p>
</div>
</body>
</html> --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Error</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #fff5f5 0%, #fed7d7 100%);
            min-height: 100vh;
        }

        .error-icon {
            color: #dc3545;
            font-size: 5rem;
            animation: shake 0.82s cubic-bezier(.36,.07,.19,.97) both;
            transform: translate3d(0, 0, 0);
            backface-visibility: hidden;
            perspective: 1000px;
        }

        @keyframes shake {
            10%, 90% { transform: translate3d(-1px, 0, 0); }
            20%, 80% { transform: translate3d(2px, 0, 0); }
            30%, 50%, 70% { transform: translate3d(-4px, 0, 0); }
            40%, 60% { transform: translate3d(4px, 0, 0); }
        }

        .error-card {
            border: 1px solid rgba(220, 53, 69, 0.2);
            background: rgba(255, 255, 255, 0.9);
        }

        .error-message {
            color: #dc3545;
            font-weight: 500;
        }

        .retry-button {
            background: #dc3545;
            border: none;
            transition: all 0.3s ease;
        }

        .retry-button:hover {
            background: #c82333;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }
    </style>
</head>
<body>
    <div class="container-fluid min-vh-100 d-flex align-items-center justify-content-center">
        <div class="card shadow-lg error-card rounded-4 p-5 animate__animated animate__fadeIn" style="max-width: 600px; width: 90%;">
            <div class="error-icon mb-4 animate__animated animate__bounceIn">
                <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" fill="currentColor" viewBox="0 0 16 16">
                    <path d="M8 15A7 7 0 1 1 8 1a7 7 0 0 1 0 14zm0 1A8 8 0 1 0 8 0a8 8 0 0 0 0 16z"/>
                    <path d="M4.646 4.646a.5.5 0 0 1 .708 0L8 7.293l2.646-2.647a.5.5 0 0 1 .708.708L8.707 8l2.647 2.646a.5.5 0 0 1-.708.708L8 8.707l-2.646 2.647a.5.5 0 0 1-.708-.708L7.293 8 4.646 5.354a.5.5 0 0 1 0-.708z"/>
                </svg>
            </div>
            <h1 class="display-4 mb-4 animate__animated animate__fadeInUp error-message">Payment Failed</h1>
            <div class="bg-light p-4 rounded-3 mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                <p class="lead p-0 m-0">We couldn't process your payment. Please try again.</p>
            </div>
            <p class="text-muted animate__animated animate__fadeInUp" style="animation-delay: 0.4s">
                If the problem persists, please contact our support team.
            </p>
            <div class="d-flex gap-3 justify-content-center mt-4">
                <button class="btn retry-button btn-lg rounded-pill px-5 py-3 animate__animated animate__fadeInUp" style="animation-delay: 0.6s">
                    Try Again
                </button>
                <a href="https://elmotech-ecommerce.vercel.app/en" class="btn btn-outline-secondary btn-lg rounded-pill px-5 py-3 animate__animated animate__fadeInUp" style="animation-delay: 0.6s">
                    Return Home
                </a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.6/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add click event to retry button
        document.querySelector('.retry-button').addEventListener('click', function() {
            // Add shake animation to the card
            const card = document.querySelector('.card');
            card.classList.add('animate__animated', 'animate__shakeX');

            // Remove the animation class after it completes
            setTimeout(() => {
                card.classList.remove('animate__animated', 'animate__shakeX');
            }, 1000);
        });
    </script>
</body>
</html>
