<?php

namespace FlexyBundle\UiComponents\Checkout;

class CheckoutEvents
{
    public const UPDATE_ITEM_QUANTITY_EVENT = 'UPDATE_ITEM_QUANTITY_EVENT';
    public const ADD_ITEM_EVENT = 'CART_ADD_ITEM_EVENT';
    public const DELETE_ITEM_EVENT = 'CART_DELETE_ITEM_EVENT';
    public const SET_DELIVERY_MODULE_OPTION = 'SET_DELIVERY_MODULE_OPTION';
    public const SET_DELIVERY_ORDER_ADDRESS_ID = 'SET_DELIVERY_ORDER_ADDRESS_ID';
}
