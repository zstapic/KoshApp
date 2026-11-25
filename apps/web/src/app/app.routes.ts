import { Routes } from '@angular/router';
import { ClubList } from './features/clubs/club-list/club-list';
import { ClubForm } from './features/clubs/club-form/club-form';
import { Review } from './features/review/review';
import { FakeReviews } from './features/review/fake-reviews';

export const routes: Routes = [
    { path: 'klubovi', component: ClubList },
    { path: 'klubovi/novi', component: ClubForm },
    { path: 'klubovi/:id/uredi', component: ClubForm },
    { path: 'ocjene', component: Review, providers: [{ provide: FakeReviews, useClass: FakeReviews }] },
];
