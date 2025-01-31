<?php

namespace Vanacode\Model\Traits;

use Illuminate\Database\Eloquent\SoftDeletes as BaseSoftDeletes;
use Illuminate\Support\Facades\Route;

trait SoftDeletes
{
    use BaseSoftDeletes;

    public static function getStaticActionsBySoftDeletes(): array
    {
        return [
            'deleted-index',
        ];
    }

    public function getActionsBySoftDeletes(): array
    {
        $isNotSoftIndex = ! Route::is('*deleted*');

        return [
            'destroy' => $isNotSoftIndex,
            'edit' => $isNotSoftIndex,
            'show' => $isNotSoftIndex,
            'restore' => ! $isNotSoftIndex,
            'show-deleted' => ! $isNotSoftIndex,
            'force-destroy',
        ];
    }

    public static function getAttributeOptionsBySoftDeletes(): array
    {
        $deletedAt = defined(static::class.'::DELETED_AT') ? static::DELETED_AT : 'deleted_at';

        return [
            $deletedAt => [
                'show_by_route' => '*deleted*',
            ],
        ];
    }
}
