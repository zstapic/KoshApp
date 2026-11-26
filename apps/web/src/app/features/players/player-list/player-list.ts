import { Component, inject, OnInit, signal } from '@angular/core';
import { HttpErrorResponse } from '@angular/common/http';
import { RouterLink } from '@angular/router';
import { Players, Player } from '../players';

@Component({
  selector: 'app-player-list',
  imports: [RouterLink],
  templateUrl: './player-list.html',
  styleUrl: './player-list.scss',
})
export class PlayerList implements OnInit {
  private playersService = inject(Players);
  players = signal<Player[]>([]);
  error = signal('');

  ngOnInit() {
    this.playersService.getAll().subscribe(res => this.players.set(res.data));
  }

  remove(player: Player) {
    if (!confirm(`Obrisati igrača ${player.first_name} ${player.last_name}?`)) return;

    this.error.set('');
    this.playersService.delete(player.id).subscribe({
      next: () => this.players.update(list => list.filter(p => p.id !== player.id)),
      error: (err: HttpErrorResponse) =>
        this.error.set(err.error?.message ?? 'Brisanje nije uspjelo.'),
    });
  }
}