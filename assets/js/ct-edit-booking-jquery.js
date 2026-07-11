var ser_ids_dynamic = 0;
/* tooltipster jquery */
jQuery(document).ready(function () {
    jQuery('.ct-tooltip').tooltipster({
        animation: 'grow',
        delay: 20,
        theme: 'tooltipster-shadow',
        trigger: 'hover'
    });
    jQuery('.ct-tooltipss').tooltipster({
        animation: 'grow',
        delay: 20,
        theme: 'tooltipster-shadow',
        trigger: 'hover'
    });
    jQuery('.ct-tooltip-services').tooltipster({
        animation: 'grow',
        side: 'bottom',
        interactive: 'true',
        theme: 'tooltipster-shadow',
        trigger: 'hover',
        delayTouch: 300,
        maxWidth: 400,
        functionPosition: function (instance, helper, position) {
            position.coord.top -= 70;
            return position;
        },
        contentAsHTML: 'true'
    });
});

var ct_postalcode_status_check = 'N';
var guest_user_status = 'off';
/* scroll to next step */
jQuery(document).ready(function () {
    jQuery('.ct-service').on('click', function () {
        jQuery('html, body').stop().animate({
            'scrollTop': jQuery('.ct-scroll-meth-unit').offset().top - 30
        }, 800, 'swing', function () { });
    });
});

jQuery(document).ready(function () {
    jQuery('body').niceScroll();
    jQuery('.common-data-dropdown').niceScroll();
    jQuery('.ct-services-dropdown').niceScroll();
});

jQuery(document).ready(function () {
    jQuery('.ct-loading-main').hide();
    var subheader_status = subheaderObj.subheader_status;
    if (subheader_status == 'Y') {
        jQuery('.ct-sub').show();
    } else {
        jQuery('.ct-sub').hide();
        jQuery('.ct-sub-complete-booking').html('<br>');
    }
    if (ct_postalcode_status_check == 'Y') {
        jQuery('.ct_remove_id').attr('id', '');
        jQuery(document).on('click', '.ct_remove_id', function () {
            jQuery('#ct_postal_code').focus();
            jQuery('#ct_postal_code').keyup();
        });
    }
    jQuery('.ct-loading-main').hide();
    jQuery('.remove_guest_user_preferred_email').hide();
    jQuery('.show_methods_after_service_selection').hide();
    jQuery('.freq_discount_display').hide();
    jQuery('.edit_hide_allsss_addons').hide();
    /*jQuery('.partial_amount_hide_on_load').hide();*/
    jQuery('.hide_right_side_box').hide();
    jQuery('.client_logout').hide();
    jQuery('.postal_code_error').show();
    jQuery('.postal_code_error').html(errorobj_please_enter_postal_code);
    jQuery('.hideservice_name').hide();
    jQuery('.hidedatetime_value').hide();
    jQuery('.hideduration_value').hide();
    jQuery('.edit_s_m_units_design_1').hide();
    jQuery('.edit_s_m_units_design_2').hide();
    jQuery('.edit_s_m_units_design_3').hide();
    jQuery('.edit_s_m_units_design_4').hide();
    jQuery('.edit_s_m_units_design_5').hide();

});

/* dropdown services list */
/* services dropdown show hide list */
jQuery(document).on("click", ".service-is", function () {
    jQuery(".ct-services-dropdown").toggle("blind", { direction: "vertical" }, 300);
});

jQuery(document).on("click", ".select_service", function () {
    jQuery("#ct_selected_service").html(jQuery(this).html());
    jQuery(".ct-services-dropdown").hide("blind", { direction: "vertical" }, 300);
});

/* select hours based service */
jQuery(document).on("click", ".ct-duration-btn", function () {
    jQuery('.ct-duration-btn').each(function () {
        jQuery(this).removeClass('duration-box-selected');
    });
    jQuery(this).addClass('duration-box-selected');
});


/* for show how many addon counting when checked */
jQuery(document).ready(function () {
    jQuery('input[type="checkbox"]').click(function () {
        if (jQuery('.addon-checkbox').is(':checked')) {
            jQuery('.common-selection-main.addon-select').show();
        }
        else {
            jQuery('.common-selection-main.addon-select').hide();
        }
    });
});


/* addons */
jQuery(document).on("click", ".ct-addon-btn", function () {
    var curr_methodname = jQuery(this).data('method_name');
    jQuery('.ct-addon-btn').each(function () {
        if (jQuery(this).data('method_name') == curr_methodname) {
            jQuery(this).removeClass('ct-addon-selected');
        }
    });
    jQuery(this).addClass('ct-addon-selected');
});

/* see more instructions in service popup */
jQuery(document).ready(function () {
    jQuery(".show-more-toggler").click(function () {
        jQuery(".bullet-more").toggle("blind", { direction: "vertical" }, 500);
        jQuery(".show-more-toggler:after").addClass('rotate');
    });
});

jQuery(document).ready(function () {
    jQuery('.panel-collapse').on('show.bs.collapse', function () {
        jQuery(this).siblings('.panel-heading').addClass('active');
    });

    jQuery('.panel-collapse').on('hide.bs.collapse', function () {
        jQuery(this).siblings('.panel-heading').removeClass('active');
    });
});

/************* Code by developer side --- ****************/

jQuery(document).on('click', '#edit_complete_bookings', function (e) {
    jQuery('.ct-loading-main').show();
    jQuery('.ct_all_booking_errors').css('display', 'none');

    var site_url = siteurlObj.site_url;
    var ajax_url = ajaxurlObj.ajax_url;
    var front_url = fronturlObj.front_url;

    /** new **/
    var staff_id = jQuery('.provider_disable:checked').data('staff_id');

    if (staff_id == undefined) {
        var staff_id = '';
    }
    else {
        var staff_id = staff_id;
    }

    var currency_symbol = jQuery(this).data('currency_symbol');

    var cart_sub_total = jQuery('.cart_sub_total').text();
    var amount = cart_sub_total.replace(currency_symbol, '');

    var cart_discount = jQuery('.cart_discount').text().substring(2);
    var discount = cart_discount.replace(currency_symbol, '');

    var cart_tax = jQuery('.cart_tax').text();
    var taxes = cart_tax.replace(currency_symbol, '');

    var cart_total = jQuery('.cart_total').text();
    var net_amount = cart_total.replace(currency_symbol, '');

    var cart_counting = jQuery("#total_cart_count").val();
    var order_id = jQuery(this).attr("data-order_id");

    var frequently_discount_id = jQuery(".edit_frequently_discount_radio").val();
    var frequent_discount_amount = 0;
    var recurrence_booking_1 = 'N';
    if (frequently_discount_id != "1") {
        recurrence_booking_1 = 'Y';
        var frequent_discount_text = jQuery('.frequent_discount').text();
        frequent_discount_amount = frequent_discount_text.replace(currency_symbol, '');
    }

    var no_units_in_cart_err = jQuery('#no_units_in_cart_err').val();
    var no_units_in_cart_err_count = jQuery('#no_units_in_cart_err_count').val();

    var payment_method = jQuery(".payment_method").val();

    dataString = { order_id: order_id, payment_method: payment_method, staff_id: staff_id, amount: amount, discount: discount, taxes: taxes, net_amount: net_amount, frequently_discount: frequently_discount_id, frequent_discount_amount: frequent_discount_amount, action: "edit_complete_bookings" };

    if (jQuery("input[name='service-radio']:checked").val() != 'on' && jQuery("#ct-service-0").val() != 'off' && cart_counting == 1) {
        clicked = false;
        jQuery('.ct-loading-main').hide();
        jQuery('.ct_all_booking_errors').css('display', 'block');
        jQuery('.ct_all_booking_errors').css('color', 'red');
        jQuery('.ct_all_booking_errors').html(errorobj_please_select_a_service);
    } else if (jQuery('.ser_name_for_error').text() == 'Cleaning Service' && cart_counting == 1) {
        clicked = false;
        jQuery('.ct-loading-main').hide();
        jQuery('.ct_all_booking_errors').css('display', 'block');
        jQuery('.ct_all_booking_errors').css('color', 'red');
        jQuery('.ct_all_booking_errors').html(errorobj_please_select_a_service);
    } else if (jQuery('#ct_selected_servic_method .service-method-name').text() == 'Service Usage Methods' && cart_counting == 1) {
        clicked = false;
        jQuery('.ct-loading-main').hide();
        jQuery('.ct_all_booking_errors').css('display', 'block');
        jQuery('.ct_all_booking_errors').css('color', 'red');
        jQuery('.ct_all_booking_errors').html(errorobj_please_select_method);
    } else if (cart_counting == 1) {
        clicked = false;
        jQuery('.ct-loading-main').hide();
        jQuery('.ct_all_booking_errors').css('display', 'block');
        jQuery('.ct_all_booking_errors').css('color', 'red');
        jQuery('.ct_all_booking_errors').html(errorobj_please_select_units_or_addons);
    } else if (no_units_in_cart_err == 'units_and_addons_both_exists' && no_units_in_cart_err_count == 'unit_not_added') {
        clicked = false;
        jQuery('.ct-loading-main').hide();
        jQuery('.ct_all_booking_errors').css('display', 'block');
        jQuery('.ct_all_booking_errors').css('color', 'red');
        jQuery('.ct_all_booking_errors').html(errorobj_please_select_atleast_one_unit);
    } else if (jQuery('#check_login_click').val() == 'not' && jQuery('#existing-user').prop("checked") == true) {
        clicked = false;
        jQuery('.ct-loading-main').hide();
        jQuery('.ct_all_booking_errors').css('display', 'block');
        jQuery('.ct_all_booking_errors').css('color', 'red');
        jQuery('.ct_all_booking_errors').html(errorobj_please_login_to_complete_booking);
    } else {
        if (clicked === false) {
            clicked = true;
            jQuery('.ct-loading-main').show();
            jQuery.ajax({
                type: "POST",
                url: front_url + "edit_booking_checkout.php",
                data: dataString,
                success: function (response) {
                    if (jQuery.trim(response) == 'ok') {
                        location.reload();
                    }
                }
            });

        } else {
            e.preventDefault();
        }
    }

    jQuery('.ct-loading-main').hide();
    clicked = false;

    jQuery('.add_show_error_class').each(function () {
        jQuery(this).trigger('keyup');
    });

});

/* dropdown services methods list */
/* services methods dropdown show hide list */
jQuery(document).on("click", ".service-method-is", function () {
    jQuery(".ct-services-method-dropdown").toggle("blind", { direction: "vertical" }, 300);
});

jQuery(document).on("click", ".select_service_method", function () {
    jQuery("#ct_selected_servic_method").html(jQuery(this).html());
    jQuery(".ct-services-method-dropdown").hide("blind", { direction: "vertical" }, 300);
    jQuery('#ct_selected_servic_method h3').removeClass('edit_s_m_units_design');
});

