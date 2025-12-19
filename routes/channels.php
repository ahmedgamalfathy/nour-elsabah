<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});
Broadcast::channel('send-created-order-to-dashboard', function ($user) {
    // فحص إذا المستخدم له صلاحية
    // return $user->hasRole('admin') || $user->hasRole('manager');
    // أو بدون صلاحيات - أي مستخدم مسجل دخول
    return true;
    // أو فحص معين
    // return $user->id && $user->can('view-orders');
});
