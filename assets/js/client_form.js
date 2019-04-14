const $ = require('jquery');

let $collectionContainer;

let $addAddressButton = $('<button type="button" class="btn btn-primary">Add an address</button>');
let $newLinkContainer = $('<div></div>').append($addAddressButton);

$(function() {
    $collectionContainer = $('#client_addresses');

    $collectionContainer.find('.client-address').each(function () {
        addAddressFormDeleteLink($(this));
    });

    $collectionContainer.append($newLinkContainer);

    $collectionContainer.data('index', $collectionContainer.find(':input').length);

    $addAddressButton.on('click', function(e) {
        addAddressForm($collectionContainer, $newLinkContainer);
    });
});

function addAddressForm($collectionContainer, $newLinkContainer) {
    let prototype = $collectionContainer.data('prototype');

    let index = $collectionContainer.data('index');

    let newForm = prototype;
    newForm = newForm.replace(/__name__/g, index);

    $collectionContainer.data('index', index + 1);

    let $newFormContainer = $('<div class="card card-body bg-light my-2 client-address"></div>').append(newForm);
    $newLinkContainer.before($newFormContainer);

    addAddressFormDeleteLink($newFormContainer);
}

function addAddressFormDeleteLink($addressFormContainer) {
    let $removeFormButton = $('<button class="btn btn-danger" type="button">Delete the address</button>');
    $addressFormContainer.append($removeFormButton);

    $removeFormButton.on('click', function(e) {
        $addressFormContainer.remove();
    });
}