jQuery(document).on('click', '.edit_ser_details', function () {
    if (ser_ids_dynamic != 0) {
        var service_ul = '.cart_item_listing' + ser_ids_dynamic;

        if ((jQuery("ul" + service_ul).has("li").length === 0)) {
            // jQuery( ".cart_service_item_listing_li"+ser_ids_dynamic).remove();
        }
    }

    jQuery(":input", this).prop('checked', true);
    jQuery('.ct-loading-main').show();
    jQuery('.hideduration_value').hide();
    jQuery('.total_time_duration_text').html('');
    jQuery('.show_methods_after_service_selection').show();
    jQuery('.edit_ct_method_tab-slider-tabs').removeClass('ct_methods_slide');
    jQuery('.service_not_selected_error_d2').removeAttr('style', '');
    jQuery('.service_not_selected_error_d2').html(errorobj_please_select_a_service);
    jQuery('.service_not_selected_error').hide();
    jQuery('.partial_amount_hide_on_load').hide();
    jQuery('.hide_right_side_box').hide();
    jQuery('.freq_disc_empty_cart_error').hide();
    jQuery('.edit_s_m_units_design_1').hide();
    jQuery('.edit_s_m_units_design_2').hide();
    jQuery('.edit_s_m_units_design_3').hide();
    jQuery('.edit_s_m_units_design_4').hide();
    jQuery('.edit_s_m_units_design_5').hide();
    jQuery('.hideservice_name').show();
    jQuery('.show_select_staff_title').show();
    jQuery(".cart_empty_msg").hide();
    jQuery('.cart_toggle_content').addClass('addon_toggle open');

    var site_url = siteurlObj.site_url;
    var ajax_url = ajaxurlObj.ajax_url;
    var id = jQuery(this).data('id');
    var name = jQuery(this).data('servicetitle');
    var chklis = "1";

    jQuery('.cart_toggle_head').each(function () {
        var nID = jQuery(this).data('id');
        if (id == nID) {
            chklis = "2";
        }
    });

    if (chklis == "1") {
        ser_ids_dynamic = id;
        ser_ids_lis = "cart_service_item_listing_li" + id;
        ser_ids_uls = "cart_item_listing" + id;
        ser_ids_divs = "cart_toggle_content_" + id;
        ser_ids_iis = "ct_icon_check_" + id;

        var lidtls = "<li class=" + ser_ids_lis + " data-id='" + ser_ids_dynamic + "'><h4 class='cart_toggle_head' data-id='" + ser_ids_dynamic + "' >" + name + "<i class='fa fa-plus pull-right " + ser_ids_iis + "'></i></h4><div id='" + ser_ids_divs + "' style='overflow:hidden;' class='cart_toggle_content'><ul class='ct-addon-items-list " + ser_ids_uls + "'></ul></div></li>";

        jQuery(".edit_cart_service_item_listing").append(lidtls);
        jQuery("#" + ser_ids_divs).slideUp();
    }

    jQuery('.addon_qty').each(function () {
        jQuery(this).val(0);
        jQuery('.edit_add_minus_button').hide();
    });

    if (jQuery('.ser_name_for_error').text() != 'Cleaning Service' && jQuery('.service-method-name').text() == 'Service Usage Methods') {
        // jQuery('.method_not_selected_error').css('display','block');
        // jQuery('.method_not_selected_error').css('color','red');
        // jQuery('.method_not_selected_error').html("Please Select Method");
    } else if (jQuery("input[name='service-radio']:checked").val() == 'on' && jQuery('.service-method-name').text() == 'Service Usage Methods') {
        // jQuery('.method_not_selected_error').css('display','block');
        // jQuery('.method_not_selected_error').css('color','red');
        // jQuery('.method_not_selected_error').html("Please Select Method");
    }

    // Use promises for better AJAX handling
    var methodsAjax = jQuery.ajax({
        type: 'post',
        data: {
            'service_id': id,
            'operationgetmethods': 1
        },
        url: ajax_url + "edit_booking_frontajax.php"
    });

    var addonsAjax = jQuery.ajax({
        type: 'post',
        data: {
            'service_id': id,
            'get_service_addons': 1
        },
        url: ajax_url + "edit_booking_frontajax.php"
    });

    jQuery.when(methodsAjax, addonsAjax).done(function (methodsRes, addonsRes) {
        var methodsData = jQuery.parseJSON(methodsRes[0]);
        var addonsData = addonsRes[0];

        jQuery('.ct-loading-main').hide();

        if (methodsData.status == 'single') {
            jQuery('.edit-services-method-list-dropdown').hide();
            jQuery('.edit_show_single_service_method').html(methodsData.m_html);
            jQuery('.edit_s_m_units_design').trigger('click');
            jQuery('#method_not_selected_error').hide();

            jQuery.ajax({
                type: 'post',
                data: {
                    'service_id': id,
                    'staff_select_according_service': 1
                },
                url: ajax_url + "edit_booking_frontajax.php",
                success: function (res) {
                    var searchSessionData = jQuery.parseJSON(res);
                    if (searchSessionData.found_status == 'found') {
                        jQuery('.edit-ct-provider-list').show();
                        var searchStaffId = searchSessionData.staff_id;
                        jQuery.ajax({
                            type: "POST",
                            url: ajax_url + "edit_booking_frontajax.php",
                            data: {
                                'staff_search': searchStaffId,
                                'get_search_staff_detail': 1
                            },
                            success: function (res) {
                                jQuery('.edit-provders-list').html(res);
                            }
                        });
                    } else if (searchSessionData.found_status == 'not found') {
                        jQuery('.edit-ct-provider-list').hide();
                    }
                }
            });

        } else {
            jQuery('.edit_show_single_service_method').html(methodsData.m_html);
            jQuery('.edit_ct_method_tab-slider-tabs li:first').trigger('click');

            jQuery.ajax({
                type: 'post',
                data: {
                    'service_id': id,
                    'staff_select_according_service': 1
                },
                url: ajax_url + "edit_booking_frontajax.php",
                success: function (res) {
                    var searchSessionData = jQuery.parseJSON(res);
                    if (searchSessionData.found_status == 'found') {
                        jQuery('.edit-ct-provider-list').show();
                        var searchStaffId = searchSessionData.staff_id;
                        jQuery.ajax({
                            type: "POST",
                            url: ajax_url + "edit_booking_frontajax.php",
                            data: {
                                'staff_search': searchStaffId,
                                'get_search_staff_detail': 1
                            },
                            success: function (res) {
                                jQuery('.edit-provders-list').html(res);
                            }
                        });
                    } else if (searchSessionData.found_status == 'not found') {
                        jQuery('.edit-ct-provider-list').hide();
                    }
                }
            });
        }

        if (addonsData == 'Extra Services Not Available') {
            jQuery('.edit_hide_allsss_addons').hide();
        } else {
            jQuery('.edit_hide_allsss_addons').show();
            jQuery('.edit_add_on_lists').html(addonsData);
            jQuery('.edit_add_minus_button').hide();
        }
    }).fail(function (jqXHR, textStatus, errorThrown) {
        console.log('AJAX Error:', errorThrown);
        // Handle AJAX failure/error here
    });

    jQuery(".remove_service_class").each(function () {
        jQuery(this).addClass("edit_ser_details");
    });

    jQuery(this).removeClass("edit_ser_details");
    return false;
});


// jQuery(document).on('click', '.edit_ser_details', function () {
//     if (ser_ids_dynamic != 0) {
//         var service_ul = '.cart_item_listing' + ser_ids_dynamic;

//         if ((jQuery("ul" + service_ul).has("li").length === 0)) {
//             // jQuery( ".cart_service_item_listing_li"+ser_ids_dynamic).remove();
//         }
//     }
//     jQuery(":input", this).prop('checked', true);
//     jQuery('.ct-loading-main').show();
//     jQuery('.hideduration_value').hide();
//     jQuery('.total_time_duration_text').html('');
//     jQuery('.show_methods_after_service_selection').show();
//     jQuery('.edit_ct_method_tab-slider-tabs').removeClass('ct_methods_slide');
//     jQuery('.service_not_selected_error_d2').removeAttr('style', '');
//     jQuery('.service_not_selected_error_d2').html(errorobj_please_select_a_service);
//     jQuery('.service_not_selected_error').hide();
//     jQuery('.partial_amount_hide_on_load').hide();
//     jQuery('.hide_right_side_box').hide();
//     jQuery('.freq_disc_empty_cart_error').hide();
//     jQuery('.edit_s_m_units_design_1').hide();
//     jQuery('.edit_s_m_units_design_2').hide();
//     jQuery('.edit_s_m_units_design_3').hide();
//     jQuery('.edit_s_m_units_design_4').hide();
//     jQuery('.edit_s_m_units_design_5').hide();
//     jQuery('.hideservice_name').show();
//     jQuery('.show_select_staff_title').show();
//     jQuery(".cart_empty_msg").hide();
//     jQuery('.cart_toggle_content').addClass('addon_toggle open');

//     var site_url = siteurlObj.site_url;
//     var ajax_url = ajaxurlObj.ajax_url;
//     var id = jQuery(this).data('id');
//     var name = jQuery(this).data('servicetitle');

//     jQuery('.cart_toggle_head').each(function () {
//         var nID = jQuery(this).data('id');
//         if (id == nID) {
//             chklis = "2";
//         }
//     });

//     if (chklis == "1") {
//         ser_ids_dynamic = id;
//         ser_ids_lis = "cart_service_item_listing_li" + id;
//         ser_ids_uls = "cart_item_listing" + id;
//         ser_ids_divs = "cart_toggle_content_" + id;
//         ser_ids_iis = "ct_icon_check_" + id;

//         var lidtls = "<li class=" + ser_ids_lis + " data-id='" + ser_ids_dynamic + "'><h4 class='cart_toggle_head' data-id='" + ser_ids_dynamic + "' >" + name + "<i class='fa fa-plus pull-right " + ser_ids_iis + "'></i></h4><div id='" + ser_ids_divs + "' style='overflow:hidden;' class='cart_toggle_content'><ul class='ct-addon-items-list " + ser_ids_uls + "'></ul></div></li>";

//         jQuery(".edit_cart_service_item_listing").append(lidtls);
//         jQuery("#" + ser_ids_divs).slideUp();
//     }

//     jQuery('.addon_qty').each(function () {
//         jQuery(this).val(0);
//         jQuery('.edit_add_minus_button').hide();
//     });

//     if (jQuery('.ser_name_for_error').text() != 'Cleaning Service' && jQuery('.service-method-name').text() == 'Service Usage Methods') {
//         // jQuery('.method_not_selected_error').css('display','block');
//         // jQuery('.method_not_selected_error').css('color','red');
//         // jQuery('.method_not_selected_error').html("Please Select Method");
//     } else if (jQuery("input[name='service-radio']:checked").val() == 'on' && jQuery('.service-method-name').text() == 'Service Usage Methods') {
//         // jQuery('.method_not_selected_error').css('display','block');
//         // jQuery('.method_not_selected_error').css('color','red');
//         // jQuery('.method_not_selected_error').html("Please Select Method");
//     }

//     var methodsAjax = jQuery.ajax({
//         type: 'post',
//         data: {
//             'service_id': id,
//             'operationgetmethods': 1
//         },
//         url: ajax_url + "edit_booking_frontajax.php"
//     });

//     var addonsAjax = jQuery.ajax({
//         type: 'post',
//         data: {
//             'service_id': id,
//             'get_service_addons': 1
//         },
//         url: ajax_url + "edit_booking_frontajax.php"
//     });

//     jQuery.when(methodsAjax, addonsAjax).done(function (methodsRes, addonsRes) {
//         var methodsData = jQuery.parseJSON(methodsRes[0]);
//         var addonsData = addonsRes[0];

//         jQuery('.ct-loading-main').hide();

//         if (methodsData.status == 'single') {
//             jQuery('.edit-services-method-list-dropdown').hide();
//             jQuery('.edit_show_single_service_method').html(methodsData.m_html);
//             jQuery('.edit_s_m_units_design').trigger('click');
//             jQuery('#method_not_selected_error').hide();

//             jQuery.ajax({
//                 type: 'post',
//                 data: {
//                     'service_id': id,
//                     'staff_select_according_service': 1
//                 },
//                 url: ajax_url + "edit_booking_frontajax.php",
//                 success: function (res) {
//                     var searchSessionData = jQuery.parseJSON(res);
//                     if (searchSessionData.found_status == 'found') {
//                         jQuery('.edit-ct-provider-list').show();
//                         var searchStaffId = searchSessionData.staff_id;
//                         jQuery.ajax({
//                             type: "POST",
//                             url: ajax_url + "edit_booking_frontajax.php",
//                             data: {
//                                 'staff_search': searchStaffId,
//                                 'get_search_staff_detail': 1
//                             },
//                             success: function (res) {
//                                 jQuery('.edit-provders-list').html(res);
//                             }
//                         });
//                     } else if (searchSessionData.found_status == 'not found') {
//                         jQuery('.edit-ct-provider-list').hide();
//                     }
//                 }
//             });

//         } else {
//             jQuery('.edit_show_single_service_method').html(methodsData.m_html);
//             jQuery('.edit_ct_method_tab-slider-tabs li:first').trigger('click');

