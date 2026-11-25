import { Component, computed, inject, OnInit, signal } from '@angular/core';
import { ReviewSummary, FakeReviews } from './fake-reviews';

@Component({
  imports: [],
  selector: 'app-review',
  styleUrl: './review.scss',
  templateUrl: './review.html',
})
export class Review implements OnInit {
  private reviewsService = inject(FakeReviews);

  readonly stars = [1, 2, 3, 4, 5];

  // forma
  rating = signal<number | null>(null);
  comment = signal('');
  formError = signal('');
  formSuccess = signal(false);
  sending = signal(false);

  // pregled
  summary = signal<ReviewSummary | null>(null);
  summaryError = signal('');

  comments = computed(() => (this.summary()?.comments ?? []).filter((c) => c.comment));
  average = computed(() => (this.summary()?.average ?? 0).toFixed(1).replace('.', ','));
  countLabel = computed(() => {
    const n = this.summary()?.count ?? 0;
    const last = n % 10;
    const lastTwo = n % 100;
    const word = last >= 1 && last <= 4 && !(lastTwo >= 11 && lastTwo <= 14) ? 'ocjene' : 'ocjena';
    return `iz ${n} ${word}`;
  });

  ngOnInit() {
    this.loadSummary();
  }

  loadSummary() {
    this.summaryError.set('');
    this.reviewsService.getSummary().subscribe({
      next: (res) => this.summary.set(res.data),
      error: () => this.summaryError.set('Pregled ocjena nije moguće učitati.'),
    });
  }

  onCommentInput(event: Event) {
    this.comment.set((event.target as HTMLTextAreaElement).value);
  }

  send() {
    this.formError.set('');
    this.formSuccess.set(false);

    const rating = this.rating();
    if (rating === null) {
      this.formError.set('Odaberi ocjenu od 1 do 5.');
      return;
    }

    const comment = this.comment().trim() || null;
    this.sending.set(true);
    this.reviewsService.send({ rating, comment }).subscribe({
      next: () => {
        this.sending.set(false);
        this.formSuccess.set(true);
        this.rating.set(null);
        this.comment.set('');
        this.loadSummary();
      },
      error: () => {
        this.sending.set(false);
        this.formError.set('Slanje nije uspjelo. Pokušaj ponovno.');
      },
    });
  }
}
