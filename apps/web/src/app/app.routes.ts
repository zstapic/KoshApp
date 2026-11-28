import { Routes } from '@angular/router';
import { ClubList } from './features/clubs/club-list/club-list';
import { ClubForm } from './features/clubs/club-form/club-form';
import { CategoryList } from './features/categories/category-list/category-list';
import { CategoryForm } from './features/categories/category-form/category-form';
import { PlayerList } from './features/players/player-list/player-list';
import { PlayerForm } from './features/players/player-form/player-form';
import { RefereeList } from './features/referee/referee-list/referee-list';
import { RefereeForm } from './features/referee/referee-form/referee-form';

export const routes: Routes = [
    { path: 'klubovi', component: ClubList },
    { path: 'klubovi/novi', component: ClubForm },
    { path: 'klubovi/:id/uredi', component: ClubForm },
    { path: 'kategorije', component: CategoryList },
    { path: 'kategorije/novi', component: CategoryForm },
    { path: 'kategorije/:id/uredi', component: CategoryForm },
    { path: 'igraci', component: PlayerList },
    { path: 'igraci/novi', component: PlayerForm },
    { path: 'igraci/:id/uredi', component: PlayerForm },
    { path: 'suci', component: RefereeList },
    { path: 'suci/novi', component: RefereeForm },
    { path: 'suci/:id/uredi', component: RefereeForm },
];
