$(document).ready(function(){
    var item = new Array();
    var itemList = new Array();
    var itemListRequired = new Array();
    var passedValidation = false;
    var isValidCategoryName;
    var isValidWarrantyName;
    
    //Validator Initialization
    //$('#modelForm , #bulkForm').validate();
    
    //Preventing enter on Model and Bulk forms
    //$('#modelForm , #bulkForm').bind('keypress', function(e){
      //  if ( e.which == 13 ) e.preventDefault();
    //});
    
    //$('#alert-message').html(alertDangerMessage($('#validationMessage').val()));
    $('.btn-popover').popover();
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
            $('a.btn-delete-confirm').attr('href' , '/'+entityType+'/delete/'+entityId);
            $('label#entityName').html(entityName);
            $('#deleteConfirmation').modal('show');
    });

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
            $('#warranty-field select').prop("disabled", false);
            $('#warranty-field').removeClass('hidden');
        } else {
            $('#warranty-field select').prop("disabled", true);
            $('#warranty-field').addClass('hidden');
        }
    });

    $('#bulkDate').datepicker().on('changeDate', function(){
        $(this).focus().datepicker('hide');
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

    // Discount button click
    $('.btn-discount').click(function(){
        $('.discount-field').removeClass('hidden');
        $(this).addClass('hidden');
    });

    // Add item to list
    $('.btn-add-item').click(function(){
        var itemSerial = $('#itemSerial').val();
        var itemBrand = $('#itemBrand').html();
        if(itemSerial == ''){
            $('#alert-message').html(alertInfoMessage('Please enter item serial.'));
        } else if(itemBrand == '') {
            $('#alert-message').html(alertInfoMessage('Check item first.'));
        } else if(itemList.length > 0){
            var flag = false;
            var itemIndex;
            $.each(itemList , function(index , value){
                if(value.i_item_serial===item.i_item_serial){
                    flag = true;
                    itemIndex = index+1;
                    return false;
                }
            });
            if(flag === true){
                $('#alert-message').html(alertInfoMessage('Item already added to list.'));
                $(".table-view-sale tr:eq("+ itemIndex +")").effect("highlight", {}, 1500);
                clearItem();
            } else {
                addItem();
            }
        } else {
            addItem();
        }
    });

    // Remove item from list
    $('.table-view-sale').on('click' , 'button.btn-remove-item' , function(){
        var itemSerial = $(this).closest('tr').children()[0].innerHTML;
        removeItem(itemSerial);
        $('#alert-message').html(alertInfoMessage('Item removed from list.'));
        $(this).closest('tr').remove();
    });

    // Load item details in sale page
    $('#item-serial-sale').click(function () {
        var itemSerial = $('#itemSerial').val();
        if(itemSerial == '')
            $('#alert-message').html(alertInfoMessage('Please enter item serial.'));
        else {
            var btn = $(this);
            btn.button('loading');
            $.ajax({
                    url: "/item/get",
                    type: "post",
                    data: {serial:itemSerial},
                    success: function(response){
                        if(response.count===1 && response.item.i_item_status==='in_stock'){
                            item = response.item;
                            $('#itemBrand').html(response.item.m_model_brand);
                            $('#itemModel').html(response.item.m_model_model);
                            $('#itemCategory').html(response.item.c_category_name);
                            $('#itemPrice').html(response.item.b_bulk_price+' L.E.');
                            $('#alert-message').html(alertSuccessMessage('Item is available.'));
                            $('.alert-serial-sale').removeClass('alert-info').removeClass('alert-danger').addClass('alert-success');
                            $('.btn-discount').removeClass('hidden');
                        } else {
                            $('#alert-message').html(alertDangerMessage('Item is not found.'));
                            $('.alert-serial-sale').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
                        }
                    }
                }).always(function () {
                        btn.button('reset');
            });
        }
    });

    // Check Item Serial
    $('#editModalContainer').on('click', 'button.btn-item-serial', function () {
        var itemSerial = $('.input-item-serial').val();
        var btn = $(this);

        validateItemSerial(btn, itemSerial, function(itemStatus){
            if(itemStatus === 1){
                $('.alert-message-modal').html(alertSuccessMessage(lang['Item is available.']));
                $('.item-serial-container').removeClass('alert-info').addClass('alert-success');
            } else {
                $('.alert-message-modal').html(alertDangerMessage(lang['Item is not found.']));
                $('.item-serial-container').removeClass('alert-info').addClass('alert-danger');
            }
        });
    });

    $('.btn-item-edit').on('click', function () {
        var itemId = $(this).children()[0].value;
        var btn = $(this);
        var data = {itemId:itemId};
        btn.button('loading');
        $('#editModalContainer').load("/item/edit" , data , function(){
            btn.button('reset');
            $('#editModal').modal('show');
            $('#itemForm').validate({
                submitHandler: function(form){
                    form.submit();
                },
                rules: {
                    itemSerial: {
                        required: true,
                        alphanumericspace: true,
                    },
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
        });
    });

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
                }
        }).always(function () {
                    btn.button('reset');
        });
        e.preventDefault(); // prevents default
        return false;
    });
        
    // Check Bulk Serial
    $('#bulkForm #model-serial').click(function () {
        return validateBulkSerial();
    });
    
    // Check Model Serial
    $('#modelForm #model-serial').click(function () {
        return validateModelSerial();
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
    
    //Validate Category Name
    $.validator.addMethod("validcategoryname", function(value, element) {
        var categoryName = $('#categoryName').val();
        $.ajax({
            url: "/category/find",
            type: "post",
            data: {categoryName:categoryName},
            success: function(response){
                if(response.category.count===1){
                    if($('#action').val() === 'edit'){
                        var categoryId = $('#categoryId').val();
                        if(categoryId == response.category.c_id){
                            isValidCategoryName = true;
                            $(element).text(lang['ok']).addClass('valid')
                                .closest('.form-group').removeClass('has-error').addClass('has-success');
                        } else {
                            isValidCategoryName = false;
                            $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                        }
                    } else {
                        isValidCategoryName = false;
                        $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                    }
                } else {
                    isValidCategoryName = true;
                    $(element).text(lang['ok']).addClass('valid')
                        .closest('.form-group').removeClass('has-error').addClass('has-success');
                }
            }
        });
        if(isValidCategoryName) return true;
        else return false;
    }, "Category already exists.");

    //Validate Warranty Name
    $.validator.addMethod("validwarrantyname", function(value, element) {
        var warrantyName = $('#warrantyName').val();
        $.ajax({
            url: "/warranty/find",
            type: "post",
            data: {warrantyName:warrantyName},
            success: function(response){
                if(response.warranty.count===1){
                    if($('#action').val() === 'edit'){
                        var warrantyId = $('#warrantyId').val();
                        if(warrantyId == response.warranty.w_id){
                            isValidWarrantyName = true;
                            $(element).text(lang['ok']).addClass('valid')
                                .closest('.form-group').removeClass('has-error').addClass('has-success');
                        } else {
                            isValidWarrantyName = false;
                            $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                        }
                    } else {
                        isValidWarrantyName = false;
                        $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                    }
                } else {
                    isValidWarrantyName = true;
                    $(element).text(lang['ok']).addClass('valid')
                        .closest('.form-group').removeClass('has-error').addClass('has-success');
                }
            }
        });
        if(isValidWarrantyName) return true;
        else return false;
    }, "Warranty already exists.");

    //Validate Alphanumeric with Space and Arabic Characters
    $.validator.addMethod("alphanumericspace", function(value, element) {
        return this.optional(element) || /^[a-z0-9\u0600-\u06FF\-\s]+$/i.test(value);
    }, "The field must contain only letters, numbers, or dashes.");

    //Validate Date to YYYY-MM-DD format
    $.validator.addMethod("regexdate", function(value, element) {          
        return this.optional(element) || /^(19|20)\d\d[\-\/.](0[1-9]|1[012])[\-\/.](0[1-9]|[12][0-9]|3[01])$/.test(value);
    }, "Please enter a valid date. (e.g. 2014-02-16)");

    //Validate Category
    $('#categoryForm input').keypress(function(){
        $('#categoryForm').validate({
            submitHandler: function(form){
                form.submit();
            },
            rules: {
                categoryName: {
                    required: true,
                    alphanumericspace: true,
                    validcategoryname: true,
                }
            },
            highlight: function (element) {
                $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
            },
            success: function (element) {
                $(element).text(lang['ok']).addClass('valid')
                    .closest('.form-group').removeClass('has-error').addClass('has-success');
            }
        }).element('input#categoryName');
    });

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

    //Validate Warranty
    $('#warrantyForm input').keypress(function(){
        $('#warrantyForm').validate({
                submitHandler: function(form){
                    form.submit();
                },
                rules: {
                    warrantyName: {
                        required: true,
                        alphanumericspace: true,
                        validwarrantyname: true,
                    }
                },
                highlight: function (element) {
                    $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                },
                success: function (element) {
                    $(element).text(lang['ok']).addClass('valid')
                        .closest('.form-group').removeClass('has-error').addClass('has-success');
                }
        }).element('input#warrantyName');
    });

    //Functions
    function validateItemSerial(btn , itemSerial , handleData){
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
    }

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
        var itemDiscount = $('#itemDiscount').val();
        item['s_sale_discount'] = itemDiscount;
        itemList.push(item);
        var tableRow = "<tr>" +
                            "<td>"+item.i_item_serial+"</td>" +
                            "<td>"+item.m_model_brand+"</td>" +
                            "<td>"+item.m_model_model+"</td>" +
                            "<td>"+item.c_category_name+"</td>" +
                            "<td>"+item.b_bulk_price+"</td>" +
                            "<td>"+item.s_sale_discount+"</td>" +
                            "<td>" +
                                "<button class='btn btn-danger btn-xs btn-remove-item'>Remove</button>" +
                            "</td>" +
                        "</tr>";
        $('.table-view-sale').append(tableRow);
        clearItem();
        $('#alert-message').html(alertInfoMessage('Item added to list.'));
        itemListRequired = new Array();
        $.each(itemList , function (index, value){
            var requiredItem = {itemId: value.i_id , itemDiscount: value.s_sale_discount};
            itemListRequired.push(requiredItem);
            $('#itemList').val(JSON.stringify(itemListRequired));
        });
        //console.log(itemListRequired);
    }
    function removeItem(itemSerial){
        $.each(itemList , function(index , value){
            if(value.i_item_serial===itemSerial){
                itemList.splice(index , 1);
                return false;
            }
        });
    }
    function clearItem(){
        $('#itemSerial').val('');
        $('#itemBrand').empty();
        $('#itemModel').empty();
        $('#itemCategory').empty();
        $('#itemPrice').empty();
        $('#itemDiscount').val(0);
        $('.btn-discount').addClass('hidden');
        $('.discount-field').addClass('hidden');
        $('.alert-serial-sale').removeClass('alert-success').addClass('alert-info');
    }
    
});