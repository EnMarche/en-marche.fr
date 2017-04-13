import VoteOfficeForm from './VoteOfficeForm';

export default class VoteOfficeFormFormFactory {
    constructor(api) {
        this._api = api;
    }

    createVoteOfficeForm(country, voteOffice) {
        return new VoteOfficeForm(
            this._api,
            dom('#'+country),
            dom('#'+voteOffice)
        );
    }
}
