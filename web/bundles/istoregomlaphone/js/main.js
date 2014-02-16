var item = new Array();
var itemList = new Array();
var itemListRequired = new Array();
$(document).ready(function(){
        //Validate Alphanumeric with Space and Arabic Characters
        $.validator.addMethod("alphanumericspace", function(value, element) {
            return this.optional(element) || /^[a-z0-9\u0600-\u06FF\-\s]+$/i.test(value);
        }, "The field must contain only letters, numbers, or dashes.");

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
	    $(this).datepicker('hide');
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
        
        // Check Model Serial
	$('#model-serial').click(function () {
            var action = $('#action').val();
            var controller = $('#controller').val();
            var modelSerial = $('#modelSerial').val();
	    var btn = $(this);
	    btn.button('loading');
	    $.ajax({
			url: "/model/find",
			type: "post",
                        data: {serial:modelSerial},
			success: function(response){
                                if(response.model.count===1){
                                    if (controller === 'bulk'){
                                        $('#bulkBrand').val(response.model.m_model_brand);
                                        $('#bulkModel').val(response.model.m_model_model);
                                        $('#bulkCategory').val(response.model.c_category_name);
                                        $('#alert-message').html(alertSuccessMessage('Model is available.'));
                                        $('.alert-serial-model').removeClass('alert-info').removeClass('alert-danger').addClass('alert-success');
                                    } else {
                                        $('#alert-message').html(alertDangerMessage('Model is already found.'));
                                        $('.alert-serial-model').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
                                    }
                                } else {
                                    if (controller === 'bulk'){
                                        $('#bulkBrand').val('');
                                        $('#bulkModel').val('');
                                        $('#bulkCategory').val('');
                                        $('#alert-message').html(alertDangerMessage('Model is not found.'));
                                        $('.alert-serial-model').removeClass('alert-info').removeClass('alert-success').addClass('alert-danger');
                                    } else {
                                        $('#alert-message').html(alertSuccessMessage('Model is available.'));
                                        $('.alert-serial-model').removeClass('alert-info').removeClass('alert-danger').addClass('alert-success');
                                    }
                                }
			}
		}).always(function () {
			btn.button('reset');
	    });
	});
        
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
                            number: true,
                        },
                        itemNotes: {
                            alphanumericspace: true
                        }
                    },
                    highlight: function (element) {
                        $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                    },
                    success: function (element) {
                        element.text(lang['ok']).addClass('valid')
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
                                    $('.alert-message-modal').html(alertDangerMessage('Customer is not found.'));
                                }
			}
		}).always(function () {
			btn.button('reset');
	    });
            e.preventDefault(); // prevents default
            return false;
        });
        
        
        $('#categoryForm').validate({
                submitHandler: function(form){
                    form.submit();
                },
                rules: {
                    categoryName: {
                        required: true,
                        alphanumericspace: true,
                    }
                },
                highlight: function (element) {
                    $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                },
                success: function (element) {
                    element.text(lang['ok']).addClass('valid')
                        .closest('.form-group').removeClass('has-error').addClass('has-success');
                }
        });
        
        
        $('#supplierForm').validate({
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
                        required: true,
                        email: true,
                    },
                    supplierAddress: {
                        required: true,
                        minlength: 2,
                        alphanumericspace: true,
                    },
                },
                highlight: function (element) {
                    $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                },
                success: function (element) {
                    element.text(lang['ok']).addClass('valid')
                        .closest('.form-group').removeClass('has-error').addClass('has-success');
                }
        });
        
        $('#warrantyForm').validate({
                submitHandler: function(form){
                    form.submit();
                },
                rules: {
                    warrantyName: {
                        required: true,
                        alphanumericspace: true,
                    }
                },
                highlight: function (element) {
                    $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                },
                success: function (element) {
                    element.text(lang['ok']).addClass('valid')
                        .closest('.form-group').removeClass('has-error').addClass('has-success');
                }
        });
        
        $('#modelForm').validate({
                submitHandler: function(form){
                    form.submit();
                },
                rules: {
                    modelSerial: {
                        required: true,
                        number: true,
                    },
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
                    element.text(lang['ok']).addClass('valid')
                        .closest('.form-group').removeClass('has-error').addClass('has-success');
                }
        });
        
        $('#bulkForm').validate({
                submitHandler: function(form){
                    form.submit();
                },
                rules: {
                    modelSerial: {
                        required: true,
                        number: true,
                    },
                    bulkPrice: {
                        required: true,
                        number: true,
                    },
                    bulkQuantity: {
                        required: true,
                        number: true,
                    },
                    bulkSpecs: {
                        alphanumericspace: true,
                    },
                },
                highlight: function (element) {
                    $(element).closest('.form-group').removeClass('has-success').addClass('has-error');
                },
                success: function (element) {
                    element.text(lang['ok']).addClass('valid')
                        .closest('.form-group').removeClass('has-error').addClass('has-success');
                }
        });
});

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
    console.log(itemListRequired);
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
