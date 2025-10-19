+function ($) {
    "use strict"

    if ($.fn === undefined) $.fn = {}

    if ($.fn.smartReports === undefined)
        $.fn.smartReports = {}

    var SmartReports = function (element, options) {
        this.$el = $(element)
        this.options = options
        this.pivot = null

        // this.init()
    }

    SmartReports.prototype.constructor = SmartReports

    SmartReports.prototype.init = function () {
        if (!this.$el.attr('id')) {
            this.$el.attr('id', 'smart-reports-container-'+Math.random().toString(36).substring(7))
        }

        this.options.container = this.$el.attr('id')
        // this.options.reportcomplete = $.proxy(this.reportComplete, this)

        this.pivot = new WebDataRocks(this.options);

        this.pivot.on('reportcomplete', $.proxy(this.reportComplete, this));
    }

    SmartReports.prototype.reportComplete = function () {
        var self = this,
            handler = this.options.alias+'::onFetchReport';

        this.pivot.off("reportcomplete");

        $.request(handler).done(function (json) {
            self.pivot.setReport({
                dataSource: {data: json.data},
                slice: json.slice,
                options: json.options,
                conditions: json.conditions,
                formats: json.formats,
                tableSizes: json.tableSizes
            });
        });
    }

    SmartReports.DEFAULTS = {
        alias: undefined,
        container: "pivot-container",
        toolbar: true,
        height: 550,
        reportOptions: {},
    }

    var old = $.fn.smartReports

    $.fn.smartReports = function (option) {
        var args = Array.prototype.slice.call(arguments, 1),
            result = undefined

        this.each(function () {
            var $this = $(this)
            var data = $this.data('ti.smartReports')
            var options = $.extend({}, SmartReports.DEFAULTS, $this.data(), typeof option == 'object' && option)
            if (!data) $this.data('ti.smartReports', (data = new SmartReports(this, options)))
            if (typeof option == 'string') result = data[option].apply(data, args)
            if (typeof result != 'undefined') return false
        })

        return result ? result : this
    }

    $.fn.smartReports.Constructor = SmartReports

    $.fn.smartReports.noConflict = function () {
        $.fn.smartReports = old
        return this
    }

    $(document).render(function () {
        $('[data-control="smart-reports"]').smartReports()
    })
}(window.jQuery)
