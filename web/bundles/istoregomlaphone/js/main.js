var globals = {};
$(document).ready(function(){
    globals.currentPage;
    globals.item = new Array();
    globals.bulk = new Array();
    globals.itemList = new Array();
    globals.bulkList = new Array();
    globals.itemListRequired = new Array();
    globals.itemId;
    globals.passedValidation = false;
    globals.subtotal = 0;
    globals.discount = 0;
    globals.total = 0;
    globals.amountPaid = 0;
    globals.bulkQty;
    globals.calculateSubtotal;
    globals.calculateTotal;
    globals.transaction = {bulks:new Array() , info:null};
    
    // Table Sorting
    $('.unsorted, .sortAsc, .sortDesc').click(function() {
        $(this).removeClass('active');
        this.className = {
            unsorted: 'sortAsc',
            sortAsc: 'sortDesc',
            sortDesc: 'unsorted'
        }[this.className];
        
        var sortColumn = $(this).attr('id');
        var sortType = 'unsorted';
        switch(this.className){
            case 'sortAsc' : sortType = 'asc'; break;
            case 'sortDesc' : sortType = 'desc'; break;
        }
        
        var controller = $('#controller').val();
        var url = '/'+controller;
        
        if(globals.currentPage>0)
            url += '?page='+globals.currentPage;
        
        if(sortType !== 'unsorted')
            url += '&column='+sortColumn+'&sort='+sortType;
        
        //alert(url);
        window.location = url;
    });
    $('.sortAsc, .sortDesc').addClass('active');
    var columnIndex = $('.sortAsc, .sortDesc').index() + 1;
    $('table tr td:nth-child('+columnIndex+')').addClass('active');
    
    //Highlight Modules
    var controller = $('#controller').val();
    $('#navbar-main ul li').removeClass('active');
    if(controller === 'default')
        $('#navbar-main ul li.home').addClass('active');
    else if(controller === 'category' || controller === 'color' || controller === 'model') {
        $('#navbar-main ul li.settings').addClass('active');
        $('#navbar-main ul li.'+controller).addClass('active');
    } else
        $('#navbar-main ul li.'+controller).addClass('active');
    
    /*$('.btn-hide-nav').on('click', function(e){
        $('.navbar').collapse('hide');
    });*/
    
    $('div#reportModel , div#reportSupplier , div#reportCustomer').slimScroll({
        position: 'left',
        height: '135px',
        alwaysVisible: false,
        wheelStep: '10',
        railVisible: true,
        allowPageScroll: true,
    });
    
    $('.btn-popover').popover();
    $('#bulkDate, #reportFromDate, #reportToDate, #filterFromDate, #filterToDate, #stockFromDate, #stockToDate').datetimepicker({
        pickTime: false,
        language: 'ar'
    });
    //.on('changeDate', function(){
    //    $(this).focus().datetimepicker('hide');
    //});
    /*$('#bulkDate, #reportFromDate, #reportToDate, #filterFromDate, #filterToDate').datetimepicker({endDate: new Date}).on('changeDate', function(){
        $(this).focus().datetimepicker('hide');
    });*/
    
    // Entity Delete Confirmation
    $(document).on("click", ".btn-delete", function (e) {
            e.preventDefault();
            var _self = $(this);
            var entityData = _self.data('id');
            var entity = entityData.split(":");
            var entityType = entity[0];
            var entityId = entity[1];
            var entityName = entity[2];
            //alert(entityType + '/' + entityId + '/' + entityName);
            $('#entityId').val(entityId);
            $('a.btn-delete-confirm').attr('href' , '/'+entityType+'/delete/'+entityId);
            $('label#entityName').html(entityName);
            $('#deleteConfirmation').modal('show');
    }).on("click", "a.btn-delete-confirm", function (e) {
            e.preventDefault();
            var controller = $('#controller').val();
            var action = $('#action').val();
            var entityId = $('#entityId').val();
            if(controller === 'bulk' && action === 'view')
                controller = 'item';
            var url = "/"+controller+"/delete/"+entityId;
            var btn = $(this);
            btn.button('loading');
            $.ajax({
                    url: url,
                    type: "get",
                    async: false,
                    success: function(response){
                        $('#deleteConfirmation').modal('hide');
                        if(response.error===1){
                            $('#alert-message').html(alertDangerMessage(response.message));
                        } else {
                            $('.'+controller+'_'+entityId).remove();
                            var bulkQuantity = $('#bulkQuantity').html();
                            $('#bulkQuantity').html(parseInt(bulkQuantity)-1);
                            $('#alert-message').html(alertSuccessMessage(response.message));
                        }
                    }
            }).always(function () {
                        btn.button('reset');
            });
            
    //Add new postpaid payment
    }).on("click", ".btn-payment-modal", function (e) {
            e.preventDefault();
            
            var _self = $(this);
            var saleId = _self.data('id');
            
            var btn = $(this);
            var data = {saleId:saleId};
            btn.button('loading');
            $('#addPaymentModalContainer').load("/sale/postpaid/add" , data , function(){
                btn.button('reset');
                $('a.btn-save-payment').attr('href' , '/sale/postpaid/add/'+saleId);
                $('#addPostpaidPayment').modal('show');
            });
            
    //Add new transaction payment        
    }).on("click", ".btn-trans-payment-modal", function (e) {
            e.preventDefault();
            
            var _self = $(this);
            var transactionId = _self.data('id');
            
            var btn = $(this);
            var data = {transactionId:transactionId};
            btn.button('loading');
            $('#addPaymentModalContainer').load("/payment/add" , data , function(){
                btn.button('reset');
                $('a.btn-save-payment').attr('href' , '/payment/add/'+transactionId);
                $('#viewTransactionPayments').modal('hide');
                $('#viewTransactionPayments').on('hidden.bs.modal',function(){
                    $('#addTransactionPayment').modal('show');
                });
            });
            
    }).on('click' , '.btn-add-trans-payment' , function(e){
        e.preventDefault();
        var element = $('#paymentAmount');
        if(!validPostpaidAmount($(element))){
            $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
            $(element).focus();
        } else {
            $('label#paidAmount').html($(element).val());
            $('#confirmPaymentModal').modal('show');
        }
    }).on('click' , '.btn-confirm-trans-payment' , function(e){
        e.preventDefault();
        var element = $('#paymentAmount');
        var transactionId = $('#transactionId').val();
        var amount = $(element).val();
        var btn = $(this);
        btn.button('loading');
        $.ajax({
                url: "/payment/add/" + transactionId,
                type: "post",
                async: false,
                data: {amount:amount},
                success: function(response){
                    if(response.error==0){
                        $(element).closest('.form-group').removeClass('has-error').addClass('has-success');
                        $('.payment-amount-error').html(lang['Amount successfully added']);
                        $('#alert-message').html(alertSuccessMessage(lang['Payment has been successfully added to transaction #']+transactionId));
                        $(element).val(0);
                        $('#label-transaction-paid').html(response.total_paid + ' L.E.');
                        $('#label-transaction-remaining').html(response.total_due - response.total_paid + lang[' L.E.']);
                        $('tr.transaction-'+transactionId+' td.total-paid').html(response.total_paid + lang[' L.E.']);
                        //$('tr.transaction-'+transactionId+' td.total-remaining').html(response.total_due - response.total_paid + lang[' L.E.']);
                        $('#confirmPaymentModal').modal('hide');
                        $('#addTransactionPayment').modal('hide');
                    } else {
                        $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                        $('.payment-amount-error').html(lang['Amount can not be added']);
                    }
                }
        }).always(function () {
                btn.button('reset');
        });
    }).on("keypress", "#paymentAmount", function(e){
            if ( e.which == 13 )
                e.preventDefault();
            //$('#filtrationBulk').modal('show');
            
    }).on("click", ".btn-bulk-filter-modal", function(e){
            e.preventDefault();
            $('#filtrationBulk').modal('show');
            
    }).on("click", ".btn-bulk-filter", function(e){
            //e.preventDefault();
            $('#filterForm').submit();
    }).on("click", ".btn-add-item-modal", function(e){
            e.preventDefault();
            $('#addSaleItem').modal('show');
    }).on("click", ".btn-add-item", function(e){
            e.preventDefault();
            $('#addSaleItem').modal('hide');
    });
    
    $('#addPaymentModalContainer').on('change' , '#postpaidAmount' , function(){
        validPostpaidAmount($(this));
        
    //Add payment modal
    }).on('click' , '.btn-add-payment' , function(e){
        e.preventDefault();
        var element = $('#postpaidAmount');
        if(!validPostpaidAmount($(element))){
            $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
            $(element).focus();
        } else {
            $('label#paidAmount').html($(element).val());
            $('#confirmPaymentModal').modal('show');
        }
    }).on('click' , '.btn-confirm-payment' , function(e){
        e.preventDefault();
        var element = $('#postpaidAmount');
        var saleId = $('#saleId').val();
        var amount = $(element).val();
        var btn = $(this);
        btn.button('loading');
        $.ajax({
                url: "/sale/postpaid/add/" + saleId,
                type: "post",
                async: false,
                data: {amount:amount},
                success: function(response){
                    if(response.error==0){
                        $(element).closest('.form-group').removeClass('has-error').addClass('has-success');
                        $('.postpaid-amount-error').html(lang['Amount successfully added']);
                        $('#alert-message').html(alertSuccessMessage(lang['Payment has been successfully added to bill #']+saleId));
                        $(element).val(0);
                        $('#label-sale-paid').html(response.total_paid + ' L.E.');
                        $('#label-sale-remaining').html(response.total_due - response.total_paid + lang[' L.E.']);
                        $('tr.sale-'+saleId+' td.total-paid').html(response.total_paid + lang[' L.E.']);
                        $('tr.sale-'+saleId+' td.total-remaining').html(response.total_due - response.total_paid + lang[' L.E.']);
                        $('#confirmPaymentModal').modal('hide');
                        $('#addPostpaidPayment').modal('hide');
                    } else {
                        $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                        $('.postpaid-amount-error').html(lang['Amount can not be added']);
                    }
                }
        }).always(function () {
                btn.button('reset');
        });
    });
    
    //View payments modal
    $(document).on("click", ".btn-view-payments", function (e) {
            e.preventDefault();
            
            var _self = $(this);
            var saleId = _self.data('id');
            
            var btn = $(this);
            var data = {saleId:saleId};
            btn.button('loading');
            $('#viewPaymentsModalContainer').load("/sale/postpaid/view" , data , function(){
                btn.button('reset');
                $('#viewPostpaidPayments').modal('show');
                $('div#table-payments').slimScroll({
                    height: '381px',
                    alwaysVisible: true,
                    wheelStep: '10',
                    railVisible: true,
                    allowPageScroll: true,
                });
            });
    }).on("click", ".btn-view-trans-payments", function (e) {
            e.preventDefault();
            
            var _self = $(this);
            var transactionId = _self.data('id');
            
            var btn = $(this);
            var data = {transactionId:transactionId};
            btn.button('loading');
            $('#viewPaymentsModalContainer').load("/payment/view" , data , function(){
                btn.button('reset');
                $('#viewTransactionPayments').modal('show');
                $('div#table-payments').slimScroll({
                    height: '381px',
                    alwaysVisible: true,
                    wheelStep: '10',
                    railVisible: true,
                    allowPageScroll: true,
                });
            });
    });
    
    //Refund payment modal
    $(document).on("click", ".btn-refund-modal", function (e) {
        e.preventDefault();
        var _self = $(this);
        var paymentData = _self.data('id');
        var payment = paymentData.split(":");
        $('#input-payment-id').val(payment[0]);
        $('#input-payment-amount').val(payment[1]);
        $('label#paymentAmount').html(payment[1]);
        $('#confirmRefundModal').modal('show');
    }).on("click", ".btn-confirm-refund", function (e){
        e.preventDefault();
        var paymentId = $('#input-payment-id').val();
        var paymentAmount = $('#input-payment-amount').val();
        var btn = $(this);
        btn.button('loading');
        $.ajax({
                url: "/sale/postpaid/refund/" + paymentId,
                type: "get",
                async: false,
                success: function(response){
                    if(response.error==0){
                        $('#confirmRefundModal').modal('hide');
                        $('tr.payment-'+paymentId).remove();
                        var salePaid = parseInt($('#salePaid').val()) - parseInt(paymentAmount);
                        var saleTotal = parseInt($('#saleTotal').val());
                        var saleDiscount = parseInt($('#saleDiscount').val());
                        //$('#salePaid').val(parseInt(salePaid) - parseInt(paymentAmount));
                        
                        //alert(salePaid);
                        $('#label-sale-paid').html(salePaid + ' L.E.');
                        $('#label-sale-remaining').html( parseInt(saleTotal) - parseInt(saleDiscount) - parseInt(salePaid) + ' L.E.');
                        $('#alert-refund-message').html(alertSuccessMessage(lang['Payment has been refunded']));
                    } else {
                        $('#confirmRefundModal').modal('hide');
                        $('#alert-refund-message').html(alertDangerMessage(lang['Can not refund payment at this time.']));
                    }
                }
        }).always(function () {
                btn.button('reset');
        });
        
    //Refund transaction payment
    }).on("click", ".btn-refund-trans-payment-modal", function (e) {
        e.preventDefault();
        var _self = $(this);
        var paymentData = _self.data('id');
        var payment = paymentData.split(":");
        $('#input-payment-id').val(payment[0]);
        $('#input-payment-amount').val(payment[1]);
        $('label#paymentAmount').html(payment[1]);
        $('#confirmRefundModal').modal('show');
    }).on("click", ".btn-confirm-trans-payment-refund", function (e){
        e.preventDefault();
        var paymentId = $('#input-payment-id').val();
        var paymentAmount = $('#input-payment-amount').val();
        var btn = $(this);
        btn.button('loading');
        $.ajax({
                url: "/payment/refund/" + paymentId,
                type: "get",
                async: false,
                success: function(response){
                    if(response.error==0){
                        $('#confirmRefundModal').modal('hide');
                        $('tr.payment-'+paymentId).remove();
                        var transactionPaid = parseInt($('#transactionPaid').val()) - parseInt(paymentAmount);
                        var transactionTotal = parseInt($('#transactionTotal').val());
                        var transactionDiscount = parseInt($('#transactionDiscount').val());
                        //$('#salePaid').val(parseInt(salePaid) - parseInt(paymentAmount));
                        
                        //alert(salePaid);
                        $('#label-transaction-paid').html(transactionPaid + ' L.E.');
                        $('#label-transaction-remaining').html( parseInt(transactionTotal) - parseInt(transactionDiscount) - parseInt(transactionPaid) + ' L.E.');
                        $('#alert-refund-message').html(alertSuccessMessage(lang['Payment has been refunded']));
                    } else {
                        $('#confirmRefundModal').modal('hide');
                        $('#alert-refund-message').html(alertDangerMessage(lang['Can not refund payment at this time.']));
                    }
                }
        }).always(function () {
                btn.button('reset');
        });
        
    //Refund sale modal
    }).on('click', '.btn-refund-sale-modal', function(e){
        e.preventDefault();
        var _self = $(this);
        $('#refundSaleId').val(_self.data('id'));
        $('#refundSaleIdLabel').html(_self.data('id'));
        $('#refundSaleModal').modal('show');
    }).on('click', '.btn-refund-sale', function(e){
        e.preventDefault();
        var saleId = $('#refundSaleId').val();
        var btn = $(this);
        btn.button('loading');
        $.ajax({
                url: "/sale/refund/" + saleId,
                type: "get",
                async: false,
                success: function(response){
                    if(response.error==0){
                        $('#refundSaleModal').modal('hide');
                        $('tr.sale-'+saleId).remove();
                        $('#alert-message').html(alertSuccessMessage(lang['Sale has been refunded']));
                    } else {
                        $('#refundSaleModal').modal('hide');
                        $('#alert-message').html(alertDangerMessage(lang['Can not refund sale at this time.']));
                    }
                }
        }).always(function () {
                btn.button('reset');
        });
        
    //Confirm discount modal
    }).on('click', '.btn-discount-modal', function(e){
        e.preventDefault();
        var _self = $(this);
        var discount = _self.data('id').split(':');
        $('#discountSaleId').val(discount[0]);
        $('#discountSaleIdLabel').html(discount[1]);
        $('#discountValueLabel').html(discount[1]);
        $('#confirmDiscountModal').modal('show');
    }).on('click', '.btn-confirm-discount', function(e){
        e.preventDefault();
        var saleId = $('#discountSaleId').val();
        var btn = $(this);
        btn.button('loading');
        $.ajax({
                url: "/sale/discount/" + saleId,
                type: "get",
                async: false,
                success: function(response){
                    if(response.error==0){
                        console.log($(this));
                        $('#confirmDiscountModal').modal('hide');
                        $('tr.sale-'+saleId+' .btn-discount-modal').addClass('hidden');
                        $('tr.sale-'+saleId+' .btn-discount-confirmed').removeClass('hidden');
                        $('#alert-message').html(alertSuccessMessage(lang['Discount has been confirmed']));
                    } else {
                        $('#confirmDiscountModal').modal('hide');
                        $('#alert-message').html(alertDangerMessage(lang['Can not confirm discount at this time.']));
                    }
                }
        }).always(function () {
                btn.button('reset');
        });
    });
    
    function validPostpaidAmount(element){
        var saleTotal = $('#saleTotal').val();
        var saleDiscount = $('#saleDiscount').val();
        var salePaid = $('#salePaid').val();
        var saleRemaining = saleTotal - saleDiscount - salePaid;
        var passedValidation = true;
        
        if(/^\d+$/.test($(element).val()) && parseInt($(element).val()) > saleRemaining){
            $('.postpaid-amount-error').html(lang['Amount can not be greater than the remaining value']);
            passedValidation = false;
        } else if(/^\d+$/.test($(element).val()) && parseInt($(element).val()) <= 0 ){
            $('.postpaid-amount-error').html(lang['Amount must be greater than zero']);
            passedValidation = false;
        } else if(!/^\d+$/.test($(element).val())){
            $('.postpaid-amount-error').html(lang['Amount must be a number']);
            passedValidation = false;
        } else {
            $('.postpaid-amount-error').html('');
        }
        if(!passedValidation)
            $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
        else
            $(element).closest('.form-group').removeClass('has-error').addClass('has-success');
        return passedValidation;
    }
    
    /*$("#clockbox").flipcountdown({
        size: 'xs',
        showHour:true,
        showMinute:true,
        showSecond:false,
        am:false
    });*/
    //var currentDate = new Date();
    //$('#datebox').html(currentDate.getDay() + '-' + currentDate.getMonth() + '-' + currentDate.getFullYear());

    // Item Warranty Field
    $('#editModalContainer').on('change', 'select[name="itemHasWarranty"]', function(){
        if(this.value === '1'){
            $('#warranty-field select, #change-status-field select').prop("disabled", false);
            $('#warranty-field, #change-status-field').removeClass('hidden');
        } else {
            $('#warranty-field select, #change-status-field select').prop("disabled", true);
            $('#warranty-field, #change-status-field').addClass('hidden');
        }
    });
    
    //Calculate discount
    $('#saleDiscount').change(function(){
        if( /^\d+$/.test($(this).val()) && parseInt($(this).val()) <= globals.subtotal && parseInt($(this).val()) >= 0 ){
            globals.discount = parseInt($(this).val());
            $('#total').html(globals.calculateTotal() + lang[' L.E.']);
            $('#amountPaid').val(0);
            $('#remainingAmount').html(globals.calculateTotal() + lang[' L.E.']);
        } else {
            $(this).val(globals.discount);
            globals.discount = parseInt($(this).val());
            $('#total').html(globals.calculateTotal() + lang[' L.E.']);
            $('#amountPaid').val(0);
            $('#remainingAmount').html(globals.calculateTotal() + lang[' L.E.']);
        }
    })/*.keyup(function(){
        if( /^\d+$/.test($(this).val()) && parseInt($(this).val()) <= globals.subtotal && parseInt($(this).val()) >= 0 ){
            globals.discount = parseInt($(this).val());
            $('#total').html(globals.calculateTotal() + ' L.E.');
        } else {
            $(this).val(globals.discount);
            globals.discount = parseInt($(this).val());
            $('#total').html(globals.calculateTotal() + ' L.E.');
        }
    });*/
    
    $('#amountPaid').change(function(){
        if( /^\d+$/.test($(this).val()) && parseInt($(this).val()) <= globals.total && parseInt($(this).val()) >= 0){
            globals.amountPaid = parseInt($(this).val())
            $('#remainingAmount').html(globals.total-globals.amountPaid + lang[' L.E.']);
        } else if(/^\d+$/.test($(this).val()) && parseInt($(this).val()) > globals.total){
            $(this).val(globals.total);
            globals.amountPaid = parseInt($(this).val())
            $('#remainingAmount').html(globals.total-parseInt($(this).val()) + lang[' L.E.']);
        } else if( parseInt($(this).val()) < 0 ){
            $(this).val(0);
            globals.amountPaid = parseInt($(this).val())
            $('#remainingAmount').html(globals.total-parseInt($(this).val()) + lang[' L.E.']);
        } else {
            $(this).val(0);
            globals.amountPaid = parseInt($(this).val())
            $('#remainingAmount').html(globals.total-parseInt($(this).val()) + lang[' L.E.']);
        } 
    });/*.keyup(function(){
        if(!/^\d+$/.test($(this).val()))
            $(this).val(0);
        else{
            $(this).val(parseInt($(this).val(), 10));
        }
    });*/
    /*
    $('.customer-data #customerName, .customer-data #customerPhone').on('keypress', function(event){
        if(event.which == 13)
            $('.btn-find-customer').click();
    });
    $('.btn-clear-customer').click(function(e){
        $('#customerPhone').val('');
        $('#customerName').val('');
    });
    $('.btn-find-customer').click(function(e){
        var customerPhone = $('#customerPhone').val();
        var customerName = $('#customerName').val();
        var btn = $(this);
        btn.button('loading');
        $.ajax({
                url: "/customer/find",
                type: "post",
                data: {phone:customerPhone, name:customerName},
                success: function(response){
                    if(response.count===1){
                        $('#customerPhone').val(response.customer.c_customer_phone);
                        $('#customerName').val(response.customer.c_customer_name);
                        //$('#customerNotes').val(response.customer.c_customer_notes);
                        $('#alert-message').html(alertInfoMessage(lang['Customer is available.']));
                    } else {
                        $('#alert-message').html(alertDangerMessage(lang['Customer is not found.']));
                    }
                }
        }).always(function () {
                    btn.button('reset');
        });
        e.preventDefault(); // prevents default
        return false;
    });
    */
    $('#itemQuantity').change(function(){
        var itemQty = $(this).val()
        if(!/^\d+$/.test(itemQty) || itemQty<=0){
            $('#itemQuantity').val(1);
        } else if(itemQty >globals.bulkQty){
            $('#itemQuantity').val(globals.bulkQty);
        }
    });
    // Payment method & Amount paid
    $('.table-view-sale').on('change', 'select[name="paymentMethod"]', function(){
        if(this.value === 'postpaid'){
            $('#amountPaid').prop("disabled", false).val(0);
            $('.input-amount-paid').removeClass('hidden');
            $('label#remainingAmount').html(globals.calculateTotal() + lang[' L.E.']);
            globals.amountPaid = 0;
        } else {
            $('#amountPaid').prop("disabled", true);
            $('.input-amount-paid').addClass('hidden');
        }
    });

    $('#itemSerial:focus').focus();
    $('.btn-specs').popover('toggle');
    $(document).on('mouseenter' , '.btn-tooltip' , function(){
        $(this).tooltip('show');
    }).on('mouseleave' , '.btn-tooltip' , function(){
        $(this).tooltip('hide');
    });

    var total_pages = $('#total_pages').val();
    var current_page = $('#current_page').val();
    var options = {
        currentPage: current_page,
        totalPages: total_pages,
        useBootstrapTooltip:true,
        pageUrl: function(type, page, current){
            globals.currentPage = current;
            if(getQueryVariable('sort'))
                return "?page="+page+'&column='+getQueryVariable('column')+'&sort='+getQueryVariable('sort');
            else if($('#controller').val() === 'stock')
                return "#";
            else
                return "?page="+page;
        },
        itemTexts: function (type, page, current) {
            switch (type) {
            case "first":
                return lang["First"];
            case "prev":
                return lang["Previous"];
            case "next":
                return lang["Next"];
            case "last":
                return lang["Last"];
            case "page":
                return page;
            }
        },
        onPageClicked: function(e,originalEvent,type,page){
            var stockForm = $('#stockForm');
            postStockData(stockForm, page);
        }
    };
    
    $('#paginator, #paginator-ar, #paginator-search').bootstrapPaginator(options);
    
    function postStockData(stockForm, page){
        data = {
            stockSerial : $(stockForm).find('#stockSerial').val(),
            stockCategory : $(stockForm).find('#stockCategory').val(),
            stockSupplier : $(stockForm).find('#stockSupplier').val(),
            stockModel : $(stockForm).find('#stockModel').val(),
            stockStatus : $(stockForm).find('#stockStatus').val(),
            stockDateRange : $(stockForm).find('#stockDateRange').val(),
            stockFromDate : $(stockForm).find('#stockFromDate').val(),
            stockToDate : $(stockForm).find('#stockToDate').val(),
            stockLowestBuyPrice : $(stockForm).find('#stockLowestBuyPrice').val(),
            stockHighestBuyPrice : $(stockForm).find('#stockHighestBuyPrice').val(),
            stockLowestSellPrice : $(stockForm).find('#stockLowestSellPrice').val(),
            stockHighestSellPrice : $(stockForm).find('#stockHighestSellPrice').val(),
        };
        
        $.ajax({
            url: "/stock?page="+page,
            type: "post",
            data: data,
            success: function(response){
                document.open();
                document.write( response );
                document.close();
            }
        });
    };
    
    $('.customer-data #customerPhone').autocomplete({
	serviceUrl:'/customer/query',
	minChars:1,
	delimiter: /(,|;)\s*/, // regex or character
	maxHeight:400,
	//width:300,
	zIndex: 9999,
	deferRequestBy: 0, //miliseconds
	params: { param:'phone' }, //aditional parameters
	noCache: false, //default is false, set to true to disable caching
	onSelect: function(customer){$('#customerName').val(customer.data);}
    });
    
    $('.customer-data #customerName').autocomplete({
	serviceUrl:'/customer/query',
	minChars:1,
	delimiter: /(,|;)\s*/, // regex or character
	maxHeight:400,
	//width:300,
	zIndex: 9999,
	deferRequestBy: 0, //miliseconds
	params: { param:'name' }, //aditional parameters
	noCache: false, //default is false, set to true to disable caching
	onSelect: function(customer){$('#customerPhone').val(customer.data);}
    });
    
    $("#saleForm").submit(function(event){
        event.preventDefault();
    });
    
    //Checkout sale
    $('.btn-checkout').click(function(e){
        var checkoutForm = $('#saleForm');
        var btn = $(this);
        //e.preventDefault();
        
        if(validCheckout(checkoutForm)){
            $(checkoutForm).submit(function(event){
                
                // Stop form from submitting normally
                event.preventDefault();
                
                // Get some values from elements on the page:
                var $form = $( this ),
                    itemList = $form.find("input[name='itemList']").val(),
                    saleDiscount = $form.find( "input[name='saleDiscount']" ).val(),
                    paymentMethod = $form.find( "select[name='paymentMethod']" ).val(),
                    amountPaid = $form.find( "input[name='amountPaid']" ).val(),
                    customerPhone = $form.find( "input[name='customerPhone']" ).val(),
                    customerName = $form.find( "input[name='customerName']" ).val(),
                    action = $form.find("input[name='action']").val(),
                    controller = $form.find("input[name='controller']").val(),
                    url = $form.attr( "action" );
                    
                // Send the data using post
                btn.button('loading');
                var posting = $.post( url, {
                    itemList: itemList,
                    saleDiscount: saleDiscount,
                    paymentMethod: paymentMethod,
                    amountPaid: amountPaid,
                    customerPhone: customerPhone,
                    customerName: customerName,
                    action: action,
                    controller: controller,
                });
                
                // Put the results in a div
                posting.done(function( response ) {
                    //console.log(response);
                    $('a#bill-link').attr('href' , response.url).on('click' , function(event) {
                        event.preventDefault();
                        event.stopPropagation();
                        window.open(this.href, '_blank');
                    }).trigger('click');
                    window.location.reload();
                });
            });
        }
    });
    
    // Discount button click
    $('.btn-discount').click(function(){
        $('.discount-field').removeClass('hidden');
        $(this).addClass('hidden');
    });
    
    $('.btn-clear-item').click(function(){
        fullClearItem();
    });
    // Add item to list
    $('.btn-add-item').click(function(){
        var itemSerial = $('#itemSerial').val();
        var itemBrand = $('#itemBrand').html();
        if(itemSerial == ''){
            $('#alert-message').html(alertDangerMessage(lang['There is no item to be added.']));
            $('.serial-error').html(lang['Check item first.'])
            $('#itemSerial').focus();
        } else if(itemBrand == '') {
            $('#alert-message').html(alertInfoMessage(lang['Check item first.']));
            $('#itemSerial').focus();
        } else if(globals.itemList.length > 0){
            var flag = false;
            var itemIndex;
            $.each(globals.itemList , function(index , value){
                if(globals.item.m_model_item_has_serial){
                    if(value.i_item_serial===globals.item.i_item_serial){
                        flag = true;
                        itemIndex = index+1;
                        return false;
                    }
                } else {
                    if(value.m_model_serial===globals.item.m_model_serial) {
                        flag = true;
                        itemIndex = index+1;
                        return false;
                    }
                }
            });
        }
    });

    // Remove item from list
    $('.table-view-sale').on('click' , 'a.btn-remove-item' , function(){
        var itemSerial = $(this).closest('tr').children()[0].innerHTML;
        removeItem(itemSerial);
        $('#alert-message').html(alertInfoMessage(lang['Item removed from list.']));
        $(this).closest('tr').remove();
        
    }).on('change', 'input.item-sell-price', function(){
        var _self = $(this);
        var id = _self.data('id');
        var sellPrice = $(_self).val();
        
        if(!globals.isPositiveInteger(sellPrice)){
            $(_self).val(0);
            return false;
        }
        
        globals.itemListRequired = new Array();
        $.each(globals.itemList , function(index , item){
            if(item.m_model_item_has_serial){
                if(item.i_id == id)
                    globals.itemList[index].i_item_sell_price = sellPrice;
            } else {
                if(item.m_id == id)
                    globals.itemList[index].i_item_sell_price = sellPrice;
            }
            var requiredItem = {
                itemId: item.i_id ,
                sellPrice: item.i_item_sell_price
            };
            globals.itemListRequired.push(requiredItem);
        });
        $('#itemList').val(JSON.stringify(globals.itemListRequired));
        $('#subtotal').html(globals.calculateSubtotal() + lang[' L.E.']);
        $('#total').html(globals.calculateTotal() + lang[' L.E.']);
        
    }).on('change', 'input.item-quantity', function(){
        var _self = $(this);
        var modelId = _self.data('id');
        var quantity = $(_self).val();
        
        var sellPrice = $('.model-'+modelId).find('.item-sell-price').val();
        
        if(!globals.isPositiveInteger(quantity) || quantity == 0){
            $(_self).val(1);
            return false;
        } else if(quantity > globals.bulkList[modelId].length) {
            $(_self).val(globals.bulkList[modelId].length);
            return false;
        }
        
        for(var i=globals.itemList.length-1 ; i>=0 ; i--){
            if(globals.itemList[i].m_id == modelId){
                globals.itemList.splice(i , 1);
            }
        }
        for(var i=0 ; i<quantity ; i++){
            globals.bulkList[modelId][i].i_item_sell_price = sellPrice;
            globals.itemList.push(globals.bulkList[modelId][i]);
        }
        
        globals.itemListRequired = new Array();
        $.each(globals.itemList , function(index , item){
            var requiredItem = {
                itemId: item.i_id ,
                sellPrice: item.i_item_sell_price
            };
            globals.itemListRequired.push(requiredItem);
        });
        $('#itemList').val(JSON.stringify(globals.itemListRequired));
        $('#subtotal').html(globals.calculateSubtotal() + lang[' L.E.']);
        $('#total').html(globals.calculateTotal() + lang[' L.E.']);
        
    });
    
    $('.alert-serial-sale #itemSerial').keypress(function(e){
        e.preventDefault();
        if ( e.which == 13 ) 
            $('#item-serial-sale').click();
    })//.focusout(function(){
        //$('#item-serial-sale').click();
    //});
    
    // Load item details in sale page
    $('#item-serial-sale').click(function () {
        var itemSerial = $('#itemSerial').val();
        
        //Validate empty serial
        if(itemSerial === ''){
            $(".serial-error").html(lang['Serial can not be empty']);
            $('#itemSerial').closest('.form-group').removeClass('has-success').addClass('has-error');
            $('.alert-serial-sale').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
            $("#itemSerial").focus();
            return false;
        }
        
        //Validate letters, numbers and dashes
        if(!/^[a-zA-Z0-9-]+$/.test(itemSerial)){
            $(".serial-error").html(lang['Please enter a valid serial first.']);
            $('#itemSerial').closest('.form-group').removeClass('has-success').addClass('has-error');
            $('.alert-serial-sale').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
            $("#itemSerial").focus();
            return false;
        }
        
        var btn = $(this);
        btn.button('loading');
        $.ajax({
            url: "/item/get",
            type: "post",
            data: {serial:itemSerial},
            success: function(response){
                if(response.count){
                    globals.item = response.items[0];
                    globals.item.quantity = response.count;
                    if(!globals.item.m_model_item_has_serial)
                        globals.bulk = response.items;
                    /*
                    $('#itemBrand').html(globals.item.br_brand_name);
                    $('#itemModel').html(globals.item.m_model_name);
                    $('#itemCategory').html(globals.item.c_category_name);
                    $('#itemPrice').html(globals.item.b_bulk_price+lang[' L.E.']);
                    $('#alert-message').html('');
                    $(".serial-error").html(lang['Item is available.']);
                    $('#itemSerial').closest('.form-group').removeClass('has-error').addClass('has-success');
                    $('.alert-serial-sale').removeClass('alert-info').removeClass('alert-danger').addClass('alert-success');
                    */
                    if(globals.itemList.length > 0){
                        var flag = false;
                        var itemIndex;
                        $.each(globals.itemList , function(index , value){
                            if(globals.item.m_model_item_has_serial){
                                if(value.i_item_serial===globals.item.i_item_serial){
                                    flag = true;
                                    itemIndex = index+1;
                                    return false;
                                }
                            } else {
                                if(value.m_model_serial===globals.item.m_model_serial) {
                                    flag = true;
                                    itemIndex = index+1;
                                    return false;
                                }
                            }
                        });
                        if(flag === true){
                            $('#alert-message').html(alertInfoMessage(lang['Item already added to list.']));
                            //$(".table-view-sale tr:eq("+ itemIndex +")").effect("highlight", {}, 1500);
                            clearItem();
                        } else {
                            addItem();
                        }
                    } else {
                        addItem();
                    }
                     
                    /*if(!globals.item.m_model_item_has_serial){
                        $('.quantity-field').removeClass('hidden');
                        $('#bulkQuantity').html(lang[' of '] + response.count);
                        $('#bulk-qty').val(response.count);
                        globals.bulkQty = response.count;
                    } else {
                        $('.quantity-field').addClass('hidden');
                        $('#bulk-qty').val(0);
                    }*/
                    
                } else {
                    $('#alert-message').html('');
                    $(".serial-error").html(lang['Item is not found.']);
                    $('#itemSerial').closest('.form-group').removeClass('has-success').addClass('has-error');
                    $('.alert-serial-sale').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
                }
            }
        }).always(function () {
                btn.button('reset');
        });
    });
    
    // Check Model Serial
    $('#modelForm #check-model-serial').on('click', function () {
        return validateModelSerial();
    });
    
// Forms Validations
    $('#modelSerial').focus();
    $('#modelForm #modelSerial').keypress(function(e){
        if ( e.which == 13 ) return validateModelSerial();
    }).focusout(function(){
        return validateModelSerial();
    });
    
    //Validate Model
    var modelValidator = $('#modelForm').validate({
        submitHandler: function(form){
            if(globals.passedValidation)
                if(form.valid())
                    form.submit();
        },
        rules: {
            modelBrand: {
                required: true,
                alphanumericspace: true,
            },
            modelModel: {
                required: true,
                alphanumericspace: true,
            },
            modelSpecs: {
                alphanumericspace: true,
            },
            modelItemHasSerial: {
                required: true,
            },
        },
        highlight: function (element) {
            $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
        },
        success: function (element) {
            $(element).text(lang['ok']).addClass('valid')
                .closest('.form-group').removeClass('has-error').addClass('has-success');
        }
    });
    
    $('#modelBrand').keypress(function(){modelValidator.element('#modelBrand');});
    $('#modelModel').keypress(function(){modelValidator.element('#modelModel');});
    $('#modelCategory').change(function(){modelValidator.element('#modelCategory');});
    $('#modelSpecs').keypress(function(){modelValidator.element('#modelSpecs');});
    
    $("#modelForm").on("submit", function () {
        return validateModelSerial();
    });
    function validateModelSerial() {
        var action = $("#action").val();
        var controller = $("#controller").val();
        var modelSerial = $("#modelSerial").val();
        var modelBrand = $("#modelBrand").val();
        var modelModel = $("#modelModel").val();
        var modelCategory = $("#modelCategory").val();
        var modelSpecs = $("#modelSpecs").val();
        
        if(action === 'edit')
            var modelId = $("#modelId").val();
        else
            var modelId = null;
        
        //Validate empty serial
        if(modelSerial === ''){
            $(".serial-error").html(lang['Serial can not be empty']);
            $('#modelSerial').closest('.form-group').removeClass('has-success').addClass('has-error');
            $('.alert-serial-model').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
            $("#modelSerial").focus();
            return false;
            
        //Validate letters, numbers and dashes
        } else if(!/^[a-zA-Z0-9-]+$/.test(modelSerial)){
            $(".serial-error").html(lang['Please enter a valid serial first.']);
            $('#modelSerial').closest('.form-group').removeClass('has-success').addClass('has-error');
            $('.alert-serial-model').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
            $("#modelSerial").focus();
            return false;
        }
        
        $('#check-model-serial').button('loading');
        $.ajax({
            async: false,
            url: "/model/validate",
            type: "post",
            data: {
                modelId:modelId,
                modelSerial:modelSerial,
                modelBrand:modelBrand,
                modelModel:modelModel,
                modelCategory:modelCategory,
                modelSpecs:modelSpecs,
                action:action,
                controller:controller
            },
            success: function(response){
                // Do whatever check of the server data you need here.
                if(response.error === null) {  
                    // Good result, allow the submission
                    $('#alert-message').html('');
                    $(".serial-error").html(lang['Serial is valid.']);
                    $('#modelSerial').closest('.form-group').removeClass('has-error').addClass('has-success');
                    $('.alert-serial-model').removeClass('alert-info').removeClass('alert-danger').addClass('alert-success');
                    $('#modelBrand').focus();
                    globals.passedValidation = true;
                    
                    // Show an error message
                } else if(response.error === 'model_exists'){
                    $(".serial-error").html(lang['Model already exists.']);
                    $('#modelSerial').closest('.form-group').removeClass('has-success').addClass('has-error');
                    $('.alert-serial-model').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
                    $("#modelSerial").focus();
                }
            }
        }).always(function () {
            $('#check-model-serial').button('reset');
        });
        return globals.passedValidation;
    }
    
    //Quantity not less than 1
    $.validator.addMethod("greaterThan", function(value, element, param) {
        return this.optional(element) || value > param;
    }, lang["Quantity must be greater than zero."]);
    
    //Validate Item    
    $(document).on('click', '.btn-item-edit' , function () {
        globals.itemId = $(this).children()[0].value;
        var btn = $(this);
        var data = {itemId:globals.itemId};
        btn.button('loading');
        $('#editModalContainer').load("/item/edit" , data , function(){
            btn.button('reset');
            $('#editModal').modal('show');
            $('#editModal').on('shown.bs.modal' , function(){
                $('#editModal #itemSerial').focus();
            });
        });
    });
    
    //Validate Category
    $('#categoryForm input').focusout(function(e){
        if(!validateCategory($(this))){
            e.preventDefault();
            $(this).focus();
        }
    });
    
    $('#categoryForm').submit(function(e){
        var element = $('#categoryForm input');
        if(!validateCategory(element)){
            e.preventDefault();
            $(element).focus();
        }
    });
    
    //Validate Category Name
    function validateCategory(element){
        var categoryName = $('#categoryName').val();
        var categoryId = $('#categoryId').val();
        var action = $('#action').val();
        var controller = $('#controller').val();
        var passedValidation = false;
        //Validate empty category name
        if(categoryName === ''){
            $(".category-error").html(lang['Category name can not be empty.']);
            $(element).closest('.form-group').removeClass('has-success').addClass('has-error').focus();
            return passedValidation;
            
        //Validate letters, numbers and dashes
        } else if(!/^[a-z0-9\u0600-\u06FF\s]+$/i.test(categoryName)){
            $(".category-error").html(lang['Please enter a valid name.']);
            $(element).closest('.form-group').removeClass('has-success').addClass('has-error').focus();
            return passedValidation;
        }
        
        var btn = $('.btn-category-add');
        btn.button('loading');
        $.ajax({
            url: "/category/validate",
            type: "post",
            async: false,
            data: {
                categoryName:categoryName,
                categoryId:categoryId,
                action:action,
                controller:controller
            },
            success: function(response){
                if(response.error==='category_exists'){
                    $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                    $(".category-error").html(lang['Category already exists.'] );
                    
                } else if (response.error==='not_found'){
                    $(element).text(lang['ok']).addClass('valid')
                        .closest('.form-group').removeClass('has-error').addClass('has-success');
                    $(".category-error").html(lang['Category name is valid']);
                    passedValidation = true;
                }
            }
        }).always(function () {
            btn.button('reset');
        });
        return passedValidation;
    }
    
    //Validate Customer
    $('#customerForm #customerPhone').keyup(function(e){
        if(!globals.isNumber($(this))){
            $(this).next(".customer-error").html(lang['Customer phone can not be empty']);
            $(this).closest('.form-group').removeClass('has-success').addClass('has-error');
        } else {
            $(this).next(".customer-error").html(lang['ok']).removeClass('hidden');
            $(this).closest('.form-group').removeClass('has-error').addClass('has-success');
        }
    });
    
    $('#customerForm #customerName').change(function(e){
        if(!globals.isAlphanumericSpace($(this))){
            $(this).next(".customer-error").html(lang['Please enter a valid name']).removeClass('hidden');
            $(this).closest('.form-group').removeClass('has-success').addClass('has-error');
        } else {
            $(this).next(".customer-error").html(lang['ok']).removeClass('hidden');
            $(this).closest('.form-group').removeClass('has-error').addClass('has-success');
        }
    });
    
    $('#customerForm').submit(function(e){
        var form = $(this);
        if(!validateCustomer(form)){
            e.preventDefault();
        }
    });
    
    //Validate Customer Phone & Name
    function validateCustomer(customerForm){
        var customerPhone = $(customerForm).find('#customerPhone');
        var customerName = $(customerForm).find('#customerName');
        var customerId = $(customerForm).find('#customerId').val();
        var action = $('#action').val();
        var controller = $('#controller').val();
        var passedValidation = false;
        
        //Validate empty customer phone
        if(globals.isEmpty(customerPhone)){
            $(customerPhone).next(".customer-error").html(lang['Customer phone can not be empty']);
            $(customerPhone).closest('.form-group').removeClass('has-success').addClass('has-error').focus();
            return passedValidation;
        } else if(!globals.isNumber(customerPhone)){
            $(customerPhone).next(".customer-error").html(lang['Please enter a valid phone number']).removeClass('hidden');
            $(customerPhone).closest('.form-group').removeClass('has-success').addClass('has-error');
            return passedValidation;
        } else {
            var btn = $('.btn-customer-add');
            btn.button('loading');
            $.ajax({
                url: "/customer/validate",
                type: "post",
                async: false,
                data: {
                    customerPhone:$(customerPhone).val(),
                    //customerName:customerName,
                    customerId:customerId,
                    action:action,
                    controller:controller
                },
                success: function(response){
                    if(response.error==='customer_exists'){
                        $(customerPhone).next(".customer-error").html(lang['Customer already exists'] );
                        $(customerPhone).closest('.form-group').removeClass('has-success').addClass('has-error');
                        
                    } else if (response.error==='not_found'){
                        $(customerPhone).next(".customer-error").html(lang['Customer name is valid']);
                        $(customerPhone).closest('.form-group').removeClass('has-error').addClass('has-success');
                        passedValidation = true;
                    }
                }
            }).always(function () {
                btn.button('reset');
            });
            return passedValidation;
        }
        
        //Validate empty customer name
        if(globals.isEmpty(customerName)){
            $(customerName).next(".customer-error").html(lang['Customer name can not be empty']);
            $(customerName).closest('.form-group').removeClass('has-success').addClass('has-error').focus();
            return passedValidation;
        } else if(!globals.isAlphanumericSpace(customerName)){
            $(customerName).next(".customer-error").html(lang['Please enter a valid name']).removeClass('hidden');
            $(customerName).closest('.form-group').removeClass('has-success').addClass('has-error');
            return passedValidation;
        } else {
            //$(customerName).next(".customer-error").html(lang['ok']).removeClass('hidden');
            $(customerName).closest('.form-group').removeClass('has-error').addClass('has-success');
        }
        
    }
    
    //Validate Warranty
    $('#warrantyForm input').focusout(function(e){
        if(!validateWarranty($(this))){
            e.preventDefault();
            $(this).focus();
        }
    });
    
    $('#warrantyForm').submit(function(e){
        var element = $('#warrantyForm input');
        if(!validateWarranty(element)){
            e.preventDefault();
            element.focus();
        }
    });

    //Validate Warranty Name
    function validateWarranty(element){
        var warrantyName = $('#warrantyName').val();
        var warrantyId = $('#warrantyId').val();
        var action = $('#action').val();
        var controller = $('#controller').val();
        var passedValidation = false;
        
        //Validate empty warranty name
        if(warrantyName === ''){
            $(".warranty-error").html(lang['Warranty name can not be empty.']);
            $(element).closest('.form-group').removeClass('has-success').addClass('has-error').focus();
            return passedValidation;
            
        //Validate letters, numbers and dashes
        } else if(!/^[a-z0-9\u0600-\u06FF\s]+$/i.test(warrantyName)){
            $(".warranty-error").html(lang['Please enter a valid name.']);
            $(element).closest('.form-group').removeClass('has-success').addClass('has-error').focus();
            return passedValidation;
        }
        
        var btn = $('.btn-warranty-add');
        btn.button('loading');
        $.ajax({
            url: "/warranty/validate",
            type: "post",
            async: false,
            data: {
                warrantyName:warrantyName,
                warrantyId:warrantyId,
                action:action,
                controller:controller
            },
            success: function(response){
                if(response.error==='warranty_exists'){
                    $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                    $(".warranty-error").html(lang['Warranty already exists.'] );
                    
                } else if (response.error==='not_found'){
                    $(element).text(lang['ok']).addClass('valid')
                        .closest('.form-group').removeClass('has-error').addClass('has-success');
                    $(".warranty-error").html(lang['Warranty name is valid']);
                    passedValidation = true;
                }
            }
        }).always(function () {
            btn.button('reset');
        });
        return passedValidation;
    }
    
    //Validate Alphanumeric with Space and Arabic Characters
    $.validator.addMethod("alphanumericspace", function(value, element) {
        return this.optional(element) || /^[a-z0-9\u0600-\u06FF\-\s]+$/i.test(value);
    }, lang["The field must contain only letters, numbers, or dashes."]);
    
    $.validator.addMethod("alphanumericspacecomma", function(value, element) {
        return this.optional(element) || /^[a-z0-9\u0600-\u06FF\-,\s]+$/i.test(value);
    }, lang["The field must contain only letters, numbers, dashes or commas."]);

    //Validate Date to YYYY-MM-DD format
    $.validator.addMethod("regexdate", function(value, element) {          
        return this.optional(element) || /^(19|20)\d\d[\-\/.](0[1-9]|1[012])[\-\/.](0[1-9]|[12][0-9]|3[01])$/.test(value);
    }, lang["Please enter a valid date. (e.g. 2014-02-16)"]);

    //Validate Supplier
    var supplierValidator = $('#supplierForm').validate({
            submitHandler: function(form){
                form.submit();
            },
            rules: {
                supplierName: {
                    required: true,
                    alphanumericspace: true,        
                },
                supplierPhone: {
                    required: true,
                    minlength: 2,
                    digits: true,
                },
                supplierEmail: {
                    //required: true,
                    email: true,
                },
                supplierAddress: {
                    required: true,
                    minlength: 2,
                    //alphanumericspace: true,
                },
            },
            highlight: function (element) {
                $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
            },
            success: function (element) {
                $(element).text(lang['ok']).addClass('valid')
                    .closest('.form-group').removeClass('has-error').addClass('has-success');
            }
    });
    $('input#supplierName').keypress(function(){supplierValidator.element('#supplierName');});
    $('input#supplierPhone').keypress(function(){supplierValidator.element('#supplierPhone');});
    $('input#supplierEmail').keypress(function(){supplierValidator.element('#supplierEmail');});
    $('input#supplierGovernorate').keypress(function(){supplierValidator.element('#supplierGovernorate');});
    $('input#supplierAddress').keypress(function(){supplierValidator.element('#supplierAddress');});

    // View Report
    $('#viewReport').click(function(e){
        e.preventDefault();
        
        var reportCategory = $('#reportCategory').val(),
            reportType = $('#reportType').val(),
            reportPayment = $('#reportPayment').val(),
            reportStatus = $('#reportStatus').val(),
            reportRange = $('#reportRange').val(),
            reportFromDate = $('#reportFromDate').val(),
            reportToDate = $('#reportToDate').val(),
            action = $('#action').val(),
            controller = $('#controller').val();
        
        var models = new Array(), 
            suppliers = new Array(), 
            customers = new Array();
        if(reportType === 'stock' || reportType === 'sales'){
            $.each($('div#reportModel input:checked') , function(index, value){
                if(value.value)
                    models.push(value.value);
            });
        } else if (reportType === 'suppliers') {
            $.each($('div#reportSupplier input:checked') , function(index, value){
                if(value.value)
                    suppliers.push(value.value);
            });
        } else if (reportType === 'customers') {
            $.each($('div#reportCustomer input:checked') , function(index, value){
                if(value.value)
                    customers.push(value.value);
            })
        }
        
        var reportModel = models;
        var reportSupplier = suppliers;
        var reportCustomer = customers;
        
        var isValid = true;
        if(reportModel.length === 0 && reportPayment != 'amount' && reportType != 'suppliers' && reportType != 'customers'){
            $('.report-model-error').html(lang['Please select model']);
            $('div#reportModel').closest('.form-group').removeClass('has-success').addClass('has-error');
            isValid = false;
        } else {
            $('.report-model-error').html('');
            $('div#reportModel').closest('.form-group').removeClass('has-error');
        }
        
        if(reportSupplier.length === 0 && reportType === 'suppliers'){
            $('.report-supplier-error').html(lang['Please select supplier']);
            $('div#reportSupplier').closest('.form-group').removeClass('has-success').addClass('has-error');
            isValid = false;
        } else {
            $('.report-supplier-error').html('');
            $('div#reportSupplier').closest('.form-group').removeClass('has-error');
        }
        
        if(reportCustomer.length === 0 && reportType === 'customers'){
            $('.report-customer-error').html(lang['Please select customer']);
            $('div#reportCustomer').closest('.form-group').removeClass('has-success').addClass('has-error');
            isValid = false;
        } else {
            $('.report-customer-error').html('');
            $('div#reportCustomer').closest('.form-group').removeClass('has-error');
        }
        
        if(!$('#reportRange').prop('disabled') && reportRange === ''){
            $('.report-range-error').html(lang['Please select period.']);
            $('#reportRange').closest('.form-group').removeClass('has-success').addClass('has-error');
            isValid = false;
        } else {
            $('.report-range-error').html('');
            $('#reportRange').closest('.form-group').removeClass('has-error');
        }
        
        if(!isValid) return isValid;
        
        var url = '/report/view';
        var btn = $(this);
        btn.button('loading');
        $.ajax({
            //async: false,
            url: url,
            type: "post",
            data: {
                reportType:reportType,
                reportPayment:reportPayment,
                reportModel:reportModel,
                reportSupplier:reportSupplier,
                reportCustomer:reportCustomer,
                reportCategory:reportCategory,
                reportStatus:reportStatus,
                reportRange:reportRange,
                reportFromDate:reportFromDate,
                reportToDate:reportToDate,
                action:action,
                controller:controller,
            },
            success: function(response){
                $('.report-view').html(response);
            }
        }).always(function () {
            btn.button('reset');
            $(".redund-cell").empty();
        });
    });
    
    $("#reportPage").on("click", "#exportReportPDF", function(e){
        var models = new Array();
        $.each($('div#reportModel input:checked') , function(index, value){
            if(value.value)
                models.push(value.value);
        })
        var reportModel = models;
        var reportCategory = $('#reportCategory ').val();
        var reportStatus = $('#reportStatus').val();
        var reportSupplier = $('#reportSupplier').val();
        var reportFromDate = $('#reportFromDate').val();
        var reportToDate = $('#reportToDate').val();
        var reportDisplay = $('#reportDisplay').val();
        var action = $('#action').val();
        var controller = $('#controller').val();
        
        var btn = $(this);
        btn.button('loading');
        $.ajax({
            async: false,
            url: "/report/export",
            type: "post",
            data: {
                reportModel:reportModel,
                reportCategory:reportCategory,
                reportStatus:reportStatus,
                reportSupplier:reportSupplier,
                reportFromDate:reportFromDate,
                reportToDate:reportToDate,
                reportDisplay:reportDisplay,
                action:action,
                controller:controller
            },
            success: function(response){
                
            }
        }).always(function () {
            btn.button('reset');
            $(".redund-cell").empty();
        });
        e.preventDefault();
    });
    
    $("#reportPage").on("click", "#printReport", function(e){
        $("#reportForm").submit();
        e.preventDefault();
    });

    function returnData(data){
        return data;
    }

    function alertSuccessMessage(message){
        alertBox =  '<div class="alert alert-success">';
        alertBox += '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
        alertBox += message;
        alertBox += '</div>';
        return alertBox;
    }

    function alertWarningMessage(message){
        alertBox =  '<div class="alert alert-warning">';
        alertBox += '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
        alertBox += message;
        alertBox += '</div>';
        return alertBox;
    }

    function alertInfoMessage(message){
        alertBox =  '<div class="alert alert-info">';
        alertBox += '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
        alertBox += message;
        alertBox += '</div>';
        return alertBox;
    }

    function alertDangerMessage(message){
        alertBox =  '<div class="alert alert-danger">';
        alertBox += '<button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>';
        alertBox += message;
        alertBox += '</div>';
        return alertBox;
    }
    function addItem(){
        var displayedSerial;
        //var itemQuantity = $('#itemQuantity').val();
        //globals.item['s_sale_quantity'] = itemQuantity;
        
        if(globals.item.m_model_item_has_serial)
            displayedSerial = globals.item.i_item_serial;
        else
            displayedSerial = globals.item.m_model_serial;
        
        if(globals.item.m_model_item_has_serial)
            globals.itemList.push(globals.item);
        else {
            globals.itemList.push(globals.bulk[0]);
            globals.bulkList[globals.bulk[0].m_id] = globals.bulk;
        }
        //console.log(globals.itemList)     
        var tableRow =  "<tr class='model-" + globals.item.m_id + "'>" +
                            "<td>" + displayedSerial + "</td>" +
                            "<td>" + globals.item.br_brand_name + ' '
                                   + globals.item.m_model_name + ' '
                                   + globals.item.co_color_name + ' '
                                   + globals.item.m_model_number +
                            "</td>"+
                            "<td>" + globals.item.c_category_name + "</td>" +
                            "<td>" +
                                     (globals.item.m_model_item_has_serial ? globals.item.quantity : "<input type='number' class='form-control text-center item-quantity' data-id='" + globals.item.m_id + "' value='1'/>") +
                            "</td>"+
                            "<td>" + '<label class="average-buy-price">' + globals.item.i_item_average_price + '</label>'
                                   + '<input type="hidden" class="item-buy-price" value="'+ globals.item.i_item_average_price +'">' +
                            "</td>"+
                            "<td><input type='number' class='form-control text-center item-sell-price' data-id='" + (globals.item.m_model_item_has_serial ? globals.item.i_id : globals.item.m_id) + "' value='" + globals.item.i_item_sell_price + "'/></td>" +
                            "<td>" +
                                "<a class='btn btn-sm btn-danger btn-remove-item'>"+ lang['Remove'] +"</a>" +
                            "</td>"+
                        "</tr>";
        $('.table-view-sale .table-footer').before(tableRow);
        /*if ($(".table-view-sale tbody tr").length > 0)
            $(".table-view-sale").tablesorter({
                widgets        : ['zebra', 'columns'],
                usNumberFormat : false,
                sortReset      : true,
                sortRestart    : true
            });*/
        $('.table-empty-row').addClass('hidden');
        clearItem();
        $('#alert-message').html(alertSuccessMessage(lang['Item added to list.']));
        globals.itemListRequired = new Array();
        $.each(globals.itemList , function (index , value){
            var requiredItem = {
                itemId: value.i_id ,
                sellPrice: value.i_item_sell_price 
            };
            globals.itemListRequired.push(requiredItem);
            $('#itemList').val(JSON.stringify(globals.itemListRequired));
        });
        globals.item = null;
        
        $('#subtotal').html(globals.calculateSubtotal() + lang[' L.E.']);
        $('#total').html(globals.calculateTotal() + lang[' L.E.']);
        //console.log(globals.itemListRequired);
        
        //Calculate Buy Price Average
        /*
        var models = new Array();
        $.each(globals.itemList , function (index , item){
            if($.inArray(item.m_id , models) < 0)
                models.push(item.m_id);
        });
        $.each(models , function(index , value){
            var priceSum = 0;
            var length = $('tr.model-'+value+' .item-buy-price').length;
            $('tr.model-'+value+' .item-buy-price').each(function(index){
                priceSum += parseInt($(this).val());
            });
            $('tr.model-'+value+' label.average-buy-price').html(priceSum / length);
        });*/
    }
    
    function removeItem(itemSerial){
        
        for(var i=globals.itemList.length-1 ; i>=0 ; i--){
            if(globals.itemList[i].m_model_item_has_serial){
                if(globals.itemList[i].i_item_serial===itemSerial){
                    globals.itemList.splice(i , 1);
                }
            } else {
                if(globals.itemList[i].m_model_serial===itemSerial){
                    globals.itemList.splice(i , 1);
                }
            }
        }
        
        //console.log(globals.itemList);
        globals.itemListRequired = new Array();
        $.each(globals.itemList , function (index, value){
            var requiredItem = {
                itemId: value.i_id , 
                itemDiscount: value.s_sale_discount , 
            };
            globals.itemListRequired.push(requiredItem);
        });
        $('#itemList').val(JSON.stringify(globals.itemListRequired));
        if (globals.itemList.length === 0)
            $('.table-empty-row').removeClass('hidden');
    }
    function clearItem(){
        $('#itemSerial').val('').closest('.form-group').removeClass('has-success');
        $('.serial-error').empty();
        $('#itemBrand').empty();
        $('#itemModel').empty();
        $('#itemCategory').empty();
        $('#itemPrice').empty();
        $('#itemQuantity').val(1);
        $('.quantity-field').addClass('hidden');
        $('.alert-serial-sale').removeClass('alert-success').addClass('alert-info');
    }
    function fullClearItem(){
        globals.item = null;
        $('#itemSerial').val('').closest('.form-group').removeClass('has-error').removeClass('has-success');
        $('.serial-error').empty();
        $('#itemBrand').empty();
        $('#itemModel').empty();
        $('#itemCategory').empty();
        $('#itemPrice').empty();
        $('#itemQuantity').val(1);
        $('.quantity-field').addClass('hidden');
        $('.alert-serial-sale').removeClass('alert-success').removeClass('alert-danger').addClass('alert-info');
    }
    globals.calculateSubtotal = function(){
        globals.subtotal = 0;
        $.each(globals.itemList , function (index, value){
            globals.subtotal += parseInt(value.i_item_sell_price);
        });
        return globals.subtotal;
    }
    globals.calculateTotal = function(){
        globals.total = globals.subtotal-globals.discount;
        return globals.total;
    }
    function validCheckout(form){
        var isValid = true;
        
        var $form = $( form ),
            //itemList = $form.find("input[name='itemList']").val(),
            saleDiscount = $form.find( "input[name='saleDiscount']" ).val(),
            paymentMethod = $form.find( "select[name='paymentMethod']" ).val(),
            amountPaid = $form.find( "input[name='amountPaid']" ).val(),
            customerPhone = $form.find( "input[name='customerPhone']" ).val(),
            customerName = $form.find( "input[name='customerName']" ).val();
        
        if(globals.itemList.length > 0){
            if( !globals.isPositiveInteger(globals.discount) || globals.discount>globals.subtotal || globals.discount<0 ){
                isValid = false;
                $('#alert-message').html(alertDangerMessage(lang['Discount is invalid.']));
                $('#saleDiscount').focus();
                //e.preventDefault();
            }
            
            if(paymentMethod === 'postpaid'){
                globals.amountPaid = $('#amountPaid').val();
                if( !globals.isPositiveInteger(globals.amountPaid) || globals.amountPaid>=globals.total || globals.amountPaid<0){
                    isValid = false;
                    $('#alert-message').html(alertDangerMessage(lang['Amount Paid is invalid.']));
                    $('#amountPaid').focus();
                    //e.preventDefault();
                }
            }
            
            if(globals.isEmpty(customerName) || globals.isEmpty(customerPhone)){
                isValid = false;
                $('#alert-message').html(alertDangerMessage(lang['Customer is invalid']));
            }
            
            //alert(isValid);
        } else {
            $('#alert-message').html(alertDangerMessage(lang['The list is empty, Please check items to list.']));
            $('#itemSerial').focus();
            //e.preventDefault();
            isValid = false;
        }
        return isValid;
    }
    
    jQuery.fn.multiselect = function() {
        $(this).each(function() {
            var checkboxes = $(this).find("input:checkbox");
            checkboxes.each(function() {
                var checkbox = $(this);
                // Highlight pre-selected checkboxes
                if (checkbox.prop("checked"))
                    checkbox.parent().addClass("multiselect-on");

                // Highlight checkboxes that the user selects
                checkbox.click(function() {
                    if (checkbox.prop("checked"))
                        checkbox.parent().addClass("multiselect-on");
                    else
                        checkbox.parent().removeClass("multiselect-on");
                });
            });
        });
    };
    $(".multiselect").multiselect();
    $(".multiselect input.checkall").change(function(){
        var multiselect = $(this).closest('.multiselect');
        if($(this).prop('checked')){
            $(multiselect).find("input").prop('checked', true);
            $(multiselect).find("label").addClass("multiselect-on");
            $(multiselect).find("label.hidden input").prop('checked', false);
            $(multiselect).find("label.hidden").removeClass("multiselect-on");
        }else{
            $(multiselect).find("input").prop('checked', false);
            $(multiselect).find("label").removeClass("multiselect-on");
        }
    });
    $(".multiselect input").change(function(){
        //var multiselect = $(this).closest('.multiselect');
        if(!$(this).hasClass('checkall')){
            if(!$(this).prop('checked')){
                $(".multiselect input.checkall").prop('checked', false);
                $(".multiselect label.checkall").removeClass("multiselect-on");
            }
        }
    });
    
    $("select#reportCategory, select#filterCategory").change(function(){
        if($(this).val()){
            $(".multiselect label").addClass("hidden").removeClass("multiselect-on");
            $(".multiselect input").prop('checked', false);
            $(".checkall").removeClass("hidden");
            $(".category-"+this.value).removeClass("hidden");
        } else {
            $(".multiselect input").prop('checked', false);
            $(".multiselect label").removeClass("hidden").removeClass("multiselect-on");
        }
    });
    $("select#reportType").change(function(){
        if($(this).val() === "stock"){
            $(".report-status, .report-category, .report-model").removeClass("hidden");
            $(".report-status select, .report-category select").prop("disabled", false);
            $(".report-supplier, .report-customer, .report-payment, .report-range, .report-from-date, .report-to-date").addClass("hidden");
            $(".report-payment select, .report-range select, .report-from-date select, .report-to-date select").prop("disabled", true);
            //Category & Models
            $("select#reportCategory").prop("disabled" , false);
            $(".report-model .multiselect input").prop('disabled', false);
            $(".report-supplier .multiselect input, .report-customer .multiselect input").prop('disabled', true);
            $('.multiselect').css('background-color', '#fff');
            $("select#reportPayment").val('prepaid');
            
        } else if($(this).val() === "sales") {
            $(".report-payment, .report-range, .report-category, .report-model").removeClass("hidden");
            $(".report-payment select, .report-range select, .report-category select").prop("disabled", false);
            $(".report-status, .report-from-date, .report-to-date, .report-supplier, .report-customer").addClass("hidden");
            $(".report-status select, .report-from-date select, .report-to-date select").prop("disabled", true);
            $(".report-model .multiselect input").prop('disabled', false);
            $(".report-supplier .multiselect input, .report-customer .multiselect input").prop('disabled', true);
            $('.multiselect').css('background-color', '#fff');
            
        } else if($(this).val() === "suppliers") {
            $(".report-range, .report-supplier").removeClass("hidden");
            $(".report-range select").prop("disabled", false);
            $(".report-status, .report-range, .report-from-date, .report-to-date, .report-category, .report-model, .report-customer, .report-payment").addClass("hidden");
            $(".report-status select, .report-range select, .report-from-date select, .report-to-date select, .report-category select, .report-payment select").prop("disabled", true);
            $(".report-supplier .multiselect input").prop('disabled', false);
            $(".report-model .multiselect input, .report-customer .multiselect input").prop('disabled', true);
            $('.multiselect').css('background-color', '#fff');
            
        } else if($(this).val() === "customers") {
            $(".report-range, .report-customer").removeClass("hidden");
            $(".report-range select").prop("disabled", false);
            $(".report-status, .report-range, .report-from-date, .report-to-date, .report-category, .report-model, .report-supplier, .report-payment").addClass("hidden");
            $(".report-status select, .report-range select, .report-from-date select, .report-to-date select, .report-category select, .report-payment select").prop("disabled", true);
            $(".report-customer .multiselect input").prop('disabled', false);
            $(".report-model .multiselect input, .report-supplier .multiselect input").prop('disabled', true);
            $('.multiselect').css('background-color', '#fff');
        }
    });
    $("select#reportRange").change(function(){
        if($(this).val() === "range"){
            $(".report-from-date, .report-to-date").removeClass("hidden");
            $(".report-from-date input, .report-to-date input").prop("disabled", false);
        } else {
            $(".report-from-date, .report-to-date").addClass("hidden");
            $(".report-from-date input, .report-to-date input").prop("disabled", true);
        }
    });
    $("select#stockDateRange").change(function(){
        if($(this).val() === "range"){
            $(".stock-from-date, .stock-to-date").removeClass("hidden");
            $(".stock-from-date input, .stock-to-date input").prop("disabled", false);
        } else {
            $(".stock-from-date, .stock-to-date").addClass("hidden");
            $(".stock-from-date input, .stock-to-date input").prop("disabled", true);
        }
    });
    $("select#filterRange").change(function(){
        if($(this).val() === "range"){
            $(".filter-from-date, .filter-to-date").removeClass("hidden");
            $(".filter-from-date input, .filter-to-date input").prop("disabled", false);
        } else {
            $(".filter-from-date, .filter-to-date").addClass("hidden");
            $(".filter-from-date input, .filter-to-date input").prop("disabled", true);
        }
    });
    $("select#reportPayment").change(function(){
        if($(this).val() === "amount"){
            $("select#reportCategory").prop("disabled" , true);
            $(".multiselect input").prop('disabled', true);
            $(".multiselect input").prop('checked', false);
            $(".multiselect label").removeClass("multiselect-on");
            $('.multiselect').css('background-color', '#eee');
        } else {
            $("select#reportCategory").prop("disabled" , false);
            $(".multiselect input").prop('disabled', false);
            $('.multiselect').css('background-color', '#fff');
        }
    });
    function getQueryVariable(variable)
    {
        var query = window.location.search.substring(1);
        var vars = query.split("&");
        for (var i=0;i<vars.length;i++) {
                var pair = vars[i].split("=");
                if(pair[0] == variable){return pair[1];}
        }
        return(false);
    }
        
    $('.number-of-bulks-modal').on('click', function(){
        var _self = $(this);
        var entityId = _self.data('id');
        $('#numberOfBulks #entityId').val(entityId);
        $('#numberOfBulks').modal('show');
        $('#numberOfBulks').on('shown.bs.modal', function(){
                $('#numberOfModels').focus();
        });
    });
    
    // Bulk Wizard
    $(document).on('click', '.btn-bulk-wizard', function(e){
        e.preventDefault();
        $('#numberOfBulks').modal('hide');
        var numberOfBulks = $('#numberOfModels').val();
        var supplierId = $('#numberOfBulks #entityId').val();
        var btn = $(this);
        var data = {supplierId:supplierId, numberOfBulks:numberOfBulks};
        btn.button('loading');
        $('#bulkWizardModalContainer').load("/bulk/wizard" , data , function(){
            btn.button('reset');
            $('#bulkWizardModal').modal('show').on('shown.bs.modal', function(){
                $('.tab-pane.active').find('#bulkSerial').focus();
            }).on('hidden.bs.modal', function(){
                globals.transaction = {bulks:new Array() , info:null};
            });
            $('#tabsleft').bootstrapWizard({
                'tabClass': 'nav nav-tabs', 
                'debug': false,
                onInit: function(tab, navigation, index) {
                    
                }, onShow: function(tab, navigation, index) {
                    $('.tab-pane.active').find('#bulkSerial').focus();
                    
                }, onNext: function(tab, navigation, index) {
                    var bulk = $('.tab-pane.active');
                    if(!isValidBulk(bulk)) 
                        return false;
                    else if(!isDuplicatedModel($(bulk).find('#bulkSerial').val() , $(bulk).find('bulkNumber').val())){
                        var bulkObject = {};
                        bulkObject.model = $(bulk).find('#modelId').val();
                        bulkObject.serial = $(bulk).find('#bulkSerial').val();
                        bulkObject.buyPrice = $(bulk).find('#bulkBuyPrice').val();
                        bulkObject.sellPrice = $(bulk).find('#bulkSellPrice').val();
                        bulkObject.quantity = $(bulk).find('#bulkQuantity').val();
                        bulkObject.itemHasSerial = $(bulk).find('#bulkItemHasSerial').val();
                        globals.transaction.bulks.push( bulkObject );
                    }
                    
                    var totalDue = 0;
                    $.each(globals.transaction.bulks, function(index, bulk){
                        totalDue += bulk.buyPrice * bulk.quantity;
                    });
                    $('.tab-pane.transaction-info').find('#transactionTotalDue').val(totalDue);
                    $('.tab-pane.transaction-info').find('.transaction-total-due').html(totalDue.toString() + lang[' L.E.']);
                    
                }, onPrevious: function(tab, navigation, index) {
                    //console.log('Tab '+index+' - onPrevious');
                    
                }, onLast: function(tab, navigation, index) {
                    if(!isValidBulk($('.tab-pane.active'))) return false;
                    
                }, onTabClick: function(tab, navigation, index) {
                    return false;
                    
                }, onTabShow: function(tab, navigation, index) {
                    $('.tab-pane.active').find('#bulkSerial').focus();
                    var $total = navigation.find('li').length;
                    var $current = index+1;
                    var $percent = ($current/$total) * 100;
                    $('.bulk-wizard').find('.progress-bar').css({width:$percent+'%'});
                    // If it's the last tab then hide the last button and show the finish instead
                    if($current >= $total) {
                        $('.transaction-info #transactionDiscount').focus();
                        $('#tabsleft').find('.pager .next').hide();
                        $('#tabsleft').find('.pager .finish').show();
                        $('#tabsleft').find('.pager .finish').removeClass('disabled');
                    } else {
                        $('#tabsleft').find('.pager .next').show();
                        $('#tabsleft').find('.pager .finish').hide();
                    }
                }
            });
            
            $('#tabsleft .finish').click(function() {
                var transactionInfo = {};
                transactionInfo.supplier = $('.tab-pane.active').find('#transactionSupplier').val();
                transactionInfo.totalDue = $('.tab-pane.active').find('#transactionTotalDue').val();
                transactionInfo.discount = $('.tab-pane.active').find('#transactionDiscount').val();
                transactionInfo.paidAmount = $('.tab-pane.active').find('#transactionPaidAmount').val();
                transactionInfo.date = $('.tab-pane.active').find('#transactionDate').val();
                globals.transaction.info = transactionInfo;
                //console.log(globals.transaction);
                
                var transactionJSON = JSON.stringify(globals.transaction);
                
                var btn = $(this);
                btn.button('loading');
                $.ajax({
                    async: false,
                    url: "/bulk/add",
                    type: "post",
                    data: { transaction: transactionJSON },
                    success: function(response){
                        globals.transaction.info.id = response.transactionId;
                        //$('#bulkWizardModal').modal('hide');
                        $('.btn-item-wizard').click();
                        //console.log(globals.transaction);
                    }
                }).always(function () {
                    $('#itemWizardModal').on('load', function(){
                        //itemWizard(btn);
                    });
                    
                    //btn.button('reset');
                });
            });
            
            $('.tab-pane #transactionDate').datetimepicker({
                pickTime: false,
                language: 'ar'
            });
        });
        
    }).on('click', '.btn-trans-edit-wizard', function(e){
        e.preventDefault();
        var _self = $(this);
        var transactionId = _self.data('id');
        var btn = $(this);
        var data = {transactionId:transactionId};
        btn.button('loading');
        $('#transactionWizardModalContainer').load("/transaction/wizard/"+transactionId , function(){
            btn.button('reset');
            $('#bulkWizardModal').modal('show').on('shown.bs.modal', function(){
                $('.tab-pane.active').find('#transactionDiscount').focus();
            });
            $('#tabsleft').bootstrapWizard({
                'tabClass': 'nav nav-tabs', 
                'debug': false,
                onInit: function(tab, navigation, index) {
                    
                }, onShow: function(tab, navigation, index) {
                    $('.tab-pane.active').find('#transactionDiscount').focus();
                    
                }, onNext: function(tab, navigation, index) {
                    
                }, onPrevious: function(tab, navigation, index) {
                    
                }, onLast: function(tab, navigation, index) {
                    
                }, onTabClick: function(tab, navigation, index) {
                    
                }, onTabShow: function(tab, navigation, index) {
                    $('.tab-pane.active').find('#bulkSerial').focus();
                    var $total = navigation.find('li').length;
                    var $current = index+1;
                    var $percent = ($current/$total) * 100;
                    $('.bulk-wizard').find('.progress-bar').css({width:$percent+'%'});
                    // If it's the last tab then hide the last button and show the finish instead
                    if($current >= $total) {
                        $('.transaction-info #transactionDiscount').focus();
                        $('#tabsleft').find('.pager .next').hide();
                        $('#tabsleft').find('.pager .finish').show();
                        $('#tabsleft').find('.pager .finish').removeClass('disabled');
                    } else {
                        $('#tabsleft').find('.pager .next').show();
                        $('#tabsleft').find('.pager .finish').hide();
                    }
                }
            });
            
            $('#tabsleft .finish').click(function() {
                
                var transactionInfo = {};
                transactionInfo.id = $('.tab-pane.active').find('#transactionId').val();
                transactionInfo.discount = $('.tab-pane.active').find('#transactionDiscount').val();
                transactionInfo.date = $('.tab-pane.active').find('#transactionDate').val();
                globals.transaction.info = transactionInfo;
                
                var btn = $(this);
                btn.button('loading');
                $.ajax({
                    async: false,
                    url: "/transaction/edit/"+transactionInfo.id,
                    type: "post",
                    data: { transactionDiscount: transactionInfo.discount, transactionDate: transactionInfo.date },
                    success: function(response){
                        globals.transaction.info.id = response.transactionId;
                        $.each(response.bulks , function(index, bulk){
                            var bulkObject = {};
                            bulkObject.model = bulk.m_id;
                            bulkObject.serial = bulk.m_model_serial;
                            bulkObject.buyPrice = bulk.b_bulk_buy_price;
                            bulkObject.sellPrice = bulk.b_bulk_sell_price;
                            bulkObject.quantity = bulk.b_bulk_quantity;
                            bulkObject.itemHasSerial = bulk.m_model_item_has_serial;
                            globals.transaction.bulks.push( bulkObject );
                        });
                        
                        $('#bulkWizardModal').modal('hide');
                        $('.btn-item-wizard').click();
                        //console.log(globals.transaction);
                    }
                }).always(function () {
                    //btn.button('reset');
                });
            });
            
            $('.tab-pane #transactionDate').datetimepicker({
                pickTime: false,
                language: 'ar'
            });
        });
        
    });
    
    // Item Wizard
    $(document).on('click', '.btn-item-wizard', function(e){
        
        var transactionId = globals.transaction.info.id;
        var data = {transactionId:transactionId};
        var btn = $(this);
        btn.button('loading');
        $('#itemWizardModalContainer').load("/item/wizard/"+transactionId , data , function(){
            btn.button('reset');
            $('#itemWizardModal').modal('show').on('shown.bs.modal', function(){
                $('.tab-pane.active').find('#itemSerial').focus();
            }).on('hidden.bs.modal', function(){
                
            });
            $('#tabsbulk').bootstrapWizard({
                'tabClass': 'nav nav-tabs', 
                'debug': false,
                onInit: function(tab, navigation, index) {
                    
                }, onShow: function(tab, navigation, index) {
                    
                }, onNext: function(tab, navigation, index) {
                    
                }, onPrevious: function(tab, navigation, index) {
                    
                }, onLast: function(tab, navigation, index) {
                    
                }, onTabClick: function(tab, navigation, index) {
                    return false;
                    
                }, onTabShow: function(tab, navigation, index) {
                    //$('.tab-pane.active').find('#bulkSerial').focus();
                    var $total = navigation.find('li').length;
                    var $current = index+1;
                    var $percent = ($current/$total) * 100;
                    $('.bulk-wizard').find('.progress-bar').css({width:$percent+'%'});
                    // If it's the last tab then hide the last button and show the finish instead
                    if($current >= $total) {
                        $('#tabsbulk').find('.pager .next').hide();
                        $('#tabsbulk').find('.pager .finish').show();
                        $('#tabsbulk').find('.pager .finish').removeClass('disabled');
                    } else {
                        $('#tabsbulk').find('.pager .next').show();
                        $('#tabsbulk').find('.pager .finish').hide();
                    }
                }
            });
            
            $('#tabsbulk .finish').click(function() {
                window.location.reload();
            });
            
            $('.tabsitem').bootstrapWizard({
                'nextSelector': '.next-item', 
                'previousSelector': '.previous-item',
                'firstSelector': '.first-item', 
                'lastSelector' : 'last-item',
                'tabClass': 'nav nav-pills', 
                'debug': false,
                onInit: function(tab, navigation, index) {
                    
                }, onShow: function(tab, navigation, index) {
                    
                }, onNext: function(tab, navigation, index) {
                    
                }, onPrevious: function(tab, navigation, index) {
                    
                }, onLast: function(tab, navigation, index) {
                    
                }, onTabClick: function(tab, navigation, index) {
                    
                }, onTabShow: function(tab, navigation, index) {
                    $('#tabsbulk .tab-pane.bulk.active .tabsitem .tab-pane.item.active #itemSerial').focus();
                    
                    var $total = navigation.find('li').length;
                    var $current = index+1;
                    //var $percent = ($current/$total) * 100;
                    //$('.bulk-wizard').find('.progress-bar').css({width:$percent+'%'});
                    // If it's the last tab then hide the last button and show the finish instead
                    if($current >= $total) {
                        //$('.tabsitem').find('.pager .next-item').hide();
                        //$('.tabsitem').find('.pager .finish-item').show();
                        //$('.tabsitem').find('.pager .finish-item').removeClass('disabled');
                    } else {
                        //$('.tabsitem').find('.pager .next-item').show();
                        //$('.tabsitem').find('.pager .finish-item').hide();
                    }
                }
            });
            
            $('.tabsitem .finish-item').click(function(){
                
            });
        });
    });
    
    $('#numberOfBulks #numberOfModels').on('keypress', function(e){
        if( e.which == 13 && globals.isPositiveInteger($(this))) $('.btn-bulk-wizard').click();
        else $(this).focus();
    });
    
    // Validate Bulk
    $('#bulkWizardModalContainer').on('click' , '#bulkForm #check-model-serial' , function () {
        var bulk = $(this).closest('div.tab-pane.active');
        return isValidBulkSerial(bulk);
        
    }).on('hidden.bs.modal' , '#bulkWizardModal' , function(){
        $('#bulkWizardModalContainer').empty();
        
    }).on('keypress', '#bulkForm #bulkSerial', function(e){
        var bulk = $(this).closest('div.tab-pane.active');
        if ( e.which == 13 && isValidBulkSerial(bulk)) $(bulk).find('#bulkBuyPrice').focus();
        else $(this).focus();
        
    }).on('focusout', '#bulkForm #bulkBuyPrice', function(e){
        var bulk = $(this).closest('div.tab-pane.active');
        return isValidBulkBuyPrice(bulk);
        
    }).on('keypress', '#bulkForm #bulkBuyPrice', function(e){
        var bulk = $(this).closest('div.tab-pane.active');
        if( e.which == 13 && isValidBulkBuyPrice(bulk)) $(bulk).find('#bulkSellPrice').focus();
        else $(this).focus();
        
    }).on('focusout', '#bulkForm #bulkSellPrice', function(e){
        var bulk = $(this).closest('div.tab-pane.active');
        return isValidBulkSellPrice(bulk);
        
    }).on('keypress', '#bulkForm #bulkSellPrice', function(e){
        var bulk = $(this).closest('div.tab-pane.active');
        if( e.which == 13 && isValidBulkSellPrice(bulk)) $(bulk).find('#bulkQuantity').focus();
        else $(this).focus();
        
    }).on('focusout', '#bulkForm #bulkQuantity', function(e){
        var bulk = $(this).closest('div.tab-pane.active');
        return isValidBulkQuantity(bulk);
        
    }).on('keypress', '#bulkForm #bulkQuantity', function(e){
        var bulk = $(this).closest('div.tab-pane.active');
        if( e.which == 13 && isValidBulkQuantity(bulk)) $('#tabsleft li.next a')[1].click();
        else $(this).focus();
        
    }).on('keypress', '.transaction-info #transactionDiscount', function(e){
        if( e.which == 13 && globals.isPositiveInteger($(this))) 
            $(this).closest('div.tab-pane.active').find('#transactionPaidAmount').focus();
        else 
            $(this).focus();
        
    }).on('keypress', '.transaction-info #transactionPaidAmount', function(e){
        if( e.which == 13 && globals.isPositiveInteger($(this))) 
            $(this).closest('div.tab-pane.active').find('#transactionDate').focus();
        else 
            $(this).focus();
        
    }).on('keypress', '.transaction-info #transactionDate', function(e){
        if( e.which == 13 && globals.isPositiveInteger($(this))) 
            $('#tabsleft li.finish a')[0].click();
        else 
            $(this).focus();
        
    });
    
    // Check Item Serial
    $("#editModalContainer").on("keypress", "#itemForm #itemSerial", function(e){
        if ( e.which == 13 ){
            var item = $('#itemForm');
            isValidItemSerial(item);
            e.preventDefault();
        }
    /*}).on("focusout", "#itemSerial", function(){
        var item = $('#itemForm');
        isValidItemSerial(item);*/
    }).on('click', 'button.btn-item-serial', function () {
        var item = $('#itemForm');
        isValidItemSerial(item);
    }).on("click", '.btn-item-edit-save', function (e) {
        var item = $('#itemForm');
        if(isValidItemSerial(item)){
            // Attach a submit handler to the form
            $(item).submit(function(event){
                // Stop form from submitting normally
                event.preventDefault();
                // Get some values from elements on the page:
                var $form = $( this ),
                    itemId = $form.find("input[name='itemId']").val(),
                    itemHasSerial = $form.find( "input[name='itemHasSerial']" ).val(),
                    itemSerial = $form.find( "input[name='itemSerial']" ).val(),
                    //itemHasWarranty = $form.find( "select[name='itemHasWarranty']" ).val(),
                    itemStatus = $form.find( "select[name='itemStatus']" ).val(),
                    action = $form.find("input[name='action']").val(),
                    controller = $form.find("input[name='controller']").val(),
                    url = $form.attr( "action" );
                    
                // Send the data using post
                var posting = $.post( '/item/edit', {
                    itemId: itemId,
                    itemHasSerial: itemHasSerial,
                    itemSerial: itemSerial,
                    //itemHasWarranty: itemHasWarranty,
                    itemStatus: itemStatus,
                    action: action,
                    controller: controller,
                });
                
                // Put the results in a div
                posting.done(function( data ) {
                    console.log(data);
                    $('#editModal').modal('hide');
                    $('#editModal').on('hidden.bs.modal' , function(){
                        $('tr.item_'+itemId).replaceWith(data);
                        //$('tr.item_'+itemId).removeClass('info').addClass('success');
                        //$('tr.item_'+itemId).find('td.item_serial_'+itemId).html('<span class="label label-default">'+itemSerial+'</span>');
                        //$('tr.item_'+itemId).find('td.item_status_'+itemId).html('<span class="label label-success">'+lang['In Stock']+'</span>');
                        //$('tr.item_'+itemId+' .btn-item-edit').html(lang['Update'] + '<input type="hidden" value="'+itemId+'" id="item-'+itemId+'">');
                        //window.location.reload(true);
                    });
                });
            });
        } else {
            e.preventDefault();
        }
    });
    
    // Check Item Serial
    $('#itemWizardModalContainer').on('click', 'button.btn-item-serial', function () {
        var item = $(this).closest('.tab-pane.active');
        return isValidItemSerial(item);
    }).on('keypress', '.input-item-serial', function(e){ 
        var item = $(this).closest('.tab-pane.active'); 
        if( e.which == 13 && isValidItemSerial(item)){
            var currentBulkIndex = $('#tabsbulk .tab-pane.bulk.active').index();
            var currentItemIndex = $('#tabsbulk .tab-pane.bulk.active .tabsitem .tab-pane.item.active').index();
            if(currentItemIndex+1 == globals.transaction.bulks[currentBulkIndex].quantity) 
                $('#tabsbulk li.next a')[1].click();
            else 
                $('#tabsbulk .tab-pane.bulk.active .tabsitem li.next-item a')[0].click();
        }
        else $(this).focus();
        
    });
    
    //Bulk validation functions
    function isValidBulk(bulk) {
        var validBulk = true;
        if(!isValidBulkSerial(bulk)) validBulk = false;
        if(!isValidBulkBuyPrice(bulk)) validBulk = false;
        if(!isValidBulkSellPrice(bulk)) validBulk = false;
        if(!isValidBulkQuantity(bulk)) validBulk = false;
        
        return validBulk;
    };
    
    function isValidBulkSerial(bulk) {
        var action = $("#action").val();
        var controller = $("#controller").val();
        
        var bulkId = null;
        if(action === 'edit') bulkId = $("#bulkId");
        
        var bulkSerial = $(bulk).find("#bulkSerial");
        var modelId = $(bulk).find("#modelId");
        var bulkItemHasSerial = $(bulk).find("#bulkItemHasSerial");
        var bulkModelBrand = $(bulk).find("#bulkModelBrand");
        var bulkModelName = $(bulk).find("#bulkModelName");
        var bulkModelNumber = $(bulk).find("#bulkModelNumber");
        var bulkCategory = $(bulk).find("#bulkCategory");
        
        //Validate empty serial
        if(globals.isEmpty(bulkSerial)){            
            $('#alert-message').html('');
            $(bulkSerial).closest('.alert-serial-model').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
            $(bulkSerial).closest('.form-group').removeClass('has-success').addClass('has-error');
            $(bulkSerial).next('label.bulk-error').html(lang['Serial can not be empty']).show();

            
            //Clear Model Data
            $(modelId).val('');
            $(bulkItemHasSerial).val('');
            $(bulkModelBrand).val('');
            $(bulkModelName).val('');
            $(bulkModelNumber).val('');
            $(bulkCategory).val('');
            $(bulkSerial).focus();
            return false;
            
        //Validate letters, numbers and dashes
        } else if(!globals.isAlphanumeric(bulkSerial)){
            $('#alert-message').html('');
            $(bulkSerial).closest('.alert-serial-model').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
            $(bulkSerial).closest('.form-group').removeClass('has-success').addClass('has-error');
            $(bulkSerial).next('label.bulk-error').html(lang['Please enter a valid serial first.']).show();
            
            //Clear Model Data
            $(modelId).val('');
            $(bulkItemHasSerial).val('');
            $(bulkModelBrand).val('');
            $(bulkModelName).val('');
            $(bulkModelNumber).val('');
            $(bulkCategory).val('');
            $(bulkSerial).focus();
            return false;
            
        //Validate model duplication
        } else if(isDuplicatedModel($(bulkSerial).val() , $(bulk).find('#bulkNumber').val())){
            $('#alert-message').html('');
            $(bulkSerial).closest('.alert-serial-model').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
            $(bulkSerial).closest('.form-group').removeClass('has-success').addClass('has-error');
            $(bulkSerial).next('label.bulk-error').html(lang['This model is already added']).show();
            
            //Clear Model Data
            $(modelId).val('');
            $(bulkItemHasSerial).val('');
            $(bulkModelBrand).val('');
            $(bulkModelName).val('');
            $(bulkModelNumber).val('');
            $(bulkCategory).val('');
            $(bulkSerial).focus();
            return false;
        }
        
        $(bulk).find('#check-model-serial').button('loading');
        $.ajax({
            async: false,
            url: "/model/validate",
            type: "post",
            data: {
                modelSerial: $(bulkSerial).val(),
                action: action,
                controller: controller
            },
            success: function(response){
                // Do whatever check of the server data you need here.
                if(response.error === null) {  
                    // Good result, allow the submission
                    $('#alert-message').html('');
                    $(bulkSerial).closest('.alert-serial-model').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
                    $(bulkSerial).closest('.form-group').removeClass('has-success').addClass('has-error');
                    $(bulkSerial).next('label.bulk-error').html(lang['Model does not exist']).show();
                    
                    //Clear Model Data
                    $(modelId).val('');
                    $(bulkItemHasSerial).val('');
                    $(bulkModelBrand).val('');
                    $(bulkModelName).val('');
                    $(bulkModelNumber).val('');
                    $(bulkCategory).val('');
                    $(bulkSerial).focus();
                    globals.passedValidation = false;
                    
                    // Show an error message
                } else if(response.error === 'model_exists'){
                    $(bulkSerial).closest('.alert-serial-model').removeClass('alert-info').removeClass('alert-danger').addClass('alert-success');
                    $(bulkSerial).closest('.form-group').removeClass('has-error').addClass('has-success');
                    $(bulkSerial).next('label.bulk-error').html(lang['Model exists']).show();
                    
                    //Load Model Data
                    $(modelId).val(response.model.m_id);
                    $(bulkItemHasSerial).val(response.model.m_model_item_has_serial);
                    $(bulkModelBrand).val(response.model.br_brand_name);
                    $(bulkModelName).val(response.model.m_model_name + ' ' + response.model.co_color_name);
                    $(bulkModelNumber).val(response.model.m_model_number);
                    $(bulkCategory).val(response.model.c_category_name);
                    
                    globals.passedValidation = true;
                }
            }
        }).always(function () {
            $(bulk).find('#check-model-serial').button('reset');
        });
        
        return globals.passedValidation;
    };
    
    function isValidTransactionDate(transaction){
        var validDate = true;
        var transactionDate = $(transaction).find('#transactionDate');
        if(globals.isEmpty(transactionDate)){
            validDate = false;
            $(transactionDate).next('.transaction-error').html(lang['The field can not be empty']).show();
            $(transactionDate).closest('.form-group').removeClass('has-success').addClass('has-error');
        } else if(!globals.isDate(transactionDate)){
            validDate = false;
            $(transactionDate).next('.bulk-error').html(lang['Please enter a valid date. (e.g. 2014-02-16)']).show();
            $(transactionDate).closest('.form-group').removeClass('has-success').addClass('has-error');
        } else {
            $(transactionDate).next('.bulk-error').html('').hide();
            $(transactionDate).closest('.form-group').removeClass('has-error').addClass('has-success');
        }
        return validDate;
    };
    
    
    function isValidBulkBuyPrice(bulk){
        var validPrice = true;
        var bulkBuyPrice = $(bulk).find('#bulkBuyPrice');
        
        if(globals.isEmpty(bulkBuyPrice)){
            validPrice = false;
            $(bulkBuyPrice).next('.bulk-error').html(lang['The field can not be empty']).show();
            $(bulkBuyPrice).closest('.form-group').removeClass('has-success').addClass('has-error');
        } else if(!globals.isPositiveInteger(bulkBuyPrice)){
            validPrice = false;
            $('#alert-message').html('');
            $(bulkBuyPrice).next('.bulk-error').html(lang['Please insert valid number']).show();
            $(bulkBuyPrice).closest('.form-group').removeClass('has-success').addClass('has-error');
        } else {
            $('#alert-message').html('');
            $(bulkBuyPrice).next('.bulk-error').html('').hide();
            $(bulkBuyPrice).closest('.form-group').removeClass('has-error').addClass('has-success');
        }
        return validPrice;
    };
    
    function isValidBulkSellPrice(bulk){
        var validPrice = true;
        var bulkSellPrice = $(bulk).find('#bulkSellPrice');
        
        if(globals.isEmpty(bulkSellPrice)){
            validPrice = false;
            $(bulkSellPrice).next('.bulk-error').html(lang['The field can not be empty']).show();
            $(bulkSellPrice).closest('.form-group').removeClass('has-success').addClass('has-error');
        } else if(!globals.isPositiveInteger(bulkSellPrice)){
            validPrice = false;
            $('#alert-message').html('');
            $(bulkSellPrice).next('.bulk-error').html(lang['Please insert valid number']).show();
            $(bulkSellPrice).closest('.form-group').removeClass('has-success').addClass('has-error');
        } else {
            $('#alert-message').html('');
            $(bulkSellPrice).next('.bulk-error').html('').hide();
            $(bulkSellPrice).closest('.form-group').removeClass('has-error').addClass('has-success');
        }
        return validPrice;
    };
    
    function isValidBulkQuantity(bulk){
        var validQuantity = true;
        var bulkQuantity = $(bulk).find('#bulkQuantity');
        
        if(globals.isEmpty(bulkQuantity)){
            validQuantity = false;
            $(bulkQuantity).next('.bulk-error').html(lang['The field can not be empty']).show();
            $(bulkQuantity).closest('.form-group').removeClass('has-success').addClass('has-error');
        } else if(!globals.isPositiveInteger(bulkQuantity)){
            validQuantity = false;
            $('#alert-message').html('');
            $(bulkQuantity).next('.bulk-error').html(lang['Please insert valid number']).show();
            $(bulkQuantity).closest('.form-group').removeClass('has-success').addClass('has-error');
        } else {
            $('#alert-message').html('');
            $(bulkQuantity).next('.bulk-error').html('').hide();
            $(bulkQuantity).closest('.form-group').removeClass('has-error').addClass('has-success');
        }
        return validQuantity;
    };
    
    function isDuplicatedModel(serial , bulkNumber){
        var duplicted = false;
        $.each(globals.transaction.bulks , function(index, bulk){
            //console.log(bulkNumber + ' ---- ' + parseInt(index+1));
            if(bulk.serial === serial && bulkNumber != index+1)
                duplicted = true;
        });
        return duplicted;
    };
    
    function isValidItemSerial(item) {
        var action = $("#action").val();
        var controller = $("#controller").val();
        var itemId = $(item).find('#itemId');
        var itemSerial = $(item).find('#itemSerial');
        
        //Validate empty serial
        if( globals.isEmpty(itemSerial) ){
            $(itemSerial).next(".item-error").html(lang['Serial can not be empty']);
            $(itemSerial).closest('.form-group').removeClass('has-success').addClass('has-error');
            $(itemSerial).closest('.alert-serial-item').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
            $(itemSerial).focus();
            return false;
            
        //Validate letters, numbers and dashes
        } else if( !globals.isAlphanumeric(itemSerial) ){
            $(itemSerial).next(".item-error").html(lang['Please enter a valid serial first.']);
            $(itemSerial).closest('.form-group').removeClass('has-success').addClass('has-error');
            $(itemSerial).closest('.alert-serial-item').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
            $(itemSerial).focus();
            return false;
        }
        
        $(itemSerial).closest('#btn-item-serial').button('loading');
        $.ajax({
            async: false,
            url: "/item/validate",
            type: "post",
            data: {
                itemId:itemId.val(),
                itemSerial:$(itemSerial).val(),
                /*itemStatus:itemStatus,
                itemHasWarranty:itemHaswarranty,
                itemWarranty:itemWarranty,
                itemColor:itemColor,*/
                action:action,
                controller:controller
            },
            success: function(response){
                // Serial is valid
                if(response.error === null){
                    $(itemSerial).next(".item-error").html(lang['Serial is valid.']);
                    $(itemSerial).closest('.form-group').removeClass('has-error').addClass('has-success');
                    $(itemSerial).closest('.alert-serial-item').removeClass('alert-info').removeClass('alert-danger').addClass('alert-success');
                    //$('#itemColor').focus();
                    globals.passedValidation = true;
                    
                // Serial is not valid
                } else if(response.error === 'item_exists'){
                    $(itemSerial).next(".item-error").html(lang['Item already exists.']);
                    $(itemSerial).closest('.form-group').removeClass('has-success').addClass('has-error');
                    $(itemSerial).closest('.alert-serial-item').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
                    //$("#itemSerial").focus();
                    globals.passedValidation = false;
                    
                // It's Model Serial
                } else if(response.error === 'model_serial'){
                    $(itemSerial).next(".item-error").html(lang['This is a model serial']);
                    $(itemSerial).closest('.form-group').removeClass('has-success').addClass('has-error');
                    $(itemSerial).closest('.alert-serial-item').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
                    //$("#itemSerial").focus();
                    globals.passedValidation = false;
                    
                // Item has no serial
                } else if(response.error === 'item_updated'){
                    $(itemSerial).next(".item-error").html(lang['Serial is valid.']);
                    $(itemSerial).closest('.form-group').removeClass('has-error').addClass('has-success');
                    $(itemSerial).closest('.alert-serial-item').removeClass('alert-info').removeClass('alert-danger').addClass('alert-success');
                    //$('#itemColor').focus();
                    globals.passedValidation = true;
                }
            }
        }).always(function () {
            $(itemSerial).closest('#btn-item-serial').button('reset');
        });
        return globals.passedValidation;
    };
    
    globals.isEmpty = function (element){
        var value;
        
        if(typeof element == 'object') value = $(element).val();
        else value = element;
        
        if(value == '') return true;
        else return false;
    };
    
    globals.isAlphanumeric = function (element){
        var value;
        
        if(typeof element == 'object') value = $(element).val();
        else value = element;
        
        if(/^[a-zA-Z0-9-]+$/.test(value)) return true;
        else return false;
    };
    
    globals.isAlphanumericSpace = function (element){
        var value;
        
        if(typeof element == 'object') value = $(element).val();
        else value = element;
        
        //lang["The field must contain only letters, numbers, or dashes."];
        if(/^[a-z0-9\u0600-\u06FF\-\s]+$/i.test(value)) return true;
        else return false;
    };
    
    //Alphanumeric with Space and Comma
    globals.isAlphanumericSpaceComma = function (element){
        var value;
        
        if(typeof element == 'object') value = $(element).val();
        else value = element;
        
        //lang["The field must contain only letters, numbers, dashes or commas."];
        if(/^[a-z0-9\u0600-\u06FF\-,\s]+$/i.test(value)) return true;
        else return false;
    };
    
    globals.isDate = function (element){
        var value;
        
        if(typeof element == 'object') value = $(element).val();
        else value = element;
        
        //lang["Please enter a valid date. (e.g. 2014-02-16)"];
        if(/^(19|20)\d\d[\-\/.](0[1-9]|1[012])[\-\/.](0[1-9]|[12][0-9]|3[01])$/.test(value)) return true;
        else return false;
    };
    
    globals.isPositiveInteger = function (element){
        var value;
        
        if(typeof element == 'object') value = $(element).val();
        else value = element;
        
        if(/^\d+$/.test(value)) return true;
        else return false;
    };
    
    globals.isNumber = function (element){
        var value;
        
        if(typeof element == 'object') value = $(element).val();
        else value = element;
        
        if(/^\d+$/.test(value)) return true;
        else return false;
    };
    
});