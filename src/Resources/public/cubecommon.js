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
        cols.each(function (colNo) {
            var colId = $(this).attr('id');
            if (hidableSettings[colId]) {
                updateOneCol(table, colNo, hidableSettings[colId].hidden);
            }
        });
    };

    var updateOneCol = function(table, colNo, hide)
    {
        var colFilter = ':nth-child';
        var cells = table.find('tr td, tr th').filter(colFilter+'(' + (colNo + 1) + ')');
        if (hide) {
            cells.hide();
        } else {
            cells.show();
        }
    };

    var tableSettings = {};

    cs.initializeColsSelection = function ()
    {
        // initialize selectors
        var selectorBtns = $('.colsSelector');
        selectorBtns.each(function () {
            var btn = $(this);
            var id = btn.attr('id') || '';
            if (true) {
                var setSettings = {};
            }
            var tbl = btn.closest('table');
            var settings = {}; // own variable for keeping the column order
            tbl.find('tr').eq(0).children('td, th').each( function() {
                var col = $(this);
                if (col.filter('[id^=col]').length > 0) {
                    var colId = col.attr('id');
                    var cSettings = setSettings[colId] || {};
                    cSettings.colId = colId;
                    settings[colId] = cSettings;
                }
            });
            tableSettings[id] = settings;
            updateCols(tbl, settings);
        });
    };

    cs.updateColumnView = function (colId, hide) {
        var col = $('#'+colId);
        var colNo = col.closest('tr').find('td, th').index(col);
        var table = col.closest('table');
        updateOneCol(table, colNo, hide);
    }

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
})();
