import React from 'react';
import { storiesOf } from '@storybook/react';
import IdeaCard from '.';

const props = {
    author: {
        name: 'Jean-Michel Français',
    },
    author_category: 'QG',
    thumbnail: '/assets/img/icn_76px_education.svg',
    created_at: new Date().toISOString(),
    comments_count: 122,
    contributors_count: 4,
    description:
		'Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed molestie sapien eu orci congue sollicitudin. Sed malesuada nisi sed diam tristique, ullamcorper fermentum massa euismod. Vivamus a augue vitae nibh scelerisque…',
    theme: { name: 'Droits civiques' },
    category: { name: 'Education' },
    name:
		'Réduire le nombre d’élèves dans les classes dans les quartiers défavorisés',
    votes_count: {
        important: 10,
        feasible: 20,
        innovative: 30,
        total: 60,
        my_votes: ['feasible', 'important'],
    },
    days_before_deadline: 18,
    status: 'PENDING',
};

storiesOf('IdeaCard', module)
    .addParameters({ jest: ['IdeaCard'] })
    .add('default', () => <IdeaCard {...props} />);
