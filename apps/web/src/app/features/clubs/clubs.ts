import { HttpClient } from '@angular/common/http';
import { inject, Injectable } from '@angular/core';
import { Observable } from 'rxjs/internal/Observable';

export interface Club { id: number; name: string; }

@Injectable({ providedIn: 'root' })
export class Clubs{
    private http = inject(HttpClient);

    getAll(): Observable<{ data: Club[] }> {
        return this.http.get<{ data: Club[] }>('/api/clubs');
    }

    getOne(id: number): Observable<{ data: Club }> {
        return this.http.get<{ data: Club }>(`/api/clubs/${id}`);
    }

    create(name: string): Observable<{ data: Club }> {
        return this.http.post<{ data: Club }>('/api/clubs', { name });
    }

    delete(id: number): Observable<void> {
        return this.http.delete<void>(`/api/clubs/${id}`);
    }

    update(id: number, name: string): Observable<{ data: Club }> {
        return this.http.put<{ data: Club }>(`/api/clubs/${id}`, { name });
    }
}