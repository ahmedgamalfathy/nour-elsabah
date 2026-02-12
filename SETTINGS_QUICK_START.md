# دليل البدء السريع - نظام الإعدادات

## خطوات التثبيت

### 1. تشغيل Migration
```bash
php artisan migrate
```

### 2. تشغيل Seeder
```bash
php artisan db:seed --class=SettingsSeeder
```

## الاستخدام السريع

### من الداشبورد (Admin)

#### تحديث إعدادات بوابات الدفع
```http
POST /api/v1/admin/settings/bulk-update
Content-Type: application/json
Authorization: Bearer {admin_token}

{
  "settings": {
    "payment.paypal.enabled": "1",
    "payment.stripe.enabled": "0",
    "payment.cash_on_delivery.enabled": "1"
  }
}
```

#### الحصول على جميع الإعدادات
```http
GET /api/v1/admin/settings
Authorization: Bearer {admin_token}
```

#### الحصول على إعدادات الدفع فقط
```http
GET /api/v1/admin/settings?group=payment
Authorization: Bearer {admin_token}
```

### من الموقع (Website)

#### الحصول على بوابات الدفع المتاحة
```http
GET /api/v1/website/payment/available-gateways
```

**Response:**
```json
{
  "success": true,
  "data": {
    "paypal": true,
    "stripe": false,
    "cash_on_delivery": true
  }
}
```

## الإعدادات الافتراضية

| المفتاح | القيمة الافتراضية | الوصف |
|---------|-------------------|-------|
| `payment.paypal.enabled` | `true` | تفعيل PayPal |
| `payment.stripe.enabled` | `true` | تفعيل Stripe |
| `payment.cash_on_delivery.enabled` | `true` | تفعيل الدفع عند الاستلام |
| `site.name` | `ecommerce` | اسم الموقع |
| `site.maintenance_mode` | `false` | وضع الصيانة |
| `order.min_amount` | `0` | الحد الأدنى للطلب |
| `order.max_amount` | `100000` | الحد الأقصى للطلب |

## أمثلة الاستخدام في الكود

### PHP
```php
use App\Models\Setting\Setting;

// الحصول على قيمة
$enabled = Setting::get('payment.paypal.enabled', true);

// تعيين قيمة
Setting::set('payment.paypal.enabled', false);

// الحصول على مجموعة
$paymentSettings = Setting::getByGroup('payment');
```

### JavaScript (Frontend)
```javascript
// الحصول على بوابات الدفع
const { data } = await axios.get('/api/v1/website/payment/available-gateways');

// عرض الأزرار المتاحة فقط
if (data.data.paypal) {
  showPayPalButton();
}
if (data.data.stripe) {
  showStripeButton();
}
if (data.data.cash_on_delivery) {
  showCashButton();
}
```

## التحقق التلقائي

عند محاولة الدفع، يتم التحقق تلقائياً من تفعيل البوابة:

- **PayPal**: يتم التحقق في `PaypalPaymentService::sendPayment()`
- **Stripe**: يتم التحقق في `StripePaymentService::sendPayment()`
- **الدفع عند الاستلام**: يتم التحقق في `AuthOrderController::cashOnDelivery()`

إذا كانت البوابة معطلة، سيتم إرجاع:
```json
{
  "success": false,
  "message": "بوابة الدفع غير متاحة حالياً",
  "errors": []
}
```

## Routes المضافة

### Admin Routes
- `GET /api/v1/admin/settings` - جميع الإعدادات
- `GET /api/v1/admin/settings/{id}` - إعداد واحد
- `PUT /api/v1/admin/settings/{id}` - تحديث إعداد
- `POST /api/v1/admin/settings/bulk-update` - تحديث متعدد
- `GET /api/v1/admin/settings/payment/gateways` - بوابات الدفع

### Website Routes
- `GET /api/v1/website/payment/available-gateways` - البوابات المتاحة

## ملاحظات مهمة

1. ✅ جميع الإعدادات يتم تخزينها مؤقتاً (Cache) لمدة ساعة
2. ✅ يتم مسح الكاش تلقائياً عند التحديث
3. ✅ التحقق من البوابات يتم تلقائياً قبل الدفع
4. ✅ Admin routes محمية بـ Authentication
5. ✅ Website routes متاحة للجميع

## استكشاف الأخطاء

### المشكلة: الإعدادات لا تظهر
**الحل:** تأكد من تشغيل Seeder
```bash
php artisan db:seed --class=SettingsSeeder
```

### المشكلة: التحديثات لا تظهر
**الحل:** امسح الكاش
```php
Setting::clearCache();
```
أو
```bash
php artisan cache:clear
```

### المشكلة: خطأ 503 عند الدفع
**السبب:** البوابة معطلة من الإعدادات
**الحل:** فعّل البوابة من الداشبورد
