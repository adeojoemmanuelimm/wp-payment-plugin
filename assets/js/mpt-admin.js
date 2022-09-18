if(wc_flutterwave_admin_params.countSubaccount != ''){
    var countSubaccount = parseInt(wc_flutterwave_admin_params.countSubaccount);

}


jQuery( function( $ ) {
    'use strict';
    

if(countSubaccount == 4){
    jQuery('.woocommerce_flutterwave_subaccount_button').parents( 'tr' ).eq( 0 ).hide();
}
	/**
	 * Object to handle Flutterwave admin functions.
	 */
	var wc_mpt_admin = {
		/**
		 * Initialize.
		 */
		init: function() {

			$( document.body ).on( 'change', '.woocommerce_flutterwave_split_payment', function() {
				var subaccount_code = $( '.woocommerce_flutterwave_subaccount_code' ).parents( 'tr' ).eq( 0 );
				var subaccount_button = $( '.woocommerce_flutterwave_subaccount_button' ).parents( 'tr' ).eq( 0 );
				var subaccount_button_2 = $( '.woocommerce_flutterwave_subaccount_code_2' ).parents( 'tr' ).eq( 0 );
				var subaccount_button_3 = $( '.woocommerce_flutterwave_subaccount_code_3' ).parents( 'tr' ).eq( 0 );
				var subaccount_button_4 = $( '.woocommerce_flutterwave_subaccount_code_4' ).parents( 'tr' ).eq( 0 );

				if ( $( this ).is( ':checked' ) ) {

                    if(isNaN(countSubaccount) == false){
                        if(countSubaccount == 2){
                            subaccount_code.show();
                            subaccount_button_2.show();
                            }
        
                            if(countSubaccount == 3){
                                subaccount_code.show();
                                subaccount_button_2.show();
                                subaccount_button_3.show();
                            }
        
                            if(countSubaccount == 4){
                                jQuery('.woocommerce_flutterwave_subaccount_button').parents( 'tr' ).eq( 0 ).hide();
                                subaccount_code.show();
                                subaccount_button_2.show();
                                subaccount_button_3.show();
                                subaccount_button_4.show();
                                
                            }
                    }else{
                        subaccount_code.show();
                        subaccount_button.show();
                    }
                    

                    

				} else {
                    subaccount_code.hide();
                    subaccount_button.hide();
                    subaccount_button_2.hide();
                    subaccount_button_3.hide();
                    subaccount_button_4.hide();

				}
			} );

			$( '#woocommerce_flutterwave_split_payment' ).change();

            },
        
        removeSubaccount: function(id){

            $('.woocommerce_flutterwave_subaccount_code_'+id).hide();
            countSubaccount = countSubaccount - 1;

        }

    };
    
    

    wc_mpt_admin.init();

    
    
    jQuery('#woocommerce_payment_add_subaccount_id').on('click', function(){



        if(countSubaccount == 2 ){
            jQuery('#woocommerce_payment_subaccount_id').show();
            jQuery('#woocommerce_payment_subaccount_id_2').show();
        }

        if(countSubaccount == 3 ){
            jQuery('#woocommerce_payment_subaccount_id').show();
            jQuery('#woocommerce_payment_subaccount_id_2').show();
            jQuery('#woocommerce_payment_subaccount_id_3').show();
        }

        if(countSubaccount == 4 ){
            jQuery('#woocommerce_payment_subaccount_id').show();
            jQuery('#woocommerce_payment_subaccount_id_2').show();
            jQuery('#woocommerce_payment_subaccount_id_3').show();
            jQuery('#woocommerce_payment_subaccount_id_4').show();
            jQuery( this ).parents( 'tr' ).eq( 0 ).hide();
        }

        while (countSubaccount > 5) {
            
            countSubaccount = countSubaccount + 1;

        }
        
            
        $('#woocommerce_payment_subaccount_count_saved').val(countSubaccount);
        
    });

} );
