import { fetchIdeas } from './ideas';
import { fetchConsultationPinned } from './pinned';
import { ideaStatus } from '../../constants/api';

export function initHomePage() {
    const params = { limit: 5, order_desc: true };
    return dispatch =>
        Promise.all([
            // consultation pinned
            dispatch(fetchConsultationPinned()),
            // ideas
            dispatch(fetchIdeas(ideaStatus.FINALIZED, params)),
            dispatch(fetchIdeas(ideaStatus.PENDING, params)),
        ]);
}
