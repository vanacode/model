<?php

namespace Vanacode\Model\Interfaces;

use Illuminate\Database\Eloquent\Model;
use Vanacode\Model\Actions\ItemActionList;
use Vanacode\Support\Interfaces\MethodMatchInterface;

/**
 * @mixin MethodMatchInterface
 * @mixin MainKeyInterface
 * @mixin Model
 */
interface ActionInterface
{
    public static function getStaticActions(): array;

    public function skipTraitActions(): bool;

    public function getActions(): array;

    public function getActionOptions(string $resource, string $subResource = '', array $actions = []): ItemActionList;

    public function canDoAction(string $action): bool;

    public function canDoShowAction(): bool;

    public function canDoEditAction(): bool;

    public function canDoDestroyAction(): bool;

    public function canDoRestoreAction(): bool;

    public function renderAction(string $action, array $options = []): string;

    public function getLinkByAction(string $label, string $action = 'show', array $options = []): string;
}
