/*
 * MODULE Psblog
 *
 * @author    Appside
 * @copyright Copyright (c) permanent, Appside
 * @license   Addons PrestaShop license
 */

function initAccessoriesAutocomplete() {
    $('#product_autocomplete_input')
        .autocomplete(ajax_product_link, {
            minChars: 1,
            autoFill: true,
            max: 20,
            matchContains: true,
            mustMatch: false,
            scroll: false,
            cacheLength: 0,
            formatItem: function (item) {
                return item[1] + ' - ' + item[0];
            }
        }).result(addAccessory);

    $('#product_autocomplete_input').setOptions({
        extraParams: {
            excludeIds: getAccessoriesIds()
        }
    });
}

function getAccessoriesIds() {
    if ($('#inputAccessories').val() === undefined) return '';
    ids = $('#inputAccessories').val().replace(/\-/g, ',');
    return ids;
}

function addAccessory(event, data, formatted) {
    if (data == null)
        return false;
    var productId = data[1];
    var productName = data[0];

    var $divAccessories = $('#divAccessories');
    var $inputAccessories = $('#inputAccessories');

    /* delete product from select + add product line to the div, input_name, input_ids elements */
    $divAccessories.html($divAccessories.html() + '<div class="form-control-static"><button type="button" class="delAccessory btn btn-default" name="' + productId + '"><i class="icon-remove text-danger"></i></button>&nbsp;' + productName + '</div>');
    $inputAccessories.val($inputAccessories.val() + productId + '-');
    $('#product_autocomplete_input').val('');
    $('#product_autocomplete_input').setOptions({
        extraParams: {excludeIds: getAccessoriesIds()}
    });
}

function delAccessory(currElement) {
    id = currElement.attr('name');
    var input = getE('inputAccessories');
    var inputCut = input.value.split('-');
    currElement.parent().remove();
    input.value = '';
    for (i in inputCut) {
        if (!inputCut[i]) continue;
        if (inputCut[i] != id) {
            input.value += inputCut[i] + '-';
        }
    }
}





