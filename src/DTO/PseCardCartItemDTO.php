<?php

namespace FlexyBundle\DTO;

class PseCardCartItemDTO
{
    public int $id = 0;
    public int $quantity = 0;


    public static function fromArray(array $data): self
    {
        $cartItem = new self();
        $cartItem->id = isset($data['id']) ? (int) $data['id'] : 0;
        $cartItem->quantity = isset($data['quantity']) ? (int) $data['quantity'] : 0;


        return $cartItem;
    }
}
