var ct_postalcode_status_check = ct_postalcode_statusObj.ct_postalcode_status;
var ct_postalcode_zip_status = ct_postalcode_statusObj.zip_show_status;
var guest_user_status = "off";
var get_all_postal_code = [];
var ser_ids_dynamic = 0;
/* front language dropdown show hide list */
jQuery(document).on("click", ".select-custom", function () {
  jQuery(".common-selection-main").addClass("clicked");
  jQuery(".custom-dropdown").slideDown();
});
/* front language select on click on custom */
jQuery(document).on("click", ".select_custom", function () {
  jQuery("#selected_custom").html(jQuery(this).html());
  jQuery(".common-selection-main").removeClass("clicked");
  jQuery(".custom-dropdown").slideUp();
});
/* tooltipster jquery */
jQuery(document).ready(function () {
  jQuery(document).on("click", "#otp_model_close", function () {
    jQuery('#verify_otp').modal('hide');
    jQuery(".checkmark").hide();
  });

  jQuery(".fancy_input").on("keyup", function () {
    if (jQuery(this).val().length > 0) {
      jQuery(this).parent().addClass("focused_label_wrap");
    } else if (jQuery(this).val().length <= 0) {
      jQuery(this).parent().removeClass("focused_label_wrap");
    }
  });
  jQuery(".phone_no_wrap .fancy_input").on("keyup", function () {
    if (jQuery(this).val().length > 0) {
      jQuery(".phone_no_wrap").addClass("focused_label_wrap");
    } else if (jQuery(this).val().length <= 0) {
      jQuery(".phone_no_wrap").removeClass("focused_label_wrap");
    }
  });
  jQuery(".ct-tooltip").tooltipster({
    animation: "grow",
    delay: 20,
    theme: "tooltipster-shadow",
    trigger: "hover"
  });
  jQuery(".ct-tooltipss").tooltipster({
    animation: "grow",
    delay: 20,
    theme: "tooltipster-shadow",
    trigger: "hover"
  });
  jQuery(".ct-tooltip-services").tooltipster({
    animation: "grow",
    side: "bottom",
    interactive: "true",
    theme: "tooltipster-shadow",
    trigger: "hover",
    delayTouch: 300,
    maxWidth: 400,
    functionPosition: function (instance, helper, position) {
      position.coord.top -= 25;
      return position;
    },
    contentAsHTML: "true"
  });

  jQuery(".ct-tooltip-services-addons").tooltipster({
    animation: "grow",
    side: "bottom",
    interactive: "true",
    theme: "tooltipster-shadow",
    trigger: "hover",
    delayTouch: 300,
    maxWidth: 400,
    functionPosition: function (instance, helper, position) {
      position.coord.top -= 25;
      return position;
    },
    contentAsHTML: "true"
  });


  /* card payment validations */
  jQuery("input.cc-number").payment("formatCardNumber");
  jQuery("input.cc-cvc").payment("formatCardCVC");
  jQuery("input.cc-exp-month").payment("restrictNumeric");
  jQuery("input.cc-exp-year").payment("restrictNumeric");
  jQuery("body").niceScroll();
  jQuery(".common-data-dropdown").niceScroll();
  jQuery(".ct-services-dropdown").niceScroll();
  var frequently_discount_id = jQuery("input[name=frequently_discount_radio]:checked").attr("data-id");
  var frequently_discount_name = jQuery("input[name=frequently_discount_radio]:checked").attr("data-name");
  if (frequently_discount_id == 0) {
    jQuery(".f_dis_img").hide();
  } else {
    jQuery(".f_dis_img").show();
    jQuery(".f_discount_name").text(frequently_discount_name);
  }
  jQuery(".ct-loading-main").hide();
  var subheader_status = subheaderObj.subheader_status;
  if (subheader_status == "Y") {
    jQuery(".ct-sub").show();
  } else {
    jQuery(".ct-sub").hide();
  }
  if (ct_postalcode_status_check == "Y") {
    jQuery(".ct_remove_id").attr("id", "");
    jQuery(document).on("click", ".ct_remove_id", function () {
      jQuery("#ct_postal_code").focus();
      jQuery("#ct_postal_code").keyup();
    });
  }
  jQuery(".ct-sub").show();
  jQuery(".ct-loading-main-complete_booking").hide();
  jQuery(".remove_guest_user_preferred_email").hide();
  jQuery(".show_methods_after_service_selection").hide();
  jQuery(".freq_discount_display").hide();
  jQuery(".hide_allsss_addons").hide();
  jQuery(".partial_amount_hide_on_load").hide();
  jQuery(".hide_right_side_box").hide();
  jQuery(".client_logout").hide();
  jQuery(".service_not_selected_error_d2").hide();
  /* jQuery(".postal_code_error").show();
  jQuery(".postal_code_error").html(errorobj_please_enter_postal_code); */
  jQuery(".hideservice_name").hide();
  jQuery(".hidedatetime_value").hide();
  //jQuery(".hideduration_value").hide();
  jQuery(".s_m_units_design_1").hide();
  jQuery(".s_m_units_design_2").hide();
  jQuery(".s_m_units_design_3").hide();
  jQuery(".s_m_units_design_4").hide();
  jQuery(".s_m_units_design_5").hide();
  jQuery(".ct-provider-list").hide();
  /*Coupon Apply*/
  jQuery(".ct-display-coupon-code").hide();
  jQuery(".coupon_display").hide();

  /* Jay */
  jQuery(".user_coupon_display").hide();
  jQuery(".ct-display-user-coupon-code").hide();
  /* */

  /* Jay */
  jQuery(".ct-display-referral-code").hide();
  /* */

  /* user contact no. */
  var site_url = siteurlObj.site_url;
  var country_alpha_code = countrycodeObj.alphacode;
  var allowed_country_alpha_code = countrycodeObj.allowed;
  var array = allowed_country_alpha_code.split(",");
  var phone_visi = phone_status.statuss;
  if (phone_visi == "on") {
    if (allowed_country_alpha_code = "") {
      jQuery("#ct-user-phone").intlTelInput({
        onlyCountries: array,
        autoPlaceholder: false,
        utilsScript: site_url + "assets/js/utils.js"
      });
      jQuery(".selected-flag .iti-flag").addClass(country_alpha_code);
      jQuery(".selected-flag").attr("title", countrycodeObj.countrytitle);
    } else {
      jQuery("#ct-user-phone").intlTelInput({
        autoPlaceholder: false,
        utilsScript: site_url + "assets/js/utils.js"
      });
      jQuery(".selected-flag .iti-flag").addClass(country_alpha_code);
      jQuery(".selected-flag").attr("title", countrycodeObj.countrytitle);
    }
  }
  /*  create the back to top button */
  jQuery("body").prepend('<a href="javascript:void(0)" class="ct-back-to-top"></a>');
  var amountScrolled = 500;
  jQuery(window).scroll(function () {
    if (jQuery(window).scrollTop() > amountScrolled) {
      jQuery("a.ct-back-to-top").fadeIn("slow");
    } else {
      jQuery("a.ct-back-to-top").fadeOut("slow");
    }
  });
  jQuery("a.ct-back-to-top, a.ct-simple-back-to-top").click(function () {
    jQuery("html, body").animate({ scrollTop: 0 }, 2000);
    return false;
  });
  var password_check = check_password;
  jQuery("#user_login_form").validate({
    rules: {
      ct_user_name: { required: true, email: true },
      ct_user_pass: { required: true, minlength: password_check.min, maxlength: password_check.max }
    },
    messages: {
      ct_user_name: { required: errorobj_please_enter_email_address, email: errorobj_please_enter_valid_email_address },
      ct_user_pass: { required: errorobj_please_enter_password, minlength: errorobj_min_ps, maxlength: errorobj_max_ps }
    }
  });
  var front_url = fronturlObj.front_url;
  jQuery.validator.addMethod("pattern_phone", function (value, element) {
    return this.optional(element) || /^[0-9+]*$/.test(value);
  }, "Enter Only Numerics");
  jQuery.validator.addMethod("pattern_zip", function (value, element) {
    return this.optional(element) || /^[a-zA-Z 0-9\-\ ]*$/.test(value);
  }, "Enter Only Alphabets");
  jQuery.validator.addMethod("pattern_name", function (value, element) {
    return this.optional(element) || /^[a-zA-Z ']+$/.test(value);
  }, "Enter Only Alphabets");
  jQuery.validator.addMethod("pattern_city_state", function (value, element) {
    return this.optional(element) || /^[a-zA-Z &]+$/.test(value);
  }, "Enter Only Alphabets");
  var phone_check = phone_status;
  var password_check = check_password;
  var fn_check = check_fn;
  var ln_check = check_ln;
  var address_check = check_addresss;
  var zip_check = check_zip_code;
  var city_check = check_city;
  var state_check = check_state;
  var notes_check = check_notes;
  /* validaition condition*/
  jQuery("#user_details_form").validate();
  if (appoint_details.status == "on") {
    if (check_addresss.statuss == "on" && check_addresss.required == "Y") {
      jQuery("#app-street-address").rules("add",
        {
          required: true, minlength: check_addresss.min, maxlength: check_addresss.max,
          messages: { required: errorobj_req_sa, minlength: errorobj_min_sa, maxlength: errorobj_max_sa }
        });
    }
    if (check_zip_code.statuss == "on" && check_zip_code.required == "Y") {
      jQuery("#app-zip-code").rules("add", { required: true, minlength: check_zip_code.min, maxlength: check_zip_code.max, messages: { required: errorobj_req_zc, minlength: errorobj_min_zc, maxlength: errorobj_max_zc } });
    }
    if (check_city.statuss == "on" && check_city.required == "Y") {
      jQuery("#app-city").rules("add",
        {
          required: true, minlength: check_city.min, maxlength: check_city.max,
          messages: { required: errorobj_req_ct, minlength: errorobj_min_ct, maxlength: errorobj_max_ct }
        });
    }
    if (check_state.statuss == "on" && check_state.required == "Y") {
      jQuery("#app-state").rules("add",
        {
          required: true, minlength: check_state.min, maxlength: check_state.max,
          messages: {
            required: errorobj_req_st, minlength: errorobj_min_st, maxlength: errorobj_max_st
          }
        });
    }
  }
  if (fn_check.statuss == "on" && fn_check.required == "Y") {
    jQuery("#ct-first-name").rules("add",
      {
        required: true, minlength: fn_check.min, maxlength: fn_check.max, pattern_name: true,
        messages: { required: errorobj_req_fn, minlength: errorobj_min_fn, maxlength: errorobj_max_fn }
      });
  }
  if (ln_check.statuss == "on" && ln_check.required == "Y") {
    jQuery("#ct-last-name").rules("add",
      {
        required: true, minlength: ln_check.min, maxlength: ln_check.max, pattern_name: true,
        messages: { required: errorobj_req_ln, minlength: errorobj_min_ln, maxlength: errorobj_max_ln }
      });
  }
  if (phone_check.statuss == "on" && phone_check.required == "Y") {
    jQuery("#ct-user-phone").rules("add",
      {
        required: true, minlength: phone_check.min, maxlength: phone_check.max,
        messages: { required: errorobj_req_ph, minlength: errorobj_min_ph, maxlength: errorobj_max_ph }
      });
  }
  if (address_check.statuss == "on" && address_check.required == "Y") {
    jQuery("#ct-street-address").rules("add",
      {
        required: true, minlength: address_check.min, maxlength: address_check.max,
        messages: { required: errorobj_req_sa, minlength: errorobj_min_sa, maxlength: errorobj_max_sa }
      });
  }
  if (zip_check.statuss == "on" && zip_check.required == "Y") {
    jQuery("#ct-zip-code").rules("add",
      {
        required: true, minlength: zip_check.min, maxlength: zip_check.max,
        messages: { required: errorobj_req_zc, minlength: errorobj_min_zc, maxlength: errorobj_max_zc }
      });
  }
  if (city_check.statuss == "on" && city_check.required == "Y") {
    jQuery("#ct-city").rules("add",
      {
        required: true, minlength: city_check.min, maxlength: city_check.max,
        messages: { required: errorobj_req_ct, minlength: errorobj_min_ct, maxlength: errorobj_max_ct }
      });
  }
  if (state_check.statuss == "on" && state_check.required == "Y") {
    jQuery("#ct-state").rules("add",
      {
        required: true, minlength: state_check.min, maxlength: state_check.max,
        messages: { required: errorobj_req_st, minlength: errorobj_min_st, maxlength: errorobj_max_st }
      });
  }
  if (notes_check.statuss == "on" && notes_check.required == "Y") {
    jQuery("#ct-notes").rules("add",
      {
        required: true, minlength: notes_check.min, maxlength: notes_check.max,
        messages: { required: errorobj_req_srn, minlength: errorobj_min_srn, maxlength: errorobj_max_srn }
      });
  }
  if (password_check.statuss == "on" && password_check.required == "Y") {
    jQuery("#ct-preffered-pass").rules("add",
      {
        required: true, minlength: password_check.min, maxlength: password_check.max,
        messages: { required: errorobj_please_enter_password, minlength: errorobj_min_ps, maxlength: errorobj_max_ps }
      });

    jQuery("#ct-email").rules("add",
      {
        required: true, email: true, remote: {
          url: front_url + "firststep.php",
          type: "POST",
          async: false,
          data: {
            email: function () { return jQuery("#ct-email").val(); },
            action: "check_user_email"
          }
        },
        messages: { required: errorobj_please_enter_email_address, email: errorobj_please_enter_valid_email_address, remote: errorobj_email_already_exists }
      });
  }
  /* end validaition condition*/
  if (jQuery(".guest-user").is(":checked")) {
    jQuery("#ct-email-guest").val("");
    jQuery("#ct-user-name").val("");
    jQuery("#ct-user-pass").val("");
    jQuery("#ct-preffered-name").val("");
    jQuery("#ct-preffered-pass").val("");
    jQuery("#ct-first-name").val("");
    jQuery("#ct-last-name").val("");
    jQuery("#ct-email").val("");
    jQuery("#ct-user-phone").val("");
    jQuery("#ct-street-address").val("");
    jQuery("#ct-zip-code").val("");
    jQuery("#ct-city").val("");
    jQuery("#ct-state").val("");
    jQuery("#ct-notes").val("");
    jQuery(".ct-new-user-details").show("blind", { direction: "vertical" }, 700);
    jQuery(".ct-login-existing").hide("blind", { direction: "vertical" }, 300);
    jQuery(".ct-peronal-details").show("blind", { direction: "vertical" }, 300);
    jQuery(".remove_preferred_password_and_preferred_email").hide("blind", { direction: "vertical" }, 300);
    jQuery(".remove_guest_user_preferred_email").show("blind", { direction: "vertical" }, 300);
    if (jQuery(".remove_zip_code_class").hasClass("ct-md-4")) {
      jQuery(".remove_zip_code_class").removeClass("ct-md-4");
      jQuery(".remove_zip_code_class").addClass("ct-md-6");
    }
    if (jQuery(".remove_city_class").hasClass("ct-md-4")) {
      jQuery(".remove_city_class").removeClass("ct-md-4");
      jQuery(".remove_city_class").addClass("ct-md-6");
    }
    if (jQuery(".remove_state_class").hasClass("ct-md-4")) {
      jQuery(".remove_state_class").removeClass("ct-md-4");
      jQuery(".remove_state_class").addClass("ct-md-6");
    }
    guest_user_status = "on";
  }
  jQuery(".space_between_date_time").hide();
  jQuery(".special_day").hide();
  var site_url = siteurlObj.site_url;
  var ajax_url = ajaxurlObj.ajax_url;
  jQuery.ajax({
    type: "POST",
    url: ajax_url + "calendar_ajax.php",
    data: { "get_calendar_on_page_load": 1 },
    success: function (res) {
      jQuery(".cal_info").html(res);
      var d = new Date();
      var month = d.getMonth() + 1;
      var day = d.getDate();
      var year = d.getFullYear();
      var output = day + "-" + (month < 10 ? "0" : "") + month + "-" + year;
      var selected_dates = jQuery(".selected_date").data("selected_dates");
      var cur_dates = jQuery(".selected_date").data("cur_dates");
      if (output == cur_dates) {
        jQuery(".by_default_today_selected").addClass("active_today");
      }
      cleanto_sidebar_scroll();
    }
  });
  jQuery.ajax({
    type: "POST",
    url: ajax_url + "front_ajax.php",
    data: { "get_postal_code": 1 },
    success: function (res) {
      get_all_postal_code = jQuery.parseJSON(res);
    }
  });
  /* validation for reset_password.php */
  jQuery("#forget_pass").submit(function (event) {
    event.preventDefault();
    event.stopImmediatePropagation();
  });
  jQuery("#forget_pass").validate({
    rules: {
      rp_user_email: {
        required: true,
        email: true,
      }
    },
    messages: {
      rp_user_email: {
        required: errorobj_please_enter_email_address,
        email: errorobj_please_enter_valid_email_address
      },
    }
  });
  /* validation for reset_new_password.php */
  jQuery("#reset_new_passwd").submit(function (event) {
    event.preventDefault();
    event.stopImmediatePropagation();
  });
  jQuery.validator.addMethod("noSpace", function (value, element) {
    return value.indexOf(" ") < 0 && value != "";
  }, "No space allowed");
  jQuery("#reset_new_passwd").validate({
    rules: {
      n_password: {
        required: true,
        minlength: 8,
        maxlength: 10,
        noSpace: true
      },
      rn_password: {
        required: true,
        minlength: 8,
        maxlength: 10,
        noSpace: true
      }
    },
    messages: {
      n_password: {
        required: errorobj_please_enter_new_password,
        minlength: errorobj_password_must_be_8_character_long,
        maxlength: errorobj_please_enter_maximum_10_chars
      },
      rn_password: {
        required: errorobj_please_enter_confirm_password,
        minlength: errorobj_password_must_be_8_character_long,
        maxlength: errorobj_please_enter_maximum_10_chars
      },
    }
  });
  var front_url = fronturlObj.front_url;
  jQuery.ajax({
    type: "post",
    data: { check_for_option: 1 },
    url: front_url + "firststep.php",
    success: function (res) {
      if (jQuery.trim(res) != "") {
        window.location = front_url + "maintainance.php";
      }
    }
  });
  jQuery('[data-toggle="tooltip"]').tooltip({ "placement": "right" });
  if (is_login_user == "Y") {
    var site_url = siteurlObj.site_url;
    var ajax_url = ajaxurlObj.ajax_url;
    jQuery(".add_show_error_class_for_login").each(function () {
      jQuery(this).trigger("keyup");
    });
    jQuery(".add_show_error_class").each(function () {
      var id = jQuery(this).attr("id");
      jQuery(this).removeClass("error");
      jQuery("#" + id).parent().removeClass("error");
      jQuery(this).removeClass("show-error");
      jQuery("#" + id).parent().removeClass("show-error");
      jQuery(".intl-tel-input").parent().removeClass("show-error");
    });
    var existing_username = jQuery("#ct-user-name").val();
    var existing_password = jQuery("#ct-user-pass").val();
    if (!jQuery("#user_login_form").valid()) { return false; }
    dataString = { action: "get_login_user_data" };
    jQuery.ajax({
      type: "POST",
      url: ajax_url + "front_ajax.php",
      data: dataString,
      success: function (response) {
        var userdata = jQuery.parseJSON(response);
        if (userdata.status == "No Login") {
          is_login_user = "N";
          jQuery(".fancy_input").each(function () { jQuery(this).trigger("keyup"); });
          return false;
        } else if (userdata.status == "Incorrect Email Address or Password") {
          is_login_user = "N";
          jQuery(".fancy_input").each(function () { jQuery(this).trigger("keyup"); });
          return false;
        } else {
          is_login_user = "Y";
          jQuery("#check_login_click").val("clicked");
          jQuery(".client_logout").css("display", "block");
          jQuery(".client_logout").show();
          jQuery(".fname").text(userdata.firstname);
          jQuery(".lname").text(userdata.lastname);
          jQuery("#ct-email").val(userdata.email);
          jQuery("#ct-user-name").val(userdata.email);
          jQuery("#existing-user").attr("checked", true);
          jQuery(".hide_login_btn").hide();
          jQuery(".hide_radio_btn_after_login").hide();
          jQuery(".hide_email").hide();
          jQuery(".hide_login_email").hide();
          jQuery(".hide_password").hide();
          jQuery(".ct-peronal-details").show();
          jQuery(".login_unsuccessfull").hide();
          jQuery(".ct-new-user-details").hide();
          jQuery(".ct-sub").hide();
          jQuery("#ct-first-name").val(userdata.firstname);
          jQuery("#ct-last-name").val(userdata.lastname);
          jQuery("#ct-user-phone").intlTelInput("setNumber", userdata.phone);
          if (check_addresss.statuss == "on") { jQuery("#ct-street-address").val(userdata.address); }
          if (check_zip_code.statuss == "on") { jQuery("#ct-zip-code").val(userdata.zip); }
          if (check_city.statuss == "on") { jQuery("#ct-city").val(userdata.city); }
          if (check_state.statuss == "on") { jQuery("#ct-state").val(userdata.state); }
          jQuery("#ct-notes").val(userdata.notes);
          if (userdata.vc_status == "N") {
            jQuery("#vaccum-no").attr("checked", true);
          } else {
            jQuery("#vaccum-yes").attr("checked", true);
          }
          if (userdata.p_status == "N") {
            jQuery("#parking-no").attr("checked", true);
          } else {
            jQuery("#parking-yes").attr("checked", true);
          }
          var con_staatus = userdata.contact_status;
          if (con_staatus == "I'll be at home" || con_staatus == "Please call me" || con_staatus == "The key is with the doorman") {
            jQuery("#contact_status").val(userdata.contact_status);
          } else {
            jQuery("#contact_status").val("Other");
            jQuery(".ct-option-others").show();
            jQuery("#other_contact_status").val(userdata.contact_status);
          }
          jQuery(".fancy_input").each(function () { jQuery(this).trigger("keyup"); });
        }
      }
    });
  }
});


/* scroll to next step */
jQuery(document).on("click", ".ct-service", function () {
  jQuery("html, body").stop().animate({ "scrollTop": jQuery(".ct-scroll-meth-unit").offset().top - 30 }, 800, "swing", function () { });
});
/* forget password */
jQuery(document).on("click", "#ct_forget_password", function () {
  jQuery("#rp_user_email").val("");
  jQuery(".forget_pass_correct").hide();
  jQuery(".forget_pass_incorrect").hide();
  jQuery(".ct-front-forget-password").addClass("show-data");
  jQuery(".ct-front-forget-password").removeClass("hide-data");
  jQuery(".main").css("display", "block");
});
jQuery(document).on("click", "#ct_login_user", function () {
  jQuery(".ct-front-forget-password").removeClass("show-data");
  jQuery(".ct-front-forget-password").addClass("hide-data");
  jQuery(".main").css("display", "none");
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
  jQuery(".ct-duration-btn").each(function () {
    jQuery(this).removeClass("duration-box-selected");
  });
  jQuery(this).addClass("duration-box-selected");
});
/* for show how many addon counting when checked */
jQuery(document).on("click", 'input[type="checkbox"]', function () {

  if (jQuery(".addon-checkbox").is(":checked")) {
    jQuery(".common-selection-main.addon-select").show();
  } else {
    jQuery(".common-selection-main.addon-select").hide();
  }
});
/* addons */
jQuery(document).on("click", ".ct-addon-btn", function () {
  var curr_methodname = jQuery(this).attr("data-method_name");
  jQuery(".ct-addon-btn").each(function () {
    if (jQuery(this).attr("data-method_name") == curr_methodname) {
      jQuery(this).removeClass("ct-addon-selected");
    }
  });
  jQuery(this).addClass("ct-addon-selected");
});
/* checkout payment method listing show hide */
jQuery(document).on("click", ".cccard", function () {
  var test = jQuery(this).val();
  jQuery(".common-payment-style").show("blind", { direction: "vertical" }, 300);
});
jQuery(document).on("click", "input[name=payment-methods]", function () {
  var abc = jQuery(this).val();
  if (jQuery(this).hasClass("cccard")) {
    jQuery(".common-payment-style-bank-transfer").hide();
    jQuery(".partial_amount_hide_on_load").show();
    jQuery("#wallet").removeAttr('checked');
  } else if (jQuery(this).hasClass("pay-cash")) {
    jQuery(".common-payment-style").hide();
    jQuery(".common-payment-style-bank-transfer").hide();
    jQuery(".partial_amount_hide_on_load").show();
    jQuery("#wallet").removeAttr('checked');
  } else {
    jQuery(".common-payment-style").hide();
    jQuery(".common-payment-style-bank-transfer").hide();
    jQuery(".partial_amount_hide_on_load").hide();
    jQuery("#pay-cash").removeAttr('checked');
    jQuery("#pay-card").removeAttr('checked');
  }
});

/* bank transfer */
jQuery(document).on("click", ".bank_transfer", function () {
  jQuery(".common-payment-style-bank-transfer").show("blind", { direction: "vertical" }, 300);
  jQuery("#wallet").removeAttr('checked');
});
jQuery(document).on("click", "input[name=payment-methods]", function () {
  if (jQuery(this).hasClass("bank_transfer")) {
    jQuery(".common-payment-style").hide();
  } else {
    jQuery(".common-payment-style-bank-transfer").hide();
  }
});
/* see more instructions in service popup */
jQuery(document).on("click", ".show-more-toggler", function () {
  jQuery(".bullet-more").toggle("blind", { direction: "vertical" }, 500);
  jQuery(".show-more-toggler:after").addClass("rotate");
});
/* right side scrolling cart */
var scrollable_cart_value = scrollable_cartObj.scrollable_cart;
if (scrollable_cart_value == "Y") {
  function cleanto_sidebar_scroll() {
    var $sidebar = jQuery(".ct-price-scroll"),
      $window = jQuery(window),
      offset = $sidebar.offset(),
      sel_service = jQuery(".sel-service").text();

    if (sel_service != "") {
      $window.scroll(function () {
        if (offset.top > $window.scrollTop()) {
          $sidebar.stop().animate({
            marginTop: 20
          });
        } else {
          $sidebar.stop().animate({
            marginTop: ($window.scrollTop() - offset.top) + 40
          });
        }
      });
    } else {
      $window.scroll(function () {
        if (offset.top > $window.scrollTop()) {
          $sidebar.stop().animate({
            marginTop: 20
          });
        } else {
          $sidebar.stop().animate({
            marginTop: ($window.scrollTop() - offset.top) + 20
          });
        }
      });
    }
  }
} else {
  function cleanto_sidebar_scroll() { }
}
/************* Code by developer side --- ****************/
jQuery(document).on("keyup keydown blur", ".add_show_error_class", function (event) {
  var id = jQuery(this).attr("id");
  var Number = /^[0-9]*$/;
  if (jQuery(this).hasClass("error")) {
    jQuery(this).removeClass("error");
    jQuery("#" + id).parent().removeClass("error");
    jQuery(this).addClass("show-error");
    jQuery("#" + id).parent().addClass("show-error");
    if (jQuery("#ct-user-phone").val() != "") {
      if (!jQuery("#ct-user-phone").val().match(Number)) {
        jQuery(".intl-tel-input").parent().addClass("show-error");
      }
    }
  } else {
    jQuery(this).removeClass("error");
    jQuery("#" + id).parent().removeClass("error");
    jQuery(this).removeClass("show-error");
    jQuery("#" + id).parent().removeClass("show-error");
    if (jQuery("#ct-user-phone").val() != "") {
      if (jQuery("#ct-user-phone").val().match(Number)) {
        jQuery(".intl-tel-input").parent().removeClass("show-error");
      }
    }
  }
});
jQuery(document).on("keyup keydown blur", ".add_show_error_class_for_login", function (event) {
  var id = jQuery(this).attr("id");
  if (jQuery(this).hasClass("error")) {
    jQuery(this).removeClass("error");
    jQuery("#" + id).parent().removeClass("error");
    jQuery(this).addClass("show-error");
    jQuery("#" + id).parent().addClass("show-error");
  } else {
    jQuery(this).removeClass("error");
    jQuery("#" + id).parent().removeClass("error");
    jQuery(this).removeClass("show-error");
    jQuery("#" + id).parent().removeClass("show-error");
  }
});
jQuery(document).ready(function () {
  var two_checkout_status = twocheckout_Obj.twocheckout_status;
  if (two_checkout_status == "Y") {
    TCO.loadPubKey("sandbox");
  }
});
var clicked = false;
jQuery(document).ready(function () {
  jQuery(document).on("change", "#recurrence-booking", function () {
    var recurrence_booking = jQuery("#recurrence-booking").prop("checked");
    if (recurrence_booking == true) {
      jQuery(".recurrence_type_dropdown").show();
      jQuery(".recurrence_type_dropdown").show();
    } else {
      jQuery(".recurrence_type_dropdown").hide();
      jQuery(".recurrence_type_dropdown").hide();
    }
  });
});


jQuery(document).on("click", "#complete_bookings", function (e) {
  var site_url = siteurlObj.site_url;
  var ajax_url = ajaxurlObj.ajax_url;
  var front_url = fronturlObj.front_url;
  var stripe_pubkey = baseurlObj.stripe_publishkey;
  var stripe_status = baseurlObj.stripe_status;
  if (stripe_status == "on") { Stripe(stripe_pubkey); }
  var terms_condition_setting_value = termsconditionObj.terms_condition;
  var privacy_policy_setting_value = privacypolicyObj.privacy_policy;
  var thankyou_page_setting_value = thankyoupageObj.thankyou_page;
  var existing_username = jQuery("#ct-user-name").val();
  var existing_password = jQuery("#ct-user-pass").val();
  var password = jQuery("#ct-preffered-pass").val();
  var firstname = jQuery("#ct-first-name").val();
  var lastname = jQuery("#ct-last-name").val();
  var email = "";
  if (guest_user_status == "on") {
    email = jQuery("#ct-email-guest").val();
  } else {
    if (is_login_user == "Y") {
      email = existing_username;
    } else {
      email = jQuery("#ct-email").val();
    }
  }
  var phone = jQuery("#ct-user-phone").val();
  /***newly added start***/
  var user_address = jQuery("#ct-street-address").val();
  var user_zipcode = jQuery("#ct-zip-code").val();
  var user_city = jQuery("#ct-city").val();
  var user_state = jQuery("#ct-state").val();
  if (appoint_details.status == "on") {
    if (check_addresss.status = "on") { var address = jQuery("#app-street-address").val(); }
    else { var address = jQuery("#ct-street-address").val(); }
    if (check_zip_code.status = "on") { var zipcode = jQuery("#app-zip-code").val(); }
    else { var zipcode = jQuery("#ct-zip-code").val(); }
    if (check_city.status = "on") { var city = jQuery("#app-city").val(); }
    else { var city = jQuery("#ct-city").val(); }
    if (check_state.status = "on") { var state = jQuery("#app-state").val(); }
    else { var state = jQuery("#ct-state").val(); }
  }
  else {
    var address = jQuery("#ct-street-address").val();
    var zipcode = jQuery("#ct-zip-code").val();
    var city = jQuery("#ct-city").val();
    var state = jQuery("#ct-state").val();
  }
  var notes = jQuery("#ct-notes").val();
  var payment_method = jQuery(".payment_gateway:checked").val();
  var tip_value = jQuery(".ct_allow_user_tip").val();
  /* console.log(tip_value) */
  /** new **/
  var staff_id = jQuery(".provider_disable:checked").attr("data-staff_id");
  if (staff_id == undefined) {
    var staff_id = "";
  } else {
    var staff_id = staff_id;
  }
  var v_c_status = jQuery(".vc_status").prop("checked");
  var vc_status = "";
  if (v_c_status == undefined) {
    vc_status = "-";
  } else {
    if (v_c_status == true) { vc_status = "Y"; } else { vc_status = "N"; }
  }
  var prkng_status = jQuery(".p_status").prop("checked");
  var p_status = "";
  if (prkng_status == undefined) {
    p_status = "-";
  } else {
    if (prkng_status == true) { p_status = "Y"; } else { p_status = "N"; }
  }
  var con_status = jQuery("#contact_status").val();
  var contact_status = "";
  if (con_status == "Other") {
    contact_status = jQuery("#other_contact_status").val();
  } else if (con_status == undefined) {
    contact_status = "";
  }
  else {
    contact_status = jQuery("#contact_status").val();
  }
  var terms_condition = jQuery("#accept-conditions").prop("checked");
  var tc_check = "N";
  if (terms_condition_setting_value == "Y" || privacy_policy_setting_value == "Y") {
    if (terms_condition == true) {
      tc_check = "Y";
    }
  } else {
    tc_check = "Y";
  }
  var booking_date_text = jQuery(".cart_date").text();
  var booking_date = jQuery(".cart_date").attr("data-date_val");
  var booking_time = jQuery(".cart_time").attr("data-time_val");
  var booking_time_text = jQuery(".cart_time").text();
  var booking_date_time = booking_date + " " + booking_time;
  var currency_symbol = jQuery(this).attr("data-currency_symbol");
  var cart_sub_total = jQuery(".cart_sub_total").text();
  var amount = cart_sub_total.replace(currency_symbol, "");
  var cart_discount = jQuery(".cart_discount").text().substring(2);
  var discount = cart_discount.replace(currency_symbol, "");
  var cart_tax = jQuery(".cart_tax").text();
  var taxes = cart_tax.replace(currency_symbol, "");
  var cart_special_days = jQuery(".cart_special_days").text();
  var special_days = cart_special_days.replace(currency_symbol, "");
  var partialamount = jQuery(".partial_amount").text();
  var partial_amount = partialamount.replace(currency_symbol, "");
  var cart_total = jQuery(".cart_total").text();
  var net_amount = cart_total.replace(currency_symbol, "");

  if (minimum_booking_price == '') {
    minimum_booking_price = 0;
  }
  if (parseFloat(net_amount) < parseFloat(minimum_booking_price)) {
    jQuery(".minimum_price_show").css("display", "block");
    return false;
  } else {
    jQuery(".minimum_price_show").css("display", "none");
  }

  if (payment_method == "Wallet-payment") {
    var current_amount = jQuery('#wallet').attr('data-wallet');
  } else {
    var current_amount = "";
  }

  var cart_counting = jQuery("#total_cart_count").val();
  var coupon_code = jQuery("#coupon_val").val();
  var user_coupon_val = jQuery("#user_coupon_val").val();
  var frequently_discount_id = jQuery("input[name=frequently_discount_radio]:checked").attr("data-id");
  var frequent_discount_amount = 0;
  var recurrence_booking_1 = "N";
  if (frequently_discount_id != "1") {
    recurrence_booking_1 = "Y";
    var frequent_discount_text = jQuery(".frequent_discount").text();
    frequent_discount_amount = frequent_discount_text.replace(currency_symbol, "");
  }
  var no_units_in_cart_err = jQuery("#no_units_in_cart_err").val();
  var no_units_in_cart_err_count = jQuery("#no_units_in_cart_err_count").val();
  var cc_card_num = jQuery(".cc-number").val();
  var cc_exp_month = jQuery(".cc-exp-month").val();
  var cc_exp_year = jQuery(".cc-exp-year").val();
  var cc_card_code = jQuery(".cc-cvc").val();
  var payment_gateway_check = jQuery("input[name='payment-methods']:checked").prop("checked");
  if (ct_company_logo == '') {
    var company_logo = site_url + 'assets/images/cleanto-logo.png';
  } else {
    var company_logo = site_url + 'assets/images/services/' + ct_company_logo;
  }
  dataString = { existing_username: existing_username, existing_password: existing_password, password: password, firstname: firstname, lastname: lastname, email: email, phone: phone, user_address: user_address, user_zipcode: user_zipcode, user_city: user_city, user_state: user_state, address: address, zipcode: zipcode, city: city, state: state, notes: notes, vc_status: vc_status, p_status: p_status, contact_status: contact_status, payment_method: payment_method, tip_value: tip_value, staff_id: staff_id, amount: amount, discount: discount, taxes: taxes, partial_amount: partial_amount, net_amount: net_amount, booking_date_time: booking_date_time, frequently_discount: frequently_discount_id, frequent_discount_amount: frequent_discount_amount, coupon_code: coupon_code, user_coupon_val: user_coupon_val, cc_card_num: cc_card_num, cc_exp_month: cc_exp_month, cc_exp_year: cc_exp_year, cc_card_code: cc_card_code, guest_user_status: guest_user_status, recurrence_booking: recurrence_booking_1, current_amount: current_amount, is_login_user: is_login_user, special_days: special_days, action: "complete_booking" };
  if (jQuery("#user_details_form").valid()) {
    if (jQuery("input[name='service-radio']:checked").val() != "on" && jQuery("#ct-service-0").val() != "off" && cart_counting == 1) {
      clicked = false;
      jQuery(".ct-loading-main-complete_booking").hide();
      jQuery(".service_not_selected_error").css("display", "block");
      jQuery(".service_not_selected_error").css("color", "red");
      jQuery(".service_not_selected_error").html(errorobj_please_select_a_service);
      jQuery(this).attr("href", "#service_not_selected_error");
      /*}*/
    } else if (jQuery(".ser_name_for_error").text() == "Cleaning Service" && cart_counting == 1) {
      clicked = false;
      jQuery(".ct-loading-main-complete_booking").hide();
      jQuery(".service_not_selected_error_d2").css("color", "red");
      jQuery(".service_not_selected_error_d2").html(errorobj_please_select_a_service);
      jQuery(this).attr("href", "#service_not_selected_error_d2");
    } else if (jQuery("#ct_selected_servic_method .service-method-name").text() == "Service Usage Methods" && cart_counting == 1) {
      clicked = false;
      jQuery(".method_not_selected_error").css("display", "block");
      jQuery(".method_not_selected_error").css("color", "red");
      jQuery(".method_not_selected_error").html("Please Select Method");
      jQuery(this).attr("href", "#method_not_selected_error");
    } else if (cart_counting == 1) {
      clicked = false;
      jQuery(".ct-loading-main-complete_booking").hide();
      jQuery(".empty_cart_error").css("display", "block");
      jQuery(".empty_cart_error").css("color", "red");
      jQuery(".empty_cart_error").html(errorobj_please_select_units_or_addons);
      jQuery(this).attr("href", "#empty_cart_error");
    } else if (booking_date_text == "" && booking_time_text == "") {
      clicked = false;
      jQuery(".ct-loading-main-complete_booking").hide();
      jQuery(".date_time_error").css("display", "block");
      jQuery(".date_time_error").css("color", "red");
      jQuery(".date_time_error").html(errorobj_please_select_appointment_date);
      jQuery(this).attr("href", "#date_time_error_id");
    } else if (no_units_in_cart_err == "units_and_addons_both_exists" && no_units_in_cart_err_count == "unit_not_added") {
      clicked = false;
      jQuery(".ct-loading-main-complete_booking").hide();
      jQuery(".no_units_in_cart_error").show();
      jQuery(".no_units_in_cart_error").css("color", "red");
      jQuery(".no_units_in_cart_error").html(errorobj_please_select_atleast_one_unit);
      jQuery(this).attr("href", "#no_units_in_cart_error");
    } else if (jQuery("#check_login_click").val() == "not" && jQuery("#existing-user").prop("checked") == true) {
      clicked = false;
      jQuery(".ct-loading-main-complete_booking").hide();
      jQuery(".login_unsuccessfull").css("display", "block");
      jQuery(".login_unsuccessfull").css("color", "red");
      jQuery(".login_unsuccessfull").css("margin-left", "15px");
      jQuery(".login_unsuccessfull").html(errorobj_please_login_to_complete_booking);
      jQuery(this).attr("href", "#login_unsuccessfull");
    } else if (payment_gateway_check == 'undefined' || payment_gateway_check == undefined || payment_gateway_check == false) {
      clicked = false;
      jQuery("#payment-method-error").show();
    } else {
      jQuery("#payment-method-error").hide();
      if (tc_check == "Y") {
        if (clicked === false) {
          jQuery(this).attr("href", "javascript:void(0);");
          clicked = true;
          if (payment_method == "paypal") {
            jQuery(".ct-loading-main-complete_booking").show();
            jQuery.ajax({
              type: "POST",
              url: front_url + "checkout.php",
              data: dataString,
              success: function (response) {
                var response_detail = jQuery.parseJSON(response);
                if (response_detail.status == "success") {
                  jQuery(".ct-loading-main-complete_booking").hide();
                  window.location = response_detail.value;
                }
                if (response_detail.status == "error") {
                  clicked = false;
                  jQuery(".ct-loading-main-complete_booking").hide();
                  /* jQuery("#paypal_error").show();
                  jQuery("#paypal_error").text(response_detail.value); */
                  showPaypalpop();
                  jQuery(".ct-paypal-error").text(response_detail.value);
                }
              }
            });
          }
          if (payment_method == "Wallet-payment") {
            if (current_amount >= net_amount) {
              jQuery(".ct-loading-main-complete_booking").show();
              jQuery.ajax({
                type: "POST",
                url: front_url + "checkout.php",
                data: dataString,
                success: function (response) {

                  var response_detail = jQuery.parseJSON(response);
                  if (response_detail.status == "success") {
                    jQuery(".ct-loading-main-complete_booking").hide();
                    window.location = thankyou_page_setting_value;
                  }
                  if (response_detail.status == "error") {
                    clicked = false;
                    jQuery(".ct-loading-main-complete_booking").hide();
                    /* jQuery("#paypal_error").show();
                    jQuery("#paypal_error").text(response_detail.value); */
                    showPaypalpop();
                  jQuery(".ct-paypal-error").text(response_detail.value);
                  }
                }
              });
            } else {
              jQuery("#ct-pay-methods").html("<div id='walletamount' style='color:red'>Sorry! Not Sufficient Wallet Amount</div>");
            }
          } else if (payment_method == "card-payment") {
            jQuery(".ct-loading-main-complete_booking").show();
            jQuery.ajax({
              type: "POST",
              url: front_url + "checkout.php",
              data: dataString,
              success: function (response) {
                var response_detail = jQuery.parseJSON(response);
                if (response_detail.success == false) {
                  clicked = false;
                  jQuery(".ct-loading-main-complete_booking").hide();
                  /* jQuery("#ct-card-payment-error").show();
                  jQuery("#ct-card-payment-error").text(response_detail.error); */
                  showPopup();
                  jQuery(".ct-payment-error").text(response_detail.error);
                } else {
                  jQuery.ajax({
                    type: "POST",
                    url: front_url + "booking_complete.php",
                    data: { transaction_id: response_detail.transaction_id },
                    success: function (response) {
                      if (jQuery.trim(response) == "ok") {
                        jQuery(".ct-loading-main-complete_booking").hide();
                        window.location = thankyou_page_setting_value;
                      } else {
                        clicked = false;
                        jQuery(".ct-loading-main-complete_booking").hide();
                        /* jQuery("#ct-card-payment-error").show();
                        jQuery("#ct-card-payment-error").text(response); */
                        showPopup();
                        jQuery(".ct-payment-error").text(response);
                      }
                    }

                    /*{
                      if(jQuery.trim(response) == "ok"){
                        jQuery(".ct-loading-main-complete_booking").hide();
                        window.location=thankyou_page_setting_value; 
                      }
                    }*/
                  })
                }
              }
            });
          } else if (payment_method == "stripe-payment") {
            jQuery(".ct-loading-main-complete_booking").show();
            jQuery("#payment-form").show();
            var cardholderName = firstname + " " + lastname;
            var form_data = jQuery("#payment-form").serialize();

            stripe.createPaymentMethod('card', cardElement, {
              billing_details: { name: cardholderName }
            }).
              then(function (result) {
                if (result.error) {
                  clicked = false;
                  showPopup();
                  jQuery(".ct-payment-error").text(result.error.message);
                  /* jQuery("#ct-card-payment-error").text("");
                  jQuery("#ct-card-payment-error").show();
                  jQuery("#ct-card-payment-error").text(result.error.message); */
                  showPopup();
                  jQuery(".ct-payment-error").text(result.error.message);
                  jQuery(".ct-loading-main-complete_booking").hide();
                } else {
                  fetch(front_url + "stripe_payment_process.php",
                    {
                      method: 'POST',
                      headers: { 'Content-Type': 'application/json' },
                      body: JSON.stringify({ stripe_payment_method_id: result.paymentMethod.id, customer_detail: form_data, existing_username: existing_username, existing_password: existing_password, password: password, firstname: firstname, lastname: lastname, email: email, phone: phone, user_address: user_address, user_zipcode: user_zipcode, user_city: user_city, user_state: user_state, address: address, zipcode: zipcode, city: city, state: state, notes: notes, vc_status: vc_status, p_status: p_status, contact_status: contact_status, payment_method: payment_method, tip_value: tip_value, staff_id: staff_id, amount: amount, discount: discount, taxes: taxes, partial_amount: partial_amount, net_amount: net_amount, booking_date_time: booking_date_time, frequently_discount: frequently_discount_id, frequent_discount_amount: frequent_discount_amount, coupon_code: coupon_code, user_coupon_val: user_coupon_val, cc_card_num: cc_card_num, cc_exp_month: cc_exp_month, cc_exp_year: cc_exp_year, cc_card_code: cc_card_code, guest_user_status: guest_user_status, recurrence_booking: recurrence_booking_1, current_amount: current_amount, is_login_user: is_login_user, special_days: special_days, action: "complete_booking" })
                    }).then(function (result) {
                      result.json().then(function (result) {
                        handleServerResponse(result);
                      });
                    });
                }
              });

            function handleServerResponse(response) {
              if (response.error) {
                clicked = false;
                /* jQuery("#ct-card-payment-error").text("");
                jQuery("#ct-card-payment-error").show();
                jQuery("#ct-card-payment-error").text(response.error); */
                showPopup();
                jQuery(".ct-payment-error").text(response.error);
                jQuery(".ct-loading-main-complete_booking").hide();
              } else if (response.requires_action) {
                var action = response.next_action;
                if (action && action.type === 'redirect_to_url') {
                  window.location = action.redirect_to_url.url;
                }

                stripe.handleCardAction(
                  response.payment_intent_client_secret
                ).then(function (result) {
                  if (result.error) {
                    clicked = false;
                    /* jQuery("#ct-card-payment-error").text("");
                    jQuery("#ct-card-payment-error").show();
                    jQuery("#ct-card-payment-error").text(result.error); */
                    showPopup();
                    jQuery(".ct-payment-error").text(result.error);
                    jQuery(".ct-loading-main-complete_booking").hide();
                  } else {
                    fetch(front_url + "stripe_payment_process.php", {
                      method: 'POST',
                      headers: { 'Content-Type': 'application/json' },
                      body: JSON.stringify({ stripe_payment_intent_id: result.paymentIntent.id, existing_username: existing_username, existing_password: existing_password, password: password, firstname: firstname, lastname: lastname, email: email, phone: phone, user_address: user_address, user_zipcode: user_zipcode, user_city: user_city, user_state: user_state, address: address, zipcode: zipcode, city: city, state: state, notes: notes, vc_status: vc_status, p_status: p_status, contact_status: contact_status, payment_method: payment_method, tip_value: tip_value, staff_id: staff_id, amount: amount, discount: discount, taxes: taxes, partial_amount: partial_amount, net_amount: net_amount, booking_date_time: booking_date_time, frequently_discount: frequently_discount_id, frequent_discount_amount: frequent_discount_amount, coupon_code: coupon_code, user_coupon_val: user_coupon_val, cc_card_num: cc_card_num, cc_exp_month: cc_exp_month, cc_exp_year: cc_exp_year, cc_card_code: cc_card_code, guest_user_status: guest_user_status, recurrence_booking: recurrence_booking_1, current_amount: current_amount, is_login_user: is_login_user, special_days: special_days, action: "complete_booking" })
                    }).then(function (confirmResult) {
                      return confirmResult.json();
                    }).then(handleServerResponse);
                  }
                });
              } else {
                var transaction_id = response.transaction_id;

                jQuery.ajax({
                  type: "POST",
                  url: front_url + "booking_complete.php",
                  data: { transaction_id: transaction_id },
                  success: function (res) {
                    if (jQuery.trim(res) == "ok") {
                      jQuery(".ct-loading-main-complete_booking").hide();
                      window.location = thankyou_page_setting_value;
                    }
                  }
                });
              }
            }
          } else if (payment_method == "2checkout-payment") {
            var seller_id = twocheckout_Obj.sellerId;
            var publishable_Key = twocheckout_Obj.publishKey;
            /*  Called when token created successfully. */
            jQuery(".ct-loading-main-complete_booking").show();
            function successCallback(data) {
              /* Set the token as the value for the token input */
              var twoctoken = data.response.token.token;
              dataString["twoctoken"] = twoctoken;
              jQuery.ajax({
                type: "POST",
                url: front_url + "checkout.php",
                data: dataString,
                success: function (response) {
                  if (jQuery.trim(response) == "ok") {
                    jQuery(".ct-loading-main-complete_booking").hide();
                    window.location = thankyou_page_setting_value;
                  } else {
                    clicked = false;
                    jQuery(".ct-loading-main-complete_booking").hide();
                    /* jQuery("#ct-card-payment-error").show();
                    jQuery("#ct-card-payment-error").text(response); */
                    showPopup();
                    jQuery(".ct-payment-error").text(response);
                  }
                }
              });
            };
            /*  Called when token creation fails. */
            function errorCallback(data) {
              if (data.errorCode === 200) {
                clicked = false;
                jQuery(".ct-loading-main-complete_booking").hide();
                tokenRequest();
              } else {
                clicked = false;
                jQuery(".ct-loading-main-complete_booking").hide();
                /* jQuery("#ct-card-payment-error").show();
                jQuery("#ct-card-payment-error").text(response.error.message); */
                showPopup();
                jQuery(".ct-payment-error").text(response.error.message);
              }
            };
            function tokenRequest() {
              /* Setup token request arguments */
              var args = {
                sellerId: seller_id,
                publishableKey: publishable_Key,
                ccNo: jQuery(".ct-card-number").val(),
                cvv: jQuery(".ct-cvc-code").val(),
                expMonth: jQuery(".ct-exp-month").val(),
                expYear: jQuery(".ct-exp-year").val()
              };
              /* Make the token request */
              TCO.requestToken(successCallback, errorCallback, args);
            };
            tokenRequest();
          } else if (payment_method == "pay at venue" || payment_method == "bank transfer" || payment_method == "") {
            jQuery(".ct-loading-main-complete_booking").show();
            jQuery.ajax({
              type: "POST",
              url: front_url + "checkout.php",
              data: dataString,
              success: function (response) {
                if (jQuery.trim(response) == "ok") {
                  jQuery(".ct-loading-main-complete_booking").hide();
                  window.location = thankyou_page_setting_value;
                }
              }
            })
          }
          payment_process_js(payment_method, thankyou_page_setting_value, dataString, front_url);
        } else {
          e.preventDefault();
        }
      } else {
        if (terms_condition_setting_value == "Y" || privacy_policy_setting_value == "Y") {
          jQuery(this).attr("href", "javascript:void(0);");
          clicked = false;
          jQuery(".ct-loading-main-complete_booking").hide();
          jQuery(".terms_and_condition").show();
          jQuery(".terms_and_condition").css("color", "red");
          jQuery(".terms_and_condition").html(errorobj_please_accept_terms_and_conditions);
        }
      }
    }
  }
  jQuery(".add_show_error_class").each(function () {
    jQuery(this).trigger("keyup");
  });
});



jQuery(document).on("click", "#accept-conditions", function () {
  jQuery(".terms_and_condition").hide();
});
jQuery(document).on("click", "#login_existing_user", function () {
  jQuery(".add_show_error_class_for_login").each(function () {
    jQuery(this).trigger("keyup");
  });
  jQuery(".add_show_error_class").each(function () {
    var id = jQuery(this).attr("id");
    jQuery(this).removeClass("error");
    jQuery("#" + id).parent().removeClass("error");
    jQuery(this).removeClass("show-error");
    jQuery("#" + id).parent().removeClass("show-error");
    jQuery(".intl-tel-input").parent().removeClass("show-error");
  });
  var site_url = siteurlObj.site_url;
  var ajax_url = ajaxurlObj.ajax_url;
  var existing_username = jQuery("#ct-user-name").val();
  var existing_password = jQuery("#ct-user-pass").val();
  dataString = { existing_username: existing_username, existing_password: existing_password, action: "get_existing_user_data" };
  if (!jQuery("#user_login_form").valid()) { return false; }
  jQuery.ajax({
    type: "POST",
    url: ajax_url + "front_ajax.php",
    data: dataString,
    success: function (response) {
      var userdata = jQuery.parseJSON(response);
      if (userdata.status == "Incorrect Email Address or Password") {
        jQuery(".login_unsuccessfull").css("display", "block");
        jQuery(".login_unsuccessfull").css("color", "red");
        jQuery(".login_unsuccessfull").css("margin-left", "15px");
        jQuery("#check_login_click").val("not");
        jQuery(".login_unsuccessfull").html(errorobj_incorrect_email_address_or_password);
        is_login_user = "N";
        jQuery(".fancy_input").each(function () { jQuery(this).trigger("keyup"); });
      } else {
        is_login_user = "Y";

        jQuery("#check_login_click").val("clicked");
        jQuery("#pay-cash").removeAttr('checked');
        jQuery(".client_logout").css("display", "block");
        jQuery(".client_logout").show();
        jQuery("#ct-email").val(existing_username);
        jQuery(".fname").text(userdata.firstname);
        jQuery(".lname").text(userdata.lastname);
        jQuery(".hide_login_btn").hide();
        jQuery(".hide_radio_btn_after_login").hide();
        jQuery(".hide_email").hide();
        jQuery(".hide_login_email").hide();
        jQuery(".hide_password").hide();
        jQuery(".ct-peronal-details").show();
        jQuery(".login_unsuccessfull").hide();
        jQuery(".ct-sub").hide();

        jQuery("#ct-first-name").val(userdata.firstname);
        jQuery("#ct-last-name").val(userdata.lastname);
        jQuery("#ct-user-phone").intlTelInput("setNumber", userdata.phone);
        if (check_addresss.statuss == "on") { jQuery("#ct-street-address").val(userdata.address); }
        if (check_zip_code.statuss == "on") { jQuery("#ct-zip-code").val(userdata.zip); }
        if (check_city.statuss == "on") { jQuery("#ct-city").val(userdata.city); }
        if (check_state.statuss == "on") { jQuery("#ct-state").val(userdata.state); }
        jQuery("#ct-notes").val(userdata.notes);
        if (userdata.vc_status == "N") {
          jQuery("#vaccum-no").attr("checked", true);
        } else {
          jQuery("#vaccum-yes").attr("checked", true);
        }
        if (userdata.p_status == "N") {
          jQuery("#parking-no").attr("checked", true);
        } else {
          jQuery("#parking-yes").attr("checked", true);
        }
        var con_staatus = userdata.contact_status;
        if (con_staatus == "I'll be at home" || con_staatus == "Please call me" || con_staatus == "The key is with the doorman") {
          jQuery("#contact_status").val(userdata.contact_status);
        } else {
          jQuery("#contact_status").val("Other");
          jQuery(".ct-option-others").show();
          jQuery("#other_contact_status").val(userdata.contact_status);
        }
        jQuery(".fancy_input").each(function () { jQuery(this).trigger("keyup"); });

        jQuery(".wallet_amount_display").css("display", "block");
        jQuery(".wallet_amount_display").html(userdata.wallet_amount);
        /* jQuery(".user_wallet_amount_value").attr('data-wallet', userdata.wallet_amount); */
      }
    }
  });
});
jQuery(document).on("click", "#ct-user-name", function () {
  jQuery(".login_unsuccessfull").hide();
});
jQuery(document).on("click", "#ct-user-pass", function () {
  jQuery(".login_unsuccessfull").hide();
});
jQuery(document).on("change", ".existing-user", function () {
  if (jQuery(".existing-user").is(":checked")) {
    jQuery(".login_unsuccessfull").html("");
    jQuery("#ct-email-guest").val("");
    jQuery("#ct-user-name").val("");
    jQuery("#ct-user-pass").val("");
    jQuery("#ct-preffered-name").val("");
    jQuery("#ct-preffered-pass").val("");
    jQuery("#ct-first-name").val("");
    jQuery("#ct-last-name").val("");
    jQuery("#ct-email").val("");
    jQuery("#ct-user-phone").val("");
    jQuery("#ct-street-address").val("");
    jQuery("#ct-zip-code").val("");
    jQuery("#ct-city").val("");
    jQuery("#ct-state").val("");
    jQuery("#ct-notes").val("");
    jQuery(".spaical_referral_class").css("display", "none");
    jQuery(".ct-login-existing").show("blind", { direction: "vertical" }, 700);
    jQuery(".ct-new-user-details").hide("blind", { direction: "vertical" }, 300);
    jQuery(".ct-peronal-details").hide("blind", { direction: "vertical" }, 300);
    guest_user_status = "off";
  }
});
jQuery(document).on("change", ".new-user", function () {
  if (jQuery(".new-user").is(":checked")) {
    jQuery(".login_unsuccessfull").html("");
    jQuery("#ct-email-guest").val("");
    jQuery("#ct-user-name").val("");
    jQuery("#ct-user-pass").val("");
    jQuery("#ct-preffered-name").val("");
    jQuery("#ct-preffered-pass").val("");
    jQuery("#ct-first-name").val("");
    jQuery("#ct-last-name").val("");
    jQuery("#ct-email").val("");
    jQuery("#ct-user-phone").val("");
    jQuery("#ct-street-address").val("");
    jQuery("#ct-zip-code").val("");
    jQuery("#ct-city").val("");
    jQuery("#ct-state").val("");
    jQuery("#ct-notes").val("");
    jQuery(".spaical_referral_class").css("display", "block");
    jQuery(".ct-new-user-details").show("blind", { direction: "vertical" }, 700);
    jQuery(".ct-login-existing").hide("blind", { direction: "vertical" }, 300);
    jQuery(".ct-peronal-details").show("blind", { direction: "vertical" }, 300);
    jQuery(".remove_preferred_password_and_preferred_email").show("blind", { direction: "vertical" }, 300);
    jQuery(".remove_guest_user_preferred_email").hide("blind", { direction: "vertical" }, 300);
    if (jQuery(".remove_zip_code_class").hasClass("ct-md-6")) {
      jQuery(".remove_zip_code_class").removeClass("ct-md-6");
      jQuery(".remove_zip_code_class").addClass("ct-md-4");
    }
    if (jQuery(".remove_city_class").hasClass("ct-md-6")) {
      jQuery(".remove_city_class").removeClass("ct-md-6");
      jQuery(".remove_city_class").addClass("ct-md-4");
    }
    if (jQuery(".remove_state_class").hasClass("ct-md-6")) {
      jQuery(".remove_state_class").removeClass("ct-md-6");
      jQuery(".remove_state_class").addClass("ct-md-4");
    }
    guest_user_status = "off";
  }
});

jQuery(document).on("change", ".guest-user", function () {
  if (jQuery(".guest-user").is(":checked")) {
    jQuery("#ct-email-guest").val("");
    jQuery("#ct-user-name").val("");
    jQuery("#ct-user-pass").val("");
    jQuery("#ct-preffered-name").val("");
    jQuery("#ct-preffered-pass").val("");
    jQuery("#ct-first-name").val("");
    jQuery("#ct-last-name").val("");
    jQuery("#ct-email").val("");
    jQuery("#ct-user-phone").val("");
    jQuery("#ct-street-address").val("");
    jQuery("#ct-zip-code").val("");
    jQuery("#ct-city").val("");
    jQuery("#ct-state").val("");
    jQuery("#ct-notes").val("");
    jQuery(".ct-new-user-details").show("blind", { direction: "vertical" }, 700);
    jQuery(".ct-login-existing").hide("blind", { direction: "vertical" }, 300);
    jQuery(".ct-peronal-details").show("blind", { direction: "vertical" }, 300);
    jQuery(".remove_preferred_password_and_preferred_email").hide("blind", { direction: "vertical" }, 300);
    jQuery(".remove_guest_user_preferred_email").show("blind", { direction: "vertical" }, 300);
    if (jQuery(".remove_zip_code_class").hasClass("ct-md-4")) {
      jQuery(".remove_zip_code_class").removeClass("ct-md-4");
      jQuery(".remove_zip_code_class").addClass("ct-md-6");
    }
    if (jQuery(".remove_city_class").hasClass("ct-md-4")) {
      jQuery(".remove_city_class").removeClass("ct-md-4");
      jQuery(".remove_city_class").addClass("ct-md-6");
    }
    if (jQuery(".remove_state_class").hasClass("ct-md-4")) {
      jQuery(".remove_state_class").removeClass("ct-md-4");
      jQuery(".remove_state_class").addClass("ct-md-6");
    }
    guest_user_status = "on";
  }
  jQuery(".fancy_input").each(function () { jQuery(this).trigger("keyup"); });
});
jQuery(document).on("click", "#logout", function () {
  var site_url = siteurlObj.site_url;
  var ajax_url = ajaxurlObj.ajax_url;
  var id = jQuery(this).attr("data-id");
  jQuery(".wallet_amount_display").css("display", "none");
  dataString = { id: id, action: "logout" };
  jQuery.ajax({
    type: "POST",
    url: ajax_url + "front_ajax.php",
    data: dataString,
    success: function (response) {
      if (jQuery.trim(response) == "logout successful") {
        jQuery("#check_login_click").val("not");
        jQuery(".client_logout").hide();
        jQuery(".client_logout").css("display", "none");
        jQuery("#other_contact_status").hide();
        jQuery(".hide_login_btn").show();
        jQuery(".ct-peronal-details").hide();
        jQuery(".hide_radio_btn_after_login").show();
        jQuery(".hide_email").show();
        jQuery(".hide_login_email").show();
        jQuery(".hide_password").show();
        jQuery(".ct-sub").show();
        jQuery("#ct-user-name").val("");
        jQuery("#ct-user-pass").val("");
        jQuery("#ct-preffered-name").val("");
        jQuery("#ct-preffered-pass").val("");
        jQuery("#ct-first-name").val("");
        jQuery("#ct-last-name").val("");
        jQuery("#ct-email").val("");
        jQuery("#ct-user-phone").val("");
        jQuery("#ct-street-address").val("");
        jQuery("#ct-zip-code").val("");
        jQuery("#ct-city").val("");
        jQuery("#ct-state").val("");
        jQuery("#ct-notes").val("");
        jQuery("#vaccum-yes").prop("checked", true);
        jQuery("#parking-yes").prop("checked", true);
        jQuery("#contact_status").val("I'll be at home");
        jQuery("#other_contact_status").val("");
        jQuery("#existing-user").prop("checked", true);
        jQuery(".existing-user").trigger("change");
        is_login_user = "N";
      }
      jQuery(".fancy_input").each(function () { jQuery(this).trigger("keyup"); });
    }
  })
});
/* dropdown services methods list */
/* services methods dropdown show hide list */
jQuery(document).on("click", ".service-method-is", function () {
  jQuery(".ct-services-method-dropdown").toggle("blind", { direction: "vertical" }, 300);
});
jQuery(document).on("click", ".select_service_method", function () {
  jQuery("#ct_selected_servic_method").html(jQuery(this).html());
  jQuery(".ct-services-method-dropdown").hide("blind", { direction: "vertical" }, 300);
  jQuery("#ct_selected_servic_method h3").removeClass("s_m_units_design");
});
jQuery(document).on('click', '.ser_details', function (e) {
  jQuery(".custom_service_error").hide();
  jQuery(".ct-extra-services-list").empty();
  if (ser_ids_dynamic != 0) {
    var service_ul = '.cart_item_listing' + ser_ids_dynamic;



    if ((jQuery("ul" + service_ul).has("li").length === 0)) {

      jQuery(".cart_service_item_listing_li" + ser_ids_dynamic).remove();
    }
  }
  jQuery(":input", this).prop('checked', true);
  jQuery('.ct-loading-main').show();
  jQuery('.show_methods_after_service_selection').show();
  jQuery('.ct_method_tab-slider-tabs').removeClass('ct_methods_slide');
  jQuery('.service_not_selected_error_d2').hide();
  /* jQuery('.service_not_selected_error_d2').removeAttr('style', '');
  jQuery('.service_not_selected_error_d2').html(errorobj_please_select_a_service); */
  /* jQuery('.freq_discount_display').hide(); */
  /* jQuery('.add_addon_in_cart_for_multipleqty').data('status','2'); */
  jQuery('.service_not_selected_error').hide();
  jQuery('.partial_amount_hide_on_load').hide();
  jQuery('.hide_right_side_box').hide();
  jQuery('.freq_disc_empty_cart_error').hide();
  jQuery('.s_m_units_design_1').hide();
  jQuery('.s_m_units_design_2').hide();
  jQuery('.s_m_units_design_3').hide();
  jQuery('.s_m_units_design_4').hide();
  jQuery('.s_m_units_design_5').hide();
  jQuery('.hideservice_name').show();
  /* jQuery('.show_select_staff_title').show(); */
  /* jQuery('.ser_mthd_units').html('Service Usage Methods'); */
  /* jQuery('.empty_cart_error').hide(); */
  /* jQuery('.no_units_in_cart_error').hide(); */
  /* jQuery( ".cart_item_listing" ).empty();
  jQuery( ".frequent_discount" ).empty();
  jQuery( ".cart_sub_total" ).empty(); */
   jQuery( ".cart_empty_msg" ).hide(); 
  /* jQuery( ".cart_tax" ).empty();
  jQuery( ".cart_total" ).empty();
  jQuery( ".remain_amount" ).empty();
  jQuery( ".partial_amount" ).empty();
  jQuery( ".cart_discount" ).empty(); */
  var site_url = siteurlObj.site_url;
  var ajax_url = ajaxurlObj.ajax_url;

  var id = jQuery(this).data('id');

  var name = jQuery(this).data('servicetitle');
  //jQuery('.sel-service').html(name);
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
    /* alert(ser_ids_dynamic); */
    var lidtls = "<li class=" + ser_ids_lis + " data-id='" + ser_ids_dynamic + "'><h4 class='cart_toggle_head' data-id='" + ser_ids_dynamic + "' >" + name + "<i class='fa fa-plus pull-right " + ser_ids_iis + "'></i></h4><div id='" + ser_ids_divs + "' style='overflow:hidden;' class='cart_toggle_content'><ul class='ct-addon-items-list " + ser_ids_uls + "'></ul></div></li>";

    jQuery(".cart_service_item_listing").append(lidtls);


    jQuery("#" + ser_ids_divs).slideUp();
  }
  jQuery('.addon_qty').each(function () {
    jQuery(this).val(0);
    jQuery('.add_minus_button').hide();
  });

  if (jQuery('.ser_name_for_error').text() != 'Cleaning Service' && jQuery('.service-method-name').text() == 'Service Usage Methods') {
    jQuery('.method_not_selected_error').css('display', 'block');
    jQuery('.method_not_selected_error').css('color', 'red');
    jQuery('.method_not_selected_error').html("Please Select Method");
    /*}else if(jQuery('.make_service_disable').prop("checked") == true && jQuery('.service-method-name').text() == 'Service Usage Methods'){*/
  } else if (jQuery("input[name='service-radio']:checked").val() == 'on' && jQuery('.service-method-name').text() == 'Service Usage Methods') {
    jQuery('.method_not_selected_error').css('display', 'block');
    jQuery('.method_not_selected_error').css('color', 'red');
    jQuery('.method_not_selected_error').html("Please Select Method");
  }


  /* display all methods of the selected services */
  jQuery.ajax({
    type: 'post',
    data: {
      'service_id': id,
      'operationgetmethods': 1
    },
    url: ajax_url + "front_ajax.php",
    success: function (res) {
      jQuery('.ct-loading-main').hide();
      var methods_data = jQuery.parseJSON(res);
      jQuery('.service_select').trigger('click');

      if (methods_data.status == 'single') {
        jQuery('.services-method-list-dropdown').hide();
        jQuery('.show_single_service_method').html(methods_data.m_html);
        jQuery('.s_m_units_design').trigger('click');
        jQuery('#method_not_selected_error').hide();
        var ct_postal_input = jQuery(".ct-postal-input").val();
        // alert(ct_postal_input);
        jQuery.ajax({
          type: 'post',
          data: {
            'service_id': id,
            ct_postal_input:ct_postal_input,
            'staff_select_according_service': 1
          },
          url: ajax_url + "front_ajax.php",
          success: function (res) {


            var search_session_data = jQuery.parseJSON(res);


            if (search_session_data.found_status == 'found') {
              jQuery('.ct-provider-list').show();
              /* jQuery('.staff_search_id').html(search_session_data.staff_id); */
              /* jQuery('.staff_search_id').attr("data-id",search_session_data.staff_id); */
              var search_staff_id = search_session_data.staff_id;

              jQuery.ajax({
                type: "POST",
                url: ajax_url + "front_ajax.php",
                data: {
                  'staff_search': search_staff_id,
                  'get_search_staff_detail': 1
                },
                success: function (res) {
                  if(res == ""){
                    jQuery('.show_select_staff_title').hide();
                  }else{
                    jQuery('.show_select_staff_title').show();
                    jQuery('.provders-list').html(res);
                  }
                }
              });

            } else if (search_session_data.found_status == 'not found') {
              jQuery('.ct-provider-list').hide();

            }
          }
        });

      } else {
        jQuery('.show_single_service_method').html(methods_data.m_html);
        $('.ct_method_tab-slider-tabs li:first').trigger('click');
        var ct_postal_input = jQuery(".ct-postal-input").val();
        // alert(ct_postal_input);
        jQuery.ajax({
          type: 'post',
          data: {
            'service_id': id,
            ct_postal_input:ct_postal_input,
            'staff_select_according_service': 1
          },
          url: ajax_url + "front_ajax.php",
          success: function (res) {



            var search_session_data = jQuery.parseJSON(res);


            if (search_session_data.found_status == 'found') {
              jQuery('.ct-provider-list').show();
              /* jQuery('.staff_search_id').html(search_session_data.staff_id); */
              /* jQuery('.staff_search_id').attr("data-id",search_session_data.staff_id); */
              var search_staff_id = search_session_data.staff_id;

              jQuery.ajax({
                type: "POST",
                url: ajax_url + "front_ajax.php",
                data: {
                  'staff_search': search_staff_id,
                  'get_search_staff_detail': 1
                },
                success: function (res) {
                  if(res == ""){
                    jQuery('.show_select_staff_title').hide();
                  }else{
                    jQuery('.show_select_staff_title').show();
                    jQuery('.provders-list').html(res);
                  }
                }
              });

            } else if (search_session_data.found_status == 'not found') {
              jQuery('.ct-provider-list').hide();

            }

          }
        });
      }
    }
  });
  jQuery.ajax({
    type: "POST",
    url: ajax_url + "calendar_ajax.php",
    data: { "get_calendar_on_page_load": 1 },
    success: function (res) {
      jQuery(".cal_info").html(res);
      var d = new Date();
      var month = d.getMonth() + 1;
      var day = d.getDate();
      var year = d.getFullYear();
      var output = day + "-" + (month < 10 ? "0" : "") + month + "-" + year;
      var selected_dates = jQuery(".selected_date").data("selected_dates");
      var cur_dates = jQuery(".selected_date").data("cur_dates");
      if (output == cur_dates) {
        jQuery(".by_default_today_selected").addClass("active_today");
      }
    }
  });

  jQuery(document).on('click', '.provider_select', function () {
    var site_url = siteurlObj.site_url;
    var staff_id = jQuery(this).data('id');

    jQuery.ajax({
      type: 'post',
      data: {
        'staff_id': staff_id,
        'get_staff_sess': 1
      },
      url: site_url + "front/firststep.php",
      success: function (res) {

      }
    });


  });
  /* display all add-on of the selected services */
  jQuery.ajax({
    type: 'post',
    data: {
      'service_id': id,
      'get_service_addons': 1
    },
    url: ajax_url + "front_ajax.php",
    success: function (res) {
      jQuery('.ct-loading-main').hide();
      if (res == 'Extra Services Not Available') {
        jQuery('.hide_allsss_addons').hide();
      } else {
        jQuery('.hide_allsss_addons').show();
        jQuery('.add_on_lists').html(res);
        jQuery('.add_minus_button').hide();
        /* jQuery('.add_addon_in_cart_for_multipleqty').each(function(){
            var multiqty_addon_id = jQuery(this).data('id');
            var value = jQuery(this).prop('checked');
            if(value == true){
                jQuery('#ct-addon-'+multiqty_addon_id).attr('checked', false);
            }
        }); */
      }
    }
  });
  e.stopImmediatePropagation();
  return false;
});
/* display all Provider of selected service */
jQuery(document).on("click", ".provider_select", function () {
  jQuery(".ct-loading-main").show();
  var site_url = siteurlObj.site_url;
  var ajax_url = ajaxurlObj.ajax_url;
  var staff_id = jQuery(this).attr("data-id");
  jQuery.ajax({
    type: "post",
    data: { "staff_id": staff_id, "get_staff_sess": 1 },
    url: site_url + "front/firststep.php",
    success: function (res) {
      jQuery.ajax({
        type: "POST",
        url: ajax_url + "calendar_ajax.php",
        data: { "get_calendar_on_page_load": 1 },
        success: function (res) {
          jQuery(".cal_info").html(res);
          var d = new Date();
          var month = d.getMonth() + 1;
          var day = d.getDate();
          var year = d.getFullYear();
          var output = day + "-" + (month < 10 ? "0" : "") + month + "-" + year;
          var selected_dates = jQuery(".selected_date").data("selected_dates");
          var cur_dates = jQuery(".selected_date").attr("data-cur_dates");
          jQuery(".hidedatetime_value").hide();
          jQuery(".cart_date").attr("data-date_val", "");
          jQuery(".cart_date").html("");
          jQuery(".space_between_date_time").hide();
          jQuery(".cart_time").attr("data-time_val", "");
          jQuery(".cart_time").html("");
          jQuery(".ct-loading-main").hide();
          if (output == cur_dates) {
            jQuery(".by_default_today_selected").addClass("active_today");
          }
        }
      });
    }
  });
  jQuery("#ct-provider-" + staff_id).prop("checked", true);
  return false;
});
jQuery(document).on("click", ".addons_servicess_2", function () {
  var id = jQuery(this).attr("data-id");
  jQuery(".add_minus_buttonid" + id).show();
  var m_name = jQuery(this).attr("data-mnamee");
  var value = jQuery(this).prop("checked");
  if (value == false) {
    jQuery(".qtyyy_" + m_name).val("1");
    var addon_id = jQuery(this).attr("data-id");
    jQuery("#minus" + addon_id).trigger("click");
  } else if (value == true) {
    var addon_id = jQuery(this).attr("data-id");
    jQuery("#add" + addon_id).trigger("click");
  }
});
/* bedroom and bathroom counting for addons */
jQuery(document).on("click", ".add", function () {
  jQuery(".freq_disc_empty_cart_error").hide();
  var site_url = siteurlObj.site_url;
  var ajax_url = ajaxurlObj.ajax_url;
  var ids = jQuery(this).attr("data-ids");
  var db_qty = jQuery(this).attr("data-db-qty");
  var service_id = jQuery(this).attr("data-service_id");
  var method_id = jQuery(this).attr("data-method_id");
  var method_name = jQuery(this).attr("data-method_name");
  var units_id = jQuery(this).attr("data-units_id");
  var type = jQuery(this).attr("data-type");
  var m_name = jQuery(this).attr("data-mnamee");
  jQuery(".coupon_display").hide();
  jQuery(".hide_coupon_textbox").show();
  jQuery(".ct-display-coupon-code").hide();

  /* */

  jQuery(".user_coupon_display").hide();
  jQuery(".hide_user_coupon_textbox").show();
  jQuery(".ct-display-user-coupon-code").hide();

  /* */

  /* */

  jQuery(".hide_referral_textbox").show();
  jQuery(".ct-display-referral-code").hide();

  /* */

  jQuery(".coupon_invalid_error").hide();
  var frequently_discount_id = jQuery("input[name=frequently_discount_radio]:checked").attr("data-id");
  var qty_val = parseInt(jQuery(".qtyyy_" + m_name).val());
  var qty_vals = qty_val + 1;
  if (qty_val < db_qty) {
    jQuery(".qtyyy_" + m_name).val(qty_vals);
    var final_qty_val = qty_vals;
    jQuery.ajax({
      type: "post",
      data: { "addon_id": ids, "qty_vals": final_qty_val, "s_addon_units_maxlimit_4_ratesss": 1 },
      url: ajax_url + "front_ajax.php",
      success: function (res) {
        jQuery(".data_addon_qtyrate").attr("data-rate", res);
        jQuery.ajax({
          type: "post",
          data: { "method_id": method_id, "service_id": service_id, "s_m_qty": final_qty_val, "s_m_rate": res, "method_name": method_name, "units_id": units_id, "frequently_discount_id": frequently_discount_id, "type": type, "add_to_cart": 1 },
          url: site_url + "front/firststep.php",
          success: function (res) {
            // cart_service_item_listing
            jQuery(".freq_discount_display").show();
            jQuery(".hide_right_side_box").show();
            jQuery(".partial_amount_hide_on_load").show();
            jQuery(".empty_cart_error").hide();
            var cart_session_data = jQuery.parseJSON(res);
            jQuery("#no_units_in_cart_err").val(cart_session_data.unit_status);
            jQuery("#no_units_in_cart_err_count").val(cart_session_data.unit_require);
            jQuery("#total_cart_count").val("2");
            jQuery(".coupon_invalid_error").hide();
            if (cart_session_data.status == "update") {
              jQuery(".cart_empty_msg").hide();
              
              jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).html(cart_session_data.s_m_html);
              jQuery("." + cart_session_data.method_name_without_space).val("data-service_id", method_id);

              jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).val("data-service_id", service_id);
              jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).val("data-method_id", method_id);
              jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).val("data-units_id", units_id);
              jQuery(".partial_amount").html(cart_session_data.partial_amount);
              jQuery(".remain_amount").html(cart_session_data.remain_amount);
              jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
              jQuery(".cart_discount").html("- " + cart_session_data.cart_discount);
              jQuery(".cart_tax").html(cart_session_data.cart_tax);
              jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
              jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
              jQuery(".cart_total").html(cart_session_data.total_amount);
              jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
            } else if (cart_session_data.status == "insert") {
              jQuery(".hideduration_value").show();
              jQuery(".cart_empty_msg").hide();
              jQuery('.cart_item_listing' + service_id).append(cart_session_data.s_m_html);
              
              jQuery(".partial_amount").html(cart_session_data.partial_amount);
              jQuery(".remain_amount").html(cart_session_data.remain_amount);
              jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
              jQuery(".cart_discount").html("- " + cart_session_data.cart_discount);
              jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
              jQuery(".cart_tax").html(cart_session_data.cart_tax);
              jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
              jQuery(".cart_total").html(cart_session_data.total_amount);
              jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
            } else if (cart_session_data.status == "firstinsert") {
              jQuery(".hideduration_value").show();
              jQuery(".cart_empty_msg").hide();
              jQuery('.cart_item_listing' + service_id).append(cart_session_data.s_m_html);
              jQuery(".partial_amount").html(cart_session_data.partial_amount);
              jQuery(".remain_amount").html(cart_session_data.remain_amount);
              jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
              jQuery(".cart_discount").html("- " + cart_session_data.cart_discount);
              jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
              jQuery(".cart_tax").html(cart_session_data.cart_tax);
              jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
              jQuery(".cart_total").html(cart_session_data.total_amount);
              jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
            } else if (cart_session_data.status == "empty calculation") {
              jQuery(".hideduration_value").hide();
              jQuery(".total_time_duration_text").html("");
              jQuery(".freq_discount_display").hide();
              jQuery(".partial_amount_hide_on_load").hide();
              jQuery(".hide_right_side_box").hide();
              jQuery(".cart_empty_msg").show();
              jQuery(".cart_item_listing" + service_id).empty();
              jQuery(".cart_sub_total").empty();
              jQuery(".cart_tax").empty();
              jQuery(".cart_special_days").empty();
              jQuery(".cart_total").empty();
              jQuery(".frequent_discount").empty();
              jQuery(".remain_amount").empty();
              jQuery(".partial_amount").empty();
              jQuery(".cart_discount").empty();
            } else if (cart_session_data.status == "delete particuler") {
              jQuery(".cart_empty_msg").hide();
              jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).remove();
              jQuery(".partial_amount").html(cart_session_data.partial_amount);
              jQuery(".remain_amount").html(cart_session_data.remain_amount);
              jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
              jQuery(".cart_discount").html("- " + cart_session_data.cart_discount);
              jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
              jQuery(".cart_tax").html(cart_session_data.cart_tax);
              jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
              jQuery(".cart_total").html(cart_session_data.total_amount);
              jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
            }
          }
        });
      }
    });
  } else {
    jQuery(".ct-loading-main").hide();
    jQuery(".qtyyy_" + m_name).val(db_qty);
  }
});
jQuery(document).on("click", ".minus", function () {
  jQuery(".freq_disc_empty_cart_error").hide();
  var site_url = siteurlObj.site_url;
  var ajax_url = ajaxurlObj.ajax_url;
  var ids = jQuery(this).attr("data-ids");
  var service_id = jQuery(this).attr("data-service_id");
  var method_id = jQuery(this).attr("data-method_id");
  var method_name = jQuery(this).attr("data-method_name");
  var m_name = jQuery(this).attr("data-mnamee");
  var units_id = jQuery(this).attr("data-units_id");
  jQuery(".coupon_display").hide();
  jQuery(".hide_coupon_textbox").show();
  jQuery(".ct-display-coupon-code").hide();

  /* */
  jQuery(".user_coupon_display").hide();
  jQuery(".hide_user_coupon_textbox").show();
  jQuery(".ct-display-user-coupon-code").hide();
  /* */

  /* */

  jQuery(".hide_referral_textbox").show();
  jQuery(".ct-display-referral-code").hide();

  /* */

  jQuery(".coupon_invalid_error").hide();
  var type = jQuery(this).attr("data-type");
  var frequently_discount_id = jQuery("input[name=frequently_discount_radio]:checked").attr("data-id");
  var qty_val = parseInt(jQuery(".qtyyy_" + m_name).val());
  var qty_vals = qty_val - 1;
  var currentVal = parseInt(jQuery(".qtyyy_" + m_name).val());
  if (currentVal <= 0) {
    jQuery(".add_minus_buttonid" + units_id).hide();
    jQuery(".qtyyy_" + m_name).val("0");
    jQuery(".update_qty_of_s_m_" + m_name).remove();
    jQuery("#ct-addon-" + units_id).attr("checked", false);
  } else if (currentVal > 0) {
    jQuery(".qtyyy_" + m_name).val(qty_vals);
    jQuery.ajax({
      type: "post",
      data: { "addon_id": ids, "qty_vals": qty_vals, "s_addon_units_maxlimit_4_ratesss": 1 },
      url: ajax_url + "front_ajax.php",
      success: function (res) {
        jQuery(".data_addon_qtyrate").attr("data-rate", res);
        jQuery.ajax({
          type: "post",
          data: { "method_id": method_id, "service_id": service_id, "s_m_qty": qty_vals, "s_m_rate": res, "method_name": method_name, "units_id": units_id, "type": type, "frequently_discount_id": frequently_discount_id, "add_to_cart": 1 },
          url: site_url + "front/firststep.php",
          success: function (res) {
            jQuery(".freq_discount_display").show();
            jQuery(".hide_right_side_box").show();
            jQuery(".partial_amount_hide_on_load").show();
            jQuery(".empty_cart_error").hide();
            jQuery("#total_cart_count").val("2");
            jQuery(".coupon_invalid_error").hide();
            var cart_session_data = jQuery.parseJSON(res);
            jQuery("#no_units_in_cart_err").val(cart_session_data.unit_status);
            jQuery("#no_units_in_cart_err_count").val(cart_session_data.unit_require);
            if (cart_session_data.status == "update") {
              jQuery(".cart_empty_msg").hide();
              jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).html(cart_session_data.s_m_html);
              jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).val("data-service_id", service_id);
              jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).val("data-method_id", method_id);
              jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).val("data-units_id", units_id);
              jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
              jQuery(".partial_amount").html(cart_session_data.partial_amount);
              jQuery(".remain_amount").html(cart_session_data.remain_amount);
              jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
              jQuery(".cart_discount").html("- " + cart_session_data.cart_discount);
              jQuery(".cart_tax").html(cart_session_data.cart_tax);
              jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
              jQuery(".cart_total").html(cart_session_data.total_amount);
              jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
            } else if (cart_session_data.status == "insert") {
              jQuery(".hideduration_value").show();
              jQuery(".cart_empty_msg").hide();
              jQuery('.cart_item_listing' + service_id).append(cart_session_data.s_m_html);
              jQuery(".partial_amount").html(cart_session_data.partial_amount);
              jQuery(".remain_amount").html(cart_session_data.remain_amount);
              jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
              jQuery(".cart_discount").html("- " + cart_session_data.cart_discount);
              jQuery(".cart_tax").html(cart_session_data.cart_tax);
              jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
              jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
              jQuery(".cart_total").html(cart_session_data.total_amount);
              jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
            } else if (cart_session_data.status == "firstinsert") {
              jQuery(".hideduration_value").show();
              jQuery(".cart_empty_msg").hide();
              jQuery('.cart_item_listing' + service_id).append(cart_session_data.s_m_html);
              jQuery(".partial_amount").html(cart_session_data.partial_amount);
              jQuery(".remain_amount").html(cart_session_data.remain_amount);
              jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
              jQuery(".cart_discount").html("- " + cart_session_data.cart_discount);
              jQuery(".cart_tax").html(cart_session_data.cart_tax);
              jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
              jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
              jQuery(".cart_total").html(cart_session_data.total_amount);
              jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
            } else if (cart_session_data.status == "empty calculation") {
              /* jQuery(".hideduration_value").hide(); */
              jQuery(".total_time_duration_text").html("");
              jQuery(".freq_discount_display").hide();
              jQuery(".partial_amount_hide_on_load").hide();
              jQuery(".hide_right_side_box").hide();
              jQuery(".add_minus_button").hide();
              jQuery("#ct-addon-" + units_id).attr("checked", false);
              jQuery(".cart_empty_msg").hide();
              jQuery(".cart_item_listing" + service_id).empty();
              jQuery(".cart_sub_total").empty();
              jQuery(".cart_tax").empty();
              jQuery(".cart_special_days").empty();
              jQuery(".frequent_discount").empty();
              jQuery(".cart_total").empty();
              jQuery(".remain_amount").empty();
              jQuery(".partial_amount").empty();
              jQuery(".cart_discount").empty();
            } else if (cart_session_data.status == "delete particuler") {
              jQuery(".add_minus_buttonid" + units_id).hide();
              jQuery("#ct-addon-" + units_id).attr("checked", false);
              jQuery(".cart_empty_msg").hide();
              jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).remove();
              jQuery(".partial_amount").html(cart_session_data.partial_amount);
              jQuery(".remain_amount").html(cart_session_data.remain_amount);
              jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
              jQuery(".cart_discount").html("- " + cart_session_data.cart_discount);
              jQuery(".cart_tax").html(cart_session_data.cart_tax);
              jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
              jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
              jQuery(".cart_total").html(cart_session_data.total_amount);
              jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
            }
          }
        });
      }
    });
  }
});
jQuery(document).on("click", ".addons_servicess", function () {
  var site_url = siteurlObj.site_url;
  var ajax_url = ajaxurlObj.ajax_url;
  var addon_id = jQuery(this).attr("data-id");
  var status = jQuery(this).attr("data-status");

  /*add to cart values */
  jQuery(".freq_disc_empty_cart_error").hide();
  var site_url = siteurlObj.site_url;
  var ajax_url = ajaxurlObj.ajax_url;
  var service_id = jQuery(this).attr("data-service_id");
  var method_id = jQuery(this).attr("data-method_id");
  var method_name = jQuery(this).attr("data-method_name");
  var type = "addon";
  var frequently_discount_id = jQuery("input[name=frequently_discount_radio]:checked").attr("data-id");
  var m_name = jQuery(this).attr("data-mnamee");
  /*end cart value */
  if (parseInt(status) == 2) {
    jQuery(this).attr("data-status", "1");
    jQuery.ajax({
      type: "post",
      data: { "addon_id": addon_id, "get_service_addons_qtys": 1 },
      url: ajax_url + "front_ajax.php",
      success: function (res) {
        jQuery(".addons_counting").append(res);
      }
    });
  } else {
    jQuery(this).attr("data-status", "2");
    jQuery(".remove_addon_intensive" + addon_id).hide();
    jQuery.ajax({
      type: "post",
      data: { "method_id": method_id, "service_id": service_id, "s_m_qty": 0, "s_m_rate": 0, "method_name": method_name, "units_id": addon_id, "type": type, "frequently_discount_id": frequently_discount_id, "add_to_cart": 1 },
      url: site_url + "front/firststep.php",
      success: function (res) {
        jQuery(".freq_discount_display").show();
        jQuery(".hide_right_side_box").show();
        jQuery(".partial_amount_hide_on_load").show();
        jQuery(".empty_cart_error").hide();
        jQuery(".coupon_invalid_error").hide();
        jQuery("#total_cart_count").val("2");
        var cart_session_data = jQuery.parseJSON(res);
        jQuery("#no_units_in_cart_err").val(cart_session_data.unit_status);
        jQuery("#no_units_in_cart_err_count").val(cart_session_data.unit_require);
        if (cart_session_data.status == "empty calculation") {
          jQuery(".hideduration_value").hide();
          jQuery(".total_time_duration_text").html("");
          jQuery(".partial_amount_hide_on_load").hide();
          jQuery(".hide_right_side_box").hide();
          jQuery(".cart_empty_msg").show();
          jQuery(".cart_item_listing" + service_id).empty();
          jQuery(".cart_sub_total").empty();
          jQuery(".cart_tax").empty();
          jQuery(".cart_special_days").empty();
          jQuery(".cart_total").empty();
          jQuery(".frequent_discount").empty();
          jQuery(".remain_amount").empty();
          jQuery(".partial_amount").empty();
          jQuery(".cart_discount").empty();
        } else if (cart_session_data.status == "delete particuler") {
          jQuery(".cart_empty_msg").hide();
          jQuery(".update_qty_of_s_m_" + m_name).remove();
          jQuery(".partial_amount").html(cart_session_data.partial_amount);
          jQuery(".remain_amount").html(cart_session_data.remain_amount);
          jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
          jQuery(".cart_discount").html("- " + cart_session_data.cart_discount);
          jQuery(".cart_tax").html(cart_session_data.cart_tax);
          jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
          jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
          jQuery(".cart_total").html(cart_session_data.total_amount);
          jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
        }
      }
    });
  }
});
/* new existing user */
/* ct_user_radio_group */
jQuery(document).on("change", ".existing-user", function () {
  if (jQuery(".existing-user").is(":checked")) {
    jQuery(".ct-login-existing").show("blind", { direction: "vertical" }, 700);
    jQuery(".ct-new-user-details").hide("blind", { direction: "vertical" }, 300);
    jQuery(".ct-peronal-details").hide("blind", { direction: "vertical" }, 300);
  }
  jQuery(".fancy_input").each(function () { jQuery(this).trigger("keyup"); });
  jQuery(".fancy_input").each(function () { jQuery(this).trigger("keyup"); });
});
jQuery(document).on("change", ".new-user", function () {
  if (jQuery(".new-user").is(":checked")) {
    jQuery(".ct-new-user-details").show("blind", { direction: "vertical" }, 700);
    jQuery(".ct-login-existing").hide("blind", { direction: "vertical" }, 300);
    jQuery(".ct-peronal-details").show("blind", { direction: "vertical" }, 300);
  }
  jQuery(".fancy_input").each(function () { jQuery(this).trigger("keyup"); });
});
function price_format_with_symbol(ct_amount) {
  var ct_amount = parseFloat(ct_amount);
  if (currency_symbol_position == "$100") {
    return "<span class=ct_total_small>" + currency_symbol + "</span>" + ct_amount.toFixed(price_format_decimal_places);
  } else {
    return ct_amount.toFixed(price_format_decimal_places) + "<span class=ct_total_small>" + currency_symbol + "</span>";
  }
}
function price_format_without_symbol(ct_amount) {
  var ct_amount = parseFloat(ct_amount);
  return ct_amount.toFixed(price_format_decimal_places);
}
/*frequently_discount*/
/* jQuery(document).on("click", ".cart_frequently_discount", function () {
  jQuery(".ct-loading-main").show();
  var p_d_status = partial_deposit.partial_deposit_status;
  var p_d_amount = partial_deposit.partial_deposit_amount;
  var p_d_type = partial_deposit.partial_deposit_type;
  jQuery(".freq_disc_empty_cart_error").hide();
  var discountname = jQuery(this).attr("data-name");
  var discount_type = jQuery(this).attr("data-discount_type");
  var discount_value = parseFloat(jQuery(this).attr("data-discount_value"));
  jQuery(".f_discount_name").html(discountname);
  var index_get_val = 0;
  if (currency_symbol_position == "$100") {
    index_get_val = 1;
  }
  if (jQuery(".cart_sub_total").text() != "") {
    var currency_symbol = jQuery(this).attr("data-currency_symbol");
    var cart_sub_total = jQuery(".cart_sub_total").text();
    var cart_sub_total_val = cart_sub_total.replace(currency_symbol, "");
    var cart_tax_val = 0;
    if (jQuery(".cart_tax").text() != "") {
      cart_tax_valss = jQuery(".cart_tax").text();
      var cart_tax_val = cart_tax_valss.replace(currency_symbol, "");
    }
    var freq_discount_amount = 0;
    if (discount_type == "P") {
      freq_discount_amount = cart_sub_total_val * (discount_value / 100);
    } else {
      freq_discount_amount = discount_value;
    }
    var cart_total = parseFloat(cart_sub_total_val) - parseFloat(freq_discount_amount) + parseFloat(cart_tax_val);
    if (cart_total <= 0) {
      cart_total = 0;
    }
    if (p_d_status == "Y") {
      var p_amount = 0;
      var r_amount = 0;
      if (p_d_type == "P") {
        var percentages = ((parseFloat(p_d_amount)) / 100);
        p_amount = parseFloat(cart_total) * parseFloat(percentages);
      } else if (p_d_type == "F") {
        p_amount = p_d_amount;
      }
      r_amount = parseFloat(cart_total) - parseFloat(p_amount);
      jQuery(".partial_amount").html(price_format_without_symbol(p_amount));
      jQuery(".remain_amount").html(price_format_without_symbol(r_amount));
    }
    jQuery(".frequent_discount").html("-" + price_format_without_symbol(freq_discount_amount));
    jQuery(".cart_total").html(price_format_with_symbol(cart_total));
    jQuery(".ct-loading-main").hide();
    var site_url = siteurlObj.site_url;
    jQuery.ajax({
      type: "POST",
      url: site_url + "front/firststep.php",
      data: {
        "freq_discount_amount": freq_discount_amount.toFixed(2),
        "frequently_discount_set": 1
      },
      success: function (res) { }
    });
  }
  else {
    jQuery(".ct-loading-main").hide();
  }
}); */
jQuery(document).on("click", ".cart_frequently_discount", function () {
  jQuery(".ct-loading-main").show();
  var p_d_status = partial_deposit.partial_deposit_status;
  var p_d_amount = parseFloat(partial_deposit.partial_deposit_amount); 
  var p_d_type = partial_deposit.partial_deposit_type;
  jQuery(".freq_disc_empty_cart_error").hide();
  
  var discountname = jQuery(this).attr("data-name");
  var discount_type = jQuery(this).attr("data-discount_type");
  var discount_value = parseFloat(jQuery(this).attr("data-discount_value"));
  
  jQuery(".f_discount_name").html(discountname);
  var index_get_val = 0;
  if (currency_symbol_position == "$100") {
      index_get_val = 1;
  }
  
  if (jQuery(".cart_sub_total").text() != "") {
      var currency_symbol = jQuery(this).attr("data-currency_symbol");
      var cart_sub_total_text = jQuery(".cart_sub_total").text().trim();
      var cart_sub_total_val = parseFloat(cart_sub_total_text.replace(currency_symbol, "").trim()); 
      var cart_tax_val = 0;
      if (jQuery(".cart_tax").text() != "") {
          var cart_tax_text = jQuery(".cart_tax").text().trim();
          cart_tax_val = parseFloat(cart_tax_text.replace(currency_symbol, "").trim());
      }
      var freq_discount_amount = 0;
      if (discount_type == "P") {
          freq_discount_amount = cart_sub_total_val * (discount_value / 100);
      } else {
          freq_discount_amount = discount_value;
      }
      var cart_total = cart_sub_total_val - freq_discount_amount + cart_tax_val;
      if (cart_total <= 0) {
          cart_total = 0;
      }
      if (p_d_status == "Y") {
          var p_amount = 0;
          var r_amount = 0;
          if (p_d_type == "P") {
              var percentages = p_d_amount / 100;
              p_amount = cart_total * percentages;
          } else if (p_d_type == "F") {
              p_amount = p_d_amount;
          }
          r_amount = cart_total - p_amount;
          jQuery(".partial_amount").html(price_format_without_symbol(p_amount.toFixed(2))); 
          jQuery(".remain_amount").html(price_format_without_symbol(r_amount.toFixed(2))); 
      }
      
      
      jQuery(".frequent_discount").html("-" + price_format_without_symbol(freq_discount_amount.toFixed(2))); 
      jQuery(".cart_total").html(price_format_with_symbol(cart_total.toFixed(2))); 
      
     
      jQuery(".ct-loading-main").hide();
      
     
      var site_url = siteurlObj.site_url;
      jQuery.ajax({
          type: "POST",
          url: site_url + "front/firststep.php",
          data: {
              "freq_discount_amount": freq_discount_amount.toFixed(2), 
              "frequently_discount_set": 1
          },
          success: function (res) {
              
          }
      });
  } else {
      
      jQuery(".ct-loading-main").hide();
  }
});
jQuery(document).on("change", "#contact_status", function () {
  var contact_status = jQuery("#contact_status").val();
  if (contact_status == "Other") {
    jQuery(".ct-option-others").show();
  } else {
    jQuery(".ct-option-others").hide();
  }
});
/******* Service method - display design according to admin selection ******/
jQuery(document).on("click", ".s_m_units_design", function () {
  jQuery(".ct-loading-main").show();
  // jQuery(".hideduration_value").hide();
  jQuery(".total_time_duration_text").html("");
  jQuery(".addons_servicess").each(function(){
    jQuery(this).attr("data-status","2");
    var value = jQuery(this).prop("checked");
    if(value == true){
      jQuery("#ct-addon-"+jQuery(this).attr("data-id")).attr("checked", false);
    }
    jQuery(".remove_addon_intensive"+jQuery(this).attr("data-id")).hide();
  }); 
  jQuery(".freq_discount_display").hide();
  /* jQuery(".cart_empty_msg").show(); */
  jQuery(".partial_amount_hide_on_load").hide();
  jQuery(".hide_right_side_box").hide();
  if (jQuery(".service-method-name").text() != "Service Usage Methods") {
    jQuery(".method_not_selected_error").attr("style", "");
    jQuery(".empty_cart_error").css("display", "none");
    jQuery(".empty_cart_error").html(errorobj_please_select_units_or_addons);
  }
  /* jQuery(".add_addon_in_cart_for_multipleqty").each(function(){
    var multiqty_addon_id = jQuery(this).attr("data-id");
    var value = jQuery(this).prop("checked");
    if(value == true){
      jQuery("#ct-addon-"+multiqty_addon_id).attr("checked", false);
    }
  });
  jQuery(".addon_qty").each(function(){
    jQuery(this).val(0);
    jQuery(".add_minus_button").hide();
    jQuery(".addons_servicess_2").attr("checked", false);
  }); */
  jQuery(".freq_disc_empty_cart_error").hide();
  //jQuery(".add_addon_in_cart_for_multipleqty").attr("data-status","2");
  var site_url = siteurlObj.site_url;
  var ajax_url = ajaxurlObj.ajax_url;
  var id = jQuery(this).attr("data-id");
  var method_id = jQuery(this).attr("data-id");
  var service_id = jQuery(this).attr("data-service_id");
  jQuery.ajax({
    type: "post",
    data: { "service_methods_id": id, "select_s_m_units_design": 1 },
    url: ajax_url + "front_ajax.php",
    success: function (response) {
      jQuery(".ct-loading-main").hide();
      if (response == 1) {
        /* jQuery( ".cart_item_listing"+service_id).empty();
        jQuery( ".cart_sub_total" ).empty();
        jQuery( ".cart_tax" ).empty();
        jQuery( ".cart_special_days" ).empty();                                  
        jQuery( ".cart_total" ).empty();
        jQuery( ".frequent_discount" ).empty();
        jQuery( ".remain_amount" ).empty();
        jQuery( ".partial_amount" ).empty();
        jQuery( ".cart_discount" ).empty();
        jQuery(".coupon_display").hide();
 */
        /* */
        jQuery(".user_coupon_display").hide();
        /* */

        jQuery(".s_m_units_design_1").show();
        jQuery(".s_m_units_design_2").hide();
        jQuery(".s_m_units_design_3").hide();
        jQuery(".s_m_units_design_4").hide();
        jQuery(".s_m_units_design_5").hide();
        jQuery.ajax({
          type: "post",
          data: { "method_id": method_id, "service_id": service_id, "s_m_units_maxlimit": 1 },
          url: ajax_url + "front_ajax.php",
          success: function (response) {
            jQuery(".duration_hrs").html(response);
          }
        });
      } else if (response == 2) {
        /* jQuery( ".cart_item_listing" ).empty();
        jQuery( ".cart_sub_total" ).empty();
        jQuery( ".cart_tax" ).empty();
        jQuery( ".cart_special_days" ).empty();                                  
        jQuery( ".cart_total" ).empty();
        jQuery( ".remain_amount" ).empty();
        jQuery( ".frequent_discount" ).empty();
        jQuery( ".partial_amount" ).empty();
        jQuery( ".cart_discount" ).empty();
        jQuery(".coupon_display").hide();
 */
        /* */
        jQuery(".user_coupon_display").hide();
        /* */

        jQuery(".s_m_units_design_1").hide();
        jQuery(".s_m_units_design_2").show();
        jQuery(".s_m_units_design_3").hide();
        jQuery(".s_m_units_design_4").hide();
        jQuery(".s_m_units_design_5").hide();
        jQuery.ajax({
          type: "post",
          data: { "method_id": method_id, "service_id": service_id, "s_m_units_maxlimit_2": 1 },
          url: ajax_url + "front_ajax.php",
          success: function (res) {
            jQuery(".ser_design_2_units").html(res);
            /*  if(jQuery("#row_tot").val()==1){                          
               jQuery(".select_bedroom").trigger("click");                         
             }  */
          }
        });
      } else if (response == 3) {
        /* jQuery( ".cart_item_listing" ).empty();
        jQuery( ".cart_sub_total" ).empty();
        jQuery( ".cart_tax" ).empty();
        jQuery( ".cart_special_days" ).empty();                                  
        jQuery( ".cart_total" ).empty();
        jQuery( ".remain_amount" ).empty();
        jQuery( ".partial_amount" ).empty();
        jQuery( ".frequent_discount" ).empty();
        jQuery( ".cart_discount" ).empty();
        jQuery(".coupon_display").hide(); */

        /* */
        jQuery(".user_coupon_display").hide();
        /* */

        jQuery(".s_m_units_design_1").hide();
        jQuery(".s_m_units_design_2").hide();
        jQuery(".s_m_units_design_3").show();
        jQuery(".s_m_units_design_4").hide();
        jQuery(".s_m_units_design_5").hide();
        jQuery.ajax({
          type: "post",
          data: { "method_id": method_id, "service_id": service_id, "s_m_units_maxlimit_3": 1 },
          url: ajax_url + "front_ajax.php",
          success: function (res) {
            jQuery(".ser_design_3_units").html(res);
            /*  if(jQuery("#row_tot").val()==1){                          
               jQuery(".select_m_u_btn").trigger("click");                         
             }  */
          }
        });
      } else if (response == 4) {
        /* jQuery( ".cart_item_listing" ).empty();
        jQuery( ".cart_sub_total" ).empty();
        jQuery( ".cart_tax" ).empty();
        jQuery( ".cart_special_days" ).empty();                                  
        jQuery( ".cart_total" ).empty();
        jQuery( ".remain_amount" ).empty();
        jQuery( ".frequent_discount" ).empty();
        jQuery( ".partial_amount" ).empty();
        jQuery( ".cart_discount" ).empty();
        jQuery(".coupon_display").hide();
 */
        /* */
        jQuery(".user_coupon_display").hide();
        /* */

        jQuery(".s_m_units_design_1").hide();
        jQuery(".s_m_units_design_2").hide();
        jQuery(".s_m_units_design_3").hide();
        jQuery(".s_m_units_design_4").show();
        jQuery(".s_m_units_design_5").hide();
        jQuery.ajax({
          type: "post",
          data: { "method_id": method_id, "service_id": service_id, "s_m_units_maxlimit_4": 1 },
          url: ajax_url + "front_ajax.php",
          success: function (res) {
            jQuery(".ser_design_4_units").html(res);
            /* if(jQuery("#row_tot").val()==1){                          
              jQuery(".addd").trigger("click");                         
            } */
          }
        });
      } else if (response == 5) {
        /* jQuery( ".cart_item_listing" ).empty();
        jQuery( ".cart_sub_total" ).empty();
        jQuery( ".cart_tax" ).empty();
        jQuery( ".cart_special_days" ).empty();                                  
        jQuery( ".cart_total" ).empty();
        jQuery( ".remain_amount" ).empty();
        jQuery( ".frequent_discount" ).empty();
        jQuery( ".partial_amount" ).empty();
        jQuery( ".cart_discount" ).empty();
        jQuery(".coupon_display").hide(); */

        /* */
        jQuery(".user_coupon_display").hide();
        /* */

        jQuery(".s_m_units_design_1").hide();
        jQuery(".s_m_units_design_2").hide();
        jQuery(".s_m_units_design_3").hide();
        jQuery(".s_m_units_design_4").hide();
        jQuery(".s_m_units_design_5").show();
        jQuery.ajax({
          type: "post",
          data: { "method_id": method_id, "service_id": service_id, "s_m_units_maxlimit_5": 1 },
          url: ajax_url + "front_ajax.php",
          success: function (res) {
            jQuery(".ser_design_5_units").html(res);
          }
        });
      }
    }
  });
});
/* bedrooms dropdown show hide list */
jQuery(document).on("click", ".select-bedrooms", function () {
  var unit_id = jQuery(this).attr("data-un_id");
  jQuery(".ct-" + unit_id + "-dropdown").toggle("blind", { direction: "vertical" }, 300);
});
/* select on click on bedroom */
jQuery(document).on("click", ".select_bedroom", function () {
  var units_id = jQuery(this).attr("data-units_id");
  jQuery("#ct_selected_" + units_id).html(jQuery(this).html());
  jQuery(".ct-" + units_id + "-dropdown").hide("blind", { direction: "vertical" }, 300);
});
jQuery(document).on("click", ".select-language", function () {
  jQuery(".ct-language-dropdown").toggle("blind", { direction: "vertical" }, 300);
});
jQuery(document).on("click", ".select_language_view", function () {
  jQuery(".ct-loading-main").show();
  var ajax_url = ajaxurlObj.ajax_url;
  jQuery("#ct_selected_language").html(jQuery(this).html());
  jQuery(".ct-language-dropdown").hide("blind", { direction: "vertical" }, 300);
  jQuery.ajax({
    type: "POST",
    data: { "select_language": "yes", "set_language": jQuery(this).attr("data-langs") },
    url: ajax_url + "front_ajax.php",
    success: function (res) {
      location.reload();
    }
  });
});
/* remove item btn-from the cart */
jQuery(document).on("click", ".remove_item_from_cart", function () {
  var ajax_url = ajaxurlObj.ajax_url;
  var unit_id = jQuery(this).data('units_id');
  var service_id = jQuery(this).data('service_id');
  var m_name = jQuery(this).data('mnamee');

  /* alert(service_id);   */

  var frequently_discount = jQuery("input[name=frequently_discount_radio]:checked").data('id');
  jQuery.ajax({
    type: 'POST',
    data: {
      'method_type': m_name,
      'cart_unit_id': unit_id,
      'frequently_discount_id': frequently_discount,
      'cart_item_remove': 1
    },
    url: ajax_url + "front_ajax.php",
    success: function (res) {
      var cart_session_data = jQuery.parseJSON(res);
      jQuery('.select-bedrooms').each(function () {
        if (jQuery(this).data('un_id') == unit_id && jQuery(this).data('mnamee') == m_name) {
          var dd_default_title = jQuery(this).attr('title');
          jQuery('#ct_selected_' + unit_id + ' .ct-count').html(dd_default_title);
        }
      });

      jQuery('.u_' + unit_id + '_btn').removeClass('ct-bed-selected');

      jQuery('#ct_area_m_units').val('');
      jQuery('#ct_area_m_units' + unit_id).val('');

      jQuery('.qtyyy_ad_unit' + unit_id).val('0');

      jQuery('.add_minus_buttonid' + unit_id).hide();
      jQuery('.remove_addon_intensive' + unit_id).hide();
      jQuery('#qty' + unit_id).val('0');

      jQuery('#ct-addon-' + unit_id).attr('data-status', '2');
      jQuery('#ct-addon-' + unit_id).prop('checked', false);
      var service_ul = '.cart_item_listing' + service_id;
      if (cart_session_data.status == 'empty calculation') {


        jQuery("#total_cart_count").val('1');
        jQuery('.freq_discount_display').hide();
        jQuery('.partial_amount_hide_on_load').hide();
        jQuery('.hide_right_side_box').hide();
        jQuery(".cart_empty_msg").hide();
        jQuery(".cart_item_listing" + service_id).empty();
        /* jQuery( ".cart_service_item_listing_li"+service_id).remove(); */
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
        jQuery(".cart_empty_msg").hide();
        jQuery(".update_qty_of_s_m_" + m_name).remove();
        jQuery('.partial_amount').html(cart_session_data.partial_amount);
        jQuery('.remain_amount').html(cart_session_data.remain_amount);
        jQuery('.cart_sub_total').html(cart_session_data.cart_sub_total);
        jQuery('.cart_discount').html('- ' + cart_session_data.cart_discount);
        jQuery('.cart_tax').html(cart_session_data.cart_tax);
        jQuery(".total_time_duration_text").html(cart_session_data.duration_text);

        jQuery('.frequent_discount').html(cart_session_data.frequent_discount);
        jQuery('.cart_total').html(cart_session_data.total_amount);

        if (ser_ids_dynamic != service_id) {
          if ((jQuery("ul" + service_ul).has("li").length === 0)) {
            jQuery(".cart_service_item_listing_li" + service_id).remove();
          }
          
        }
      }
    }
  });

});
/* bedroom counting */
jQuery(document).on("click", ".select_m_u_btn", function () {
  var units_id = jQuery(this).attr("data-units_id");
  jQuery(".u_" + units_id + "_btn").each(function () {
    jQuery(this).removeClass("ct-bed-selected");
  });
  jQuery(this).addClass("ct-bed-selected");
});
/* bedroom and bathroom counting */
jQuery(document).on("click", ".addd", function () {
  jQuery(".freq_disc_empty_cart_error").hide();
  jQuery(".custom_item_error").hide();
  jQuery(".custom_item_error").html("");
  var site_url = siteurlObj.site_url;
  var ajax_url = ajaxurlObj.ajax_url;
  var ids = jQuery(this).attr("data-ids");
  var db_qty = jQuery(this).attr("data-db-qty");
  var db_minqty = jQuery(this).attr("data-db-minqty");
  var service_id = jQuery(this).attr("data-service_id");
  var method_id = jQuery(this).attr("data-method_id");
  var method_name = jQuery(this).attr("data-method_name");
  var units_id = jQuery(this).attr("data-units_id");
  var type = jQuery(this).attr("data-type");
  var m_name = jQuery(this).attr("data-mnamee");
  var hfsec = jQuery(this).attr("data-hfsec");
  var unit_symbol = jQuery(this).attr("data-unit_symbol");
  jQuery(".coupon_display").hide();
  jQuery(".hide_coupon_textbox").show();
  jQuery(".ct-display-coupon-code").hide();

  /* */
  jQuery(".user_coupon_display").hide();
  jQuery(".hide_user_coupon_textbox").show();
  jQuery(".ct-display-user-coupon-code").hide();
  /* */


  /* */

  jQuery(".hide_referral_textbox").show();
  jQuery(".ct-display-referral-code").hide();

  /* */

  jQuery(".coupon_invalid_error").hide();
  var frequently_discount_id = jQuery("input[name=frequently_discount_radio]:checked").attr("data-id");
  var qty_val = 0;
  if (unit_symbol != "") {
    var qty_val_orignal = jQuery("#qty" + ids).val();
    var qty_val_array = qty_val_orignal.split(" ");
    qty_val = parseFloat(qty_val_array[0]);
  } else {
    qty_val = parseFloat(jQuery("#qty" + ids).val());
  }
  var qty_vals = qty_val;
  if (qty_val == 0) {
    qty_vals = db_minqty;
  } else if (qty_val < db_qty) {
    qty_vals = parseFloat(qty_val) + parseFloat(hfsec);
  }
  if (qty_val < db_qty) {
    jQuery(".qty" + ids).val(qty_vals + " " + unit_symbol);
    var final_qty_val = qty_vals;
    jQuery.ajax({
      type: "post",
      data: { "method_id": method_id, "service_id": service_id, "units_id": units_id, "qty_vals": final_qty_val, "hfsec": hfsec, "s_m_units_maxlimit_4_ratesss": 1 },
      url: ajax_url + "front_ajax.php",
      success: function (res) {
        jQuery(".data_qtyrate").attr("data-rate", res);
        jQuery.ajax({
          type: "post",
          data: { "method_id": method_id, "service_id": service_id, "s_m_qty": final_qty_val, "s_m_rate": res, "method_name": method_name, "units_id": units_id, "frequently_discount_id": frequently_discount_id, "type": type, "add_to_cart": 1 },
          url: site_url + "front/firststep.php",
          success: function (res) {
            jQuery(".freq_discount_display").show();
            jQuery(".hide_right_side_box").show();
            jQuery(".partial_amount_hide_on_load").show();
            jQuery(".empty_cart_error").hide();
            jQuery(".no_units_in_cart_error").hide();
            jQuery(".coupon_invalid_error").hide();
            jQuery("#total_cart_count").val("2");
            var cart_session_data = jQuery.parseJSON(res);
            jQuery("#no_units_in_cart_err").val(cart_session_data.unit_status);
            jQuery("#no_units_in_cart_err_count").val(cart_session_data.unit_require);
            if (cart_session_data.status == "update") {
              jQuery(".cart_empty_msg").hide();
              jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).html(cart_session_data.s_m_html);
              jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).val("data-service_id", service_id);
              jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).val("data-method_id", method_id);
              jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).val("data-units_id", units_id);
              jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
              jQuery(".partial_amount").html(cart_session_data.partial_amount);
              jQuery(".remain_amount").html(cart_session_data.remain_amount);
              jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
              jQuery(".cart_discount").html("- " + cart_session_data.cart_discount);
              jQuery(".cart_tax").html(cart_session_data.cart_tax);
              jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
              jQuery(".cart_total").html(cart_session_data.total_amount);
              jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
            } else if (cart_session_data.status == "insert") {
              jQuery(".hideduration_value").show();
              jQuery(".cart_empty_msg").hide();
              jQuery('.cart_item_listing' + service_id).append(cart_session_data.s_m_html);
              jQuery(".partial_amount").html(cart_session_data.partial_amount);
              jQuery(".remain_amount").html(cart_session_data.remain_amount);
              jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
              jQuery(".cart_discount").html("- " + cart_session_data.cart_discount);
              jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
              jQuery(".cart_tax").html(cart_session_data.cart_tax);
              jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
              jQuery(".cart_total").html(cart_session_data.total_amount);
              jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
            } else if (cart_session_data.status == "firstinsert") {
              jQuery(".hideduration_value").show();
              jQuery(".cart_empty_msg").hide();
              jQuery('.cart_item_listing' + service_id).append(cart_session_data.s_m_html);
              jQuery(".partial_amount").html(cart_session_data.partial_amount);
              jQuery(".remain_amount").html(cart_session_data.remain_amount);
              jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
              jQuery(".cart_discount").html("- " + cart_session_data.cart_discount);
              jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
              jQuery(".cart_tax").html(cart_session_data.cart_tax);
              jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
              jQuery(".cart_total").html(cart_session_data.total_amount);
              jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
            } else if (cart_session_data.status == "empty calculation") {
              jQuery(".hideduration_value").hide();
              jQuery(".total_time_duration_text").html("");
              jQuery(".freq_discount_display").hide();
              jQuery(".partial_amount_hide_on_load").hide();
              jQuery(".hide_right_side_box").hide();
              jQuery(".cart_empty_msg").show();
              jQuery(".frequent_discount").empty();
              jQuery(".cart_item_listing" + service_id).empty();
              jQuery(".cart_sub_total").empty();
              jQuery(".cart_tax").empty();
              jQuery(".cart_special_days").empty();
              jQuery(".cart_total").empty();
              jQuery(".remain_amount").empty();
              jQuery(".partial_amount").empty();
              jQuery(".cart_discount").empty();
            } else if (cart_session_data.status == "delete particuler") {
              jQuery(".cart_empty_msg").hide();
              jQuery(".update_qty_of_s_m_" + m_name).remove();
              jQuery(".partial_amount").html(cart_session_data.partial_amount);
              jQuery(".remain_amount").html(cart_session_data.remain_amount);
              jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
              jQuery(".cart_discount").html("- " + cart_session_data.cart_discount);
              jQuery(".cart_tax").html(cart_session_data.cart_tax);
              jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
              jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
              jQuery(".cart_total").html(cart_session_data.total_amount);
              jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
            }
          }
        });
      }
    });
  } else {
    jQuery(".ct-loading-main").hide();
    jQuery(".qty" + ids).val(qty_vals + " " + unit_symbol);
  }
});
jQuery(document).on("click", ".minuss", function () {
  jQuery(".freq_disc_empty_cart_error").hide();
  jQuery(".custom_item_error").hide();
  jQuery(".custom_item_error").html("");
  var site_url = siteurlObj.site_url;
  var ajax_url = ajaxurlObj.ajax_url;
  var ids = jQuery(this).attr("data-ids");
  var db_qty = jQuery(this).attr("data-db-qty");
  var db_minqty = jQuery(this).attr("data-db-minqty");
  var service_id = jQuery(this).attr("data-service_id");
  var method_id = jQuery(this).attr("data-method_id");
  var method_name = jQuery(this).attr("data-method_name");
  var hfsec = jQuery(this).attr("data-hfsec");
  var units_id = jQuery(this).attr("data-units_id");
  var type = jQuery(this).attr("data-type");
  var unit_symbol = jQuery(this).attr("data-unit_symbol");
  var frequently_discount_id = jQuery("input[name=frequently_discount_radio]:checked").attr("data-id");
  jQuery(".coupon_display").hide();
  jQuery(".hide_coupon_textbox").show();
  jQuery(".ct-display-coupon-code").hide();

  /* */
  jQuery(".user_coupon_display").hide();
  jQuery(".hide_user_coupon_textbox").show();
  jQuery(".ct-display-user-coupon-code").hide();
  /* */

  /* */

  jQuery(".hide_referral_textbox").show();
  jQuery(".ct-display-referral-code").hide();

  /* */

  jQuery(".coupon_invalid_error").hide();
  var currentVal = parseFloat(jQuery(".qty" + ids).val());
  var m_name = jQuery(this).attr("data-mnamee");
  var qty_val = 0;
  if (unit_symbol != "") {
    var qty_val_orignal = jQuery("#qty" + ids).val();
    var qty_val_array = qty_val_orignal.split(" ");
    qty_val = parseFloat(qty_val_array[0]);
  } else {
    qty_val = parseFloat(jQuery("#qty" + ids).val());
  }
  var qty_vals = qty_val;
  if (qty_val > db_minqty) {
    qty_vals = qty_val - hfsec;
  } else {
    qty_vals = 0;
  }
  if (currentVal <= 0) {
    jQuery(".ct-loading-main").hide();
    jQuery(".qty" + ids).val("0 " + unit_symbol);
    jQuery(".update_qty_of_s_m_" + m_name).remove();
  } else if (currentVal > 0) {
    jQuery(".qty" + ids).val(qty_vals + " " + unit_symbol);
    jQuery.ajax({
      type: "post",
      data: { "method_id": method_id, "service_id": service_id, "qty_vals": qty_vals, "units_id": units_id, "hfsec": hfsec, "s_m_units_maxlimit_4_ratesss": 1 },
      url: ajax_url + "front_ajax.php",
      success: function (res) {
        jQuery(".data_qtyrate").attr("data-rate", res);
        jQuery.ajax({
          type: "post",
          data: { "method_id": method_id, "service_id": service_id, "s_m_qty": qty_vals, "s_m_rate": res, "method_name": method_name, "units_id": units_id, "type": type, "frequently_discount_id": frequently_discount_id, "add_to_cart": 1 },
          url: site_url + "front/firststep.php",
          success: function (res) {
            jQuery(".freq_discount_display").show();
            jQuery(".hide_right_side_box").show();
            jQuery(".partial_amount_hide_on_load").show();
            jQuery(".empty_cart_error").hide();
            jQuery(".no_units_in_cart_error").hide();
            jQuery(".coupon_invalid_error").hide();
            jQuery("#total_cart_count").val("2");
            var cart_session_data = jQuery.parseJSON(res);
            jQuery("#no_units_in_cart_err").val(cart_session_data.unit_status);
            jQuery("#no_units_in_cart_err_count").val(cart_session_data.unit_require);
            if (cart_session_data.status == "update") {
              jQuery(".cart_empty_msg").hide();
              jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).html(cart_session_data.s_m_html);
              jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).val("data-service_id", service_id);
              jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).val("data-method_id", method_id);
              jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).val("data-units_id", units_id);
              jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
              jQuery(".partial_amount").html(cart_session_data.partial_amount);
              jQuery(".remain_amount").html(cart_session_data.remain_amount);
              jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
              jQuery(".cart_discount").html("- " + cart_session_data.cart_discount);
              jQuery(".cart_tax").html(cart_session_data.cart_tax);
              jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
              jQuery(".cart_total").html(cart_session_data.total_amount);
              jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
            } else if (cart_session_data.status == "insert") {
              jQuery(".hideduration_value").show();
              jQuery(".cart_empty_msg").hide();
              jQuery('.cart_item_listing' + service_id).append(cart_session_data.s_m_html);
              jQuery(".partial_amount").html(cart_session_data.partial_amount);
              jQuery(".remain_amount").html(cart_session_data.remain_amount);
              jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
              jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
              jQuery(".cart_discount").html("- " + cart_session_data.cart_discount);
              jQuery(".cart_tax").html(cart_session_data.cart_tax);
              jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
              jQuery(".cart_total").html(cart_session_data.total_amount);
              jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
            } else if (cart_session_data.status == "firstinsert") {
              jQuery(".hideduration_value").show();
              jQuery(".cart_empty_msg").hide();
              jQuery('.cart_item_listing' + service_id).append(cart_session_data.s_m_html);
              jQuery(".partial_amount").html(cart_session_data.partial_amount);
              jQuery(".remain_amount").html(cart_session_data.remain_amount);
              jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
              jQuery(".cart_discount").html("- " + cart_session_data.cart_discount);
              jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
              jQuery(".cart_tax").html(cart_session_data.cart_tax);
              jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
              jQuery(".cart_total").html(cart_session_data.total_amount);
              jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
            } else if (cart_session_data.status == "empty calculation") {
              jQuery(".hideduration_value").hide();
              jQuery(".total_time_duration_text").html("");
              jQuery(".freq_discount_display").hide();
              jQuery(".partial_amount_hide_on_load").hide();
              jQuery(".hide_right_side_box").hide();
              jQuery(".cart_empty_msg").show();
              jQuery(".cart_item_listing" + service_id).empty();
              jQuery(".cart_sub_total").empty();
              jQuery(".frequent_discount").empty();
              jQuery(".cart_tax").empty();
              jQuery(".cart_special_days").empty();
              jQuery(".cart_total").empty();
              jQuery(".remain_amount").empty();
              jQuery(".partial_amount").empty();
              jQuery(".cart_discount").empty();
            } else if (cart_session_data.status == "delete particuler") {
              jQuery(".cart_empty_msg").hide();
              jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).remove();
              jQuery(".partial_amount").html(cart_session_data.partial_amount);
              jQuery(".remain_amount").html(cart_session_data.remain_amount);
              jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
              jQuery(".cart_discount").html("- " + cart_session_data.cart_discount);
              jQuery(".cart_tax").html(cart_session_data.cart_tax);
              jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
              jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
              jQuery(".cart_total").html(cart_session_data.total_amount);
              jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
            }
          }
        });
      }
    });
  }
});
jQuery(document).on("keyup", ".ct_area_m_units", function (event) {
  jQuery(".freq_disc_empty_cart_error").hide();
  jQuery(".custom_item_error").hide();
  jQuery(".custom_item_error").html("");
  jQuery(".error_of_invalid_area" + units_id).hide();
  jQuery(".error_of_min_limitss" + units_id).hide();
  var site_url = siteurlObj.site_url;
  var ajax_url = ajaxurlObj.ajax_url;
  var units_id = jQuery(this).attr("data-units_id");
  var area_uniit = jQuery("#ct_area_m_units" + units_id).val();
  var service_id = jQuery(this).attr("data-service_id");
  var method_id = jQuery(this).attr("data-method_id");
  var max_limitts = parseFloat(jQuery(this).attr("data-maxx_limit"));
  var min_limitts = parseFloat(jQuery(this).attr("data-minn_limit"));
  var method_name = jQuery(this).attr("data-method_name");
  var type = jQuery(this).attr("data-type");
  var frequently_discount_id = jQuery("input[name=frequently_discount_radio]:checked").attr("data-id");
  jQuery(".coupon_display").hide();
  jQuery(".hide_coupon_textbox").show();
  jQuery(".ct-display-coupon-code").hide();

  /* */
  jQuery(".user_coupon_display").hide();
  jQuery(".hide_user_coupon_textbox").show();
  jQuery(".ct-display-user-coupon-code").hide();
  /* */

  /* */

  jQuery(".hide_referral_textbox").show();
  jQuery(".ct-display-referral-code").hide();

  /* */

  jQuery(".coupon_invalid_error").hide();
  var m_name = jQuery(this).attr("data-mnamee");
  if (event.which == 8) {
    jQuery(".error_of_invalid_area" + units_id).hide();
    jQuery(".error_of_max_limitss" + units_id).hide();
    jQuery(".error_of_min_limitss" + units_id).hide();
  }
  if (area_uniit != "" && /^[0-9\.]+$/.test(area_uniit) == false) {
    jQuery(".error_of_invalid_area" + units_id).show();
    jQuery(".error_of_invalid_area" + units_id).html(errorobj_invalid + " " + method_name);
    return false;
  }
  if (area_uniit == "") {
    jQuery.ajax({
      type: "post",
      data: { "method_id": method_id, "service_id": service_id, "s_m_qty": 0, "s_m_rate": 0, "method_name": method_name, "units_id": units_id, "type": type, "frequently_discount_id": frequently_discount_id, "add_to_cart": 1 },
      url: site_url + "front/firststep.php",
      success: function (res) {
        jQuery(".update_qty_of_s_m_mt_unit" + units_id).remove();
        jQuery(".freq_discount_display").show();
        jQuery(".hide_right_side_box").show();
        jQuery(".partial_amount_hide_on_load").show();
        jQuery(".empty_cart_error").hide();
        jQuery(".no_units_in_cart_error").hide();
        jQuery(".coupon_invalid_error").hide();
        jQuery("#total_cart_count").val("2");
        var cart_session_data = jQuery.parseJSON(res);
        jQuery("#no_units_in_cart_err").val(cart_session_data.unit_status);
        jQuery("#no_units_in_cart_err_count").val(cart_session_data.unit_require);
        if (cart_session_data.status == "empty calculation") {
          jQuery(".hideduration_value").hide();
          jQuery(".total_time_duration_text").html("");
          jQuery(".freq_discount_display").hide();
          jQuery(".partial_amount_hide_on_load").hide();
          jQuery(".hide_right_side_box").hide();
          jQuery(".cart_empty_msg").show();
          jQuery(".cart_item_listing" + service_id).empty();
          jQuery(".frequent_discount").empty();
          jQuery(".cart_sub_total").empty();
          jQuery(".cart_tax").empty();
          jQuery(".cart_special_days").empty();
          jQuery(".cart_total").empty();
          jQuery(".remain_amount").empty();
          jQuery(".partial_amount").empty();
          jQuery(".cart_discount").empty();
        } else if (cart_session_data.status == "delete particuler") {
          jQuery(".cart_empty_msg").hide();
          jQuery(".update_qty_of_s_m_" + m_name).remove();
          jQuery(".partial_amount").html(cart_session_data.partial_amount);
          jQuery(".remain_amount").html(cart_session_data.remain_amount);
          jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
          jQuery(".cart_discount").html("- " + cart_session_data.cart_discount);
          jQuery(".cart_tax").html(cart_session_data.cart_tax);
          jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
          jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
          jQuery(".cart_total").html(cart_session_data.total_amount);
          jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
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
    jQuery.ajax({
      type: "post",
      data: { "method_id": method_id, "service_id": service_id, "units_id": units_id, "qty_vals": area_uniit, "s_m_units_maxlimit_4_ratesss": 1 },
      url: ajax_url + "front_ajax.php",
      success: function (res) {
        jQuery(".ct_area_m_units_rattee").attr("data-rate", res);
        jQuery.ajax({
          type: "post",
          data: { "method_id": method_id, "service_id": service_id, "s_m_qty": area_uniit, "s_m_rate": res, "method_name": method_name, "units_id": units_id, "type": type, "frequently_discount_id": frequently_discount_id, "add_to_cart": 1 },
          url: site_url + "front/firststep.php",
          success: function (res) {
            jQuery(".freq_discount_display").show();
            jQuery(".hide_right_side_box").show();
            jQuery(".partial_amount_hide_on_load").show();
            jQuery(".empty_cart_error").hide();
            jQuery(".no_units_in_cart_error").hide();
            jQuery(".coupon_invalid_error").hide();
            jQuery("#total_cart_count").val("2");
            var cart_session_data = jQuery.parseJSON(res);
            jQuery("#no_units_in_cart_err").val(cart_session_data.unit_status);
            jQuery("#no_units_in_cart_err_count").val(cart_session_data.unit_require);
            if (cart_session_data.status == "update") {
              jQuery(".cart_empty_msg").hide();
              jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).html(cart_session_data.s_m_html);
              jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).val("data-service_id", service_id);
              jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).val("data-method_id", method_id);
              jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).val("data-units_id", units_id);
              jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
              jQuery(".partial_amount").html(cart_session_data.partial_amount);
              jQuery(".remain_amount").html(cart_session_data.remain_amount);
              jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
              jQuery(".cart_discount").html("- " + cart_session_data.cart_discount);
              jQuery(".cart_tax").html(cart_session_data.cart_tax);
              jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
              jQuery(".cart_total").html(cart_session_data.total_amount);
              jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
            } else if (cart_session_data.status == "insert") {
              jQuery(".hideduration_value").show();
              jQuery(".cart_empty_msg").hide();
              jQuery('.cart_item_listing' + service_id).append(cart_session_data.s_m_html);
              jQuery(".partial_amount").html(cart_session_data.partial_amount);
              jQuery(".remain_amount").html(cart_session_data.remain_amount);
              jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
              jQuery(".cart_discount").html("- " + cart_session_data.cart_discount);
              jQuery(".cart_tax").html(cart_session_data.cart_tax);
              jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
              jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
              jQuery(".cart_total").html(cart_session_data.total_amount);
              jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
            } else if (cart_session_data.status == "firstinsert") {
              jQuery(".hideduration_value").show();
              jQuery(".cart_empty_msg").hide();
              jQuery('.cart_item_listing' + service_id).append(cart_session_data.s_m_html);
              jQuery(".partial_amount").html(cart_session_data.partial_amount);
              jQuery(".remain_amount").html(cart_session_data.remain_amount);
              jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
              jQuery(".cart_discount").html("- " + cart_session_data.cart_discount);
              jQuery(".cart_tax").html(cart_session_data.cart_tax);
              jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
              jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
              jQuery(".cart_total").html(cart_session_data.total_amount);
              jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
            } else if (cart_session_data.status == "empty calculation") {
              jQuery(".hideduration_value").hide();
              jQuery(".total_time_duration_text").html("");
              jQuery(".freq_discount_display").hide();
              jQuery(".partial_amount_hide_on_load").hide();
              jQuery(".hide_right_side_box").hide();
              jQuery(".cart_empty_msg").show();
              jQuery(".cart_item_listing" + service_id).empty();
              jQuery(".cart_sub_total").empty();
              jQuery(".frequent_discount").empty();
              jQuery(".cart_tax").empty();
              jQuery(".cart_special_days").empty();
              jQuery(".cart_total").empty();
              jQuery(".remain_amount").empty();
              jQuery(".partial_amount").empty();
              jQuery(".cart_discount").empty();
            } else if (cart_session_data.status == "delete particuler") {
              jQuery(".cart_empty_msg").hide();
              jQuery(".update_qty_of_s_m_" + m_name).remove();
              jQuery(".partial_amount").html(cart_session_data.partial_amount);
              jQuery(".remain_amount").html(cart_session_data.remain_amount);
              jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
              jQuery(".cart_discount").html("- " + cart_session_data.cart_discount);
              jQuery(".cart_tax").html(cart_session_data.cart_tax);
              jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
              jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
              jQuery(".cart_total").html(cart_session_data.total_amount);
              jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
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
});
jQuery(document).on("click", ".add_item_in_cart", function () {
  jQuery(".freq_disc_empty_cart_error").hide();
  jQuery(".custom_item_error").hide();
  jQuery(".custom_item_error").html("");
  var site_url = siteurlObj.site_url;
  var ajax_url = ajaxurlObj.ajax_url;
  jQuery(".coupon_display").hide();
  jQuery(".hide_coupon_textbox").show();
  jQuery(".ct-display-coupon-code").hide();

  /* */
  jQuery(".user_coupon_display").hide();
  jQuery(".hide_user_coupon_textbox").show();
  jQuery(".ct-display-user-coupon-code").hide();
  /* */

  /* */

  jQuery(".hide_referral_textbox").show();
  jQuery(".ct-display-referral-code").hide();

  /* */

  jQuery(".coupon_invalid_error").hide();
  var s_m_qty = jQuery(this).attr("data-duration_value");
  var s_m_rate = jQuery(this).attr("data-rate");
  var service_id = jQuery(this).attr("data-service_id");
  var method_id = jQuery(this).attr("data-method_id");
  var method_name = jQuery(this).attr("data-method_name");
  var units_id = jQuery(this).attr("data-units_id");
  var type = jQuery(this).attr("data-type");
  var frequently_discount_id = jQuery("input[name=frequently_discount_radio]:checked").attr("data-id");
  var m_name = jQuery(this).attr("data-mnamee");
  jQuery.ajax({
    type: "post",
    data: { "method_id": method_id, "service_id": service_id, "s_m_qty": s_m_qty, "s_m_rate": s_m_rate, "method_name": method_name, "units_id": units_id, "type": type, "frequently_discount_id": frequently_discount_id, "add_to_cart": 1 },
    url: site_url + "front/firststep.php",
    success: function (res) {
      jQuery(".freq_discount_display").show();
      jQuery(".hide_right_side_box").show();
      jQuery(".partial_amount_hide_on_load").show();
      jQuery(".empty_cart_error").hide();
      jQuery(".no_units_in_cart_error").hide();
      jQuery(".coupon_invalid_error").hide();
      jQuery("#total_cart_count").val("2");
      var cart_session_data = jQuery.parseJSON(res);
      jQuery("#no_units_in_cart_err").val(cart_session_data.unit_status);
      jQuery("#no_units_in_cart_err_count").val(cart_session_data.unit_require);
      if (cart_session_data.status == "update") {
        jQuery(".cart_empty_msg").hide();
        jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).html(cart_session_data.s_m_html);
        jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).val("data-service_id", service_id);
        jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).val("data-method_id", method_id);
        jQuery(".update_qty_of_s_m_" + cart_session_data.method_name_without_space).val("data-units_id", units_id);
        jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
        jQuery(".partial_amount").html(cart_session_data.partial_amount);
        jQuery(".remain_amount").html(cart_session_data.remain_amount);
        jQuery(".special_offer").html(cart_session_data.special_offer_amount);
        jQuery(".cart_tax").html(cart_session_data.cart_tax);
        jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
        jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
        jQuery(".cart_total").html(cart_session_data.total_amount);
        jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
      } else if (cart_session_data.status == "insert") {
        jQuery(".hideduration_value").show();
        jQuery(".cart_empty_msg").hide();
        jQuery('.cart_item_listing' + service_id).append(cart_session_data.s_m_html);
        jQuery(".partial_amount").html(cart_session_data.partial_amount);
        jQuery(".remain_amount").html(cart_session_data.remain_amount);
        jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
        jQuery(".special_offer").html(cart_session_data.special_offer_amount);
        jQuery(".cart_tax").html(cart_session_data.cart_tax);
        jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
        jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
        jQuery(".cart_total").html(cart_session_data.total_amount);
        jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
      } else if (cart_session_data.status == "firstinsert") {
        jQuery(".hideduration_value").show();
        jQuery(".cart_empty_msg").hide();
        jQuery('.cart_item_listing' + service_id).append(cart_session_data.s_m_html);
        jQuery(".partial_amount").html(cart_session_data.partial_amount);
        jQuery(".remain_amount").html(cart_session_data.remain_amount);
        jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
        jQuery(".cart_tax").html(cart_session_data.cart_tax);
        jQuery(".special_offer").html(cart_session_data.special_offer_amount);
        jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
        jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
        jQuery(".cart_total").html(cart_session_data.total_amount);
        jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
      } else if (cart_session_data.status == "empty calculation") {
        jQuery(".hideduration_value").hide();
        jQuery(".total_time_duration_text").html("");
        jQuery(".freq_discount_display").hide();
        jQuery(".partial_amount_hide_on_load").hide();
        jQuery(".hide_right_side_box").hide();
        jQuery(".cart_empty_msg").show();
        jQuery(".cart_item_listing" + service_id).empty();
        jQuery(".cart_sub_total").empty();
        jQuery(".special_offer").empty();
        jQuery(".cart_tax").empty();
        jQuery(".cart_special_days").empty();
        jQuery(".cart_total").empty();
        jQuery(".remain_amount").empty();
        jQuery(".partial_amount").empty();
        jQuery(".cart_discount").empty();
        jQuery(".frequent_discount").empty();
      } else if (cart_session_data.status == "delete particuler") {
        jQuery(".cart_empty_msg").hide();
        jQuery(".update_qty_of_s_m_" + m_name).remove();
        jQuery(".partial_amount").html(cart_session_data.partial_amount);
        jQuery(".remain_amount").html(cart_session_data.remain_amount);
        jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
        jQuery(".cart_discount").html("- " + cart_session_data.cart_discount);
        jQuery(".special_offer").html(cart_session_data.special_offer_amount);
        jQuery(".cart_tax").html(cart_session_data.cart_tax);
        jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
        jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
        jQuery(".cart_total").html(cart_session_data.total_amount);
        jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
      }
    }
  });
});



jQuery(document).on("click touchstart", "#apply_referral", function () {
  jQuery(".ct-loading-main").show();
  jQuery(".freq_disc_empty_cart_error").hide();
  var site_url = siteurlObj.site_url;
  var ajax_url = ajaxurlObj.ajax_url;
  var referral_code = jQuery("#referral_val").val();
  if (referral_code == "") {
    jQuery(".ct-loading-main").hide();
    jQuery(".invalid_referral_error").css("display", "block");
    jQuery(".invalid_referral_error").html("Enter referral code");
  } else {
    jQuery.ajax({
      type: "POST",
      url: site_url + "front/firststep.php",
      data: { "referral_code": referral_code, "referral_check": 1 },
      success: function (res) {
        jQuery(".ct-loading-main").hide();
        jQuery(".freq_discount_display").show();
        jQuery(".ct-display-referral-code").show();
        jQuery(".hide_referral_textbox").hide();
      }
    });
  }
});

jQuery(document).on("click touchstart", "#apply_referral", function () {
  jQuery(".ct-loading-main").show();
  jQuery(".freq_disc_empty_cart_error").hide();
  var site_url = siteurlObj.site_url;
  var ajax_url = ajaxurlObj.ajax_url;
  var referral_code = jQuery("#referral_val").val();
  var dataString = { referral_code: referral_code, action: "referral_check" };
  if (referral_code == "") {
    jQuery(".ct-loading-main").hide();
    jQuery(".invalid_referral_error").css("display", "block");
    jQuery(".invalid_referral_error").html("Enter referral code");
  } else {
    jQuery.ajax({
      type: "POST",
      url: site_url + "front/checkout.php",
      data: dataString,
      success: function (res) {
        jQuery(".freq_discount_display").show();
        jQuery(".ct-display-referral-code").hide();
        jQuery(".hide_referral_textbox").show();
      }
    });
  }
});

  /* jQuery(document).on("click", "#apply_referral", function () {
    jQuery(".invalid_referral_error").hide();
  }); */

/*Reverse Coupon Code*/
/*jQuery(document).on("click touchstart",".user_referral_coupon",function(){
  jQuery(".ct-loading-main").show();
  jQuery(".freq_disc_empty_cart_error").hide();
  var site_url=siteurlObj.site_url;
  var display_referral = jQuery("#display_referral").text();
  var frequently_discount_id=jQuery("input[name=frequently_discount_radio]:checked").attr("data-id");
  jQuery.ajax({
    type:"POST",
    url: site_url+"front/firststep.php",
    data : { "display_referral" : display_referral, "frequently_discount_id" : frequently_discount_id, "referral_code_reversed" : 1 },
    success: function(res){
      jQuery(".ct-loading-main").hide(); 
      jQuery(".freq_discount_display").show();
      jQuery(".ct-display-referral-code").hide();
      jQuery(".hide_referral_textbox").show();
    }
  });
});*/

jQuery(document).on("click", ".user_referral_coupon", function () {
  jQuery("#referral_val").val('');
});



jQuery(document).on("click touchstart", "#apply_user_coupon", function () {
  jQuery(".ct-loading-main").show();
  jQuery(".freq_disc_empty_cart_error").hide();
  var site_url = siteurlObj.site_url;
  var ajax_url = ajaxurlObj.ajax_url;
  var user_coupon_val = jQuery(".user_coupon_val").val();
  var subtotal = jQuery(".cart_sub_total").text();

  var frequently_discount_id = jQuery("input[name=frequently_discount_radio]:checked").attr("data-id");
  if (subtotal == "") {
    jQuery(".ct-loading-main").hide();
    jQuery(".user_coupon_invalid_error").css("display", "block");
    jQuery(".user_coupon_invalid_error").html(errorobj_your_cart_is_empty_please_add_cleaning_services);
  } else {
    if (user_coupon_val == "") {
      jQuery(".ct-loading-main").hide();
      jQuery(".user_coupon_invalid_error").css("display", "block");
      jQuery(".user_coupon_invalid_error").html(errorobj_please_enter_coupon_code);
    } else {
      jQuery.ajax({
        type: "POST",
        url: site_url + "front/firststep.php",
        data: { "user_coupon_val": user_coupon_val, "frequently_discount_id": frequently_discount_id, "coupon_code_check": 1 },
        success: function (res) {
          jQuery(".ct-loading-main").hide();
          var cart_session_data = jQuery.parseJSON(res);
          if (cart_session_data.discount_status == "not") {
            jQuery(".user_coupon_invalid_error").css("display", "block");
            jQuery(".user_coupon_invalid_error").html(errorobj_coupon_expired);
          } else if (cart_session_data.discount_status == "wrongcode") {
            jQuery(".user_coupon_invalid_error").css("display", "block");
            jQuery(".user_coupon_invalid_error").html(errorobj_invalid_coupon);
          } else if (cart_session_data.discount_status == "available") {
            jQuery(".freq_discount_display").show();
            jQuery(".ct-display-user-coupon-code").show();
            jQuery(".hide_user_coupon_textbox").hide();
            jQuery(".user_coupon_display").show();
            jQuery(".ct-apply-user-coupon").hide();
            jQuery(".partial_amount").html(cart_session_data.partial_amount);
            jQuery(".remain_amount").html(cart_session_data.remain_amount);
            jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
            jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
            jQuery(".cart_tax").html(cart_session_data.cart_tax);
            jQuery(".cart_total").html(cart_session_data.total_amount);
            jQuery(".cart_discount").html("- " + cart_session_data.cart_discount);
          }
        }
      });
    }
  }
});

jQuery(document).on("click", "#user_coupon_val", function () {
  jQuery(".user_coupon_invalid_error").hide();
});

/*Reverse Coupon Code*/
jQuery(document).on("click touchstart", ".user_reverse_coupon", function () {
  jQuery(".ct-loading-main").show();
  jQuery(".freq_disc_empty_cart_error").hide();
  var site_url = siteurlObj.site_url;
  var display_user_coupon = jQuery("#display_user_code").text();
  var frequently_discount_id = jQuery("input[name=frequently_discount_radio]:checked").attr("data-id");
  jQuery.ajax({
    type: "POST",
    url: site_url + "front/firststep.php",
    data: { "display_user_coupon": display_user_coupon, "frequently_discount_id": frequently_discount_id, "referral_reversed": 1 },
    success: function (res) {
      jQuery(".ct-loading-main").hide();
      var cart_session_data = jQuery.parseJSON(res);
      if (cart_session_data.status == "reversed") {
        jQuery(".freq_discount_display").show();
        jQuery(".ct-display-user-coupon-code").hide();
        jQuery(".hide_user_coupon_textbox").show();
        jQuery(".user_coupon_display").hide();
        jQuery(".ct-apply-user-coupon").show();
        jQuery(".partial_amount").html(cart_session_data.partial_amount);
        jQuery(".remain_amount").html(cart_session_data.remain_amount);
        jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
        jQuery(".cart_tax").html(cart_session_data.cart_tax);
        jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
        jQuery(".cart_total").html(cart_session_data.total_amount);
      }
    }
  });
});


jQuery(document).on("click touchstart", "#apply_coupon", function () {
  jQuery(".ct-loading-main").show();
  jQuery(".freq_disc_empty_cart_error").hide();
  var site_url = siteurlObj.site_url;
  var ajax_url = ajaxurlObj.ajax_url;
  var coupon_code = jQuery("#coupon_val").val();
  var subtotal = jQuery(".cart_sub_total").text();
  var frequently_discount_id = jQuery("input[name=frequently_discount_radio]:checked").attr("data-id");
  if (subtotal == "") {
    jQuery(".ct-loading-main").hide();
    jQuery(".coupon_invalid_error").css("display", "block");
    jQuery(".coupon_invalid_error").html(errorobj_your_cart_is_empty_please_add_cleaning_services);
  } else {
    if (coupon_code == "") {
      jQuery(".ct-loading-main").hide();
      jQuery(".coupon_invalid_error").css("display", "block");
      jQuery(".coupon_invalid_error").html("Invalid coupon code");
    } else {
      jQuery.ajax({
        type: "POST",
        url: site_url + "front/firststep.php",
        data: { "coupon_code": coupon_code, "frequently_discount_id": frequently_discount_id, "coupon_check": 1 },
        success: function (res) {
          jQuery(".ct-loading-main").hide();
          jQuery("#display_code").text(coupon_code);
          jQuery(".fa-lgs").hide();
          var cart_session_data = jQuery.parseJSON(res);
          if (cart_session_data.discount_status == "not") {
            jQuery(".coupon_invalid_error").css("display", "block");
            jQuery(".coupon_invalid_error").html(errorobj_coupon_expired);
          }
          else if (cart_session_data.discount_status == "wrongcode") {
            jQuery(".coupon_invalid_error").css("display", "block");
            jQuery(".coupon_invalid_error").html(errorobj_invalid_coupon);
          } else if (cart_session_data.discount_status == "available") {
            jQuery(".ct-display-coupon-code").show();
            jQuery(".freq_discount_display").show();
            jQuery(".hide_coupon_textbox").hide();
            jQuery(".coupon_display").show();
            jQuery(".partial_amount").html(cart_session_data.partial_amount);
            jQuery(".remain_amount").html(cart_session_data.remain_amount);
            jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
            jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
            jQuery(".cart_tax").html(cart_session_data.cart_tax);
            jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
            jQuery(".cart_total").html(cart_session_data.total_amount);
            jQuery(".cart_discount").html("- " + cart_session_data.cart_discount);
          }
        }
      });
    }
  }
});

jQuery(document).on("click", "#coupon_val", function () {
  jQuery(".coupon_invalid_error").hide();
});
/*Reverse Coupon Code*/
jQuery(document).on("click touchstart", ".reverse_coupon", function () {
  jQuery(".ct-loading-main").show();
  jQuery(".freq_disc_empty_cart_error").hide();
  var site_url = siteurlObj.site_url;
  var coupon_reverse = jQuery("#display_code").text();
  var frequently_discount_id = jQuery("input[name=frequently_discount_radio]:checked").attr("data-id");
  jQuery.ajax({
    type: "POST",
    url: site_url + "front/firststep.php",
    data: { "coupon_reverse": coupon_reverse, "frequently_discount_id": frequently_discount_id, "coupon_reversed": 1 },
    success: function (res) {
      jQuery(".ct-loading-main").hide();
      var cart_session_data = jQuery.parseJSON(res);
      if (cart_session_data.status == "reversed") {
        jQuery(".freq_discount_display").show();
        jQuery(".ct-display-coupon-code").hide();
        jQuery(".hide_coupon_textbox").show();
        jQuery(".coupon_display").hide();
        jQuery(".partial_amount").html(cart_session_data.partial_amount);
        jQuery(".remain_amount").html(cart_session_data.remain_amount);
        jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
        jQuery(".cart_tax").html(cart_session_data.cart_tax);
        jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
        jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
        jQuery(".cart_total").html(cart_session_data.total_amount);
      }
    }
  });
});
/*** calendar code start ***/
/* time slots dropdown show hide list */
jQuery(document).on("click", ".time-slot-is", function () {
  jQuery(".time-slots-dropdown").show("blind", { direction: "vertical" }, 700);
});
jQuery(document).on("click", ".time-slot", function () {
  jQuery(".time-slot").each(function () {
    jQuery(this).removeClass("selected-time-slot");
  });
  jQuery(this).addClass("selected-time-slot");
  jQuery(".time-slots-dropdown").hide("blind", { direction: "vertical" }, 300);
});
jQuery(document).on("click", ".ct-week", function () {
  var valuess = jQuery(this).val();
  var s_date = jQuery(this).attr("data-s_date");
  var c_date = jQuery(this).attr("data-c_date");
  if (s_date >= c_date) {
    jQuery(".ct-week").each(function () {
      jQuery(this).removeClass("active");
      jQuery(".ct-show-time").removeClass("shown");
    });
    jQuery(this).addClass("active");
    jQuery(".ct-show-time").addClass("shown");
  } else if (s_date < c_date || valuess == "") {
    jQuery(".time_slot_box").hide();
  }
});
jQuery(document).on("click", ".selected_date", function () {
  jQuery(".ct-loading-main").show();
  var site_url = siteurlObj.site_url;
  var ajax_url = ajaxurlObj.ajax_url;
  var selected_dates = jQuery(this).attr("data-selected_dates");
  var s_date = jQuery(this).attr("data-s_date");
  var cur_dates = jQuery(this).attr("data-cur_dates");
  var c_date = jQuery(this).attr("data-c_date");
  var id = jQuery(this).attr("data-id");
  var ct_time_selected = jQuery(".ct-time-selected").text();
  var ct_date = jQuery("#save_selected_date").val();
  jQuery.ajax({
    type: "POST",
    url: ajax_url + "calendar_ajax.php",
    data: { "selected_dates": selected_dates, "id": id, "cur_dates": cur_dates, "get_slots": 1 },
    success: function (res) {
      jQuery(".ct-loading-main").hide();
      jQuery(".time_slot_box").hide();
      jQuery(".display_selected_date_slots_box" + id).html(res);
      jQuery(".display_selected_date_slots_box" + id).show();
      if (ct_time_selected != "") {
        jQuery(".time-slot").each(function () {
          var selectedtime = jQuery(this).attr("data-ct_time_selected");
          var slot_date = jQuery(this).attr("data-slot_date");
          if (selectedtime == ct_time_selected && slot_date == ct_date) {
            jQuery(this).addClass("ct-booked");
          }
        });
      }
      /*jQuery.ajax({
        type:"POST",
        url: site_url + "front/firststep.php",
        data : { "selected_dates" : selected_dates, "cur_dates" : cur_dates },
        success: function(res){
          jQuery(".ct-loading-main").hide();
          jQuery(".time_slot_box").hide();
          jQuery(".display_selected_date_slots_box"+id).html(res);
          jQuery(".display_selected_date_slots_box"+id).show();
          
          if(ct_time_selected != ""){
            jQuery(".time-slot").each(function(){
              var selectedtime = jQuery(this).attr("data-ct_time_selected");
              var slot_date = jQuery(this).attr("data-slot_date");
              if(selectedtime == ct_time_selected && slot_date == ct_date){
                jQuery(this).addClass("ct-booked");
              }
            });
          }
        }
      });*/
    }
  });
});
jQuery(document).on("click", ".previous_next,.today_btttn", function () {
  jQuery(".ct-loading-main").show();
  var site_url = siteurlObj.site_url;
  var ajax_url = ajaxurlObj.ajax_url;
  var month = jQuery(this).attr("data-next_month");
  var year = jQuery(this).attr("data-next_month_year");
  var todaybtn = jQuery(this).attr("data-istoday");
  var today_date = jQuery(this).attr("data-cur_dates");
  var ct_date_selected = jQuery(".ct-date-selected").text();
  var ct_time_selected = jQuery(".ct-time-selected").text();
  var ct_date = jQuery(".ct-date-selected").attr("data-date");
  jQuery.ajax({
    type: "POST",
    url: ajax_url + "calendar_ajax.php",
    data: { "month": month, "year": year, "get_calendar": 1 },
    success: function (res) {
      jQuery(".ct-loading-main").hide();
      jQuery(".cal_info").html(res);
      if (ct_date_selected != "") {
        jQuery(".add_date").addClass("ct-date-selected");
        jQuery(".add_time").addClass("ct-time-selected");
        jQuery(".add_date").html(ct_date_selected);
        jQuery(".add_date").attr("data-date", ct_date);
        jQuery(".add_time").html(ct_time_selected);
        jQuery(".ct-week").each(function () {
          var selecteddate = jQuery(this).attr("data-selected_dates");
          if (selecteddate == ct_date) {
            jQuery(".selected_datess" + ct_date).addClass("active");
            jQuery(".time-slot").each(function () {
              var selectedtime = jQuery(this).attr("data-ct_time_selected");
              if (selectedtime == ct_time_selected && selecteddate == ct_date) {
                jQuery(this).addClass("ct-booked");
              }
            });
          }
        });
      }
      var d = new Date();
      var month = d.getMonth() + 1;
      var day = d.getDate();
      var output = day + "-" + (month < 10 ? "0" : "") + month + "-" + d.getFullYear();
      var selected_dates = jQuery(".selected_date").attr("data-selected_dates");
      var cur_dates = jQuery(".selected_date").attr("data-cur_dates");
      if (output == cur_dates) {
        jQuery(".by_default_today_selected").addClass("active_today");
      }
      if (todaybtn == "Y") {
        jQuery(".dates .selected_datess" + today_date).trigger("click");
      }
    }
  });
});
jQuery(document).on("click", ".time_slotss", function () {
  jQuery(".ct-selected-date-view").removeClass("pulse");
  jQuery(".date_time_error").hide();
  jQuery(".time_slot_box").hide();
  jQuery(".space_between_date_time").show();
  jQuery(".special_day").show();
  jQuery(".hidedatetime_value").show();
  jQuery(".add_date").addClass("ct-date-selected");
  jQuery(".add_time").addClass("ct-time-selected");
  var slot_date_to_display = jQuery(this).attr("data-slot_date_to_display");
  var slot_date = jQuery(this).attr("data-slot_date");
  var slotdb_date = jQuery(this).attr("data-slotdb_date");
  var slot_time = jQuery(this).attr("data-slot_time");
  var slotdb_time = jQuery(this).attr("data-slotdb_time");
  var ct_date_selected = jQuery(this).attr("data-ct_date_selected");
  var ct_time_selected = jQuery(this).attr("data-ct_time_selected");
  jQuery(".ct-date-selected").attr("data-date", slot_date);
  jQuery("#save_selected_date").val(slot_date);
  jQuery(".ct-date-selected").html(ct_date_selected);
  jQuery(".ct-time-selected").html(ct_time_selected);
  jQuery(".ct-selected-date-view").addClass("pulse");
  jQuery(".cart_date").html(slot_date_to_display);
  jQuery(".cart_date").attr("data-date_val", slotdb_date);
  jQuery(".cart_time").html(slot_time);
  jQuery(".cart_time").attr("data-time_val", slotdb_time);
});
jQuery(document).on("click", ".today_btttn", function () {
  var today_date = jQuery(this).attr("data-cur_dates");
  jQuery(".dates .selected_datess" + today_date).trigger("click");
});
/*** calendar code end ***/
/* Display Country Code on click flag on phone*/
jQuery(document).on("click", ".country", function () {
  var country_code = jQuery(this).attr("data-dial-code");
  jQuery("#ct-user-phone").val("+" + country_code);
});
/** Code for area code **/
if (ct_postalcode_status_check == "Y") {
  jQuery(document).on("keyup", "#ct_postal_code", function (event) {
    var ajax_url = ajaxurlObj.ajax_url;
    var postal_code = jQuery(this).val();
    var postal_code_low = jQuery(this).val().toLowerCase();
    var postal_code_up = jQuery(this).val().toUpperCase();
    jQuery.validator.addMethod("alphanumeric", function(value, element) {
      return this.optional(element) || /^[a-zA-Z0-9]+$/.test(value);
    }); 
    if (ct_postalcode_zip_status == "on") {
      jQuery("#app-zip-code").val(postal_code);
    }
    if (postal_code == "") {
      jQuery(".remove_show_error_class").addClass("show-error");
      jQuery("#complete_bookings").addClass("ct_remove_id");
      jQuery(document).on("click", ".ct_remove_id", function () {
        jQuery("#ct_postal_code").focus();
      });
      jQuery(".ct_remove_id").attr("id", "");
      jQuery(".postal_code_available").hide();
      jQuery(".postal_code_error").show();
      jQuery(".postal_code_error").html(errorobj_please_enter_postal_code);
    } else {
      var postal_arr = jQuery(this).attr('data-id');
      var p_arr = postal_arr.split(',');
      var flag = 0;
      if (jQuery.inArray(postal_code, p_arr) !== -1) {
        flag = 1;
      } else if (jQuery.inArray(postal_code_up, p_arr) !== -1) {
        flag = 1;
      } else if (jQuery.inArray(postal_code_low, p_arr) !== -1) {
        flag = 1;
      }
      if (flag == 1) {
        jQuery(".ct_remove_id").attr("id", "complete_bookings");
        jQuery("#complete_bookings").removeClass("ct_remove_id");
        jQuery(".remove_show_error_class").removeClass("show-error");
        jQuery(".postal_code_error").hide();
      } else {
        jQuery(".remove_show_error_class").addClass("show-error");
        jQuery("#complete_bookings").addClass("ct_remove_id");
        jQuery(document).on("click", ".ct_remove_id", function () {
          jQuery("#ct_postal_code").focus();
        });
        jQuery(".ct_remove_id").attr("id", "");
        jQuery(".postal_code_error").show();
        jQuery(".postal_code_error").html("Our Service not Available at your location.");
      }
    }
    jQuery(".fancy_input").each(function () { jQuery(this).trigger("keyup"); });
  });
}
jQuery(document).on("click", ".add_addon_in_cart_for_multipleqty", function () {
  jQuery(".freq_disc_empty_cart_error").hide();
  var site_url = siteurlObj.site_url;
  var ajax_url = ajaxurlObj.ajax_url;
  jQuery(".coupon_display").hide();
  jQuery(".hide_coupon_textbox").show();
  jQuery(".ct-display-coupon-code").hide();
  jQuery(".coupon_invalid_error").hide();
  var s_m_qty = jQuery(this).attr("data-duration_value");
  var s_m_rate = jQuery(this).attr("data-rate");
  var service_id = jQuery(this).attr("data-service_id");
  var method_id = jQuery(this).attr("data-method_id");
  var method_name = jQuery(this).attr("data-method_name");
  var units_id = jQuery(this).attr("data-units_id");
  var type = jQuery(this).attr("data-type");
  var frequently_discount_id = jQuery("input[name=frequently_discount_radio]:checked").attr("data-id");
  var m_name = jQuery(this).attr("data-mnamee");
  var status = jQuery(this).attr("data-status");
  if (parseInt(status) == 2) {
    jQuery(this).attr("data-status", "1");
    jQuery.ajax({
      type: "post",
      data: { "method_id": method_id, "service_id": service_id, "s_m_qty": s_m_qty, "s_m_rate": s_m_rate, "method_name": method_name, "units_id": units_id, "type": type, "frequently_discount_id": frequently_discount_id, "add_to_cart": 1 },
      url: site_url + "front/firststep.php",
      success: function (res) {
        jQuery(".freq_discount_display").show();
        jQuery(".hide_right_side_box").show();
        jQuery(".partial_amount_hide_on_load").show();
        jQuery(".empty_cart_error").hide();
        jQuery(".coupon_invalid_error").hide();
        jQuery("#total_cart_count").val("2");
        var cart_session_data = jQuery.parseJSON(res);
        jQuery("#no_units_in_cart_err").val(cart_session_data.unit_status);
        jQuery("#no_units_in_cart_err_count").val(cart_session_data.unit_require);
        if (cart_session_data.status == "insert") {
          jQuery(".cart_empty_msg").hide();
          jQuery('.cart_item_listing' + service_id).append(cart_session_data.s_m_html);
          jQuery(".partial_amount").html(cart_session_data.partial_amount);
          jQuery(".remain_amount").html(cart_session_data.remain_amount);
          jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
          jQuery(".cart_tax").html(cart_session_data.cart_tax);
          jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
          jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
          jQuery(".cart_total").html(cart_session_data.total_amount);
          jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
        } else if (cart_session_data.status == "empty calculation") {
          jQuery(".hideduration_value").hide();
          /* jQuery(".total_time_duration_text").html(""); */
          jQuery(".freq_discount_display").show();
          jQuery(".partial_amount_hide_on_load").hide();
          jQuery(".hide_right_side_box").hide();
          jQuery(".cart_empty_msg").hide();
          jQuery(".cart_item_listing" + service_id).empty();
          jQuery(".cart_sub_total").empty();
          jQuery(".frequent_discount").empty();
          jQuery(".cart_tax").empty();
          jQuery(".cart_special_days").empty();
          jQuery(".cart_total").empty();
          jQuery(".remain_amount").empty();
          jQuery(".partial_amount").empty();
          jQuery(".cart_discount").empty();
        } else if (cart_session_data.status == "delete particuler") {
          jQuery(".cart_empty_msg").hide();
          jQuery(".update_qty_of_s_m_" + m_name).remove();
          jQuery(".partial_amount").html(cart_session_data.partial_amount);
          jQuery(".remain_amount").html(cart_session_data.remain_amount);
          jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
          jQuery(".cart_discount").html("- " + cart_session_data.cart_discount);
          jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
          jQuery(".cart_tax").html(cart_session_data.cart_tax);
          jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
          jQuery(".cart_total").html(cart_session_data.total_amount);
          jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
        }
      }
    });
  } else {
    jQuery(this).attr("data-status", "2");
    jQuery.ajax({
      type: "post",
      data: { "method_id": method_id, "service_id": service_id, "s_m_qty": s_m_qty, "s_m_rate": s_m_rate, "method_name": method_name, "units_id": units_id, "type": type, "frequently_discount_id": frequently_discount_id, "add_to_cart": 1 },
      url: site_url + "front/firststep.php",
      success: function (res) {
        jQuery(".freq_discount_display").show();
        jQuery(".hide_right_side_box").show();
        jQuery(".partial_amount_hide_on_load").show();
        jQuery(".empty_cart_error").hide();
        jQuery(".coupon_invalid_error").hide();
        jQuery("#total_cart_count").val("2");
        var cart_session_data = jQuery.parseJSON(res);
        jQuery("#no_units_in_cart_err").val(cart_session_data.unit_status);
        jQuery("#no_units_in_cart_err_count").val(cart_session_data.unit_require);
        if (cart_session_data.status == "empty calculation") {
          // jQuery(".hideduration_value").hide();
          jQuery(".total_time_duration_text").html("");
          jQuery(".partial_amount_hide_on_load").hide();
          jQuery(".hide_right_side_box").hide();
          jQuery(".cart_empty_msg").show();
          jQuery(".cart_item_listing" + service_id).empty();
          jQuery(".cart_sub_total").empty();
          jQuery(".cart_tax").empty();
          jQuery(".cart_special_days").empty();
          jQuery(".cart_total").empty();
          jQuery(".frequent_discount").empty();
          jQuery(".remain_amount").empty();
          jQuery(".partial_amount").empty();
          jQuery(".cart_discount").empty();
        } else if (cart_session_data.status == "delete particuler") {
          jQuery(".cart_empty_msg").hide();
          jQuery(".update_qty_of_s_m_" + m_name).remove();
          jQuery(".partial_amount").html(cart_session_data.partial_amount);
          jQuery(".remain_amount").html(cart_session_data.remain_amount);
          jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);
          jQuery(".cart_discount").html("- " + cart_session_data.cart_discount);
          jQuery(".cart_tax").html(cart_session_data.cart_tax);
          jQuery(".cart_special_days").html(cart_session_data.cart_special_days);
          jQuery(".frequent_discount").html(cart_session_data.frequent_discount);
          jQuery(".cart_total").html(cart_session_data.total_amount);
          jQuery(".total_time_duration_text").html(cart_session_data.duration_text);
        }
      }
    });
  }
});
/*Reset Password*/
jQuery(document).on("click", "#reset_pass", function () {
  jQuery(".ct-loading-main").show();
  jQuery(".add_show_error_class").each(function () {
    jQuery(this).trigger("keyup");
  });
  var front_url = fronturlObj.front_url;
  var site_url = siteurlObj.site_url;
  var email = jQuery("#rp_user_email").val();
  var dataString = { email: email, action: "forget_password" };
  if (jQuery("#forget_pass").valid()) {
    jQuery.ajax({
      type: "POST",
      url: front_url + "firststep.php",
      data: dataString,
      success: function (response) {
        jQuery(".ct-loading-main").hide();
        if (response == "not") {
          jQuery(".forget_pass_incorrect").css("display", "block");
          jQuery(".forget_pass_incorrect").css("color", "red");
          jQuery(".forget_pass_incorrect").html(errorobj_invalid_email_id_please_register_first);
        } else {
          jQuery(".forget_pass_correct").css("display", "block");
          jQuery(".forget_pass_correct").css("color", "green");
          jQuery(".forget_pass_correct").html(errorobj_your_password_send_successfully_at_your_registered_email_id);
          jQuery("#reset_pass").unbind("click");
          jQuery("#reset_pass").css({ "pointer-events": "none", "cursor": "default" });
          setTimeout(function () { window.location.href = site_url; }, 5000);
          event.preventDefault();
        }
      },
    });
  }
});
jQuery(document).on("click", "#rp_user_email", function () {
  jQuery(".forget_pass_incorrect").hide();
});
jQuery(document).on("click", "#rn_password", function () {
  jQuery(".mismatch_password").hide();
});
jQuery(document).on("click", "#n_password", function () {
  jQuery(".mismatch_password").hide();
});
jQuery(document).on("click", "#password", function () {
  jQuery(".succ_password").hide();
});
jQuery(document).on("click", "#email", function () {
  jQuery(".succ_password").hide();
});
/*Reset New Password*/
jQuery(document).on("click", "#reset_new_password", function () {
  jQuery(".ct-loading-main").show();
  var front_url = fronturlObj.front_url;
  var new_reset_pass = jQuery("#n_password").val();
  var retype_new_reset_pass = jQuery("#rn_password").val();
  var dataString = { retype_new_reset_pass: retype_new_reset_pass, action: "reset_new_password" };
  if (jQuery("#reset_new_passwd").valid()) {
    if (new_reset_pass == retype_new_reset_pass) {
      jQuery.ajax({
        type: "POST",
        url: front_url + "firststep.php",
        data: dataString,
        success: function (response) {
          jQuery(".ct-loading-main").hide();
          if (response == "password reset successfully") {
            jQuery(".succ_password").css("display", "block");
            jQuery(".succ_password").addClass("txt-success");
            jQuery(".succ_password").html(errorobj_your_password_reset_successfully_please_login);
          }
        },
      });
    } else {
      jQuery(".ct-loading-main").hide();
      jQuery(".mismatch_password").css("display", "block");
      jQuery(".mismatch_password").addClass("error");
      jQuery(".mismatch_password").html(errorobj_new_password_and_retype_new_password_mismatch);
    }
  }
});
/* same as above details  */
jQuery(document).on("change", "#retype_status", function () {
  var user_address = jQuery("#ct-street-address").val();
  var user_zipcode = jQuery("#ct-zip-code").val();
  var user_city = jQuery("#ct-city").val();
  var user_state = jQuery("#ct-state").val();
  if (jQuery("#retype_status").prop("checked")) {
    jQuery("#app-street-address").val(user_address);
    if (ct_postalcode_status_check != "Y") {
      jQuery("#app-zip-code").val(user_zipcode);
    }
    jQuery("#app-city").val(user_city);
    jQuery("#app-state").val(user_state);
  } else {
    jQuery("#app-street-address").val("");
    if (ct_postalcode_status_check != "Y") {
      jQuery("#app-zip-code").val("");
    }
    jQuery("#app-city").val("");
    jQuery("#app-state").val("");
  }
  jQuery(".fancy_input").each(function () { jQuery(this).trigger("keyup"); });
});

jQuery(document).on("click", ".ct_method_tab-slider--nav li,.ct_method_tab-slider--nav li.active", function () {
  if (!jQuery(this).hasClass("ct_method_tab-blank_li")) {
    var totallis = 0;
    var selectedli = 0;
    var currentli = jQuery(this).html();
    var divid = jQuery(this).attr("data-id");
    var maindivid = jQuery(this).attr("data-maindivid");
    jQuery(".ct_method_tab-slider--nav").each(function () {
      var common_id = jQuery(this).attr("data-id");
      if (jQuery(".ct_method_tab-slider--nav_dynamic" + common_id + " li").length == 2) {
        jQuery(".ct_method_tab-slider--nav_dynamic" + common_id + " ul li").css('width', '50%');

      } else if (jQuery(".ct_method_tab-slider--nav_dynamic" + common_id + " li").length == 1) {
        jQuery(".ct_method_tab-slider--nav_dynamic" + common_id + " ul").append("<li class='ct_method_tab-slider-trigger ct_method_tab-blank_li'>&nbsp;</li><li class='ct_method_tab-slider-trigger ct_method_tab-blank_li'>&nbsp;</li>");
      }
    });
    jQuery(".ct_method_tab-slider--nav_dynamic" + maindivid + " li").each(function () {
      if (jQuery(this).html() == currentli) {
        selectedli = totallis;
      }
      totallis++;
    });
    var leftpercent = 100 / totallis;
    var currentpercent = leftpercent * selectedli;
    jQuery("head").find("style").each(function () {
      var attr = jQuery(this).attr("data-dynmicstyle");
      if (typeof attr !== typeof undefined && attr !== false) {
        jQuery(this).remove();
      }
    });
    jQuery("<style data-dynmicstyle>.ct_method_tab-slider--nav_dynamic" + maindivid + " .ct_method_tab-slider-tabs.ct_methods_slide:after{width:" + leftpercent + "% !important;left:" + currentpercent + "% !important;}</style>").appendTo("head");
    jQuery(".ct_method_tab-slider--nav_dynamic" + maindivid + " li").removeClass("active");
    jQuery(".ct_method_tab-slider-trigger_dynamic" + divid).addClass("active");
  }
});


jQuery(document).ready(function () {

  jQuery("#step_previous").hide();
  jQuery(document).on("click", "#step_next", function () {
    if (jQuery("#ct-step-two").not(":visible")) {
      jQuery("#step_previous").show();
    }

    if (jQuery("#ct-step-one").is(":visible")) {
      jQuery("#step_previous").hide();
      if (ct_postalcode_status_check == "Y") {
        jQuery("#postalcodefrm").validate({
          rules: {
            ct_postal_code: {
              required: true,
              digits: true
            },
          },
          messages: {
            ct_postal_code: {
              required: "The zip code is required field",
              digits: "Only degits allow"
            },
          }
        });
      }

      if (ct_postalcode_status_check == "Y") {
        if (jQuery("#postalcodefrm").valid()) {
          var postal_code = jQuery("#ct_postal_code").val();
          postalcode = 1;
        } else {
          postalcode = 0;
          jQuery(".postal_code_available").hide();
          jQuery(".postal_code_error").show();
          jQuery(".postal_code_error").html(errorobj_please_enter_postal_code);
          return false;
        }
      
      if (postal_code == "") {
        jQuery(".remove_show_error_class").addClass("show-error");
        jQuery("#complete_bookings").addClass("ct_remove_id");
        jQuery(document).on("click", ".ct_remove_id", function () {
          jQuery("#ct_postal_code").focus();
        });
        jQuery(".ct_remove_id").attr("id", "");
        jQuery(".postal_code_available").hide();
        jQuery(".postal_code_error").show();
        jQuery(".postal_code_error").html(errorobj_please_enter_postal_code);
      } else {
        var postal_arr = jQuery("#ct_postal_code").attr('data-id');
        var postal_code_low = jQuery("#ct_postal_code").val().toLowerCase();
        var postal_code_up = jQuery("#ct_postal_code").val().toUpperCase();
        var p_arr = postal_arr.split(',');
        var flag = 0;
        if (jQuery.inArray(postal_code, p_arr) !== -1) {
          flag = 1;
        } else if (jQuery.inArray(postal_code_up, p_arr) !== -1) {
          flag = 1;
        } else if (jQuery.inArray(postal_code_low, p_arr) !== -1) {
          flag = 1;
        }
        if (flag == 1) {
          jQuery(".ct_remove_id").attr("id", "complete_bookings");
          jQuery("#complete_bookings").removeClass("ct_remove_id");
          jQuery(".remove_show_error_class").removeClass("show-error");
          jQuery(".postal_code_error").hide();
        } else {
          jQuery(".remove_show_error_class").addClass("show-error");
          jQuery("#complete_bookings").addClass("ct_remove_id");
          jQuery(document).on("click", ".ct_remove_id", function () {
            jQuery("#ct_postal_code").focus();
          });
          jQuery(".ct_remove_id").attr("id", "");
          jQuery(".postal_code_error").show();
          jQuery(".postal_code_error").html("Our Service not Available at your location.");
        }
      }
    }
      var addon = document.getElementsByName('service-radio');

      if (!jQuery("input[name='service-radio']").is("checked")) {
        
        jQuery(".custom_service_error").show();
        jQuery(".custom_service_error").first().html("Please select service");


      } else {
        jQuery(".custom_service_error").html("");
      }

      if (jQuery(".custom_item_error").css('display') == 'block') {
       
        // jQuery(".custom_service_error").html("");
        if (jQuery('.cart-items-main').find('ul').find("li").length == 1) {
          jQuery(".custom_service_error").html("");

          jQuery(".custom_item_error").html("Please select service item");
          jQuery(".custom_item_error").show();
        }
      }
      else {
        jQuery(".custom_item_error").html("");
      }

      if (ct_postalcode_status_check == "Y") {
        if (jQuery(".custom_item_error").css('display') != 'block') {
          var staff_provider_status = jQuery("#staff_provider_status").val();
          var staff_count_forservice = jQuery("#staff_count_forservice").val();
          if (frequency_status.status == 'N' && staff_count_forservice == '0') {
            if (staff_provider_status == 'Y') {
              jQuery("#ct-step-one").slideUp(300);
              jQuery("#ct-step-two").slideDown(300);
              jQuery("#step_previous").show();
            } else {
              jQuery("#ct-step-one").slideUp(300);
              jQuery("#ct-step-three").slideDown(300);
              jQuery("#step_previous").show();
            }
          } else {
            jQuery("#ct-step-one").slideUp(300);
            jQuery("#ct-step-two").slideDown(300);
            jQuery("#step_previous").show();
          }
        } else {
          return false;
        }
      } else {
        if (jQuery(".custom_item_error").css('display') != 'block') {
          var staff_provider_status = jQuery("#staff_provider_status").val();
          var staff_count_forservice = jQuery("#staff_count_forservice").val();
          if (frequency_status.status == 'N' && staff_count_forservice == '0') {
            if (staff_provider_status == 'Y') {
              jQuery("#ct-step-one").slideUp(300);
              jQuery("#ct-step-two").slideDown(300);
              jQuery("#step_previous").show();
            } else {
              jQuery("#ct-step-one").slideUp(300);
              jQuery("#ct-step-three").slideDown(300);
              jQuery("#step_previous").show();
            }
          } else {
            jQuery("#ct-step-one").slideUp(300);
            jQuery("#ct-step-two").slideDown(300);
            jQuery("#step_previous").show();
          }
        } else {
          return false;
        }
      }
    } else if (jQuery("#ct-step-two").is(":visible")) {
      jQuery("#ct-step-two").slideUp(300);
      jQuery("#ct-step-three").slideDown(300);
    } else if (jQuery("#ct-step-three").is(":visible")) {
      var booking_date_text = jQuery(".cart_date").text();
      var booking_date = jQuery(".cart_date").attr('data-date_val');
      var booking_time = jQuery(".cart_time").attr('data-time_val');
      var booking_time_text = jQuery(".cart_time").text();
      

      if (booking_date_text == '' &&  booking_date == '' &&  booking_time == '' &&  booking_time_text == '') {
        clicked = false;
        jQuery('.ct-loading-main-complete_booking').hide();
        jQuery('.date_time_error').css('display', 'block');
        jQuery('.date_time_error').css('color', 'red');
        jQuery('.date_time_error').html(errorobj_please_select_appointment_date);
        jQuery(this).attr("href", '#date_time_error_id');
        return false;
      }
      else{
        jQuery("#ct-step-three").slideUp(300);
      jQuery("#ct-step-four").slideDown(300);
      }
      
    } else if (jQuery("#ct-step-four").is(":visible")) {
      
      jQuery(".login_unsuccessfull").hide();
      jQuery(".login_unsuccessfull").html("");
      if (jQuery(".ct-login-existing").css('display') == 'block') {
        jQuery("#user_login_form").validate({
          rules: {
            ct_user_name: {
              required: true,
            },
            ct_user_pass: {
              required: true,
            }
          },
          messages: {
            ct_user_name: {
              required: "The email is required field",
            },
            ct_user_pass: {
              required: "The password is required field",
            }
          }
        });

        if (jQuery("#user_login_form").valid()) {
          jQuery(".login_unsuccessfull").hide();
          jQuery(".login_unsuccessfull").html("");
         
          if (jQuery("#user_details_form").valid() && is_login_user == "Y") {
            jQuery("#app-street-address").rules("add", {
              required: true,
              minlength: check_addresss.min,
              maxlength: check_addresss.max,
              messages: {
                required: errorobj_req_sa,
                minlength: errorobj_min_sa,
                maxlength: errorobj_max_sa
              }
            });

            jQuery("#app-zip-code").rules("add", {
              required: true,
              minlength: check_zip_code.min,
              maxlength: check_zip_code.max,
              messages: {
                required: errorobj_req_zc,
                minlength: errorobj_min_zc,
                maxlength: errorobj_max_zc
              }
            });

            jQuery("#app-city").rules("add", {
              required: true,
              minlength: check_city.min,
              maxlength: check_city.max,
              messages: {
                required: errorobj_req_ct,
                minlength: errorobj_min_ct,
                maxlength: errorobj_max_ct
              }
            });

            jQuery("#app-state").rules("add", {
              required: true,
              minlength: check_state.min,
              maxlength: check_state.max,
              messages: {
                required: errorobj_req_st,
                minlength: errorobj_min_st,
                maxlength: errorobj_max_st
              }
            });

            jQuery("#ct-first-name").rules("add", {
              required: true,
              messages: {
                required: errorobj_req_fn
              }
            });

            jQuery("#ct-last-name").rules("add", {
              required: true,
              messages: {
                required: errorobj_req_ln
              }
            });

            jQuery("#ct-street-address").rules("add", {
              required: true,
              messages: {
                required: errorobj_req_sa
              }
            });

            jQuery("#ct-zip-code").rules("add", {
              required: true,
              messages: {
                required: errorobj_req_zc
              }
            });

            jQuery("#ct-city").rules("add", {
              required: true,
              messages: {
                required: errorobj_req_ct
              }
            });

            jQuery("#ct-state").rules("add", {
              required: true,
              messages: {
                required: errorobj_req_st
              }
            });

            jQuery("#ct-notes").rules("add", {
              required: true,
              messages: {
                required: errorobj_req_srn
              }
            });

            jQuery(".login_unsuccessfull").hide();
            jQuery(".login_unsuccessfull").html("");
            jQuery("#ct-step-four").slideUp(300);
            jQuery("#ct-step-five").slideDown(300);
            jQuery("#step_next").hide();

          } else if (is_login_user != "Y") {
            jQuery(".login_unsuccessfull").show();
            jQuery(".login_unsuccessfull").html("<span style='color:red;'>Please do login</span>");
            
          }
        }
      } else {
        if (jQuery("#user_details_form").valid()) {
          jQuery(".login_unsuccessfull").hide();
          jQuery(".login_unsuccessfull").html("");
          jQuery("#ct-step-four").slideUp(300);
          jQuery("#ct-step-five").slideDown(300);
          jQuery("#step_next").hide();
          jQuery("#app-street-address").rules("add", {
            required: true,
            minlength: check_addresss.min,
            maxlength: check_addresss.max,
            messages: {
              required: errorobj_req_sa,
              minlength: errorobj_min_sa,
              maxlength: errorobj_max_sa
            }
          });

          jQuery("#app-zip-code").rules("add", {
            required: true,
            minlength: check_zip_code.min,
            maxlength: check_zip_code.max,
            messages: {
              required: errorobj_req_zc,
              minlength: errorobj_min_zc,
              maxlength: errorobj_max_zc
            }
          });

          jQuery("#app-city").rules("add", {
            required: true,
            minlength: check_city.min,
            maxlength: check_city.max,
            messages: {
              required: errorobj_req_ct,
              minlength: errorobj_min_ct,
              maxlength: errorobj_max_ct
            }
          });

          jQuery("#app-state").rules("add", {
            required: true,
            minlength: check_state.min,
            maxlength: check_state.max,
            messages: {
              required: errorobj_req_st,
              minlength: errorobj_min_st,
              maxlength: errorobj_max_st
            }
          });

          jQuery("#ct-first-name").rules("add", {
            required: true,
            messages: {
              required: errorobj_req_fn
            }
          });

          jQuery("#ct-last-name").rules("add", {
            required: true,
            messages: {
              required: errorobj_req_ln
            }
          });

          jQuery("#ct-street-address").rules("add", {
            required: true,
            messages: {
              required: errorobj_req_sa
            }
          });

          jQuery("#ct-zip-code").rules("add", {
            required: true,
            messages: {
              required: errorobj_req_zc
            }
          });

          jQuery("#ct-city").rules("add", {
            required: true,
            messages: {
              required: errorobj_req_ct
            }
          });

          jQuery("#ct-state").rules("add", {
            required: true,
            messages: {
              required: errorobj_req_st
            }
          });

          jQuery("#ct-notes").rules("add", {
            required: true,
            messages: {
              required: errorobj_req_srn
            }
          });

          jQuery("#ct-step-four").slideUp(300);
          jQuery("#ct-step-five").slideDown(300);
          jQuery("#step_next").hide();
        }
      }
    }
    jQuery(".add_show_error_class").each(function () {
      jQuery(this).trigger("keyup");
    });
  });

  jQuery(document).on("click", "#step_previous", function () {
    jQuery(".custom_service_error").hide();
    if (jQuery("#ct-step-one").is(":visible")) {
      jQuery("#step_previous").hide();
      jQuery("#step_next").show();
    }
    else if (jQuery("#ct-step-two").is(":visible")) {
      jQuery("#ct-step-two").slideUp(300);
      jQuery("#ct-step-one").slideDown(300);
      jQuery("#step_previous").hide();
    }
    else if (jQuery("#ct-step-three").is(":visible")) {
      var staff_provider_status = jQuery("#staff_provider_status").val();
      var staff_count_forservice = jQuery("#staff_count_forservice").val();
      if (frequency_status.status == 'N' && staff_count_forservice == '0') {
        if (staff_provider_status == 'Y') {
          jQuery("#ct-step-two").slideDown(300);
          jQuery("#ct-step-three").slideUp(300);
        } else {
          jQuery("#ct-step-one").slideDown(300);
          jQuery("#ct-step-three").slideUp(300);
        }
      } else {
        jQuery("#ct-step-two").slideDown(300);
        jQuery("#ct-step-three").slideUp(300);
      }

    }

    else if (jQuery("#ct-step-four").is(":visible")) {
      jQuery("#ct-step-three").slideDown(300);
      jQuery("#ct-step-four").slideUp(300);
    }

    else if (jQuery("#ct-step-five").is(":visible")) {
      jQuery("#ct-step-four").slideDown(300);
      jQuery("#ct-step-five").slideUp(300);
      jQuery("#step_next").show();
    }
  });
});


/* Send OTP function Jay W */
jQuery(document).on("click", "#send_otp", function () {
  var ajax_url = ajaxurlObj.ajax_url;
  var phoneno = jQuery("#ct-user-phone").val();
  jQuery(".ct-loading-main").show();
  jQuery.ajax({
    type: "post",
    data: {
      "phoneno": phoneno,
      "sendotp": 1
    },
    url: ajax_url + "staff_ajax.php",
    success: function (response) {
      jQuery(".ct-loading-main").hide();
      var staff_phone = response;
      jQuery("#phone_number").val(staff_phone);
      jQuery("#verify_otp").modal('show');
    }
  });
});
/* Send OTP function end Jay W */

/* Send OTP check Jay W */
jQuery(document).on("click", "#input_otp_check", function () {
  var ajax_url = ajaxurlObj.ajax_url;
  var otp_input = jQuery("#otp_input").val();
  var phone = jQuery(".phone_number").val();
  jQuery(".ct-loading-main").show();
  jQuery.ajax({
    type: "POST",
    data: {
      "otp_input": otp_input,
      "phone": phone,
      "Verify_otp": 1
    },
    url: ajax_url + "staff_ajax.php",
    success: function (res) {
      jQuery(".ct-loading-main").hide();
      if (res == 1) {
        jQuery('#verify_otp').modal('hide');
        jQuery('#send_otp').hide();
        jQuery('#admin_staff_phno').attr('readonly', true);
        jQuery(".checkmark").show();
        jQuery('#new_staff_add_admin').prop('disabled', false);
      } else {
        jQuery("#invalid-otp").css("display", "inline");
        jQuery("#invalid-otp").val("Invalid OTP");
        jQuery("#invalid-otp").css("color", "red");
      }
    }
  });
});
/* Send OTP check end Jay W */

jQuery(document).ready(function () {
  //called when key is pressed in textbox
  jQuery("#ct-user-phone").keypress(function (e) {
    //if the letter is not digit then display error and don't type anything
    if (e.which != 8 && e.which != 0 && (e.which < 48 || e.which > 57)) {
      //display error message
      jQuery("#errmsg").html("Digits Only").show().fadeOut("slow");
      return false;
    }
  });
});

jQuery(document).ready(function () {

  jQuery(".cart_toggle_content").hide();
});
jQuery(document).on('click', ".cart_toggle_head", function () {
  var id = jQuery(this).data('id');

  jQuery('.cart_toggle_head').each(function () {
    var nID = jQuery(this).data('id');
    if (nID == id) {
      if (jQuery('.ct_icon_check_' + nID).hasClass('fa-plus')) {
        jQuery('.ct_icon_check_' + nID).removeClass('fa-plus');
        jQuery('.ct_icon_check_' + nID).addClass('fa-minus');
        jQuery("#cart_toggle_content_" + nID).slideToggle();
        /* alert(nID+"show"); */
      }
      else {
        jQuery('.ct_icon_check_' + nID).addClass('fa-plus');
        jQuery('.ct_icon_check_' + nID).removeClass('fa-minus');
        jQuery("#cart_toggle_content_" + nID).slideUp();
        /* alert(nID+"hide"); */
      }
    } else {
      jQuery('.ct_icon_check_' + nID).addClass('fa-plus');
      jQuery('.ct_icon_check_' + nID).removeClass('fa-minus');
      jQuery("#cart_toggle_content_" + nID).slideUp();
    }
  });
});

jQuery(document).on('change', ".payment_gateway", function () {
  var payment_gateway_checkk = jQuery("input[name='payment-methods']:checked").prop("checked");
  if (payment_gateway_checkk == true) {
    jQuery("#payment-method-error").hide();
  } else {
    jQuery("#payment-method-error").show();
  }
});
/* jQuery(".ct-postal-input").on("keyup", function () {
  var ct_member_zip = jQuery("#ct-member-zip").val();
  var ct_postal_input = jQuery(".ct-postal-input").val();

  if(ct_member_zip == ct_postal_input){
    jQuery(".ct-provider-list").show();
  }else{
    jQuery(".ct-provider-list").hide();
  }
}); */

jQuery(document).on("keyup", "#ct_allow_user_tip", function () {

  jQuery(".freq_disc_empty_cart_error").hide();

  var site_url = siteurlObj.site_url;

  var ajax_url = ajaxurlObj.ajax_url;

  var tip_value = jQuery(this).val();
  var currency_symbol = jQuery(this).attr("data-currency_symbol");

  if (tip_value !== "") {
      var formatted_tip_value = currency_symbol + tip_value;
      jQuery('.cart_tip').html(formatted_tip_value);
  } else {
      jQuery('.cart_tip').empty();
  }
  if (tip_value == "") { tip_value = 0; }

  var Number = /^[0-9]*$/;

  var subtotal = jQuery(".cart_sub_total").text();

  var frequently_discount_id = jQuery("input[name=frequently_discount_radio]:checked").attr("data-id");

  if (subtotal == "") {

    jQuery(".tip_invalid_error").css("display", "block");

    jQuery(".tip_invalid_error").html(errorobj_your_cart_is_empty_please_add_cleaning_services);

  } else {

    if (!jQuery(this).val().match(Number)) {

      jQuery(".tip_invalid_error").css("display", "block");

      jQuery(".tip_invalid_error").html("Enter a valid number");

    } else {

      jQuery.ajax({

        type: "POST",

        url: site_url + "front/firststep.php",

        data: { "tip_value": tip_value, "frequently_discount_id": frequently_discount_id, "add_tip_value": 1 },

        success: function (res) {

          jQuery(".fa-lgs").hide();

          jQuery(".tip_invalid_error").css("display", "none");

          var cart_session_data = jQuery.parseJSON(res);

          jQuery(".freq_discount_display").show();

          jQuery(".partial_amount").html(cart_session_data.partial_amount);

          jQuery(".remain_amount").html(cart_session_data.remain_amount);

          jQuery(".cart_sub_total").html(cart_session_data.cart_sub_total);

          jQuery(".frequent_discount").html(cart_session_data.frequent_discount);

          jQuery(".cart_tax").html(cart_session_data.cart_tax);

          jQuery(".cart_special_days").html(cart_session_data.cart_special_days);

          jQuery(".cart_total").html(cart_session_data.total_amount);

          jQuery(".cart_discount").html("- " + cart_session_data.cart_discount);

        }

      });

    }

  }

});

function showPopup() {
  jQuery('#ct-card-payment-error').show();
  jQuery(".ct-payment-error").show();
}

jQuery(document).on('click', ".close-btn", function (e) {
  e.preventDefault();
  jQuery('#ct-card-payment-error').hide();
});

function showPaypalpop() {
  jQuery('#paypal_error').show();
  jQuery(".ct-paypal-error").show();
}

jQuery(document).on('click', ".close-btns", function (e) {
  e.preventDefault();
  jQuery('#paypal_error').hide();
});

//Today report(15/07/2024)
// -> project name - cleanto

// - C14 - cart is empty why we show inventory name.(its required blank.).
// - Booking Notifications model not show date with a title.
// - C06 - image not show fix the problem.
// - C12 - in addons click on one drop down option second one automatic open.(in addons one addons open second one by default close functionality required. ) 
// - C07 - edit book in drop down inventory name not show.
// - C17 - in manual booking inventory title not show.



