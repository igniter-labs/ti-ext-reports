+function ($) {
    "use strict"

    if ($.fn === undefined) $.fn = {}

    if ($.fn.reportEditor === undefined)
        $.fn.reportEditor = {}

    var ReportEditor = function (element, options) {
        this.$el = $(element)
        this.options = options
        this.queryBuilder = null
        this.$builder = this.$el.find('[data-control="builder"]');
        this.$rulesHolder = this.$el.find('[data-control="rules"]');

        this.init()
        this.initBuilder();
    }

    ReportEditor.prototype.constructor = ReportEditor

    ReportEditor.prototype.init = function () {
        this.$el.closest('form').on('ajaxSetup', $.proxy(this.onSubmitForm, this))
    }

    ReportEditor.prototype.initBuilder = function () {
        if (!this.options.filters.length) {
            return
        }

        this.options.filters.forEach((filter) => {
            var fieldTemplate = $('[data-field-template="'+filter.id+'"]');
            if (fieldTemplate.length) {
                filter.input = (rule, input_name) => fieldTemplate.html().replace(/name="[^"]*"/g, `name="${input_name}"`)
            }
        })

        // console.log(this.options, JSON.parse(JSON.stringify(this.options.rules)))
        this.$builder.queryBuilder(this.options);
        this.queryBuilder = this.$builder[0].queryBuilder;

        this.queryBuilder.on('afterUpdateRuleFilter', $.proxy(this.onUpdateRuleFilter, this));
        this.queryBuilder.on('afterUpdateRuleOperator', $.proxy(this.onUpdateRuleOperator, this));

        $('[data-control="selectlist"]').selectList()
    }

    ReportEditor.prototype.onSubmitForm = function () {
        this.$rulesHolder.val(JSON.stringify(this.queryBuilder.getRules()));
    }

    ReportEditor.prototype.onUpdateRuleFilter = function (event, rule, previousFilter) {
        $('[data-control="selectlist"]').selectList()
    }

    ReportEditor.prototype.onUpdateRuleOperator = function (event, rule, previousFilter) {
        $('[data-control="selectlist"]').selectList()
    }

    ReportEditor.DEFAULTS = {
        alias: undefined,
        filters: [],
        plugins: [],
        rules: false,
        templates: {
            group: ({group_id, level, conditions, icons, settings, translate, builder}) => {
                return `
<div id="${group_id}" class="rules-group-container bg-light">
  <div class="rules-group-header">
    <div class="btn-group float-end group-actions">
      <button type="button" class="btn btn-link text-success text-decoration-none fw-bold" data-add="rule">
        <i class="fa fa-plus"></i> ${translate("add_rule")}
      </button>
      ${settings.allow_groups === -1 || settings.allow_groups >= level ? `
        <button type="button" class="btn btn-link text-success text-decoration-none fw-bold ms-2 px-0" data-add="group">
          <i class="fa fa-plus-circle"></i> ${translate("add_group")}
        </button>
      ` : ''}
      ${level > 1 ? `
        <button type="button" class="btn btn-link text-danger text-decoration-none fw-bold ms-2 px-0" data-delete="group">
          <i class="fa fa-trash-alt"></i> ${translate("delete_group")}
        </button>
      ` : ''}
    </div>
    <div class="btn-group group-conditions">
      ${conditions.map(condition => `
        <label class="btn btn-sm btn-outline-secondary">
          <input type="radio" name="${group_id}_cond" value="${condition}"> ${translate("conditions", condition)}
        </label>
      `).join('\n')}
    </div>
    ${settings.display_errors ? `
      <div class="error-container"><i class="${icons.error}"></i></div>
    ` : ''}
  </div>
  <div class=rules-group-body>
    <div class=rules-list></div>
  </div>
</div>`;
            },
            rule: ({rule_id, icons, settings, translate, builder}) => {
                return `
<div id="${rule_id}" class="rule-container py-0">
  <div class="rule-header">
    <div class="btn-group float-end rule-actions pt-2">
      <button type="button" class="btn btn-link text-danger text-decoration-none fw-bold" data-delete="rule">
        <i class="fa fa-trash-alt"></i>
      </button>
    </div>
  </div>
  ${settings.display_errors ? `
    <div class="error-container"><i class="fa fa-triangle-exclamation"></i></div>
  ` : ''}
  <div class="rule-filter-container"></div>
  <div class="rule-operator-container"></div>
  <div class="rule-value-container"></div>
</div>`;
            },
            operatorSelect: ({rule, operators, icons, settings, translate, builder}) => {
                let optgroup = null;
                return `
${operators.length === 1 ? `
<span>
${translate("operators", operators[0].type)}
</span>
` : ''}
<div class="control-selectlist w-100">
<select data-control="selectlist" class="form-select ${operators.length === 1 ? 'd-none' : ''}" name="${rule.id}_operator">
  ${operators.map(operator => `
    ${optgroup !== operator.optgroup ? `
      ${optgroup !== null ? `</optgroup>` : ''}
      ${(optgroup = operator.optgroup) !== null ? `
        <optgroup label="${translate(settings.optgroups[optgroup])}">
      ` : ''}
    ` : ''}
    <option value="${operator.type}" ${operator.icon ? `data-icon="${operator.icon}"` : ''}>${translate("operators", operator.type)}</option>
  `).join('')}
  ${optgroup !== null ? '</optgroup>' : ''}
</select>
</div>`;
            },
            filterSelect: ({rule, filters, icons, settings, translate, builder}) => {
                let optgroup = null;
                return `
<div class="control-selectlist w-100">
<select class="form-select" name="${rule.id}_filter" data-control="selectlist">
  ${settings.display_empty_filter ? `
    <option value="-1">${settings.select_placeholder}</option>
  ` : ''}
  ${filters.map(filter => `
    ${optgroup !== filter.optgroup ? `
      ${optgroup !== null ? `</optgroup>` : ''}
      ${(optgroup = filter.optgroup) !== null ? `
        <optgroup label="${translate(settings.optgroups[optgroup])}">
      ` : ''}
    ` : ''}
    <option value="${filter.id}" ${filter.icon ? `data-icon="${filter.icon}"` : ''}>${translate(filter.label)}</option>
  `).join('')}
  ${optgroup !== null ? '</optgroup>' : ''}
</select>
</div>`;
            },
            ruleValueSelect: ({name, rule, icons, settings, translate, builder}) => {
                let optgroup = null;
                return `
<div class="control-selectlist w-100">
<select data-control="selectlist" class="form-select" name="${name}" ${rule.filter.multiple ? 'multiple' : ''}>
  ${rule.filter.placeholder ? `
    <option value="${rule.filter.placeholder_value}" disabled selected>${rule.filter.placeholder}</option>
  ` : ''}
  ${rule.filter.values.map(entry => `
    ${optgroup !== entry.optgroup ? `
      ${optgroup !== null ? `</optgroup>` : ''}
      ${(optgroup = entry.optgroup) !== null ? `
        <optgroup label="${translate(settings.optgroups[optgroup])}">
      ` : ''}
    ` : ''}
    <option value="${entry.value}">${entry.label}</option>
  `).join('')}
  ${optgroup !== null ? '</optgroup>' : ''}
</select>
</div>`;
            }
        }
    }

    var old = $.fn.reportEditor

    $.fn.reportEditor = function (option) {
        var args = Array.prototype.slice.call(arguments, 1),
            result = undefined

        this.each(function () {
            var $this = $(this)
            var data = $this.data('ti.reportEditor')
            var options = $.extend({}, ReportEditor.DEFAULTS, $this.data(), typeof option == 'object' && option)
            if (!data) $this.data('ti.reportEditor', (data = new ReportEditor(this, options)))
            if (typeof option == 'string') result = data[option].apply(data, args)
            if (typeof result != 'undefined') return false
        })

        return result ? result : this
    }

    $.fn.reportEditor.Constructor = ReportEditor

    $.fn.reportEditor.noConflict = function () {
        $.fn.reportEditor = old
        return this
    }

    $(document).render(function () {
        $('[data-control="report-editor"]').reportEditor()
    })
}(window.jQuery)
