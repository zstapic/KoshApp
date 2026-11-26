import { TestBed } from '@angular/core/testing';
import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { Categories, opisDobi } from './categories';

describe('Categories', () => {
  let service: Categories;
  let http: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      providers: [provideHttpClient(), provideHttpClientTesting()],
    });
    service = TestBed.inject(Categories);
    http = TestBed.inject(HttpTestingController);
  });

  afterEach(() => http.verify());

  it('dohvaća popis s paginacijom', () => {
    service.getAll().subscribe();
    const req = http.expectOne(r => r.url === '/api/categories');
    expect(req.request.method).toBe('GET');
    expect(req.request.params.get('page')).toBe('1');
    expect(req.request.params.get('per_page')).toBe('100');
    req.flush({ data: [], meta: { page: 1, per_page: 100, total: 0, last_page: 1 } });
  });

  it('šalje max_age: null za seniore', () => {
    service.create({ name: 'Seniori', max_age: null }).subscribe();
    const req = http.expectOne('/api/categories');
    expect(req.request.method).toBe('POST');
    expect(req.request.body).toEqual({ name: 'Seniori', max_age: null });
    req.flush({ data: { id: 1, name: 'Seniori', max_age: null } });
  });

  it('opisuje dob za prikaz', () => {
    expect(opisDobi(14)).toBe('do 14 godina');
    expect(opisDobi(null)).toBe('Bez gornje granice');
  });
});
