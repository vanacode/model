<?php

namespace Vanacode\Model\Traits;

use Illuminate\Support\Str;
use Vanacode\Model\Actions\ItemAction;
use Vanacode\Model\Actions\ItemActionList;
use Vanacode\Model\Interfaces\ActionInterface;
use Vanacode\Support\VnStr;

/**
 * @mixin ActionInterface
 */
trait ActionTrait
{
    public bool $skipTraitActions = false;

    protected array $actions = [
        'show',
        'edit',
        'destroy',
    ];

    protected array $routeActions = [];

    protected static array $staticActions = [
        'index',
        'create',
        'truncate',
    ];

    public static function getStaticActions(): array
    {
        return static::$staticActions;
    }

    public static function canDoStaticAction(string $action): bool
    {
        $action = VnStr::forceSnake($action);
        $canDoAction = sprintf('canDo%sStaticAction', Str::studly($action));

        return method_exists(static::class, $canDoAction) ? static::$canDoAction() : true;
    }

    public function skipTraitActions(): bool
    {
        return $this->skipTraitActions;
    }

    public function getActions(): array
    {
        return $this->getRouteScopedOptions($this->routeActions, $this->actions);
    }

    public function getActionOptions(string $resource, string $subResource = '', array $actions = []): ItemActionList
    {
        return new ItemActionList($this, $resource, $subResource, $actions);
    }

    public function canDoAction(string $action): bool
    {
        $action = VnStr::forceSnake($action);
        $canDoAction = sprintf('canDo%sAction', Str::studly($action));

        return method_exists($this, $canDoAction) ? $this->$canDoAction() : true;
    }

    public function canDoShowAction(): bool
    {
        return true;
    }

    public function canDoEditAction(): bool
    {
        return true;
    }

    public function canDoDestroyAction(): bool
    {
        return true;
    }

    public function canDoRestoreAction(): bool
    {
        return true;
    }

    public function renderAction(string $action, array $options = []): string
    {
        $action = new ItemAction($this, $action, $this->getResource(), '', $options);

        return $action->render();
    }

    public function getLinkByAction(string $label, string $action = 'show', array $options = []): string
    {
        $options['label'] = $label;
        $link = $this->renderAction($action, $options);

        return $link ?: $label;
    }
}
