# نظام الإعدادات - Settings System

## نظرة عامة
تم إضافة نظام إعدادات شامل للتحكم في إعدادات المتجر من الداشبورد، بما في ذلك التحكم في بوابات الدفع.

## المميزات

### 1. إدارة بوابات الدفع
- تفعيل/إيقاف PayPal
- تفعيل/إيقاف Stripe
- تفعيل/إيقاف الدفع عند الاستلام

### 2. الإعدادات العامة
- اسم الموقع
- وضع الصيانة
- الحد الأدنى والأقصى لقيمة الطلب

### 3. التخزين المؤقت (Caching)
- جميع الإعدادات يتم تخزينها مؤقتاً لمدة ساعة
- يتم مسح الكاش تلقائياً عند التحديث

## التثبيت

### 1. تشغيل Migration
```bash
php artisan migrate
```

### 2. تشغيل Seeder
```bash
php artisan db:seed --class=SettingsSeeder
```

أو تشغيل جميع الـ Seeders:
```bash
php artisan db:seed
```

## API Endpoints

### Dashboard (Admin) Routes

#### 1. الحصول على جميع الإعدادات
```http
GET /api/v1/admin/settings
```

**Query Parameters:**
- `group` (optional): فلترة حسب المجموعة (payment, general, order)

**Response:**
```json
{
  "success": true,
  "message": "تم الاسترجاع بنجاح",
  "data": [
    {
      "id": 1,
      "key": "payment.paypal.enabled",
      "value": true,
      "type": "boolean",
      "group": "payment",
      "description": "تفعيل/إيقاف بوابة الدفع PayPal"
    }
  ]
}
```

#### 2. الحصول على إعداد واحد
```http
GET /api/v1/admin/settings/{id}
```

#### 3. تحديث إعداد
```http
PUT /api/v1/admin/settings/{id}
Content-Type: application/json

{
  "value": "1",
  "description": "وصف جديد"
}
```

#### 4. تحديث متعدد (Bulk Update)
```http
POST /api/v1/admin/settings/bulk-update
Content-Type: application/json

{
  "settings": {
    "payment.paypal.enabled": "1",
    "payment.stripe.enabled": "0",
    "payment.cash_on_delivery.enabled": "1"
  }
}
```

#### 5. الحصول على بوابات الدفع المتاحة
```http
GET /api/v1/admin/settings/payment/gateways
```

**Response:**
```json
{
  "success": true,
  "data": {
    "gateways": {
      "paypal": true,
      "stripe": true,
      "cash_on_delivery": true
    }
  }
}
```

### Website (Client) Routes

#### 1. الحصول على بوابات الدفع المتاحة للعملاء
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

## استخدام في الكود

### 1. الحصول على قيمة إعداد
```php
use App\Models\Setting\Setting;

// الطريقة الأولى
$isPaypalEnabled = Setting::get('payment.paypal.enabled', true);

// الطريقة الثانية - باستخدام Service
$settingService = app(SettingService::class);
$isEnabled = $settingService->isPaymentGatewayEnabled('paypal');
```

### 2. تعيين قيمة إعداد
```php
Setting::set('payment.paypal.enabled', true);
```

### 3. الحصول على إعدادات مجموعة
```php
$paymentSettings = Setting::getByGroup('payment');
// Returns: ['payment.paypal.enabled' => true, ...]
```

### 4. مسح الكاش
```php
Setting::clearCache();
```

## التحقق من بوابات الدفع

### في PayPal Service
```php
public function sendPayment(Request $request)
{
    // التحقق من تفعيل PayPal
    if (!\App\Models\Setting\Setting::get('payment.paypal.enabled', true)) {
        return ApiResponse::error(
            'بوابة الدفع PayPal غير متاحة حالياً',
            [],
            HttpStatusCode::SERVICE_UNAVAILABLE
        );
    }
    
    // باقي الكود...
}
```

### في Stripe Service
```php
public function sendPayment(Request $request)
{
    // التحقق من تفعيل Stripe
    if (!\App\Models\Setting\Setting::get('payment.stripe.enabled', true)) {
        return ApiResponse::error(
            'بوابة الدفع Stripe غير متاحة حالياً',
            [],
            HttpStatusCode::SERVICE_UNAVAILABLE
        );
    }
    
    // باقي الكود...
}
```

