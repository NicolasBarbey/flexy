<?php

declare(strict_types=1);

/*
 * This file is part of the Thelia package.
 * http://www.thelia.net
 *
 * (c) OpenStudio <info@thelia.net>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace FlexyBundle\DTO;

class CategoryDTO
{
    public int $id = 0;
    public int $parent = 0;
    public bool $visible = false;
    public int $position = 0;
    public string $title = '';
    public string $chapo = '';
    public string $description = '';
    public ?int $defaultTemplated = null;
    public bool $defaultCategory = false;

    public static function fromArray(array $data): self
    {
        $dto = new self();
        $dto->id = isset($data['id']) ? (int) $data['id'] : 0;
        $dto->parent = isset($data['parent']) ? (int) $data['parent'] : 0;
        $dto->visible = (bool) ($data['visible'] ?? false);
        $dto->position = isset($data['position']) ? (int) $data['position'] : 0;
        $dto->title = isset($data['i18ns']['title']) ? (string) $data['i18ns']['title'] : '';
        $dto->chapo = isset($data['i18ns']['chapo']) ? (string) $data['i18ns']['chapo'] : '';
        $dto->description = isset($data['i18ns']['description']) ? (string) $data['i18ns']['description'] : '';

        if (array_key_exists('defaultTemplated', $data)) {
            $dto->defaultTemplated = $data['defaultTemplated'] === null ? null : (int) $data['defaultTemplated'];
        }
        $dto->defaultCategory = (bool) ($data['defaultCategory'] ?? false);

        return $dto;
    }
}
