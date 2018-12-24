import { createRequestTypes } from '../helpers/actions';

// modal
export const SHOW_MODAL = 'SHOW_MODAL';
export const HIDE_MODAL = 'HIDE_MODAL';

// ideas
export const FETCH_IDEAS = createRequestTypes('FETCH_IDEAS');
export const SET_IDEAS = 'SET_IDEAS';
export const ADD_IDEAS = 'ADD_IDEAS';

// pinned
export const FETCH_CONSULTATION_PINNED = createRequestTypes(
    'FETCH_CONSULTATION_PINNED'
);
export const SHOW_CONSULTATION_PINNED = 'SHOW_CONSULTATION_PINNED';
export const HIDE_CONSULTATION_PINNED = 'HIDE_CONSULTATION_PINNED';
// reports
export const FETCH_REPORTS = createRequestTypes('FETCH_REPORTS');
export const ADD_REPORTS = 'ADD_REPORTS';
