import { Component, inject, OnInit, signal } from '@angular/core';
import { HttpErrorResponse } from '@angular/common/http';
import { FormsModule } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { Players } from '../players';
import { Clubs, Club } from '../../clubs/clubs';

@Component({
  selector: 'app-player-form',
  imports: [FormsModule, RouterLink],
  templateUrl: './player-form.html',
  styleUrl: './player-form.scss',
})
export class PlayerForm implements OnInit {
  private playersService = inject(Players);
  private clubsService = inject(Clubs);
  private router = inject(Router);
  private route = inject(ActivatedRoute);

  id: number | null = null;          // null = novi igrač
  firstName = signal('');
  lastName = signal('');
  dateOfBirth = signal('');
  licenseNumber = signal('');
  clubId = signal<number | null>(null);
  clubs = signal<Club[]>([]);
  error = signal('');

  ngOnInit() {
    this.clubsService.getAll().subscribe(res => this.clubs.set(res.data));

    const idParam = this.route.snapshot.paramMap.get('id');
    if (idParam) {
      this.id = Number(idParam);
      this.playersService.getOne(this.id).subscribe({
        next: res => {
          const p = res.data;
          this.firstName.set(p.first_name);
          this.lastName.set(p.last_name);
          this.dateOfBirth.set(p.date_of_birth);
          this.licenseNumber.set(p.license_number);
          this.clubId.set(p.club.id);
        },
        error: () => this.error.set('Igrač nije pronađen.'),
      });
    }
  }

  save() {
    this.error.set('');
    const input = {
      first_name: this.firstName(),
      last_name: this.lastName(),
      date_of_birth: this.dateOfBirth(),
      license_number: this.licenseNumber(),
      club_id: this.clubId(),
    };

    const request = this.id === null
      ? this.playersService.create(input)
      : this.playersService.update(this.id, input);

    request.subscribe({
      next: () => this.router.navigate(['/igraci']),
      error: (err: HttpErrorResponse) => {
        const errors = err.error?.errors;
        this.error.set(
          errors ? Object.values(errors).flat().join(' ') : (err.error?.message ?? 'Spremanje nije uspjelo.')
        );
      },
    });
  }
}