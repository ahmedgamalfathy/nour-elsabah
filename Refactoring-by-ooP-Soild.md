# 🗺️ كتيب أنماط التصميم المطبقة | Design Patterns & Architectural Guide

يوضح هذا الملف الأنماط التصميمية (Design Patterns) والمعايير الهندسية التي تم بناء نظام الطلبات (Order Module) عليها في مشروع **Nour-elsabah**. تم توزيع هذه الأنماط بناءً على فلسفة الـ OOP لتحقيق أقصى درجات المرونة، الأمان، وقابلية التوسع.

---

## 🎯 الهيكل العام للأنماط المطبقة (Architecture Map)

| اسم نمط التصميم (Design Pattern) | نوع النمط | مكان التطبيق في المشروع (Files & Classes) | الوظيفة البرمجية والتشغيلية |
| :--- | :--- | :--- | :--- |
| **Strategy Pattern** | Behavioral | `PaymentGatewayInterface`<br>`PaypalPaymentService`<br>`StripePaymentService` | تبديل بوابات الدفع ديناميكياً وقت التشغيل بناءً على الطلب دون شروط `if/else` معقدة. |
| **Pipeline Pattern** | Architectural | `CheckoutService`<br>`App\Pipelines\Order\*` | تمرير بيانات الطلب عبر سلسلة خطوات متتالية وموحدة لمعالجة الزائر والمسجل بدون تكرار كود. |
| **Observer Pattern** | Behavioral | `OrderObserver`<br>`Order` Model | مراقبة تغير حالات الطلب مركزياً لتنفيذ العمليات الجانبية (خصم/إرجاع المخزون والنقاط). |
| **State Pattern** | Behavioral | `OrderStatus` (Enum / States)<br>`OrderItemsWebsiteController` | التحكم في صلاحيات وسلوك السلة (قفل أو فتح السلة) بناءً على حالة الطلب الحالية (`CHECKOUT`). |
| **Data Transfer Object (DTO)** | Architectural | `App\DTOs\Order\OrderCheckoutData` | تغليف البيانات وتوحيد شكل الـ Payload المنتقل بين مراحل الـ Pipeline لضمان سلامة البيانات ونوعها. |

---

## 🔍 الشرح التفصيلي للأنماط وتطبيقها بالكود

### 1. نمط الاستراتيجية (Strategy Pattern)
*   **الهدف منه:** تعريف عائلة من الخوارزميات (بوابات الدفع)، وعزل كل واحدة منها في كلاس مستقل، وجعلها قابلة للتبديل ديناميكياً.
*   **أين تم تطبيقه؟** 
    *   `App\Contracts\PaymentGatewayInterface.php` (الواجهة المجرّدة).
    *   `App\Services\Payment\PaypalPaymentService.php` (التنفيذ الملموس الأول).
    *   `App\Services\Payment\StripePaymentService.php` (التنفيذ الملموس الثاني).
*   **فائدته في الكود:** الـ `PaymentController` لا يعرف تفاصيل كود Stripe أو PayPal، بل يتعامل مع الـ Interface فقط. الـ `PaymentServiceProvider` يقوم بحقن الخدمة المناسبة بناءً على `gatewayType` المرسل من الفرونت إند.

### 2. نمط خط الإنتاج (Pipeline Pattern)
*   **الهدف منه:** تمرير كائن (Object) عبر سلسلة من المراحل المستقلة (Pipes)، كل مرحلة تؤدي وظيفة واحدة محددة (Single Responsibility) وتمرر النتيجة للمرحلة التالية.
*   **أين تم تطبيقه؟**
    *   `App\Services\Order\CheckoutService.php` (المايسترو الحاضن للـ Pipeline).
    *   مراحل المعالجة: `ResolveClient`, `ValidateStockAndSteps`, `CreateOrderRecords`, `CalculateAndApplyPromotions`.
*   **فائدته في الكود:** دمج مسار الـ Guest Checkout والـ Authenticated Cart Checkout. "خط الإنتاج" ثابت للطرفين، والمرحلة الأولى (`ResolveClient`) هي الوحيدة التي تفصل بذكاء؛ فإذا كان زائر تنشئ له السجلات (العميل، الهاتف، العنوان)، وإذا كان مسجل تتخطى ذلك وتمرر البيانات للمراحل الموحدة التالية (الفحص والحساب).