//             jQuery.ajax({
//                 type: 'post',
//                 data: {
//                     'service_id': id,
//                     'staff_select_according_service': 1
//                 },
//                 url: ajax_url + "edit_booking_frontajax.php",
//                 success: function (res) {
//                     var searchSessionData = jQuery.parseJSON(res);
//                     if (searchSessionData.found_status == 'found') {
//                         jQuery('.edit-ct-provider-list').show();
//                         var searchStaffId = searchSessionData.staff_id;
//                         jQuery.ajax({
//                             type: "POST",
//                             url: ajax_url + "edit_booking_frontajax.php",
//                             data: {
//                                 'staff_search': searchStaffId,
//                                 'get_search_staff_detail': 1
//                             },
//                             success: function (res) {
//                                 jQuery('.edit-provders-list').html(res);
//                             }
//                         });
//                     } else if (searchSessionData.found_status == 'not found') {
//                         jQuery('.edit-ct-provider-list').hide();
//                     }
//                 }
//             });
//         }

//         if (addonsData == 'Extra Services Not Available') {
//             jQuery('.edit_hide_allsss_addons').hide();
//         } else {
//             jQuery('.edit_hide_allsss_addons').show();
//             jQuery('.edit_add_on_lists').html(addonsData);
//             jQuery('.edit_add_minus_button').hide();
//         }
//     }).fail(function (jqXHR, textStatus, errorThrown) {
//         console.log('AJAX Error:', errorThrown);
//     });

//     jQuery(".remove_service_class").each(function () {
//         jQuery(this).addClass("edit_ser_details");
//     });

//     jQuery(this).removeClass("edit_ser_details");
//     return false;
// });
jQuery(document).on('click', '.addons_servicess_2', function () {
    var id = jQuery(this).data('id');
    jQuery('.add_minus_buttonid' + id).show();
    var m_name = jQuery(this).data('mnamee');
    var value = jQuery(this).prop('checked');

    if (value == false) {
        jQuery('.qtyyy_' + m_name).val('1');
        var addon_id = jQuery(this).data('id');
        jQuery('#minus' + addon_id).trigger('click');
    } else if (value == true) {
        var addon_id = jQuery(this).data('id');
        jQuery('#add' + addon_id).trigger('click');
    }
});
/* bedroom and bathroom counting for addons */
jQuery(document).on('click', '.edit_add', function () {
    jQuery('.freq_disc_empty_cart_error').hide();
    var site_url = siteurlObj.site_url;
    var ajax_url = ajaxurlObj.ajax_url;
    var ids = jQuery(this).data('ids');
    var db_qty = jQuery(this).data('db-qty');
    var service_id = jQuery(this).data('service_id');
    var method_id = jQuery(this).data('method_id');
    var method_name = jQuery(this).data('method_name');
    var units_id = jQuery(this).data('units_id');
    var type = jQuery(this).data('type');
    var m_name = jQuery(this).data('mnamee');

    jQuery('.coupon_display').hide();
    jQuery('.hide_coupon_textbox').show();
    jQuery('.ct-display-coupon-code').hide();
    jQuery('.coupon_invalid_error').hide();
    var frequently_discount_id = jQuery(".edit_frequently_discount_radio").val();
    var qty_val = parseInt(jQuery('.qtyyy_' + m_name).val());
    var qty_vals = qty_val + 1;

    if (qty_val < db_qty) {
        jQuery('.qtyyy_' + m_name).val(qty_vals);
        var final_qty_val = qty_vals;
        jQuery.ajax({
            type: 'post',
            data: {
                'addon_id': ids,
                'qty_vals': final_qty_val,
                's_addon_units_maxlimit_4_ratesss': 1
            },
            url: ajax_url + "edit_booking_frontajax.php",
            success: function (res) {
                jQuery('.data_addon_qtyrate').attr("data-rate", res);
                jQuery.ajax({
                    type: 'post',
                    data: {
                        'method_id': method_id,
                        'service_id': service_id,
                        's_m_qty': final_qty_val,
                        's_m_rate': res,
                        'method_name': method_name,
                        'units_id': units_id,
                        'frequently_discount_id': frequently_discount_id,
                        'type': type,
                        'add_to_cart': 1
                    },
                    url: site_url + "front/edit_booking_firststep.php",
                    success: function (res) {
                        jQuery('.freq_discount_display').show();
                        jQuery('.hide_right_side_box').show();
                        /* jQuery('.partial_amount_hide_on_load').show(); */
                        jQuery('.empty_cart_error').hide();
                        var cart_session_data = jQuery.parseJSON(res);
                        jQuery('#no_units_in_cart_err').val(cart_session_data.unit_status);
                        jQuery('#no_units_in_cart_err_count').val(cart_session_data.unit_require);
                        jQuery("#total_cart_count").val('2');
                        jQuery('.coupon_invalid_error').hide();
                        if (cart_session_data.status == 'update') {
                            jQuery(".edit_cart_empty_msg").hide();
                            jQuery('.update_qty_of_s_m_' + cart_session_data.method_name_without_space).html(cart_session_data.s_m_html);
                            jQuery('.update_qty_of_s_m_' + cart_session_data.method_name_without_space).val('data-service_id', service_id);
                            jQuery('.update_qty_of_s_m_' + cart_session_data.method_name_without_space).val('data-method_id', method_id);
                            jQuery('.update_qty_of_s_m_' + cart_session_data.method_name_without_space).val('data-units_id', units_id);
                            jQuery('.partial_amount').html(cart_session_data.partial_amount);
                            jQuery('.remain_amount').html(cart_session_data.remain_amount);
                            jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                            jQuery('.cart_discount').html('- ' + cart_session_data.cart_discount);
                            jQuery('.cart_tax').html(cart_session_data.cart_tax);
                            jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                            jQuery('.cart_total').html(cart_session_data.total_amount);
                            jQuery('.total_time_duration_text').html(cart_session_data.duration_text);
                        } else if (cart_session_data.status == 'insert') {
                            jQuery('.hideduration_value').show();
                            jQuery(".edit_cart_empty_msg").hide();
                            jQuery('.cart_item_listing' + service_id).append(cart_session_data.s_m_html);
                            jQuery('.partial_amount').html(cart_session_data.partial_amount);
                            jQuery('.remain_amount').html(cart_session_data.remain_amount);
                            jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                            jQuery('.cart_discount').html('- ' + cart_session_data.cart_discount);
                            jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                            jQuery('.cart_tax').html(cart_session_data.cart_tax);
                            jQuery('.cart_total').html(cart_session_data.total_amount);
                            jQuery('.total_time_duration_text').html(cart_session_data.duration_text);
                        } else if (cart_session_data.status == 'firstinsert') {
                            jQuery('.hideduration_value').show();
                            jQuery(".edit_cart_empty_msg").hide();
                            jQuery('.cart_item_listing' + service_id).append(cart_session_data.s_m_html);
                            jQuery('.partial_amount').html(cart_session_data.partial_amount);
                            jQuery('.remain_amount').html(cart_session_data.remain_amount);
                            jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                            jQuery('.cart_discount').html('- ' + cart_session_data.cart_discount);
                            jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                            jQuery('.cart_tax').html(cart_session_data.cart_tax);
                            jQuery('.cart_total').html(cart_session_data.total_amount);
                            jQuery('.total_time_duration_text').html(cart_session_data.duration_text);
                        } else if (cart_session_data.status == 'empty calculation') {
                            jQuery('.hideduration_value').hide();
                            jQuery('.total_time_duration_text').html('');
                            jQuery('.freq_discount_display').hide();
                            jQuery('.partial_amount_hide_on_load').hide();
                            jQuery('.hide_right_side_box').hide();
                            jQuery(".cart_item_listing" + service_id).empty();
                            jQuery(".cart_item_listing").empty();
                            jQuery(".cart_sub_total").empty();
                            jQuery(".cart_tax").empty();
                            jQuery(".cart_total").empty();
                            jQuery('.frequent_discount').empty();
                            jQuery(".remain_amount").empty();
                            jQuery(".partial_amount").empty();
                            jQuery(".cart_discount").empty();
                        } else if (cart_session_data.status == 'delete particuler') {
                            jQuery(".edit_cart_empty_msg").hide();
                            jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).remove();
                            jQuery('.partial_amount').html(cart_session_data.partial_amount);
                            jQuery('.remain_amount').html(cart_session_data.remain_amount);
                            jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                            jQuery('.cart_discount').html('- ' + cart_session_data.cart_discount);
                            jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                            jQuery('.cart_tax').html(cart_session_data.cart_tax);
                            jQuery('.cart_total').html(cart_session_data.total_amount);
                            jQuery('.total_time_duration_text').html(cart_session_data.duration_text);
                        }
                    }
                });
            }
        });
    } else {
        jQuery('.ct-loading-main').hide();
        jQuery('.qtyyy_' + m_name).val(db_qty);
    }



});
jQuery(document).on('click', '.edit_minus', function () {
    jQuery('.freq_disc_empty_cart_error').hide();
    var site_url = siteurlObj.site_url;
    var ajax_url = ajaxurlObj.ajax_url;
    var ids = jQuery(this).data('ids');
    var service_id = jQuery(this).data('service_id');
    var method_id = jQuery(this).data('method_id');
    var method_name = jQuery(this).data('method_name');
    var m_name = jQuery(this).data('mnamee');
    var units_id = jQuery(this).data('units_id');

    jQuery('.coupon_display').hide();
    jQuery('.hide_coupon_textbox').show();
    jQuery('.ct-display-coupon-code').hide();
    jQuery('.coupon_invalid_error').hide();
    var type = jQuery(this).data('type');
    var frequently_discount_id = jQuery(".edit_frequently_discount_radio").val();
    var qty_val = parseInt(jQuery('.qtyyy_' + m_name).val());
    var qty_vals = qty_val - 1;

    var currentVal = parseInt(jQuery('.qtyyy_' + m_name).val());

    if (currentVal <= 0) {
        jQuery('.add_minus_buttonid' + units_id).hide();
        jQuery('.qtyyy_' + m_name).val('0');
        jQuery(".update_qty_of_s_m_" + m_name).remove();
        jQuery('#edit-ct-addon-' + units_id).attr('checked', false);
    } else if (currentVal > 0) {
        jQuery('.qtyyy_' + m_name).val(qty_vals);
        jQuery.ajax({
            type: 'post',
            data: {
                'addon_id': ids,
                'qty_vals': qty_vals,
                's_addon_units_maxlimit_4_ratesss': 1
            },
            url: ajax_url + "edit_booking_frontajax.php",
            success: function (res) {
                jQuery('.data_addon_qtyrate').attr("data-rate", res);
                jQuery.ajax({
                    type: 'post',
                    data: {
                        'method_id': method_id,
                        'service_id': service_id,
                        's_m_qty': qty_vals,
                        's_m_rate': res,
                        'method_name': method_name,
                        'units_id': units_id,
                        'type': type,
                        'frequently_discount_id': frequently_discount_id,
                        'add_to_cart': 1
                    },
                    url: site_url + "front/edit_booking_firststep.php",
                    success: function (res) {
                        jQuery('.freq_discount_display').show();
                        jQuery('.hide_right_side_box').show();
                        /* jQuery('.partial_amount_hide_on_load').show(); */
                        jQuery('.empty_cart_error').hide();
                        jQuery("#total_cart_count").val('2');
                        jQuery('.coupon_invalid_error').hide();
                        var cart_session_data = jQuery.parseJSON(res);
                        jQuery('#no_units_in_cart_err').val(cart_session_data.unit_status);
                        jQuery('#no_units_in_cart_err_count').val(cart_session_data.unit_require);
                        if (cart_session_data.status == 'update') {
                            jQuery(".edit_cart_empty_msg").hide();
                            jQuery('.update_qty_of_s_m_' + cart_session_data.method_name_without_space).html(cart_session_data.s_m_html);
                            jQuery('.update_qty_of_s_m_' + cart_session_data.method_name_without_space).val('data-service_id', service_id);
                            jQuery('.update_qty_of_s_m_' + cart_session_data.method_name_without_space).val('data-method_id', method_id);
                            jQuery('.update_qty_of_s_m_' + cart_session_data.method_name_without_space).val('data-units_id', units_id);
                            jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                            jQuery('.partial_amount').html(cart_session_data.partial_amount);
                            jQuery('.remain_amount').html(cart_session_data.remain_amount);
                            jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                            jQuery('.cart_discount').html('- ' + cart_session_data.cart_discount);
                            jQuery('.cart_tax').html(cart_session_data.cart_tax);
                            jQuery('.cart_total').html(cart_session_data.total_amount);
                        } else if (cart_session_data.status == 'insert') {
                            jQuery('.hideduration_value').show();
                            jQuery(".edit_cart_empty_msg").hide();
                            jQuery('.cart_item_listing' + service_id).append(cart_session_data.s_m_html);
                            jQuery('.partial_amount').html(cart_session_data.partial_amount);
                            jQuery('.remain_amount').html(cart_session_data.remain_amount);
                            jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                            jQuery('.cart_discount').html('- ' + cart_session_data.cart_discount);
                            jQuery('.cart_tax').html(cart_session_data.cart_tax);
                            jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                            jQuery('.cart_total').html(cart_session_data.total_amount);
                        } else if (cart_session_data.status == 'firstinsert') {
                            jQuery('.hideduration_value').show();
                            jQuery(".edit_cart_empty_msg").hide();
                            jQuery('.cart_item_listing' + service_id).append(cart_session_data.s_m_html);
                            jQuery('.partial_amount').html(cart_session_data.partial_amount);
                            jQuery('.remain_amount').html(cart_session_data.remain_amount);
                            jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                            jQuery('.cart_discount').html('- ' + cart_session_data.cart_discount);
                            jQuery('.cart_tax').html(cart_session_data.cart_tax);
                            jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                            jQuery('.cart_total').html(cart_session_data.total_amount);
                        } else if (cart_session_data.status == 'empty calculation') {
                            jQuery('.hideduration_value').hide();
                            jQuery('.total_time_duration_text').html('');
                            jQuery('.freq_discount_display').hide();
                            jQuery('.partial_amount_hide_on_load').hide();
                            jQuery('.hide_right_side_box').hide();
                            jQuery('.edit_add_minus_button').hide();
                            jQuery('#edit-ct-addon-' + units_id).attr('checked', false);
                            jQuery(".edit_cart_empty_msg").show();
                            jQuery(".cart_item_listing" + service_id).empty();
                            jQuery(".cart_sub_total").empty();
                            jQuery(".cart_tax").empty();
                            jQuery(".frequent_discount").empty();
                            jQuery(".cart_total").empty();
                            jQuery(".remain_amount").empty();
                            jQuery(".partial_amount").empty();
                            jQuery(".cart_discount").empty();
                        } else if (cart_session_data.status == 'delete particuler') {
                            jQuery('.add_minus_buttonid' + units_id).hide();
                            jQuery('#edit-ct-addon-' + units_id).attr('checked', false);
                            jQuery(".edit_cart_empty_msg").hide();
                            jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).remove();
                            jQuery('.partial_amount').html(cart_session_data.partial_amount);
                            jQuery('.remain_amount').html(cart_session_data.remain_amount);
                            jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                            jQuery('.cart_discount').html('- ' + cart_session_data.cart_discount);
                            jQuery('.cart_tax').html(cart_session_data.cart_tax);
                            jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                            jQuery('.cart_total').html(cart_session_data.total_amount);
                        }
                    }
                });
            }
        });
    }
});


