function Dashboard() {
    this.init = function(callback){
        if (typeof callback == 'undefined') {
            callback = function () {};
        }

        $.get('/dashboard/', function( response ){
            render(response);

            callback(response);
        }).done(function(){
            $(".loading").addClass('hide');
        });
    };

    var getCompileData = function (status, response) {
        return {
            status: status,
            headers: response.headers,
            total: response.totals[status],
            dates: response.statuses[status].dates,
            list: response.stockItem.hasOwnProperty(status) ? Object.values(response.stockItem[status]) : []
        };
    };

    var render = function(response){
        buildStockItemsConcern([
            { key: 'assigned', mapper: getCompileData },
            { key: 'waiting_for_picking', mapper: getCompileData },
            { key: 'picked', mapper: getCompileData },
            { key: 'ready_for_shipping', mapper: getCompileData },
            {
                key: 'mono-sku',
                mapper: function (key, response) {
                    return {
                        headers: response.headers,
                        total: response.totals.monoSku,
                        dates: response.monoSku.dates,
                        list: []
                    }
                }
            }
        ], response);

        buildProgressPanel(response.progress);
        buildWarnings(response.losted_orders);
        buildUpdateButton();
    };

    var buildStockItemsConcern = function(render, response){
        $.each(render, function( index, renderObj ) {
            var html    = $('#table-' + renderObj.key).html();
            var compile = Handlebars.compile(html);

            $('#table-'+ renderObj.key +'-render').html(compile(
                renderObj.mapper(renderObj.key, response)
            ));
        })
    };

    var buildProgressPanel = function (progress) {
        var html = $('#progress-panel').html(),
            compile = Handlebars.compile(html);

        $('#progress-panel-render').html(compile(progress));
    };

    var buildWarnings = function (lostedOrders) {
        var html = $('#losted-orders').html(),
            compile = Handlebars.compile(html),
            data = {orders: lostedOrders};

        $('#losted-orders-render').html(compile(data));
    };

    var buildUpdateButton = function () {
        var html = $('#button-action').html(),
            compile = Handlebars.compile(html);

        $('#button-action-render').html(compile());

        $('#update_dashboard').click(function(){
            $('#update_dashboard').blur();
            $('.text-button').addClass('hide');
            $('.la-ball-fall').removeClass('hide');

            $('.well-table').css('opacity', '0.5');
            $('.table tbody tr td:nth-child(2)').unbind();


            dashboard.init(function () {
                $('.text-button').removeClass('hide');
                $('.la-ball-fall').addClass('hide');

                $('.well-table').css('opacity', '1');
                $('.table:first tbody tr td:nth-child(2)').click(function(){
                    $('#myModal').modal('show')
                });
            });
        });
    };
}

var dashboard = new Dashboard();

dashboard.init();
