$(function() {
    $('.table:first tbody tr td:nth-child(2)').click(function(){

        var logisticsProviderName = $(this).siblings(":first").text();
        var modalTitle            = "Logistic Provider";

        $('#pickingListModal').modal('show');

        $('.modal-body .content').html('');
        $('.modal-title').html(modalTitle);

        $(".loading").removeClass('hide');
        $(".loading").addClass('show');

        $.get('/zed/zed-order-item/picking-list',
        { logisticsProvider: logisticsProviderName },
        function( response ){
            $('.modal-body .content').jsGrid({
                width: "100%",
                height: "100%",
                sorting: true,
                paging: true,
                filtering: true,
                paging: true,
                pageLoading: true,
                noDataContent: "Not found",
                data: response,
                fields: [
                    { name: "dateList"           , type: "text"  , width: 80  },
                    { name: "idList"             , type: "number", width: 50  },
                    { name: "dateReadyPicking"   , type: "text"  , width: 100 },
                    { name: "username"           , type: "text"  , width: 100 },
                    { name: "incrementId"        , type: "text"  , width: 100 },
                    { name: "customerName"       , type: "text"  , width: 150 },
                    { name: "pickingObservation" , type: "text"  , width: 100 },
                ]
            });
        }).done(function(){
            $(".loading").addClass('hide');
            $(".loading").removeClass('show');
            $(".modal-title").html(modalTitle + " - " + logisticsProviderName);
        });
    })
})
