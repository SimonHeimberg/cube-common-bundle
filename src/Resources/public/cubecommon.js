/* CubeTools CubeCommonBundle */
if (typeof(cubetools) === 'undefined') {
    cubetools = {};
}

(function () {
    cubetools.colsSelector || ( cubetools.colsSelector = {} );

    var cs = cubetools.colsSelector;

    var updateCols = function(table, hidableSettings)
    {
        var cols = table.find('tr').eq(0).find('td, th');
        cols.each(function () {
            var colId = $(this).attr('id');
            if (hidableSettings[colId]) {
                updateOneCol(table, hidableSettings[colId].colNo, hidableSettings[colId].hidden);
            }
        });
    };

    var updateOneCol = function(table, colNo, hide)
    {
        var colFilter = ':nth-child';
        if (table.attr('data-colFilter')) {
            colFilter = table.attr('data-colFilter');
            // example: :nth-child, :nth-of-type, :nth-col (from bramstein/column-selector)
        }
        var cells = table.find('tr td, tr th').filter(colFilter+'('+colNo+')');
        if (hide) {
            cells.hide();
        } else {
            cells.show();
        }
    };

    var tableSettings = {};

    cs.initializeColsSelection = function (settingsOfTables)
    {
        // initialize selectors
        var selectorBtns = $('.colsSelector');
        selectorBtns.each(function () {
            var btn = $(this);
            var id = btn.attr('id') || '';
            if (settingsOfTables[id] && null !== settingsOfTables[id].settings) {
                var setSettings = settingsOfTables[id].settings;
            } else {
                var setSettings = {};
            }
            var tbl = btn.closest('table');
            var settings = {}; // own variable for keeping the column order
            tbl.find('tr').eq(0).children('td, th').each( function(i) {
                var col = $(this);
                if (col.filter('[id^=col]').length > 0) {
                    var colId = col.attr('id');
                    var cSettings = setSettings[colId] || {};
                    cSettings.colId = colId;
                    cSettings.colNo = i + 1;
                    settings[colId] = cSettings;
                }
            });
            tableSettings[id] = settings;
            updateCols(tbl, settings);
        });
    };

    cs.updateColumnView = function (colId, hide) {
        var col = $('#'+colId);
        var table = col.closest('table');
        var id = table.find('.colsSelector').attr('id') || '';
        var settings = cs.getHidableSettings(id);
        updateOneCol(table, settings[colId].colNo, hide);
        settings[colId].hidden = hide;
        cs.saveHidableSettings(id, settings);
    };


    cs.getHidableSettings = function (id) {
        return tableSettings[id];
    };

    cs.getButtonForId = function (id) {
        var btnSel = '.colsSelector';
        if (id) {
            btnSel += '#'+id;
        }
        return $(btnSel);
    };

    cs.saveHidableSettings = function (id, settings) {
        var sendUrl = cs.selectorSendUrl;
        if (!sendUrl) {
            console.error('cubetools.columnselector.selectorSendUrl is not set');
            return null;
        }
        if (Array.isArray(settings)) {
            if (!settings.length) {
                var saveSettings = {};
            } else if (settings[0].name && settings[0].value) {
                var saveSettings = {};
                for (var i in settings) {
                    saveSettings[settings[i].name] = settings[i].value;
                }
            }
        } else {
            var saveSettings = {}
            for (var i in settings) {
                var toSave = $.extend({}, settings[i]);
                delete toSave.colId;
                delete toSave.colNo;
                saveSettings[i] = toSave;
            }
        }

        $.ajax({
            method: 'PUT',
            url: sendUrl,
            data: {id: id, fullPath: window.location.pathname, settings: saveSettings},
            dataType: 'json', // response data type
            success: submittedSuccessful,
            error: submittedUnsuccessful,
            _colSelId: id
        });

        return false;
    };

    var submittedSuccessful = function (content /*,jqXHR*/)
    {
        var btn = cs.getButtonForId(this._colSelId);
        var evt = $.Event('cubetools.colselector.column_settings_saved_passed');
        btn.trigger(evt, [content]); // id can be read from event.target.id
    };

    var submittedUnsuccessful = function (jqXHR, textStatus, errorThrown) {
        var btn = cs.getButtonForId(this._colSelId);
        var evt = $.Event('cubetools.colselector.column_settings_saved_failed');
        btn.trigger(evt, [textStatus, errorThrown]); // for id, see above
    };

    var bootstrap = {};

    bootstrap.getContentColumnPopover = function (btn) {
        var id = btn.attr('id') || '';
        var hidableCols = cs.getHidableSettings(id);
        var htmlContent = btn.data('colSelContent');
        if (!htmlContent) {
            var content = $('#popoverContentTemplate').clone().attr('id', null);
            content.find('input[name=id]').val(id);
            var formFieldsTemplate = content.find(".columnSelection").eq(0);
            var fieldParent = formFieldsTemplate.parent();
            for(var colId in hidableCols) {
                var columnFields = formFieldsTemplate.clone().show();
                var colData = hidableCols[colId];
                columnFields.find('label').attr('for', colId).children(':not(:input)').eq(0).text($('#'+colId).text());
                columnFields.find('input').attr('name', colId).attr('checked', !colData.hidden);
                fieldParent.append(columnFields);
            }
            formFieldsTemplate.remove();
            var html = content.html();
            btn.data('colSelContent', html);
        } else {
            var content = $(htmlContent).wrap('<div>').parent(); // wrap because html() returns inner html
            var columnFields = content.find(".columnSelection");
            for(var colId in hidableCols) {
                var colData = hidableCols[colId];
                columnFields.find('input[name='+colId+']').attr('checked', !colData.hidden);
            }
            var html = content.html();
        }

        return html;
    };

    bootstrap.closePopover = function (/* event */) {
        var id = $(this).closest('form').find('input[name=id]').val();
        var btn = cs.getButtonForId(id);
        btn.popover('hide');
    };

    bootstrap.updateColumnHidden = function (/* event */) {
        var inp = $(this);
        cs.updateColumnView(inp.attr('name'), !inp.prop('checked'));
    };

    cs.initializeBootstrapPopover = function (title, closeBtnSelector, checkboxSelector, rootSelector) {
        if ('undefined' === typeof(closeBtnSelector)) {
            closeBtnSelector = '.colSelCloseBtn';
        }
        if ('undefined' === typeof(checkboxSelector)) {
            checkboxSelector = 'form.colSelForm .columnSelection input';
        }
        if ('undefined' === typeof(rootSelector)) {
            rootSelector = document.body;
        }
        $('.colsSelector').popover({
            placement: 'right',
            html: true,
            title: '<button type="button" class="close colSelCloseBtn" aria-label="Close"><span aria-hidden="true">&times;</span></button>'+title,
            content: function() {
                return bootstrap.getContentColumnPopover($(this));
            }
        });
        $(rootSelector).on('click', closeBtnSelector, bootstrap.closePopover);
        $(rootSelector).on('change', checkboxSelector, bootstrap.updateColumnHidden);
    };
})();
