import AutocompletedAddressForm from '../services/address/AutocompletedAddressForm';
import AddressObject from '../services/address/AddressObject';
import changeFieldsVisibility from '../services/form/changeFieldsVisibility';

export default (countryFieldSelector, postalCodeFieldSelector, stateFieldSelector) => {
    const countryElement = dom(countryFieldSelector);
    const autocompleteAddressForm = new AutocompletedAddressForm(
        dom('.address-autocomplete'),
        dom('.address-block'),
        dom('#address-autocomplete-help-message'),
        new AddressObject(
            dom('#app_procuration_request_address'),
            dom('#app_procuration_request_postalCode'),
            dom('#app_procuration_request_cityName'),
            null,
            dom('#app_procuration_request_country')
        )
    );

    autocompleteAddressForm.once('changed', () => {
        countryElement.dispatchEvent(new CustomEvent('change', {
            target: countryElement,
        }));
    });

    autocompleteAddressForm.buildWidget();

    changeFieldsVisibility(countryElement, postalCodeFieldSelector, stateFieldSelector);
};
