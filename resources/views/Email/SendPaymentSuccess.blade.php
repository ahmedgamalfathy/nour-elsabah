<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إيصال الدفع</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-md mx-auto bg-white rounded-lg shadow-lg p-6">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-bold text-gray-800">إيصال الدفع</h1>
            </div>

            <div class="space-y-4">
                <div class="text-right">
                    <p class="text-xl font-semibold text-gray-700">مرحباً {{ $client->name }}</p>
                    <p class="text-gray-600 mt-2">شكراً لك لاستخدام تطبيقنا</p>
                </div>

                <div class="border-t border-b border-gray-200 py-4">
                    <h2 class="text-lg font-semibold text-gray-800 mb-3">تفاصيل الفاتورة</h2>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-gray-600">رقم الطلب:</span>
                            <span class="font-medium">{{ $order->number }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">المبلغ:</span>
                            <span class="font-medium">{{ number_format($order->price_after_discount, 2) }} $</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-600">حالة الدفع:</span>
                            <span class="font-medium text-green-600">Paid</span>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-6">
                    <div class="inline-flex items-center px-4 py-2 bg-green-100 text-green-800 rounded-full">
                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                        </svg>
                        تم الدفع بنجاح
                    </div>
                </div>

                <div class="text-center mt-6">
                    <p class="text-sm text-gray-500">تاريخ الدفع: {{ \Carbon\Carbon::parse($order->created_at)->format('Y-m-d H:i') }}</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>