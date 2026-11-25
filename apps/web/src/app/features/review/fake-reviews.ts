import { Injectable } from '@angular/core';
import { delay, Observable, of } from 'rxjs';

export interface ReviewInput {
  rating: number; // 1-5
  comment: string | null; // prijedlog nije obavezan
}

export interface ReviewSummary {
  average: number;
  count: number;
  comments: { rating: number; comment: string | null }[];
  screens: { name: string; percent: number }[];
}

// PRIVREMENO: lažni servis dok backend nema /api/reviews.
// Podaci žive samo u memoriji (nestaju nakon osvježavanja stranice).
// Kad backend bude gotov, makni provider za FakeReviews iz app.routes.ts.
@Injectable()
export class FakeReviews {
  private nextId = 4;
  private reviews: ReviewInput[] = [
    { rating: 5, comment: 'App je super, fali izvoz u PDF.' },
    { rating: 4, comment: null },
    { rating: 3, comment: 'Obavijesti stižu prekasno.' },
  ];

  private screens = [
    { name: 'Klubovi', percent: 62 },
    { name: 'Ocjene', percent: 21 },
    { name: 'Prijava', percent: 9 },
  ];

  send(input: ReviewInput): Observable<{ data: { id: number } }> {
    this.reviews.push({ ...input });
    return of({ data: { id: this.nextId++ } }).pipe(delay(300));
  }

  getSummary(): Observable<{ data: ReviewSummary }> {
    const count = this.reviews.length;
    const sum = this.reviews.reduce((acc, r) => acc + r.rating, 0);

    return of({
      data: {
        average: count ? Math.round((sum / count) * 10) / 10 : 0,
        count,
        comments: [...this.reviews].reverse(),
        screens: this.screens,
      },
    }).pipe(delay(300));
  }
}
