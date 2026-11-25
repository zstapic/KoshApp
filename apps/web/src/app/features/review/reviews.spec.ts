import { TestBed } from '@angular/core/testing';
import { firstValueFrom } from 'rxjs';
import { describe, it, expect, beforeEach } from 'vitest';
import { FakeReviews } from './fake-reviews';

// FakeReviews drži ocjene u memoriji (nema HTTP-a).
// Počinje s 3 ocjene: 5, 4, 3 -> prosjek 4.

describe('FakeReviews (servis)', () => {
  let service: FakeReviews;

  beforeEach(() => {
    TestBed.configureTestingModule({ providers: [FakeReviews] });
    service = TestBed.inject(FakeReviews);
  });

  it('getSummary() vraća početne podatke', async () => {
    const res = await firstValueFrom(service.getSummary());

    expect(res.data.count).toBe(3);
    expect(res.data.average).toBe(4);
  });

  it('send() vraća id nove ocjene', async () => {
    const res = await firstValueFrom(service.send({ rating: 4, comment: 'Fali izvoz u PDF' }));

    expect(res.data.id).toEqual(expect.any(Number));
  });

  it('nakon send() nova ocjena ulazi u broj i prosjek', async () => {
    await firstValueFrom(service.send({ rating: 1, comment: null }));
    const res = await firstValueFrom(service.getSummary());

    expect(res.data.count).toBe(4);
    expect(res.data.average).toBe(3.3); // (5 + 4 + 3 + 1) / 4 = 3.25 -> 3.3
  });

  it('poslani prijedlog se pojavljuje među prijedlozima', async () => {
    await firstValueFrom(service.send({ rating: 5, comment: 'Tamna tema, molim!' }));
    const res = await firstValueFrom(service.getSummary());

    expect(res.data.comments).toContainEqual({ rating: 5, comment: 'Tamna tema, molim!' });
  });
});
