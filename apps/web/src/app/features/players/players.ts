import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';

export interface Player {
  id: number;
  first_name: string;
  last_name: string;
  date_of_birth: string;
  license_number: string;
  club: { id: number; name: string };
}

export interface PlayerInput {
  first_name: string;
  last_name: string;
  date_of_birth: string;
  license_number: string;
  club_id: number | null;
}

@Injectable({ providedIn: 'root' })
export class Players {
  private http = inject(HttpClient);

  getAll(): Observable<{ data: Player[] }> {
    return this.http.get<{ data: Player[] }>('/api/players');
  }

  getOne(id: number): Observable<{ data: Player }> {
    return this.http.get<{ data: Player }>(`/api/players/${id}`);
  }

  create(input: PlayerInput): Observable<{ data: Player }> {
    return this.http.post<{ data: Player }>('/api/players', input);
  }

  update(id: number, input: PlayerInput): Observable<{ data: Player }> {
    return this.http.put<{ data: Player }>(`/api/players/${id}`, input);
  }

  delete(id: number): Observable<void> {
    return this.http.delete<void>(`/api/players/${id}`);
  }
}