<div
    id="{{ $this->getId('smart-reports-container') }}"
    class="p-3"
    data-control="smart-reports"
    data-alias="{{ $this->alias }}"
    data-toolbar="false"
>
    @if($widgetTitle)
        <h6 class="widget-title">
            {{ $widgetTitle }}
        </h6>
    @endif
    {!! $dataTableWidget?->render() !!}
</div>
