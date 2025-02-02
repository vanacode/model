<?php

namespace Vanacode\Model\Attributes;

use Illuminate\Support\Str;
use Vanacode\Support\Traits\ClassConstantTraits;

class SearchAttribute
{
    use ClassConstantTraits;

    const OPERATION_LIKE = 'like';

    const OPERATION_WHERE_IN = 'whereIn';

    const OPERATION_WHERE_HAS = 'whereHas';

    const INPUT_METHOD_TEXT = 'text';

    const INPUT_METHOD_SELECT = 'select';

    public string $name;

    public string $label;

    public string $operation;

    public string $inputMethod;

    public array|string $searchBy;

    public ?string $optionsVariable;

    public ?string $relation;

    public ?string $composer;

    public ?string $class;

    public ?int $position;

    public ?bool $multiple;

    public function __construct(protected readonly Attribute $attribute, public array $options)
    {
        $this->name = $options['name'] ?? $attribute->name;
        $this->composer = $options['composer'] ?? null;
        if ($this->composer) {
            $options['options_variable'] = $options['options_variable'] ?? Str::camel(Str::plural(str_replace('_id', '', $this->name))); // TODO _id by config
            $options['input_method'] = $options['input_method'] ?? self::INPUT_METHOD_SELECT;
            $options['multiple'] = $options['multiple'] ?? true;
            $options['operation'] = $options['operation'] ?? ($options['multiple'] ? self::OPERATION_WHERE_IN : self::OPERATION_LIKE);
            $options['class'] = empty($options['class']) ? 'form-control dropdown-tags' : $options['class'].' dropdown-tags';
        }

        $this->label = $options['label'] ?? $attribute->label;
        $this->searchBy = $options['search_by'] ?? $this->name;
        $this->inputMethod = $options['input_method'] ?? self::INPUT_METHOD_TEXT;
        if (! in_array($this->inputMethod, $this->inputMethods())) {
            throw new \Exception("inputMethod '{$this->inputMethod}' is not allowed.");
        }

        $this->operation = $options['operation'] ?? self::OPERATION_LIKE;
        if (! in_array($this->operation, $this->operations())) {
            throw new \Exception("Operation '{$this->operation}' is not allowed.");
        }

        $this->position = $options['position'] ?? null;
        $this->optionsVariable = $options['options_variable'] ?? null;
        $this->multiple = $options['multiple'] ?? null;
        $this->class = $options['class'] ?? null;
        $this->relation = $options['relation'] ?? null;
        if ($this->operation == self::OPERATION_WHERE_HAS) {
            $this->relation = $this->relation ?? $this->name;
            $this->searchBy = 'id';
        }
    }

    public function operations(): array
    {
        return self::getConstantsByPrefix('OPERATION');
    }

    public function inputMethods(): array
    {
        return self::getConstantsByPrefix('INPUT_METHOD');
    }
}
