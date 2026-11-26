import { Component, inject, OnInit, signal } from '@angular/core';
import { HttpErrorResponse } from '@angular/common/http';
import { FormsModule } from '@angular/forms';
import { ActivatedRoute, Router, RouterLink } from '@angular/router';
import { Categories, CategoryInput } from '../categories';

@Component({
  selector: 'app-category-form',
  imports: [FormsModule, RouterLink],
  templateUrl: './category-form.html',
  styleUrl: './category-form.scss',
})
export class CategoryForm implements OnInit {
  private categoriesService = inject(Categories);
  private router = inject(Router);
  private route = inject(ActivatedRoute);

  id: number | null = null;   // null = nova kategorija, broj = uređivanje
  name = signal('');
  maxAge = signal<number | null>(null);
  noUpperLimit = signal(false);   // seniori: max_age se šalje kao null

  error = signal('');             // opća greška (npr. 404, nema veze)
  nameError = signal('');
  maxAgeError = signal('');
  saving = signal(false);

  ngOnInit() {
    const idParam = this.route.snapshot.paramMap.get('id');
    if (idParam) {
      this.id = Number(idParam);
      this.categoriesService.getOne(this.id).subscribe({
        next: res => {
          this.name.set(res.data.name);
          this.maxAge.set(res.data.max_age);
          this.noUpperLimit.set(res.data.max_age === null);
        },
        error: () => this.error.set('Kategorija nije pronađena.'),
      });
    }
  }

  toggleNoUpperLimit(checked: boolean) {
    this.noUpperLimit.set(checked);
    if (checked) this.maxAge.set(null);
  }

  /** Što se šalje na API. Prazno polje dobi bez označenog checkboxa je greška, ne seniori. */
  buildInput(): CategoryInput | null {
    if (!this.noUpperLimit() && this.maxAge() === null) {
      this.maxAgeError.set('Upiši najveću dob ili označi "Bez gornje granice".');
      return null;
    }
    return { name: this.name(), max_age: this.noUpperLimit() ? null : this.maxAge() };
  }

  save() {
    this.error.set('');
    this.nameError.set('');
    this.maxAgeError.set('');

    const input = this.buildInput();
    if (!input) return;

    const request = this.id === null
      ? this.categoriesService.create(input)
      : this.categoriesService.update(this.id, input);

    this.saving.set(true);
    request.subscribe({
      next: () => this.router.navigate(['/kategorije']),
      error: (err: HttpErrorResponse) => {
        this.saving.set(false);
        const errors = err.error?.errors;
        if (err.status === 422 && errors) {
          this.nameError.set(errors.name?.[0] ?? '');
          this.maxAgeError.set(errors.max_age?.[0] ?? '');
        } else {
          this.error.set(err.error?.message ?? 'Spremanje nije uspjelo.');
        }
      },
    });
  }
}
