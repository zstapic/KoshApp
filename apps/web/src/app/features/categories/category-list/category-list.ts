import { Component, inject, OnInit, signal } from '@angular/core';
import { HttpErrorResponse } from '@angular/common/http';
import { RouterLink } from '@angular/router';
import { Categories, Category, opisDobi } from '../categories';

@Component({
  selector: 'app-category-list',
  imports: [RouterLink],
  templateUrl: './category-list.html',
  styleUrl: './category-list.scss',
})
export class CategoryList implements OnInit {
  private categoriesService = inject(Categories);

  categories = signal<Category[]>([]);
  error = signal('');
  readonly opisDobi = opisDobi;

  ngOnInit() {
    this.categoriesService.getAll().subscribe({
      next: res => this.categories.set(res.data),
      error: () => this.error.set('Dohvat kategorija nije uspio.'),
    });
  }

  remove(category: Category) {
    if (!confirm('Obrisati kategoriju ' + category.name + '?')) return;
    this.error.set('');
    this.categoriesService.delete(category.id).subscribe({
      next: () => this.categories.update(list => list.filter(c => c.id !== category.id)),
      // 409 resource_in_use: poruka dolazi sa servera
      error: (err: HttpErrorResponse) =>
        this.error.set(err.error?.message ?? 'Brisanje nije uspjelo.'),
    });
  }
}
