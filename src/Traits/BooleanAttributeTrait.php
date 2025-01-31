<?php

namespace Vanacode\Model\Traits;

use Illuminate\Support\Facades\Lang;

trait BooleanAttributeTrait
{
    public function getAttributeOptionsByBoolean(): array
    {
        $casts = $this->getCasts();

        $attributeDetails = [];
        foreach ($casts as $attribute => $type) {
            if (! is_string($type) || ! in_array($type, ['bool', 'boolean'])) {
                continue;
            }

            $attributeDetails[$attribute] = [
                'method' => 'booleanLabel',
            ];
        }

        return $attributeDetails;
    }

    public function booleanLabel($attribute): ?string
    {
        return Lang::boolean($this->$attribute);
    }
}
