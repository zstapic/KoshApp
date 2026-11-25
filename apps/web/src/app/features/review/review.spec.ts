import { ComponentFixture, TestBed } from '@angular/core/testing';
import { provideRouter } from '@angular/router';
import { of, throwError } from 'rxjs';
import { describe, it, expect, vi } from 'vitest';
import { Review } from './review';
import { FakeReviews } from './fake-reviews';

// UGOVOR (što implementacija mora imati)
//
// Komponenta Review (review.ts) ima dva dijela na istoj stranici:
//
// 1) FORMA ZA OCJENU
//    - 5 gumba s aria-label "Ocjena 1" ... "Ocjena 5"
//    - textarea s aria-label "Prijedlog"
//    - gumb s tekstom "Pošalji"
//    - data-testid="form-error" (greška) i data-testid="form-success" (zahvala)
//
// 2) PREGLED ZA VLASNIKA (učitava se čim se komponenta otvori)
//    - prosjek s decimalnim zarezom, npr. "4,0", i tekst "iz 3 ocjene"
//    - svaki prijedlog:  data-testid="comment"
//    - svaki zaslon:     data-testid="screen" s tekstom "Zadaci 62 %"
//    - data-testid="summary-empty" (nema ocjena) i "summary-error" (greška)
//
// Server za getSummary vraća:
//   { data: { average, count, comments:[{rating, comment}], screens:[{name, percent}] } }

function summary(over: Record<string, unknown> = {}) {
  return {
    data: {
      average: 4,
      count: 3,
      comments: [] as { rating: number; comment: string | null }[],
      screens: [] as { name: string; percent: number }[],
      ...over,
    },
  };
}

interface Options {
  send?: () => unknown;
  getSummary?: () => unknown;
}

async function setup(options: Options = {}) {
  const send = vi.fn(options.send ?? (() => of({ data: { id: 1 } })));
  const getSummary = vi.fn(options.getSummary ?? (() => of(summary())));

  await TestBed.configureTestingModule({
    imports: [Review],
    providers: [provideRouter([]), { provide: FakeReviews, useValue: { send, getSummary } }],
  }).compileComponents();

  const fixture: ComponentFixture<Review> = TestBed.createComponent(Review);
  const root: HTMLElement = fixture.nativeElement;
  await fixture.whenStable();

  const star = (n: number) => root.querySelector(`button[aria-label="Ocjena ${n}"]`) as HTMLButtonElement;
  const textarea = () => root.querySelector('textarea[aria-label="Prijedlog"]') as HTMLTextAreaElement;
  const sendButton = () =>
    Array.from(root.querySelectorAll('button')).find((b) => b.textContent?.includes('Pošalji')) as HTMLButtonElement;
  const byId = (id: string) => root.querySelector(`[data-testid="${id}"]`);

  const click = async (el: HTMLElement) => {
    el.click();
    await fixture.whenStable();
  };
  const type = async (text: string) => {
    textarea().value = text;
    textarea().dispatchEvent(new Event('input'));
    await fixture.whenStable();
  };

  return { fixture, root, send, getSummary, star, textarea, sendButton, byId, click, type };
}

