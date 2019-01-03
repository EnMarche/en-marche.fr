import { createRequestTypes } from '../helpers/actions';

// ui
export const SHOW_HEADER = 'SHOW_HEADER';
export const HIDE_HEADER = 'HIDE_HEADER';

// auth
export const SET_AUTH_USER = 'SET_AUTH_USER';
export const FETCH_AUTH_USER = createRequestTypes('FETCH_AUTH_USER');

// modal
export const SHOW_MODAL = 'SHOW_MODAL';
export const HIDE_MODAL = 'HIDE_MODAL';

// ideas
export const FETCH_IDEA = createRequestTypes('FETCH_IDEA');
export const FETCH_IDEAS = createRequestTypes('FETCH_IDEAS');
export const SET_IDEAS = 'SET_IDEAS';
export const ADD_IDEAS = 'ADD_IDEAS';

// current ideas
export const SAVE_CURRENT_IDEA = createRequestTypes('SAVE_CURRENT_IDEA');
export const SET_CURRENT_IDEA = 'SET_CURRENT_IDEA';
export const UPDATE_CURRENT_IDEA = 'UPDATE_CURRENT_IDEA';
export const FETCH_GUIDELINES = createRequestTypes('FETCH_GUIDELINES');
export const SET_GUIDELINES = 'SET_GUIDELINES';

// pinned
export const FETCH_CONSULTATION_PINNED = createRequestTypes('FETCH_CONSULTATION_PINNED');
export const SHOW_CONSULTATION_PINNED = 'SHOW_CONSULTATION_PINNED';
export const HIDE_CONSULTATION_PINNED = 'HIDE_CONSULTATION_PINNED';
// reports
export const FETCH_REPORTS = createRequestTypes('FETCH_REPORTS');
export const SET_REPORTS = 'SET_REPORTS';
