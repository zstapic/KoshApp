import { TestBed } from '@angular/core/testing';
import { provideHttpClient } from '@angular/common/http';
import { HttpTestingController, provideHttpClientTesting } from '@angular/common/http/testing';
import { provideRouter } from '@angular/router';
import { CategoryForm } from './category-form';

describe('CategoryForm', () => {
  let form: CategoryForm;
  let http: HttpTestingController;

  beforeEach(() => {
    TestBed.configureTestingModule({
      imports: [CategoryForm],
      providers: [provideHttpClient(), provideHttpClientTesting(), provideRouter([])],
    });
    form = TestBed.createComponent(CategoryForm).componentInstance;
    http = TestBed.inject(HttpTestingController);
  });

  afterEach(() => http.verify());

  it('checkbox "bez gornje granice" šalje max_age: null', () => {
    form.name.set('Seniori');
    form.maxAge.set(18);
    form.toggleNoUpperLimit(true);

    expect(form.buildInput()).toEqual({ name: 'Seniori', max_age: null });
  });

  it('prazna dob bez checkboxa je greška i ne šalje zahtjev', () => {
    form.name.set('U14');
    form.save();

    expect(form.maxAgeError()).toContain('Upiši najveću dob');
    http.expectNone('/api/categories');
  });

  it('prikazuje greške polja iz 422 odgovora', () => {
    form.name.set('U14');
    form.maxAge.set(14);
    form.save();

    http.expectOne('/api/categories').flush(
      {
        message: 'Podaci nisu ispravni.',
        errors: {
          name: ['Kategorija s tim nazivom već postoji.'],
          max_age: ['Već postoji kategorija za tu dob.'],
        },
      },
      { status: 422, statusText: 'Unprocessable Content' },
    );

    expect(form.nameError()).toBe('Kategorija s tim nazivom već postoji.');
    expect(form.maxAgeError()).toBe('Već postoji kategorija za tu dob.');
    expect(form.saving()).toBe(false);
  });
});
