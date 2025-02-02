<?php

namespace Vanacode\Model\Interfaces;

interface RelationshipsInterface
{
    // https://laracasts.com/discuss/channels/eloquent/get-all-model-relationships
    // https://laracasts.com/discuss/channels/eloquent/is-there-a-way-to-list-all-relationships-of-a-model
    public function relationships(): array;

    /**
     * Called dynamically by Attributes
     */
    public function getAttributeOptionsByRelationships(): array;
}
