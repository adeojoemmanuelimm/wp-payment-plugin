/* global location mpt_payment_args jQuery*/
"use strict";

var amount = mpt_payment_args.amount,
  cbUrl = mpt_payment_args.cb_url,
  country = mpt_payment_args.country,
  curr = mpt_payment_args.currency,
  desc = mpt_payment_args.desc,
  email = mpt_payment_args.email,
  firstname = mpt_payment_args.firstname,
  lastname = mpt_payment_args.lastname,
  form = jQuery("#mpt-pay-now-button"),
  p_key = mpt_payment_args.p_key,
  txref = mpt_payment_args.txnref,
  paymentOptions = mpt_payment_args.payment_options,
  paymentStyle = mpt_payment_args.payment_style,
  disableBarter = mpt_payment_args.barter,
  redirect_url;

if (form) {
  form.on("click", function (evt) {
    evt.preventDefault();
    if (paymentStyle == "inline") {
      processPayment();
    } else {
      location.href = mpt_payment_args.cb_url;
    }
  });
}

//switch country base on currency
switch (curr) {
  case "KES":
    country = "KE";
    break;
  case "GHS":
    country = "GH";
    break;
  case "ZAR":
    country = "ZA";
    break;
  case "TZS":
    country = "TZ";
    break;

  default:
    country = "NG";
    break;
}

var processPayment = function () {
  // setup payload
  var paymentPayload = {
    amount: amount,
    country: country,
    currency: curr,
    custom_description: desc,
    customer_email: email,
    customer_firstname: firstname,
    customer_lastname: lastname,
    txref: txref,
    payment_options: paymentOptions,
    PBFPubKey: p_key,
    onclose: function () {},
    callback: function (response) {
      if (
        response.tx.chargeResponseCode == "00" ||
        response.tx.chargeResponseCode == "0"
      ) {
        // popup.close();
        redirectPost(cbUrl + "?txref=" + response.data.data.txRef, response.tx);
      } else {
        alert(response.respmsg);
      }

      popup.close(); // close modal
    },
  };

  // disable barter or not
  if (disableBarter == "yes") {
    paymentPayload.disable_pwb = true;
  }

  // add payload
  var popup = getpaidSetup(paymentPayload);
};

var sendPaymentRequestResponse = function (res) {
  jQuery.post(cbUrl, res.tx).success(function (data) {
    var response = JSON.parse(data);
    redirect_url = response.redirect_url;
    setTimeout(redirectTo, 5000, redirect_url);
  });
};

//redirect function
var redirectPost = function (location, args) {
  // console.log(args);
  var form = "";
  jQuery.each(args, function (key, value) {
    // value = value.split('"').join('\"')
    form += '<input type="hidden" name="' + key + '" value="' + value + '">';
  });
  jQuery('<form action="' + location + '" method="POST">' + form + "</form>")
    .appendTo(jQuery(document.body))
    .submit();
};

var redirectTo = function (url) {
  location.href = url;
};
