import { Component, inject, OnInit, signal } from '@angular/core';
import { FormsModule } from '@angular/forms';
import { RouterLink, Router, ActivatedRoute } from '@angular/router';
import { Referees, Referee } from '../referees';
import { HttpErrorResponse } from '@angular/common/http';

@Component({
  imports: [FormsModule, RouterLink],
  selector: 'app-referee-form',
  styleUrl: './referee-form.scss',
  templateUrl: './referee-form.html',
})
export class RefereeForm {
  private refereeService = inject(Referees);
  private router = inject(Router);
  private route = inject(ActivatedRoute);

  id: number | null = null;          // null = novi igrač
  firstName = signal('');
  lastName = signal('');
  error = signal('');

  ngOnInit() {
    const idParam = this.route.snapshot.paramMap.get('id');
    if (idParam) {
      this.id = Number(idParam);
      this.refereeService.getOne(this.id).subscribe({
        next: res => {
          const p = res.data;
          this.firstName.set(p.first_name);
          this.lastName.set(p.last_name);
        },
        error: () => this.error.set('Klub nije pronađen.'),
      });
    }
  }

  save() {
    this.error.set('');
    const input = {
      first_name: this.firstName(),
      last_name: this.lastName(),
    };
    console.log(this.lastName());

    const request = this.id === null
      ? this.refereeService.create(input)
      : this.refereeService.update(this.id, input);

    request.subscribe({
      next: () => this.router.navigate(['/suci']),
      error: (err: HttpErrorResponse) =>
        this.error.set(err.error?.errors?.name?.[0] ?? err.error?.message ?? 'Spremanje nije uspjelo.'),
    });
  }


}