### 3. نمط المراقب (Observer Pattern)
*   **الهدف منه:** إنشاء آلية اشتراك (Subscription) لإشعار كلاسات معينة تلقائياً بأي أحداث أو تغيرات تحدث لكائن آخر (مثل الـ Model).
*   **أين تم تطبيقه؟**
    *   `App\Observers\OrderObserver.php` ومربوط بالموديل `Order::observe(OrderObserver::class)`.
*   **فائدته في الكود:** عزل العمليات الجانبية (Side-effects) تماماً عن خدمات الدفع والـ Controllers. الـ Observer يراقب الـ `status`:
    *   إذا تغير لـ `CONFIRM` -> يستدعي الـ `InventoryService` للخصم الآمن والـ `PointsService` لمنح النقاط.
    *   إذا تغير لـ `CANCELED` أو حدث `deleting` للطلب -> يستدعي الخدمات لإرجاع المخزون ورد النقاط فوراً.

### 4. نمط الحالة (State Pattern)
*   **الهدف منه:** السماح للكائن بتعديل سلوكه وصلاحياته داخلياً عند تغير حالته الحالية، لتبدو الحالات وكأنها كلاسات مستقلة تحكم الكائن.
*   **أين تم تطبيقه؟**
    *   تطبيق منطق القفل في حالة `OrderStatus::CHECKOUT`.
    *   كود الفحص `isLockedForCheckout()` داخل الـ `Order` model والـ `OrderItemWebsiteController`.
*   **فائدته في الكود:** بمجرد دخول الطلب لمرحلة الـ `CHECKOUT` (تم توليد رابط الدفع وبانتظار الـ Callback)، تتغير طبيعة السلة برمجياً وتصبح **للقراءة فقط (Read-Only)**. أي محاولة من الفرونت إند لاستدعاء ميثود (إضافة/تعديل/حذف) عناصر السلة ستواجه بـ `Abort(422)` فوراً لمنع ثغرات التلاعب بأسعار وعناصر الطلب أثناء الدفع.

### 5. نمط كائن نقل البيانات (Data Transfer Object - DTO)
*   **الهدف منه:** تجميع وتغليف البيانات الموجهة من طبقة (كـ الـ Request) ونقلها إلى طبقة أخرى (كالـ Services والـ Pipelines) بشكل كائن منظم وثابت، بدلاً من تمرير مصفوفات عشوائية (Raw Arrays).
*   **أين تم تطبيقه؟**
    *   `App\DTOs\Order\OrderCheckoutData.php`
*   **فائدته في الكود:** يضمن الـ DTO أن البيانات التي تدور داخل مراحل الـ Pipeline تمتلك Type-Hinting صارم ومحدد (مثل: `$clientId` هو دائماً `?int` والـ `$inputData` هي دائماً `array`). هذا يمنع أخطاء الـ Run-time الناتجة عن عدم وجود حقول معينة في المصفوفات المرسلة.

---

## 💎 العوائد الهندسية للنظام بعد تطبيق هذه الأنماط

1.  **Strict SOLID Compliance:** الكود الآن يحترم مبادئ SOLID بالكامل، وخاصة الـ **SRP** (كل كلاس مسؤول عن شيء واحد) والـ **DIP** (الاعتماد على الواجهات المجردة وبث الأحداث).
2.  **High Extensibility (مفتوح للتوسع):** لو طلب العميل غداً إضافة بوابة دفع ثالثة (مثل Paymob)، أو إضافة خطوة فحص جديدة للطلب (التحقق من حظر العميل)، يتم إضافة كلاس جديد تماماً وضبطه في الـ Container أو الـ Pipeline، **دون تعديل أو لمس سطر كود واحد قديم مستقر**.
3.  **Zero Dead Code:** تم تنظيف الملفات المهجورة والقديمة (مثل `AuthOrderItemController.php`) لضمان خلو بيئة العمل من التشتت وسهولة قراءتها من أي مطور Senior ينضم للفريق مستقبلاً.