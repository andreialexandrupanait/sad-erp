@props(['isTemplate' => false])

@if($isTemplate)
    <div :style="'height: ' + (block.data.height || 40) + 'px'"></div>
@else
    <div class="h-8"></div>
@endif
