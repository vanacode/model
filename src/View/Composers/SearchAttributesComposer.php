<?php

namespace Vanacode\Model\View\Composers;

use Illuminate\View\View;
use Vanacode\Model\Attributes\AttributeList;

class SearchAttributesComposer
{
    public function compose(View $view): void
    {
        /** @var AttributeList $attributeList */
        $attributeList = $view->offsetGet('attributeList');
        $searchAttributes = $attributeList->getSearchAttributes()->pluck('searchAttribute');
        foreach ($searchAttributes as $attribute) {
            if ($attribute->composer) {
                $composer = \App::make($attribute->composer);
                $composer->compose($view);
            }
        }
        $view->with(compact('searchAttributes'));
    }
}
