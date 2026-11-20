import { Component, inject, OnInit, signal } from '@angular/core';
import { HttpErrorResponse } from '@angular/common/http';
import { FormsModule } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { Clubs } from '../clubs';

@Component({
  selector: 'app-club-form',
  imports: [FormsModule, RouterLink],
  templateUrl: './club-form.html',
  styleUrl: './club-form.scss',
})
export class ClubForm implements OnInit {
  private clubsService = inject(Clubs);
  private router = inject(Router);
  private route = inject(ActivatedRoute);

  id: number | null = null;   // null = novi klub, broj = uređivanje
  name = signal('');
  error = signal('');

  ngOnInit() {
    const idParam = this.route.snapshot.paramMap.get('id');
    if (idParam) {
      this.id = Number(idParam);
      this.clubsService.getOne(this.id).subscribe({
        next: res => this.name.set(res.data.name),   
        error: () => this.error.set('Klub nije pronađen.'),
      });
    }
  }

  save() {
    this.error.set('');
    const request = this.id === null
      ? this.clubsService.create(this.name())
      : this.clubsService.update(this.id, this.name());

    request.subscribe({
      next: () => this.router.navigate(['/klubovi']),
      error: (err: HttpErrorResponse) =>
        this.error.set(err.error?.errors?.name?.[0] ?? err.error?.message ?? 'Spremanje nije uspjelo.'),
    });
  }
}