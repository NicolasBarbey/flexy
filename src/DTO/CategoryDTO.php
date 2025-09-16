<?php

namespace FlexyBundle\DTO;

class CategoryDTO
{
    // Données principales
    public int $id = 0;
    public int $parent = 0;
    public bool $visible = false;
    public int $position = 0;
    public string $title = '';
    public string $chapo = '';
    public string $description = '';

    // Champs optionnels vus dans la capture
    public ?int $defaultTemplated = null;
    public bool $defaultCategory = false;

    public static function fromArray(array $data): self
    {
        $categoryDTO = new self();

        $categoryDTO->id = isset($data['id']) ? (int) $data['id'] : 0;
        $categoryDTO->parent = isset($data['parent']) ? (int) $data['parent'] : 0;
        $categoryDTO->visible = (bool)($data['visible'] ?? false);
        $categoryDTO->position = isset($data['position']) ? (int) $data['position'] : 0;
        $categoryDTO->title = isset($data['i18ns']['title']) ? (string) $data['i18ns']['title'] : '';
        $categoryDTO->chapo = isset($data['i18ns']['chapo']) ? (string) $data['i18ns']['chapo'] : '';
        $categoryDTO->description = isset($data['i18ns']['description']) ? (string) $data['i18ns']['description'] : '';

        // Optionnels
        if (array_key_exists('defaultTemplated', $data)) {
            $categoryDTO->defaultTemplated = is_null($data['defaultTemplated']) ? null : (int) $data['defaultTemplated'];
        }
        $categoryDTO->defaultCategory = (bool)($data['defaultCategory'] ?? false);

        return $categoryDTO;
    }
}
