import { HttpClient, HttpParams } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { Observable } from 'rxjs';

/** Dobna kategorija (OpenAPI: Category). max_age = null znači seniori, bez gornje granice. */
export interface Category { id: number; name: string; max_age: number | null; }

/** Podaci za unos/izmjenu (OpenAPI: CategoryInput). */
export interface CategoryInput { name: string; max_age: number | null; }

export interface PaginationMeta { page: number; per_page: number; total: number; last_page: number; }

@Injectable({ providedIn: 'root' })
export class Categories {
    private http = inject(HttpClient);

    /** Kategorija je malo, pa se po defaultu dohvaća cijeli popis (max 100). Backend ih vraća sortirane po dobi. */
    getAll(page = 1, perPage = 100): Observable<{ data: Category[]; meta: PaginationMeta }> {
        const params = new HttpParams().set('page', page).set('per_page', perPage);
        return this.http.get<{ data: Category[]; meta: PaginationMeta }>('/api/categories', { params });
    }

    getOne(id: number): Observable<{ data: Category }> {
        return this.http.get<{ data: Category }>(`/api/categories/${id}`);
    }

    create(input: CategoryInput): Observable<{ data: Category }> {
        return this.http.post<{ data: Category }>('/api/categories', input);
    }

    update(id: number, input: CategoryInput): Observable<{ data: Category }> {
        return this.http.put<{ data: Category }>(`/api/categories/${id}`, input);
    }

    delete(id: number): Observable<void> {
        return this.http.delete<void>(`/api/categories/${id}`);
    }
}

/** Tekst dobi za prikaz u popisu. */
export function opisDobi(maxAge: number | null): string {
    return maxAge === null ? 'Bez gornje granice' : `do ${maxAge} godina`;
}