jQuery(document).on('click', '.addons_servicess', function () {
    var site_url = siteurlObj.site_url;
    var ajax_url = ajaxurlObj.ajax_url;
    var addon_id = jQuery(this).data('id');
    var status = jQuery(this).data('status');

    /*add to cart values */
    jQuery('.freq_disc_empty_cart_error').hide();
    var site_url = siteurlObj.site_url;
    var ajax_url = ajaxurlObj.ajax_url;
    var service_id = jQuery(this).data('service_id');
    var method_id = jQuery(this).data('method_id');
    var method_name = jQuery(this).data('method_name');
    var type = 'addon';
    var frequently_discount_id = jQuery(".edit_frequently_discount_radio").val();
    var m_name = jQuery(this).data('mnamee');
    /*end cart value */

    if (parseInt(status) == 2) {
        jQuery(this).data('status', '1');
        jQuery.ajax({
            type: 'post',
            data: {
                'addon_id': addon_id,
                'get_service_addons_qtys': 1
            },
            url: ajax_url + "edit_booking_frontajax.php",
            success: function (res) {
                jQuery('.addons_counting').append(res);
            }
        });
    } else {
        jQuery(this).data('status', '2');
        jQuery('.remove_addon_intensive' + addon_id).hide();
        jQuery.ajax({
            type: 'post',
            data: {
                'method_id': method_id,
                'service_id': service_id,
                's_m_qty': 0,
                's_m_rate': 0,
                'method_name': method_name,
                'units_id': addon_id,
                'type': type,
                'frequently_discount_id': frequently_discount_id,
                'add_to_cart': 1
            },
            url: site_url + "front/edit_booking_firststep.php",
            success: function (res) {
                jQuery('.freq_discount_display').show();
                jQuery('.hide_right_side_box').show();
                /*  jQuery('.partial_amount_hide_on_load').show(); */
                jQuery('.empty_cart_error').hide();
                jQuery('.coupon_invalid_error').hide();
                jQuery("#total_cart_count").val('2');

                var cart_session_data = jQuery.parseJSON(res);
                jQuery('#no_units_in_cart_err').val(cart_session_data.unit_status);
                jQuery('#no_units_in_cart_err_count').val(cart_session_data.unit_require);
                if (cart_session_data.status == 'empty calculation') {
                    jQuery('.hideduration_value').hide();
                    jQuery('.total_time_duration_text').html('');
                    jQuery('.partial_amount_hide_on_load').hide();
                    jQuery('.hide_right_side_box').hide();
                    jQuery(".edit_cart_empty_msg").show();
                    jQuery(".cart_item_listing" + service_id).empty();
                    jQuery(".cart_sub_total").empty();
                    jQuery(".cart_tax").empty();
                    jQuery(".cart_total").empty();
                    jQuery(".frequent_discount").empty();
                    jQuery(".remain_amount").empty();
                    jQuery(".partial_amount").empty();
                    jQuery(".cart_discount").empty();
                } else if (cart_session_data.status == 'delete particuler') {
                    jQuery(".edit_cart_empty_msg").hide();
                    jQuery(".update_qty_of_s_m_" + m_name).remove();
                    jQuery('.partial_amount').html(cart_session_data.partial_amount);
                    jQuery('.remain_amount').html(cart_session_data.remain_amount);
                    jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                    jQuery('.cart_discount').html('- ' + cart_session_data.cart_discount);
                    jQuery('.cart_tax').html(cart_session_data.cart_tax);
                    jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                    jQuery('.cart_total').html(cart_session_data.total_amount);
                    jQuery('.total_time_duration_text').html(cart_session_data.duration_text);
                }
            }
        });
    }

});


/******* Service method - display design according to admin selection ******/
jQuery(document).on('click', '.edit_s_m_units_design', function () {
    jQuery('.ct-loading-main').show();
    /* jQuery('.hideduration_value').hide();
    jQuery('.total_time_duration_text').html(''); */
    jQuery('.addons_servicess').each(function () {
        jQuery(this).data('status', '2');
        var value = jQuery(this).prop('checked');
        if (value == true) {
            jQuery('#edit-ct-addon-' + jQuery(this).data('id')).attr('checked', false);
        }
        jQuery('.remove_addon_intensive' + jQuery(this).data('id')).hide();
    });
    jQuery('.freq_discount_display').hide();
    jQuery(".edit_cart_empty_msg").hide();
    /*jQuery('.partial_amount_hide_on_load').hide();*/
    jQuery('.hide_right_side_box').hide();
    if (jQuery('.service-method-name').text() != 'Service Usage Methods') {
        jQuery('.method_not_selected_error').attr('style', '');
        /* jQuery('.empty_cart_error').css('display','block');
        jQuery('.empty_cart_error').css('color','red');
        jQuery('.empty_cart_error').html(errorobj_please_select_units_or_addons); */
    }
    jQuery('.edit_add_addon_in_cart_for_multipleqty').each(function () {
        var multiqty_addon_id = jQuery(this).data('id');
        var value = jQuery(this).prop('checked');
        if (value == true) {
            jQuery('#edit-ct-addon-' + multiqty_addon_id).attr('checked', false);
        }
    });

    jQuery('.addon_qty').each(function () {
        jQuery(this).val(0);
        jQuery('.edit_add_minus_button').hide();
        jQuery('.addons_servicess_2').attr('checked', false);
    });
    jQuery('.freq_disc_empty_cart_error').hide();
    jQuery('.edit_add_addon_in_cart_for_multipleqty').data('status', '2');
    var site_url = siteurlObj.site_url;
    var ajax_url = ajaxurlObj.ajax_url;
    var id = jQuery(this).data('id');
    var method_id = jQuery(this).data('id');
    var service_id = jQuery(this).data('service_id');
    jQuery.ajax({
        type: 'post',
        data: {
            'service_methods_id': id,
            'select_s_m_units_design': 1
        },
        url: ajax_url + "edit_booking_frontajax.php",
        success: function (response) {
            jQuery('.ct-loading-main').hide();
            if (response == 1) {
                /* jQuery( ".cart_item_listing" ).empty();
                jQuery( ".cart_sub_total" ).empty();
                jQuery( ".cart_tax" ).empty();
                jQuery( ".cart_total" ).empty();
                jQuery( ".frequent_discount" ).empty();
                jQuery( ".remain_amount" ).empty();
                jQuery( ".partial_amount" ).empty();
                jQuery( ".cart_discount" ).empty(); */
                jQuery('.coupon_display').hide();
                jQuery('.edit_s_m_units_design_1').show();
                jQuery('.edit_s_m_units_design_2').hide();
                jQuery('.edit_s_m_units_design_3').hide();
                jQuery('.edit_s_m_units_design_4').hide();
                jQuery('.edit_s_m_units_design_5').hide();
                jQuery.ajax({
                    type: 'post',
                    data: {
                        'method_id': method_id,
                        'service_id': service_id,
                        's_m_units_maxlimit': 1
                    },
                    url: ajax_url + "edit_booking_frontajax.php",
                    success: function (response) {
                        jQuery('.duration_hrs').html(response);
                    }
                });
            } else if (response == 2) {
                /* jQuery( ".cart_item_listing" ).empty();
                jQuery( ".cart_sub_total" ).empty();
                jQuery( ".cart_tax" ).empty();
                jQuery( ".cart_total" ).empty();
                jQuery( ".remain_amount" ).empty();
                jQuery( ".frequent_discount" ).empty();
                jQuery( ".partial_amount" ).empty();
                jQuery( ".cart_discount" ).empty(); */
                jQuery('.coupon_display').hide();
                jQuery('.edit_s_m_units_design_1').hide();
                jQuery('.edit_s_m_units_design_2').show();
                jQuery('.edit_s_m_units_design_3').hide();
                jQuery('.edit_s_m_units_design_4').hide();
                jQuery('.edit_s_m_units_design_5').hide();
                jQuery.ajax({
                    type: 'post',
                    data: {
                        'method_id': method_id,
                        'service_id': service_id,
                        's_m_units_maxlimit_2': 1
                    },
                    url: ajax_url + "edit_booking_frontajax.php",
                    success: function (res) {
                        jQuery('.ser_design_2_units').html(res);
                    }
                });
            } else if (response == 3) {
                /* jQuery( ".cart_item_listing" ).empty();
                jQuery( ".cart_sub_total" ).empty();
                jQuery( ".cart_tax" ).empty();
                jQuery( ".cart_total" ).empty();
                jQuery( ".remain_amount" ).empty();
                jQuery( ".partial_amount" ).empty();
                jQuery( ".frequent_discount" ).empty();
                jQuery( ".cart_discount" ).empty(); */
                jQuery('.coupon_display').hide();
                jQuery('.edit_s_m_units_design_1').hide();
                jQuery('.edit_s_m_units_design_2').hide();
                jQuery('.edit_s_m_units_design_3').show();
                jQuery('.edit_s_m_units_design_4').hide();
                jQuery('.edit_s_m_units_design_5').hide();
                jQuery.ajax({
                    type: 'post',
                    data: {
                        'method_id': method_id,
                        'service_id': service_id,
                        's_m_units_maxlimit_3': 1
                    },
                    url: ajax_url + "edit_booking_frontajax.php",
                    success: function (res) {
                        jQuery('.ser_design_3_units').html(res);
                    }
                });
            } else if (response == 4) {
                /* jQuery( ".cart_item_listing" ).empty();
                jQuery( ".cart_sub_total" ).empty();
                jQuery( ".cart_tax" ).empty();
                jQuery( ".cart_total" ).empty();
                jQuery( ".remain_amount" ).empty();
                jQuery( ".frequent_discount" ).empty();
                jQuery( ".partial_amount" ).empty();
                jQuery( ".cart_discount" ).empty(); */
                jQuery('.coupon_display').hide();
                jQuery('.edit_s_m_units_design_1').hide();
                jQuery('.edit_s_m_units_design_2').hide();
                jQuery('.edit_s_m_units_design_3').hide();
                jQuery('.edit_s_m_units_design_4').show();
                jQuery('.edit_s_m_units_design_5').hide();
                jQuery.ajax({
                    type: 'post',
                    data: {
                        'method_id': method_id,
                        'service_id': service_id,
                        's_m_units_maxlimit_4': 1
                    },
                    url: ajax_url + "edit_booking_frontajax.php",
                    success: function (res) {
                        jQuery('.ser_design_4_units').html(res);
                    }
                });
            } else if (response == 5) {
                /* jQuery( ".cart_item_listing" ).empty();
                jQuery( ".cart_sub_total" ).empty();
                jQuery( ".cart_tax" ).empty();
                jQuery( ".cart_total" ).empty();
                jQuery( ".remain_amount" ).empty();
                jQuery( ".frequent_discount" ).empty();
                jQuery( ".partial_amount" ).empty();
                jQuery( ".cart_discount" ).empty();
                jQuery('.coupon_display').hide(); */
                jQuery('.edit_s_m_units_design_1').hide();
                jQuery('.edit_s_m_units_design_2').hide();
                jQuery('.edit_s_m_units_design_3').hide();
                jQuery('.edit_s_m_units_design_4').hide();
                jQuery('.edit_s_m_units_design_5').show();
                jQuery.ajax({
                    type: 'post',
                    data: {
                        'method_id': method_id,
                        'service_id': service_id,
                        's_m_units_maxlimit_5': 1
                    },
                    url: ajax_url + "edit_booking_frontajax.php",
                    success: function (res) {
                        jQuery('.ser_design_5_units').html(res);
                    }
                });
            }
        }
    });
});