### في Cash on Delivery
```php
public function cashOnDelivery(Request $request)
{
    // التحقق من تفعيل الدفع عند الاستلام
    if (!\App\Models\Setting\Setting::get('payment.cash_on_delivery.enabled', true)) {
        return ApiResponse::error(
            'الدفع عند الاستلام غير متاح حالياً',
            [],
            HttpStatusCode::SERVICE_UNAVAILABLE
        );
    }
    
    // باقي الكود...
}
```

## قاعدة البيانات

### جدول Settings
```sql
CREATE TABLE settings (
    id BIGINT PRIMARY KEY AUTO_INCREMENT,
    key VARCHAR(255) UNIQUE NOT NULL,
    value TEXT,
    type VARCHAR(255) DEFAULT 'string',
    `group` VARCHAR(255) DEFAULT 'general',
    description TEXT,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);
```

### البيانات الافتراضية
```sql
-- Payment Settings
payment.paypal.enabled = 1
payment.stripe.enabled = 1
payment.cash_on_delivery.enabled = 1

-- General Settings
site.name = 'ecommerce'
site.maintenance_mode = 0

-- Order Settings
order.min_amount = 0
order.max_amount = 100000
```

## أنواع البيانات المدعومة
- `string`: نص عادي
- `boolean`: true/false (1/0)
- `integer`: أرقام صحيحة
- `float`: أرقام عشرية
- `json`: بيانات JSON

## أمثلة استخدام في Frontend

### React/Vue Example
```javascript
// الحصول على بوابات الدفع المتاحة
const response = await axios.get('/api/v1/website/payment/available-gateways');
const gateways = response.data.data;

// عرض الأزرار حسب البوابات المتاحة
{gateways.paypal && <PayPalButton />}
{gateways.stripe && <StripeButton />}
{gateways.cash_on_delivery && <CashButton />}
```

### Admin Dashboard Example
```javascript
// تحديث إعدادات الدفع
const updatePaymentSettings = async () => {
  await axios.post('/api/v1/admin/settings/bulk-update', {
    settings: {
      'payment.paypal.enabled': paypalEnabled ? '1' : '0',
      'payment.stripe.enabled': stripeEnabled ? '1' : '0',
      'payment.cash_on_delivery.enabled': codEnabled ? '1' : '0'
    }
  });
};
```

## الأمان
- جميع endpoints الخاصة بالـ Admin محمية بـ Authentication
- التحقق من الصلاحيات يتم عبر Middleware
- القيم يتم التحقق منها قبل الحفظ

## الأداء
- استخدام Cache لتقليل الاستعلامات
- مدة التخزين المؤقت: 1 ساعة
- يتم مسح الكاش تلقائياً عند التحديث

## إضافة إعدادات جديدة

### 1. إضافة في Seeder
```php
[
    'key' => 'feature.new_feature.enabled',
    'value' => '1',
    'type' => 'boolean',
    'group' => 'features',
    'description' => 'تفعيل الميزة الجديدة',
    'created_at' => now(),
    'updated_at' => now(),
]
```

### 2. استخدام في الكود
```php
if (Setting::get('feature.new_feature.enabled', false)) {
    // تنفيذ الميزة
}
```

## الملفات المضافة
```
app/
├── Models/Setting/Setting.php
├── Services/Setting/SettingService.php
├── Http/
│   ├── Controllers/Api/V1/
│   │   ├── Dashboard/Setting/SettingController.php
│   │   └── Website/Setting/SettingWebsiteController.php
│   ├── Resources/Setting/SettingResource.php
│   └── Requests/Setting/UpdateSettingRequest.php
database/
├── migrations/2025_02_12_000001_create_settings_table.php
└── seeders/SettingsSeeder.php
```

## الخلاصة
نظام الإعدادات يوفر طريقة مرنة وآمنة للتحكم في إعدادات المتجر من الداشبورد، مع دعم التخزين المؤقت والتحقق من الصلاحيات.
