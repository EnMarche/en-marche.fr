import React from 'react';
import { render } from 'react-dom';

import HomeDonation from './controllers/HomeDonation';
import DonationIndexAmoutChooser from './controllers/DonationIndexAmoutChooser';
import DonationIndexAddress from './controllers/DonationIndexAddress';

import './style/app.scss';

export default class App {
    global() {
        let banner = document.getElementById('header-banner');

        if (banner) {
            document
                .getElementById('header-banner-close-btn')
                .addEventListener('click', () => {
                    banner.style.display = 'none';
                });
        }
    }

    home() {
        render(<HomeDonation />, document.getElementById('home-donation'));
    }

    donationIndex(donation, countries) {
        render(<DonationIndexAmoutChooser defaultAmount={donation.amount} />, document.getElementById('donation-amount'));
        render(<DonationIndexAddress countries={countries} defaultAddress={donation} />, document.getElementById('donation-address'));
    }
}