/* service checkbox */

jQuery(document).ready(function () {
    jQuery("input[name=service-radio]").click(function () {
        /*  jQuery(".ct-meth-unit-count").show( "blind", {direction: "vertical"}, 700 ); */
    });
});


/* bedrooms dropdown show hide list */
jQuery(document).on("click", ".edit-select-bedrooms", function () {
    var unit_id = jQuery(this).data('un_id');
    jQuery(".ct-edit-" + unit_id + "-dropdown").toggle("blind", { direction: "vertical" }, 300);
});

/* select on click on bedroom */
jQuery(document).on("click", ".edit_select_bedroom", function () {
    var units_id = jQuery(this).data('units_id');
    jQuery('#ct_edit_selected_' + units_id).html(jQuery(this).html());
    jQuery(".ct-edit-" + units_id + "-dropdown").hide("blind", { direction: "vertical" }, 300);
});



jQuery(document).on("click", ".select-language", function () {
    jQuery(".ct-language-dropdown").toggle("blind", { direction: "vertical" }, 300);
});
jQuery(document).on("click", ".select_language_view", function () {
    var ajax_url = ajaxurlObj.ajax_url;
    jQuery('#ct_selected_language').html(jQuery(this).html());
    jQuery(".ct-language-dropdown").hide("blind", { direction: "vertical" }, 300);
    jQuery.ajax({
        type: 'POST',
        data: { 'select_language': "yes", "set_language": jQuery(this).data("langs") },
        url: ajax_url + "edit_booking_frontajax.php",
        success: function (res) {
            location.reload();
        }
    });
});


/* remove item btn-from the cart */
jQuery(document).on("click", ".edit_remove_item_from_cart", function () {
    var ajax_url = ajaxurlObj.ajax_url;
    var m_name = jQuery(this).data('mnamee');
    var unit_id = jQuery(this).data('units_id');
    var service_id = jQuery(this).data('service_id');

    var frequently_discount = jQuery(".edit_frequently_discount_radio").val();
    jQuery.ajax({
        type: 'POST',
        data: {
            'cart_unit_id': unit_id,
            'frequently_discount_id': frequently_discount,
            'cart_item_remove': 1
        },
        url: ajax_url + "edit_booking_frontajax.php",
        success: function (res) {
            var cart_session_data = jQuery.parseJSON(res);

            jQuery('.edit-select-bedrooms').each(function () {
                if (jQuery(this).data('un_id') == unit_id) {
                    var dd_default_title = jQuery(this).data('un_title');
                    jQuery('#ct_edit_selected_' + unit_id + ' .ct-count').html(dd_default_title);
                }
            });

            jQuery('.u_' + unit_id + '_btn').removeClass('ct-bed-selected');

            jQuery('.ct_area_m_units').val('');
            jQuery('#ct_area_m_units' + unit_id).val('');

            jQuery('.qtyyy_ad_unit' + unit_id).val('0');
            jQuery('.add_minus_buttonid' + unit_id).hide();
            jQuery('.remove_addon_intensive' + unit_id).hide();

            jQuery('#qty' + unit_id).val('0');

            jQuery('#edit-ct-addon-' + unit_id).data('status', '2');
            jQuery('#edit-ct-addon-' + unit_id).prop('checked', false);

            if (cart_session_data.status == 'empty calculation') {
                jQuery('.hideduration_value').hide();
                jQuery('.total_time_duration_text').html('');
                jQuery("#total_cart_count").val('1');
                jQuery('.freq_discount_display').hide();
                jQuery('.partial_amount_hide_on_load').hide();
                jQuery('.hide_right_side_box').hide();
                jQuery(".edit_cart_empty_msg").hide();
                jQuery(".cart_item_listing" + service_id).empty();
                jQuery(".cart_sub_total").empty();
                jQuery(".cart_tax").empty();
                jQuery(".cart_total").empty();
                jQuery(".remain_amount").empty();
                jQuery(".partial_amount").empty();
                jQuery(".cart_discount").empty();
                jQuery('.frequent_discount').empty();
                jQuery('.total_time_duration_text').empty();
                if (ser_ids_dynamic != service_id) {
                    if ((jQuery("ul" + service_ul).has("li").length === 0)) {
                        jQuery(".cart_service_item_listing_li" + service_id).remove();
                    }
                }
            } else {
                jQuery("#total_cart_count").val('2');
                jQuery(".edit_cart_empty_msg").hide();
                jQuery(".update_qty_of_s_m_" + m_name).remove();
                jQuery('.partial_amount').html(cart_session_data.partial_amount);
                jQuery('.remain_amount').html(cart_session_data.remain_amount);
                jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                jQuery('.cart_discount').html('- ' + cart_session_data.cart_discount);
                jQuery('.cart_tax').html(cart_session_data.cart_tax);
                jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                jQuery('.cart_total').html(cart_session_data.total_amount);
            }
        }
    });

});


/* bedroom counting */

jQuery(document).on("click", ".select_m_u_btn", function () {
    var units_id = jQuery(this).data('units_id');
    jQuery('.u_' + units_id + '_btn').each(function () {
        jQuery(this).removeClass('ct-bed-selected');
    });
    jQuery(this).addClass('ct-bed-selected');
});


