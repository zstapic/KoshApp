# AI_LOG: feature `review` (ocjene aplikacije)

## Tijek i odluke

### 1. Sučelje
**Uputa:** "Dodaj simple sučelje za features/review, minimalno SCSS-a, isti dizajn kao do sada."

- Komponenta je napravljena prema ugovoru iz `review.spec.ts` (`aria-label`, `data-testid`).
- Stil je preuzet iz ekrana za klubove (kartica, narančasta `#ea580c`, isti okvir za greške i isti gumbi). Novi su samo okvir za uspjeh i zvjezdice.
- Prosjek se prikazuje s decimalnim zarezom ("4,0"), a riječ "ocjena" mijenja oblik prema broju (1, 3, 11, 57).
- Prijedlog koji je prazan ili sadrži samo razmake šalje se kao `null`.
- Dodana je ruta `/ocjene` i link "Ocjene" u izborniku. **Pretpostavka:** naziv rute nije dogovoren s timom.

### 2. Slanje ne radi: backend ne postoji
**Pitanje:** "Zašto slanje nije uspjelo?"

- U `apps/api` nema ruta, kontrolera ni tablice za ocjene, pa server vraća 404.
- AI je predložio backend, ali ga nije napravio jer feature nema tiket, a `screens` zahtijeva bilježenje korištenja zaslona koje ne postoji.

### 3. Lažni (fake) servis
**Uputa:** "Napravi mi fake servis u Angularu."

- Napravljen je `fake-reviews.ts` s podacima u memoriji (3 početne ocjene, kašnjenje 300 ms). Prosjek se računa iz poslanih ocjena.
- **Najkorišteniji zasloni u fakeu su izmišljeni i fiksni** (zbroj im je 92 %). Ne računaju se iz stvarnog korištenja. Ostaje otvoreno: računati ih iz posjeta rutama ili sakriti sekciju dok tim ne odluči.

### 4. Pravi servis obrisan, ostaje samo fake
**Pitanje developera:** "Zašto ne možemo obrisati Reviews ako ćemo koristiti samo fake?"

- AI je preporučio da `Reviews` ostane kao ključ za ubacivanje servisa, kako bi se kasnije lako vratilo na backend.
- **Odluka developera:** `reviews.ts` je obrisan. Sučelja `ReviewInput` i `ReviewSummary` premještena su u `fake-reviews.ts`, a komponenta i ruta izravno koriste `FakeReviews`.

### 5. Pokretanje testova
- Naredba (iz `apps/web`): `npx ng test --watch=false`
- **Problem na Windowsu:** ako je terminal otvoren na `c:\...` s malim slovom diska, Vitest javlja "failed to find the runner" za sve testove. Treba ga pokrenuti iz putanje s velikim `C:\...`.

### 6. Popravci testova
- `review.html`: Angular je brisao razmak između naziva zaslona i postotka ("Zadaci62 %"). Ispravljeno.
- `reviews.spec.ts`: prepisan tako da testira `FakeReviews` (početni podaci, `send()` vraća id, nova ocjena mijenja broj i prosjek, prijedlog se vidi). Stari testovi HTTP poziva uklonjeni su jer fake ne šalje zahtjeve.
- `app.spec.ts` (greška od prije, nije vezana uz ocjene):
  - Dodan je `provideRouter([])`, jer `routerLink` u izborniku treba router.
  - Test za naslov "Hello, web" iz početnog predloška zamijenjen je provjerom da se prikazuje "KoshApp".
- Rezultat: 4/4 datoteke, 22/22 testa prolaze.
