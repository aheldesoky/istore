$(document).ready(function(){
    var currentPage;
    var item = new Array();
    var bulk = new Array();
    var itemList = new Array();
    var bulkList = new Array();
    var itemListRequired = new Array();
    var itemId;
    var passedValidation = false;
    var subtotal = 0;
    var discount = 0;
    var total = 0;
    var amountPaid = 0;
    var bulkQty;
    
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
        
        if(currentPage>0)
            url += '?page='+currentPage;
        
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
    $('#navbar-main ul li').removeClass('active')
    if(controller === 'default')
        $('#navbar-main ul li.home').addClass('active');
    else
        $('#navbar-main ul li.'+controller).addClass('active');
    
    $('div#reportModel').slimScroll({
        height: '135px',
        alwaysVisible: false,
        wheelStep: '10',
        railVisible: true,
        allowPageScroll: true,
    });
    
    //Validator Initialization
    //$('#modelForm , #bulkForm').validate();
    
    //Preventing enter on Model and Bulk forms
    //$('#modelForm , #bulkForm').bind('keypress', function(e){
      //  if ( e.which == 13 ) e.preventDefault();
    //});
    
    /*$('#editModalContainer').on('click', '.btn-item-specs', function (e) {
        $('.btn-item-edit-close').click();
        $('.modal-item-specs').modal('show');
    });*/
    
    //$('#alert-message').html(alertDangerMessage($('#validationMessage').val()));
    $('.btn-popover').popover();
    $('#bulkDate, #reportFromDate, #reportToDate, #filterFromDate, #filterToDate').datepicker({endDate: new Date}).on('changeDate', function(){
        $(this).focus().datepicker('hide');
    });
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
            
    }).on("click", ".btn-bulk-filter-modal", function(e){
            e.preventDefault();
            $('#filtrationBulk').modal('show');
            
    }).on("click", ".btn-bulk-filter", function(e){
            //e.preventDefault();
            $('#filterForm').submit();
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
                        $('.postpaid-amount-error').html('Amount successfully added');
                        $('#alert-message').html(alertSuccessMessage('Payment of '+ amount +' L.E. is successfully added to bill #'+saleId));
                        $(element).val(0);
                        $('#label-sale-paid').html(response.total_paid + ' ' + 'L.E.');
                        $('#label-sale-remaining').html(response.total_due - response.total_paid + ' ' + 'L.E.');
                        $('tr.sale-'+saleId+' td.total-paid').html(response.total_paid + ' ' + 'L.E.');
                        $('tr.sale-'+saleId+' td.total-remaining').html(response.total_due - response.total_paid + ' ' + 'L.E.');
                        $('#confirmPaymentModal').modal('hide');
                        $('#addPostpaidPayment').modal('hide');
                    } else {
                        $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                        $('.postpaid-amount-error').html('Amount can not be added');
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
                        $('#alert-refund-message').html(alertSuccessMessage('Payment of '+paymentAmount+' L.E. has been refunded'));
                    } else {
                        $('#confirmRefundModal').modal('hide');
                        $('#alert-refund-message').html(alertDangerMessage('Can not refund '+paymentAmount+' L.E. at this time.'));
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
                        $('#alert-message').html(alertSuccessMessage('Sale #'+saleId+' has been refunded'));
                    } else {
                        $('#refundSaleModal').modal('hide');
                        $('#alert-message').html(alertDangerMessage('Can not refund Sale # '+saleId+' at this time.'));
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
                        $('#alert-message').html(alertSuccessMessage('Discount for Sale #'+saleId+' has been confirmed'));
                    } else {
                        $('#confirmDiscountModal').modal('hide');
                        $('#alert-message').html(alertDangerMessage('Can not confirm discount for Sale # '+saleId+' at this time.'));
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
            $('.postpaid-amount-error').html('Amount can not be greater than the remaining value');
            passedValidation = false;
        } else if(/^\d+$/.test($(element).val()) && parseInt($(element).val()) <= 0 ){
            $('.postpaid-amount-error').html('Amount must be greater than zero');
            passedValidation = false;
        } else if(!/^\d+$/.test($(element).val())){
            $('.postpaid-amount-error').html('Amount must be a number');
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
    
    $("#clockbox").flipcountdown({
        size: 'xs',
        showHour:true,
        showMinute:true,
        showSecond:false,
        am:false
    });
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
    
    $('#saleForm').submit(function(e){
        e.preventDefault();
        $(this).ajaxForm({ 
            //target:        '_blank',   // target element(s) to be updated with server response 
            beforeSubmit:  function(){
                //alert('before submittt');
            },
            success:       function(){ 
                //alert('sdddsddsd')
                window.location.reload();
                self.close();
                return false;
            },

            // other available options: 
            url:       '/sale/add',         // override for form's 'action' attribute 
            type:      'post'        // 'get' or 'post', override for form's 'method' attribute 
            //dataType:  null        // 'xml', 'script', or 'json' (expected server response type) 
            //clearForm: true        // clear all form fields after successful submit 
            //resetForm: true        // reset the form after successful submit 

            // $.ajax options can be used here too, for example: 
            //timeout:   3000 
        });
        
    });
    
    //Calculate discount
    $('#saleDiscount').change(function(){
        if( /^\d+$/.test($(this).val()) && parseInt($(this).val()) <= subtotal && parseInt($(this).val()) >= 0 ){
            discount = parseInt($(this).val());
            $('#total').html(calculateTotal() + ' L.E.');
            $('#amountPaid').val(0);
            $('#remainingAmount').html(calculateTotal() + ' L.E.');
        } else {
            $(this).val(discount);
            discount = parseInt($(this).val());
            $('#total').html(calculateTotal() + ' L.E.');
            $('#amountPaid').val(0);
            $('#remainingAmount').html(calculateTotal() + ' L.E.');
        }
    })/*.keyup(function(){
        if( /^\d+$/.test($(this).val()) && parseInt($(this).val()) <= subtotal && parseInt($(this).val()) >= 0 ){
            discount = parseInt($(this).val());
            $('#total').html(calculateTotal() + ' L.E.');
        } else {
            $(this).val(discount);
            discount = parseInt($(this).val());
            $('#total').html(calculateTotal() + ' L.E.');
        }
    });*/
    
    $('#amountPaid').change(function(){
        if( /^\d+$/.test($(this).val()) && parseInt($(this).val()) <= total && parseInt($(this).val()) >= 0){
            amountPaid = parseInt($(this).val())
            $('#remainingAmount').html(total-amountPaid + ' L.E.');
        } else if(/^\d+$/.test($(this).val()) && parseInt($(this).val()) > total){
            $(this).val(total);
            amountPaid = parseInt($(this).val())
            $('#remainingAmount').html(total-parseInt($(this).val()) + ' L.E.');
        } else if( parseInt($(this).val()) < 0 ){
            $(this).val(0);
            amountPaid = parseInt($(this).val())
            $('#remainingAmount').html(total-parseInt($(this).val()) + ' L.E.');
        } else {
            $(this).val(0);
            amountPaid = parseInt($(this).val())
            $('#remainingAmount').html(total-parseInt($(this).val()) + ' L.E.');
        } 
    });/*.keyup(function(){
        if(!/^\d+$/.test($(this).val()))
            $(this).val(0);
        else{
            $(this).val(parseInt($(this).val(), 10));
        }
    });*/
    
    var customerValidator = $('#saleForm').validate({
        submitHandler: function(form){
            form.submit();
        },
        rules: {
            customerPhone: {
                required: true,
                number: true,
            },
            customerFirstName: {
                required: true,
                alphanumericspace: true,
            },
            customerLastName: {
                required: true,
                alphanumericspace: true,
            },
            customerAddress: {
                required: true,
                alphanumericspacecomma: true,
            },
            customerNotes: {
                alphanumericspace: true,
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
    $('#customerPhone').keypress(function(){customerValidator.element('#customerPhone');});
    $('#customerFirstName').keypress(function(){customerValidator.element('#customerFirstName');});
    $('#customerLastName').keypress(function(){customerValidator.element('#customerLastName');});
    $('#customerAddress').keypress(function(){customerValidator.element('#customerAddress');});
    $('#customerNotes').keypress(function(){customerValidator.element('#customerNotes');});
    
    $('.btn-find-phone').click(function(e){
        var customerPhone = $('#customerPhone').val();
        var btn = $(this);
        btn.button('loading');
        $.ajax({
                url: "/customer/find",
                type: "post",
                data: {phone:customerPhone},
                success: function(response){
                    if(response.count===1){
                        $('#customerFirstName').val(response.customer.c_customer_fname);
                        $('#customerLastName').val(response.customer.c_customer_lname);
                        $('#customerAddress').val(response.customer.c_customer_address);
                        $('#customerNotes').val(response.customer.c_customer_notes);
                        $('.alert-message-modal').html(alertInfoMessage('Customer is available.'));
                    } else {
                        $('#customerFirstName').val('');
                        $('#customerLastName').val('');
                        $('#customerAddress').val('');
                        $('#customerNotes').val('');
                        $('.alert-message-modal').html(alertDangerMessage('Customer is not found.'));
                    }
                    customerValidator.element('#customerPhone');
                    customerValidator.element('#customerFirstName');
                    customerValidator.element('#customerLastName');
                    customerValidator.element('#customerAddress');
                    customerValidator.element('#customerNotes');
                }
        }).always(function () {
                    btn.button('reset');
        });
        customerValidator.element('#customerPhone');
        customerValidator.element('#customerFirstName');
        customerValidator.element('#customerLastName');
        customerValidator.element('#customerAddress');
        customerValidator.element('#customerNotes');
        e.preventDefault(); // prevents default
        return false;
    });
    
    $('#itemQuantity').change(function(){
        var itemQty = $(this).val()
        if(!/^\d+$/.test(itemQty) || itemQty<=0){
            $('#itemQuantity').val(1);
        } else if(itemQty >bulkQty){
            $('#itemQuantity').val(bulkQty);
        }
    });
    // Payment method & Amount paid
    $('.table-view-sale').on('change', 'select[name="paymentMethod"]', function(){
        if(this.value === 'postpaid'){
            $('#amountPaid').prop("disabled", false).val(0);
            $('.input-amount-paid').removeClass('hidden');
            $('label#remainingAmount').html(calculateTotal() + ' L.E.');
            amountPaid = 0;
        } else {
            $('#amountPaid').prop("disabled", true);
            $('.input-amount-paid').addClass('hidden');
        }
    });

    $('#itemSerial:focus').focus();
    $('.btn-specs').popover('toggle');

    var total_pages = $('#total_pages').val();
    var current_page = $('#current_page').val();
    var options = {
        currentPage: current_page,
        totalPages: total_pages,
                useBootstrapTooltip:true,
        pageUrl: function(type, page, current){
            currentPage = page;
            if(getQueryVariable('sort'))
                return "?page="+page+'&column='+getQueryVariable('column')+'&sort='+getQueryVariable('sort');
            else
                return "?page="+page;
        },
        itemTexts: function (type, page, current) {
            switch (type) {
            case "first":
                return "First";
            case "prev":
                return "Previous";
            case "next":
                return "Next";
            case "last":
                return "Last";
            case "page":
                return page;
            }
        }
    };

    $('#paginator').bootstrapPaginator(options);
    $('#paginator-ar').bootstrapPaginator(options);

    //Checkout sale
    $('.btn-checkout').click(function(e){
        if(validCheckout(e)){ 
            $('.bs-modal-lg').modal('show');
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
            $('#alert-message').html(alertDangerMessage('There is no item to be added.'));
            $('.serial-error').html('Check item first.')
            $('#itemSerial').focus();
        } else if(itemBrand == '') {
            $('#alert-message').html(alertInfoMessage('Check item first.'));
            $('#itemSerial').focus();
        } else if(itemList.length > 0){
            var flag = false;
            var itemIndex;
            $.each(itemList , function(index , value){
                if(item.m_model_item_has_serial){
                    if(value.i_item_serial===item.i_item_serial){
                        flag = true;
                        itemIndex = index+1;
                        return false;
                    }
                } else {
                    if(value.m_model_serial===item.m_model_serial) {
                        flag = true;
                        itemIndex = index+1;
                        return false;
                    }
                }
            });
            if(flag === true){
                $('#alert-message').html(alertInfoMessage('Item already added to list.'));
                //$(".table-view-sale tr:eq("+ itemIndex +")").effect("highlight", {}, 1500);
                clearItem();
            } else {
                addItem();
            }
        } else {
            addItem();
        }
    });

    // Remove item from list
    $('.table-view-sale').on('click' , 'a.btn-remove-item' , function(){
        var itemSerial = $(this).closest('tr').children()[0].innerHTML;
        removeItem(itemSerial);
        $('#alert-message').html(alertInfoMessage('Item removed from list.'));
        $(this).closest('tr').remove();
    });
    
    $('.alert-serial-sale #itemSerial').keypress(function(e){
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
            $(".serial-error").html('Serial can not be empty.');
            $('#itemSerial').closest('.form-group').removeClass('has-success').addClass('has-error');
            $('.alert-serial-sale').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
            $("#itemSerial").focus();
            return false;
            
        //Validate letters, numbers and dashes
        } else if(!/^[a-zA-Z0-9-]+$/.test(itemSerial)){
            $(".serial-error").html('Please enter a valid serial first.');
            $('#itemSerial').closest('.form-group').removeClass('has-success').addClass('has-error');
            $('.alert-serial-sale').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
            $("#itemSerial").focus();
            return false;
        } else {
            var btn = $(this);
            btn.button('loading');
            $.ajax({
                    url: "/item/get",
                    type: "post",
                    data: {serial:itemSerial},
                    success: function(response){
                        if(response.count){
                            item = response.items[0];
                            if(!item.m_model_item_has_serial)
                                bulk = response.items;
                            
                            $('#itemBrand').html(item.m_model_brand);
                            $('#itemModel').html(item.m_model_model);
                            $('#itemCategory').html(item.c_category_name);
                            $('#itemPrice').html(item.b_bulk_price+' L.E.');
                            $('#alert-message').html('');
                            $(".serial-error").html('Item is available.');
                            $('#itemSerial').closest('.form-group').removeClass('has-error').addClass('has-success');
                            $('.alert-serial-sale').removeClass('alert-info').removeClass('alert-danger').addClass('alert-success');
                            if(!item.m_model_item_has_serial){
                                $('.quantity-field').removeClass('hidden');
                                $('#bulkQuantity').html(' of ' + response.count);
                                $('#bulk-qty').val(response.count);
                                bulkQty = response.count;
                            } else {
                                $('.quantity-field').addClass('hidden');
                                $('#bulk-qty').val(0);
                            }
                            
                        } else {
                            $('#alert-message').html('');
                            $(".serial-error").html('Item is not found.');
                            $('#itemSerial').closest('.form-group').removeClass('has-success').addClass('has-error');
                            $('.alert-serial-sale').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
                        }
                    }
                }).always(function () {
                        btn.button('reset');
            });
        }
    });
       
    // Check Model Serial
    $('#modelForm #model-serial').click(function () {
        return validateModelSerial();
    });
    
    // Check Bulk Serial
    $('#bulkForm #model-serial').click(function () {
        return validateBulkSerial();
    });

    // Check Item Serial
    $('#editModalContainer').on('click', 'button.btn-item-serial', function () {
        return validateItemSerial();
    });
// Forms Validations
    $('#modelSerial').focus();
    $('#modelForm #modelSerial').keypress(function(e){
        if ( e.which == 13 ) return validateModelSerial();
    }).focusout(function(){
        return validateModelSerial();
    });
    $('#bulkForm #modelSerial').keypress(function(e){
        if ( e.which == 13 ) return validateBulkSerial();
    }).focusout(function(){
        return validateBulkSerial();
    });
    
    $('#itemSerial').focus();
    $("#editModalContainer").on("keypress", "#itemForm #itemSerial", function(e){
        if ( e.which == 13 ){ 
            validateItemSerial();
            e.preventDefault();
        }
    }).on("focusout", "#itemSerial", function(){
        validateItemSerial();
    });
    
    //Validate Model
    var modelValidator = $('#modelForm').validate({
        submitHandler: function(form){
            if(passedValidation)
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
            $(".serial-error").html('Serial can not be empty.');
            $('#modelSerial').closest('.form-group').removeClass('has-success').addClass('has-error');
            $('.alert-serial-model').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
            $("#modelSerial").focus();
            return false;
            
        //Validate letters, numbers and dashes
        } else if(!/^[a-zA-Z0-9-]+$/.test(modelSerial)){
            $(".serial-error").html('Please enter a valid serial first.');
            $('#modelSerial').closest('.form-group').removeClass('has-success').addClass('has-error');
            $('.alert-serial-model').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
            $("#modelSerial").focus();
            return false;
        }
        
        $('#model-serial').button('loading');
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
                    $(".serial-error").html('Serial is valid.');
                    $('#modelSerial').closest('.form-group').removeClass('has-error').addClass('has-success');
                    $('.alert-serial-model').removeClass('alert-info').removeClass('alert-danger').addClass('alert-success');
                    $('#modelBrand').focus();
                    passedValidation = true;
                    
                    // Show an error message
                } else if(response.error === 'model_exists'){
                    $(".serial-error").html('Model already exists.');
                    $('#modelSerial').closest('.form-group').removeClass('has-success').addClass('has-error');
                    $('.alert-serial-model').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
                    $("#modelSerial").focus();
                }
            }
        }).always(function () {
            $('#model-serial').button('reset');
        });
        return passedValidation;
    }
    
    //Validate Bulk
    var bulkValidator = $('#bulkForm').validate({
        submitHandler: function(form){
            if(passedValidation)
                if(form.valid())
                    form.submit();
        },
        rules: {
            bulkPrice: {
                required: true,
                number: true,
                greaterThan: 0,
            },
            bulkQuantity: {
                required: true,
                number: true,
                greaterThan: 0,
            },
            bulkSpecs: {
                alphanumericspace: true,
            },
            bulkDate: {
                required: true,
                regexdate: true,
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
    $('#bulkDate').keypress(function(){bulkValidator.element('#bulkDate');});
    $('#bulkPrice').keypress(function(){bulkValidator.element('#bulkPrice');});
    $('#bulkQuantity').keypress(function(){bulkValidator.element('#bulkQuantity');});
    $('#bulkSupplier').change(function(){bulkValidator.element('#bulkSupplier');});
    
    //Quantity not less than 1
    $.validator.addMethod("greaterThan", function(value, element, param) {
        return this.optional(element) || value > param;
    }, "Quantity must be greater than zero.");
    
    function validateBulkSerial() {
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
            $(".serial-error").html('Serial can not be empty.');
            $('#modelSerial').closest('.form-group').removeClass('has-success').addClass('has-error');
            $('.alert-serial-model').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
            //Clear Model Data
            $('#bulkBrand').val('');
            $('#bulkModel').val('');
            $('#bulkCategory').val('');
            $("#modelSerial").focus();
            return false;
            
        //Validate letters, numbers and dashes
        } else if(!/^[a-zA-Z0-9-]+$/.test(modelSerial)){
            $(".serial-error").html('Please enter a valid serial.');
            $('#modelSerial').closest('.form-group').removeClass('has-success').addClass('has-error');
            $('.alert-serial-model').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
            //Clear Model Data
            $('#bulkBrand').val('');
            $('#bulkModel').val('');
            $('#bulkCategory').val('');
            $("#modelSerial").focus();
            return false;
        }
        
        $('#model-serial').button('loading');
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
                    $(".serial-error").html('Model does not exist.');
                    $('#modelSerial').closest('.form-group').removeClass('has-success').addClass('has-error');
                    $('.alert-serial-model').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
                    $('#bulkBrand').val('');
                    $('#bulkModel').val('');
                    $('#bulkCategory').val('');
                    $("#modelSerial").focus();
                    
                    // Show an error message
                } else if(response.error === 'model_exists'){
                    $(".serial-error").html('Model exists.');
                    $('#modelSerial').closest('.form-group').removeClass('has-error').addClass('has-success');
                    $('.alert-serial-model').removeClass('alert-info').removeClass('alert-danger').addClass('alert-success');
                    $('#bulkBrand').val(response.model.m_model_brand);
                    $('#bulkModel').val(response.model.m_model_model);
                    $('#bulkCategory').val(response.model.c_category_name);
                    $('#bulkDate').focus();
                    passedValidation = true;
                }
            }
        }).always(function () {
            $('#model-serial').button('reset');
        });
        return passedValidation;
    }
    
    //Validate Item    
    $('.btn-item-edit').on('click', function () {
        itemId = $(this).children()[0].value;
        var btn = $(this);
        var data = {itemId:itemId};
        btn.button('loading');
        $('#editModalContainer').load("/item/edit" , data , function(){
            btn.button('reset');
            $('#editModal').modal('show');
            var itemValidator = $('#itemForm').validate({
                submitHandler: function(form){
                    if(passedValidation)
                        if(form.valid())
                            form.submit();
                },
                rules: {
                    itemNotes: {
                        alphanumericspace: true
                    }
                },
                highlight: function (element) {
                    $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                },
                success: function (element) {
                    $(element).text(lang['ok']).addClass('valid')
                        .closest('.form-group').removeClass('has-error').addClass('has-success');
                }
            });
            $('#itemNotes').keypress(function(){itemValidator.element('#itemNotes');});
        });
    });

    $("#editModalContainer").on("click", '.btn-item-edit-save', function () {
        return validateItemSerial();
    });
    
    function validateItemSerial() {
        var action = $("#action").val();
        var controller = $("#controller").val();
        var itemSerial = $("#itemSerial").val();
        var itemColor = $("#itemColor").val();
        var itemStatus = $("#itemStatus").val();
        var itemHaswarranty = $("#itemHasWarranty").val();
        var itemWarranty = $("#itemWarranty").val();
        
        //Validate empty serial
        if(itemSerial === ''){
            $(".serial-error").html('Serial can not be empty.');
            $('#itemSerial').closest('.form-group').removeClass('has-success').addClass('has-error');
            $('.alert-serial-item').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
            $("#itemSerial").focus();
            return false;
            
        //Validate letters, numbers and dashes
        } else if(!/^[a-zA-Z0-9-]+$/.test(itemSerial)){
            $(".serial-error").html('Please enter a valid serial first.');
            $('#itemSerial').closest('.form-group').removeClass('has-success').addClass('has-error');
            $('.alert-serial-item').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
            $("#itemSerial").focus();
            return false;
        }
        
        $('#btn-item-serial').button('loading');
        $.ajax({
            async: false,
            url: "/item/validate",
            type: "post",
            data: {
                itemId:itemId,
                itemSerial:itemSerial,
                itemStatus:itemStatus,
                itemHasWarranty:itemHaswarranty,
                itemWarranty:itemWarranty,
                itemColor:itemColor,
                action:action,
                controller:controller
            },
            success: function(response){
                // Do whatever check of the server data you need here.
                if(response.error === null) {  
                    // Good result, allow the submission
                    $('#alert-message').html('');
                    $(".serial-error").html('Serial is valid.');
                    $('#itemSerial').closest('.form-group').removeClass('has-error').addClass('has-success');
                    $('.alert-serial-item').removeClass('alert-info').removeClass('alert-danger').addClass('alert-success');
                    $('#itemColor').focus();
                    passedValidation = true;
                    
                    // Show an error message
                } else if(response.error === 'item_exists'){
                    $(".serial-error").html('Item already exists.');
                    $('#itemSerial').closest('.form-group').removeClass('has-success').addClass('has-error');
                    $('.alert-serial-item').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
                    $("#itemSerial").focus();
                } else if(response.error === 'has_no_serial'){
                    passedValidation = true;
                }
            }
        }).always(function () {
            $('#btn-item-serial').button('reset');
        });
        return passedValidation;
    }
    
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
            element.focus();
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
            $(".category-error").html('Category name can not be empty.');
            $(element).closest('.form-group').removeClass('has-success').addClass('has-error').focus();
            return passedValidation;
            
        //Validate letters, numbers and dashes
        } else if(!/^[a-z0-9\u0600-\u06FF\s]+$/i.test(categoryName)){
            $(".category-error").html('Please enter a valid name.');
            $(element).closest('.form-group').removeClass('has-success').addClass('has-error').focus();
            return passedValidation;
        }
        
        var btn = $('.btn-categroy-add');
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
                    $(".category-error").html('Category' + ' ' + categoryName + ' ' + 'already exists.' );
                    
                } else if (response.error==='not_found'){
                    $(element).text(lang['ok']).addClass('valid')
                        .closest('.form-group').removeClass('has-error').addClass('has-success');
                    $(".category-error").html('Category name is valid');
                    passedValidation = true;
                }
            }
        }).always(function () {
            btn.button('reset');
        });
        return passedValidation;
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
            $(".warranty-error").html('Warranty name can not be empty.');
            $(element).closest('.form-group').removeClass('has-success').addClass('has-error').focus();
            return passedValidation;
            
        //Validate letters, numbers and dashes
        } else if(!/^[a-z0-9\u0600-\u06FF\s]+$/i.test(warrantyName)){
            $(".warranty-error").html('Please enter a valid name.');
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
                    $(".warranty-error").html('Warranty' + ' ' + warrantyName + ' ' + 'already exists.' );
                    
                } else if (response.error==='not_found'){
                    $(element).text(lang['ok']).addClass('valid')
                        .closest('.form-group').removeClass('has-error').addClass('has-success');
                    $(".warranty-error").html('Warranty name is valid');
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
    }, "The field must contain only letters, numbers, or dashes.");
    
    $.validator.addMethod("alphanumericspacecomma", function(value, element) {
        return this.optional(element) || /^[a-z0-9\u0600-\u06FF\-,\s]+$/i.test(value);
    }, "The field must contain only letters, numbers, dashes or commas.");

    //Validate Date to YYYY-MM-DD format
    $.validator.addMethod("regexdate", function(value, element) {          
        return this.optional(element) || /^(19|20)\d\d[\-\/.](0[1-9]|1[012])[\-\/.](0[1-9]|[12][0-9]|3[01])$/.test(value);
    }, "Please enter a valid date. (e.g. 2014-02-16)");

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
        var models = new Array();
        $.each($('div#reportModel input:checked') , function(index, value){
            if(value.value)
                models.push(value.value);
        })
        var reportModel = models;
        var reportCategory = $('#reportCategory').val();
        var reportType = $('#reportType').val();
        var reportPayment = $('#reportPayment').val();
        var reportStatus = $('#reportStatus').val();
        var reportRange = $('#reportRange').val();
        var reportFromDate = $('#reportFromDate').val();
        var reportToDate = $('#reportToDate').val();
        var action = $('#action').val();
        var controller = $('#controller').val();
        
        var isValid = true;
        if(reportModel.length === 0 && reportPayment != 'amount'){
            $('.report-model-error').html('Please select model.');
            $('div#reportModel').closest('.form-group').removeClass('has-success').addClass('has-error');
            isValid = false;
        } else {
            $('.report-model-error').html('');
            $('div#reportModel').closest('.form-group').removeClass('has-error');
        }
        
        if(!$('#reportRange').prop('disabled') && reportRange === ''){
            $('.report-range-error').html('Please select period.');
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
    //Functions
    /*function validateItemSerial(btn , itemSerial , handleData){
    btn.button('loading');
    $.ajax({
            url: "/item/find",
            type: "post",
            data: {serial:itemSerial},
            success: function(response){
                    handleData(response.item);
            },
    }).always(function () {
            btn.button('reset');
    });
    }*/

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
        var itemQuantity = $('#itemQuantity').val();
        item['s_sale_quantity'] = itemQuantity;
        
        if(item.m_model_item_has_serial)
            displayedSerial = item.i_item_serial;
        else
            displayedSerial = item.m_model_serial;

        if(item.m_model_item_has_serial)
            itemList.push(item);
        else
            for(var i=0 ; i<itemQuantity ; i++){
                itemList.push(bulk[i]);
                bulkList.push(bulk[i]);
            }
       //console.log(itemList)     
        var tableRow = "<tr>" +
                            "<td>" + displayedSerial + "</td>" +
                            "<td>" + item.m_model_brand + ' ' + item.m_model_model + "</td>" +
                            "<td>" + item.c_category_name + "</td>" +
                            "<td>" + itemQuantity + "</td>" +
                            "<td>" + item.b_bulk_price + " L.E.</td>" +
                            "<td>" +
                                "<a class='btn btn-xs btn-remove-item'></a>" +
                            "</td>" +
                        "</tr>";
        $('.table-view-sale .table-footer').before(tableRow);
        $('.table-empty-row').addClass('hidden');
        clearItem();
        $('#alert-message').html(alertInfoMessage('Item added to list.'));
        itemListRequired = new Array();
        $.each(itemList , function (index, value){
            var requiredItem = {
                itemId: value.i_id , 
            };
            itemListRequired.push(requiredItem);
            $('#itemList').val(JSON.stringify(itemListRequired));
        });
        item = null;
        
        $('#subtotal').html(calculateSubtotal() + ' L.E.');
        $('#total').html(calculateTotal() + ' L.E.');
        //console.log(itemListRequired);
    }
    function removeItem(itemSerial){
        var x = 0;
        /*$.each(itemList , function(index , value){ console.log(x++);
            if(value.m_model_item_has_serial){
                console.log('aaaaaa')
                if(value.i_item_serial===itemSerial){
                    console.log('bbbbbb')
                    itemList.splice(index , 1);
                }
            } else {
                if(value.m_model_serial===itemSerial){
                    console.log('cccccc')
                    itemList.splice(index , 1);
                }
                console.log('dddddd')
            }
            console.log(itemList);
        });*/
        //console.log(itemList);
        
        for(var i=itemList.length-1 ; i>=0 ; i--){
            if(itemList[i].m_model_item_has_serial){
                if(itemList[i].i_item_serial===itemSerial){
                    itemList.splice(i , 1);
                }
            } else {
                if(itemList[i].m_model_serial===itemSerial){
                    itemList.splice(i , 1);
                }
            }
        }
        
        //console.log(itemList);
        itemListRequired = new Array();
        $.each(itemList , function (index, value){
            var requiredItem = {
                itemId: value.i_id , 
                itemDiscount: value.s_sale_discount , 
            };
            itemListRequired.push(requiredItem);
        });
        $('#itemList').val(JSON.stringify(itemListRequired));
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
        item = null;
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
    function calculateSubtotal(){
        subtotal = 0;
        $.each(itemList , function (index, value){
            subtotal += parseInt(value.b_bulk_price);
        });
        return subtotal;
    }
    function calculateTotal(){
        total = subtotal-discount;
        return total;
    }
    function validCheckout(e){
        var isValid = true;
        var paymentMethod = $('#paymentMethod').val();
        if(itemList.length > 0){
            if( !/^\d+$/.test(discount) || discount>subtotal || discount<0 ){
                isValid = false;
                $('#alert-message').html(alertDangerMessage('Discount is invalid.'));
                $('#saleDiscount').focus();
                e.preventDefault();
            }
            
            if(paymentMethod === 'postpaid'){
                amountPaid = $('#amountPaid').val();
                if( !/^\d+$/.test(amountPaid) || amountPaid>=total || amountPaid<0){
                    isValid = false;
                    $('#alert-message').html(alertDangerMessage('Amount Paid is invalid.'));
                    $('#amountPaid').focus();
                    e.preventDefault();
                }
            }
            //alert(isValid);
        } else {
            $('#alert-message').html(alertDangerMessage('The list is empty, Please check items to list.'));
            $('#itemSerial').focus();
            e.preventDefault();
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
        if($(this).prop('checked')){
            $(".multiselect input").prop('checked', true);
            $(".multiselect label").addClass("multiselect-on");
            $(".multiselect label.hidden input").prop('checked', false);
            $(".multiselect label.hidden").removeClass("multiselect-on");
        }else{
            $(".multiselect input").prop('checked', false);
            $(".multiselect label").removeClass("multiselect-on");
        }
    });
    $(".multiselect input").change(function(){
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
            $(".report-status").removeClass("hidden");
            $(".report-status select").prop("disabled", false);
            $(".report-payment, .report-range, .report-from-date, .report-to-date").addClass("hidden");
            $(".report-payment select, .report-range select, .report-from-date select, .report-to-date select").prop("disabled", true);
            //Category & Models
            $("select#reportCategory").prop("disabled" , false);
            $(".multiselect input").prop('disabled', false);
            $('.multiselect').css('background-color', '#fff');
            $("select#reportPayment").val('prepaid');
        } else if($(this).val() === "sales") {
            $(".report-payment, .report-range").removeClass("hidden");
            $(".report-payment select, .report-range select").prop("disabled", false);
            $(".report-status, .report-from-date, .report-to-date").addClass("hidden");
            $(".report-status select, .report-from-date select, .report-to-date select").prop("disabled", true);
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
});