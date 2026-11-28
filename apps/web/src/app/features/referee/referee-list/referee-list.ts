import { Component, inject, OnInit, signal } from '@angular/core';
import { RouterLink } from '@angular/router';
import { Referees, Referee } from '../referees';
import { HttpErrorResponse } from '@angular/common/http';

@Component({
  imports: [RouterLink],
  selector: 'app-referee-list',
  styleUrl: './referee-list.scss',
  templateUrl: './referee-list.html',
  
})
export class RefereeList implements OnInit {
  private refereeService=inject(Referees);
  referees=signal<Referee[]>([]);
  error=signal('');
  

  ngOnInit(){
    this.refereeService.getAll().subscribe(res=>this.referees.set(res.data));
  }

  remove(referee: Referee) {
      if (!confirm('Obrisati suca ${referee.first_name} ${referee.last_name}?')) return;
      this.error.set('');
      this.refereeService.delete(referee.id).subscribe({
        next: () => this.referees.update(list => list.filter(r => r.id !== referee.id)),
        error: (err: HttpErrorResponse) =>
          this.error.set(err.error?.message ?? 'Brisanje nije uspjelo.'),
      });
    }

}
