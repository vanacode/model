<?php

namespace Vanacode\Model\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\Lang;
use Illuminate\Support\Str;
use Vanacode\Model\Interfaces\ModelInterface;
use Vanacode\Model\Interfaces\RelationshipsInterface;

/**
 * @mixin RelationshipsInterface
 */
trait RelationshipsTrait
{
    protected array $relationships;

    // https://laracasts.com/discuss/channels/eloquent/get-all-model-relationships
    // https://laracasts.com/discuss/channels/eloquent/is-there-a-way-to-list-all-relationships-of-a-model
    public function relationships(): array
    {
        if (! isset($this->relationships)) {
            $reflector = new \ReflectionClass($this);
            $relations = [];
            foreach ($reflector->getMethods() as $reflectionMethod) {
                $returnType = $reflectionMethod->getReturnType();
                if (is_a($returnType, \ReflectionNamedType::class)) {
                    $returnClass = $returnType->getName();
                    if (is_subclass_of($returnClass, Relation::class)) {
                        $relations[$reflectionMethod->getName()] = $returnClass;
                    }
                }
            }
            $this->relationships = $relations;
        }

        return $this->relationships;
    }

    /**
     * Called dynamically by Attributes
     */
    public function getAttributeOptionsByRelationships(): array
    {
        $relationships = $this->relationships();
        $attributeDetails = [];
        foreach ($relationships as $method => $relationType) {
            if ($relationType !== BelongsTo::class) {
                continue;
            }
            /**
             * @var BelongsTo $related
             */
            $related = $this->$method();
            $relation = $related->getRelated();
            if (! is_a($relation, ModelInterface::class)) {
                $table = $relation->getTable();
                $attributeDetails[$related->getForeignKeyName()] = [
                    'attribute' => Str::snake($method).'_name',
                    'label' => Lang::resourceSingular($table),
                    'html' => true,
                    'with' => $method,
                ];

                continue;
            }
            $resource = $relation->getResource();
            $attributeDetails[$related->getForeignKeyName()] = [
                'attribute' => Str::snake($method).'_self_link',
                'label' => Lang::resourceSingular($resource),
                'html' => true,
                'with' => $method.':'.$relation->getKeyName().','.$relation->getMainKeyName(),
            ];
        }

        return $attributeDetails;
    }
}