/* bedroom and bathroom counting */
jQuery(document).on('click', '.edit_addd', function () {
    jQuery('.freq_disc_empty_cart_error').hide();
    var site_url = siteurlObj.site_url;
    var ajax_url = ajaxurlObj.ajax_url;
    var ids = jQuery(this).data('ids');
    var db_qty = jQuery(this).data('db-qty');
    var db_minqty = jQuery(this).attr("data-db-minqty");
    var service_id = jQuery(this).data('service_id');
    var method_id = jQuery(this).data('method_id');
    var method_name = jQuery(this).data('method_name');
    var units_id = jQuery(this).data('units_id');
    var type = jQuery(this).data('type');
    var m_name = jQuery(this).data('mnamee');
    var hfsec = jQuery(this).data('hfsec');
    var unit_symbol = jQuery(this).data('unit_symbol');
    jQuery('.coupon_display').hide();
    jQuery('.hide_coupon_textbox').show();
    jQuery('.ct-display-coupon-code').hide();
    jQuery('.coupon_invalid_error').hide();
    var frequently_discount_id = jQuery(".edit_frequently_discount_radio").val();
    var qty_val = 0;
    if (unit_symbol != "") {
        var qty_val_orignal = jQuery('#qty' + ids).val();
        var qty_val_array = qty_val_orignal.split(" ");
        qty_val = parseFloat(qty_val_array[0]);
    } else {
        qty_val = parseFloat(jQuery('#qty' + ids).val());
    }
    var qty_vals = qty_val;

    if (qty_val == 0) {

        qty_vals = db_minqty;

    } else if (qty_val < db_qty) {

        qty_vals = parseFloat(qty_val) + parseFloat(hfsec);

    }

    if (qty_val < db_qty) {
        jQuery('.qty' + ids).val(qty_vals + " " + unit_symbol);
        var final_qty_val = qty_vals;
        jQuery.ajax({
            type: 'post',
            data: {
                'method_id': method_id,
                'service_id': service_id,
                'units_id': units_id,
                'qty_vals': final_qty_val,
                'hfsec': hfsec,
                's_m_units_maxlimit_4_ratesss': 1
            },
            url: ajax_url + "edit_booking_frontajax.php",
            success: function (res) {
                jQuery('.data_qtyrate').attr("data-rate", res);
                jQuery.ajax({
                    type: 'post',
                    data: {
                        'method_id': method_id,
                        'service_id': service_id,
                        's_m_qty': final_qty_val,
                        's_m_rate': res,
                        'method_name': method_name,
                        'units_id': units_id,
                        'frequently_discount_id': frequently_discount_id,
                        'type': type,
                        'add_to_cart': 1
                    },
                    url: site_url + "front/edit_booking_firststep.php",
                    success: function (res) {
                        jQuery('.freq_discount_display').show();
                        jQuery('.hide_right_side_box').show();
                        /* jQuery('.partial_amount_hide_on_load').show(); */
                        jQuery('.empty_cart_error').hide();
                        jQuery('.no_units_in_cart_error').hide();
                        jQuery('.coupon_invalid_error').hide();
                        jQuery("#total_cart_count").val('2');
                        var cart_session_data = jQuery.parseJSON(res);
                        jQuery('#no_units_in_cart_err').val(cart_session_data.unit_status);
                        jQuery('#no_units_in_cart_err_count').val(cart_session_data.unit_require);
                        if (cart_session_data.status == 'update') {
                            jQuery(".edit_cart_empty_msg").hide();
                            jQuery('.update_qty_of_s_m_' + cart_session_data.method_name_without_space).html(cart_session_data.s_m_html);
                            jQuery('.update_qty_of_s_m_' + cart_session_data.method_name_without_space).val('data-service_id', service_id);
                            jQuery('.update_qty_of_s_m_' + cart_session_data.method_name_without_space).val('data-method_id', method_id);
                            jQuery('.update_qty_of_s_m_' + cart_session_data.method_name_without_space).val('data-units_id', units_id);
                            jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                            jQuery('.partial_amount').html(cart_session_data.partial_amount);
                            jQuery('.remain_amount').html(cart_session_data.remain_amount);
                            jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                            jQuery('.cart_discount').html('- ' + cart_session_data.cart_discount);
                            jQuery('.cart_tax').html(cart_session_data.cart_tax);
                            jQuery('.cart_total').html(cart_session_data.total_amount);
                            jQuery('.total_time_duration_text').html(cart_session_data.duration_text);
                        } else if (cart_session_data.status == 'insert') {
                            jQuery('.hideduration_value').show();
                            jQuery(".edit_cart_empty_msg").hide();
                            jQuery('.cart_item_listing' + service_id).append(cart_session_data.s_m_html);
                            jQuery('.partial_amount').html(cart_session_data.partial_amount);
                            jQuery('.remain_amount').html(cart_session_data.remain_amount);
                            jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                            jQuery('.cart_discount').html('- ' + cart_session_data.cart_discount);
                            jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                            jQuery('.cart_tax').html(cart_session_data.cart_tax);
                            jQuery('.cart_total').html(cart_session_data.total_amount);
                            jQuery('.total_time_duration_text').html(cart_session_data.duration_text);
                        } else if (cart_session_data.status == 'firstinsert') {
                            jQuery('.hideduration_value').show();
                            jQuery(".edit_cart_empty_msg").hide();
                            jQuery('.cart_item_listing' + service_id).append(cart_session_data.s_m_html);
                            jQuery('.partial_amount').html(cart_session_data.partial_amount);
                            jQuery('.remain_amount').html(cart_session_data.remain_amount);
                            jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                            jQuery('.cart_discount').html('- ' + cart_session_data.cart_discount);
                            jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                            jQuery('.cart_tax').html(cart_session_data.cart_tax);
                            jQuery('.cart_total').html(cart_session_data.total_amount);
                            jQuery('.total_time_duration_text').html(cart_session_data.duration_text);
                        } else if (cart_session_data.status == 'empty calculation') {
                            jQuery('.hideduration_value').hide();
                            jQuery('.total_time_duration_text').html('');
                            jQuery('.freq_discount_display').hide();
                            jQuery('.partial_amount_hide_on_load').hide();
                            jQuery('.hide_right_side_box').hide();
                            jQuery(".edit_cart_empty_msg").show();
                            jQuery(".frequent_discount").empty();
                            jQuery(".cart_item_listing" + service_id).empty();
                            jQuery(".cart_sub_total").empty();
                            jQuery(".cart_tax").empty();
                            jQuery(".cart_total").empty();
                            jQuery(".remain_amount").empty();
                            jQuery(".partial_amount").empty();
                            jQuery(".cart_discount").empty();
                        } else if (cart_session_data.status == 'delete particuler') {
                            jQuery(".edit_cart_empty_msg").hide();
                            jQuery(".update_qty_of_s_m_" + m_name).remove();
                            jQuery('.partial_amount').html(cart_session_data.partial_amount);
                            jQuery('.remain_amount').html(cart_session_data.remain_amount);
                            jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                            jQuery('.cart_discount').html('- ' + cart_session_data.cart_discount);
                            jQuery('.cart_tax').html(cart_session_data.cart_tax);
                            jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                            jQuery('.cart_total').html(cart_session_data.total_amount);
                            jQuery('.total_time_duration_text').html(cart_session_data.duration_text);
                        }
                    }
                });
            }
        });
    } else {
        jQuery('.ct-loading-main').hide();
        jQuery('.qty' + ids).val(qty_vals + " " + unit_symbol);
    }

});
jQuery(document).on("click", ".edit_minuss", function () {
    jQuery(".freq_disc_empty_cart_error, .custom_item_error, .coupon_display, .ct-display-coupon-code, .user_coupon_display, .ct-display-user-coupon-code, .ct-display-referral-code, .coupon_invalid_error").hide();
    jQuery(".custom_item_error, .hide_coupon_textbox, .hide_user_coupon_textbox, .hide_referral_textbox").show();
    jQuery(".custom_item_error").html("");

    var site_url = siteurlObj.site_url;
    var ajax_url = ajaxurlObj.ajax_url;
    var ids = jQuery(this).data("ids") || jQuery(this).attr("data-ids");
    var service_id = jQuery(this).data("service_id") || jQuery(this).attr("data-service_id");
    var method_id = jQuery(this).data("method_id") || jQuery(this).attr("data-method_id");
    var method_name = jQuery(this).data("method_name") || jQuery(this).attr("data-method_name");
    var hfsec = jQuery(this).data("hfsec") || jQuery(this).attr("data-hfsec");
    var units_id = jQuery(this).data("units_id") || jQuery(this).attr("data-units_id");
    var type = jQuery(this).data("type") || jQuery(this).attr("data-type");
    var unit_symbol = jQuery(this).data("unit_symbol") || jQuery(this).attr("data-unit_symbol");
    var frequently_discount_id = jQuery(".edit_frequently_discount_radio").val() || jQuery("input[name=frequently_discount_radio]:checked").attr("data-id");
    var db_minqty = jQuery(this).data("db-minqty") || jQuery(this).attr("data-db-minqty");

    var currentVal = parseFloat(jQuery(".qty" + ids).val());
    var qty_val = 0;
    if (unit_symbol != "") {
        var qty_val_orignal = jQuery("#qty" + ids).val();
        var qty_val_array = qty_val_orignal.split(" ");
        qty_val = parseFloat(qty_val_array[0]);
    } else {
        qty_val = parseFloat(jQuery("#qty" + ids).val());
    }
    var qty_vals = (qty_val > db_minqty) ? qty_val - hfsec : 0;

    if (currentVal <= 0) {
        jQuery(".ct-loading-main").hide();
        jQuery(".qty" + ids).val("0 " + unit_symbol);
        jQuery(".update_qty_of_s_m_" + method_name).remove();
    } else if (currentVal > 0) {
        jQuery(".qty" + ids).val(qty_vals + " " + unit_symbol);
        jQuery.ajax({
            type: "post",
            data: {
                "method_id": method_id,
                "service_id": service_id,
                "qty_vals": qty_vals,
                "units_id": units_id,
                "hfsec": hfsec,
                "s_m_units_maxlimit_4_ratesss": 1
            },
            url: ajax_url + "edit_booking_frontajax.php",
            success: function (res) {
                jQuery(".data_qtyrate").attr("data-rate", res);
                jQuery.ajax({
                    type: "post",
                    data: {
                        "method_id": method_id,
                        "service_id": service_id,
                        "s_m_qty": qty_vals,
                        "s_m_rate": res,
                        "method_name": method_name,
                        "units_id": units_id,
                        "type": type,
                        "frequently_discount_id": frequently_discount_id,
                        "add_to_cart": 1
                    },
                    url: site_url + "front/edit_booking_firststep.php",
                    success: function (res) {
                        jQuery('.freq_discount_display').show();
                        jQuery('.hide_right_side_box').show();
                        / jQuery('.partial_amount_hide_on_load').show(); /
                        jQuery('.empty_cart_error').hide();
                        jQuery('.no_units_in_cart_error').hide();
                        jQuery('.coupon_invalid_error').hide();
                        jQuery("#total_cart_count").val('2');
                        var cart_session_data = jQuery.parseJSON(res);
                        jQuery('#no_units_in_cart_err').val(cart_session_data.unit_status);
                        jQuery('#no_units_in_cart_err_count').val(cart_session_data.unit_require);
                        if (cart_session_data.status == 'update') {
                            jQuery(".edit_cart_empty_msg").hide();
                            jQuery('.update_qty_of_s_m_' + cart_session_data.method_name_without_space).html(cart_session_data.s_m_html);
                            jQuery('.update_qty_of_s_m_' + cart_session_data.method_name_without_space).val('data-service_id', service_id);
                            jQuery('.update_qty_of_s_m_' + cart_session_data.method_name_without_space).val('data-method_id', method_id);
                            jQuery('.update_qty_of_s_m_' + cart_session_data.method_name_without_space).val('data-units_id', units_id);
                            jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                            jQuery('.partial_amount').html(cart_session_data.partial_amount);
                            jQuery('.remain_amount').html(cart_session_data.remain_amount);
                            jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                            jQuery('.cart_discount').html('- ' + cart_session_data.cart_discount);
                            jQuery('.cart_tax').html(cart_session_data.cart_tax);
                            jQuery('.cart_total').html(cart_session_data.total_amount);
                            jQuery('.total_time_duration_text').html(cart_session_data.duration_text);
                        } else if (cart_session_data.status == 'insert') {
                            jQuery('.hideduration_value').show();
                            jQuery(".edit_cart_empty_msg").hide();
                            jQuery('.cart_item_listing' + service_id).append(cart_session_data.s_m_html);
                            jQuery('.partial_amount').html(cart_session_data.partial_amount);
                            jQuery('.remain_amount').html(cart_session_data.remain_amount);
                            jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                            jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                            jQuery('.cart_discount').html('- ' + cart_session_data.cart_discount);
                            jQuery('.cart_tax').html(cart_session_data.cart_tax);
                            jQuery('.cart_total').html(cart_session_data.total_amount);
                            jQuery('.total_time_duration_text').html(cart_session_data.duration_text);
                        } else if (cart_session_data.status == 'firstinsert') {
                            jQuery('.hideduration_value').show();
                            jQuery(".edit_cart_empty_msg").hide();
                            jQuery('.cart_item_listing' + service_id).append(cart_session_data.s_m_html);
                            jQuery('.partial_amount').html(cart_session_data.partial_amount);
                            jQuery('.remain_amount').html(cart_session_data.remain_amount);
                            jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                            jQuery('.cart_discount').html('- ' + cart_session_data.cart_discount);
                            jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                            jQuery('.cart_tax').html(cart_session_data.cart_tax);
                            jQuery('.cart_total').html(cart_session_data.total_amount);
                            jQuery('.total_time_duration_text').html(cart_session_data.duration_text);
                        } else if (cart_session_data.status == 'empty calculation') {
                            jQuery('.hideduration_value').hide();
                            jQuery('.total_time_duration_text').html('');
                            jQuery('.freq_discount_display').hide();
                            jQuery('.partial_amount_hide_on_load').hide();
                            jQuery('.hide_right_side_box').hide();
                            jQuery(".edit_cart_empty_msg").show();
                            jQuery(".cart_item_listing" + service_id).empty();
                            jQuery(".cart_sub_total").empty();
                            jQuery(".frequent_discount").empty();
                            jQuery(".cart_tax").empty();
                            jQuery(".cart_total").empty();
                            jQuery(".remain_amount").empty();
                            jQuery(".partial_amount").empty();
                            jQuery(".cart_discount").empty();
                        } else if (cart_session_data.status == 'delete particuler') {
                            jQuery(".edit_cart_empty_msg").hide();
                            jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).remove();
                            jQuery('.partial_amount').html(cart_session_data.partial_amount);
                            jQuery('.remain_amount').html(cart_session_data.remain_amount);
                            jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                            jQuery('.cart_discount').html('- ' + cart_session_data.cart_discount);
                            jQuery('.cart_tax').html(cart_session_data.cart_tax);
                            jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                            jQuery('.cart_total').html(cart_session_data.total_amount);
                            jQuery('.total_time_duration_text').html(cart_session_data.duration_text);
                        }
                    }
                });
            }
        });
    }
});