describe('Review: forma za ocjenu', () => {
  it('prikazuje 5 zvjezdica, polje za prijedlog i gumb Pošalji', async () => {
    const { star, textarea, sendButton } = await setup();

    for (let n = 1; n <= 5; n++) expect(star(n)).not.toBeNull();
    expect(textarea()).not.toBeNull();
    expect(sendButton()).not.toBeNull();
  });

  it('bez odabrane ocjene ne šalje ništa i pokazuje grešku', async () => {
    const { send, sendButton, click, byId } = await setup();

    await click(sendButton());

    expect(send).not.toHaveBeenCalled();
    expect(byId('form-error')?.textContent).toContain('ocjenu');
  });

  it('šalje odabranu ocjenu i prijedlog', async () => {
    const { send, star, sendButton, click, type } = await setup();

    await click(star(4));
    await type('Fali izvoz u PDF');
    await click(sendButton());

    expect(send).toHaveBeenCalledTimes(1);
    expect(send).toHaveBeenCalledWith({ rating: 4, comment: 'Fali izvoz u PDF' });
  });

  it('prijedlog nije obavezan: prazan prijedlog se šalje kao null', async () => {
    const { send, star, sendButton, click } = await setup();

    await click(star(5));
    await click(sendButton());

    expect(send).toHaveBeenCalledWith({ rating: 5, comment: null });
  });

  it('prijedlog koji su samo razmaci šalje se kao null', async () => {
    const { send, star, sendButton, click, type } = await setup();

    await click(star(3));
    await type('   ');
    await click(sendButton());

    expect(send).toHaveBeenCalledWith({ rating: 3, comment: null });
  });

  it('nakon uspješnog slanja prikazuje zahvalu i briše formu', async () => {
    const { star, sendButton, click, type, textarea, byId } = await setup();

    await click(star(5));
    await type('Super');
    await click(sendButton());

    expect(byId('form-success')).not.toBeNull();
    expect(byId('form-error')).toBeNull();
    expect(textarea().value).toBe('');
  });

  it('nakon uspješnog slanja ponovno učitava pregled (nova ocjena se vidi odmah)', async () => {
    const { getSummary, star, sendButton, click } = await setup();
    expect(getSummary).toHaveBeenCalledTimes(1); // pri otvaranju

    await click(star(5));
    await click(sendButton());

    expect(getSummary).toHaveBeenCalledTimes(2);
  });

  it('ako server vrati grešku, prikazuje poruku i ne javlja uspjeh', async () => {
    const { star, sendButton, click, byId } = await setup({
      send: () => throwError(() => ({ status: 500 })),
    });

    await click(star(2));
    await click(sendButton());

    expect(byId('form-error')).not.toBeNull();
    expect(byId('form-success')).toBeNull();
  });
});

describe('Review: pregled za vlasnika', () => {
  it('kriterij prihvatljivosti: ocjene 5, 4 i 3 -> "4,0" iz 3 ocjene', async () => {
    const { root } = await setup({ getSummary: () => of(summary({ average: 4, count: 3 })) });

    expect(root.textContent).toContain('4,0');
    expect(root.textContent).toContain('iz 3 ocjene');
  });

  it('prosjek se prikazuje s jednom decimalom i zarezom (4,3 iz 57)', async () => {
    const { root } = await setup({ getSummary: () => of(summary({ average: 4.3, count: 57 })) });

    expect(root.textContent).toContain('4,3');
    expect(root.textContent).toContain('iz 57 ocjena');
  });

  it('prikazuje sve prijedloge', async () => {
    const { root } = await setup({
      getSummary: () =>
        of(
          summary({
            comments: [
              { rating: 5, comment: 'App je super, fali izvoz u PDF.' },
              { rating: 3, comment: 'Obavijesti stižu prekasno.' },
            ],
          }),
        ),
    });

    const items = root.querySelectorAll('[data-testid="comment"]');
    expect(items.length).toBe(2);
    expect(items[0].textContent).toContain('App je super');
    expect(items[1].textContent).toContain('Obavijesti stižu prekasno');
  });

  it('ocjene bez prijedloga (null) ne prikazuje kao prijedloge', async () => {
    const { root } = await setup({
      getSummary: () =>
        of(
          summary({
            comments: [
              { rating: 5, comment: null },
              { rating: 4, comment: 'Tamna tema, molim!' },
            ],
          }),
        ),
    });

    expect(root.querySelectorAll('[data-testid="comment"]').length).toBe(1);
  });

  it('prikazuje najkorištenije zaslone s postotkom', async () => {
    const { root } = await setup({
      getSummary: () =>
        of(
          summary({
            screens: [
              { name: 'Zadaci', percent: 62 },
              { name: 'App', percent: 21 },
              { name: 'Pretraga', percent: 9 },
            ],
          }),
        ),
    });

    const rows = Array.from(root.querySelectorAll('[data-testid="screen"]')).map((r) =>
      r.textContent?.replace(/\s+/g, ' ').trim(),
    );
    expect(rows).toEqual(['Zadaci 62 %', 'App 21 %', 'Pretraga 9 %']);
  });

  it('bez ocjena prikazuje prazno stanje umjesto prosjeka', async () => {
    const { root, byId } = await setup({ getSummary: () => of(summary({ average: 0, count: 0 })) });

    expect(byId('summary-empty')).not.toBeNull();
    expect(root.textContent).not.toContain('iz 0 ocjena');
  });

  it('ako server vrati grešku, prikazuje poruku', async () => {
    const { byId } = await setup({ getSummary: () => throwError(() => ({ status: 500 })) });

    expect(byId('summary-error')).not.toBeNull();
  });
});