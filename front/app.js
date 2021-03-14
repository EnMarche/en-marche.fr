import './style/app.scss';

import Container from './services/Container';
import registerServices from './services';

// Listeners
import amountChooser from './listeners/amount-chooser';
import donationBanner from './listeners/donation-banner';
import progressiveBackground from './listeners/progressive-background';
import externalLinks from './listeners/external-links';
import noJsRecaptcha from './listeners/no-js-recaptcha';
import alogliaSearch from './listeners/algolia-search';
import confirmModal from './listeners/confirm-modal';
import emModal from './listeners/em-modal';
import emDateTimePicker from './listeners/em-datetime-picker';
import AutocompletedAddressForm from './services/address/AutocompletedAddressForm';
import AddressObject from './services/address/AddressObject';

class App {
    constructor() {
        this._di = null;
        this._listeners = [
            donationBanner,
            amountChooser,
            progressiveBackground,
            externalLinks,
            noJsRecaptcha,
            alogliaSearch,
            confirmModal,
            emModal,
            emDateTimePicker,
        ];
    }

    addListener(listener) {
        this._listeners.push(listener);
    }

    run(parameters) {
        this._di = new Container(parameters);

        // Register the services
        registerServices(this._di);

        // Execute the page load listeners
        this._listeners.forEach((listener) => {
            listener(this._di);
        });
    }

    get(key) {
        return this._di.get(key);
    }

    share(type, url, title) {
        this._di.get('sharer').share(type, url, title);
    }

    createAddressSelector(country, postalCode, city, cityName, cityNameRequired, region = null) {
        const formFactory = this._di.get('address.form_factory');
        const form = formFactory.createAddressForm(country, postalCode, city, cityName, cityNameRequired, region);

        form.prepare();
        form.refresh();
        form.attachEvents();
    }

    createAutocompleteAddress(
        addressFieldSelector,
        zipCodeFieldSelector,
        cityNameFieldSelector,
        regionFieldSelector,
        countryFieldSelector,
        autocompleteWrapperSelector = '.address-autocomplete',
        addressBlockWrapperSelector = '.address-block',
        helpMessageContainer = '#address-autocomplete-help-message',
        showOnlyAutocomplete = false
    ) {
        const addressObject = new AddressObject(
            dom(addressFieldSelector),
            dom(zipCodeFieldSelector),
            dom(cityNameFieldSelector),
            dom(regionFieldSelector),
            dom(countryFieldSelector)
        );

        const autocompleteAddressForm = new AutocompletedAddressForm(
            dom(autocompleteWrapperSelector),
            dom(addressBlockWrapperSelector),
            addressObject,
            dom(helpMessageContainer),
            false,
            showOnlyAutocomplete
        );

        autocompleteAddressForm.buildWidget();
    }

    createVoteLocationSelector(country, postalCode, city, cityName, office) {
        const formFactory = this._di.get('vote_location.form_factory');
        const form = formFactory.createVoteLocationForm(country, postalCode, city, cityName, office);

        form.prepare();
        form.refresh();
        form.attachEvents();
    }

    startDateFieldsSynchronisation(referenceDateFieldName, targetDateFieldName) {
        this._di.get('form.date_synchronizer').sync(referenceDateFieldName, targetDateFieldName);
    }

    runDonation() {
        System.import('pages/donation').catch((error) => { throw error; }).then((module) => {
            module.default('.donation-widget-wrapper');
        });
    }

    runProgrammaticFoundation() {
        System.import('pages/programmatic_foundation').catch((error) => { throw error; }).then((module) => {
            module.default('.programmatic-foundation-widget-wrapper', this.get('api'));
        });
    }

    runDonationInformation(formType) {
        System.import('pages/donation_information').catch((error) => { throw error; }).then((module) => {
            module.default(formType);
        });
    }

    runReport() {
        System.import('pages/report').catch((error) => { throw error; }).then((module) => {
            module.default();
        });
    }

    runCommitteesMap() {
        System.import('pages/committees_map').catch((error) => { throw error; }).then((module) => {
            module.default(this.get('map_factory'), this.get('api'));
        });
    }

