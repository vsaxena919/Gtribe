
/**
 * Function to reset auto end mic listening timeout
 *
 * @param this- DOMElement Object
 * @param evt - Event 
 */
function uvsResetTimeoutDefaultValue(el, evt) {
    if (typeof(el) == 'undefined') return;
    
    if (el.value.length == 0) {
        el.value = "8";
    } else if (parseInt(el.value) > 20) {
        el.value = "20";
    } else if (parseInt(el.value) < 8) {
        el.value = "8";
    }
}

/**
 * Function to validate length of timeout value
 *
 * @param this- DOMElement Object
 * @param evt - Event 
 */
function uvsValidateTimeoutValue(el, evt) {
    if (typeof(el) == 'undefined') return;

    if (el.value.length == 2 && parseInt(el.value) > 20) {
        evt.preventDefault();
    }
}