import { Component, inject, OnInit, signal } from '@angular/core';
import { RouterLink } from '@angular/router';
import { Clubs, Club } from '../clubs';
import { HttpErrorResponse } from '@angular/common/http';

@Component({
  selector: 'app-club-list',
  imports: [RouterLink],
  templateUrl: './club-list.html',
  styleUrl: './club-list.scss',
})
export class ClubList implements OnInit {
  private clubsService = inject(Clubs);
  clubs = signal<Club[]>([]);

  ngOnInit() {
    this.clubsService.getAll().subscribe(res => this.clubs.set(res.data));
  }

  error = signal('');

  remove(club: Club) {
    if (!confirm('Obrisati klub ' + club.name + '?')) return;
    this.error.set('');
    this.clubsService.delete(club.id).subscribe({
      next: () => this.clubs.update(list => list.filter(c => c.id !== club.id)),
      error: (err: HttpErrorResponse) =>
        this.error.set(err.error?.message ?? 'Brisanje nije uspjelo.'),
    });
  }
}