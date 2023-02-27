/**
 * Paradox Labs, Inc.
 * http://www.paradoxlabs.com
 * 717-431-3330
 * 
 * Need help? Open a ticket in our support system:
 *  http://support.paradoxlabs.com
 *
 * @category    ParadoxLabs
 * @package     AuthorizeNetCim
 * @author      Ryan Hoerr <support@paradoxlabs.com>
 * @license     http://store.paradoxlabs.com/license.html
 */

/**
 * Hook the given payment form onto Authorize.Net's Accept.js service.
 *
 * Accept.js swaps CC details with a nonce from Authorize.Net on-the-fly,
 * obviating the need to route that info through the web server.
 * 
 * This class provides a bunch of logic to make that happen.
 */

var pdlAcceptJs = Class.create();
pdlAcceptJs.prototype = {
    fields: [
        '_cc_type',
        '_cc_number',
        '_cc_exp_month',
        '_cc_exp_year',
        '_cc_cid'
    ],
    
    protectedFields: [
        '_cc_number'
    ],
    
    tmpHaveAllFields: null,
    tmpConcat: null,
    nonceConcat: null,
    timeout: null,
    
    /**
     * Initialize Accept.js interface
     */
    initialize : function(formSelector, apiLoginId, clientKey, method, submitSelector) {
        this.formSelector = formSelector;
        this.apiLoginId = apiLoginId;
        this.clientKey = clientKey;
        this.method = method;
        this.submitSelector = submitSelector;
        
        this.hasError = false;

        this.bind();
    },
    
    /**
     * Hook onto elements of the current page, if we can
     */
    bind : function() {
        if ($(this.formSelector)) {
            $(this.formSelector).select('input:not([type=hidden]), select').each(function(field) {
                if( typeof field != 'undefined' ) {
                    field.observe('input', this.onFieldChange.bind(this));
                    field.observe('keyup', this.onFieldChange.bind(this));
                    field.observe('change', this.onFieldChange.bind(this));
                }
            }.bind(this));
        }
    },
    
    /**
     * Check validity, request accept.js nonce if everything checks out
     */
    onFieldChange: function() {
        if( this.isValidForAcceptjs() ) {
            this.tmpHaveAllFields = true;
            
            this.fields.each(function(elemIndex) {
                var field = $(this.formSelector).down('#' + this.method + elemIndex);
                
                if( typeof field != 'undefined' ) {
                    // If we're missing a value or find a masked one, not valid for sending.
                    if( field.value.length < 1 || field.value.indexOf('XX') >= 0 ) {
                        this.tmpHaveAllFields = false;
                    }
                    else if( elemIndex == '_cc_cid' && field.value.length < 3 ) {
                        this.tmpHaveAllFields = false;
                    }
                }
            }.bind(this));
            
            // If all fields are filled in, the form validates, and something has changed, request a nonce.
            if( this.tmpHaveAllFields === true && this.validate() && this.concatFields() != this.nonceConcat ) {
                this.nonceConcat = this.tmpConcat;
                
                this.sendPaymentInfo();
                
                // Refresh periodically to avoid 15-minute token expiration, and try to play nice with checkout errors.
                if( this.timeout !== null ) {
                    clearTimeout( this.timeout );
                }
                
                this.timeout = setTimeout(
                    function() {
                        this.nonceConcat = null;
                        this.onFieldChange();
                    }.bind(this),
                    60000
                );
            }
        }
    },
    
    /**
     * Check whether Accept.js applies in the current situation
     */
    isValidForAcceptjs : function() {
        var form = $(this.formSelector);
        if( form && ( typeof payment == 'undefined' || typeof payment.currentMethod == 'undefined' || payment.currentMethod == this.method ) ) {
            if( form.down('#' + this.method + '_cc_number').value != ''/* && form.down('#' + this.method + '_acceptjs_value').value == ''*/ ) {
                return true;
            }
        }
        
        return false;
    },
    
    /**
     * Validate payment form fields before submit
     */
    validate : function() {
        var validator = new Validation($(this.formSelector), {focusOnError: false});
        if ( validator.validate() && ( typeof payment == 'undefined' || typeof payment.validate == 'undefined' || payment.validate() != false ) ) {
            return true;
        }
        else {
            return false;
        }
    },
    
    /**
     * Send payment info via Accept.js
     */
    sendPaymentInfo : function() {
        this.startLoadWaiting();
        
        var form = $(this.formSelector);
        var paymentData = {
            cardData: {
                cardNumber: form.down('#' + this.method + '_cc_number').value.replace(/\D/g,''),
                month: form.down('#' + this.method + '_cc_exp_month').value,
                year: form.down('#' + this.method + '_cc_exp_year').value,
                cardCode: ''
            },
            authData: {
                clientKey: this.clientKey,
                apiLoginID: this.apiLoginId
            }
        };
        
        if( typeof form.down('#' + this.method + '_cc_cid') != 'undefined' ) {
            paymentData['cardData']['cardCode'] = form.down('#' + this.method + '_cc_cid').value;
        }
        
        Accept.dispatchData(
            paymentData,
            'acceptJs_' + this.method + '_callback'
        );
    },
    
    /**
     * Handle Accept.js response
     */
    handlePaymentResponse : function(response) {
        if (response.messages.resultCode === 'Error') {
            this.hasError = true;
            
            var messages = '';
            for (var i = 0; i < response.messages.message.length; i++) {
                if (i > 0) {
                    messages += "\n";
                }
                
                if (typeof Translator != 'undefined') {
                    messages += Translator.translate( response.messages.message[i].text + ' (' + response.messages.message[i].code + ')' );
                }
                else {
                    messages += response.messages.message[i].text + ' (' + response.messages.message[i].code + ')';
                }
            }
            
            // Unset data
            $(this.formSelector).down('#' + this.method + '_acceptjs_key').value = '';
            $(this.formSelector).down('#' + this.method + '_acceptjs_value').value = '';
            $(this.formSelector).down('#' + this.method + '_cc_last4').value = '';
            
            this.stopLoadWaiting(messages);
        }
        else {
            var cc_no = $(this.formSelector).down('#' + this.method + '_cc_number').value;
            
            // Set data
            $(this.formSelector).down('#' + this.method + '_acceptjs_key').value = response.opaqueData.dataDescriptor;
            $(this.formSelector).down('#' + this.method + '_acceptjs_value').value = response.opaqueData.dataValue;
            $(this.formSelector).down('#' + this.method + '_cc_last4').value = cc_no.substring( cc_no.length - 4 );
            
            // Remove fields from request
            this.protectedFields.each(function(elemIndex) {
                if( typeof $(this.formSelector).down('#' + this.method + elemIndex) != 'undefined' ) {
                    $(this.formSelector).down('#' + this.method + elemIndex).name = '';
                }
            }.bind(this));
            
            this.stopLoadWaiting(false);
        }
    },
    
    /**
     * Show the spinner effect on the CC fields while loading.
     */
    startLoadWaiting : function() {
        try {
            // Field opacity
            this.fields.each(function(elemIndex) {
                var field = $(this.formSelector).down('#' + this.method + elemIndex);
                
                if( typeof field != 'undefined' ) {
                    $(field).up('li, tr').setStyle({
                        opacity: 0.5
                    });
                }
            }.bind(this));
            
            // Spinner / messages
            $(this.formSelector).down('#' + this.method + '_processing').show();
            $(this.formSelector).down('#' + this.method + '_complete').hide();
            $(this.formSelector).down('#' + this.method + '_failed').hide();
            $(this.formSelector).down('#' + this.method + '_failed').down('.error-text').update('');
            
            // Button disable
            if( this.submitSelector && $$(this.submitSelector).length > 0 ) {
                $$(this.submitSelector).each(function(el) {
                    el.disabled = true;
                    el.addClassName('disabled');
                });
            }
        } catch(error) {
            // do nothing on load-waiting error.
        }
    },
    
    /**
     * Remove the spinner effect on the CC fields.
     */
    stopLoadWaiting : function(error) {
        try {
            // Field opacity
            this.fields.each(function(elemIndex) {
                var field = $(this.formSelector).down('#' + this.method + elemIndex);
                
                if( typeof field != 'undefined' ) {
                    $(field).up('li, tr').setStyle({
                        opacity: 1
                    });
                }
            }.bind(this));
            
            // Spinner / messages
            $(this.formSelector).down('#' + this.method + '_processing').hide();
            
            if( error == false ) {
                $(this.formSelector).down('#' + this.method + '_complete').show().setStyle({opacity: 1});
            }
            else {
                $(this.formSelector).down('#' + this.method + '_failed').down('.error-text').update(error);
                $(this.formSelector).down('#' + this.method + '_failed').show().setStyle({opacity: 1});
            }
            
            setTimeout(
                function() {
                    $(this.formSelector).down('#' + this.method + '_complete').fade({duration: 0.3});
                }.bind(this),
                1500
            );
            
            // Button disable
            if( this.submitSelector && $$(this.submitSelector).length > 0 ) {
                $$(this.submitSelector).each(function(el) {
                    el.disabled = false;
                    el.removeClassName('disabled');
                });
            }
        } catch(error) {
            // do nothing on load-waiting error.
        }
    },
    
    /**
     * Combine all fields into a single string for quick comparison purposes.
     */
    concatFields : function() {
        this.tmpConcat = '';
        
        this.fields.each(function(elemIndex) {
            var field = $(this.formSelector).down('#' + this.method + elemIndex);
            
            if( typeof field != 'undefined' && field.value.length > 0 ) {
                this.tmpConcat += field.value;
            }
        }.bind(this));
        
        return this.tmpConcat;
    }

};
