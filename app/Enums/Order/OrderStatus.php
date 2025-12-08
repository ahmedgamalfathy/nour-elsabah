<?php
namespace App\Enums\Order;
enum OrderStatus :int{
    case DRAFT = 0;
    case IN_CART= 3;//السلة المتروكة
    case WATING =8;//قيد الانتظار
    case INPROGRESS = 9;//قيد التجهيز
    case CHECKOUT = 2;
    case CONFIRM = 1;//تاكيد الطلب
    case DELIVERED = 5;// تم التوصيل
    case RETURNED = 6;//المرتجعات
    case CANCELED = 4;//طلبات مرفوضة
    case CASHONDELIVERY =7;//الدفع عند الاستلام


//canel =4
    public static function values(){
        return  array_column(self::cases(), 'value');
    }

}