    runEventsMap() {
        System.import('pages/events_map').catch((error) => { throw error; }).then((module) => {
            module.default(this.get('map_factory'), this.get('api'));
        });
    }

    runManagedList(columns, items) {
        System.import('pages/managed_list').catch((error) => { throw error; }).then((module) => {
            module.default(this.get('slugifier'), columns, items);
        });
    }

    runJeMarche() {
        System.import('pages/jemarche').catch((error) => { throw error; }).then((module) => {
            module.default();
        });
    }

    runRegistration(formType) {
        System.import('pages/registration').catch((error) => { throw error; }).then((module) => {
            module.default(formType);
        });
    }

    runJoin(formType) {
        System.import('pages/join').catch((error) => { throw error; }).then((module) => {
            module.default(formType);
        });
    }

    runComplete() {
        System.import('pages/complete').catch((error) => { throw error; }).then((module) => {
            module.default();
        });
    }

    runCopyToClipboard() {
        System.import('pages/copy_to_clipboard').catch((error) => { throw error; }).then((module) => {
            module.default();
        });
    }

    runProcurationManagerRequests(queryString, totalCount, perPage) {
        System.import('pages/procuration_manager_requests').catch((error) => { throw error; }).then((module) => {
            module.default(queryString, totalCount, perPage, this.get('api'));
        });
    }

    runProcurationManagerProposals(queryString, totalCount, perPage) {
        System.import('pages/procuration_manager_proposals').catch((error) => { throw error; }).then((module) => {
            module.default(queryString, totalCount, perPage, this.get('api'));
        });
    }

    runSocialShare(urlAll, urlCategory) {
        System.import('pages/social_share').catch((error) => { throw error; }).then((module) => {
            module.default(urlAll, urlCategory);
        });
    }

    runFacebookPictureChooser(urls) {
        System.import('pages/facebook_pictures').catch((error) => { throw error; }).then((module) => {
            module.default(urls, this.get('api'));
        });
    }

    runLegislativesCandidatesList() {
        System.import('pages/candidates_list').catch((error) => { throw error; }).then((module) => {
            module.default();
        });
    }

    runLegislativesCandidatesMap() {
        System.import('pages/candidates_map').catch((error) => { throw error; }).then((module) => {
            module.default(this.get('map_factory'), this.get('api'));
        });
    }

    runReferentsList() {
        System.import('pages/referents_list').catch((error) => { throw error; }).then((module) => {
            module.default();
        });
    }

    runBoardMember() {
        System.import('pages/board_member_list').catch((error) => { throw error; }).then((module) => {
            module.default(this.get('api'));
        });
    }

    runCitizenProjectImageGenerator() {
        System.import('pages/citizen_project_image_generator').catch((error) => { throw error; }).then((module) => {
            module.default();
            module.previewHandler();
        });
    }

    runManageParticipants() {
        System.import('pages/manage_participants').catch((error) => { throw error; }).then((module) => {
            module.default();
        });
    }

    runManageUserSegment(segmentType, wrapperSelector, checkboxSelector, countMembers) {
        System.import('pages/manage_user_segment').catch((error) => { throw error; }).then((module) => {
            module.default(segmentType, wrapperSelector, checkboxSelector, this.get('api'), countMembers);
        });
    }

    runUserListDefinitions(memberType, type, wrapperSelector, checkboxSelector, mainCheckboxSelector,
        postApplyCallback) {
        System.import('pages/user_list_definition').catch((error) => { throw error; }).then((module) => {
            module.default(
                memberType,
                type,
                wrapperSelector,
                checkboxSelector,
                mainCheckboxSelector,
                this.get('api'),
                postApplyCallback
            );
        });
    }

    runGrandeMarcheEurope() {
        System.import('pages/grande_marche_europe').catch((error) => { throw error; }).then((module) => {
            module.default();
        });
    }

    runMessageFilters(messageId, synchronized, recipientCount, sendLocked) {
        System.import('pages/message_filters').catch((error) => { throw error; }).then((module) => {
            module.default(this.get('api'), messageId, synchronized, recipientCount, sendLocked);
        });
    }

