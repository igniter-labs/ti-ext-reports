<div
    id="{{ $this->getId('smart-reports-container') }}"
    class="p-3"
    data-control="smart-reports"
    data-alias="{{ $this->alias }}"
    data-toolbar="false"
>
    {{ $dataTableWidget?->render() }}
</div>
