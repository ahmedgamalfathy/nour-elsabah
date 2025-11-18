<?php
namespace App\Enums\Order;
enum OrderStatus :int{
    case DRAFT = 0;
    case IN_CART= 3;
    case CHECKOUT = 2;
    case CONFIRM = 1;
    case DELIVERED = 5;
    case RETURNED = 6;
    case CANCELED = 4;
    case CASHONDELIVERY =7;
    

//canel =4
    public static function values(){
        return  array_column(self::cases(), 'value');
    }

}
