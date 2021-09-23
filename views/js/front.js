$(document).on('keyup','.qty-input',function(e){
    if ($(this).val() == "") {
        updateProductQuantityCustom($(this).closest(".add-to-cart-or-refresh"), 1)
    } else {
        if (isNaN($(this).val())) {
            updateProductQuantityCustom($(this).closest(".add-to-cart-or-refresh"), 1)
        } else {
            updateProductQuantityCustom($(this).closest(".add-to-cart-or-refresh"), parseInt($(this).val()))
        }
    }
});

function updateProductQuantityCustom(selector, number) {
    let sele = selector.find($('input[name ="qty"]'));
    sele.val(number);
}