jQuery(document).on('keyup', '.ct_area_m_units', function (event) {
    jQuery('.freq_disc_empty_cart_error').hide();
    jQuery('.error_of_invalid_area').hide();
    var site_url = siteurlObj.site_url;
    var ajax_url = ajaxurlObj.ajax_url;
    var area_uniit = jQuery(".ct_area_m_units").val();
    var service_id = jQuery(this).data('service_id');
    var method_id = jQuery(this).data('method_id');
    var max_limitts = jQuery(this).data('maxx_limit');
    var method_name = jQuery(this).data('method_name');
    var units_id = jQuery(this).data('units_id');
    var type = jQuery(this).data('type');
    var frequently_discount_id = jQuery(".edit_frequently_discount_radio").val();
    jQuery('.coupon_display').hide();
    jQuery('.hide_coupon_textbox').show();
    jQuery('.ct-display-coupon-code').hide();
    jQuery('.coupon_invalid_error').hide();
    var m_name = jQuery(this).data('mnamee');
    var Number = /^[0-9]+$/;

    if (event.which == 8) {
        jQuery(".error_of_invalid_area" + units_id).hide();
        jQuery(".error_of_max_limitss" + units_id).hide();
        jQuery(".error_of_min_limitss" + units_id).hide();
    }
    if (/^[0-9]+$/.test(area_uniit) == false) {
        jQuery(".error_of_invalid_area").show();
        jQuery('.error_of_invalid_area').html('Invalid ' + method_name);
    }
    if (area_uniit == '') {
        jQuery.ajax({
            type: 'post',
            data: {
                'method_id': method_id,
                'service_id': service_id,
                's_m_qty': 0,
                's_m_rate': 0,
                'method_name': method_name,
                'units_id': units_id,
                'type': type,
                'frequently_discount_id': frequently_discount_id,
                'add_to_cart': 1
            },
            url: site_url + "front/edit_booking_firststep.php",
            success: function (res) {
                jQuery('.freq_discount_display').show();
                jQuery('.hide_right_side_box').show();
                /* jQuery('.partial_amount_hide_on_load').show(); */
                jQuery('.empty_cart_error').hide();
                jQuery('.no_units_in_cart_error').hide();
                jQuery('.coupon_invalid_error').hide();
                jQuery("#total_cart_count").val('2');
                var cart_session_data = jQuery.parseJSON(res);
                jQuery('#no_units_in_cart_err').val(cart_session_data.unit_status);
                jQuery('#no_units_in_cart_err_count').val(cart_session_data.unit_require);
                if (cart_session_data.status == 'empty calculation') {
                    jQuery('.hideduration_value').hide();
                    jQuery('.total_time_duration_text').html('');
                    jQuery('.freq_discount_display').hide();
                    jQuery('.partial_amount_hide_on_load').hide();
                    jQuery('.hide_right_side_box').hide();
                    jQuery(".edit_cart_empty_msg").show();
                    jQuery(".cart_item_listing" + service_id).empty();
                    jQuery(".frequent_discount").empty();
                    jQuery(".cart_sub_total").empty();
                    jQuery(".cart_tax").empty();
                    jQuery(".cart_total").empty();
                    jQuery(".remain_amount").empty();
                    jQuery(".partial_amount").empty();
                    jQuery(".cart_discount").empty();
                } else if (cart_session_data.status == 'delete particuler') {
                    jQuery(".edit_cart_empty_msg").hide();
                    jQuery(".update_qty_of_s_m_" + m_name).remove();
                    jQuery('.partial_amount').html(cart_session_data.partial_amount);
                    jQuery('.remain_amount').html(cart_session_data.remain_amount);
                    jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                    jQuery('.cart_discount').html('- ' + cart_session_data.cart_discount);
                    jQuery('.cart_tax').html(cart_session_data.cart_tax);
                    jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                    jQuery('.cart_total').html(cart_session_data.total_amount);
                    jQuery('.total_time_duration_text').html(cart_session_data.duration_text);
                }
            }
        });
    } else if (area_uniit == 0) {
        jQuery(".error_of_invalid_area" + units_id).show();
        jQuery(".error_of_invalid_area" + units_id).html(errorobj_invalid + " " + method_name);
    } else if (area_uniit > max_limitts) {
        jQuery(".error_of_max_limitss" + units_id).show();
        jQuery(".error_of_max_limitss" + units_id).html(errorobj_max_limit_reached);
    } else if (area_uniit < min_limitts) {
        jQuery(".error_of_min_limitss" + units_id).show();
        jQuery(".error_of_min_limitss" + units_id).html(errorobj_min_limit_reached + min_limitts);




    } else if (area_uniit <= max_limitts) {
        if (area_uniit.match(Number)) {
            jQuery.ajax({
                type: 'post',
                data: {
                    'method_id': method_id,
                    'service_id': service_id,
                    'units_id': units_id,
                    'qty_vals': area_uniit,
                    's_m_units_maxlimit_4_ratesss': 1
                },
                url: ajax_url + "edit_booking_frontajax.php",
                success: function (res) {
                    jQuery('.ct_area_m_units_rattee').attr("data-rate", res);
                    jQuery.ajax({
                        type: 'post',
                        data: {
                            'method_id': method_id,
                            'service_id': service_id,
                            's_m_qty': area_uniit,
                            's_m_rate': res,
                            'method_name': method_name,
                            'units_id': units_id,
                            'type': type,
                            'frequently_discount_id': frequently_discount_id,
                            'add_to_cart': 1
                        },
                        url: site_url + "front/edit_booking_firststep.php",
                        success: function (res) {
                            jQuery('.freq_discount_display').show();
                            jQuery('.hide_right_side_box').show();
                            /* jQuery('.partial_amount_hide_on_load').show(); */
                            jQuery('.empty_cart_error').hide();
                            jQuery('.no_units_in_cart_error').hide();
                            jQuery('.coupon_invalid_error').hide();
                            jQuery("#total_cart_count").val('2');
                            var cart_session_data = jQuery.parseJSON(res);
                            jQuery('#no_units_in_cart_err').val(cart_session_data.unit_status);
                            jQuery('#no_units_in_cart_err_count').val(cart_session_data.unit_require);
                            if (cart_session_data.status == 'update') {
                                jQuery(".edit_cart_empty_msg").hide();
                                jQuery('.update_qty_of_s_m_' + cart_session_data.method_name_without_space).html(cart_session_data.s_m_html);
                                jQuery('.update_qty_of_s_m_' + cart_session_data.method_name_without_space).val('data-service_id', service_id);
                                jQuery('.update_qty_of_s_m_' + cart_session_data.method_name_without_space).val('data-method_id', method_id);
                                jQuery('.update_qty_of_s_m_' + cart_session_data.method_name_without_space).val('data-units_id', units_id);
                                jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                                jQuery('.partial_amount').html(cart_session_data.partial_amount);
                                jQuery('.remain_amount').html(cart_session_data.remain_amount);
                                jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                                jQuery('.cart_discount').html('- ' + cart_session_data.cart_discount);
                                jQuery('.cart_tax').html(cart_session_data.cart_tax);
                                jQuery('.cart_total').html(cart_session_data.total_amount);
                                jQuery('.total_time_duration_text').html(cart_session_data.duration_text);
                            } else if (cart_session_data.status == 'insert') {
                                jQuery('.hideduration_value').show();
                                jQuery(".edit_cart_empty_msg").hide();
                                jQuery('.cart_item_listing' + service_id).append(cart_session_data.s_m_html);
                                jQuery('.partial_amount').html(cart_session_data.partial_amount);
                                jQuery('.remain_amount').html(cart_session_data.remain_amount);
                                jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                                jQuery('.cart_discount').html('- ' + cart_session_data.cart_discount);
                                jQuery('.cart_tax').html(cart_session_data.cart_tax);
                                jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                                jQuery('.cart_total').html(cart_session_data.total_amount);
                                jQuery('.total_time_duration_text').html(cart_session_data.duration_text);
                            } else if (cart_session_data.status == 'firstinsert') {
                                jQuery('.hideduration_value').show();
                                jQuery(".edit_cart_empty_msg").hide();
                                jQuery('.cart_item_listing' + service_id).append(cart_session_data.s_m_html);
                                jQuery('.partial_amount').html(cart_session_data.partial_amount);
                                jQuery('.remain_amount').html(cart_session_data.remain_amount);
                                jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                                jQuery('.cart_discount').html('- ' + cart_session_data.cart_discount);
                                jQuery('.cart_tax').html(cart_session_data.cart_tax);
                                jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                                jQuery('.cart_total').html(cart_session_data.total_amount);
                                jQuery('.total_time_duration_text').html(cart_session_data.duration_text);
                            } else if (cart_session_data.status == 'empty calculation') {
                                jQuery('.hideduration_value').hide();
                                jQuery('.total_time_duration_text').html('');
                                jQuery('.freq_discount_display').hide();
                                jQuery('.partial_amount_hide_on_load').hide();
                                jQuery('.hide_right_side_box').hide();
                                jQuery(".edit_cart_empty_msg").show();
                                jQuery(".cart_item_listing" + service_id).empty();
                                jQuery(".cart_sub_total").empty();
                                jQuery(".frequent_discount").empty();
                                jQuery(".cart_tax").empty();
                                jQuery(".cart_total").empty();
                                jQuery(".remain_amount").empty();
                                jQuery(".partial_amount").empty();
                                jQuery(".cart_discount").empty();
                            } else if (cart_session_data.status == 'delete particuler') {
                                jQuery(".edit_cart_empty_msg").hide();
                                jQuery(".update_qty_of_s_m_" + m_name).remove();
                                jQuery('.partial_amount').html(cart_session_data.partial_amount);
                                jQuery('.remain_amount').html(cart_session_data.remain_amount);
                                jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                                jQuery('.cart_discount').html('- ' + cart_session_data.cart_discount);
                                jQuery('.cart_tax').html(cart_session_data.cart_tax);
                                jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                                jQuery('.cart_total').html(cart_session_data.total_amount);
                                jQuery('.total_time_duration_text').html(cart_session_data.duration_text);
                            }
                        }
                    });
                }
            });
        } else {
            jQuery(".ct-loading-main").hide();
            jQuery(".error_of_invalid_area" + units_id).show();
            jQuery(".error_of_invalid_area" + units_id).html(errorobj_invalid + " " + method_name);
        }
    }
});

jQuery(document).on('click', '.edit_add_item_in_cart', function () {
    jQuery('.freq_disc_empty_cart_error').hide();
    var site_url = siteurlObj.site_url;
    var ajax_url = ajaxurlObj.ajax_url;
    jQuery('.coupon_display').hide();
    jQuery('.hide_coupon_textbox').show();
    jQuery('.ct-display-coupon-code').hide();
    jQuery('.coupon_invalid_error').hide();
    var s_m_qty = jQuery(this).data('duration_value');
    var s_m_rate = jQuery(this).data('rate');
    var service_id = jQuery(this).data('service_id');
    var method_id = jQuery(this).data('method_id');
    var method_name = jQuery(this).data('method_name');
    var units_id = jQuery(this).data('units_id');
    var type = jQuery(this).data('type');
    var frequently_discount_id = jQuery(".edit_frequently_discount_radio").val();
    var m_name = jQuery(this).data('mnamee');

    jQuery.ajax({
        type: 'post',
        data: {
            'method_id': method_id,
            'service_id': service_id,
            's_m_qty': s_m_qty,
            's_m_rate': s_m_rate,
            'method_name': method_name,
            'units_id': units_id,
            'type': type,
            'frequently_discount_id': frequently_discount_id,
            'add_to_cart': 1
        },
        url: site_url + "front/edit_booking_firststep.php",
        success: function (res) {
            jQuery('.freq_discount_display').show();
            jQuery('.hide_right_side_box').show();
            /* jQuery('.partial_amount_hide_on_load').show(); */
            jQuery('.empty_cart_error').hide();
            jQuery('.no_units_in_cart_error').hide();
            jQuery('.coupon_invalid_error').hide();
            jQuery("#total_cart_count").val('2');
            var cart_session_data = jQuery.parseJSON(res);
            jQuery('#no_units_in_cart_err').val(cart_session_data.unit_status);
            jQuery('#no_units_in_cart_err_count').val(cart_session_data.unit_require);
            if (cart_session_data.status == 'update') {
                jQuery(".edit_cart_empty_msg").hide();
                jQuery('.update_qty_of_s_m_' + cart_session_data.method_name_without_space).html(cart_session_data.s_m_html);
                jQuery('.update_qty_of_s_m_' + cart_session_data.method_name_without_space).val('data-service_id', service_id);
                jQuery('.update_qty_of_s_m_' + cart_session_data.method_name_without_space).val('data-method_id', method_id);
                jQuery('.update_qty_of_s_m_' + cart_session_data.method_name_without_space).val('data-units_id', units_id);

                jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                jQuery('.partial_amount').html(cart_session_data.partial_amount);
                jQuery('.remain_amount').html(cart_session_data.remain_amount);
                jQuery('.cart_tax').html(cart_session_data.cart_tax);
                jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                jQuery('.cart_total').html(cart_session_data.total_amount);
                jQuery('.total_time_duration_text').html(cart_session_data.duration_text);
            } else if (cart_session_data.status == 'insert') {
                jQuery('.hideduration_value').show();
                jQuery(".edit_cart_empty_msg").hide();
                jQuery('.cart_item_listing' + service_id).append(cart_session_data.s_m_html);
                jQuery('.partial_amount').html(cart_session_data.partial_amount);
                jQuery('.remain_amount').html(cart_session_data.remain_amount);
                jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                jQuery('.cart_tax').html(cart_session_data.cart_tax);
                jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                jQuery('.cart_total').html(cart_session_data.total_amount);
                jQuery('.total_time_duration_text').html(cart_session_data.duration_text);
            } else if (cart_session_data.status == 'firstinsert') {
                jQuery('.hideduration_value').show();
                jQuery(".edit_cart_empty_msg").hide();
                jQuery('.cart_item_listing' + service_id).append(cart_session_data.s_m_html);
                jQuery('.partial_amount').html(cart_session_data.partial_amount);
                jQuery('.remain_amount').html(cart_session_data.remain_amount);
                jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                jQuery('.cart_tax').html(cart_session_data.cart_tax);
                jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                jQuery('.cart_total').html(cart_session_data.total_amount);
                jQuery('.total_time_duration_text').html(cart_session_data.duration_text);
            } else if (cart_session_data.status == 'empty calculation') {
                jQuery('.hideduration_value').hide();
                jQuery('.total_time_duration_text').html('');
                jQuery('.freq_discount_display').hide();
                jQuery('.partial_amount_hide_on_load').hide();
                jQuery('.hide_right_side_box').hide();
                jQuery(".edit_cart_empty_msg").show();
                jQuery(".cart_item_listing" + service_id).empty();
                jQuery(".cart_sub_total").empty();
                jQuery(".cart_tax").empty();
                jQuery(".cart_total").empty();
                jQuery(".remain_amount").empty();
                jQuery(".partial_amount").empty();
                jQuery(".cart_discount").empty();
                jQuery('.frequent_discount').empty();
            } else if (cart_session_data.status == 'delete particuler') {
                jQuery(".edit_cart_empty_msg").hide();
                jQuery(".update_qty_of_s_m_" + m_name).remove();
                jQuery('.partial_amount').html(cart_session_data.partial_amount);
                jQuery('.remain_amount').html(cart_session_data.remain_amount);
                jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                jQuery('.cart_discount').html('- ' + cart_session_data.cart_discount);
                jQuery('.cart_tax').html(cart_session_data.cart_tax);
                jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                jQuery('.cart_total').html(cart_session_data.total_amount);
                jQuery('.total_time_duration_text').html(cart_session_data.duration_text);
            }
        }
    });
});