    runProcurationProxy(countryFieldSelector, postalCodeFieldSelector, stateFieldSelector) {
        System.import('pages/procuration_proxy').catch((error) => { throw error; }).then((module) => {
            module.default(countryFieldSelector, postalCodeFieldSelector, stateFieldSelector);
        });
    }

    runProcurationRequest(countryFieldSelector, postalCodeFieldSelector, stateFieldSelector) {
        System.import('pages/procuration_request').catch((error) => { throw error; }).then((module) => {
            module.default(countryFieldSelector, postalCodeFieldSelector, stateFieldSelector);
        });
    }

    runAssessorManagerRequests(queryString, totalCount, perPage) {
        System.import('pages/assessor_manager_requests').catch((error) => { throw error; }).then((module) => {
            module.default(queryString, totalCount, perPage, this.get('api'));
        });
    }

    runAssessorManagerVotePlaces(queryString, totalCount, perPage) {
        System.import('pages/assessor_manager_vote_places').catch((error) => { throw error; }).then((module) => {
            module.default(queryString, totalCount, perPage, this.get('api'));
        });
    }

    runMessageList(buttonClass) {
        System.import('pages/message_list').catch((error) => { throw error; }).then((module) => {
            module.default(buttonClass, this.get('api'));
        });
    }

    runApplicationRequest(formToDisplay) {
        System.import('pages/application_request').catch((error) => { throw error; }).then((module) => {
            module.default(formToDisplay);
        });
    }

    createCKEditor(elementSelector, uploadUrl) {
        System.import('services/form/CKEditor').catch((error) => { throw error; }).then((module) => {
            module.default(elementSelector, uploadUrl, { removePlugins: ['MediaEmbed'] });
        });
    }

    runReferentUserList(committeeModalButtonClass) {
        System.import('pages/referent_user_list').catch((error) => { throw error; }).then((module) => {
            module.default(committeeModalButtonClass, this.get('api'));
        });
    }

    runMyActivityCommitteeList(switchSelector) {
        System.import('pages/my_activity_committees').catch((error) => { throw error; }).then((module) => {
            module.default(switchSelector, this.get('api'));
        });
    }

    runCandidacyModal(triggerSelector) {
        System.import('pages/committee_candidacies').catch((error) => { throw error; }).then((module) => {
            module.default(triggerSelector, this.get('api'));
        });
    }

    runProfileUpdatePage() {
        System.import('pages/profile_update').catch((error) => { throw error; }).then((module) => {
            module.default();
        });
    }

    runImageCropper(inputFileElement, inputCroppedImageElement) {
        System.import('services/utils/imageCropper').catch((error) => { throw error; }).then((module) => {
            module.default(inputFileElement, inputCroppedImageElement);
        });
    }

    runCountdownClock(clockSelector, refreshPage = false) {
        System.import('services/utils/countdownClock').catch((error) => { throw error; }).then((module) => {
            module.default(clockSelector, refreshPage);
        });
    }

    runTerritorialCouncilCandidacy(qualityFieldSelector, submitButtonSelector, wrapperSelector) {
        System.import('pages/territorial_council_candidacy').catch((error) => { throw error; }).then((module) => {
            module.default(this.get('api'), qualityFieldSelector, submitButtonSelector, wrapperSelector);
        });
    }

    runNationalCouncilCandidacy(qualityFieldSelector, submitButtonSelector, wrapperSelector, messages, availableGenders, neededQualities, invitations) {
        System.import('pages/national_council_candidacy').catch((error) => { throw error; }).then((module) => {
            module.default(this.get('api'), qualityFieldSelector, submitButtonSelector, wrapperSelector, messages, availableGenders, neededQualities, invitations);
        });
    }

    runCommitteeCandidacy(slug, membershipFieldSelector, submitButtonSelector, wrapperSelector) {
        System.import('pages/committee_candidacy').catch((error) => { throw error; }).then((module) => {
            module.default(this.get('api'), slug, membershipFieldSelector, submitButtonSelector, wrapperSelector);
        });
    }
}

window.App = new App();
