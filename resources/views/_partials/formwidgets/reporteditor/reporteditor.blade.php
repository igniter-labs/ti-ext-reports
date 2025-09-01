<div
    data-control="report-editor"
    data-filters='@json($filters)'
    data-rules='@json($rules)'
    {!! $field->getAttributes() !!}
>
    <div data-control="builder" class="w-100"></div>

    <textarea data-control="rules" name="{{ $field->getName() }}" class="d-none">@json($rules)</textarea>

    @foreach($this->customFilterInputs as $fieldName => $filterInput)
        <script type="text/template" data-field-template="{{$fieldName}}">
            {!! $filterInput !!}
        </script>
    @endforeach
</div>