jQuery(document).on('click', '.edit_add_addon_in_cart_for_multipleqty', function () {
    jQuery('.freq_disc_empty_cart_error').hide();
    var site_url = siteurlObj.site_url;
    var ajax_url = ajaxurlObj.ajax_url;
    jQuery('.coupon_display').hide();
    jQuery('.hide_coupon_textbox').show();
    jQuery('.ct-display-coupon-code').hide();
    jQuery('.coupon_invalid_error').hide();
    var s_m_qty = jQuery(this).data('duration_value');
    var s_m_rate = jQuery(this).data('rate');
    var service_id = jQuery(this).data('service_id');
    var method_id = jQuery(this).data('method_id');
    var method_name = jQuery(this).data('method_name');
    var units_id = jQuery(this).data('units_id');
    var type = jQuery(this).data('type');
    var frequently_discount_id = jQuery(".edit_frequently_discount_radio").val();
    var m_name = jQuery(this).data('mnamee');
    var status = jQuery(this).data('status');

    if (parseInt(status) == 2) {
        jQuery(this).data('status', '1');

        jQuery.ajax({
            type: 'post',
            data: {
                'method_id': method_id,
                'service_id': service_id,
                's_m_qty': s_m_qty,
                's_m_rate': s_m_rate,
                'method_name': method_name,
                'units_id': units_id,
                'type': type,
                'frequently_discount_id': frequently_discount_id,
                'add_to_cart': 1
            },
            url: site_url + "front/edit_booking_firststep.php",
            success: function (res) {
                jQuery('.freq_discount_display').show();
                jQuery('.hide_right_side_box').show();
                /* jQuery('.partial_amount_hide_on_load').show(); */
                jQuery('.empty_cart_error').hide();
                jQuery('.coupon_invalid_error').hide();
                jQuery("#total_cart_count").val('2');
                var cart_session_data = jQuery.parseJSON(res);
                jQuery('#no_units_in_cart_err').val(cart_session_data.unit_status);
                jQuery('#no_units_in_cart_err_count').val(cart_session_data.unit_require);
                if (cart_session_data.status == 'insert') {
                    jQuery(".edit_cart_empty_msg").hide();
                    jQuery('.cart_item_listing' + service_id).append(cart_session_data.s_m_html);
                    jQuery('.partial_amount').html(cart_session_data.partial_amount);
                    jQuery('.remain_amount').html(cart_session_data.remain_amount);
                    jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                    jQuery('.cart_tax').html(cart_session_data.cart_tax);
                    jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                    jQuery('.cart_total').html(cart_session_data.total_amount);
                    jQuery('.total_time_duration_text').html(cart_session_data.duration_text);
                } else if (cart_session_data.status == 'empty calculation') {
                    jQuery('.hideduration_value').hide();
                    jQuery('.total_time_duration_text').html('');
                    jQuery('.total_time_duration_text').html('');
                    jQuery('.freq_discount_display').show();
                    jQuery('.partial_amount_hide_on_load').hide();
                    jQuery('.hide_right_side_box').hide();
                    jQuery(".edit_cart_empty_msg").show();
                    jQuery(".cart_item_listing" + service_id).empty();
                    jQuery(".cart_sub_total").empty();
                    jQuery(".frequent_discount").empty();
                    jQuery(".cart_tax").empty();
                    jQuery(".cart_total").empty();
                    jQuery(".remain_amount").empty();
                    jQuery(".partial_amount").empty();
                    jQuery(".cart_discount").empty();
                } else if (cart_session_data.status == 'delete particuler') {
                    jQuery(".edit_cart_empty_msg").hide();
                    jQuery(".update_qty_of_s_m_" + m_name).remove();
                    jQuery('.partial_amount').html(cart_session_data.partial_amount);
                    jQuery('.remain_amount').html(cart_session_data.remain_amount);
                    jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                    jQuery('.cart_discount').html('- ' + cart_session_data.cart_discount);
                    jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                    jQuery('.cart_tax').html(cart_session_data.cart_tax);
                    jQuery('.cart_total').html(cart_session_data.total_amount);
                    jQuery('.total_time_duration_text').html(cart_session_data.duration_text);
                }
            }
        });
    } else {
        jQuery(this).data('status', '2');

        jQuery.ajax({
            type: 'post',
            data: {
                'method_id': method_id,
                'service_id': service_id,
                's_m_qty': s_m_qty,
                's_m_rate': s_m_rate,
                'method_name': method_name,
                'units_id': units_id,
                'type': type,
                'frequently_discount_id': frequently_discount_id,
                'add_to_cart': 1
            },
            url: site_url + "front/edit_booking_firststep.php",
            success: function (res) {
                jQuery('.freq_discount_display').show();
                jQuery('.hide_right_side_box').show();
                /*  jQuery('.partial_amount_hide_on_load').show(); */
                jQuery('.empty_cart_error').hide();
                jQuery('.coupon_invalid_error').hide();
                jQuery("#total_cart_count").val('2');
                var cart_session_data = jQuery.parseJSON(res);
                jQuery('#no_units_in_cart_err').val(cart_session_data.unit_status);
                jQuery('#no_units_in_cart_err_count').val(cart_session_data.unit_require);
                if (cart_session_data.status == 'empty calculation') {
                    //    jQuery('.hideduration_value').hide();
                    jQuery('.total_time_duration_text').html('');
                    jQuery('.partial_amount_hide_on_load').hide();
                    jQuery('.hide_right_side_box').hide();
                    jQuery(".edit_cart_empty_msg").show();
                    jQuery(".cart_item_listing" + service_id).empty();
                    jQuery(".cart_sub_total").empty();
                    jQuery(".cart_tax").empty();
                    jQuery(".cart_total").empty();
                    jQuery(".frequent_discount").empty();
                    jQuery(".remain_amount").empty();
                    jQuery(".partial_amount").empty();
                    jQuery(".cart_discount").empty();
                } else if (cart_session_data.status == 'delete particuler') {
                    jQuery(".edit_cart_empty_msg").hide();
                    jQuery(".update_qty_of_s_m_" + m_name).remove();
                    jQuery('.partial_amount').html(cart_session_data.partial_amount);
                    jQuery('.remain_amount').html(cart_session_data.remain_amount);
                    jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
                    jQuery('.cart_discount').html('- ' + cart_session_data.cart_discount);
                    jQuery('.cart_tax').html(cart_session_data.cart_tax);
                    jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
                    jQuery('.cart_total').html(cart_session_data.total_amount);
                    jQuery('.total_time_duration_text').html(cart_session_data.duration_text);
                }
            }
        });
    }
});

jQuery(document).ready(function () {
    jQuery('[data-toggle="tooltip"]').tooltip({ 'placement': 'right' });
});

jQuery(document).on('click', ".ct_method_tab-slider--nav li,.ct_method_tab-slider--nav li.active", function () {
    if (!jQuery(this).hasClass('ct_method_tab-blank_li')) {
        var totallis = 0;
        var selectedli = 0;
        var currentli = jQuery(this).html();
        var divid = jQuery(this).data('id');
        var maindivid = jQuery(this).data('maindivid');
        jQuery('.ct_method_tab-slider--nav').each(function () {
            var common_id = jQuery(this).data('id');
            if (jQuery(".ct_method_tab-slider--nav_dynamic" + common_id + " li").length == 2) {
                jQuery(".ct_method_tab-slider--nav_dynamic" + common_id + " ul li").css('width', '50%');

            } else if (jQuery(".ct_method_tab-slider--nav_dynamic" + common_id + " li").length == 1) {
                jQuery(".ct_method_tab-slider--nav_dynamic" + common_id + " ul").append("<li class='ct_method_tab-slider-trigger ct_method_tab-blank_li'>&nbsp;</li><li class='ct_method_tab-slider-trigger ct_method_tab-blank_li'>&nbsp;</li>");
            }
        });
        jQuery('.ct_method_tab-slider--nav_dynamic' + maindivid + ' li').each(function () {
            if (jQuery(this).html() == currentli) {
                selectedli = totallis;
            }
            totallis++;
        });
        var leftpercent = 100 / totallis;
        var currentpercent = leftpercent * selectedli;
        jQuery('head').find('style').each(function () {
            var attr = jQuery(this).attr('data-dynmicstyle');
            if (typeof attr !== typeof undefined && attr !== false) {
                jQuery(this).remove();
            }
        });
        jQuery('<style data-dynmicstyle>.ct_method_tab-slider--nav_dynamic' + maindivid + ' .edit_ct_method_tab-slider-tabs.ct_methods_slide:after{width:' + leftpercent + '% !important;left:' + currentpercent + '% !important;}</style>').appendTo('head');
        jQuery(".ct_method_tab-slider--nav_dynamic" + maindivid + " li").removeClass("active");

        jQuery(".ct_method_tab-slider-trigger_dynamic" + divid).addClass("active");
    }
});
jQuery(document).ready(function () {

    jQuery(".cart_toggle_content").hide();
});
/* jQuery(document).on('click', ".cart_toggle_head", function() {
    var id = jQuery(this).data('id');
	
    jQuery('.cart_toggle_head').each(function(){
        var nID = jQuery(this).data('id');
        if(nID == id){
            if(jQuery('.ct_icon_check_'+nID).hasClass('p_m_toggle')){
                jQuery('.ct_icon_check_'+nID).removeClass('fa-plus');
        jQuery('.ct_icon_check_'+nID).removeClass('p_m_toggle');
                jQuery('.ct_icon_check_'+nID).addClass('fa-minus');
                jQuery("#cart_toggle_content_"+nID).slideToggle();
            }else{
                jQuery('.ct_icon_check_'+nID).addClass('fa-plus');
        jQuery('.ct_icon_check_'+nID).addClass('p_m_toggle');
                jQuery('.ct_icon_check_'+nID).removeClass('fa-minus');
                jQuery("#cart_toggle_content_"+nID).slideUp();
            }
        }else{
            jQuery('.ct_icon_check_'+nID).addClass('fa-plus');
      jQuery('.ct_icon_check_'+nID).addClass('p_m_toggle');
            jQuery('.ct_icon_check_'+nID).removeClass('fa-minus');
            jQuery("#cart_toggle_content_"+nID).slideUp();
        }
    });
}); */
