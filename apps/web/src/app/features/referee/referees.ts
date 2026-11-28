import { Injectable, inject } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { Observable } from 'rxjs';


export interface Referee {
  id:number;
  first_name: string;
  last_name: string;
}

export interface RefereeInput {
  first_name: string;
  last_name: string;
}

@Injectable({ providedIn: 'root' })
export class Referees {
  private http = inject(HttpClient);

  getAll(): Observable<{ data: Referee[] }> {
    return this.http.get<{ data: Referee[] }>('/api/referees');
  }

  getOne(id: number): Observable<{ data: Referee }> {
    return this.http.get<{ data: Referee }>(`/api/referees/${id}`);
  }

  create(input: RefereeInput): Observable<{ data: Referee }> {
    return this.http.post<{ data: Referee }>('/api/referees', input);
  }

  update(id: number, input: RefereeInput): Observable<{ data: Referee }> {
    return this.http.put<{ data: Referee }>(`/api/referees/${id}`, input);
  }

  delete(id: number): Observable<void> {
    return this.http.delete<void>(`/api/referees/${id}`);
  }
}